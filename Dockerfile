# Multi-stage build para optimizar tamaño y velocidad
FROM php:8.2-fpm-alpine AS base

# Instalar dependencias del sistema necesarias
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    unzip \
    git \
    curl \
    oniguruma-dev \
    mysql-client \
    supervisor \
    nginx

# Configurar extensiones de PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    gd

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Stage de dependencias
FROM base AS dependencies

# Copiar archivos de configuración de dependencias
COPY composer.json composer.lock* ./

# Actualizar composer.lock si es necesario y luego instalar dependencias
# Esto regenera el lock file compatible con PHP 8.2
RUN if [ -f composer.lock ]; then \
        composer update --no-scripts --no-autoloader --no-dev --prefer-dist && \
        composer install --no-dev --no-scripts --no-autoloader --prefer-dist --optimize-autoloader; \
    else \
        composer install --no-dev --no-scripts --no-autoloader --prefer-dist --optimize-autoloader; \
    fi

# Copiar package.json para construir assets
COPY package.json package-lock.json* ./

# Instalar Node.js para construir assets
RUN apk add --no-cache nodejs npm

# Instalar dependencias de Node
RUN npm ci --only=production

# Stage de construcción
FROM dependencies AS build

# Copiar el resto del código
COPY . .

# Completar la instalación de Composer
RUN composer dump-autoload --optimize --no-dev

# Construir assets de frontend
RUN npm run build

# Stage final - imagen de producción
FROM base AS production

# Argumentos de build para variables de entorno
ARG APP_NAME="Laravel"
ARG APP_ENV=production
ARG APP_DEBUG=false
ARG APP_KEY
ARG APP_URL=http://localhost

ARG DB_CONNECTION=mysql
ARG DB_HOST=db
ARG DB_PORT=3306
ARG DB_DATABASE=laravel
ARG DB_USERNAME=laravel
ARG DB_PASSWORD

# Variables de entorno
ENV APP_NAME=${APP_NAME} \
    APP_ENV=${APP_ENV} \
    APP_DEBUG=${APP_DEBUG} \
    APP_KEY=${APP_KEY} \
    APP_URL=${APP_URL} \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=${DB_CONNECTION} \
    DB_HOST=${DB_HOST} \
    DB_PORT=${DB_PORT} \
    DB_DATABASE=${DB_DATABASE} \
    DB_USERNAME=${DB_USERNAME} \
    DB_PASSWORD=${DB_PASSWORD}

# Copiar vendor desde el stage de dependencias
COPY --from=build --chown=www-data:www-data /var/www/html/vendor ./vendor

# Copiar código de aplicación
COPY --chown=www-data:www-data . .

# Copiar assets construidos
COPY --from=build --chown=www-data:www-data /var/www/html/public/build ./public/build

# Configurar permisos
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Copiar configuración de Nginx
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Copiar configuración de Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copiar script de entrada
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Exponer puerto
EXPOSE 80

# Usuario www-data
USER www-data

# Usar script de entrada
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

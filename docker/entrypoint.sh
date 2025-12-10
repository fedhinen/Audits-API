#!/bin/sh
set -e

echo "Starting Laravel Application..."

# Debug: mostrar variables de entorno importantes (sin passwords)
echo "=== Environment Debug ==="
echo "APP_ENV: ${APP_ENV}"
echo "APP_DEBUG: ${APP_DEBUG}"
echo "APP_URL: ${APP_URL}"
echo "DB_CONNECTION: ${DB_CONNECTION}"
echo "DB_HOST: ${DB_HOST}"
echo "DB_PORT: ${DB_PORT}"
echo "DB_DATABASE: ${DB_DATABASE}"
echo "DB_USERNAME: ${DB_USERNAME}"
echo "========================="

# Asegurar que los directorios existen con permisos correctos ANTES de cualquier operación
echo "Creating and setting permissions for storage directories..."
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Limpiar cache anterior antes de reconfigurar
echo "Clearing previous cache..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

# Asegurar permisos correctos DESPUÉS de limpiar cache
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Esperar a que la base de datos esté lista usando PHP/Laravel
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Waiting for database to be ready..."
    counter=0
    max_tries=30
    
    # Usar PHP para verificar la conexión (más confiable que mysqladmin en Alpine)
    until php -r "
        try {
            new PDO(
                'mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}',
                '${DB_USERNAME}',
                '${DB_PASSWORD}',
                [PDO::ATTR_TIMEOUT => 5]
            );
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    " 2>/dev/null; do
        counter=$((counter+1))
        if [ $counter -gt $max_tries ]; then
            echo "Database connection timeout after $max_tries attempts."
            echo "DB_HOST=$DB_HOST, DB_PORT=$DB_PORT"
            echo "Continuing anyway..."
            break
        fi
        echo "Database is unavailable - sleeping (attempt $counter/$max_tries)"
        sleep 2
    done
    echo "Database is ready!"
fi

# Ejecutar migraciones
echo "Running database migrations..."
php artisan migrate --force || {
    echo "Migration failed! Check database connection."
}

# Ejecutar seeders
echo "Running database seeders..."
php artisan db:seed --force || echo "Seeding failed or already seeded."

# Cache de configuración solo si NO estamos en modo debug
if [ "$APP_DEBUG" = "true" ] || [ "$APP_DEBUG" = "1" ]; then
    echo "Debug mode enabled - skipping config cache..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
else
    echo "Caching configuration for production..."
    php artisan config:cache || {
        echo "Config cache failed! Running without config cache."
        php artisan config:clear
    }

    php artisan route:cache || {
        echo "Route cache failed! Running without route cache."
        php artisan route:clear
    }

    php artisan view:cache || {
        echo "View cache failed! Running without view cache."
        php artisan view:clear
    }
fi

# Crear enlace simbólico de storage si no existe
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link || echo "Storage link already exists or failed."
fi

# IMPORTANTE: Asegurar permisos DESPUÉS del caching
echo "Final permission fix..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Verificar que Laravel puede arrancar
echo "Testing Laravel bootstrap..."
php artisan --version || {
    echo "ERROR: Laravel cannot start properly!"
    echo "Checking for errors..."
    php -r "require '/var/www/html/vendor/autoload.php';" 2>&1
}

echo "Application ready!"

# Ejecutar supervisor para gestionar PHP-FPM y Nginx
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

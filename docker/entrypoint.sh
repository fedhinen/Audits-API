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

# Limpiar cache anterior antes de reconfigurar
echo "Clearing previous cache..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Asegurar permisos correctos en storage y cache
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Esperar a que la base de datos esté lista (opcional pero recomendado)
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Waiting for database to be ready..."
    counter=0
    max_tries=30
    
    # Usar mysqladmin para verificar conexión directa
    until mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent 2>/dev/null; do
        counter=$((counter+1))
        if [ $counter -gt $max_tries ]; then
            echo "Database connection timeout after $max_tries attempts."
            echo "DB_HOST=$DB_HOST, DB_PORT=$DB_PORT"
            echo "Continuing anyway - app may fail if DB is not available..."
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
    echo "Trying to show DB connection info..."
    php artisan db:show 2>/dev/null || true
}

# Ejecutar seeders
echo "Running database seeders..."
php artisan db:seed --force || echo "Seeding failed or already seeded."

# Cache de configuración (recomendado para producción)
echo "Caching configuration..."
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

# Crear enlace simbólico de storage si no existe
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link || echo "Storage link already exists or failed."
fi

echo "Application ready!"

# Ejecutar supervisor para gestionar PHP-FPM y Nginx
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

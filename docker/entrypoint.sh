#!/bin/sh
set -e

echo "Starting Laravel Application..."

# Esperar a que la base de datos esté lista (opcional pero recomendado)
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Waiting for database to be ready..."
    counter=0
    max_tries=30
    until php artisan migrate:status > /dev/null 2>&1 || [ $? -eq 1 ]; do
        counter=$((counter+1))
        if [ $counter -gt $max_tries ]; then
            echo "Database connection timeout. Continuing anyway..."
            break
        fi
        echo "Database is unavailable - sleeping (attempt $counter/$max_tries)"
        sleep 2
    done
    echo "Database is ready!"
fi

# Ejecutar migraciones
echo "Running database migrations..."
php artisan migrate --force

# Ejecutar seeders
echo "Running database seeders..."
php artisan db:seed --force

# Cache de configuración (recomendado para producción)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Crear enlace simbólico de storage si no existe
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link
fi

echo "Application ready!"

# Ejecutar supervisor para gestionar PHP-FPM y Nginx
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

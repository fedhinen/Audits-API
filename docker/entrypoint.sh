#!/bin/sh
set -e

echo "Starting Laravel Application..."

# Esperar a que la base de datos esté lista (opcional pero recomendado)
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Waiting for database to be ready..."
    until php artisan migrate:status > /dev/null 2>&1 || [ $? -eq 1 ]; do
        echo "Database is unavailable - sleeping"
        sleep 2
    done
fi

# Ejecutar migraciones (opcional - comentar si prefieres hacerlo manual)
# php artisan migrate --force

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

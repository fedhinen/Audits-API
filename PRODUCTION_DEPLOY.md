# 🚀 Guía de Despliegue en Producción con Docker Compose

Esta guía te ayudará a desplegar la aplicación Audits-API en producción usando Docker Compose.

## 📋 Pre-requisitos

- Docker 20.10+ instalado
- Docker Compose 2.0+ instalado
- Servidor con al menos 2GB RAM
- Dominio configurado (opcional pero recomendado)

## 🔧 Configuración Inicial

### 1. Clonar el Repositorio

```bash
git clone <tu-repositorio>
cd Audits-API
```

### 2. Configurar Variables de Entorno

```bash
# Copiar el archivo de ejemplo
cp .env.production.example .env.production

# Editar con tus valores
nano .env.production
```

### 3. Generar APP_KEY

```bash
# Opción 1: Si tienes PHP instalado
php artisan key:generate --show

# Opción 2: Con Docker
docker run --rm php:8.1-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

Copia la clave generada y pégala en `.env.production` en la variable `APP_KEY`.

### 4. Configurar Valores Sensibles

Edita `.env.production` y configura:

```bash
APP_KEY=base64:TU_CLAVE_GENERADA
APP_URL=https://tu-dominio.com

DB_DATABASE=audits_production
DB_USERNAME=audits_user
DB_PASSWORD=ContraseñaSegura123!
DB_ROOT_PASSWORD=RootPasswordSeguro456!

SANCTUM_STATEFUL_DOMAINS=tu-dominio.com,www.tu-dominio.com
```

## 🚀 Despliegue

### 1. Construir las Imágenes

```bash
# Cargar variables de entorno
export $(cat .env.production | xargs)

# Construir la imagen
docker-compose -f docker-compose.example.yml build --no-cache
```

### 2. Iniciar los Servicios

```bash
# Iniciar en modo detached
docker-compose -f docker-compose.example.yml up -d

# Ver los logs
docker-compose -f docker-compose.example.yml logs -f
```

### 3. Ejecutar Migraciones

```bash
# Primera vez - ejecutar migraciones
docker-compose -f docker-compose.example.yml exec app php artisan migrate --force

# Ejecutar seeders (opcional)
docker-compose -f docker-compose.example.yml exec app php artisan db:seed --force
```

### 4. Verificar el Estado

```bash
# Ver contenedores en ejecución
docker-compose -f docker-compose.example.yml ps

# Verificar salud de los servicios
docker-compose -f docker-compose.example.yml ps
```

## 🔒 Configuración de Nginx Reverse Proxy (Recomendado)

Si tienes un servidor con dominio, configura Nginx como reverse proxy:

```nginx
# /etc/nginx/sites-available/audits-api

server {
    listen 80;
    server_name tu-dominio.com www.tu-dominio.com;
    
    # Redirigir a HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name tu-dominio.com www.tu-dominio.com;

    # Certificados SSL (usar Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/tu-dominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tu-dominio.com/privkey.pem;

    # Configuración SSL
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
}
```

Habilitar el sitio:
```bash
sudo ln -s /etc/nginx/sites-available/audits-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 🔐 Configurar SSL con Let's Encrypt

```bash
# Instalar Certbot
sudo apt update
sudo apt install certbot python3-certbot-nginx

# Obtener certificado
sudo certbot --nginx -d tu-dominio.com -d www.tu-dominio.com

# Auto-renovación (ya configurado por defecto)
sudo certbot renew --dry-run
```

## 📊 Comandos Útiles

### Gestión de Contenedores

```bash
# Ver logs
docker-compose -f docker-compose.example.yml logs -f app
docker-compose -f docker-compose.example.yml logs -f db

# Reiniciar servicios
docker-compose -f docker-compose.example.yml restart

# Detener servicios
docker-compose -f docker-compose.example.yml down

# Detener y eliminar volúmenes (⚠️ CUIDADO: Elimina datos)
docker-compose -f docker-compose.example.yml down -v
```

### Comandos de Laravel

```bash
# Acceder al contenedor
docker-compose -f docker-compose.example.yml exec app sh

# Ejecutar comandos Artisan
docker-compose -f docker-compose.example.yml exec app php artisan cache:clear
docker-compose -f docker-compose.example.yml exec app php artisan config:cache
docker-compose -f docker-compose.example.yml exec app php artisan route:cache
docker-compose -f docker-compose.example.yml exec app php artisan view:cache

# Ver estado de migraciones
docker-compose -f docker-compose.example.yml exec app php artisan migrate:status

# Crear usuario (si tienes comando personalizado)
docker-compose -f docker-compose.example.yml exec app php artisan user:create
```

### Base de Datos

```bash
# Acceder a MySQL
docker-compose -f docker-compose.example.yml exec db mysql -u root -p

# Backup de base de datos
docker-compose -f docker-compose.example.yml exec db mysqldump -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} > backup_$(date +%Y%m%d_%H%M%S).sql

# Restaurar backup
docker-compose -f docker-compose.example.yml exec -T db mysql -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} < backup.sql
```

## 🔄 Actualización de la Aplicación

```bash
# 1. Hacer backup de la base de datos
docker-compose -f docker-compose.example.yml exec db mysqldump -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} > backup_pre_update.sql

# 2. Detener los servicios
docker-compose -f docker-compose.example.yml down

# 3. Actualizar el código
git pull origin main

# 4. Reconstruir la imagen
export $(cat .env.production | xargs)
docker-compose -f docker-compose.example.yml build --no-cache

# 5. Iniciar servicios
docker-compose -f docker-compose.example.yml up -d

# 6. Ejecutar migraciones
docker-compose -f docker-compose.example.yml exec app php artisan migrate --force

# 7. Limpiar cache
docker-compose -f docker-compose.example.yml exec app php artisan optimize:clear
docker-compose -f docker-compose.example.yml exec app php artisan config:cache
docker-compose -f docker-compose.example.yml exec app php artisan route:cache
docker-compose -f docker-compose.example.yml exec app php artisan view:cache
```

## 🔍 Monitoreo y Logs

### Ver Logs en Tiempo Real

```bash
# Logs de aplicación Laravel
docker-compose -f docker-compose.example.yml exec app tail -f /var/www/html/storage/logs/laravel.log

# Logs de Nginx
docker-compose -f docker-compose.example.yml exec app tail -f /var/log/nginx/error.log

# Logs de MySQL
docker-compose -f docker-compose.example.yml exec db tail -f /var/log/mysql/error.log
```

### Health Checks

```bash
# Verificar salud de los contenedores
docker-compose -f docker-compose.example.yml ps

# El resultado debe mostrar "healthy" en la columna Status
```

## 🐛 Troubleshooting

### Error: "SQLSTATE[HY000] [2002] Connection refused"

```bash
# Verificar que el contenedor de BD esté ejecutándose
docker-compose -f docker-compose.example.yml ps db

# Ver logs de MySQL
docker-compose -f docker-compose.example.yml logs db

# Esperar a que MySQL esté listo
docker-compose -f docker-compose.example.yml exec app php artisan migrate:status
```

### Error 500 - Internal Server Error

```bash
# Ver logs de Laravel
docker-compose -f docker-compose.example.yml exec app cat /var/www/html/storage/logs/laravel.log

# Verificar permisos
docker-compose -f docker-compose.example.yml exec app ls -la /var/www/html/storage

# Limpiar cache
docker-compose -f docker-compose.example.yml exec app php artisan optimize:clear
```

### Problemas de Permisos

```bash
# Dentro del contenedor, arreglar permisos
docker-compose -f docker-compose.example.yml exec app chown -R www-data:www-data /var/www/html/storage
docker-compose -f docker-compose.example.yml exec app chmod -R 775 /var/www/html/storage
```

## 🔐 Seguridad

### Checklist de Seguridad

- [ ] `APP_DEBUG=false` en producción
- [ ] Contraseñas fuertes para BD
- [ ] SSL/TLS configurado (HTTPS)
- [ ] Firewall configurado (solo puertos necesarios abiertos)
- [ ] Backups automáticos configurados
- [ ] Logs monitoreados
- [ ] Variables sensibles en `.env.production` (no en Git)
- [ ] Actualizaciones de seguridad aplicadas

### Configurar Firewall (UFW)

```bash
# Permitir SSH
sudo ufw allow 22/tcp

# Permitir HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Denegar MySQL desde exterior (solo localhost)
sudo ufw deny 3306/tcp

# Habilitar firewall
sudo ufw enable
```

## 📦 Backups Automáticos

Crear script de backup:

```bash
# /usr/local/bin/backup-audits-api.sh

#!/bin/bash
BACKUP_DIR="/backups/audits-api"
DATE=$(date +%Y%m%d_%H%M%S)

# Crear directorio de backups
mkdir -p $BACKUP_DIR

# Backup de base de datos
docker-compose -f /ruta/a/docker-compose.example.yml exec -T db mysqldump \
  -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} > $BACKUP_DIR/db_$DATE.sql

# Comprimir
gzip $BACKUP_DIR/db_$DATE.sql

# Eliminar backups antiguos (más de 7 días)
find $BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete

echo "Backup completado: db_$DATE.sql.gz"
```

Programar con cron:

```bash
# Editar crontab
sudo crontab -e

# Agregar backup diario a las 2 AM
0 2 * * * /usr/local/bin/backup-audits-api.sh >> /var/log/audits-backup.log 2>&1
```

## 📞 Soporte

Para problemas o preguntas:
1. Revisa los logs
2. Verifica las variables de entorno
3. Consulta la documentación en `ENV_CONFIG.md`
4. Contacta al equipo de desarrollo

# Configuración de Variables de Entorno para Docker

Este documento describe todas las variables de entorno necesarias para desplegar la aplicación Laravel con Docker a través de Dokploy.

## 📋 Variables Requeridas

### Aplicación Base

| Variable | Descripción | Valor por Defecto | Requerida |
|----------|-------------|-------------------|-----------|
| `APP_NAME` | Nombre de la aplicación | Laravel | No |
| `APP_ENV` | Entorno de ejecución | production | Sí |
| `APP_KEY` | Clave de encriptación de Laravel (base64:...) | - | **Sí** |
| `APP_DEBUG` | Modo debug (true/false) | false | Sí |
| `APP_URL` | URL pública de la aplicación | http://localhost | Sí |

### Base de Datos

| Variable | Descripción | Valor por Defecto | Requerida |
|----------|-------------|-------------------|-----------|
| `DB_CONNECTION` | Tipo de base de datos (mysql, pgsql, sqlite) | mysql | Sí |
| `DB_HOST` | Host de la base de datos | db | Sí |
| `DB_PORT` | Puerto de la base de datos | 3306 | Sí |
| `DB_DATABASE` | Nombre de la base de datos | laravel | Sí |
| `DB_USERNAME` | Usuario de la base de datos | laravel | Sí |
| `DB_PASSWORD` | Contraseña de la base de datos | - | **Sí** |

### Cache y Sesiones (Opcional)

| Variable | Descripción | Valor por Defecto | Requerida |
|----------|-------------|-------------------|-----------|
| `CACHE_DRIVER` | Driver de cache (file, redis, memcached) | file | No |
| `SESSION_DRIVER` | Driver de sesiones (file, cookie, database, redis) | file | No |
| `SESSION_LIFETIME` | Duración de sesiones en minutos | 120 | No |
| `QUEUE_CONNECTION` | Driver de colas (sync, database, redis) | sync | No |

### Mail (Si se utiliza)

| Variable | Descripción | Valor por Defecto | Requerida |
|----------|-------------|-------------------|-----------|
| `MAIL_MAILER` | Driver de correo (smtp, sendmail, mailgun, etc.) | smtp | No |
| `MAIL_HOST` | Host del servidor SMTP | - | Si usa mail |
| `MAIL_PORT` | Puerto SMTP | 587 | Si usa mail |
| `MAIL_USERNAME` | Usuario SMTP | - | Si usa mail |
| `MAIL_PASSWORD` | Contraseña SMTP | - | Si usa mail |
| `MAIL_ENCRYPTION` | Encriptación (tls, ssl) | tls | Si usa mail |
| `MAIL_FROM_ADDRESS` | Email remitente | - | Si usa mail |
| `MAIL_FROM_NAME` | Nombre remitente | ${APP_NAME} | Si usa mail |

### Laravel Sanctum

| Variable | Descripción | Valor por Defecto | Requerida |
|----------|-------------|-------------------|-----------|
| `SANCTUM_STATEFUL_DOMAINS` | Dominios permitidos para cookies | localhost | Si usa SPA |

## 🔑 Generación de APP_KEY

La variable `APP_KEY` es **crítica** para la seguridad de la aplicación. Para generarla:

### Opción 1: Localmente (Recomendado)
```bash
php artisan key:generate --show
```

### Opción 2: En contenedor temporal
```bash
docker run --rm php:8.1-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

La clave debe tener el formato: `base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX`

## 🚀 Configuración en Dokploy

### 1. Variables de Build (Build Args)

Al construir la imagen, Dokploy permite pasar **Build Arguments**. Configura las siguientes:

```bash
APP_NAME=Audits-API
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:TU_CLAVE_GENERADA_AQUI
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=tu-host-db
DB_PORT=3306
DB_DATABASE=audits_db
DB_USERNAME=audits_user
DB_PASSWORD=tu_password_seguro
```

### 2. Variables de Runtime (Environment Variables)

Estas variables se pueden sobrescribir en tiempo de ejecución sin reconstruir la imagen:

```bash
# En Dokploy, sección "Environment Variables"
APP_KEY=base64:TU_CLAVE_GENERADA_AQUI
APP_URL=https://tu-dominio.com
DB_HOST=tu-host-db
DB_DATABASE=audits_db
DB_USERNAME=audits_user
DB_PASSWORD=tu_password_seguro
```

## 📦 Ejemplo de Configuración Completa para Dokploy

### Pestaña "Build"
```
Build Args:
APP_ENV=production
APP_DEBUG=false
```

### Pestaña "Environment"
```bash
# Aplicación
APP_NAME=Audits-API
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_URL=https://audits.ejemplo.com
APP_ENV=production
APP_DEBUG=false

# Base de Datos
DB_CONNECTION=mysql
DB_HOST=mysql.ejemplo.com
DB_PORT=3306
DB_DATABASE=audits_production
DB_USERNAME=audits_user
DB_PASSWORD=SuperSecurePassword123!

# Logs
LOG_CHANNEL=stderr

# Sesiones
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache
CACHE_DRIVER=file

# Colas
QUEUE_CONNECTION=database
```

## 🔒 Seguridad

### Variables Sensibles
Las siguientes variables **NUNCA** deben estar en el código:
- `APP_KEY`
- `DB_PASSWORD`
- `MAIL_PASSWORD`
- Cualquier API key o token

### Recomendaciones
1. ✅ Usa contraseñas fuertes para `DB_PASSWORD`
2. ✅ Genera un `APP_KEY` único por entorno
3. ✅ Mantén `APP_DEBUG=false` en producción
4. ✅ Usa HTTPS en producción (`APP_URL=https://...`)
5. ✅ Limita `SANCTUM_STATEFUL_DOMAINS` a tus dominios reales

## 📝 Notas Importantes

### Primera Ejecución
En la primera ejecución, necesitas ejecutar las migraciones. Puedes:

1. **Automático**: Descomentar en `docker/entrypoint.sh`:
   ```bash
   php artisan migrate --force
   ```

2. **Manual**: Ejecutar desde Dokploy terminal:
   ```bash
   docker exec -it <container-name> php artisan migrate --force
   ```

### Storage
El directorio `/var/www/html/storage` debe ser persistente. En Dokploy:
- Configura un volumen para `/var/www/html/storage`
- Esto preservará logs, archivos subidos, cache, etc.

### Health Check
Dokploy puede verificar que la app esté funcionando:
```
Health Check Path: /
Health Check Port: 80
```

## 🔧 Troubleshooting

### Error: "No application encryption key has been specified"
- Verifica que `APP_KEY` esté configurado correctamente
- Debe empezar con `base64:`

### Error de Conexión a Base de Datos
- Verifica que `DB_HOST` sea accesible desde el contenedor
- Confirma que las credenciales sean correctas
- Asegúrate que el puerto `DB_PORT` esté abierto

### Problemas de Permisos
- Los directorios `storage/` y `bootstrap/cache/` tienen permisos 775
- El usuario `www-data` debe poder escribir en estos directorios

## 📚 Recursos Adicionales

- [Documentación Laravel - Configuration](https://laravel.com/docs/10.x/configuration)
- [Documentación Laravel - Deployment](https://laravel.com/docs/10.x/deployment)
- [Dokploy Documentation](https://docs.dokploy.com)

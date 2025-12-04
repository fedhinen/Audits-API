# Guía Rápida de Despliegue en Dokploy

## 🚀 Pasos para Desplegar

### 1. Preparación Previa

Antes de subir a Dokploy, genera tu `APP_KEY`:
```bash
# Opción 1: Si tienes PHP localmente
php artisan key:generate --show

# Opción 2: Con Docker
docker run --rm php:8.1-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

Copia la clave generada (debe empezar con `base64:`)

### 2. Configuración en Dokploy

#### A. Crear Aplicación
1. En Dokploy, crea una nueva aplicación
2. Selecciona "Dockerfile" como método de build
3. Conecta tu repositorio Git

#### B. Configurar Build Arguments
En la sección de "Build", agrega estos argumentos:

```
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:TU_CLAVE_AQUI
APP_URL=https://tu-dominio.com
DB_CONNECTION=mysql
DB_HOST=tu-host-mysql
DB_PORT=3306
DB_DATABASE=nombre_bd
DB_USERNAME=usuario_bd
DB_PASSWORD=password_bd
```

#### C. Variables de Entorno
En la sección "Environment Variables", agrega:

```bash
APP_NAME=Audits-API
APP_KEY=base64:TU_CLAVE_AQUI
APP_URL=https://tu-dominio.com
DB_HOST=tu-host-mysql
DB_DATABASE=nombre_bd
DB_USERNAME=usuario_bd
DB_PASSWORD=password_bd
LOG_CHANNEL=stderr
```

#### D. Configurar Base de Datos

**Opción 1: Base de datos externa**
- Usa los datos de tu proveedor MySQL

**Opción 2: Base de datos en Dokploy**
1. Crea un servicio MySQL en Dokploy
2. Usa el nombre del servicio como `DB_HOST`
3. Configura las credenciales

#### E. Volúmenes Persistentes
Agrega un volumen para persistir datos:
```
/var/www/html/storage
```

### 3. Construir y Desplegar

1. Click en "Build"
2. Espera a que termine el build (~2-5 minutos)
3. Una vez completado, click en "Deploy"

### 4. Primera Ejecución - Migraciones

**Opción A: Automático** (Recomendado para primera vez)
Edita `docker/entrypoint.sh` y descomenta:
```bash
php artisan migrate --force
```
Luego reconstruye la imagen.

**Opción B: Manual**
Ejecuta desde el terminal de Dokploy:
```bash
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder  # Si tienes seeders
```

### 5. Verificación

Accede a tu dominio y verifica:
- ✅ La aplicación carga
- ✅ No hay errores 500
- ✅ Las rutas API funcionan

## 🔧 Comandos Útiles

Desde el terminal de Dokploy:

```bash
# Ver logs
php artisan log:show

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ver estado de migraciones
php artisan migrate:status

# Crear usuario admin (si tienes un comando)
php artisan user:create

# Verificar configuración
php artisan config:show
```

## 📊 Monitoreo

### Health Check en Dokploy
```
Path: /
Port: 80
Interval: 30s
```

### Ver Logs
```bash
# Logs de aplicación
tail -f /var/www/html/storage/logs/laravel.log

# Logs de Nginx
tail -f /var/log/nginx/error.log
```

## 🐛 Solución de Problemas Comunes

### Error 500
```bash
# Verificar logs
cat /var/www/html/storage/logs/laravel.log

# Verificar permisos
ls -la /var/www/html/storage
```

### Error de Base de Datos
```bash
# Probar conexión
php artisan tinker
>>> DB::connection()->getPdo();
```

### Cache de Configuración
```bash
# Limpiar todo el cache
php artisan optimize:clear
```

## 🔄 Actualización de la Aplicación

Cada vez que hagas cambios:

1. **Push al repositorio Git**
2. **En Dokploy**: Click en "Rebuild"
3. **Esperar**: El build se ejecutará automáticamente
4. **Verificar**: Revisa que todo funcione

Si hay nuevas migraciones:
```bash
php artisan migrate --force
```

## 📁 Estructura de Archivos Docker

```
.
├── Dockerfile                      # Imagen principal
├── .dockerignore                   # Archivos ignorados
├── docker/
│   ├── nginx/
│   │   └── default.conf           # Configuración Nginx
│   ├── supervisor/
│   │   └── supervisord.conf       # Supervisor (PHP-FPM + Nginx)
│   └── entrypoint.sh              # Script de inicio
├── ENV_CONFIG.md                   # Esta guía de variables
└── DOKPLOY_GUIDE.md               # Esta guía rápida
```

## 🎯 Checklist Pre-Deploy

- [ ] `APP_KEY` generado y configurado
- [ ] Base de datos MySQL creada
- [ ] Credenciales de BD configuradas
- [ ] `APP_URL` con tu dominio real
- [ ] `APP_DEBUG=false` en producción
- [ ] Volumen de `/var/www/html/storage` configurado
- [ ] Variables de entorno verificadas
- [ ] Build exitoso

## 📞 Soporte

Si encuentras problemas:
1. Revisa los logs de Dokploy
2. Verifica las variables de entorno
3. Confirma que la BD sea accesible
4. Revisa `ENV_CONFIG.md` para más detalles

# Guía de Despliegue en Producción - Diner

## Índice
1. [Configuración Inicial del Servidor](#configuración-inicial-del-servidor)
2. [Despliegue de la Aplicación](#despliegue-de-la-aplicación)
3. [Solución de Problemas Comunes](#solución-de-problemas-comunes)
4. [Mantenimiento](#mantenimiento)

---

## Configuración Inicial del Servidor

### Requisitos del Servidor
- PHP 8.3+
- MySQL 8.0+
- Composer
- Node.js 18+ y NPM
- Git

### Instalación de Dependencias del Sistema

```bash
# Actualizar el sistema
sudo apt update && sudo apt upgrade -y

# Instalar PHP y extensiones requeridas
sudo apt install php8.3 php8.3-fpm php8.3-mysql php8.3-xml php8.3-mbstring \
php8.3-curl php8.3-zip php8.3-gd php8.3-intl php8.3-bcmath -y

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar Node.js y NPM
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y
```

---

## Despliegue de la Aplicación

### 1. Clonar el Repositorio

```bash
# Navegar al directorio web
cd /var/www

# Clonar el repositorio
git clone https://github.com/lrmaldo/Diner.git diner.sattlink.com
cd diner.sattlink.com

# Establecer permisos correctos
sudo chown -R www-data:www-data /var/www/diner.sattlink.com
sudo chmod -R 755 /var/www/diner.sattlink.com
```

### 2. Configurar Variables de Entorno

```bash
# Copiar el archivo de configuración
cp .env.example .env

# Editar el archivo .env con los datos de producción
nano .env
```

Configurar las siguientes variables:
```env
APP_NAME=Diner
APP_ENV=production
APP_DEBUG=false
APP_URL=https://diner.sattlink.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=diner_production
DB_USERNAME=diner_user
DB_PASSWORD=tu_contraseña_segura
```

### 3. Instalar Dependencias

```bash
# Instalar dependencias de PHP
composer install --optimize-autoloader --no-dev

# Instalar dependencias de Node.js
npm install

# Compilar assets para producción
npm run build
```

### 4. Configurar la Aplicación

```bash
# Generar la clave de la aplicación
php artisan key:generate

# Crear enlaces simbólicos para storage
php artisan storage:link

# Optimizar la aplicación
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Ejecutar Migraciones

```bash
# IMPORTANTE: Ver sección de "Solución de Problemas" antes de ejecutar
php artisan migrate --force
```

---

## Solución de Problemas Comunes

### Error: "Duplicate column name 'monto_sugerido'" al ejecutar migraciones

Este error ocurre porque la columna `monto_sugerido` ya existe en la base de datos de producción, pero Laravel intenta crearla nuevamente a través de la migración `2025_11_07_183421_add_monto_sugerido_to_cliente_prestamo_table.php`.

#### Causa
La migración fue creada después de que la columna ya existía en la base de datos de producción.

#### Solución

**Método 1: Usando Tinker (Recomendado)**

```bash
cd /var/www/diner.sattlink.com

# Marcar la migración problemática como ejecutada sin ejecutarla
php artisan tinker --execute="DB::table('migrations')->insert(['migration' => '2025_11_07_183421_add_monto_sugerido_to_cliente_prestamo_table', 'batch' => DB::table('migrations')->max('batch') + 1]); echo 'Migración marcada como ejecutada';"

# Ejecutar las migraciones pendientes
php artisan migrate --force
```

**Método 2: Usando MySQL directamente**

```bash
# Conectarse a MySQL
mysql -u root -p

# Seleccionar la base de datos
USE diner_production;

# Verificar migraciones actuales
SELECT * FROM migrations ORDER BY id DESC LIMIT 5;

# Insertar la migración problemática como ya ejecutada
INSERT INTO migrations (migration, batch) 
VALUES ('2025_11_07_183421_add_monto_sugerido_to_cliente_prestamo_table', 
        (SELECT MAX(batch) + 1 FROM (SELECT batch FROM migrations) as m));

# Salir de MySQL
EXIT;

# Ejecutar las migraciones pendientes
cd /var/www/diner.sattlink.com
php artisan migrate --force
```

**Método 3: Usando Tinker interactivo**

```bash
cd /var/www/diner.sattlink.com
php artisan tinker
```

Dentro de tinker, ejecutar:
```php
DB::table('migrations')->insert([
    'migration' => '2025_11_07_183421_add_monto_sugerido_to_cliente_prestamo_table',
    'batch' => DB::table('migrations')->max('batch') + 1
]);

// Verificar que se insertó correctamente
DB::table('migrations')->where('migration', 'LIKE', '%monto_sugerido%')->first();

// Presionar Ctrl+D para salir
```

Luego ejecutar:
```bash
php artisan migrate --force
```

#### Verificación

Después de aplicar la solución, verifica que:

1. La migración problemática esté registrada:
```bash
php artisan migrate:status
```

2. La columna existe en la tabla:
```bash
php artisan tinker --execute="Schema::hasColumn('cliente_prestamo', 'monto_sugerido') ? 'Columna existe' : 'Columna NO existe';"
```

3. La columna `monto_autorizado` fue creada correctamente:
```bash
php artisan tinker --execute="Schema::hasColumn('cliente_prestamo', 'monto_autorizado') ? 'Columna existe' : 'Columna NO existe';"
```

---

## Actualización de la Aplicación (Pull Updates)

### Proceso de Actualización

```bash
# 1. Navegar al directorio de la aplicación
cd /var/www/diner.sattlink.com

# 2. Poner la aplicación en modo mantenimiento
php artisan down

# 3. Hacer backup de la base de datos (IMPORTANTE)
mysqldump -u diner_user -p diner_production > ~/backup_$(date +%Y%m%d_%H%M%S).sql

# 4. Obtener los últimos cambios
git pull origin master

# 5. Instalar/actualizar dependencias
composer install --optimize-autoloader --no-dev
npm install
npm run build

# 6. Ejecutar migraciones (verificar sección de solución de problemas si hay errores)
php artisan migrate --force

# 7. Limpiar y optimizar caché
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Restaurar permisos
sudo chown -R www-data:www-data /var/www/diner.sattlink.com
sudo chmod -R 755 /var/www/diner.sattlink.com

# 9. Salir del modo mantenimiento
php artisan up
```

### Verificación Post-Actualización

```bash
# Verificar el estado de las migraciones
php artisan migrate:status

# Verificar que la aplicación funciona
curl -I https://diner.sattlink.com

# Revisar logs de errores
tail -f storage/logs/laravel.log
```

---

## Mantenimiento

### Backup Automático de Base de Datos

Crear un script de backup:

```bash
# Crear el archivo de script
sudo nano /usr/local/bin/backup-diner.sh
```

Contenido del script:
```bash
#!/bin/bash
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/backups/diner"
DB_USER="diner_user"
DB_PASS="tu_contraseña"
DB_NAME="diner_production"

# Crear directorio si no existe
mkdir -p $BACKUP_DIR

# Realizar backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/diner_backup_$TIMESTAMP.sql.gz

# Mantener solo los últimos 7 días de backups
find $BACKUP_DIR -name "diner_backup_*.sql.gz" -mtime +7 -delete

echo "Backup completado: diner_backup_$TIMESTAMP.sql.gz"
```

Hacer ejecutable y programar con cron:
```bash
sudo chmod +x /usr/local/bin/backup-diner.sh

# Editar crontab
crontab -e

# Agregar línea para backup diario a las 2 AM
0 2 * * * /usr/local/bin/backup-diner.sh >> /var/log/diner-backup.log 2>&1
```

### Limpieza de Caché

```bash
# Limpiar toda la caché
php artisan optimize:clear

# Limpiar caché específica
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Monitoreo de Logs

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Ver últimas 100 líneas de logs
tail -n 100 storage/logs/laravel.log

# Buscar errores específicos
grep "ERROR" storage/logs/laravel.log
```

### Permisos de Archivos

Si hay problemas con permisos:

```bash
cd /var/www/diner.sattlink.com

# Establecer propietario correcto
sudo chown -R www-data:www-data .

# Establecer permisos de directorios
sudo find . -type d -exec chmod 755 {} \;

# Establecer permisos de archivos
sudo find . -type f -exec chmod 644 {} \;

# Permisos especiales para storage y bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## Notas Importantes

1. **Siempre hacer backup** antes de ejecutar migraciones en producción
2. **Revisar el estado de las migraciones** con `php artisan migrate:status` antes de actualizar
3. **Verificar las columnas de la base de datos** antes de ejecutar migraciones que modifican el esquema
4. **Mantener el modo de mantenimiento activo** durante actualizaciones importantes
5. **Probar las migraciones** primero en un ambiente de staging si es posible
6. **Documentar cualquier cambio manual** realizado en la base de datos

---

## Contacto y Soporte

Para problemas o preguntas sobre el despliegue:
- **Repositorio**: https://github.com/lrmaldo/Diner
- **Documentación adicional**: Ver archivos en `/docs` del repositorio

---

**Última actualización**: 8 de noviembre de 2025
**Versión de Laravel**: 12.x
**Versión de PHP**: 8.3+

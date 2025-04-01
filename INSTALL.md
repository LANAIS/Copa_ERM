# Guía de Instalación de Copa Robótica 2025

Este documento explica cómo instalar y configurar el sistema de Copa Robótica 2025 en un servidor.

## Requisitos del Sistema

- PHP 8.2 o superior
- Extensiones de PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- Base de datos: MySQL 5.7+, PostgreSQL 10+ o SQLite 3
- Servidor web: Apache/Nginx
- Composer (gestor de dependencias de PHP)

## Proceso de Instalación

### 1. Preparación del Servidor

1. Asegúrate de que tu servidor cumple con los requisitos mínimos mencionados arriba.
2. Configura el servidor web para que apunte al directorio `public/` de la aplicación.

### 2. Instalación de Archivos

Existen dos formas de instalar el sistema:

#### Opción 1: Instalación desde el Repositorio (Recomendado para Desarrollo)

1. Clona el repositorio:
   ```bash
   git clone https://github.com/tu-usuario/copa-robotica-2025.git
   cd copa-robotica-2025
   ```

2. Instala las dependencias:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   ```

3. Copia el archivo de configuración:
   ```bash
   cp .env.example .env
   ```

4. Genera una clave de aplicación:
   ```bash
   php artisan key:generate
   ```

5. Configura la base de datos en el archivo `.env`.

6. Ejecuta las migraciones:
   ```bash
   php artisan migrate --seed
   ```

7. Crea un usuario administrador:
   ```bash
   php artisan app:create-admin tu-email@ejemplo.com tu-contraseña
   ```

#### Opción 2: Instalación mediante el Asistente Web (Recomendado para Producción)

1. Sube todos los archivos de la aplicación a tu servidor web.
   
2. Asegúrate de que los siguientes directorios y archivos tienen permisos de escritura:
   - `.env`
   - `storage/`
   - `bootstrap/cache/`
   
3. Accede a tu sitio web a través del navegador. Serás redirigido automáticamente al asistente de instalación.

4. Sigue los pasos del asistente:
   - Verificación de requisitos del sistema
   - Configuración de la base de datos
   - Configuración de la aplicación
   - Creación de la cuenta de administrador

5. Una vez completada la instalación, podrás acceder al panel de administración con las credenciales que proporcionaste.

## Configuración Adicional

### Tareas Programadas (Cron)

Para algunas características como correos programados, es necesario configurar una tarea cron:

```bash
* * * * * cd /ruta/a/tu/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

### Configuración de Correo Electrónico

Edita tu archivo `.env` para configurar el servidor de correo:

```
MAIL_MAILER=smtp
MAIL_HOST=tu-servidor-smtp.com
MAIL_PORT=587
MAIL_USERNAME=tu-usuario
MAIL_PASSWORD=tu-contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@tu-dominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Solución de Problemas

### Permisos de Directorios

Si encuentras errores de permisos, asegúrate de que los siguientes directorios tengan permisos correctos:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Caché de Configuración

Si realizas cambios en la configuración y no se reflejan, limpia la caché:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Actualización

Para actualizar el sistema a una versión más reciente:

1. Realiza una copia de seguridad de tus datos y archivos.
2. Reemplaza los archivos de la aplicación con los nuevos.
3. Ejecuta:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate
   php artisan view:clear
   php artisan config:clear
   php artisan cache:clear
   ```

## Soporte

Si necesitas ayuda, contacta con el soporte técnico en soporte@coparobotica.com 
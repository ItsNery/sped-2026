# Guía de Despliegue: Laravel en Debian

Para subir tu proyecto `sped` a un servidor Debian, sigue estas recomendaciones sobre qué archivos incluir y cuáles omitir para evitar errores de permisos, seguridad o peso innecesario.

## 📂 Qué carpetas y archivos SUBIR

| Carpeta / Archivo | Descripción |
| :--- | :--- |
| `app/` | Toda la lógica del sistema. |
| `bootstrap/` | (Excepto la carpeta `cache/`, aunque subirla vacía está bien). |
| [config/](file:///c:/laragon/www/sped/app/Providers/JetstreamServiceProvider.php#33-49) | Configuración del framework. |
| `database/` | Migraciones y seeders. |
| `lang/` / `resources/lang/` | Traducciones. |
| `public/` | El punto de entrada web (index.php, assets compilados, imágenes). |
| `resources/` | Vistas (Blade), CSS y JS originales. |
| `routes/` | Definición de todas las rutas del sistema. |
| `artisan` | Herramienta de comandos de Laravel. |
| `composer.json` | Lista de dependencias de PHP. |
| `package.json` | Lista de dependencias de Node.js (Vite). |
| `vite.config.js` | Configuración de compilación. |

---

## 🚫 Qué carpetas y archivos NO SUBIR

| Carpeta / Archivo | Razón |
| :--- | :--- |
| `node_modules/` | No funcionará si se copia de Windows a Linux. Se genera con `npm install`. |
| `vendor/` | Se genera en el servidor con `composer install`. |
| `storage/` | **IMPORTANTE**: No subas los archivos dentro de `logs` o `framework/sessions`. Sube la carpeta vacía pero asegúrate de que exista y tenga permisos de escritura. |
| `.env` | **NUNCA** subas tu archivo local. Crea uno nuevo en el servidor y configura la base de datos de producción. |
| `.git/` | Innecesario si vas a subir por FTP/SFTP. |
| `.vscode/` / `.idea/` | Configuraciones locales de tu editor. |

---

## 🛠️ Pasos críticos en el servidor Debian

Una vez que los archivos estén arriba, ejecuta estos comandos en la terminal del servidor:

### 1. Instalar dependencias
```bash
composer install --no-dev --optimize-autoloader
npm install && npm run build
```

### 2. Permisos de carpetas (Vital en Linux)
El servidor web (generalmente `www-data`) debe poder escribir en estas carpetas:
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 3. Configurar entorno
```bash
cp .env.example .env
php artisan key:generate
# Edita el .env con tus credenciales de BD
nano .env
```

### 4. Limpiar caché configurada
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Base de datos
```bash
php artisan migrate --force
```

> [!IMPORTANT]
> Aseguráte de que el `DocumentRoot` de tu servidor Apache/Nginx apunte a la carpeta `/public` del proyecto, no a la raíz.

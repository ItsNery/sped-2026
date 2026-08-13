# Browsershot en Producción

Procedimiento para preparar la generación de fichas PDF en la rama `main`, cuyo proyecto se encuentra en:

```text
/var/www/html/sped
```

## Preparación única del servidor

### 1. Confirmar Node y npm

Ejecutar por SSH:

```bash
cd /var/www/html/sped

command -v node
node -v
command -v npm
npm -v
```

Se requiere Node `22` o superior. En el servidor beta la instalación funcional está en `/home/linuxbrew/.linuxbrew/bin`; producción puede tener otra ruta.

### 2. Instalar dependencias Node del proyecto

`node_modules` no se versiona en Git. Instalarlo directamente en el servidor:

```bash
cd /var/www/html/sped

/RUTA/REAL/npm ci --omit=dev --legacy-peer-deps --no-audit --no-fund
```

Sustituir `/RUTA/REAL/npm` por la ruta obtenida con `command -v npm`. Por ejemplo:

```bash
/home/linuxbrew/.linuxbrew/bin/npm ci --omit=dev --legacy-peer-deps --no-audit --no-fund
```

Confirmar que Puppeteer quedó instalado:

```bash
test -f node_modules/puppeteer/package.json
```

### 3. Instalar Chrome Headless Shell

Usar la misma instalación de npm:

```bash
cd /var/www/html/sped
mkdir -p storage/app/puppeteer
export PUPPETEER_CACHE_DIR=/var/www/html/sped/storage/app/puppeteer

/RUTA/REAL/npx puppeteer browsers install chrome-headless-shell
```

Verificar el ejecutable:

```bash
find storage/app/puppeteer -type f -name chrome-headless-shell -print
```

### 4. Instalar librerías del sistema

En Debian/Ubuntu:

```bash
sudo apt-get update
sudo apt-get install -y \
  libnspr4 \
  libnss3 \
  libatk1.0-0 \
  libatk-bridge2.0-0 \
  libcups2 \
  libdrm2 \
  libxkbcommon0 \
  libxcomposite1 \
  libxdamage1 \
  libxfixes3 \
  libxrandr2 \
  libgbm1 \
  libasound2 \
  libpango-1.0-0 \
  libcairo2 \
  libatspi2.0-0 \
  fonts-liberation
```

Comprobar que no falten bibliotecas:

```bash
CHROME=$(find storage/app/puppeteer -type f -name chrome-headless-shell -print -quit)
ldd "$CHROME" | grep "not found" || true
```

La salida debe quedar vacía.

### 5. Configurar el binario de Node

En `.env`, agregar la ruta absoluta devuelta por `command -v node`:

```dotenv
BROWSERSHOT_NODE_BINARY=/RUTA/REAL/node
```

Ejemplo:

```dotenv
BROWSERSHOT_NODE_BINARY=/home/linuxbrew/.linuxbrew/bin/node
```

Actualizar permisos y caché de Laravel:

```bash
sudo chown -R user_planeacion:www-data storage/app/puppeteer
sudo chmod -R 775 storage/app/puppeteer

php artisan config:clear
php artisan config:cache
```

## Después de cada deploy

El workflow conserva `node_modules` porque está fuera de Git, pero valida que Puppeteer exista. Después de un deploy revisar:

```bash
cd /var/www/html/sped
test -f node_modules/puppeteer/package.json
grep '^BROWSERSHOT_NODE_BINARY=' .env
php artisan config:show browsershot
```

Si cambian `package.json` o `package-lock.json`, repetir la instalación de dependencias Node:

```bash
/RUTA/REAL/npm ci --omit=dev --legacy-peer-deps --no-audit --no-fund
export PUPPETEER_CACHE_DIR=/var/www/html/sped/storage/app/puppeteer
/RUTA/REAL/npx puppeteer browsers install chrome-headless-shell
```

## Prueba de generación

1. Abrir una ficha técnica pública.
2. Descargar la ficha PDF.
3. Confirmar que el archivo se descarga y que la gráfica aparece.
4. Si falla el navegador, revisar el log de Laravel:

```bash
tail -n 100 storage/logs/laravel.log
```

Errores frecuentes:

| Error | Corrección |
| --- | --- |
| `Cannot find module 'puppeteer'` | Ejecutar `npm ci` en `/var/www/html/sped`. |
| `Could not find chrome-headless-shell` | Instalar `chrome-headless-shell` con la caché de `storage/app/puppeteer`. |
| `libnspr4.so: cannot open shared object file` | Instalar las librerías del sistema indicadas arriba. |
| Se usa Node 18 | Revisar `BROWSERSHOT_NODE_BINARY` y ejecutar `php artisan config:cache`. |
| `node_modules/puppeteer/package.json` no existe | Repetir la instalación de dependencias Node. |

## Notas

- No versionar `node_modules`.
- No agregar credenciales al repositorio ni a esta guía.
- El aviso `Module "gd" is already loaded` es independiente de Browsershot y no impide la generación PDF.
- El aviso sobre `public/storage` existente tampoco es un error de Browsershot.

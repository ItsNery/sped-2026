# Release Candidate: Beta hacia Main sin Deploy Automático

Procedimiento recomendado para integrar `beta` en una versión candidata, probarla en el servidor sin activar todavía el deploy de `main` y realizar un cambio controlado hacia producción.

## Objetivo

Separar estas tres acciones:

1. Integrar el código de `beta`.
2. Probar la versión integrada en una carpeta aislada.
3. Publicarla en producción solo cuando todos los problemas estén resueltos.

El proyecto productivo actual se encuentra en:

```text
/var/www/html/sped
```

La carpeta candidata será:

```text
/var/www/html/sped-candidate
```

## Reglas importantes

- No hacer merge directamente a `main` al inicio.
- No hacer push a `main` hasta terminar las pruebas.
- No ejecutar `git reset --hard` en la carpeta productiva para preparar la candidata.
- No restaurar la base de datos de beta sobre producción.
- No sobrescribir el `.env` productivo.
- No copiar `node_modules` desde Windows.
- No copiar `storage` completo desde beta sobre producción.
- No borrar la carpeta productiva hasta confirmar que la candidata funciona.
- Mantener un rollback de código y una copia verificable de los assets productivos.

### Comprobación obligatoria de historial

Antes de intentar el merge, confirmar que las ramas comparten historial:

```bash
git merge-base origin/main origin/beta
```

Si el comando no devuelve un commit, las ramas tienen historiales no relacionados. No ejecutar inmediatamente:

```bash
git merge --allow-unrelated-histories origin/beta
```

Ese comando puede convertir prácticamente todo el repositorio en un conflicto y no representa una promoción normal. En ese caso detener el procedimiento y decidir entre:

- Reconstruir `main` desde `beta`, conservando el historial mediante una estrategia de reemplazo controlado.
- Hacer una integración manual archivo por archivo.
- Corregir primero el historial del repositorio si alguna rama fue creada desde otro origen por error.

No continuar con las fases del servidor hasta tomar esa decisión.

## Arquitectura temporal

Durante las pruebas existirán dos carpetas:

```text
/var/www/html/sped             # Producción actual, intacta
/var/www/html/sped-candidate   # Release candidate
```

La candidata debe utilizar una base de datos de prueba o una copia controlada de producción. No se debe conectar a la base productiva si existe riesgo de que la aplicación ejecute migraciones, seeders o escrituras durante las pruebas.

Si no existe una base de pruebas disponible, la candidata puede prepararse y validarse con cuidado usando una copia de la base, pero debe quedar protegida contra tráfico público y no debe usarse para operaciones destructivas.

## Fase 1: Preparar la rama candidata localmente


Desde una copia local del repositorio, nunca desde el servidor:

```bash
git fetch origin --prune
git status --short
git branch --show-current
```

No continuar si hay cambios locales desconocidos.

### 2. Crear la rama candidata

Actualizar `main` y crear una rama de release:

```bash
git switch main
git pull --ff-only origin main
git switch -c release/beta-to-main-YYYYMMDD
```

### 3. Integrar beta sin publicar en main

```bash
git merge --no-ff --no-commit origin/beta
```

Revisar el resultado:

```bash
git status
git diff --stat
git diff --name-status
git diff --check
git log --oneline origin/main..HEAD
```

Revisar manualmente en particular:

- Migraciones.
- Seeders y comandos con operaciones de datos.
- `composer.json` y `composer.lock`.
- `package.json` y `package-lock.json`.
- `.github/workflows/deploy.yml`.
- `config/browsershot.php`.
- Cambios de PDF, assets y permisos.

Si aparecen conflictos:

```bash
git add <archivos-resueltos>
git diff --check
git commit -m "Merge: release candidate beta a main"
```

Si se decide cancelar:

```bash
git merge --abort
```

## Fase 2: Validar localmente

Ejecutar antes de subir la rama candidata:

```bash
composer validate --no-check-publish
php artisan route:list
php artisan view:cache
php -l app/Http/Controllers/HomeController.php
php -l app/Http/Controllers/IndicadorMunicipalController.php
php -l app/Http/Controllers/DashboardExportController.php
git diff --check
```

Si el proyecto tiene pruebas:

```bash
php artisan test
```

Confirmar el alcance final:

```bash
git diff --stat origin/main...HEAD
git log --oneline origin/main..HEAD
```

## Fase 3: Subir la candidata sin activar el deploy

Publicar únicamente la rama `release/...`:

```bash
git push -u origin release/beta-to-main-YYYYMMDD
```

No abrir todavía el Pull Request hacia `main` si el workflow de GitHub está configurado para desplegar automáticamente cualquier push a `main`.

La rama candidata no activa los jobs actuales porque el workflow solo tiene condiciones para:

```yaml
github.ref == 'refs/heads/beta'
github.ref == 'refs/heads/main'
```

## Fase 4: Preparar la carpeta candidata en el servidor

### 1. Proteger producción antes de tocar el servidor

Por SSH:

```bash
cd /var/www/html/sped
git status --short
git branch --show-current
git rev-parse HEAD
test -f .env
```

Si el checkout productivo tiene cambios locales, no ejecutar comandos destructivos. Documentar el estado y resolverlo antes de continuar.

### 2. Crear la carpeta candidata

Desde `/var/www/html`:

```bash
cd /var/www/html
sudo mkdir -p sped-candidate
sudo chown user_planeacion:www-data sped-candidate
```

Clonar la rama candidata como usuario de despliegue:

```bash
git clone --branch release/beta-to-main-YYYYMMDD --single-branch \
  https://github.com/ItsNery/sped-2026.git sped-candidate
```

Si la carpeta ya existe y se va a reutilizar:

```bash
cd /var/www/html/sped-candidate
git fetch origin
git checkout release/beta-to-main-YYYYMMDD
git pull --ff-only origin release/beta-to-main-YYYYMMDD
```

No usar `git reset --hard` si existen archivos locales que todavía no estén respaldados.

## Fase 5: Copiar configuración y assets de forma segura

### 1. No copiar el `.env` de beta

La candidata debe usar una configuración propia. Crear el `.env` a partir del productivo solo si se va a apuntar a una base de pruebas:

```bash
sudo cp /var/www/html/sped/.env /var/www/html/sped-candidate/.env
sudo chown user_planeacion:www-data /var/www/html/sped-candidate/.env
sudo chmod 640 /var/www/html/sped-candidate/.env
```

Después editar el `.env` candidato y confirmar:

- `APP_ENV` y `APP_DEBUG` apropiados para pruebas.
- `APP_URL` de la candidata, si existe un dominio o subdominio temporal.
- Base de datos de pruebas, no producción.
- `BROWSERSHOT_NODE_BINARY` con la ruta real de Node del servidor.
- No exponer secretos en logs ni capturas.

Si no hay una URL independiente, restringir la candidata por configuración del servidor web, IP permitida, autenticación o firewall. No dejarla públicamente accesible sin protección.

### 2. Copiar `storage` con cuidado

No copiar el `storage` completo de beta sobre producción. Para la candidata, se puede copiar una base inicial de almacenamiento:

```bash
sudo rsync -a --exclude='logs/' --exclude='framework/sessions/' \
  /var/www/html/sped/storage/ \
  /var/www/html/sped-candidate/storage/
```

Crear las carpetas necesarias:

```bash
cd /var/www/html/sped-candidate
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/app/puppeteer
```

### 3. Conservar assets ignorados por Git

El repositorio ignora varios assets públicos. La candidata necesita sus propias copias:

```bash
sudo rsync -a /var/www/html/sped/public/img/ /var/www/html/sped-candidate/public/img/
sudo rsync -a /var/www/html/sped/public/fontAwesome/ /var/www/html/sped-candidate/public/fontAwesome/
sudo rsync -a /var/www/html/sped/public/webfonts/ /var/www/html/sped-candidate/public/webfonts/
sudo rsync -a /var/www/html/sped/public/docs/ /var/www/html/sped-candidate/public/docs/ 2>/dev/null || true
sudo rsync -a /var/www/html/sped/public/videos/ /var/www/html/sped-candidate/public/videos/ 2>/dev/null || true
```

No eliminar los assets de `/var/www/html/sped` durante este proceso.

## Fase 6: Instalar dependencias en la candidata

### 1. PHP

```bash
cd /var/www/html/sped-candidate
composer install --no-dev --optimize-autoloader
```

### 2. Node y Puppeteer

Usar la instalación funcional del servidor, por ejemplo:

```bash
command -v node
node -v
command -v npm
npm -v
```

Se requiere Node `22` o superior. Instalar las dependencias locales:

```bash
/RUTA/REAL/npm ci --omit=dev --legacy-peer-deps --no-audit --no-fund
test -f node_modules/puppeteer/package.json
```

Instalar Chrome Headless Shell en la candidata:

```bash
export PUPPETEER_CACHE_DIR=/var/www/html/sped-candidate/storage/app/puppeteer
/RUTA/REAL/npx puppeteer browsers install chrome-headless-shell
```

Confirmar librerías del sistema:

```bash
CHROME=$(find storage/app/puppeteer -type f -name chrome-headless-shell -print -quit)
ldd "$CHROME" | grep "not found" || true
```

La salida debe quedar vacía.

## Fase 7: Preparar Laravel en la candidata

```bash
cd /var/www/html/sped-candidate

sudo chown -R user_planeacion:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R user_planeacion:www-data storage/app/puppeteer
sudo chmod -R 775 storage/app/puppeteer

php artisan storage:link
php artisan migrate:status
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

No ejecutar `php artisan migrate --force` en la candidata hasta confirmar que su `.env` apunta a una base de pruebas. Si la candidata usa una copia de producción, las migraciones deben aprobarse y ejecutarse con respaldo propio.

## Fase 8: Probar sin afectar producción

Probar en este orden:

1. La aplicación carga sin errores.
2. El login funciona.
3. El buscador de indicadores funciona.
4. Las fichas públicas cargan.
5. La gráfica histórica se muestra.
6. La descarga PDF funciona.
7. El PDF conserva la gráfica SVG y la pleca.
8. Los formularios o procesos de escritura están deshabilitados o apuntan a la base de pruebas.
9. Los archivos se guardan en la candidata y no en producción.

Revisar logs:

```bash
tail -n 100 storage/logs/laravel.log
```

Probar Browsershot especialmente con:

- Un indicador con muchos años.
- Un indicador con línea base y meta en el mismo año.
- Un indicador con pocos datos.
- Un indicador sin datos suficientes.

## Fase 9: Respaldar producción para el cambio final

No realizar el cutover sin un respaldo verificable.

```bash
cd /var/www/html/sped

DB_HOST="$(sed -n 's/^DB_HOST=//p' .env | head -n 1 | tr -d '"' | tr -d "'")"
DB_PORT="$(sed -n 's/^DB_PORT=//p' .env | head -n 1 | tr -d '"' | tr -d "'")"
DB_DATABASE="$(sed -n 's/^DB_DATABASE=//p' .env | head -n 1 | tr -d '"' | tr -d "'")"
DB_USERNAME="$(sed -n 's/^DB_USERNAME=//p' .env | head -n 1 | tr -d '"' | tr -d "'")"
DB_PASSWORD="$(sed -n 's/^DB_PASSWORD=//p' .env | head -n 1 | tr -d '"' | tr -d "'")"

DUMP_BIN="$(command -v mariadb-dump || command -v mysqldump)"
BACKUP_DIR=/var/backups/sped
BACKUP_NAME="sped-before-cutover-$(date +%Y%m%d-%H%M%S).sql.gz"

sudo install -d -m 750 -o user_planeacion -g www-data "$BACKUP_DIR"
MYSQL_PWD="$DB_PASSWORD" "$DUMP_BIN" \
  --host="${DB_HOST:-127.0.0.1}" \
  --port="${DB_PORT:-3306}" \
  --user="$DB_USERNAME" \
  --single-transaction \
  --routines --triggers --events --hex-blob \
  "$DB_DATABASE" | gzip > "/tmp/$BACKUP_NAME"

test -s "/tmp/$BACKUP_NAME"
sha256sum "/tmp/$BACKUP_NAME" > "/tmp/$BACKUP_NAME.sha256"
sudo mv "/tmp/$BACKUP_NAME" "$BACKUP_DIR/$BACKUP_NAME"
sudo mv "/tmp/$BACKUP_NAME.sha256" "$BACKUP_DIR/$BACKUP_NAME.sha256"
sudo chown user_planeacion:www-data "$BACKUP_DIR/$BACKUP_NAME" "$BACKUP_DIR/$BACKUP_NAME.sha256"
sudo chmod 640 "$BACKUP_DIR/$BACKUP_NAME" "$BACKUP_DIR/$BACKUP_NAME.sha256"
sudo sh -c "cd '$BACKUP_DIR' && sha256sum -c '$BACKUP_NAME.sha256'"
```

Respaldar también configuración y assets productivos:

```bash
sudo install -d -m 750 /var/backups/sped/assets
sudo cp /var/www/html/sped/.env /var/backups/sped/.env-before-cutover
sudo tar -czf "/var/backups/sped/assets/sped-assets-before-cutover-$(date +%Y%m%d-%H%M%S).tar.gz" \
  -C /var/www/html/sped public/img public/fontAwesome public/webfonts public/docs public/videos storage/app/puppeteer 2>/dev/null || true
sudo chmod 600 /var/backups/sped/.env-before-cutover
```

## Fase 10: Cutover controlado

El cutover debe hacerse en una ventana de mantenimiento o con el tráfico detenido.

### Opción recomendada: conservar la carpeta anterior

No borrar producción. Renombrarla con fecha y activar la candidata:

```bash
cd /var/www/html

STAMP=$(date +%Y%m%d-%H%M%S)
sudo mv sped "sped-previous-$STAMP"
sudo mv sped-candidate sped
```

Después de renombrar, revisar que producción conserve:

- `.env` productivo.
- `storage/` productivo.
- `public/storage` apuntando al storage correcto.
- Assets públicos ignorados por Git.
- `node_modules` y Puppeteer.

Si se preparó la candidata con una base de pruebas, reemplazar su `.env` por el `.env` productivo respaldado **solo después de revisar el código y antes de recibir tráfico**:

```bash
sudo cp "/var/backups/sped/.env-before-cutover" /var/www/html/sped/.env
sudo chown user_planeacion:www-data /var/www/html/sped/.env
sudo chmod 640 /var/www/html/sped/.env
```

Si la candidata no contiene el storage productivo, conservarlo desde la carpeta anterior:

```bash
sudo rsync -a "$(ls -td /var/www/html/sped-previous-* | head -n 1)/storage/" \
  /var/www/html/sped/storage/
```

Copiar o verificar assets públicos si la candidata no los tenía:

```bash
PREVIOUS=$(ls -dt /var/www/html/sped-previous-* | head -n 1)
sudo rsync -a "$PREVIOUS/public/img/" /var/www/html/sped/public/img/
sudo rsync -a "$PREVIOUS/public/fontAwesome/" /var/www/html/sped/public/fontAwesome/
sudo rsync -a "$PREVIOUS/public/webfonts/" /var/www/html/sped/public/webfonts/
sudo rsync -a "$PREVIOUS/public/docs/" /var/www/html/sped/public/docs/ 2>/dev/null || true
sudo rsync -a "$PREVIOUS/public/videos/" /var/www/html/sped/public/videos/ 2>/dev/null || true
```

Corregir permisos y cachés:

```bash
cd /var/www/html/sped
sudo chown -R user_planeacion:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Ejecutar migraciones solo si fueron revisadas y la base productiva ya tiene respaldo:

```bash
php artisan migrate --force
```

## Fase 11: Validar después del cutover

```bash
cd /var/www/html/sped
git branch --show-current
git rev-parse HEAD
php artisan migrate:status
test -f .env
test -f node_modules/puppeteer/package.json
```

Validar desde navegador:

- Inicio de sesión.
- Buscador.
- Ficha pública.
- Descarga PDF.
- Gráfica histórica.
- Indicadores y conteos esperados.
- API, si forma parte de la release.

Revisar:

```bash
tail -n 100 storage/logs/laravel.log
```

No borrar `sped-previous-*` hasta completar la ventana de observación y confirmar que el rollback ya no es necesario.

## Fase 12: Rollback

### Rollback inmediato de código

Si la nueva versión falla y la base no requiere revertir migraciones:

```bash
cd /var/www/html
sudo mv sped "sped-failed-$(date +%Y%m%d-%H%M%S)"
sudo mv "$(ls -dt /var/www/html/sped-previous-* | head -n 1)" sped
```

Después:

```bash
cd /var/www/html/sped
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Rollback de base de datos

No revertir migraciones automáticamente. Si una migración o importación modificó datos:

1. Detener el tráfico o activar mantenimiento.
2. Identificar exactamente las tablas afectadas.
3. Confirmar el respaldo y su checksum.
4. Obtener autorización para restaurar.
5. Restaurar el respaldo productivo correspondiente.
6. Verificar conteos y relaciones.
7. Documentar la operación.

## Cuándo promover a `main`

Solo después de que:

- La rama candidata esté probada.
- El PDF funcione en el servidor.
- Node, Puppeteer y Chrome Headless estén resueltos.
- Los assets estén completos.
- Las migraciones estén revisadas.
- La base candidata haya sido validada.
- Exista un respaldo productivo verificable.
- El rollback haya sido entendido y sea viable.

Entonces abrir el Pull Request:

```text
release/beta-to-main-YYYYMMDD -> main
```

Al aceptar el Pull Request, el push a `main` activará el workflow de producción. No hacer un segundo deploy manual desde `/var/www/html/sped`.

## Checklist

- [ ] Se creó una rama `release/...` desde `main`.
- [ ] Se integró `beta` sin tocar `main`.
- [ ] Se revisó el diff completo.
- [ ] Se probaron migraciones y comandos de datos.
- [ ] Se subió solo la rama candidata.
- [ ] Se creó `/var/www/html/sped-candidate`.
- [ ] La candidata no usa la base productiva para pruebas destructivas.
- [ ] Se copió el `.env` sin sobrescribir producción.
- [ ] Se conservaron assets ignorados por Git.
- [ ] Se instaló Node/Puppeteer en Linux.
- [ ] Se instaló Chrome Headless Shell.
- [ ] Se verificaron librerías con `ldd`.
- [ ] Se probó la descarga PDF.
- [ ] Se respaldó la base productiva y se verificó el checksum.
- [ ] Se respaldó `.env`, assets y Puppeteer.
- [ ] Se renombró la carpeta anterior, no se eliminó.
- [ ] Se activó la candidata en una ventana controlada.
- [ ] Se conservaron los datos productivos.
- [ ] Se probaron login, fichas, PDF y API.
- [ ] Se mantuvo disponible `sped-previous-*` para rollback.
- [ ] Solo después de todo lo anterior se hizo merge a `main`.

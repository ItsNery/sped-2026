# Promoción segura de Beta a Main

Guía para integrar el código de `beta` en `main` y desplegarlo en producción, cuyo checkout se encuentra en:

```text
/var/www/html/sped
```

## Principio de seguridad

El merge de Git mueve **código**, migraciones y archivos versionados. No copia ni reemplaza la base de datos de beta.

Para no perder información de producción:

- No restaurar el dump completo de beta sobre producción.
- No ejecutar `migrate:fresh`, `db:wipe` ni comandos destructivos en producción.
- No hacer merge ni cambiar ramas manualmente dentro de `/var/www/html/sped`.
- No ejecutar `git reset --hard` manualmente en `/var/www/html/sped`.
- No sobrescribir el `.env` productivo.
- Promover datos mediante migraciones, seeders o importadores idempotentes y revisados.

El workflow de `main` realiza un respaldo de producción antes de actualizar el checkout y después ejecuta `git reset --hard origin/main` dentro de `/var/www/html/sped`. Ese `reset` pertenece al proceso automatizado; no debe ejecutarse manualmente fuera del workflow.

## 1. Preparar el repositorio local

Realizar el merge desde una copia local de trabajo o mediante un Pull Request. No hacerlo desde el servidor.

```bash
git fetch origin --prune
git status --short
git branch --show-current
```

Si existen cambios locales no relacionados, detenerse y guardarlos o limpiarlos antes de continuar. No descartarlos con comandos destructivos.

Actualizar `main` sin crear una combinación implícita:

```bash
git switch main
git pull --ff-only origin main
```

Crear una rama de promoción:

```bash
git switch -c promote/beta-to-main-YYYYMMDD
```

## 2. Integrar Beta sin publicar todavía

Preparar el merge sin crear el commit, para revisar todo el resultado:

```bash
git merge --no-ff --no-commit origin/beta
```

Revisar el alcance:

```bash
git diff --stat
git diff --name-status
git diff --check
```

Revisar especialmente:

- Migraciones nuevas o modificadas.
- Seeders y comandos que puedan actualizar o eliminar datos.
- Cambios en `composer.json`, `package.json` y sus lockfiles.
- `.github/workflows/deploy.yml`.
- Configuración de Browsershot y la generación de PDF.
- Cambios que afecten rutas, permisos, archivos públicos o almacenamiento.

Si hay conflictos, resolverlos archivo por archivo. No usar `git checkout --ours` o `git checkout --theirs` sin revisar el contenido y el impacto en producción.

Después de resolverlos:

```bash
git add <archivos-resueltos>
git diff --check
git commit -m "Merge: promoción de beta a main"
```

Si el merge no debe continuar, cancelarlo antes del commit:

```bash
git merge --abort
```

## 3. Validar el código integrado

Ejecutar las validaciones disponibles en el entorno local:

```bash
composer validate --no-check-publish
php artisan route:list
php artisan view:cache
git diff origin/main...HEAD --check
```

Validar la sintaxis de los controladores modificados:

```bash
php -l app/Http/Controllers/HomeController.php
php -l app/Http/Controllers/IndicadorMunicipalController.php
php -l app/Http/Controllers/DashboardExportController.php
```

Si hay pruebas automatizadas:

```bash
php artisan test
```

Revisar también el historial que se va a publicar:

```bash
git log --oneline origin/main..HEAD
git diff --stat origin/main...HEAD
```

## 4. Publicar mediante Pull Request

Subir la rama de promoción:

```bash
git push -u origin promote/beta-to-main-YYYYMMDD
```

Crear un Pull Request hacia `main` y verificar:

- Que el origen sea la rama de promoción correcta.
- Que el destino sea `main`.
- Que todos los commits de beta esperados estén incluidos.
- Que no aparezcan cambios de datos o archivos locales no intencionados.
- Que las comprobaciones de CI pasen.

Preferir el merge del Pull Request en GitHub. No hacer push directo a `main` si el repositorio requiere revisión.

## 5. Preparar producción antes de activar el código

El workflow realiza un dump automático, pero antes de una promoción importante conviene verificar manualmente el estado de producción por SSH:

```bash
cd /var/www/html/sped

git status --short
git branch --show-current
git rev-parse HEAD
test -f .env
```

Confirmar que el checkout está en `main` y que `.env` contiene la configuración productiva. No reemplazar `.env` con el de beta.

Guardar una copia protegida del `.env`, sin subirla al repositorio:

```bash
sudo install -d -m 750 /var/backups/sped
sudo cp .env "/var/backups/sped/.env-$(date +%Y%m%d-%H%M%S)"
sudo chmod 600 /var/backups/sped/.env-*
```

El archivo contiene secretos. No mostrarlo ni incluirlo en tickets, commits o logs.

## 6. Promover el código

Después de aceptar el Pull Request, el push a `main` dispara el workflow de producción.

El workflow debe realizar, en este orden:

1. Respaldo de la base productiva.
2. Verificación del checksum del respaldo.
3. Actualización del checkout `/var/www/html/sped` a `origin/main`.
4. Instalación de dependencias PHP.
5. Verificación de Puppeteer existente.
6. Ejecución de migraciones con `php artisan migrate --force`.
7. Limpieza y reconstrucción de cachés.

No interrumpir el proceso durante el respaldo ni durante las migraciones salvo que exista un riesgo inmediato.

## 7. Promover información de beta sin perder producción

La base de beta puede contener una copia de producción más ajustes adicionales. Eso no significa que sea seguro restaurarla completa sobre producción: puede contener IDs, cambios o eliminaciones que no deben reemplazar los datos productivos.

Antes de trasladar información:

1. Identificar exactamente qué registros nuevos o modificados deben llegar a producción.
2. Crear o utilizar una migración, seeder o importador idempotente.
3. Ejecutar primero un `dry-run` si el comando lo permite.
4. Respaldar las tablas afectadas en producción.
5. Ejecutar la operación aprobada.
6. Validar conteos, relaciones, fichas y API.

Para el alcance de indicadores, utilizar como referencia la guía existente:

```text
documentation/migracion-beta-produccion.md
```

No transportar relaciones usando IDs de beta. Las relaciones deben resolverse en producción por claves funcionales, nombres normalizados o el mecanismo definido por el importador.

## 8. Validaciones posteriores

En producción:

```bash
cd /var/www/html/sped

php artisan migrate:status
php artisan config:show app
php artisan route:list --path=ficha-tecnica
test -f node_modules/puppeteer/package.json
```

Validar desde navegador:

- Inicio de sesión.
- Buscador de indicadores.
- Una ficha técnica del PED.
- Descarga de la ficha PDF.
- Una ficha con gráfica histórica.
- API pública, si está incluida en la promoción.

Revisar el log si alguna prueba falla:

```bash
tail -n 100 storage/logs/laravel.log
```

## 9. Rollback

### Rollback de código

No hacer `git reset` manual en `/var/www/html/sped` mientras el workflow esté ejecutándose. Para revertir código:

1. Identificar el commit estable anterior.
2. Crear un commit revert en una rama local.
3. Abrir un Pull Request hacia `main`.
4. Dejar que el workflow despliegue el revert.

### Rollback de datos

Restaurar un respaldo de producción solo con aprobación y un plan específico. No restaurar automáticamente el dump de beta.

Antes de restaurar:

- Confirmar la base y tablas afectadas.
- Confirmar que el respaldo corresponde a producción.
- Detener o poner en mantenimiento la aplicación si el procedimiento lo requiere.
- Verificar el checksum.
- Documentar el motivo y el alcance.

## Checklist final

- [ ] Se revisaron los commits de `beta` que llegarán a `main`.
- [ ] El merge se hizo fuera de `/var/www/html/sped`.
- [ ] No se restauró la base de beta sobre producción.
- [ ] Se respaldó la base productiva.
- [ ] Se verificó que `.env` sigue siendo el de producción.
- [ ] Se revisaron migraciones y operaciones de datos.
- [ ] Pasaron las validaciones de código.
- [ ] El workflow de `main` terminó correctamente.
- [ ] Se probó una ficha web y una descarga PDF.
- [ ] Se validaron los conteos y relaciones de indicadores.

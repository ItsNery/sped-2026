# Progresion de Base Productiva a Candidata

Procedimiento para construir y probar el estado final esperado sin modificar la base ni el directorio de produccion. La candidata usa `/var/www/html/sped-candidate` y una copia aislada llamada `bd_sped_candidate`.

## Reglas

- No ejecutar migraciones, seeders, importadores ni comandos de reconciliacion sobre la base productiva durante esta prueba.
- No usar `migrate:fresh`, `db:wipe` ni `db:seed` sin especificar `--class`.
- Los archivos de Excel no se importan directamente con MySQL.
- Conservar el dump de produccion, los reportes JSON y las salidas de cada paso.
- Antes de cualquier comando que escriba, confirmar que la candidata usa `bd_sped_candidate`.

## 1. Preparar Codigo y Copia de Produccion

Actualizar el checkout candidato a la rama aprobada y verificar que no tenga cambios locales desconocidos:

```bash
cd /var/www/html/sped-candidate
git status --short
git pull --ff-only origin release/beta-content-on-main
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Crear una copia consistente de produccion. Sustituir los valores entre `<...>` y nunca imprimir ni guardar la contraseña en el historial del shell:

```bash
mysqldump --single-transaction --routines --triggers -u <usuario> -p <bd_produccion> > /var/backups/sped/sped-produccion-<fecha>.sql
mysql -u <usuario> -p -e "CREATE DATABASE bd_sped_candidate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u <usuario> -p bd_sped_candidate < /var/backups/sped/sped-produccion-<fecha>.sql
```

El usuario de MySQL de la candidata debe tener permisos solo sobre `bd_sped_candidate`.

## 2. Configurar y Verificar la Candidata

En `/var/www/html/sped-candidate/.env` usar una configuracion aislada:

```dotenv
APP_ENV=staging
APP_DEBUG=false
APP_URL=http://127.0.0.1:8081
DB_DATABASE=bd_sped_candidate
```

Limpiar configuracion cacheada y confirmar el destino antes de continuar:

```bash
php artisan optimize:clear
php artisan about
php artisan migrate:status
```

Detenerse si el nombre de la base no es `bd_sped_candidate`.

## 3. Aplicar Esquema y Catalogos PED 3

Las migraciones deben aplicarse antes de los seeders porque crean las columnas, indices y la tabla pivote requeridos por las cargas posteriores:

```bash
php artisan migrate --force
php artisan migrate:status
```

Confirmar que el plan activo es el PED 3 y que existe en la copia:

```bash
php artisan tinker --execute='dump(config("sped.active_plan_id"), App\Models\CatPlanEstatalDesarrollo::find(config("sped.active_plan_id", 3))?->nombre);'
```

Crear o actualizar los ejes y el catalogo institucional:

```bash
php artisan db:seed --class=EjesSeeder --force
php artisan db:seed --class=Ped3InstitutionalCatalogSeeder --force
```

`EjesSeeder` tambien reasigna indicadores que estaban ligados directamente al plan cuando su campo `programa` coincide con un eje. `Ped3InstitutionalCatalogSeeder` solamente agrega programas institucionales PED 3 que no existan por nombre normalizado.

## 4. Conciliar Instituciones Antes de Cargar Indicadores

Los indicadores institucionales se resuelven por nombre de institucion y el importador no crea instituciones. Ejecutar el reporte de diferencias:

```bash
php artisan sped:reconcile-candidate-excel
```

El reporte queda en `storage/app/reconciliation/` y separa `indicadors` e `instituciones` en:

- Nuevos en Excel.
- Ausentes en Excel.
- Mismo ID con campos distintos.

Revisar las entradas de `instituciones` primero. El comando actual es deliberadamente de solo lectura: no aplica las altas o actualizaciones de `instituciones0210.xls`. No continuar con la carga institucional si faltan instituciones que el Excel de carga referencia; primero debe aprobarse y aplicarse una conciliacion especifica sobre la copia candidata.

## 5. Cargar Indicadores Institucionales PED 3

Usar el archivo versionado de carga institucional, normalmente `public/Indicadores nuevos para carga en el SPED.xlsx`. No usar `public/indicadors0210.xls`: es una exportacion tabular para conciliacion y no tiene los encabezados requeridos por este importador.

Primero ejecutar el dry-run:

```bash
php artisan sped:import-ped3-institutional \
  --file='public/Indicadores nuevos para carga en el SPED.xlsx'
```

Revisar el JSON en `storage/app/imports/`. Corregir programas, instituciones, ODS y valores reportados antes de escribir. Cuando no haya errores:

```bash
php artisan sped:import-ped3-institutional \
  --file='public/Indicadores nuevos para carga en el SPED.xlsx' \
  --execute
```

El importador crea o actualiza indicadores, sus valores anuales y sus relaciones ODS dentro de una transaccion.

## 6. Crear Relaciones Institucionales

Con el catalogo y los indicadores ya cargados, resolver el manifiesto de relaciones:

```bash
php artisan db:seed --class=Ped3InstitutionalRelationsSeeder --force
```

El seeder inserta solo relaciones faltantes en `programa_institucional_indicador`. Si informa programas o indicadores faltantes o ambiguos, no inserta relaciones y se debe resolver el reporte antes de reintentar.

## 7. Conciliar y Validar el Estado Final

Repetir la conciliacion tras todas las operaciones de datos:

```bash
php artisan sped:reconcile-candidate-excel
```

Revisar el JSON y validar conteos basicos:

```bash
php artisan tinker --execute='dump([
    "programas_institucionales_ped3" => App\Models\CatProgramaDerivadoInstitucional::where("plan_estatal", 3)->count(),
    "indicadores_ped3" => App\Models\Indicador::forPlan(3)->count(),
    "datos_anuales" => App\Models\DatoAnual::count(),
    "relaciones_institucionales" => DB::table("programa_institucional_indicador")->count(),
]);'
```

Probar mediante el tunel SSH: inicio de sesion, buscador, fichas, graficas historicas, PDF, assets y API. Consultar `storage/logs/laravel.log` despues de las pruebas.

## Plantilla de Carga Masiva

La opcion **Descargar plantilla** de `panel-indicadores/prueba` se ajusta al importador masivo de indicadores. Sus columnas fijas son las 23 primeras, desde `ID (Opcional)` hasta `Fecha Actualizacion`, incluyendo `Meta (Año)` y `Meta (Dato)`, y despues admite valores anuales de 2015 a 2030.

La plantilla llena estas partes del modelo:

- `indicadors`: identidad, alineacion con plan/programa, responsable, institucion, tematica, linea base, meta, fuente, liga, descripcion, periodicidad, cobertura, tendencia, formula, ODS y fecha de actualizacion.
- `datos_anuales`: un valor `valor_dato` por cada columna anual; la linea base se guarda como validada y los demas anios como pendientes de validacion.

No es una exportacion completa de `indicadors` ni de `datos_anuales`. No permite cargar `slug`, `cod_tematica`, `meta_2024`, `periodo`, marcas de tiempo, ni los campos anuales `fecha_actualizacion`, `resultados`, `evidencia` y `observaciones`. Esos valores se derivan, conservan o se gestionan mediante los formularios de la aplicacion.

## Prohibido

- Ejecutar este procedimiento sobre `/var/www/html/sped` o su base productiva durante las pruebas.
- Ejecutar `IndicadoresArchivoSeeder`; copia datos historicos y despues vacia tablas operativas.
- Ejecutar `db:seed` sin `--class`.
- Cargar `indicadors0210.xls` con `sped:import-ped3-institutional`.
- Continuar un importador que presente errores en su dry-run.

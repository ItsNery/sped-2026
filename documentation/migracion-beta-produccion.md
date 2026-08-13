# Migracion Incremental de Beta a Produccion

Esta guia evita copiar o restaurar la base local completa. Produccion es la fuente de verdad y beta se usa solamente como origen de codigo, migraciones, catalogos aprobados y archivos de importacion.

No se debe hacer merge con `main` como parte de esta preparacion.

## Regla Principal

- El codigo se despliega mediante el flujo normal de ramas.
- Las migraciones se ejecutan en produccion.
- Los datos se trasladan mediante seeders o comandos idempotentes.
- Los IDs de beta nunca se transportan como relaciones hacia produccion.
- Toda operacion destructiva tiene `dry-run`, respaldo y confirmacion separada.

## Orden Propuesto

1. Congelar el alcance de esta entrega en `beta`.
2. Revisar los cambios, sintaxis, vistas y migraciones.
3. Crear un commit de preparacion en `beta`; no mezclarlo con `main` todavia.
4. Desplegar el codigo a produccion mediante el procedimiento autorizado.
5. Verificar el `.env` productivo y el estado de la base.
6. Ejecutar migraciones de esquema con `php artisan migrate --force`.
7. Ejecutar el seeder de programas institucionales.
8. Ejecutar el dry-run de historicos PED 1 y PED 2.
9. Respaldar las tablas afectadas.
10. Ejecutar las importaciones historicas aprobadas.
11. Ejecutar por separado el dry-run de eliminacion de regionales.
12. Eliminar regionales solamente despues de confirmar el alcance exacto.
13. Validar conteos, relaciones, fichas publicas, API y dashboard.

## Seeder Institucional

Si el catalogo institucional de PED 3 aprobado contiene `79` programas, se puede crear un seeder dedicado:

```bash
php artisan db:seed --class=Ped3InstitutionalCatalogSeeder --force
```

El seeder debe cumplir estas reglas:

- Buscar por `plan_estatal` y nombre normalizado.
- Crear solamente los programas faltantes.
- No transportar los IDs de beta.
- No sobrescribir nombres, documentos, grupos o siglas que ya existan en produccion.
- Reportar registros creados, existentes y conflictos.
- Ejecutarse varias veces sin duplicar programas.

No conviene meter este seeder dentro de `DatabaseSeeder`, porque no debe ejecutarse automáticamente en todos los entornos.

Después de crear el catálogo y de ejecutar la migración `programa_institucional_indicador`, crear las relaciones con los indicadores que ya existen en producción:

```bash
php artisan db:seed --class=Ped3InstitutionalRelationsSeeder --force
```

Este seeder usa `public/Relacion nuevis derivados indicadr.ods` como manifiesto de relaciones. Sólo inserta filas faltantes en la tabla pivote; no crea ni actualiza indicadores, metas, datos anuales u ODS. Si encuentra un indicador faltante o ambiguo, aborta antes de insertar cambios.

## Indicadores Nuevos Institucionales

Antes del seeder de relaciones debe ejecutarse el importador del archivo `public/Indicadores nuevos para carga en el SPED.xlsx`:

```bash
php scratch/importar_indicadores_nuevos.php
php scratch/importar_indicadores_nuevos.php --execute
```

El primer comando es un dry-run. El segundo crea los indicadores nuevos, sus datos anuales disponibles y sus relaciones institucionales dentro de una transacción. Es idempotente para indicadores que ya existan.

Actualmente queda pendiente el indicador `Porcentaje de docentes evaluados con nivel de desempeño satisfactorio`, porque aparece en el manifiesto de relaciones pero no en el Excel. El seeder de relaciones lo reporta como pendiente y no bloquea las demás relaciones.

## Respaldo Productivo

Antes de modificar datos, realizar respaldo de las tablas que correspondan al alcance. Para institucionales e historicos normalmente incluye:

```text
cat_programas_derivados_institucionales
cat_programas_derivados_regionales
indicadors
datos_anuales
indicador_ods
programa_institucional_indicador
```

No restaurar este respaldo completo sobre produccion después. Es un mecanismo de recuperación controlada.

## Importacion Historica

Después de las migraciones y del seeder institucional:

```bash
php artisan sped:import-historical \
  --plan=1 \
  --file=storage/app/imports/BD_Completa.xlsx
```

```bash
php artisan sped:import-historical \
  --plan=2 \
  --file=storage/app/imports/BaseCompletaModPED.xlsx
```

Revisar los reportes JSON. Si los conteos y pendientes son correctos:

```bash
php artisan sped:import-historical \
  --plan=1 \
  --file=storage/app/imports/BD_Completa.xlsx \
  --execute
```

```bash
php artisan sped:import-historical \
  --plan=2 \
  --file=storage/app/imports/BaseCompletaModPED.xlsx \
  --execute
```

La importacion usa los catálogos y las instituciones de produccion. No debe importar IDs de beta. El comando es idempotente para los registros que identifica por plan, catalogo, nombre y temática.

## Eliminacion de Regionales

No usar un seeder para borrar regionales. Debe existir un comando separado, por ejemplo:

```bash
php artisan sped:cleanup-regional --plan=3
```

El dry-run debe mostrar antes de borrar:

- Programas regionales afectados.
- Indicadores afectados.
- Datos anuales.
- Relaciones ODS.
- Relaciones institucionales, si existieran.
- Evidencias o archivos relacionados.
- Conteos agrupados por plan.

La eliminacion debe ejecutarse dentro de una transaccion y borrar primero las dependencias. No se debe eliminar por IDs de beta ni usar un `DELETE` global sobre todos los registros regionales.

Actualmente beta contiene regionales en los tres planes:

- PED 1: `510` indicadores en `26` programas.
- PED 2: `220` indicadores en `22` programas.
- PED 3: `7` indicadores en `7` programas.

El comando implementado genera primero un snapshot JSON con la estructura y los registros de programas, indicadores, datos anuales, ODS, carrusel y pivotes. Conserva los archivos fisicos de evidencia:

```bash
php artisan sped:cleanup-regional --plan=3
```

Si el dry-run es correcto y existe un respaldo adicional de la base:

```bash
php artisan sped:cleanup-regional --plan=3 --execute
```

El comando no elimina programas regionales ni archivos de evidencia. El snapshot se guarda en `storage/app/backups/` o en la ruta indicada con `--backup`.

Como respaldo adicional antes de `--execute`, realizar un dump selectivo de las tablas afectadas:

```bash
mysqldump --single-transaction --no-tablespaces "$DB_DATABASE" \
  cat_programas_derivados_regionales \
  indicadors \
  datos_anuales \
  datos_anuales_indicadores \
  indicador_ods \
  carrusel_indicadors \
  programa_institucional_indicador \
  > backup-regionales-ped3-$(date +%Y%m%d-%H%M%S).sql
```

Por eso falta confirmar si se eliminaran solamente los regionales del PED 3, todos los regionales de todos los planes, o únicamente un subconjunto.

## PAE

No se encontró una tabla o modelo cuyo nombre corresponda a PAE en el esquema revisado. No debe incluirse en la migracion hasta confirmar si “PAE” se refiere a otro módulo, catálogo o conjunto de archivos.

## Validacion Final

Después de cada operación validar:

```bash
php artisan migrate:status
```

```bash
php artisan tinker --execute="echo json_encode([
  'ped1' => App\\Models\\Indicador::forPlan(1)->count(),
  'ped2' => App\\Models\\Indicador::forPlan(2)->count(),
  'ped3' => App\\Models\\Indicador::forPlan(3)->count(),
  'institucionales_ped3' => App\\Models\\CatProgramaDerivadoInstitucional::where('plan_estatal', 3)->count()
]);"
```

También revisar una ficha pública por cada tipo de programa, relaciones institucionales, datos anuales, API y enlaces del dashboard.

## Prohibido

- Copiar la base local completa a producción.
- Ejecutar `migrate:fresh` en producción.
- Ejecutar un seeder destructivo automáticamente.
- Restaurar IDs de beta sobre producción.
- Borrar todos los regionales sin especificar plan y alcance.
- Ejecutar importaciones antes de revisar el dry-run.

# Guia: Importacion Incremental a Produccion

La base local no debe exportarse completa a produccion. La importacion historica debe ejecutarse con el codigo desplegado y el archivo Excel como fuente, usando la base productiva como destino.

Esta guia aplica para importar el PED 2 sin reemplazar informacion actual de otros planes.

## Principios

- Ejecutar solamente el plan solicitado, por ejemplo `--plan=2`.
- Resolver catalogos por nombre dentro del plan productivo.
- Resolver instituciones contra el catalogo productivo; no transportar IDs de la base local.
- Crear o actualizar indicadores por catalogo, nombre y tematica.
- Crear o actualizar datos anuales por indicador y anio.
- No crear instituciones automaticamente.
- No modificar datos de otros planes.

## Antes de Ejecutar

1. Desplegar primero el commit que contiene el importador, la correccion de fichas historicas y el buscador del drill-down.
2. Entrar al servidor productivo y ubicarse en la carpeta de la aplicacion.
3. Confirmar que la aplicacion apunta a la base productiva. No cambiar el archivo `.env` de produccion por el de local.
4. Confirmar que el plan existe en produccion:

```bash
php artisan tinker --execute="echo App\\Models\\CatPlanEstatalDesarrollo::find(2)?->nombre;"
```

5. Confirmar que las migraciones y tablas del destino estan actualizadas:

```bash
php artisan migrate:status
```

Si el commit incluye la migracion de `meta_anio` y `meta`, ejecutarla antes de importar indicadores:

```bash
php artisan migrate --force
```

La migracion precarga PED 1 y PED 2 con `meta_anio = 2024`, PED 3 con `meta_anio = 2030`, y conserva `meta_2024` como espejo legado.

6. Comparar el catalogo `ods` de local y produccion por `id` y `nombre`. El importador usa los IDs numericos del archivo para la tabla `indicador_ods`.
7. Revisar si produccion ya tiene datos del PED 2:

```bash
php artisan tinker --execute="echo App\\Models\\Indicador::forPlan(2)->count();"
```

Si el resultado es mayor que cero, detenerse y revisar si existen capturas manuales que no deban ser reemplazadas.

8. Crear la carpeta de trabajo y copiar el Excel al servidor productivo, preferentemente fuera de `public/`:

```bash
mkdir -p storage/app/imports
```

Usar como destino `storage/app/imports/BaseCompletaModPED.xlsx`.

## Respaldo Selectivo

Respaldar las tablas afectadas, sin reemplazar la base completa:

```bash
mysqldump --single-transaction --no-create-info "$DB_DATABASE" \
  cat_ejes \
  cat_programas_derivados_sectoriales \
  cat_programas_derivados_especiales \
  cat_programas_derivados_regionales \
  cat_programas_derivados_institucionales \
  indicadors \
  datos_anuales \
  indicador_ods \
  programa_institucional_indicador \
  > backup-ped2-antes-$(date +%Y%m%d-%H%M%S).sql
```

No se modifican directamente las tablas `ods`, `instituciones`, usuarios ni planes estatales.

## Dry Run

```bash
php artisan sped:import-historical \
  --plan=2 \
  --file=storage/app/imports/BaseCompletaModPED.xlsx
```

En la ejecucion local de referencia se obtuvieron `419` filas validas, `2,582` valores anuales, `5` ejes y `39` programas. En produccion pueden variar los conteos de creados, actualizados e instituciones asignadas, porque se usa el catalogo productivo.

Detenerse si las filas validas, ejes, programas u ODS no coinciden con lo esperado. Revisar el JSON en `storage/app/imports/` y no continuar si hay errores.

## Ejecucion

```bash
php artisan sped:import-historical \
  --plan=2 \
  --file=storage/app/imports/BaseCompletaModPED.xlsx \
  --execute
```

La operacion se ejecuta dentro de una transaccion. Si ocurre un error, los cambios de esa ejecucion se revierten. El reporte y el listado de instituciones pendientes deben conservarse como evidencia del despliegue.

El comando no crea instituciones nuevas. Cuando la institución no existe en el catalogo productivo, el indicador queda con `id_institucion = null` y se documenta en `documentation/pendientes-instituciones-ped2.md`.

## Validacion Posterior

```bash
php artisan tinker --execute="echo json_encode(['plan_2'=>App\\Models\\Indicador::forPlan(2)->count(),'datos'=>App\\Models\\DatoAnual::whereHas('indicador',fn(`$q)=>`$q->forPlan(2))->count(),'sin_institucion'=>App\\Models\\Indicador::forPlan(2)->whereNull('id_institucion')->count()]);"
```

Comparar los conteos con el reporte generado y confirmar que los conteos de los planes existentes no cambiaron.

Tambien revisar manualmente un indicador de cada tipo de programa y abrir su ficha publica. Para el dashboard, comprobar que el enlace conserve `plan_id=2` al abrir un indicador historico.

## Punto de Detencion

Si produccion ya contiene indicadores del PED 2, no ejecutar directamente sin revisar el reporte: el comando actual actualiza los registros coincidentes y sus datos anuales. Si hay captura manual productiva para esos indicadores, primero se debe definir si prevalece el Excel historico o la informacion productiva.

No ejecutar `migrate:fresh`, no restaurar la base local completa y no importar un dump de todas las tablas. El respaldo selectivo es para recuperacion controlada, no para reemplazar informacion productiva posterior.

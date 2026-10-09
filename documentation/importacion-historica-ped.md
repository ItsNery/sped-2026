# Importacion Historica Del PED

## Proposito

El comando importa indicadores historicos a las tablas operativas sin crear una estructura paralela. La pertenencia a cada plan se conserva mediante las relaciones entre ejes, programas derivados e indicadores.

## Plan Activo

El plan que utiliza el dashboard y las consultas operativas se define en `.env`:

```text
SPED_ACTIVE_PLAN_ID=3
```

El valor predeterminado es el PED 2024-2030 (`id = 3`). Cambiarlo permite consultar otro plan ya importado, pero no modifica datos.

## Comando

El archivo predeterminado para PED 1 es:

```text
public/docs/datos-abiertos/2019-2024/ped/datos-generales/BD_Completa.xlsx
```

Primero se debe ejecutar una simulacion:

```bash
php artisan sped:import-historical --plan=1
```

Para escribir en la base de datos:

```bash
php artisan sped:import-historical --plan=1 --execute
```

Tambien se puede indicar otro archivo:

```bash
php artisan sped:import-historical --plan=1 --file="ruta/al/archivo.xlsx" --execute
```

Cada ejecucion genera un reporte JSON en `storage/app/imports/`.

## Relaciones

- Los indicadores del plan se relacionan con `CatEje` mediante la relacion polimorfica.
- Los indicadores sectoriales, especiales y regionales se relacionan con su catalogo polimorfico.
- Los indicadores institucionales se relacionan mediante `programa_institucional_indicador`.
- Los catalogos se crean asociados al plan importado y no se reutilizan entre planes.
- Los indicadores utilizan IDs autoincrementales de la base de datos.
- El numero consecutivo del Excel no se almacena.

## Valores Historicos

- Los indicadores y datos anuales importados quedan validados.
- Los valores `N/D` y celdas vacias no generan datos anuales.
- Los valores cero se conservan.
- Las fechas no interpretables se almacenan como `null`.
- Los ODS inexistentes se reportan y no se crean automaticamente.
- Los campos sin equivalente se dejan en `null` cuando la tabla lo permite.
- Las columnas no nulas sin informacion usan `N/D` o `No definida`.
- Los catalogos faltantes usan imagen, color y descripcion genericos.

## Respaldo

Antes de ejecutar la importacion debe existir un dump completo de la base de datos. La importacion se ejecuta dentro de una transaccion y se revierte si ocurre un error.

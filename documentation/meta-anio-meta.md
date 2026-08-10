# Meta por Ano

## Modelo nuevo

La tabla estatal `indicadors` ahora conserva:

- `meta_anio`: ano al que corresponde la meta.
- `meta`: valor de la meta.
- `meta_2024`: columna legado mantenida temporalmente como espejo de `meta`.

El calculo de avance, los filtros, las fichas, la API y el importador usan `meta_anio` y `meta`. La columna `meta_2024` no debe usarse para nuevas funcionalidades.

## Precarga existente

- PED 1: `meta_anio = 2024`.
- PED 2: `meta_anio = 2024`.
- PED 3: `meta_anio = 2030`.
- `meta` se precargo con el valor anterior de `meta_2024`.

## Compatibilidad

Durante la transicion, el modelo mantiene sincronizadas `meta` y `meta_2024` cuando se guarda un indicador. Esto permite que exportaciones o integraciones antiguas no fallen mientras migran al contrato nuevo.

La tabla `indicadores_municipales` no se modifica en esta etapa porque tiene un flujo y semantica de metas independiente.

## Siguiente etapa

Cuando todas las integraciones externas usen `meta_anio` y `meta`, se puede crear una migracion posterior para eliminar `meta_2024` y retirar el espejo del modelo.

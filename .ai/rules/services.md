---
paths:
  - app/Services/IndicadorSeguimientoService.php
---

# Services

## Estado de actualización de indicadores
La próxima fecha del dato anual elegible más reciente con fecha prevalece sobre la fecha del indicador; una fecha vencida marca desactualización. Sin fecha, comparar el último año con la periodicidad: para frecuencias subanuales, un dato del año actual solo acredita disponibilidad anual, no el periodo cubierto. Respetar el filtro de validación.

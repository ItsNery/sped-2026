# Documentación: Centro de mando del PED

## Propósito

El dashboard administrativo es una superficie ejecutiva para revisar el estado del Plan Estatal de Desarrollo, detectar riesgos y dirigir la atención hacia indicadores, instituciones y programas que requieren intervención.

La vista principal se encuentra en:

- `GET /dashboard`
- `resources/views/dashboard.blade.php`

El dashboard público continúa disponible en `/ped` y utiliza únicamente información validada.

## Universo de datos

Todas las métricas administrativas parten del mismo universo:

- Indicadores asociados al plan seleccionado.
- Ejes del plan.
- Programas derivados del plan.
- Programas institucionales vinculados al plan.

El plan se selecciona mediante `plan_id`. Si no se envía, se utiliza el plan más reciente por identificador.

## Filtros

La pantalla acepta los siguientes parámetros:

- `plan_id`: plan estatal a consultar.
- `solo_validados`: `1` para datos validados o `0` para todos los datos registrados.
- `anio_desde`: año inicial del análisis histórico.
- `anio_hasta`: año final del análisis histórico.
- `eje_id[]`: ejes seleccionados.
- `programa_tipo`: tipo de programa derivado.
- `programa_id[]`: programas seleccionados.
- `institucion_id[]`: instituciones seleccionadas.
- `semaforo[]`: estados del semáforo.
- `calidad[]`: criterios de calidad de información.
- `buscar`: búsqueda por nombre, descripción o temática.

La vista ejecutiva usa datos validados por defecto.

Los filtros de alcance y diagnóstico se encuentran dentro del drawer **Más filtros** para mantener compacta la pantalla inicial. Los filtros activos se muestran como chips removibles.

## Drill-down

La ruta `GET /dashboard/drill-down` muestra el detalle paginado del universo filtrado.

Puede recibir los mismos filtros del dashboard y ordena por prioridad, indicador, institución o avance. Cada fila enlaza al detalle administrativo del indicador.

Los enlaces desde semáforo, ejes, instituciones, programas y prioridades conservan el plan, modo de validación y filtros seleccionados.

## Métricas principales

### Desempeño

- Avance promedio evaluable.
- Cobertura de evaluación.
- Distribución por semáforo.
- Avance por eje.
- Avance por programa derivado.

### Calidad

- Indicadores sin dato anual.
- Indicadores pendientes de validación.
- Indicadores sin meta válida.
- Indicadores sin tendencia definida.
- Fecha del último corte de datos.

### Prioridades

La cola de atención se ordena así:

1. Avance insuficiente.
2. Actualización vencida.
3. Pendiente de validación.
4. Sin dato anual.
5. Sin meta válida.
6. Sin tendencia definida.

Cada registro enlaza al detalle administrativo del indicador.

## Evolución histórica

El servicio `app/Services/PedTrendService.php` compara los dos últimos años disponibles por indicador.

Clasificaciones:

- `Mejoran`: incremento del avance superior a un punto porcentual.
- `Retroceden`: disminución del avance superior a un punto porcentual.
- `Estables`: variación de hasta un punto porcentual.
- `Sin comparación`: no existen dos años comparables.

La evolución se presenta como comportamiento observado. No es una proyección ni un pronóstico.

## Dashboard municipal

El módulo municipal conserva por ahora su interfaz y flujo administrativo anterior. No se modifica durante esta fase, porque los municipios se encuentran a mitad de su administración.

El rediseño con métricas municipales, comparativos regionales y evolución histórica queda diferido para la renovación administrativa del próximo año.

## Exportaciones

Rutas administrativas:

- `GET /dashboard/exportar/pdf`
- `GET /dashboard/exportar/xlsx`

Ambas reutilizan los mismos filtros y métricas del dashboard web.

### PDF

Incluye:

- Resumen ejecutivo.
- Semaforización.
- Calidad de información.
- Avance por eje.
- Indicadores prioritarios.
- Serie histórica.
- Comparación entre periodos.

### XLSX

Incluye las hojas:

- `Resumen`
- `Prioridades`
- `Ejes`
- `Instituciones`
- `Programas`
- `Serie histórica`
- `Metodología`

## Servicios

### `PedMetricsService`

Calcula avance, cobertura, semaforización, validación y motivos de no evaluación.

### `PedTrendService`

Calcula series históricas y variaciones entre periodos.

## Reglas de avance

- `Mayor es mejor`: `dato / meta * 100`.
- `Menor es mejor`: `meta / dato * 100`.
- `Constante`: `dato / meta * 100`.

Si no existe meta, tendencia o dato válido, el avance es `null` y no cero.

Semáforo:

- Excedido: `>= 110%`.
- Aceptable: `>= 91%`.
- Moderado: `>= 71%`.
- Insuficiente: `< 71%`.
- No clasificado: sin avance calculable.

## Verificación manual

- Cambiar el plan y confirmar que todas las secciones cambian.
- Alternar datos validados y registrados.
- Aplicar rango de años.
- Revisar indicadores con valor cero.
- Revisar indicadores sin meta o tendencia.
- Abrir una prioridad desde la tabla.
- Generar PDF y XLSX con los mismos filtros.
- Confirmar que el dashboard municipal conserva su interfaz anterior.

# Plan de implementación - Dashboard ejecutivo fase 2

## Objetivo

Convertir el Centro de mando del PED en una herramienta de seguimiento temporal y exportación ejecutiva, manteniendo una única regla de cálculo para datos validados y registrados. El rediseño del administrador municipal queda reservado para la renovación administrativa del próximo año.

La fase parte del dashboard ejecutivo implementado en:

- `app/Http/Controllers/DashboardController.php`
- `resources/views/dashboard.blade.php`
- `public/css/estilos-admin.css`
- `app/Services/PedMetricsService.php`

## Resultado esperado

El administrador podrá responder desde una sola sesión:

- ¿El avance mejora o empeora respecto al periodo anterior?
- ¿Qué instituciones y ejes concentran el riesgo?
- ¿Qué indicadores están vencidos o pendientes de validación?
- ¿Qué información puede compartir en una reunión ejecutiva?

## Decisiones recomendadas

> [!IMPORTANT]
>
> - Mantener **datos validados** como vista ejecutiva predeterminada.
> - Mantener el filtro **Todos los registrados** únicamente para diagnóstico administrativo.
> - No calcular impacto, presupuesto o población beneficiaria mientras esas variables no existan en el modelo.
> - No presentar una regresión estadística como pronóstico. La tendencia histórica debe etiquetarse como comportamiento observado.
> - Mantener `/ped` como vista pública y el dashboard ejecutivo como superficie administrativa.

## Alcance

### Incluido

1. Análisis histórico anual del PED.
2. Variación contra el periodo anterior.
3. Identificación de mejoras, retrocesos y estabilidad.
4. Exportación ejecutiva en PDF y XLSX.
5. Pruebas de cálculo, autorización, filtros y exportación.
6. Optimización de consultas y cache de agregados.

### No incluido

- Modelos de impacto causal.
- Proyecciones presupuestales.
- Ranking público de municipios.
- Nuevos campos de presupuesto o población sin definición funcional aprobada.
- Sustitución del catálogo de indicadores o del flujo de captura.

## Fase 2A: Análisis temporal del PED

### Datos de entrada

Fuente principal:

- `datos_anuales`
- `indicadors`
- `cat_planes_estatales_desarrollo`
- Relaciones con ejes, programas e instituciones.

Solo se considerarán datos con valor numérico. El filtro de validación se aplicará antes de calcular cualquier agregado.

### Métricas

#### Variación anual

Para un indicador con valores `valor_actual` y `valor_anterior`:

```text
variación absoluta = valor_actual - valor_anterior
variación relativa = ((valor_actual - valor_anterior) / valor_anterior) * 100
```

La variación relativa se omitirá cuando el valor anterior sea cero.

#### Avance observado

- Mejoró: el valor se mueve en la dirección de la tendencia configurada.
- Retrocedió: el valor se mueve contra la tendencia configurada.
- Estable: la variación está dentro de un umbral configurable.
- Sin comparación: solo existe un periodo con dato.

El umbral inicial recomendado será de `1%` relativo, salvo indicadores con valores cercanos a cero.

#### Métricas agregadas

- Avance promedio por año.
- Cobertura de evaluación por año.
- Indicadores que mejoran.
- Indicadores que retroceden.
- Indicadores estables.
- Variación por eje.
- Variación por institución.
- Variación por programa derivado.
- Indicadores con mayor mejora.
- Indicadores con mayor retroceso.

### Componentes nuevos

#### `app/Services/PedTrendService.php`

Responsabilidades:

- Seleccionar los dos últimos datos válidos por indicador.
- Calcular variaciones respetando `Mayor es mejor`, `Menor es mejor` y `Constante`.
- Generar series agregadas por año, eje, institución y programa.
- Devolver motivos de ausencia de comparación.
- Evitar consultas por indicador cuando las relaciones estén precargadas.

#### `app/Http/Controllers/DashboardController.php`

Agregar al contrato del dashboard:

- `trendSummary`
- `trendByYear`
- `trendByAxis`
- `improvingIndicators`
- `decliningIndicators`
- `trendCutoff`

#### `resources/views/dashboard.blade.php`

Agregar una sección `Evolución del desempeño` con:

- Línea de avance promedio por año.
- Resumen de mejora, retroceso y estabilidad.
- Tabla de indicadores con mayor retroceso.
- Filtro de año inicial y final.
- Leyenda explícita: `Comportamiento observado`, no `Pronóstico`.

## Fase 2B: Exportación ejecutiva

### Rutas

Agregar rutas protegidas por permiso:

```text
GET /dashboard/exportar/pdf
GET /dashboard/exportar/xlsx
```

Nombres sugeridos:

- `dashboard.export.pdf`
- `dashboard.export.xlsx`

Los parámetros aceptados deben coincidir con el dashboard:

- `plan_id`
- `solo_validados`
- `anio_desde`
- `anio_hasta`
- `institucion_id` opcional
- `eje_id` opcional

### PDF ejecutivo

Contenido mínimo:

1. Nombre del plan y fecha de corte.
2. Estado de validación seleccionado.
3. KPIs principales.
4. Distribución de semaforización.
5. Avance por eje.
6. Instituciones bajo presión.
7. Indicadores prioritarios.
8. Evolución anual.
9. Nota metodológica.

El PDF debe utilizar el mismo resultado de métricas que la vista web. No se deben recalcular fórmulas distintas para el documento.

### XLSX ejecutivo

Hojas sugeridas:

- `Resumen`
- `Indicadores prioritarios`
- `Avance por eje`
- `Instituciones`
- `Programas derivados`
- `Serie histórica`
- `Metodología`

Cada hoja debe incluir fecha de corte y modo de validación.

### Archivos candidatos

- `app/Http/Controllers/DashboardExportController.php`
- `app/Exports/DashboardExecutiveExport.php`
- `resources/views/exports/dashboard-executive-pdf.blade.php`
- `resources/views/exports/dashboard-executive-xlsx.blade.php`, si el exportador actual lo requiere.

Se reutilizará la infraestructura existente de Browsershot y PhpSpreadsheet cuando sea compatible.

## Fase 2C: Dashboard municipal - diferido

> [!NOTE]
>
> Esta fase queda fuera de la entrega actual. El módulo municipal conservará su interfaz y flujo existentes durante el periodo administrativo vigente. Se retomará antes de la renovación administrativa del próximo año.

### Alcance futuro del usuario municipal

El usuario municipal debe ver únicamente el municipio asociado a su cuenta.

### KPIs

- Indicadores municipales registrados.
- Indicadores con resultado.
- Cobertura de resultados.
- Indicadores validados.
- Indicadores sin actualización.
- Avance promedio, cuando exista meta y tendencia válidas.
- Indicadores insuficientes.

### Análisis

- Evolución anual del municipio.
- Avance por dimensión.
- Avance por nivel.
- Avance por tipo de indicador.
- Estado por periodicidad.
- Indicadores pendientes de captura.
- Indicadores con solo línea base.
- Comparativo contra el promedio regional, únicamente de forma agregada.

### Componentes nuevos

#### `app/Services/MunicipalMetricsService.php`

Centralizará:

- Selección del resultado más reciente.
- Cálculo de cobertura por periodo.
- Cálculo de avance municipal.
- Semaforización municipal.
- Variación histórica municipal.

No se debe duplicar esta lógica dentro de `MunicipioConvenioController` o las vistas.

#### `resources/views/panel-indicadores-municipales/dashboard.blade.php`

Reemplazar la tarjeta de acceso actual por un tablero con:

- Encabezado del municipio.
- Fecha de corte.
- KPIs.
- Alertas de captura.
- Evolución histórica.
- Tabla prioritaria.
- Acceso a fichas técnicas.

## Fase 2D: Rendimiento y datos

### Consultas

- Mantener una sola consulta base de indicadores por plan.
- Eager load de institución, relaciones de programa y datos anuales.
- Evitar `exists()` por indicador cuando la colección ya esté cargada.
- Reemplazar conteos PHP repetidos por agregaciones SQL donde sea medible.
- Agregar índices si el análisis de consultas confirma su necesidad.

### Cache

Separar cache por:

- Plan.
- Modo de validación.
- Rango de años.
- Filtros institucionales.
- Versión de métricas.

Invalidar la versión de métricas después de:

- Crear o actualizar un indicador.
- Crear o actualizar un dato anual.
- Cambiar validación.
- Cambiar meta, tendencia o periodicidad.

## Fase 2E: Accesibilidad y experiencia

- Agregar alternativa tabular para cada gráfico.
- Usar `aria-label`, `aria-controls`, `aria-expanded` y `aria-selected`.
- Agregar estados de carga, vacío y error.
- Añadir foco visible para filtros y acciones.
- Permitir navegación sin depender del clic sobre una gráfica.
- Hacer que las prioridades funcionen como enlaces y no solo como tarjetas visuales.
- Mantener el sistema visual institucional: verde profundo, arena, guinda y colores oficiales del semáforo.
- Evitar gauges cuando una barra comparativa comunique mejor el resultado.

## Permisos

Permisos sugeridos:

- `ver-dashboard-ejecutivo`
- `exportar-dashboard-ejecutivo`
- `ver-dashboard-municipal`
- `ver-comparativo-regional`

Reglas:

- Administrador: todos los planes y filtros.
- Enlace: únicamente la información permitida por sus instituciones.
- Usuario municipal: solo su municipio.
- Público: únicamente el dashboard público validado.

## Pruebas

### Pruebas unitarias

- Variación para `Mayor es mejor`.
- Variación para `Menor es mejor`.
- Variación para `Constante`.
- Valor anterior cero.
- Un solo dato histórico.
- Datos nulos o no numéricos.
- Indicador sin meta.
- Indicador sin tendencia.
- Indicador con valor cero.

### Pruebas de autorización

- Administrador puede consultar y exportar.
- Enlace no puede consultar instituciones no asignadas.
- Usuario municipal no puede cambiar `municipio_id` por URL.
- Usuario sin permiso recibe `403`.

### Pruebas de integración

- Dashboard validado y dashboard registrado usan el mismo universo.
- Filtros de plan y años modifican todas las tarjetas.
- PDF y XLSX coinciden con la vista web.
- La ruta administrativa antigua redirige al dashboard unificado.
- Dashboard municipal no muestra indicadores de otro municipio.

### Verificación manual

- Comparar avance de un indicador con su ficha técnica.
- Comparar avance por eje con el dashboard público.
- Revisar un indicador con valor cero.
- Revisar un indicador menor es mejor.
- Revisar un indicador sin dato validado.
- Exportar un plan con datos y otro sin datos.
- Revisar el dashboard en escritorio, tablet y móvil.

## Orden de trabajo

1. Crear pruebas y contrato de métricas temporales.
2. Implementar `PedTrendService`.
3. Integrar evolución y comparativos en el dashboard ejecutivo.
4. Implementar exportación PDF.
5. Implementar exportación XLSX.
6. Crear `MunicipalMetricsService`.
7. Retomar el rediseño municipal antes de la renovación administrativa.
8. Optimizar consultas, cache e índices.
9. Completar accesibilidad y pruebas manuales.

## Criterios de aceptación

- Todas las métricas de una pantalla usan el mismo plan y modo de validación.
- La evolución histórica identifica claramente mejora, retroceso y estabilidad.
- La exportación coincide con la información visible en la web.
- Cuando se retome el rediseño, un usuario municipal no podrá consultar datos de otro municipio.
- Ningún valor sin base suficiente se presenta como proyección o impacto.
- Las prioridades tienen enlace directo a la acción correspondiente.
- La pantalla funciona sin depender exclusivamente de gráficas o interacción con mouse.
- Las consultas del dashboard no generan N+1 por indicador en el flujo principal.

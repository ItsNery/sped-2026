# Patrón de índices administrativos

Este patrón concentra la presentación usada por el índice de indicadores para que pueda aplicarse gradualmente a los demás catálogos y secciones administrativas. Los estilos viven en `public/css/estilos-admin.css` bajo el bloque `Patrón reutilizable para índices administrativos`.

## Estructura base

```blade
<x-slot name="header">
    <div class="exec-header admin-index-header">
        <div>
            <span class="exec-eyebrow">Contexto de la sección</span>
            <h2 class="exec-header__title">Título del módulo</h2>
        </div>
        <span class="exec-header__plan">Descripción breve</span>
    </div>
</x-slot>

<div class="admin-index">
    <div class="contenedor-principal admin-index__surface mx-auto">
        <div class="admin-index__heading">
            <div>
                <span class="exec-eyebrow">Universo registrado</span>
                <h1>Listado de elementos</h1>
            </div>
            <span class="admin-index__count">{{ count($items) }} registros</span>
        </div>

        <div class="admin-index__actions">
            <!-- Acciones principales de la sección -->
        </div>

        <div class="table-responsive admin-index-table-wrap">
            <table class="table table-striped admin-index-table">
                <!-- Encabezado y registros -->
            </table>
        </div>
    </div>
</div>
```

## Filtros opcionales

```blade
<section class="admin-index-filter" aria-labelledby="filter-title">
    <div class="admin-index-filter__heading">
        <div>
            <span class="exec-eyebrow">Filtros de consulta</span>
            <h2 id="filter-title">Acota el listado</h2>
        </div>
        <span>Instrucción breve</span>
    </div>
    <div class="admin-index-filter__grid">
        <div class="admin-index-filter__field">
            <label for="filter-example">Campo</label>
            <select id="filter-example"></select>
        </div>
    </div>
</section>
```

La cuadrícula admite dos campos por fila y cambia a una columna debajo de `768px`. La clase de campo también contiene los estilos de Tom Select.

## Acciones de tabla

Agrupa acciones con `admin-index-table-actions` y usa `admin-index-table-action` como clase base.

| Variante | Uso |
| --- | --- |
| `--edit` | Editar el registro responsable |
| `--review` | Abrir una revisión o acción pendiente |
| `--delete` | Acción destructiva confirmada |
| `--document` | Abrir o descargar un documento relacionado |
| `--validated` | Estado validado, no interactivo |
| `--updated` | Estado actualizado, no interactivo |
| `--pending` | Estado pendiente, no interactivo |

Los enlaces y botones deben contener texto visible. Los estados no deben comunicarse únicamente mediante color.

## DataTables

Asigna estas clases mediante `className` para conservar la barra compacta:

```js
const buttons = [
    { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'admin-index-export-button admin-index-export-button--excel' },
    { extend: 'csvHtml5', text: '<i class="fas fa-file-csv"></i> CSV', className: 'admin-index-export-button admin-index-export-button--csv' },
    { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'admin-index-export-button admin-index-export-button--pdf' },
    { extend: 'copy', text: '<i class="fas fa-copy"></i> Copiar', className: 'admin-index-export-button admin-index-export-button--copy' },
];
```

La paginación se tematiza automáticamente cuando DataTables está dentro de `.admin-index`.

## Criterios

- Mantener una superficie blanca, espaciamiento consistente y jerarquía tipográfica clara.
- Usar los tokens institucionales definidos en `:root`; no agregar colores Bootstrap a este patrón.
- Reservar verde para acciones operativas, guinda para énfasis o acciones destructivas y arena para revisión.
- Mantener foco visible, etiquetas asociadas y nombres accesibles en grupos de acciones.
- En móvil, conservar la tabla dentro de `table-responsive`; no comprimir columnas hasta volverlas ilegibles.
- Limitar cualquier ajuste de DataTables al contenedor `.admin-index` para no alterar módulos aún no migrados.

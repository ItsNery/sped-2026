@extends('layouts.plantilla')

@section('title', 'Documentación y Consola de API de Indicadores')
@section('meta-description', 'Página pública de documentación, consola de pruebas interactiva y consulta en tiempo real de la API de indicadores del SPED del Estado de Puebla.')

@section('css')
@endsection

@section('content')
    <div class="api-docs">

        {{-- ============================================================
         HERO SECTION (matches inicio-hero style)
         ============================================================ --}}
        <section class="api-docs__hero">
            <div class="api-docs__hero-container">
                <div class="api-docs__hero-content">
                    <span class="api-docs__hero-tag">API Pública</span>
                    <h1 class="api-docs__hero-title">Documentación e Integración de la API</h1>
                    <p class="api-docs__hero-desc">
                        Accede en tiempo real a los indicadores del SPED. Ponemos a tu disposición
                        endpoints REST públicos donde puedes consultar la información de manera rápida y eficaz.
                    </p>
                    <a href="#consola" class="api-docs__hero-cta">
                        Probar Consola <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </section>

        {{-- ============================================================
         TABS NAVIGATION
         ============================================================ --}}
        <section class="api-docs__section api-docs__section--light" id="consola">
            <div class="api-docs__section-container">
                <div class="api-docs__tabs">
                    <button class="api-docs__tab-btn api-docs__tab-btn--active" data-tab="tab-console">
                        Consola Interactiva
                    </button>
                    <button class="api-docs__tab-btn" data-tab="tab-docs">
                        Documentación
                    </button>
                </div>

                {{-- TAB 1: CONSOLE --}}
                <div id="tab-console" class="api-docs__tab-content api-docs__tab-content--active">
                    <div class="row">
                        {{-- Panel de Filtros --}}
                        <div class="col-lg-4">
                            <div class="api-docs__card">
                                <div class="api-docs__card-header">
                                    <h3>Filtros de Búsqueda</h3>
                                    <span class="api-docs__card-header-sub">Consulta indicadores en tiempo real</span>
                                </div>
                                <div class="api-docs__card-body">
                                    <form id="console-filter-form">
                                        <div class="mb-3 api-docs__form-group">
                                            <label class="api-docs__form-label" for="filter-buscar">Palabra Clave</label>
                                            <input type="text" id="filter-buscar" class="form-control api-docs__form-control"
                                                placeholder="Ej: pobreza, agua, educación...">
                                        </div>

                                        <div class="mb-3 api-docs__form-group">
                                            <label class="api-docs__form-label" for="filter-institucion">Institución Responsable</label>
                                            <select id="filter-institucion" class="form-select api-docs__form-select">
                                                <option value="">Todas las instituciones</option>
                                                @foreach ($instituciones as $inst)
                                                    <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3 api-docs__form-group">
                                            <label class="api-docs__form-label" for="filter-ods">Objetivo de Desarrollo Sostenible (ODS)</label>
                                            <select id="filter-ods" class="form-select api-docs__form-select">
                                                <option value="">Todos los ODS</option>
                                                @foreach ($ods as $o)
                                                    <option value="{{ $o->id }}">ODS {{ $o->id }}: {{ $o->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-4 api-docs__form-group">
                                            <label class="api-docs__form-label" for="filter-programa">Programa Derivado</label>
                                            <select id="filter-programa" class="form-select api-docs__form-select">
                                                <option value="">Todos los programas</option>
                                                @foreach ($programasDerivados as $prog)
                                                    <option value="{{ $prog }}">{{ $prog }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <button type="submit" class="w-100 api-docs__btn">
                                            <i class="fas fa-play me-2"></i>Ejecutar Consulta
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Panel de Resultados --}}
                        <div class="col-lg-8">
                            {{-- Request URL preview --}}
                            <div class="api-docs__card mb-4">
                                <div class="api-docs__card-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="api-docs__method-badge api-docs__method-badge--get">GET</span>
                                        <div class="api-docs__url-string flex-grow-1" id="request-url-preview">
                                            Cargando...
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tablas y JSON de Resultados --}}
                            <div class="api-docs__card">
                                <div class="api-docs__card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h2 class="api-docs__card-title api-docs__card-title--no-border mb-0">Resultados de la API</h2>
                                        <ul class="nav nav-pills" id="results-toggle" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active py-1 px-3" id="pill-table-tab" data-bs-toggle="pill"
                                                    data-bs-target="#pill-table" type="button" role="tab">Tabla</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link py-1 px-3" id="pill-json-tab" data-bs-toggle="pill"
                                                    data-bs-target="#pill-json" type="button" role="tab">JSON</button>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="tab-content" id="results-tab-content">
                                        {{-- Table View --}}
                                        <div class="tab-pane fade show active" id="pill-table" role="tabpanel">
                                            <div class="table-responsive api-docs__table-wrapper">
                                                <table class="table api-docs__table" id="results-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Nombre</th>
                                                            <th>Institución</th>
                                                            <th>Semáforo</th>
                                                            <th>Avance</th>
                                                            <th>Acción</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="results-table-body">
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted py-5">
                                                                <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                                                Haz clic en "Ejecutar Consulta" para buscar indicadores.
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            {{-- Pagination --}}
                                            <div class="d-flex justify-content-between align-items-center mt-3 api-docs__pagination-panel--hidden" id="pagination-panel">
                                                <span class="text-muted" id="pagination-text"></span>
                                                <nav>
                                                    <ul class="pagination pagination-sm mb-0" id="pagination-buttons"></ul>
                                                </nav>
                                            </div>
                                        </div>

                                        {{-- JSON Raw View --}}
                                        <div class="tab-pane fade" id="pill-json" role="tabpanel">
                                            <div class="api-docs__code-wrapper">
                                                <pre class="api-docs__code-block" id="json-response-preview">{
  "info": "Ejecuta una consulta para ver la respuesta JSON aquí."
}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB 2: TECHNICAL DOCS --}}
                <div id="tab-docs" class="api-docs__tab-content">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="api-docs__card">
                                <div class="api-docs__card-body">
                                    <h5 class="fw-bold mb-3">Contenido</h5>
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><a href="#doc-general"
                                                class="text-decoration-none text-muted hover-primary">Generalidades</a></li>
                                        <li class="mb-2"><a href="#doc-list"
                                                class="text-decoration-none text-muted hover-primary">1. Listado de Indicadores</a>
                                        </li>
                                        <li class="mb-2"><a href="#doc-detail"
                                                class="text-decoration-none text-muted hover-primary">2. Detalle del Indicador</a></li>
                                        <li class="mb-2"><a href="#doc-codes"
                                                class="text-decoration-none text-muted hover-primary">Ejemplos de Código</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-9">
                            {{-- General Info --}}
                            <div class="api-docs__card" id="doc-general">
                                <div class="api-docs__card-header">
                                    <h3>Información General</h3>
                                </div>
                                <div class="api-docs__card-body">
                                    <p>La API de Indicadores del SPED es de acceso público y de solo lectura. No requiere de
                                        autenticación (tokens o llaves API) por lo que es de libre integración para tableros ciudadanos,
                                        aplicaciones gubernamentales y análisis científico.</p>

                                    <div class="alert alert-info py-2" role="alert">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Importante:</strong> Los datos anuales históricos devueltos por esta API corresponden
                                        <strong>únicamente</strong> a los registros que han sido validados por los administradores en el
                                        sistema.
                                    </div>
                                </div>
                            </div>

                            {{-- Endpoint List --}}
                            <div class="api-docs__card" id="doc-list">
                                <div class="api-docs__card-header">
                                    <h3>1. Listado General de Indicadores</h3>
                                </div>
                                <div class="api-docs__card-body">
                                    <p>Obtiene una lista paginada de todos los indicadores del sistema con sus relaciones de ODS e
                                        institución asociada.</p>

                                    <div class="mb-3 d-flex align-items-center gap-3">
                                        <span class="api-docs__method-badge api-docs__method-badge--get">GET</span>
                                         <div class="api-docs__url-string flex-grow-1 api-url-text" data-path="/api/v1/indicadores">Cargando...
                                        </div>
                                    </div>

                                    <h5 class="fw-bold mt-4 mb-2">Parámetros de Consulta (Query Params)</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped text-sm">
                                            <thead>
                                                <tr>
                                                    <th>Parámetro</th>
                                                    <th>Tipo</th>
                                                    <th>Descripción</th>
                                                    <th>Ejemplo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><code>buscar</code></td>
                                                    <td>String</td>
                                                    <td>Filtro de búsqueda por texto en nombre, descripción y temática del indicador.
                                                    </td>
                                                    <td><code>pobreza</code></td>
                                                </tr>
                                                <tr>
                                                    <td><code>institucion_id</code></td>
                                                    <td>Integer</td>
                                                    <td>Filtra los indicadores que pertenecen a una institución específica.</td>
                                                    <td><code>2</code></td>
                                                </tr>
                                                <tr>
                                                    <td><code>ods_id</code></td>
                                                    <td>Integer</td>
                                                    <td>Filtra los indicadores asociados a un Objetivo de Desarrollo Sostenible.</td>
                                                    <td><code>1</code></td>
                                                </tr>
                                                <tr>
                                                    <td><code>programa_derivado</code></td>
                                                    <td>String</td>
                                                    <td>Filtra por el tipo de programa de desarrollo del indicador.</td>
                                                    <td><code>Plan Estatal de Desarrollo</code></td>
                                                </tr>
                                                <tr>
                                                    <td><code>per_page</code></td>
                                                    <td>Integer</td>
                                                    <td>Controla el número de registros por página (mínimo 1, máximo 100, default 15).
                                                    </td>
                                                    <td><code>10</code></td>
                                                </tr>
                                                <tr>
                                                    <td><code>page</code></td>
                                                    <td>Integer</td>
                                                    <td>Número de la página de resultados a consultar.</td>
                                                    <td><code>2</code></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- Endpoint Detail --}}
                            <div class="api-docs__card" id="doc-detail">
                                <div class="api-docs__card-header">
                                    <h3>2. Detalle de un Indicador</h3>
                                </div>
                                <div class="api-docs__card-body">
                                    <p>Consulta la ficha técnica detallada de un indicador específico, incluyendo su desglose histórico
                                        completo de años validados.</p>

                                    <div class="mb-3 d-flex align-items-center gap-3">
                                        <span class="api-docs__method-badge api-docs__method-badge--get">GET</span>
                                         <div class="api-docs__url-string flex-grow-1 api-url-text" data-path="/api/v1/indicadores/{id_or_slug}">
                                            Cargando...</div>
                                    </div>

                                    <h5 class="fw-bold mt-4 mb-2">Parámetros de Ruta (Path Params)</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped text-sm">
                                            <thead>
                                                <tr>
                                                    <th>Parámetro</th>
                                                    <th>Tipo</th>
                                                    <th>Obligatorio</th>
                                                    <th>Descripción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><code>id_or_slug</code></td>
                                                    <td>Integer | String</td>
                                                    <td>Sí</td>
                                                    <td>El ID numérico único del indicador o el slug legible por humanos.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- Code Snippets --}}
                            <div class="api-docs__card" id="doc-codes">
                                <div class="api-docs__card-header">
                                    <h3>Ejemplos de Integración</h3>
                                </div>
                                <div class="api-docs__card-body">
                                    <ul class="nav nav-tabs mb-3 api-docs__snippet-tabs" id="snippet-tabs">
                                        <li class="nav-item">
                                            <button class="nav-link active" data-snippet-tab="snip-js">JavaScript
                                                (Fetch)</button>
                                        </li>
                                        <li class="nav-item">
                                            <button class="nav-link" data-snippet-tab="snip-php">PHP (cURL)</button>
                                        </li>
                                    </ul>

                                    <div id="snip-js" class="api-docs__snippet-content">
                                        <div class="api-docs__code-wrapper">
                                            <pre class="api-docs__code-block"><code>// Ejemplo de consulta de indicadores con filtros desde JS
const url = new URL(window.location.origin + '/api/v1/indicadores');
url.searchParams.append('buscar', 'pobreza');
url.searchParams.append('institucion_id', '2');

fetch(url, {
    method: 'GET',
    headers: {
        'Accept': 'application/json'
    }
})
.then(response => response.json())
.then(data => {
    console.log("Indicadores cargados:", data);
})
.catch(error => console.error("Error al consultar la API:", error));</code></pre>
                                        </div>
                                    </div>

                                    <div id="snip-php" class="api-docs__snippet-content d-none">
                                        <div class="api-docs__code-wrapper">
                                            <pre class="api-docs__code-block"><code>&lt;?php
// Ejemplo de consumo en PHP usando cURL
$ch = curl_init();
$url = "http://localhost/api/v1/indicadores?buscar=" . urlencode("pobreza");

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    $data = json_decode($response, true);
    print_r($data);
}
curl_close($ch);
?&gt;</code></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- INDICATOR DETAIL MODAL --}}
    <div class="modal fade" id="indicatorDetailModal" tabindex="-1" aria-labelledby="indicatorDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog api-docs__modal-xl modal-dialog-centered">
            <div class="modal-content api-docs__modal-content">
                <div class="modal-header api-docs__modal-header">
                    <h5 class="modal-title fw-bold" id="detail-indicator-nombre">Nombre del Indicador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body api-docs__modal-body p-4">
                    <div class="row">
                        {{-- Left: Metadata --}}
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm p-3 mb-3 bg-white">
                                <h6 class="text-muted text-uppercase fw-bold mb-3 api-docs__modal-section-title">Ficha Descriptiva</h6>
                                <p class="mb-3 api-docs__modal-desc" id="detail-indicator-descripcion"></p>

                                <table class="table table-sm table-borderless text-sm mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold text-muted w-30">Programa Derivado:</td>
                                            <td id="detail-indicator-programa-derivado"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Eje/Programa:</td>
                                            <td id="detail-indicator-programa"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Temática:</td>
                                            <td id="detail-indicator-tematica"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Unidad Medida:</td>
                                            <td id="detail-indicator-unidad"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Fórmula:</td>
                                            <td id="detail-indicator-formula" class="font-monospace"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Cobertura:</td>
                                            <td id="detail-indicator-cobertura"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Periodicidad:</td>
                                            <td id="detail-indicator-periodicidad"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Tendencia:</td>
                                            <td id="detail-indicator-tendencia"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Fuente de Información:</td>
                                            <td id="detail-indicator-fuente"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- ODS & Institucion --}}
                            <div class="card border-0 shadow-sm p-3 bg-white">
                                <h6 class="text-muted text-uppercase fw-bold mb-3 api-docs__modal-section-title">Responsabilidad y Sostenibilidad</h6>
                                <div class="mb-3">
                                    <label class="fw-bold text-muted d-block text-xs mb-1">Institución Responsable</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-building text-secondary api-docs__modal-icon"></i>
                                        <div>
                                            <div class="fw-bold text-sm" id="detail-indicator-inst-nombre"></div>
                                            <div class="text-xs text-muted" id="detail-indicator-inst-titular"></div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="fw-bold text-muted d-block text-xs mb-2">Relación con ODS</label>
                                    <div id="detail-indicator-ods-list"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Right: Performance Data --}}
                        <div class="col-lg-6">
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <div class="card border-0 shadow-sm p-3 text-center bg-white h-100">
                                        <span class="text-muted text-xs fw-bold text-uppercase d-block mb-1">Avance Validado</span>
                                        <h3 class="fw-bold mb-0" style="color: var(--colorpricipalbackup);" id="detail-indicator-avance">0%</h3>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card border-0 shadow-sm p-3 text-center bg-white h-100">
                                        <span class="text-muted text-xs fw-bold text-uppercase d-block mb-1">Semáforo de Desempeño</span>
                                        <div>
                                            <span class="api-docs__semaforo" id="detail-indicator-semaforo">N/A</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Datos Anuales Table --}}
                            <div class="card border-0 shadow-sm p-3 bg-white">
                                <h6 class="text-muted text-uppercase fw-bold mb-3 api-docs__modal-section-title">Historial de Datos Anuales Validados</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped text-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Año</th>
                                                <th>Valor</th>
                                                <th>Resultado / Logros</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detail-indicator-datos-anuales-body">
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">Cargando datos históricos...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer api-docs__modal-footer border-0 p-3">
                    <a href="#" id="btn-ver-ficha-completa" class="btn btn-outline-danger py-2 px-4 rounded-8" target="_blank" title="Abrir ficha completa del indicador en una nueva pestaña">
                        <i class="fas fa-external-link-alt me-1"></i> Ver ficha completa
                    </a>
                    <button type="button" class="btn btn-secondary py-2 px-4 rounded-8" data-bs-dismiss="modal">Cerrar Detalle</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('jss-final')
    <script src="{{ asset('js/api_docs.js') }}" defer></script>
@endsection

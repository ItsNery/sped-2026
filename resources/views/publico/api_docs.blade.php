@extends('layouts.plantilla')

@section('title', 'Documentación y Consola de API de Indicadores')
@section('meta-description', 'Página pública de documentación, consola de pruebas interactiva y consulta en tiempo real de la API de indicadores del SPED del Estado de Puebla.')

@section('css')
<style>
    :root {
        --color-primary: #691A32;
        --color-primary-light: #8A2545;
        --color-secondary: #D4C19C;
        --color-dark: #1F2937;
        --color-light: #F9FAFB;
        --color-success: #10B981;
        --color-warning: #F59E0B;
        --color-info: #3B82F6;
        --font-mono: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
    }

    .api-container {
        max-width: 1280px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .api-header {
        background: linear-gradient(135deg, var(--color-primary) 0%, #3a0d1b 100%);
        color: white;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(105, 26, 50, 0.3);
        margin-bottom: 30px;
    }

    .api-header h1 {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .api-header p {
        font-size: 1.1rem;
        opacity: 0.9;
        max-width: 800px;
        margin-bottom: 0;
    }

    /* Tabs navigation */
    .api-tabs {
        display: flex;
        border-bottom: 2px solid #E5E7EB;
        margin-bottom: 30px;
        gap: 15px;
    }

    .api-tab-btn {
        background: none;
        border: none;
        padding: 12px 24px;
        font-size: 1.05rem;
        font-weight: 600;
        color: #6B7280;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        bottom: -2px;
        border-bottom: 3px solid transparent;
    }

    .api-tab-btn:hover {
        color: var(--color-primary);
    }

    .api-tab-btn.active {
        color: var(--color-primary);
        border-bottom-color: var(--color-primary);
    }

    .api-tab-content {
        display: none;
        animation: fadeIn 0.4s ease;
    }

    .api-tab-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Cards */
    .api-card {
        background: white;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        padding: 25px;
        margin-bottom: 25px;
    }

    .api-card-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--color-dark);
        margin-bottom: 20px;
        border-left: 4px solid var(--color-primary);
        padding-left: 12px;
    }

    /* HTTP Badges */
    .method-badge {
        font-family: var(--font-mono);
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.85rem;
        display: inline-block;
    }

    .method-get {
        background-color: #E0F2FE;
        color: #0369A1;
        border: 1px solid #BAE6FD;
    }

    .url-string {
        font-family: var(--font-mono);
        background-color: #F3F4F6;
        padding: 6px 12px;
        border-radius: 6px;
        color: #374151;
        font-size: 0.9rem;
        word-break: break-all;
    }

    /* Code Blocks */
    .code-block-wrapper {
        position: relative;
        margin: 15px 0;
    }

    .code-block {
        font-family: var(--font-mono);
        background-color: #1E293B;
        color: #E2E8F0;
        padding: 20px;
        border-radius: 8px;
        overflow-x: auto;
        font-size: 0.88rem;
        line-height: 1.5;
        max-height: 450px;
    }

    /* Forms */
    .form-group label {
        font-weight: 600;
        color: #4B5563;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        border: 1px solid #D1D5DB;
        padding: 10px 14px;
        font-size: 0.95rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(105, 26, 50, 0.15);
    }

    .btn-primary-api {
        background-color: var(--color-primary);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary-api:hover {
        background-color: var(--color-primary-light);
        color: white;
        box-shadow: 0 4px 10px rgba(105, 26, 50, 0.2);
    }

    /* Tables */
    .table-api {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-api th {
        background-color: #F9FAFB;
        color: #374151;
        font-weight: 600;
        border-bottom: 2px solid #E5E7EB;
        padding: 12px 16px;
        font-size: 0.9rem;
    }

    .table-api td {
        padding: 14px 16px;
        border-bottom: 1px solid #F3F4F6;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .table-api tbody tr:hover {
        background-color: #F9FAFB;
    }

    /* Semaforos */
    .semaforo-badge {
        font-weight: 600;
        font-size: 0.8rem;
        padding: 4px 8px;
        border-radius: 12px;
        display: inline-block;
        text-align: center;
    }

    /* Modal styles */
    .modal-xl {
        max-width: 1140px;
    }

    .modal-content-custom {
        border-radius: 16px;
        overflow: hidden;
        border: none;
    }

    .modal-header-custom {
        background: linear-gradient(135deg, var(--color-primary) 0%, #3a0d1b 100%);
        color: white;
        padding: 20px 25px;
    }

    .modal-header-custom .btn-close {
        filter: invert(1) grayscale(1) brightness(2);
    }

    .badge-ods {
        background-color: #FEF3C7;
        color: #92400E;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
        display: inline-block;
        margin-right: 6px;
        margin-bottom: 6px;
        font-size: 0.85rem;
    }
</style>
@endsection

@section('content')
<div class="api-container">

    <!-- Header -->
    <div class="api-header">
        <h1>Documentación e Integración de API</h1>
        <p>Accede en tiempo real a los indicadores oficiales de planeación y desarrollo del Estado de Puebla. Ponemos a tu disposición endpoints REST públicos y una vista de base de datos para integraciones directas.</p>
    </div>

    <!-- Tabs Navigation -->
    <div class="api-tabs">
        <button class="api-tab-btn active" onclick="switchTab('tab-console')">
            <i class="fas fa-terminal me-2"></i>Consola Interactiva
        </button>
        <button class="api-tab-btn" onclick="switchTab('tab-docs')">
            <i class="fas fa-book me-2"></i>Documentación de API
        </button>
        <button class="api-tab-btn" onclick="switchTab('tab-db')">
            <i class="fas fa-database me-2"></i>Vista de Base de Datos
        </button>
    </div>

    <!-- TAB 1: CONSOLE -->
    <div id="tab-console" class="api-tab-content active">
        <div class="row">
            <!-- Panel de Filtros -->
            <div class="col-lg-4">
                <div class="api-card">
                    <h2 class="api-card-title">Filtros de Búsqueda</h2>
                    <form id="console-filter-form" onsubmit="event.preventDefault(); runLiveQuery();">

                        <div class="mb-3 form-group">
                            <label for="filter-buscar">Palabra Clave</label>
                            <input type="text" id="filter-buscar" class="form-control" placeholder="Ej: pobreza, agua, educación...">
                        </div>

                        <div class="mb-3 form-group">
                            <label for="filter-institucion">Institución Responsable</label>
                            <select id="filter-institucion" class="form-select">
                                <option value="">Todas las instituciones</option>
                                @foreach($instituciones as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 form-group">
                            <label for="filter-ods">Objetivo de Desarrollo Sostenible (ODS)</label>
                            <select id="filter-ods" class="form-select">
                                <option value="">Todos los ODS</option>
                                @foreach($ods as $o)
                                <option value="{{ $o->id }}">ODS {{ $o->id }}: {{ $o->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4 form-group">
                            <label for="filter-programa">Programa Derivado</label>
                            <select id="filter-programa" class="form-select">
                                <option value="">Todos los programas</option>
                                @foreach($programasDerivados as $prog)
                                <option value="{{ $prog }}">{{ $prog }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="w-100 btn-primary-api">
                            <i class="fas fa-play me-2"></i>Ejecutar Consulta
                        </button>
                    </form>
                </div>
            </div>

            <!-- Panel de Resultados -->
            <div class="col-lg-8">
                <!-- Request URL preview -->
                <div class="api-card mb-4" style="background-color: #F8FAFC;">
                    <div class="d-flex align-items-center gap-3">
                        <span class="method-badge method-get">GET</span>
                        <div class="url-string flex-grow-1" id="request-url-preview">
                            Cargando...
                        </div>
                    </div>
                </div>

                <!-- Tablas y JSON de Resultados -->
                <div class="api-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="api-card-title mb-0" style="border:none; padding-left:0;">Resultados de la API</h2>
                        <ul class="nav nav-pills" id="results-toggle" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active py-1 px-3" id="pill-table-tab" data-bs-toggle="pill" data-bs-target="#pill-table" type="button" role="tab">Tabla</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-1 px-3" id="pill-json-tab" data-bs-toggle="pill" data-bs-target="#pill-json" type="button" role="tab">JSON</button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content" id="results-tab-content">
                        <!-- Table View -->
                        <div class="tab-pane fade show active" id="pill-table" role="tabpanel">
                            <div class="table-responsive" style="max-height: 500px;">
                                <table class="table table-api" id="results-table">
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
                            <!-- Pagination info -->
                            <div class="d-flex justify-content-between align-items-center mt-3" id="pagination-panel" style="display:none !important;">
                                <span class="text-muted" id="pagination-text"></span>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0" id="pagination-buttons"></ul>
                                </nav>
                            </div>
                        </div>

                        <!-- JSON Raw View -->
                        <div class="tab-pane fade" id="pill-json" role="tabpanel">
                            <div class="code-block-wrapper">
                                <pre class="code-block" id="json-response-preview">{
  "info": "Ejecuta una consulta para ver la respuesta JSON aquí."
}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: TECHNICAL DOCS -->
    <div id="tab-docs" class="api-tab-content">
        <div class="row">
            <div class="col-lg-3">
                <div class="api-card sticky-top" style="top: 20px; z-index: 10;">
                    <h5 class="fw-bold mb-3">Contenido</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#doc-general" class="text-decoration-none text-muted hover-primary">Generalidades</a></li>
                        <li class="mb-2"><a href="#doc-list" class="text-decoration-none text-muted hover-primary">1. Listado de Indicadores</a></li>
                        <li class="mb-2"><a href="#doc-detail" class="text-decoration-none text-muted hover-primary">2. Detalle del Indicador</a></li>
                        <li class="mb-2"><a href="#doc-codes" class="text-decoration-none text-muted hover-primary">Ejemplos de Código</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-9">
                <!-- General Info -->
                <div class="api-card" id="doc-general">
                    <h2 class="api-card-title">Información General</h2>
                    <p>La API de Indicadores del SPED es de acceso público y de solo lectura. No requiere de autenticación (tokens o llaves API) por lo que es de libre integración para tableros ciudadanos, aplicaciones gubernamentales y análisis científico.</p>

                    <div class="alert alert-info py-2" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Importante:</strong> Los datos anuales históricos devueltos por esta API corresponden **únicamente** a los registros que han sido validados oficialmente por los administradores en el sistema central.
                    </div>
                </div>

                <!-- Endpoint List -->
                <div class="api-card" id="doc-list">
                    <h2 class="api-card-title">1. Listado General de Indicadores</h2>
                    <p>Obtiene una lista paginada de todos los indicadores del sistema con sus relaciones de ODS e institución asociada.</p>

                    <div class="mb-3 d-flex align-items-center gap-3">
                        <span class="method-badge method-get">GET</span>
                        <div class="url-string flex-grow-1 api-url-text" data-path="/api/indicadores">Cargando...</div>
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
                                    <td>Filtro de búsqueda por texto en nombre, descripción y temática del indicador.</td>
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
                                    <td>Controla el número de registros por página (mínimo 1, máximo 100, default 15).</td>
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

                <!-- Endpoint Detail -->
                <div class="api-card" id="doc-detail">
                    <h2 class="api-card-title">2. Detalle de un Indicador</h2>
                    <p>Consulta la ficha técnica detallada de un indicador específico, incluyendo su desglose histórico completo de años validados.</p>

                    <div class="mb-3 d-flex align-items-center gap-3">
                        <span class="method-badge method-get">GET</span>
                        <div class="url-string flex-grow-1 api-url-text" data-path="/api/indicadores/{id_or_slug}">Cargando...</div>
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

                <!-- Code Snippets -->
                <div class="api-card" id="doc-codes">
                    <h2 class="api-card-title">Ejemplos de Integración</h2>

                    <ul class="nav nav-tabs mb-3" id="snippet-tabs">
                        <li class="nav-item">
                            <button class="nav-link active" onclick="switchSnippet('snip-js')">JavaScript (Fetch)</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" onclick="switchSnippet('snip-php')">PHP (cURL)</button>
                        </li>
                    </ul>

                    <div id="snip-js" class="snippet-content">
                        <div class="code-block-wrapper">
                            <pre class="code-block"><code>// Ejemplo de consulta de indicadores con filtros desde JS
const url = new URL(window.location.origin + '/api/indicadores');
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

                    <div id="snip-php" class="snippet-content d-none">
                        <div class="code-block-wrapper">
                            <pre class="code-block"><code>&lt;?php
// Ejemplo de consumo en PHP usando cURL
$ch = curl_init();
$url = "http://localhost/api/indicadores?buscar=" . urlencode("pobreza");

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

    <!-- TAB 3: DATABASE VIEW -->
    <div id="tab-db" class="api-tab-content">
        <div class="api-card">
            <h2 class="api-card-title">Vista de SQL en Base de Datos</h2>
            <p>Si eres administrador o tienes acceso directo al motor de base de datos MySQL del SPED, puedes utilizar la vista física precompilada <code>vista_consulta_indicadores</code>. Esta vista recopila la última información de avance validada del indicador en una sola tabla plana, ideal para generar reportes en herramientas de BI como Excel o PowerBI.</p>

            <h5 class="fw-bold mt-4 mb-2">Estructura del Esquema de la Vista</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-sm">
                    <thead>
                        <tr>
                            <th>Campo</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>indicador_id</code></td>
                            <td>int</td>
                            <td>ID del indicador.</td>
                        </tr>
                        <tr>
                            <td><code>indicador_nombre</code></td>
                            <td>varchar</td>
                            <td>Nombre del indicador.</td>
                        </tr>
                        <tr>
                            <td><code>indicador_slug</code></td>
                            <td>varchar</td>
                            <td>Slug del indicador.</td>
                        </tr>
                        <tr>
                            <td><code>indicador_descripcion</code></td>
                            <td>text</td>
                            <td>Descripción del indicador.</td>
                        </tr>
                        <tr>
                            <td><code>programa_derivado</code></td>
                            <td>varchar</td>
                            <td>Programa derivado (ej. Plan Estatal de Desarrollo).</td>
                        </tr>
                        <tr>
                            <td><code>programa</code></td>
                            <td>varchar</td>
                            <td>Eje o programa específico.</td>
                        </tr>
                        <tr>
                            <td><code>tematica</code></td>
                            <td>varchar</td>
                            <td>Temática sectorial.</td>
                        </tr>
                        <tr>
                            <td><code>institucion_nombre</code></td>
                            <td>varchar</td>
                            <td>Nombre de la dependencia responsable.</td>
                        </tr>
                        <tr>
                            <td><code>institucion_titular</code></td>
                            <td>varchar</td>
                            <td>Nombre del titular de la dependencia.</td>
                        </tr>
                        <tr>
                            <td><code>ods_relacionados</code></td>
                            <td>text</td>
                            <td>Nombres de los ODS asociados (separados por comas).</td>
                        </tr>
                        <tr>
                            <td><code>ultimo_anio_validado</code></td>
                            <td>int</td>
                            <td>Año más reciente que cuenta con datos anuales validados.</td>
                        </tr>
                        <tr>
                            <td><code>ultimo_valor_validado</code></td>
                            <td>decimal</td>
                            <td>Valor del dato para el año más reciente validado.</td>
                        </tr>
                        <tr>
                            <td><code>ultimo_resultado_validado</code></td>
                            <td>text</td>
                            <td>Texto de resultados/avance del último año validado.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h5 class="fw-bold mt-5 mb-2">Ejemplos de Consultas Útiles (SQL)</h5>
            <div class="code-block-wrapper">
                <pre class="code-block"><code>-- 1. Consultar todos los indicadores del Plan Estatal de Desarrollo
SELECT * FROM vista_consulta_indicadores 
WHERE programa_derivado = 'Plan Estatal de Desarrollo';

-- 2. Buscar indicadores correspondientes a una Secretaría
SELECT indicador_nombre, ultimo_anio_validado, ultimo_valor_validado 
FROM vista_consulta_indicadores 
WHERE institucion_nombre LIKE '%Bienestar%';

-- 3. Listar indicadores que se relacionan con ODS de 'Salud' o 'Hambre'
SELECT indicador_nombre, ods_relacionados, institucion_nombre 
FROM vista_consulta_indicadores 
WHERE ods_relacionados LIKE '%salud%' OR ods_relacionados LIKE '%hambre%';</code></pre>
            </div>
        </div>
    </div>
</div>

<!-- INDICATOR DETAIL MODAL -->
<div class="modal fade" id="indicatorDetailModal" tabindex="-1" aria-labelledby="indicatorDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content modal-content-custom">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title fw-bold" id="detail-indicator-nombre">Nombre del Indicador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background-color: var(--color-light);">
                <div class="row">
                    <!-- Left: Metadata -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm p-3 mb-3 bg-white">
                            <h6 class="text-muted text-uppercase fw-bold mb-3" style="font-size:0.75rem;">Ficha Descriptiva</h6>
                            <p class="mb-3" id="detail-indicator-descripcion" style="font-size: 0.95rem; line-height: 1.6;"></p>

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

                        <!-- ODS & Institucion -->
                        <div class="card border-0 shadow-sm p-3 bg-white">
                            <h6 class="text-muted text-uppercase fw-bold mb-3" style="font-size:0.75rem;">Responsabilidad y Sostenibilidad</h6>
                            <div class="mb-3">
                                <label class="fw-bold text-muted d-block text-xs mb-1">Institución Responsable</label>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-building text-secondary" style="font-size:1.2rem;"></i>
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

                    <!-- Right: Performance Data -->
                    <div class="col-lg-6">
                        <!-- Semaforo widget -->
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="card border-0 shadow-sm p-3 text-center bg-white h-100">
                                    <span class="text-muted text-xs fw-bold text-uppercase d-block mb-1">Avance Validado</span>
                                    <h3 class="fw-bold mb-0 text-primary" id="detail-indicator-avance">0%</h3>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card border-0 shadow-sm p-3 text-center bg-white h-100">
                                    <span class="text-muted text-xs fw-bold text-uppercase d-block mb-1">Semáforo de Desempeño</span>
                                    <div>
                                        <span class="semaforo-badge" id="detail-indicator-semaforo">N/A</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Datos Anuales Table -->
                        <div class="card border-0 shadow-sm p-3 bg-white">
                            <h6 class="text-muted text-uppercase fw-bold mb-3" style="font-size:0.75rem;">Historial de Datos Anuales Validados</h6>
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
            <div class="modal-footer border-0 p-3" style="background-color: #E5E7EB;">
                <button type="button" class="btn btn-secondary py-2 px-4 rounded-8" data-bs-dismiss="modal">Cerrar Detalle</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('jss-final')
<script>
    // Tab switching logic
    function switchTab(tabId) {
        document.querySelectorAll('.api-tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.api-tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        document.getElementById(tabId).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    // Code Snippet toggle
    function switchSnippet(snippetId) {
        document.querySelectorAll('.snippet-content').forEach(block => {
            block.classList.add('d-none');
        });
        document.getElementById(snippetId).classList.remove('d-none');

        // Active styling for tabs
        const tabList = event.currentTarget.closest('.nav');
        tabList.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        event.currentTarget.classList.add('active');
    }

    // Helper to build same-origin API URLs dynamically to prevent CORS issues
    function getApiUrl(path = '/api/indicadores') {
        const currentPath = window.location.pathname;
        const docsRoute = '/docs/api-indicadores';
        let basePath = '';
        if (currentPath.endsWith(docsRoute)) {
            basePath = currentPath.substring(0, currentPath.length - docsRoute.length);
        }
        return window.location.origin + basePath + path;
    }

    // Generate Request URL based on filters
    function buildRequestUrl() {
        const baseUrl = getApiUrl('/api/indicadores');
        const params = new URLSearchParams();

        const buscar = document.getElementById('filter-buscar').value.trim();
        const institucionId = document.getElementById('filter-institucion').value;
        const odsId = document.getElementById('filter-ods').value;
        const programa = document.getElementById('filter-programa').value;

        if (buscar) params.append('buscar', buscar);
        if (institucionId) params.append('institucion_id', institucionId);
        if (odsId) params.append('ods_id', odsId);
        if (programa) params.append('programa_derivado', programa);

        const queryString = params.toString();
        return queryString ? `${baseUrl}?${queryString}` : baseUrl;
    }

    // Event listener for filter changes to update request preview live
    document.querySelectorAll('#console-filter-form input, #console-filter-form select').forEach(element => {
        element.addEventListener('change', () => {
            document.getElementById('request-url-preview').textContent = buildRequestUrl();
        });
        element.addEventListener('input', () => {
            document.getElementById('request-url-preview').textContent = buildRequestUrl();
        });
    });

    // Run query in console
    function runLiveQuery(pageUrl = null) {
        const queryUrl = pageUrl || buildRequestUrl();

        // Show loading state
        const tableBody = document.getElementById('results-table-body');
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Consultando API...</span>
                    </div>
                    <span class="ms-3 text-muted">Consultando API en tiempo real...</span>
                </td>
            </tr>
        `;

        fetch(queryUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Respuesta de red no satisfactoria: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                // Update JSON Raw preview
                document.getElementById('json-response-preview').textContent = JSON.stringify(data, null, 2);

                // Populate Table
                tableBody.innerHTML = '';
                if (data.success && data.data.length > 0) {
                    data.data.forEach(indicador => {
                        // Semáforo colors mapping
                        let semClass = 'bg-secondary text-white';
                        let semText = indicador.semaforo_real_time || 'N/A';

                        const lowerSem = semText.toLowerCase();
                        if (lowerSem.includes('aceptable') || lowerSem.includes('excedido') || lowerSem.includes('cumplido') || lowerSem.includes('meta alcanzada')) {
                            semClass = 'bg-success text-white';
                        } else if (lowerSem.includes('moderado') || lowerSem.includes('preventivo')) {
                            semClass = 'bg-warning text-dark';
                        } else if (lowerSem.includes('insuficiente') || lowerSem.includes('crítico')) {
                            semClass = 'bg-danger text-white';
                        }

                        const instNombre = indicador.institucion ? indicador.institucion.nombre : '<span class="text-muted">Sin asignar</span>';
                        const avanceVal = indicador.avance_real_time !== null ? `${parseFloat(indicador.avance_real_time).toFixed(2)}%` : 'N/A';

                        tableBody.innerHTML += `
                        <tr>
                            <td class="fw-bold">${indicador.nombre}</td>
                            <td class="text-sm">${instNombre}</td>
                            <td><span class="semaforo-badge ${semClass}">${semText}</span></td>
                            <td class="font-monospace fw-bold">${avanceVal}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="showIndicatorDetail('${indicador.id}')">
                                    <i class="fas fa-eye"></i> Detalle
                                </button>
                            </td>
                        </tr>
                    `;
                    });

                    // Setup Pagination
                    setupPagination(data);
                } else {
                    tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-search-minus fa-2x mb-2 d-block"></i>
                            No se encontraron indicadores con los filtros seleccionados.
                        </td>
                    </tr>
                `;
                    document.getElementById('pagination-panel').style.setProperty('display', 'none', 'important');
                }
            })
            .catch(error => {
                tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>
                        Error al cargar la información: ${error.message}
                    </td>
                </tr>
            `;
                document.getElementById('pagination-panel').style.setProperty('display', 'none', 'important');
                document.getElementById('json-response-preview').textContent = JSON.stringify({
                    "error": true,
                    "message": error.message
                }, null, 2);
            });
    }

    // Build pagination buttons
    function setupPagination(data) {
        const panel = document.getElementById('pagination-panel');
        const text = document.getElementById('pagination-text');
        const list = document.getElementById('pagination-buttons');

        text.textContent = `Mostrando página ${data.current_page} de ${data.last_page} (Total: ${data.total} indicadores)`;
        list.innerHTML = '';

        // Generate URL helper for pagination
        const getPagedUrl = (pageNumber) => {
            const currentUrl = new URL(buildRequestUrl());
            currentUrl.searchParams.set('page', pageNumber);
            return currentUrl.toString();
        };

        // Prev Button
        if (data.current_page > 1) {
            list.innerHTML += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); runLiveQuery('${getPagedUrl(data.current_page - 1)}')">&laquo;</a></li>`;
        } else {
            list.innerHTML += `<li class="page-item disabled"><span class="page-link">&laquo;</span></li>`;
        }

        // Numerical Pages
        const maxPagesToShow = 5;
        let startPage = Math.max(1, data.current_page - 2);
        let endPage = Math.min(data.last_page, startPage + maxPagesToShow - 1);

        if (endPage - startPage < maxPagesToShow - 1) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === data.current_page ? 'active' : '';
            list.innerHTML += `<li class="page-item ${activeClass}"><a class="page-link" href="#" onclick="event.preventDefault(); runLiveQuery('${getPagedUrl(i)}')">${i}</a></li>`;
        }

        // Next Button
        if (data.current_page < data.last_page) {
            list.innerHTML += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); runLiveQuery('${getPagedUrl(data.current_page + 1)}')">&raquo;</a></li>`;
        } else {
            list.innerHTML += `<li class="page-item disabled"><span class="page-link">&raquo;</span></li>`;
        }

        panel.style.setProperty('display', 'flex', 'important');
    }

    // Show Detail Modal
    function showIndicatorDetail(id) {
        const detailUrl = `${getApiUrl('/api/indicadores')}/${id}`;

        // Setup placeholders
        document.getElementById('detail-indicator-nombre').textContent = 'Cargando...';
        document.getElementById('detail-indicator-descripcion').textContent = '';
        document.getElementById('detail-indicator-programa-derivado').textContent = '';
        document.getElementById('detail-indicator-programa').textContent = '';
        document.getElementById('detail-indicator-tematica').textContent = '';
        document.getElementById('detail-indicator-unidad').textContent = '';
        document.getElementById('detail-indicator-formula').textContent = '';
        document.getElementById('detail-indicator-cobertura').textContent = '';
        document.getElementById('detail-indicator-periodicidad').textContent = '';
        document.getElementById('detail-indicator-tendencia').textContent = '';
        document.getElementById('detail-indicator-fuente').textContent = '';
        document.getElementById('detail-indicator-inst-nombre').textContent = '';
        document.getElementById('detail-indicator-inst-titular').textContent = '';
        document.getElementById('detail-indicator-ods-list').innerHTML = '';
        document.getElementById('detail-indicator-avance').textContent = '0%';

        const semBadge = document.getElementById('detail-indicator-semaforo');
        semBadge.textContent = 'N/A';
        semBadge.className = 'semaforo-badge bg-secondary text-white';

        const tableBody = document.getElementById('detail-indicator-datos-anuales-body');
        tableBody.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-muted">Cargando datos...</td></tr>`;

        // Fetch detail
        fetch(detailUrl, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    const ind = res.data;

                    document.getElementById('detail-indicator-nombre').textContent = ind.nombre;
                    document.getElementById('detail-indicator-descripcion').textContent = ind.descripcion || 'Sin descripción descriptiva.';
                    document.getElementById('detail-indicator-programa-derivado').textContent = ind.programa_derivado || 'No especificado';
                    document.getElementById('detail-indicator-programa').textContent = ind.programa || 'No especificado';
                    document.getElementById('detail-indicator-tematica').textContent = ind.tematica || 'No especificado';
                    document.getElementById('detail-indicator-unidad').textContent = ind.unidad_medida || 'No especificado';
                    document.getElementById('detail-indicator-formula').textContent = ind.formula || 'No especificado';
                    document.getElementById('detail-indicator-cobertura').textContent = ind.cobertura || 'No especificada';
                    document.getElementById('detail-indicator-periodicidad').textContent = ind.periodicidad || 'No especificada';
                    document.getElementById('detail-indicator-tendencia').textContent = ind.tendencia || 'No especificada';
                    document.getElementById('detail-indicator-fuente').textContent = ind.fuente || 'No especificada';

                    // Institution info
                    if (ind.institucion) {
                        document.getElementById('detail-indicator-inst-nombre').textContent = ind.institucion.nombre;
                        document.getElementById('detail-indicator-inst-titular').textContent = `Titular: ${ind.institucion.titular || 'Sin registrar'}`;
                    } else {
                        document.getElementById('detail-indicator-inst-nombre').textContent = 'Sin institución asignada';
                        document.getElementById('detail-indicator-inst-titular').textContent = '';
                    }

                    // ODS list
                    const odsContainer = document.getElementById('detail-indicator-ods-list');
                    if (ind.ods && ind.ods.length > 0) {
                        ind.ods.forEach(o => {
                            odsContainer.innerHTML += `<span class="badge-ods" title="${o.nombre}">ODS ${o.id}: ${o.nombre}</span>`;
                        });
                    } else {
                        odsContainer.innerHTML = '<span class="text-muted text-sm">Sin relación directa registrada con ODS</span>';
                    }

                    // Performance data
                    const avanceVal = ind.avance_real_time !== null ? `${parseFloat(ind.avance_real_time).toFixed(2)}%` : 'N/A';
                    document.getElementById('detail-indicator-avance').textContent = avanceVal;

                    // Semaphore badge
                    const semText = ind.semaforo_real_time || 'N/A';
                    semBadge.textContent = semText;

                    let semClass = 'bg-secondary text-white';
                    const lowerSem = semText.toLowerCase();
                    if (lowerSem.includes('aceptable') || lowerSem.includes('excedido') || lowerSem.includes('cumplido') || lowerSem.includes('meta alcanzada')) {
                        semClass = 'bg-success text-white';
                    } else if (lowerSem.includes('moderado') || lowerSem.includes('preventivo')) {
                        semClass = 'bg-warning text-dark';
                    } else if (lowerSem.includes('insuficiente') || lowerSem.includes('crítico')) {
                        semClass = 'bg-danger text-white';
                    }
                    semBadge.className = `semaforo-badge ${semClass} px-3 py-2 text-sm`;

                    // Populating historical data table
                    tableBody.innerHTML = '';
                    if (ind.datos_anuales && ind.datos_anuales.length > 0) {
                        ind.datos_anuales.forEach(da => {
                            const valor = da.valor_dato !== null ? parseFloat(da.valor_dato).toFixed(2) : 'Sin capturar';
                            const resultados = da.resultados || '<span class="text-muted">Sin logros descritos</span>';

                            tableBody.innerHTML += `
                            <tr>
                                <td class="fw-bold font-monospace">${da.anio}</td>
                                <td class="font-monospace fw-bold">${valor}</td>
                                <td class="text-sm">${resultados}</td>
                            </tr>
                        `;
                        });
                    } else {
                        tableBody.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-muted">No se registran datos anuales validados para este indicador.</td></tr>`;
                    }

                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('indicatorDetailModal'));
                    modal.show();
                } else {
                    alert('No se pudo cargar la información del indicador.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error al realizar la consulta del indicador.');
            });
    }

    // Run first query automatically on page load & populate dynamic documentation URLs
    document.addEventListener("DOMContentLoaded", function() {
        // Populate request URL preview initial text
        document.getElementById('request-url-preview').textContent = buildRequestUrl();

        // Populate doc static blocks
        document.querySelectorAll('.api-url-text').forEach(el => {
            const path = el.getAttribute('data-path') || '/api/indicadores';
            el.textContent = getApiUrl(path);
        });

        runLiveQuery();
    });
</script>
@endsection
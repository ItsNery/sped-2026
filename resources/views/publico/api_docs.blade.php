@extends('layouts.plantilla')

@section('title', 'Documentación y Consola de API de Indicadores')
@section('meta-description', 'Página pública de documentación, consola de pruebas interactiva y consulta en tiempo real de la API de indicadores del SPED del Estado de Puebla.')

@section('css')
@endsection

@section('content')
    <section class="container api-container">

        <!-- Header -->
        <div class="api-header">
            <h1>
                Documentación para el uso de la API
            </h1>
            <p>
                Accede en tiempo real a los indicadores de este portal, para lo cual ponemos a tu disposición este endpoint
                REST público.
            </p>
        </div>
        <div class="api-tabs nav" id="apiMainTabs" role="tablist">
            <button class="api-tab-btn nav-link active" id="console-tab" data-bs-toggle="tab" data-bs-target="#tab-console"
                type="button" role="tab" aria-controls="tab-console" aria-selected="true">
                Consola Interactiva
            </button>
            <button class="api-tab-btn nav-link" id="docs-tab" data-bs-toggle="tab" data-bs-target="#tab-docs" type="button"
                role="tab" aria-controls="tab-docs" aria-selected="false">
                Documentación de API
            </button>
        </div>

        <div class="tab-content" id="apiMainTabsContent">

            <div id="tab-console" class="api-tab-content tab-pane fade show active" role="tabpanel"
                aria-labelledby="console-tab">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="api-card">
                            <h2 class="api-card-title">Filtros de Búsqueda</h2>
                            <form id="console-filter-form" onsubmit="event.preventDefault(); runLiveQuery();">

                                <div class="mb-3 form-group">
                                    <label for="filter-buscar">Palabra Clave</label>
                                    <input type="text" id="filter-buscar" class="form-control"
                                        placeholder="Ej: pobreza, agua, educación...">
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

                    <div class="col-lg-8">
                        <div class="api-card mb-4" style="background-color: #F8FAFC;">
                            <div class="d-flex align-items-center gap-3">
                                <span class="method-badge method-get">GET</span>
                                <div class="url-string flex-grow-1" id="request-url-preview">
                                    Cargando...
                                </div>
                            </div>
                        </div>

                        <div class="api-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="api-card-title mb-0" style="border:none; padding-left:0;">Resultados de la API
                                </h2>
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
                                <div class="tab-pane fade show active" id="pill-table" role="tabpanel">
                                    <div class="table-responsive" style="max-height: 500px;">
                                        <table class="table table-api" id="results-table">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Institución</th>
                                                    <th>Línea Base</th>
                                                    <th>Último dato</th>
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
                                    <div class="d-flex justify-content-between align-items-center mt-3"
                                        id="pagination-panel" style="display:none !important;">
                                        <span class="text-muted" id="pagination-text"></span>
                                        <nav>
                                            <ul class="pagination pagination-sm mb-0" id="pagination-buttons"></ul>
                                        </nav>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="pill-json" role="tabpanel">
                                    <div class="code-block-wrapper">
                                        <pre class="code-block" id="json-response-preview">
                                            {
                                                "info": "Ejecuta una consulta para ver la respuesta JSON aquí."
                                            }
                                        </pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="tab-docs" class="api-tab-content tab-pane fade" role="tabpanel" aria-labelledby="docs-tab">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="api-card sticky-top">
                            <h5 class="fw-bold mb-3">Contenido</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="#doc-general"
                                        class="text-decoration-none text-muted hover-primary">Generalidades</a></li>
                                <li class="mb-2"><a href="#doc-list"
                                        class="text-decoration-none text-muted hover-primary">1.
                                        Listado de Indicadores</a></li>
                                <li class="mb-2"><a href="#doc-detail"
                                        class="text-decoration-none text-muted hover-primary">2.
                                        Detalle del Indicador</a></li>
                                <li class="mb-2"><a href="#doc-codes"
                                        class="text-decoration-none text-muted hover-primary">Ejemplos de Código</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-9">
                        <div class="api-card" id="doc-general">
                            <h2 class="api-card-title">Información General</h2>
                            <p>
                                La API de Indicadores del SPED es de acceso público y de solo lectura. No requiere de
                                autenticación (tokens o llaves API) por lo que es de libre integración para tableros,
                                aplicaciones gubernamentales y análisis científico.
                            </p>

                            <div class="alert alert-info py-2" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Importante:</strong> Los datos anuales históricos devueltos por esta API
                                corresponden *únicamente** a los registros que han sido validados oficialmente por los administradores
                                en el sistema central.
                            </div>
                        </div>

                        <div class="api-card" id="doc-list">
                            <h2 class="api-card-title">
                                1. Listado General de Indicadores
                            </h2>
                            <p>
                                Obtiene una lista paginada de todos los indicadores del sistema con sus relaciones de ODS e
                                institución asociada.
                            </p>

                            <div class="mb-3 d-flex align-items-center gap-3">
                                <span class="method-badge method-get">GET</span>
                                <div class="url-string flex-grow-1 api-url-text" data-path="/api/indicadores">Cargando...
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
                                            <td>Filtro de búsqueda por texto en nombre, descripción y temática del
                                                indicador.
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
                                            <td>Filtra los indicadores asociados a un Objetivo de Desarrollo Sostenible.
                                            </td>
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
                                            <td>Controla el número de registros por página (mínimo 1, máximo 100, default
                                                15).
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

                        <div class="api-card" id="doc-detail">
                            <h2 class="api-card-title">2. Detalle de un Indicador</h2>
                            <p>Consulta la ficha técnica detallada de un indicador específico, incluyendo su desglose
                                histórico
                                completo de años validados.</p>

                            <div class="mb-3 d-flex align-items-center gap-3">
                                <span class="method-badge method-get">GET</span>
                                <div class="url-string flex-grow-1 api-url-text" data-path="/api/indicadores/{id_or_slug}">
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

                        <div class="api-card" id="doc-codes">
                            <h2 class="api-card-title">Ejemplos de Integración</h2>

                            <ul class="nav nav-tabs mb-3" id="snippet-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="snip-js-tab" data-bs-toggle="tab"
                                        data-bs-target="#snip-js" type="button" role="tab" aria-selected="true">JavaScript
                                        (Fetch)</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="snip-php-tab" data-bs-toggle="tab"
                                        data-bs-target="#snip-php" type="button" role="tab" aria-selected="false">PHP
                                        (cURL)</button>
                                </li>
                            </ul>

                            <div class="tab-content" id="snippet-tabs-content">
                                <div id="snip-js" class="snippet-content tab-pane fade show active" role="tabpanel"
                                    aria-labelledby="snip-js-tab">
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

                                <div id="snip-php" class="snippet-content tab-pane fade" role="tabpanel"
                                    aria-labelledby="snip-php-tab">
                                    <div class="code-block-wrapper">
                                        <pre class="code-block">
                                            <code>
                                                &lt;?php
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
                                                ?&gt;
                                            </code>
                                        </pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Helper to build same-origin API URLs dynamically to prevent CORS issues
        function getApiUrl(path = '/api/indicadores') {
            const currentPath = window.location.pathname;
            const docsRoute = '/informacion-general/api';
            let basePath = '';
            if (currentPath.endsWith(docsRoute)) {
                basePath = currentPath.substring(0, currentPath.length - docsRoute.length);
            }
            return window.location.origin + basePath + path;
        }

        function getDetailPageUrl(id) {
            const currentPath = window.location.pathname;
            const docsRoute = '/informacion-general/api';
            let basePath = '';
            if (currentPath.endsWith(docsRoute)) {
                basePath = currentPath.substring(0, currentPath.length - docsRoute.length);
            }
            return window.location.origin + basePath + '/informacion-general/api/indicador/' + encodeURIComponent(id);
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
                            const instNombre = indicador.institucion ? indicador.institucion.nombre : '<span class="text-muted">Sin asignar</span>';

                            const lineaBaseRaw = indicador.dato_linea_base !== null && indicador.dato_linea_base !== undefined ? indicador.dato_linea_base : 'N/A';
                            const lineaBaseValue = !isNaN(parseFloat(lineaBaseRaw)) ? parseFloat(lineaBaseRaw).toFixed(2) : lineaBaseRaw;
                            const lineaBaseYear = indicador.linea_base ? ` (${indicador.linea_base})` : '';
                            const lineaBaseText = `${lineaBaseValue}${lineaBaseYear}`;

                            const ultimoDatoRaw = indicador.ultimo_dato_validado !== null && indicador.ultimo_dato_validado !== undefined ? indicador.ultimo_dato_validado : 'N/A';
                            const ultimoDatoValue = !isNaN(parseFloat(ultimoDatoRaw)) ? parseFloat(ultimoDatoRaw).toFixed(2) : ultimoDatoRaw;
                            const ultimoDatoYear = indicador.anio_ultimo_dato_validado ? ` (${indicador.anio_ultimo_dato_validado})` : '';
                            const ultimoDatoText = `${ultimoDatoValue}${ultimoDatoYear}`;

                            tableBody.innerHTML += `
                                                                        <tr>
                                                                            <td class="fw-bold">${indicador.nombre}</td>
                                                                            <td class="text-sm">${instNombre}</td>
                                                                            <td class="font-monospace fw-bold">${lineaBaseText}</td>
                                                                            <td class="font-monospace fw-bold">${ultimoDatoText}</td>
                                                                            <td>
                                                                                <a href="${getDetailPageUrl(indicador.id)}" class="btn btn-sm btn-outline-primary">
                                                                                    <i class="fas fa-eye"></i> Ver detalle
                                                                                </a>
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

        // Run first query automatically on page load & populate dynamic documentation URLs
        document.addEventListener("DOMContentLoaded", function () {
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
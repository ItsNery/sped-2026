/**
 * Javascript for API Documentation & Interactive Console
 */

document.addEventListener("DOMContentLoaded", function() {
    // === INITIALIZATION ===
    
    // Set up request URL preview initial text and documentation paths
    updateRequestUrlPreview();
    initializeDocPaths();
    
    // Auto-run first query on page load
    runLiveQuery();

    // === EVENT LISTENERS ===

    // Main Tab switching logic (event delegation)
    const tabsContainer = document.querySelector(".api-docs__tabs");
    if (tabsContainer) {
        tabsContainer.addEventListener("click", function(event) {
            const btn = event.target.closest("[data-tab]");
            if (btn) {
                const tabId = btn.getAttribute("data-tab");
                switchTab(tabId, btn);
            }
        });
    }

    // Code Snippet toggle (event delegation)
    const snippetTabs = document.getElementById("snippet-tabs");
    if (snippetTabs) {
        snippetTabs.addEventListener("click", function(event) {
            const btn = event.target.closest("[data-snippet-tab]");
            if (btn) {
                const snippetId = btn.getAttribute("data-snippet-tab");
                switchSnippet(snippetId, btn);
            }
        });
    }

    // Live URL preview on form changes
    const filterForm = document.getElementById("console-filter-form");
    if (filterForm) {
        // Prevent form default action on submit and run query
        filterForm.addEventListener("submit", function(event) {
            event.preventDefault();
            runLiveQuery();
        });

        // Watch inputs to update preview dynamically
        filterForm.querySelectorAll("input, select").forEach(element => {
            element.addEventListener("change", updateRequestUrlPreview);
            element.addEventListener("input", updateRequestUrlPreview);
        });
    }

    // Table detail buttons delegation
    const tableBody = document.getElementById("results-table-body");
    if (tableBody) {
        tableBody.addEventListener("click", function(event) {
            const detailBtn = event.target.closest("[data-detail-id]");
            if (detailBtn) {
                event.preventDefault();
                const indicatorId = detailBtn.getAttribute("data-detail-id");
                showIndicatorDetail(indicatorId);
            }
        });
    }

    // Pagination buttons delegation
    const paginationButtons = document.getElementById("pagination-buttons");
    if (paginationButtons) {
        paginationButtons.addEventListener("click", function(event) {
            const pageLink = event.target.closest("[data-page-url]");
            if (pageLink) {
                event.preventDefault();
                const targetUrl = pageLink.getAttribute("data-page-url");
                runLiveQuery(targetUrl);
            }
        });
    }
});

// === FUNCTIONS ===

/**
 * Switch active main tabs
 */
function switchTab(tabId, buttonElement) {
    document.querySelectorAll(".api-docs__tab-content").forEach(tab => {
        tab.classList.remove("api-docs__tab-content--active");
    });
    document.querySelectorAll(".api-docs__tab-btn").forEach(btn => {
        btn.classList.remove("api-docs__tab-btn--active");
    });

    const targetTab = document.getElementById(tabId);
    if (targetTab) {
        targetTab.classList.add("api-docs__tab-content--active");
    }
    buttonElement.classList.add("api-docs__tab-btn--active");
}

/**
 * Switch active code snippet tab
 */
function switchSnippet(snippetId, buttonElement) {
    document.querySelectorAll(".api-docs__snippet-content").forEach(block => {
        block.classList.add("d-none");
    });
    
    const targetSnippet = document.getElementById(snippetId);
    if (targetSnippet) {
        targetSnippet.classList.remove("d-none");
    }

    const tabList = buttonElement.closest(".nav");
    if (tabList) {
        tabList.querySelectorAll(".nav-link").forEach(link => {
            link.classList.remove("active");
        });
    }
    buttonElement.classList.add("active");
}

/**
 * Get the application's base API URL dynamically to avoid CORS issues
 */
function getApiUrl(path = "/api/indicadores") {
    const currentPath = window.location.pathname;
    const docsRoute = "/docs/api-indicadores";
    let basePath = "";
    
    // Check if the current route has subfolders and strip them accordingly
    if (currentPath.endsWith(docsRoute)) {
        basePath = currentPath.substring(0, currentPath.length - docsRoute.length);
    } else if (currentPath.includes("/informacion-general/api")) {
        basePath = currentPath.substring(0, currentPath.indexOf("/informacion-general/api"));
    }
    
    return window.location.origin + basePath + path;
}

/**
 * Generate full Request URL based on filters
 */
function buildRequestUrl() {
    const baseUrl = getApiUrl("/api/indicadores");
    const params = new URLSearchParams();

    const buscarInput = document.getElementById("filter-buscar");
    const instSelect = document.getElementById("filter-institucion");
    const odsSelect = document.getElementById("filter-ods");
    const progSelect = document.getElementById("filter-programa");

    const buscar = buscarInput ? buscarInput.value.trim() : "";
    const institucionId = instSelect ? instSelect.value : "";
    const odsId = odsSelect ? odsSelect.value : "";
    const programa = progSelect ? progSelect.value : "";

    if (buscar) params.append("buscar", buscar);
    if (institucionId) params.append("institucion_id", institucionId);
    if (odsId) params.append("ods_id", odsId);
    if (programa) params.append("programa_derivado", programa);

    const queryString = params.toString();
    return queryString ? `${baseUrl}?${queryString}` : baseUrl;
}

/**
 * Update the UI preview for the target Request URL
 */
function updateRequestUrlPreview() {
    const previewEl = document.getElementById("request-url-preview");
    if (previewEl) {
        previewEl.textContent = buildRequestUrl();
    }
}

/**
 * Populate dynamic API path names in documentation sections
 */
function initializeDocPaths() {
    document.querySelectorAll(".api-url-text").forEach(el => {
        const path = el.getAttribute("data-path") || "/api/indicadores";
        el.textContent = getApiUrl(path);
    });
}

/**
 * Query indicators database in real-time
 */
function runLiveQuery(pageUrl = null) {
    const queryUrl = pageUrl || buildRequestUrl();

    // Show loading state in results table
    const tableBody = document.getElementById("results-table-body");
    if (!tableBody) return;

    tableBody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center py-5">
                <div class="spinner-border text-danger" role="status">
                    <span class="visually-hidden">Consultando API...</span>
                </div>
                <span class="ms-3 text-muted">Consultando API en tiempo real...</span>
            </td>
        </tr>
    `;

    fetch(queryUrl, {
            method: "GET",
            headers: {
                "Accept": "application/json"
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error("Respuesta de red no satisfactoria: " + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            // Update Raw JSON preview
            const jsonPreview = document.getElementById("json-response-preview");
            if (jsonPreview) {
                jsonPreview.textContent = JSON.stringify(data, null, 2);
            }

            // Populate Table
            tableBody.innerHTML = "";
            if (data.success && data.data && data.data.length > 0) {
                data.data.forEach(indicador => {
                    // Semaphore modifier configuration
                    let semClass = "api-docs__semaforo--secondary";
                    let semText = indicador.semaforo_real_time || "N/A";

                    const lowerSem = semText.toLowerCase();
                    if (lowerSem.includes("aceptable") || lowerSem.includes("excedido") || lowerSem.includes("cumplido") || lowerSem.includes("meta alcanzada")) {
                        semClass = "bg-success text-white";
                    } else if (lowerSem.includes("moderado") || lowerSem.includes("preventivo")) {
                        semClass = "bg-warning text-dark";
                    } else if (lowerSem.includes("insuficiente") || lowerSem.includes("crítico")) {
                        semClass = "bg-danger text-white";
                    }

                    const instNombre = indicador.institucion ? indicador.institucion.nombre : '<span class="text-muted">Sin asignar</span>';
                    const avanceVal = indicador.avance_real_time !== null ? `${parseFloat(indicador.avance_real_time).toFixed(2)}%` : "N/A";

                    tableBody.innerHTML += `
                        <tr class="api-docs__table-row--hover">
                            <td class="fw-bold">${indicador.nombre}</td>
                            <td class="text-sm">${instNombre}</td>
                            <td><span class="api-docs__semaforo ${semClass}">${semText}</span></td>
                            <td class="font-monospace fw-bold">${avanceVal}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-detail-id="${indicador.id}">
                                    <i class="fas fa-eye"></i> Detalle
                                </button>
                            </td>
                        </tr>
                    `;
                });

                // Set up pagination panel
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
                const pagPanel = document.getElementById("pagination-panel");
                if (pagPanel) {
                    pagPanel.classList.add("api-docs__pagination-panel--hidden");
                }
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
            const pagPanel = document.getElementById("pagination-panel");
            if (pagPanel) {
                pagPanel.classList.add("api-docs__pagination-panel--hidden");
            }
            
            const jsonPreview = document.getElementById("json-response-preview");
            if (jsonPreview) {
                jsonPreview.textContent = JSON.stringify({
                    "error": true,
                    "message": error.message
                }, null, 2);
            }
        });
}

/**
 * Configure pagination buttons in interactive console
 */
function setupPagination(data) {
    const panel = document.getElementById("pagination-panel");
    const text = document.getElementById("pagination-text");
    const list = document.getElementById("pagination-buttons");

    if (!panel || !text || !list) return;

    text.textContent = `Mostrando página ${data.current_page} de ${data.last_page} (Total: ${data.total} indicadores)`;
    list.innerHTML = "";

    // URL generator helper for pages
    const getPagedUrl = (pageNumber) => {
        const currentUrl = new URL(buildRequestUrl());
        currentUrl.searchParams.set("page", pageNumber);
        return currentUrl.toString();
    };

    // Prev Button
    if (data.current_page > 1) {
        list.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page-url="${getPagedUrl(data.current_page - 1)}">&laquo;</a></li>`;
    } else {
        list.innerHTML += `<li class="page-item disabled"><span class="page-link">&laquo;</span></li>`;
    }

    // Numerical Buttons (maximum 5)
    const maxPagesToShow = 5;
    let startPage = Math.max(1, data.current_page - 2);
    let endPage = Math.min(data.last_page, startPage + maxPagesToShow - 1);

    if (endPage - startPage < maxPagesToShow - 1) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === data.current_page ? "active" : "";
        list.innerHTML += `<li class="page-item ${activeClass}"><a class="page-link" href="#" data-page-url="${getPagedUrl(i)}">${i}</a></li>`;
    }

    // Next Button
    if (data.current_page < data.last_page) {
        list.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page-url="${getPagedUrl(data.current_page + 1)}">&raquo;</a></li>`;
    } else {
        list.innerHTML += `<li class="page-item disabled"><span class="page-link">&raquo;</span></li>`;
    }

    panel.classList.remove("api-docs__pagination-panel--hidden");
    panel.style.setProperty("display", "flex", "important");
}

/**
 * Fetch and display details of a single indicator in modal
 */
function showIndicatorDetail(id) {
    const detailUrl = `${getApiUrl("/api/indicadores")}/${id}`;

    // Placeholders
    document.getElementById("detail-indicator-nombre").textContent = "Cargando...";
    document.getElementById("detail-indicator-descripcion").textContent = "";
    document.getElementById("detail-indicator-programa-derivado").textContent = "";
    document.getElementById("detail-indicator-programa").textContent = "";
    document.getElementById("detail-indicator-tematica").textContent = "";
    document.getElementById("detail-indicator-unidad").textContent = "";
    document.getElementById("detail-indicator-formula").textContent = "";
    document.getElementById("detail-indicator-cobertura").textContent = "";
    document.getElementById("detail-indicator-periodicidad").textContent = "";
    document.getElementById("detail-indicator-tendencia").textContent = "";
    document.getElementById("detail-indicator-fuente").textContent = "";
    document.getElementById("detail-indicator-inst-nombre").textContent = "";
    document.getElementById("detail-indicator-inst-titular").textContent = "";
    document.getElementById("detail-indicator-ods-list").innerHTML = "";
    document.getElementById("detail-indicator-avance").textContent = "0%";

    const semBadge = document.getElementById("detail-indicator-semaforo");
    if (semBadge) {
        semBadge.textContent = "N/A";
        semBadge.className = "api-docs__semaforo api-docs__semaforo--secondary";
    }

    const tableBody = document.getElementById("detail-indicator-datos-anuales-body");
    if (tableBody) {
        tableBody.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-muted">Cargando datos...</td></tr>`;
    }

    // Request detail
    fetch(detailUrl, {
            headers: {
                "Accept": "application/json"
            }
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const ind = res.data;

                document.getElementById("detail-indicator-nombre").textContent = ind.nombre;
                document.getElementById("detail-indicator-descripcion").textContent = ind.descripcion || "Sin descripción descriptiva.";
                document.getElementById("detail-indicator-programa-derivado").textContent = ind.programa_derivado || "No especificado";
                document.getElementById("detail-indicator-programa").textContent = ind.programa || "No especificado";
                document.getElementById("detail-indicator-tematica").textContent = ind.tematica || "No especificado";
                document.getElementById("detail-indicator-unidad").textContent = ind.unidad_medida || "No especificado";
                document.getElementById("detail-indicator-formula").textContent = ind.formula || "No especificado";
                document.getElementById("detail-indicator-cobertura").textContent = ind.cobertura || "No especificada";
                document.getElementById("detail-indicator-periodicidad").textContent = ind.periodicidad || "No especificada";
                document.getElementById("detail-indicator-tendencia").textContent = ind.tendencia || "No especificada";
                document.getElementById("detail-indicator-fuente").textContent = ind.fuente || "No especificada";

                // Institution info
                const instNombreEl = document.getElementById("detail-indicator-inst-nombre");
                const instTitularEl = document.getElementById("detail-indicator-inst-titular");
                if (ind.institucion) {
                    if (instNombreEl) instNombreEl.textContent = ind.institucion.nombre;
                    if (instTitularEl) instTitularEl.textContent = `Titular: ${ind.institucion.titular || "Sin registrar"}`;
                } else {
                    if (instNombreEl) instNombreEl.textContent = "Sin institución asignada";
                    if (instTitularEl) instTitularEl.textContent = "";
                }

                // ODS links
                const odsContainer = document.getElementById("detail-indicator-ods-list");
                if (odsContainer) {
                    if (ind.ods && ind.ods.length > 0) {
                        ind.ods.forEach(o => {
                            odsContainer.innerHTML += `<span class="api-docs__badge-ods" title="${o.nombre}">ODS ${o.id}: ${o.nombre}</span>`;
                        });
                    } else {
                        odsContainer.innerHTML = '<span class="text-muted text-sm">Sin relación directa registrada con ODS</span>';
                    }
                }

                // Overall progress
                const avanceVal = ind.avance_real_time !== null ? `${parseFloat(ind.avance_real_time).toFixed(2)}%` : "N/A";
                document.getElementById("detail-indicator-avance").textContent = avanceVal;

                // Semaphore badge
                const semText = ind.semaforo_real_time || "N/A";
                if (semBadge) {
                    semBadge.textContent = semText;

                    let semClass = "api-docs__semaforo--secondary";
                    const lowerSem = semText.toLowerCase();
                    if (lowerSem.includes("aceptable") || lowerSem.includes("excedido") || lowerSem.includes("cumplido") || lowerSem.includes("meta alcanzada")) {
                        semClass = "bg-success text-white";
                    } else if (lowerSem.includes("moderado") || lowerSem.includes("preventivo")) {
                        semClass = "bg-warning text-dark";
                    } else if (lowerSem.includes("insuficiente") || lowerSem.includes("crítico")) {
                        semClass = "bg-danger text-white";
                    }
                    semBadge.className = `api-docs__semaforo ${semClass} px-3 py-2 text-sm`;
                }

                // Populating historical table
                if (tableBody) {
                    tableBody.innerHTML = "";
                    if (ind.datos_anuales && ind.datos_anuales.length > 0) {
                        ind.datos_anuales.forEach(da => {
                            const valor = da.valor_dato !== null ? parseFloat(da.valor_dato).toFixed(2) : "Sin capturar";
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
                }

                // Actualizar enlace "Ver ficha completa" con el slug del indicador
                const btnFicha = document.getElementById("btn-ver-ficha-completa");
                if (btnFicha && ind.slug) {
  
                    btnFicha.href = window.location.origin + '/ficha-tecnica/' + ind.slug;
                }

                // Disparar bootstrap modal nativamente
                const modalEl = document.getElementById("indicatorDetailModal");
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            } else {
                alert("No se pudo cargar la información del indicador.");
            }
        })
        .catch(err => {
            console.error(err);
            alert("Error al realizar la consulta del indicador.");
        });
}

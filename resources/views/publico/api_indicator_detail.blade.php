@extends('layouts.plantilla')

@section('title', 'Detalle de Indicador - API SPED')
@section('meta-description', 'Detalle de indicador con histórico validado y gráfico interactivo de la API pública de indicadores SPED.')

@section('css')
    <style>
        .indicator-summary-card {
            min-height: 120px;
        }
        .indicator-summary-card .value {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .indicator-summary-card .label {
            font-size: 0.85rem;
            color: #6c757d;
        }
        #indicator-chart {
            width: 100%;
            min-height: 360px;
        }
    </style>
@endsection

@section('content')
    <section class="container api-container">
        <div class="api-header">
            <h1>Detalle de Indicador</h1>
            <p>Visualiza los datos validados y el historial anual del indicador seleccionado, con gráfica interactiva.</p>
        </div>

        <div class="api-card mb-4">
            <div id="indicator-loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando indicador...</span>
                </div>
                <p class="text-muted mt-3">Cargando información desde la API pública...</p>
            </div>

            <div id="indicator-content" style="display:none;">
                <div class="d-flex justify-content-between align-items-start mb-4 flex-column flex-md-row gap-3">
                    <div>
                        <h2 id="detail-name" class="api-card-title mb-2">Indicador</h2>
                        <p id="detail-description" class="text-muted mb-0"></p>
                    </div>
                    <a href="{{ route('public.api_docs') }}" class="btn btn-outline-secondary">Volver a la consola API</a>
                </div>

                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm p-4 bg-white indicator-summary-card">
                            <div class="label">Línea Base</div>
                            <div id="detail-base-value" class="value">-</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm p-4 bg-white indicator-summary-card">
                            <div class="label">Último dato validado</div>
                            <div id="detail-latest-value" class="value">-</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm p-4 bg-white indicator-summary-card">
                            <div class="label">Semaforización</div>
                            <div id="detail-semaforo" class="value">-</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm p-4 bg-white indicator-summary-card">
                            <div class="label">Avance</div>
                            <div id="detail-avance" class="value">-</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4 bg-white mt-4">
                    <div class="mb-4">
                        <h5 class="mb-1">Histórico de Datos Anuales Validados</h5>
                        <p class="text-muted mb-0">Gráfica generada con eCharts usando los datos validados retornados por la API.</p>
                    </div>

                    <div id="indicator-chart"></div>

                    <div class="table-responsive mt-4">
                        <table class="table table-sm table-striped text-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Año</th>
                                    <th>Valor</th>
                                    <th>Resultado / Logros</th>
                                </tr>
                            </thead>
                            <tbody id="indicator-history-body">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="indicator-error" class="text-center py-5" style="display:none;">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                <p class="text-danger">No se pudo cargar el detalle del indicador. Intenta recargar la página.</p>
            </div>
        </div>
    </section>
@endsection

@section('jss-final')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
    <script>
        const indicatorSlug = @json($id_or_slug);

        function getApiUrl(path = '/api/indicadores') {
            const currentPath = window.location.pathname;
            const docsRoute = '/informacion-general/api';
            let basePath = '';
            if (currentPath.includes(docsRoute)) {
                basePath = currentPath.substring(0, currentPath.indexOf(docsRoute));
            }
            return window.location.origin + basePath + path;
        }

        function parseNumericValue(value) {
            if (value === null || value === undefined || value === '') {
                return null;
            }
            const normalized = String(value).replace(/\s+/g, '').replace(',', '.');
            const number = parseFloat(normalized);
            return Number.isFinite(number) ? number : null;
        }

        function formatValue(value) {
            if (value === null || value === undefined || value === '') {
                return 'N/A';
            }
            const number = parseNumericValue(value);
            return number !== null ? number.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : String(value);
        }

        function createChart(years, values, indicatorName) {
            const chartDom = document.getElementById('indicator-chart');
            if (!chartDom) {
                return;
            }

            chartDom.style.width = '100%';
            chartDom.style.minHeight = '360px';

            if (typeof echarts === 'undefined') {
                chartDom.textContent = 'La biblioteca eCharts no está disponible.';
                return;
            }

            try {
                const chart = echarts.init(chartDom);
                const options = {
                    title: {
                        text: 'Histórico Validado',
                        left: 'center',
                        textStyle: { fontSize: 16 }
                    },
                    tooltip: {
                        trigger: 'axis',
                        formatter: params => {
                            if (!params || !params.length) return '';
                            const item = params[0];
                            return `${item.axisValueLabel}<br/>${indicatorName}: <strong>${item.data}</strong>`;
                        }
                    },
                    xAxis: {
                        type: 'category',
                        data: years,
                        boundaryGap: false,
                        axisLabel: { rotate: 0 }
                    },
                    yAxis: {
                        type: 'value',
                        axisLabel: {
                            formatter: value => value.toLocaleString('en-US', { maximumFractionDigits: 2 })
                        }
                    },
                    series: [
                        {
                            name: indicatorName,
                            type: 'line',
                            smooth: true,
                            data: values,
                            label: { show: true, position: 'top', formatter: '{c}' },
                            lineStyle: { width: 3 },
                            itemStyle: { color: '#0d6efd' },
                            areaStyle: { opacity: 0.1 }
                        }
                    ],
                    grid: { top: 60, left: 40, right: 20, bottom: 40 }
                };

                chart.setOption(options);
                chart.resize();
                setTimeout(() => chart.resize(), 120);
                window.addEventListener('resize', () => chart.resize());
            } catch (error) {
                chartDom.textContent = 'No se pudo inicializar la gráfica.';
                console.error('eCharts initialization error:', error);
            }
        }

        function renderIndicatorDetail(data) {
            document.getElementById('detail-name').textContent = data.nombre || 'Detalle del Indicador';
            document.getElementById('detail-description').textContent = data.descripcion || 'Sin descripción disponible.';
            document.getElementById('detail-base-value').textContent = `${formatValue(data.dato_linea_base)}${data.linea_base ? ' (' + data.linea_base + ')' : ''}`;
            document.getElementById('detail-latest-value').textContent = `${formatValue(data.ultimo_dato_validado)}${data.anio_ultimo_dato_validado ? ' (' + data.anio_ultimo_dato_validado + ')' : ''}`;
            document.getElementById('detail-semaforo').textContent = data.semaforo_real_time || 'N/A';
            document.getElementById('detail-avance').textContent = data.avance_real_time !== null && data.avance_real_time !== undefined ? `${parseFloat(data.avance_real_time).toFixed(2)}%` : 'N/A';

            const historyBody = document.getElementById('indicator-history-body');
            historyBody.innerHTML = '';
            const years = [];
            const values = [];

            if (Array.isArray(data.datos_anuales) && data.datos_anuales.length > 0) {
                data.datos_anuales.forEach(da => {
                    const year = da.anio || 'N/A';
                    const formattedValue = formatValue(da.valor_dato);
                    const numericValue = parseNumericValue(da.valor_dato);

                    years.push(year);
                    values.push(numericValue !== null ? numericValue : null);

                    historyBody.innerHTML += `
                        <tr>
                            <td class="fw-bold font-monospace">${year}</td>
                            <td class="font-monospace fw-bold">${formattedValue}</td>
                            <td class="text-sm">${da.resultados || '<span class="text-muted">Sin logros descritos</span>'}</td>
                        </tr>
                    `;
                });

                createChart(years, values, data.nombre || 'Valor');
            } else {
                historyBody.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center py-4 text-muted">No hay datos anuales validados disponibles para este indicador.</td>
                    </tr>
                `;
                document.getElementById('indicator-chart').textContent = 'No hay datos históricos validados para graficar.';
                document.getElementById('indicator-chart').style.display = 'flex';
                document.getElementById('indicator-chart').style.alignItems = 'center';
                document.getElementById('indicator-chart').style.justifyContent = 'center';
                document.getElementById('indicator-chart').style.color = '#6c757d';
            }
        }

        function setIndicatorState(state) {
            const loadingBlock = document.getElementById('indicator-loading');
            const contentBlock = document.getElementById('indicator-content');
            const errorBlock = document.getElementById('indicator-error');

            if (loadingBlock) loadingBlock.style.display = state === 'loading' ? 'block' : 'none';
            if (contentBlock) contentBlock.style.display = state === 'content' ? 'block' : 'none';
            if (errorBlock) errorBlock.style.display = state === 'error' ? 'block' : 'none';
        }

        function fetchIndicatorDetail() {
            setIndicatorState('loading');

            const detailUrl = `${getApiUrl('/api/indicadores')}/${encodeURIComponent(indicatorSlug)}`;
            fetch(detailUrl, { headers: { 'Accept': 'application/json' } })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Respuesta de red no satisfactoria: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data) {
                        setIndicatorState('content');
                        renderIndicatorDetail(data.data);
                    } else {
                        throw new Error(data.message || 'No se encontró el indicador.');
                    }
                })
                .catch(() => {
                    setIndicatorState('error');
                });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fetchIndicatorDetail);
        } else {
            fetchIndicatorDetail();
        }
    </script>
@endsection
{{-- resources/views/partials/contenido-ejes.blade.php --}}

@include('partials.nav-unificada', [
'tipoNav' => 'ped',
'itemActivo' => $numEje,
'bannerImg' => 'img/Banners/Banner_PED/Eje_' . $numEje . '.jpg'
])

@php
    $etiquetaEje = (int) $numEje === 6 ? 'Eje Transversal' : 'Eje ' . $numEje;
    $totalIndicadoresGeneral = 0;
    if (isset($indicadoresAgrupados) && $indicadoresAgrupados->count() > 0) {
        foreach ($indicadoresAgrupados as $grupoDeIndicadores) {
            $totalIndicadoresGeneral += $grupoDeIndicadores->count();
        }
    }

    $avanceGeneral = min((float) ($avanceEje ?? 0), 100);
    $colorGaugeGeneral = '#adb5bd';
    if (($avanceEje ?? 0) >= 110) {
        $colorGaugeGeneral = '#0d6efd';
    } elseif (($avanceEje ?? 0) >= 91) {
        $colorGaugeGeneral = '#198754';
    } elseif (($avanceEje ?? 0) >= 71) {
        $colorGaugeGeneral = '#ffc107';
    } elseif (($avanceEje ?? 0) > 0) {
        $colorGaugeGeneral = '#dc3545';
    }
@endphp

<main class="eje-dashboard" style="--eje-color: var(--color-eje{{ $numEje }});">
    <section class="eje-dashboard__intro">
        <div class="eje-dashboard__container">
            <span class="eje-dashboard__eyebrow">Seguimiento del PED 2024-2030</span>
            <h1 class="eje-dashboard__title">{{ $etiquetaEje }}</h1>
            <p class="eje-dashboard__intro-text">
                Consulta el avance de los indicadores y el enfoque estratégico de este eje.
            </p>
        </div>
    </section>

    <section class="eje-dashboard__container">
        <div class="eje-dashboard__summary">
            <div class="eje-dashboard__summary-copy">
                <h2 class="eje-dashboard__summary-title">Enfoque estratégico</h2>
                <p class="eje-dashboard__summary-description">{{ $textoEnfoque }}</p>
            </div>
            <div class="eje-dashboard__summary-stats">
                <div class="eje-dashboard__total">{{ $totalIndicadoresGeneral }}</div>
                <div class="eje-dashboard__total-label">Indicadores en total</div>
                <div class="eje-dashboard__gauge-wrap">
                    <div id="gauge-general" class="eje-dashboard__gauge"></div>
                    <div class="eje-dashboard__gauge-value" style="color: {{ $colorGaugeGeneral }};">
                        {{ number_format($avanceEje ?? 0, 2) }}%
                    </div>
                </div>
            </div>
        </div>

        <div class="eje-dashboard__list-header">
            <div>
                <span class="eje-dashboard__eyebrow">Consulta detallada</span>
                <h2 class="eje-dashboard__list-title">Listado de indicadores</h2>
                <p class="eje-dashboard__list-description">Revisa los resultados, metas y avances registrados para este eje.</p>
            </div>
            <div class="eje-dashboard__toolbar ocultar_impresion">
                <span class="eje-dashboard__toolbar-label">Visualización de indicadores</span>
                <div class="eje-dashboard__view-controls">
                    <div class="btn-group btn-group-sm border rounded overflow-hidden">
                        <button type="button" class="btn btn-sm px-3 btn-toggle-vista active" data-view="lista"
                            data-target="contenedor-eje-{{ $numEje }}">
                            <i class="fas fa-list"></i><span class="visually-hidden">Vista de lista</span>
                        </button>
                        <button type="button" class="btn btn-sm px-3 btn-toggle-vista" data-view="grid"
                            data-target="contenedor-eje-{{ $numEje }}">
                            <i class="fas fa-th-large"></i><span class="visually-hidden">Vista de cuadrícula</span>
                        </button>
                    </div>
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm eje-dashboard__filter-toggle dropdown-toggle"
                            data-bs-toggle="dropdown" aria-expanded="false"
                            data-target="contenedor-eje-{{ $numEje }}">
                            <i class="fas fa-filter me-1"></i>
                            <span data-filter-label>Semaforización</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end p-2">
                            <li><button type="button" class="dropdown-item active" data-semaforo-filter="all">Todas</button></li>
                            <li><button type="button" class="dropdown-item" data-semaforo-filter="excedido">Excedido</button></li>
                            <li><button type="button" class="dropdown-item" data-semaforo-filter="aceptable">Aceptable</button></li>
                            <li><button type="button" class="dropdown-item" data-semaforo-filter="moderado">Moderado</button></li>
                            <li><button type="button" class="dropdown-item" data-semaforo-filter="insuficiente">Insuficiente</button></li>
                            <li><button type="button" class="dropdown-item" data-semaforo-filter="solo-linea-base">Solo línea base</button></li>
                            <li><button type="button" class="dropdown-item" data-semaforo-filter="no-clasificado">No clasificado</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="eje-dashboard__indicators row indicador_{{ $numEje }}" id="contenedor-eje-{{ $numEje }}">
            <div class="container">
        @forelse ($indicadoresAgrupados as $nombreTematica => $listaIndicadoresDeLaTematica)
        <div class="tematica-group mt-4 mb-3">
            <h3 class="titulo-tematica fw-bold mb-4">
                Temática: {{ $nombreTematica ?: 'Indicadores Sin Temática Específica' }}
            </h3>

            @if ($listaIndicadoresDeLaTematica->isNotEmpty())
            @foreach ($listaIndicadoresDeLaTematica as $indicador)
            @php

            $semText = $indicador->semaforizacion_validada ?: 'No Clasificado';
            $semKey = \Illuminate\Support\Str::slug($semText);
            $colorSemaforo = '#6c757d'; // Gris por defecto
            $bgBadge = 'bg-secondary';
            $esDatoLineaBase = false;
            $explicacionDetallada = 'Sin datos suficientes para clasificar.';

            switch (strtolower($semText)) {
            case 'excedido':
            $colorSemaforo = '#0d6efd'; // Azul
            $bgBadge = 'bg-primary';
            $explicacionDetallada = 'El valor logrado del indicador supera en 10% a la meta programada, es decir, el resultado del indicador se desvió significativamente de la meta establecida.';
            break;
            case 'aceptable':
            $colorSemaforo = '#198754'; // Verde
            $bgBadge = 'bg-success';
            $explicacionDetallada = 'El valor logrado del indicador se encuentra entre -9% y +10% por debajo y por encima de la meta programada, es decir, se mantiene dentro de los rangos establecidos como aceptables.';
            break;
            case 'moderado':
            $colorSemaforo = '#ffc107'; // Amarillo
            $bgBadge = 'bg-warning text-dark';
            $explicacionDetallada = 'El valor logrado del indicador es menor que la meta programada, representa un avance significativo, pero deficiente o moderado para alcanzar la meta establecida.';
            break;
            case 'insuficiente':
            $colorSemaforo = '#dc3545'; // Rojo
            $bgBadge = 'bg-danger';
            $explicacionDetallada = 'El valor alcanzado del indicador está muy por debajo de la meta programada, lo que representa un avance insuficiente para alcanzar la meta establecida.';
            break;
            case 'solo línea base':
            $colorSemaforo = '#adb5bd';
            $bgBadge = 'bg-light text-dark border';
            $esDatoLineaBase = true;
            $explicacionDetallada = 'El indicador sólo cuenta con el dato de línea base, por lo que está a la espera de un nuevo periodo de medición.';
            break;
            }

             $avanceVal = $indicador->avance_validado ?: 0;
             $chartVal = $avanceVal > 100 ? 100 : $avanceVal;
             $resultadoTexto = isset($indicador->dato_reciente_validado)
                 ? number_format($indicador->dato_reciente_validado, 2, '.', ',')
                 : 'N/D';
             $resultadoClase = strlen($resultadoTexto) > 16
                 ? 'indicador-cifra--muy-larga'
                 : (strlen($resultadoTexto) > 11 ? 'indicador-cifra--larga' : '');
             @endphp

            {{-- 2. TARJETA COMPACTA DEL INDICADOR --}}
            <div class="eje-indicator-item" data-filter-item data-semaforo="{{ $semKey }}"
                style="--semaforo-color: {{ $colorSemaforo }};">
            <div class="card shadow-sm mb-4 border-0 rounded-4 card-indicador eje-indicator__list-card">
                <div class="card-body p-4">
                    <div class="row align-items-center">

                        <div class="col-12 col-lg-4 mb-4 mb-lg-0 pe-lg-4 border-end-lg card-indicador_info eje-indicador__identity">
                            <a href="{{ route('ficha-tecnica.show', $indicador) }}"
                                class="text-decoration-none text-dark fw-bold fs-5 d-block mb-3 hover-primary lh-13rem"
                                title="{{ $indicador->nombre }}">
                                {{ $indicador->nombre }}
                            </a>
                            @if ($indicador->ods->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($indicador->ods->unique('id') as $ods_item)
                                <img src="{{ asset('/img/Icons_ODS/' . $ods_item->id . '.png') }}"
                                    class="shadow-sm rounded" style="height: 35px;" title="{{ $ods_item->nombre }}"
                                    alt="ODS {{ $ods_item->id }}">
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <div class="col-6 col-md-4 col-lg-4 text-center px-lg-4 mb-4 mb-md-0 border-end-lg card-indicador_info eje-indicador__metrics">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="small text-muted mb-1">Unidad de medida</div>
                                    <div class="fw-semibold text-dark text-truncate"
                                        title="{{ $indicador->unidad_medida }}">
                                        {{ $indicador->unidad_medida }}
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted mb-1">Tendencia</div>
                                    <div class="fw-semibold text-dark">{{ $indicador->tendencia }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted mb-1">Línea Base {{ $indicador->linea_base }}</div>
                                     <div class="fw-semibold text-dark indicador-cifra {{ strlen((string) $indicador->dato_linea_base) > 11 ? 'indicador-cifra--larga' : '' }}">
                                        {{ isset($indicador->dato_linea_base) ? number_format($indicador->dato_linea_base, 2, '.', ',') : 'N/D' }}
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted mb-1">Meta {{ $indicador->meta_anio }}</div>
                                     <div class="fw-semibold text-dark indicador-cifra {{ strlen((string) $indicador->meta) > 11 ? 'indicador-cifra--larga' : '' }}">
                                        {{ isset($indicador->meta) ? number_format($indicador->meta, 2, '.', ',') : 'N/D' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 text-center mb-4 mb-md-0 border-end-md card-indicador_info eje-indicador__status">
                            <div class="text-uppercase small fw-semibold text-muted mb-1">
                                Resultado {{ $indicador->anio_reciente_validado ?? '' }}
                            </div>
                             <div class="fs-2 fw-bold indicador-cifra {{ $resultadoClase }}" style="color: {{ $colorSemaforo }};">
                                 {{ $resultadoTexto }}
                            </div>
                            <span class="badge rounded-pill {{ $bgBadge }} px-3 py-1 mt-1 fw-normal shadow-sm" style="cursor: help;"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="top"
                                title="Estado: {{ $semText }}"
                                data-bs-content="{{ $explicacionDetallada }}">
                                {{ $semText }}
                            </span>
                        </div>
                        <div
                            class="col-12 col-md-4 col-lg-2 text-center d-flex flex-column align-items-center justify-content-center eje-indicador__progress">
                            @if($esDatoLineaBase)
                            <i class="fas fa-clock text-muted opacity-50 mb-2 fs-3rem"></i>
                            <div class="small text-muted mt-2 fw-semibold text-center">
                                Medición Pendiente
                            </div>
                            @else
                            <div class="grafico-gauge-pendiente" data-gauge="true"
                                data-chart-val="{{ $chartVal }}"
                                data-color="{{ $colorSemaforo }}"
                                style="cursor: help;"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="top"
                                title="Estado: {{ $semText }}"
                                data-bs-content="{{ $explicacionDetallada }}"></div>
                             <div class="fw-bold fs-5 text-dark mt-35px indicador-cifra {{ strlen(number_format($indicador->avance_validado, 2)) > 11 ? 'indicador-cifra--larga' : '' }}">
                                {{ number_format($indicador->avance_validado, 2) }}%
                            </div>
                            <div class="small text-muted mt-1 fw-semibold">Avance Meta</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @php($decimalesIndicador = 2)
            @include('partials.indicador-grid-card')
            </div>
            @endforeach
            @else
            <p class="text-muted">No hay indicadores disponibles para esta temática.</p>
            @endif
        </div>
        @empty
        <div class="alert alert-info mt-4 rounded-3 shadow-sm border-0" role="alert">
            <i class="fas fa-info-circle me-2"></i> No se encontraron indicadores para este Eje.
        </div>
        @endforelse
    </div>
</div>
</div>
</section>
</main>

{{-- 5. Scripts Unificados (Gráfica General, Popovers y Toggle) --}}
<script src="{{ asset('js/indicator-mini-charts.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Gauge General
        var chartValGeneral = Number("{{ $avanceGeneral }}");
        var chartGeneral = echarts.init(document.getElementById('gauge-general'));
        chartGeneral.setOption({
            series: [{
                type: 'gauge',
                startAngle: 180, endAngle: 0,
                min: 0, max: 100,
                progress: { show: true, width: 15, roundCap: true, itemStyle: { color: @json($colorGaugeGeneral) } },
                axisLine: { lineStyle: { width: 15, color: [[1, '#e7e7e7']] } },
                axisTick: { show: false }, splitLine: { show: false },
                axisLabel: { show: false }, pointer: { show: false },
                detail: { show: false },
                data: [{ value: chartValGeneral }]
            }]
        });
        chartGeneral.resize();

        // Inicializar Toggle Lista/Grid
        document.querySelectorAll('.btn-toggle-vista').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var view = this.dataset.view;
                var targetId = this.dataset.target;
                var container = document.getElementById(targetId);
                if (!container) return;

                // Actualizar botones
                var btnGroup = this.closest('.btn-group');
                btnGroup.querySelectorAll('.btn-toggle-vista').forEach(function(b) {
                    b.classList.remove('active');
                    if (b.dataset.view === 'lista') {
                        b.style.backgroundColor = '#fff';
                        b.style.color = 'var(--color-eje{{ $numEje }})';
                    } else {
                        b.style.backgroundColor = '#fff';
                        b.style.color = 'var(--color-eje{{ $numEje }})';
                    }
                });
                this.classList.add('active');
                if (view === 'lista') {
                    this.style.backgroundColor = 'var(--color-eje{{ $numEje }})';
                    this.style.color = '#fff';
                } else {
                    this.style.backgroundColor = 'var(--color-eje{{ $numEje }})';
                    this.style.color = '#fff';
                }

                // Aplicar modo
                if (view === 'grid') {
                    container.classList.add('modo-grid');
                    window.renderIndicatorMiniCharts(container);
                } else {
                    container.classList.remove('modo-grid');
                }
            });
        });

                document.querySelectorAll('.eje-dashboard__filter-toggle').forEach(function(toggle) {
            var toolbar = toggle.closest('.eje-dashboard__toolbar');
            var target = document.getElementById(toggle.dataset.target);

            if (!toolbar || !target) return;

            toolbar.querySelectorAll('[data-semaforo-filter]').forEach(function(filterButton) {
                filterButton.addEventListener('click', function() {
                    var filter = this.dataset.semaforoFilter;
                    var visibleItems = 0;

                    target.querySelectorAll('[data-filter-item]').forEach(function(item) {
                        var visible = filter === 'all' || item.dataset.semaforo === filter;
                        item.hidden = !visible;
                        if (visible) visibleItems++;
                    });

                    var emptyState = target.querySelector('[data-filter-empty]');
                    if (!emptyState) {
                        emptyState = document.createElement('div');
                        emptyState.className = 'eje-dashboard__filter-empty';
                        emptyState.dataset.filterEmpty = 'true';
                        emptyState.textContent = 'No hay indicadores con esta semaforización.';
                        (target.querySelector('.container') || target).appendChild(emptyState);
                    }
                    emptyState.hidden = visibleItems > 0;

                    toolbar.querySelectorAll('[data-semaforo-filter]').forEach(function(button) {
                        button.classList.toggle('active', button === filterButton);
                    });
                    toolbar.querySelector('[data-filter-label]').textContent =
                        filter === 'all' ? 'Semaforización' : this.textContent;
                    toggle.classList.toggle('has-filter', filter !== 'all');
                });
            });
        });

        // Inicializar Gauges con Lazy Loading (IntersectionObserver)
        function renderGauge(el) {
            var val = Number(el.dataset.chartVal);
            if (val > 100) val = 100;
            var color = el.dataset.color;
            var chart = echarts.init(el);
            chart.setOption({
                series: [{
                    type: 'gauge',
                    startAngle: 180, endAngle: 0,
                    min: 0, max: 100,
                    progress: { show: true, width: 15, roundCap: true, itemStyle: { color: color } },
                    axisLine: { lineStyle: { width: 15, color: [[1, '#f0f0f0']] } },
                    axisTick: { show: false }, splitLine: { show: false },
                    axisLabel: { show: false }, pointer: { show: false },
                    detail: { show: false },
                    data: [{ value: val }]
                }]
            });
            chart.resize();
        }

        var gaugeEls = document.querySelectorAll('[data-gauge="true"]');
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        renderGauge(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '150px' });
            gaugeEls.forEach(function(el) { observer.observe(el); });
        } else {
            gaugeEls.forEach(renderGauge);
        }

        // Inicializar Popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl, {
                sanitize: false
            });
        });
    });
</script>
<style>
    .hover-primary:hover {
        color: var(--color-eje{{ $numEje }}) !important;
        text-decoration: underline !important;
    }
</style>

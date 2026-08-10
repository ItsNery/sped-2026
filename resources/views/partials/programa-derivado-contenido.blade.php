@php
    $descFinal = $descripcion ?? ($programaData->descripcion ?? '');
    $programaColor = $programa->color ?? '#0c312d';
    $colorGaugeGeneral = '#adb5bd';
    if (($avancePrograma ?? 0) >= 110) {
        $colorGaugeGeneral = '#0d6efd';
    } elseif (($avancePrograma ?? 0) >= 91) {
        $colorGaugeGeneral = '#198754';
    } elseif (($avancePrograma ?? 0) >= 71) {
        $colorGaugeGeneral = '#ffc107';
    } elseif (($avancePrograma ?? 0) > 0) {
        $colorGaugeGeneral = '#dc3545';
    }
@endphp

@include('partials.nav-unificada', [
    'tipoNav' => 'derivados',
    'itemActivo' => $itemActivoNav,
    'colorTema' => $programaColor
])
<main class="eje-dashboard" style="--eje-color: {{ $programaColor }};">
<section class="eje-dashboard__intro">
    <div class="eje-dashboard__container">
        <span class="eje-dashboard__eyebrow">{{ $tituloBadge }}</span>
        <h1 class="eje-dashboard__title">{{ $programa->nombre }}</h1>
        <p class="eje-dashboard__intro-text">Consulta el avance de los indicadores asociados a este programa derivado.</p>
    </div>
</section>

<section class="eje-dashboard__container">
    <div class="eje-dashboard__summary">
        <div class="eje-dashboard__summary-copy">
            <h2 class="eje-dashboard__summary-title">{{ $programa->nombre }}</h2>
            <p class="eje-dashboard__summary-description">{{ $descFinal }}</p>
            @if ($programa->documento)
                <a target="_blank" href="{{ $programa->documento }}" class="btn text-white fw-bold px-4 py-2 rounded-pill shadow-sm mt-4"
                    style="background-color: {{ $programaColor }};">
                    <i class="fas fa-file-pdf me-2"></i>Ver documento
                </a>
            @endif
        </div>
        <div class="eje-dashboard__summary-stats">
            <div class="eje-dashboard__total">{{ $indicadores->count() }}</div>
            <div class="eje-dashboard__total-label">Indicadores en total</div>
            <div class="eje-dashboard__gauge-wrap">
                <div id="gauge-general" class="eje-dashboard__gauge"></div>
                <div class="eje-dashboard__gauge-value" style="color: {{ $colorGaugeGeneral }};">
                    {{ number_format($avancePrograma, 2) }}%
                </div>
            </div>
        </div>
    </div>

    <div class="eje-dashboard__list-header">
        <div>
            <span class="eje-dashboard__eyebrow">Consulta detallada</span>
            <h2 class="eje-dashboard__list-title">Listado de indicadores</h2>
        </div>
        <div class="eje-dashboard__toolbar ocultar_impresion">
            <span class="eje-dashboard__toolbar-label">Visualización</span>
            <div class="eje-dashboard__view-controls">
                <div class="btn-group btn-group-sm border rounded overflow-hidden">
                    <button type="button" class="btn btn-sm px-3 btn-toggle-vista active" data-view="lista" data-target="contenedor-programa">
                        <i class="fas fa-list"></i><span class="visually-hidden">Vista de lista</span>
                    </button>
                    <button type="button" class="btn btn-sm px-3 btn-toggle-vista" data-view="grid" data-target="contenedor-programa">
                        <i class="fas fa-th-large"></i><span class="visually-hidden">Vista de cuadrícula</span>
                    </button>
                </div>
                <div class="dropdown">
                    <button type="button" class="btn btn-sm eje-dashboard__filter-toggle dropdown-toggle"
                        data-bs-toggle="dropdown" aria-expanded="false" data-target="contenedor-programa">
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

<div class="eje-dashboard__indicators row ficha" id="contenedor-programa">
    @forelse ($indicadores as $indicador)
    @php
    $semText = $indicador->semaforizacion_validada ?: 'No Clasificado';
    $semKey = \Illuminate\Support\Str::slug($semText);
    $colorSemaforo = '#6c757d';
    $bgBadge = 'bg-secondary';
    $esDatoLineaBase = false;
    $explicacionDetallada = 'Sin datos suficientes para clasificar.';

    switch(strtolower($semText)){
    case 'excedido': $colorSemaforo = '#0d6efd'; $bgBadge = 'bg-primary'; $explicacionDetallada = 'El valor logrado del indicador supera en 10% a la meta programada, es decir, el resultado del indicador se desvió significativamente de la meta establecida.'; break;
    case 'aceptable': $colorSemaforo = '#198754'; $bgBadge = 'bg-success'; $explicacionDetallada = 'El valor logrado del indicador se encuentra entre -9% y +10% por debajo y por encima de la meta programada, es decir, se mantiene dentro de los rangos establecidos como aceptables.'; break;
    case 'moderado': $colorSemaforo = '#ffc107'; $bgBadge = 'bg-warning text-dark'; $explicacionDetallada = 'El valor logrado del indicador es menor que la meta programada, representa un avance significativo, pero deficiente o moderado para alcanzar la meta establecida.'; break;
    case 'insuficiente': $colorSemaforo = '#dc3545'; $bgBadge = 'bg-danger'; $explicacionDetallada = 'El valor alcanzado del indicador está muy por debajo de la meta programada, lo que representa un avance insuficiente para alcanzar la meta establecida.'; break;
    case 'solo línea base':
    $colorSemaforo = '#adb5bd'; $bgBadge = 'bg-light text-dark border';
    $esDatoLineaBase = true; $explicacionDetallada = 'El indicador sólo cuenta con el dato de línea base, por lo que está a la espera de un nuevo periodo de medición.'; break;
    }

    $avanceVal = $indicador->avance_validado ?: 0;
    $chartVal = $avanceVal > 100 ? 100 : $avanceVal;
    @endphp
    <div class="container" data-filter-item data-semaforo="{{ $semKey }}">
        <div class="card shadow-sm mb-4 border-0 rounded-4 card-indicador" style="--semaforo-color: {{ $colorSemaforo }}; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="card-body p-4">
                <div class="row align-items-center">

                    <div class="col-12 col-lg-4 mb-4 mb-lg-0 pe-lg-4 border-end-lg eje-indicador__identity" style="border-color: #eee !important;">
                        <a href="{{ route('ficha-tecnica.show', $indicador) }}" class="text-decoration-none fw-bold fs-5 d-block mb-3" style="color: {{ $programaColor }}; line-height: 1.3;">
                            {{ $indicador->nombre }}
                        </a>
                        @if ($indicador->ods->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($indicador->ods->unique('id') as $ods_item)
                            <img src="{{ asset('/img/Icons_ODS/' . $ods_item->id . '.png') }}" class="shadow-sm rounded" style="height: 35px;" title="{{ $ods_item->nombre }}">
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <div class="col-6 col-md-4 col-lg-4 text-center px-lg-4 mb-4 mb-md-0 border-end-lg eje-indicador__metrics" style="border-color: #eee !important;">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="small text-muted mb-1">Unidad de medida</div>
                                <div class="fw-semibold text-dark text-truncate" title="{{ $indicador->unidad_medida }}">{{ $indicador->unidad_medida }}</div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted mb-1">Tendencia</div>
                                <div class="fw-semibold text-dark">{{ $indicador->tendencia }}</div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted mb-1">Línea Base {{ $indicador->linea_base }}</div>
                                <div class="fw-semibold text-dark">
                                    {{ isset($indicador->dato_linea_base) ? number_format((float)str_replace(',', '', $indicador->dato_linea_base), $indicador->id == 100 ? 6 : 2, '.', ',') : 'N/D' }}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted mb-1">Meta {{ $indicador->meta_anio }}</div>
                                <div class="fw-semibold text-dark">
                                    {{ isset($indicador->meta) ? number_format((float)str_replace(',', '', $indicador->meta), $indicador->id == 100 ? 6 : 2, '.', ',') : 'N/D' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 text-center mb-4 mb-md-0 border-end-md eje-indicador__status" style="border-color: #eee !important;">
                        <div class="text-uppercase small fw-semibold text-muted mb-1">Resultado {{ $indicador->anio_reciente_validado ?? '' }}</div>
                        <div class="fs-2 fw-bold" style="color: {{ $colorSemaforo }};">
                            {{ isset($indicador->dato_reciente_validado) ? number_format((float)str_replace(',', '', $indicador->dato_reciente_validado), $indicador->id == 100 ? 6 : 2, '.', ',') : 'N/D' }}
                        </div>
                        <span class="badge rounded-pill {{ $bgBadge }} px-3 py-1 mt-1 fw-normal shadow-sm" style="cursor: help;" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" title="Estado: {{ $semText }}" data-bs-content="{{ $explicacionDetallada }}">{{ $semText }}</span>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2 text-center d-flex flex-column align-items-center justify-content-center eje-indicador__progress">
                        @if($esDatoLineaBase)
                        <i class="fas fa-clock text-muted opacity-50 mb-2" style="font-size: 3rem;"></i>
                        <div class="small text-muted mt-2 fw-semibold text-center">Medición Pendiente</div>
                        @else
                        <div class="grafico-gauge-pendiente" data-gauge="true" data-chart-val="{{ $chartVal }}" data-color="{{ $colorSemaforo }}" style="cursor: help;" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" title="Estado: {{ $semText }}" data-bs-content="{{ $explicacionDetallada }}"></div>
                        <div class="fw-bold fs-5 text-dark" style="margin-top: -30px;">{{ number_format($indicador->avance_validado, 2) }}%</div>
                        <div class="small text-muted mt-1 fw-semibold">Avance Meta</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @empty
    <div class="alert alert-info text-center shadow-sm rounded-4 border-0 p-4">
        <i class="fas fa-info-circle fs-3 mb-3 d-block"></i>
        No hay indicadores registrados para este programa actualmente.
    </div>
    @endforelse
</div>
</section>
</main>
@section('jss-final')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Toggle Lista/Grid
        document.querySelectorAll('.btn-toggle-vista').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var view = this.dataset.view;
                var targetId = this.dataset.target;
                var container = document.getElementById(targetId);
                if (!container) return;

                var btnGroup = this.closest('.btn-group');
                btnGroup.querySelectorAll('.btn-toggle-vista').forEach(function(b) {
                    b.classList.remove('active');
                    b.style.backgroundColor = '#fff';
                    b.style.color = '{{ $programaColor }}';
                });
                this.classList.add('active');
                this.style.backgroundColor = '{{ $programaColor }}';
                this.style.color = '#fff';

                if (view === 'grid') {
                    container.classList.add('modo-grid');
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
                        target.appendChild(emptyState);
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

        var chartValGeneral = Number("{{ ($avancePrograma ?? 0) > 100 ? 100 : ($avancePrograma ?? 0) }}");
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

        var popoverList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]')).map(function(el) {
            return new bootstrap.Popover(el, {
                sanitize: false
            });
        });
        document.addEventListener('click', function(e) {});
    });
</script>
@endsection

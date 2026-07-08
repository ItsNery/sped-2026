{{-- resources/views/partials/contenido-ejes.blade.php --}}

@include('partials.nav-unificada', [
'tipoNav' => 'ped',
'itemActivo' => $numEje,
'bannerImg' => 'img/Banners/Banner_PED/Eje_' . $numEje . '.jpg'
])

@php
// Calculamos el total de indicadores
$totalIndicadoresGeneral = 0;
if (isset($indicadoresAgrupados) && $indicadoresAgrupados->count() > 0) {
foreach ($indicadoresAgrupados as $grupoDeIndicadores) {
$totalIndicadoresGeneral += $grupoDeIndicadores->count();
}
}
@endphp

{{-- 2. TARJETA RESUMEN UNIFICADA (Enfoque + Velocímetro) --}}
<div class="card shadow-sm border-0 mb-5 overflow-hidden container" style="border-radius: 15px;">
    <div class="row g-0">

        <div class="col-lg-8 p-4 p-md-5 bg-white" style="border-left: 8px solid var(--color-eje{{ $numEje }});">
            <div class="d-flex align-items-center mb-4">
                <span class="badge rounded-pill px-3 py-2 me-3 fs-6 text-white"
                    style="background-color: var(--color-eje{{ $numEje }});">
                    Eje {{ $numEje }}
                </span>
                <h2 class="mb-0 fw-bold text-dark">Enfoque</h2>
            </div>
            <p class="fs-5 text-muted lh-lg mb-0 text-justify">
                {{ $textoEnfoque }}
            </p>
        </div>

        <div
            class="col-lg-4 p-4 p-md-5 d-flex flex-column justify-content-center align-items-center border-start bg-light">

            <div class="text-center mb-4">
                <h3 class="fw-bold mb-0 fs-25rem" style="color: var(--color-eje{{ $numEje }});">
                    {{ $totalIndicadoresGeneral }}
                </h3>
                <div class="text-uppercase fw-semibold text-muted small tracking-wide">
                    Indicadores en Total
                </div>
            </div>

            <div class="position-relative w-100 d-flex flex-column align-items-center justify-content-center">
                <div id="gauge-general" class="grafico-gauge-listado">
                </div>

                <div class="position-absolute text-center top-70px">
                    <div class="fw-bold text-dark mt-3 fs-18rem">
                        {{ number_format($avanceEje ?? 0, 2) }}%
                    </div>
                    <div class="small text-muted fw-semibold">Avance</div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- 3. Toggle Lista / Grid --}}
<div class="d-flex align-items-center gap-2 mb-3 ocultar_impresion">
    <span class="text-muted small fw-semibold">Vista:</span>
    <div class="btn-group btn-group-sm border rounded overflow-hidden">
        <button type="button" class="btn btn-sm px-3 btn-toggle-vista active" data-view="lista" data-target="contenedor-eje-{{ $numEje }}" style="background-color: var(--color-eje{{ $numEje }}); color: #fff; border: none;">
            <i class="fas fa-list"></i>
        </button>
        <button type="button" class="btn btn-sm px-3 btn-toggle-vista" data-view="grid" data-target="contenedor-eje-{{ $numEje }}" style="background-color: #fff; color: var(--color-eje{{ $numEje }});">
            <i class="fas fa-th-large"></i>
        </button>
    </div>
</div>

{{-- 4. Sección de Indicadores --}}
<div class="row indicador_{{ $numEje }}" id="contenedor-eje-{{ $numEje }}">
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
            @endphp

            {{-- 2. TARJETA COMPACTA DEL INDICADOR --}}
            <div class="card shadow-sm mb-4 border-0 rounded-4 card-indicador"
                style="border-left: 6px solid {{ $colorSemaforo }};">
                <div class="card-body p-4">
                    <div class="row align-items-center">

                        <div class="col-12 col-lg-4 mb-4 mb-lg-0 pe-lg-4 border-end-lg card-indicador_info">
                            <a href="{{ route('ficha-tecnica.show', $indicador) }}"
                                class="text-decoration-none text-dark fw-bold fs-5 d-block mb-3 hover-primary lh-13rem">
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

                        <div class="col-6 col-md-4 col-lg-4 text-center px-lg-4 mb-4 mb-md-0 border-end-lg card-indicador_info">
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
                                    <div class="fw-semibold text-dark">
                                        {{ isset($indicador->dato_linea_base) ? number_format($indicador->dato_linea_base, 2, '.', ',') : 'N/D' }}
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted mb-1">Meta 2030</div>
                                    <div class="fw-semibold text-dark">
                                        {{ isset($indicador->meta_2024) ? number_format($indicador->meta_2024, 2, '.', ',') : 'N/D' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 text-center mb-4 mb-md-0 border-end-md card-indicador_info">
                            <div class="text-uppercase small fw-semibold text-muted mb-1">
                                Resultado {{ $indicador->anio_reciente_validado ?? '' }}
                            </div>
                            <div class="fs-2 fw-bold" style="color: {{ $colorSemaforo }};">
                                @isset($indicador->dato_reciente_validado)
                                {{ number_format($indicador->dato_reciente_validado, 2, '.', ',') }}
                                @else
                                N/D
                                @endisset
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
                            class="col-12 col-md-4 col-lg-2 text-center d-flex flex-column align-items-center justify-content-center">
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
                            <div class="fw-bold fs-5 text-dark mt-35px">
                                {{ number_format($indicador->avance_validado, 2) }}%
                            </div>
                            <div class="small text-muted mt-1 fw-semibold">Avance Meta</div>
                            @endif
                        </div>
                    </div>
                </div>
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

{{-- 5. Scripts Unificados (Gráfica General, Popovers y Toggle) --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Gauge General
        var chartValGeneral = Number("{{ ($avanceEje ?? 0) > 100 ? 100 : ($avanceEje ?? 0) }}");
        var chartGeneral = echarts.init(document.getElementById('gauge-general'));
        chartGeneral.setOption({
            series: [{
                type: 'gauge',
                startAngle: 180, endAngle: 0,
                min: 0, max: 100,
                progress: { show: true, width: 15, roundCap: true, itemStyle: { color: '#691A32' } },
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
                } else {
                    container.classList.remove('modo-grid');
                }
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
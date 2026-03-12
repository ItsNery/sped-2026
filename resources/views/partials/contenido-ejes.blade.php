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
                <h2 class="mb-0 fw-bold text-dark">Enfoque Estratégico</h2>
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
                    <div class="fw-bold text-dark fs-18rem">
                        {{ number_format($avanceEje ?? 0, 1) }}%
                    </div>
                    <div class="small text-muted fw-semibold">Avance</div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- 3. Sección de Indicadores --}}
<div class="row indicador_{{ $numEje }}">
    <div class="container">
        @forelse ($indicadoresAgrupados as $nombreTematica => $listaIndicadoresDeLaTematica)
        <div class="tematica-group mt-4 mb-3">
            <h3 class="titulo-tematica fw-bold mb-4">
                Temática: {{ $nombreTematica ?: 'Indicadores Sin Temática Específica' }}
            </h3>

            @if ($listaIndicadoresDeLaTematica->isNotEmpty())
            @foreach ($listaIndicadoresDeLaTematica as $indicador)
            @php
            // 1. Calculamos el estado, colores y valores una sola vez al inicio
            $semText = $indicador->semaforizacion_validada ?: 'No Clasificado';
            $colorSemaforo = '#6c757d'; // Gris por defecto
            $bgBadge = 'bg-secondary';
            $esDatoLineaBase = false;

            switch (strtolower($semText)) {
            case 'excedido':
            $colorSemaforo = '#0d6efd'; // Azul
            $bgBadge = 'bg-primary';
            break;
            case 'aceptable':
            $colorSemaforo = '#198754'; // Verde
            $bgBadge = 'bg-success';
            break;
            case 'moderado':
            $colorSemaforo = '#ffc107'; // Amarillo
            $bgBadge = 'bg-warning text-dark';
            break;
            case 'insuficiente':
            $colorSemaforo = '#dc3545'; // Rojo
            $bgBadge = 'bg-danger';
            break;
            case 'solo línea base':
            $colorSemaforo = '#adb5bd'; // Un gris más claro para diferenciarlo del "No clasificado"
            $bgBadge = 'bg-light text-dark border';
            $esDatoLineaBase = true; // Usaremos esto para ocultar el velocímetro si queremos
            break;
            }

            $avanceVal = $indicador->avance_validado ?: 0;
            $chartVal = $avanceVal > 100 ? 100 : $avanceVal;
            @endphp

            {{-- 2. TARJETA COMPACTA DEL INDICADOR --}}
            <div class="card shadow-sm mb-4 border-0 rounded-4 card-indicador"
                style="border-left: 6px solid {{ $colorSemaforo }};"
                onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
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
                            <span class="badge rounded-pill {{ $bgBadge }} px-3 py-1 mt-1 fw-normal shadow-sm">
                                {{ $semText }}
                            </span>
                        </div>

                        <div class="col-6 col-md-4 col-lg-4 text-center px-lg-4 mb-4 mb-md-0 border-end-lg card-indicador_info">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="small text-muted mb-1">Unidad</div>
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

                        <div
                            class="col-12 col-md-4 col-lg-2 text-center d-flex flex-column align-items-center justify-content-center">
                            @if($esDatoLineaBase)
                            <i class="fas fa-clock text-muted opacity-50 mb-2 fs-3rem"></i>
                            <div class="small text-muted mt-2 fw-semibold text-center">
                                Medición Pendiente
                            </div>
                            @else
                            <div id="gauge-{{ $indicador->id }}" class="grafico-gauge-pendiente"></div>
                            <div class="fw-bold fs-5 text-dark mt-35px">
                                {{ number_format($indicador->avance_validado, 1) }}%
                            </div>
                            <div class="small text-muted mt-1 fw-semibold">Avance Meta</div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var options = {
                        series: [Number("{{ $chartVal }}")],
                        chart: {
                            type: 'radialBar',
                            height: 180,
                            sparkline: {
                                enabled: true
                            }
                        },
                        plotOptions: {
                            radialBar: {
                                startAngle: -90,
                                endAngle: 90,
                                track: {
                                    background: "#f0f0f0",
                                    strokeWidth: '97%'
                                },
                                dataLabels: {
                                    name: {
                                        show: false
                                    },
                                    value: {
                                        show: false
                                    }
                                }
                            }
                        },
                        fill: {
                            colors: ['{{ $colorSemaforo }}']
                        },
                        stroke: {
                            lineCap: 'round'
                        }
                    };
                    new ApexCharts(document.querySelector("#gauge-{{ $indicador->id }}"), options).render();
                });
            </script>
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

{{-- 4. Scripts Unificados (Gráfica General y Popovers) --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Gauge General
        var chartValGeneral = Number("{{ ($avanceEje ?? 0) > 100 ? 100 : ($avanceEje ?? 0) }}");
        var optionsGeneral = {
            series: [chartValGeneral],
            chart: {
                type: 'radialBar',
                height: 220,
                sparkline: {
                    enabled: true
                }
            },
            plotOptions: {
                radialBar: {
                    startAngle: -90,
                    endAngle: 90,
                    track: {
                        background: "#e7e7e7",
                        strokeWidth: '97%'
                    },
                    dataLabels: {
                        name: {
                            show: false
                        },
                        value: {
                            show: false
                        }
                    }
                }
            },
            fill: {
                colors: ['#691A32']
            },
            labels: ['Avance General'],
        };
        new ApexCharts(document.querySelector("#gauge-general"), optionsGeneral).render();

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
    @media (min-width: 768px) {
        .border-end-md {
            border-right: 1px solid #eee !important;
        }
    }

    @media (min-width: 992px) {
        .border-end-lg {
            border-right: 1px solid #eee !important;
        }
    }

    .hover-primary:hover {
        color: var(--color-eje {{$numEje}}) !important;
        text-decoration: underline !important;
    }
</style>
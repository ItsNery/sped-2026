@extends('layouts.plantilla')
@section('title', 'Programa Derivado Regional ' . $programa->nombre)
@section('meta-description', $programaData->descripcion)
@section('canonical-url', url()->current())
@section('og-title',
'Programa Derivado Regional ' .
$programa->nombre .
' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description', $programaData->descripcion)
@section('og:url', url()->current())
@section('twitter-title',
$programa->nombre .
' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description', $programaData->descripcion)
@section('css')
@endsection
@section('jss-inicial')
@endsection
@section('content')
@include('partials.nav-unificada', [
'tipoNav' => 'derivados',
'itemActivo' => 'App\Models\CatProgramaDerivadoRegional',
'colorTema' => $programa->color
])
<div class="container mt-4 mb-5">
    <div class="card shadow-sm border-0 mb-5 overflow-hidden br-15px">
        <div class="row g-0">

            <div class="col-lg-8 p-4 p-md-5 bg-white" style="border-left: 8px solid {{ $programa->color }};">

                <div class="d-flex flex-column flex-sm-row align-items-sm-center mb-4">
                    <img src="{{ asset($imagen) }}"
                        class="shadow rounded-3 mb-3 mb-sm-0 me-sm-4 portada-derivado"
                        alt="Portada de {{ $programa->nombre }}">

                    <div>
                        <span class="badge rounded-pill px-3 py-1 mb-2 fs-6 shadow-sm text-white" style="background-color: {{ $programa->color }};">
                            Programa Especial
                        </span>
                        <h2 class="mb-0 fw-bold text-dark lh-sm">{{ $programa->nombre }}</h2>
                    </div>
                </div>

                <p class="fs-5 text-muted lh-lg mb-4" style="text-align: justify;">
                    {{ $descripcion }}
                </p>

                @if ($programa->documento)
                <a target="_blank" href="{{ $programa->documento }}" class="btn text-white fw-bold px-4 py-2 rounded-pill shadow-sm" style="background-color: {{ $programa->color }}; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fas fa-file-pdf me-2"></i> Ver Documento
                </a>
                @endif
            </div>

            <div class="col-lg-4 p-4 p-md-5 d-flex flex-column justify-content-center align-items-center border-start bg-light">

                <div class="text-center mb-4">
                    <h3 class="fw-bold mb-0 contador-indicadores" style="color: {{ $programa->color }};">
                        {{ $indicadores->count() }}
                    </h3>
                    <div class="text-uppercase fw-semibold text-muted small tracking-wide mt-1">Indicadores en Total</div>
                </div>

                <div class="position-relative w-100 d-flex flex-column align-items-center justify-content-center">
                    <div id="gauge-general" class="grafico-gauge-general"></div>
                    <div class="position-absolute text-center" style="top: 60px;">
                        <div class="fw-bold text-dark mt-3 grafico-gauge-general_texto">
                            {{ number_format($avancePrograma, 1) }}%
                        </div>
                        <div class="small text-muted fw-semibold">Avance</div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<div class="row ficha py-50px" style="background-color:{{ $programa->color }}">
    @forelse ($indicadores as $indicador)
    @php
    $semText = $indicador->semaforizacion_validada ?: 'No Clasificado';
    $colorSemaforo = '#6c757d';
    $bgBadge = 'bg-secondary';
    $esDatoLineaBase = false;

    switch(strtolower($semText)){
    case 'excedido': $colorSemaforo = '#0d6efd'; $bgBadge = 'bg-primary'; break;
    case 'aceptable': $colorSemaforo = '#198754'; $bgBadge = 'bg-success'; break;
    case 'moderado': $colorSemaforo = '#ffc107'; $bgBadge = 'bg-warning text-dark'; break;
    case 'insuficiente': $colorSemaforo = '#dc3545'; $bgBadge = 'bg-danger'; break;
    case 'solo línea base':
    $colorSemaforo = '#adb5bd'; $bgBadge = 'bg-light text-dark border';
    $esDatoLineaBase = true; break;
    }

    $avanceVal = $indicador->avance_validado ?: 0;
    $chartVal = $avanceVal > 100 ? 100 : $avanceVal;
    @endphp
    <div class="container">
        <div class="card shadow-sm mb-4 border-0 rounded-4" style="border-left: 6px solid {{ $colorSemaforo }}; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="card-body p-4">
                <div class="row align-items-center">

                    <div class="col-12 col-lg-4 mb-4 mb-lg-0 pe-lg-4 border-end-lg" style="border-color: #eee !important;">
                        <a href="{{ route('ficha-tecnica.show', $indicador) }}" class="text-decoration-none fw-bold fs-5 d-block mb-3" style="color: {{ $programa->color }}; line-height: 1.3;">
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

                    <div class="col-6 col-md-4 col-lg-4 text-center px-lg-4 mb-4 mb-md-0 border-end-lg" style="border-color: #eee !important;">
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
                                    {{ isset($indicador->dato_linea_base) ? number_format((float)str_replace(',', '', $indicador->dato_linea_base), 2, '.', ',') : 'N/D' }}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted mb-1">Meta 2030</div>
                                <div class="fw-semibold text-dark">
                                    {{ isset($indicador->meta_2024) ? number_format((float)str_replace(',', '', $indicador->meta_2024), 2, '.', ',') : 'N/D' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 text-center mb-4 mb-md-0 border-end-md" style="border-color: #eee !important;">
                        <div class="text-uppercase small fw-semibold text-muted mb-1">Resultado {{ $indicador->anio_reciente_validado ?? '' }}</div>
                        <div class="fs-2 fw-bold" style="color: {{ $colorSemaforo }};">
                            {{ isset($indicador->dato_reciente_validado) ? number_format((float)str_replace(',', '', $indicador->dato_reciente_validado), 2, '.', ',') : 'N/D' }}
                        </div>
                        <span class="badge rounded-pill {{ $bgBadge }} px-3 py-1 mt-1 fw-normal shadow-sm">{{ $semText }}</span>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2 text-center d-flex flex-column align-items-center justify-content-center">
                        @if($esDatoLineaBase)
                        <i class="fas fa-clock text-muted opacity-50 mb-2" style="font-size: 3rem;"></i>
                        <div class="small text-muted mt-2 fw-semibold text-center">Medición Pendiente</div>
                        @else
                        <div id="gauge-{{ $indicador->id }}" style="height: 140px; width:100%; display:flex; justify-content:center;"></div>
                        <div class="fw-bold fs-5 text-dark" style="margin-top: -30px;">{{ number_format($indicador->avance_validado, 1) }}%</div>
                        <div class="small text-muted mt-1 fw-semibold">Avance Meta</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if(!$esDatoLineaBase)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new ApexCharts(document.querySelector("#gauge-{{ $indicador->id }}"), {
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
            }).render();
        });
    </script>
    @endif

    @empty
    <div class="alert alert-info text-center shadow-sm rounded-4 border-0 p-4">
        <i class="fas fa-info-circle fs-3 mb-3 d-block"></i>
        No hay indicadores registrados para este programa actualmente.
    </div>
    @endforelse
</div>
@section('jss-final')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var chartValGeneral = Number("{{ ($avancePrograma ?? 0) > 100 ? 100 : ($avancePrograma ?? 0) }}");
        new ApexCharts(document.querySelector("#gauge-general"), {
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
                colors: ['{{ $programa->color }}']
            },
            stroke: {
                lineCap: 'round'
            }
        }).render();

        var popoverList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]')).map(function(el) {
            return new bootstrap.Popover(el, {
                sanitize: false
            });
        });
        document.addEventListener('click', function(e) {});
    });
</script>
@endsection
@endsection
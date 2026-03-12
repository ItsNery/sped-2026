@extends('layouts.plantilla')
@section('title', 'Ficha técnica del Indicador ' . $indicador->nombre)
@section('meta-description', 'Ficha ténica del indicador ' . $indicador->nombre)
@section('canonical-url', url()->current())
@section('og-title',
$indicador->nombre .
' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description', $indicador->descripcion)
@section('og:url', url()->current())
@section('twitter-title',
$indicador->nombre .
' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description', $indicador->descripcion)
@section('css')
<style>
    @media print {
        .line-break {
            display: block;
            page-break-before: always;
        }

        #grafica {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            text-align: center !important;
            overflow: visible !important;
        }

    }
</style>
@endsection
@section('jss-inicial')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endsection
@section('content')
<div class="row" style="margin-left: auto; margin-right: auto;">
    @if ($indicador->programa == 'Humanismo con Bienestar' && $indicador->programa_derivado == 'Plan Estatal de Desarrollo 2024-2030')
    <img src="{{ asset('img/Banners/Banner_PED/Eje_1.webp') }}" alt="banner del Eje 1" class="w-100 px-0">
    @elseif(
    $indicador->programa == 'Prosperidad y Estabilidad Económica' &&
    $indicador->programa_derivado == 'Plan Estatal de Desarrollo 2024-2030')
    <img src="{{ asset('img/Banners/Banner_PED/Eje_2.webp') }}" alt="banner del Eje 2" class="w-100 px-0">
    @elseif(
    $indicador->programa == 'Estado de Derecho, Seguridad y Justicia' &&
    $indicador->programa_derivado == 'Plan Estatal de Desarrollo 2024-2030')
    <img src="{{ asset('img/Banners/Banner_PED/Eje_3.webp') }}" alt="banner del Eje 3" class="w-100 px-0">
    @elseif(
    $indicador->programa == 'Desarrollo Urbano y Crecimiento Sostenible' &&
    $indicador->programa_derivado == 'Plan Estatal de Desarrollo 2024-2030')
    <img src="{{ asset('img/Banners/Banner_PED/Eje_4.webp') }}" alt="banner del Eje 4" class="w-100 px-0">
    @elseif(
    $indicador->programa == 'Gobierno Transformador y de Resultados' &&
    $indicador->programa_derivado == 'Plan Estatal de Desarrollo 2024-2030')
    <img src="{{ asset('img/Banners/Banner_PED/Eje_5.webp') }}" alt="banner del Eje 5" class="w-100 px-0">
    @elseif($indicador->programa == 'Por Amor a Puebla' && $indicador->programa_derivado == 'Plan Estatal de Desarrollo 2024-2030')
    <img src="{{ asset('img/Banners/Banner_PED/Eje_6.webp') }}" alt="banner del Eje 6" class="w-100 px-0">
    @endif
</div>
<div class="row nav_ejes">
    @if ($indicador->programa_derivado == 'Plan Estatal de Desarrollo 2024-2030')

    @php
    $ejes = [
    ['nombre' => 'Eje 1', 'url' => '/ped/eje-1', 'programa' => 'Humanismo con Bienestar'],
    ['nombre' => 'Eje 2', 'url' => '/ped/eje-2', 'programa' => 'Prosperidad y Estabilidad Económica'],
    ['nombre' => 'Eje 3', 'url' => '/ped/eje-3', 'programa' => 'Estado de Derecho, Seguridad y Justicia'],
    ['nombre' => 'Eje 4', 'url' => '/ped/eje-4', 'programa' => 'Desarrollo Urbano y Crecimiento Sostenible'],
    ['nombre' => 'Eje 5', 'url' => '/ped/eje-5', 'programa' => 'Gobierno Transformador y de Resultados'],
    ['nombre' => 'Eje 6', 'url' => '/ped/eje-6', 'programa' => 'Por Amor a Puebla'],
    ];
    @endphp

    @foreach ($ejes as $eje)
    @php
    $nombreClase = 'nav_' . strtolower(str_replace(' ', '', $eje['nombre']));
    $esActivo = $indicador->programa == $eje['programa'];
    @endphp

    <div class="col-md-2 p-0 ocultar_submenu">
        <a href="{{ url($eje['url']) }}"
            @class([ 'd-block w-100 text-decoration-none' ,
            $nombreClase=> !$esActivo,
            $nombreClase . '_active' => $esActivo
            ])>
            {{ $eje['nombre'] }}
        </a>
    </div>
    @endforeach

    @else
    <div class="row nav_derivados ms-0">
        <div @class([ 'col-md-3 nav_derivados1 ocultar_submenu' , 'nav_derivados1_active'=> $indicador->indicadorable_type === 'App\Models\CatProgramaDerivadoSectorial'
            ])>
            <a href="{{ url('/ped-programas/sectoriales') }}" class="dropbtn nav_eje_link">Sectoriales</a>
        </div>

        <div @class([ 'col-md-3 nav_derivados2 ocultar_submenu' , 'nav_derivados2_active'=> $indicador->indicadorable_type === 'App\Models\CatProgramaDerivadoEspecial'
            ])>
            <a href="{{ url('/ped-programas/especiales') }}" class="dropbtn nav_eje_link">Especiales</a>
        </div>

        <div @class([ 'col-md-3 nav_derivados3 ocultar_submenu' , 'nav_derivados3_active'=> $indicador->indicadorable_type === 'App\Models\CatProgramaDerivadoInstitucional'
            ])>
            <a href="{{ url('/ped-programas/institucionales') }}" class="dropbtn nav_eje_link">Institucionales</a>
        </div>

        <div @class([ 'col-md-3 nav_derivados4 ocultar_submenu' , 'nav_derivados4_active'=> $indicador->indicadorable_type === 'App\Models\CatProgramaDerivadoRegional'
            ])>
            <a href="{{ url('/ped-programas/regionales') }}" class="dropbtn nav_eje_link">Regionales</a>
        </div>

    </div>
    @endif
</div>
&nbsp;
<div class="container" id="imprimir">
    <div class="row" id="encabezado" style="display:none;">
        {{-- <img class="img-fluid" src="../components/TCPDF/images/banner_S.jpg" width="100%"/> --}}
        <img class="img-fluid w-100" src="{{ asset('img/logos_sped.png') }}" title="Pleca ficha">
    </div>
    <div class="row ficha">
        <div class="row div-ficha">
            <h2 style="color:{{ $indicador->color }}">
                Indicador
            </h2>
            <div class="col-12 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 ficha_indicador"
                                    style="color:{{ $indicador->color }};">
                                    {{ $indicador->nombre }}
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12 ficha_datos align-self-center"
                                    style="text-align: center; margin-top:10px;">
                                    @foreach ($indicador->ods->unique('id') as $ods)
                                    <img src="{{ asset('/img/Icons_ODS/' . $ods->id . '.png') }}"
                                        alt="Imagen de ODS {{ $ods->id }}" class="hvr-wobble-top"
                                        style="width:60px; border-radius: 5px 5px 5px 5px;">
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-6 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-6 ficha_titulo"
                                    style="color:{{ $indicador->color }};">
                                    Institución responsable
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6 ficha_datos">
                                    {{ $indicador->institucion->nombre ?? 'Sin institución responsable' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-6 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-6 ficha_titulo"
                                    style="color:{{ $indicador->color }};">
                                    Instrumento de Planeación
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6 ficha_datos">
                                    @if ($indicador->programa_derivado == 'Programa Regional')
                                    {{ $indicador->programa_derivado }} de {{ $indicador->tematica }}
                                    @else
                                    {{ $indicador->programa_derivado }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-6 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 ficha_titulo" style="color:{{ $indicador->color }};">
                                    @if ($indicador->programa_derivado == 'Programa Regional')
                                    Temática
                                    @else
                                    Eje
                                    @endif
                                </div>
                                <div class="col-md-9 ficha_datos">
                                    {{ $indicador->programa }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-6 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 ficha_titulo" style="color:{{ $indicador->color }};">
                                    Temática
                                </div>
                                <div class="col-md-9 ficha_datos">
                                    {{ $indicador->tematica }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row my-2">
            <h2 style="color:{{ $indicador->color }}; margin-left:20px;">Identificador del Indicador</h2>
            <div class="col-xs-12 col-sm-12 col-md-12 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-2 ficha_titulo"
                                    style="color:{{ $indicador->color }};">
                                    Descripción
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-10 ficha_datos">
                                    {{ $indicador->descripcion }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4 col-md-12 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-2 ficha_titulo"
                                    style="color:{{ $indicador->color }};">
                                    Fórmula
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-10 ficha_datos">
                                    {{ $indicador->formula }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-3 col-md-4 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-7 ficha_titulo"
                                    style="color:{{ $indicador->color }};">
                                    Periodicidad
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-5 ficha_datos">
                                    {{ $indicador->periodicidad }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4 col-md-4 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-6 ficha_titulo"
                                    style="color:{{ $indicador->color }};">
                                    Unidad de Medida
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6 ficha_datos">
                                    {{ $indicador->unidad_medida }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-3 col-md-4 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-6 ficha_titulo"
                                    style="color:{{ $indicador->color }};">
                                    Tendencia
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6 ficha_datos">
                                    {{ $indicador->tendencia }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-3 col-md-4 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-6 ficha_titulo"
                                    style="color:{{ $indicador->color }};">
                                    Línea base {{ $indicador->linea_base }}
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6 ficha_datos">
                                    @if(isset($indicador->dato_linea_base))
                                    {{ number_format($indicador->dato_linea_base, $indicador->id === 100 ? 6 : 2, '.', ',') }}
                                    @else
                                    Sin datos
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-3 col-md-4 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-6 ficha_titulo"
                                    style="color:{{ $indicador->color }};">
                                    Meta 2030
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6 ficha_datos">
                                     @if(isset($indicador->meta_2024))
                                    {{ number_format($indicador->meta_2024, $indicador->id === 100 ? 6 : 2, '.', ',') }}
                                    @else
                                    Sin datos
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-3 col-md-4 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-8 ficha_titulo"
                                    style="color:{{ $indicador->color }};">
                                    Próxima Actualización
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-4 ficha_datos">
                                    {{ $indicador->getProximaFechaActualizacionParaVista('N/D', true) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-12 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-2 ficha_titulo"
                                    style="color:{{ $indicador->color }};">
                                    Fuente
                                </div>
                                @if ($indicador->liga == null || $indicador->liga == '0')
                                <div class="col-xs-12 col-sm-12 col-md-10 ficha_datos">
                                    {{ $indicador->fuente }}
                                </div>
                                @else
                                <div class="col-xs-12 col-sm-12 col-md-9 ficha_datos">
                                    {{ $indicador->fuente }}
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-1 ficha_datos">
                                    <a href="{{ $indicador->liga }}" target="_blank">
                                        <i class="fa fa-globe fa-2x" aria-hidden="true"></i>
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-6 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-6 ficha_titulo"
                                    style="color:{{ $indicador->color }};">
                                    Cobertura Geográfica
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6 ficha_datos">
                                    {{ $indicador->cobertura }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-6 my-2">
                <div class="card h-100">
                    <div class="card-content card_ficha"
                        style="border-top: 12px solid {{ $indicador->color ?? '#6c757d' }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 ficha_titulo"
                                    style="color:{{ $indicador->color }};">
                                    Semaforización
                                </div>
                                <div class="col-12 ficha_datos_semaforizacion">
                                    @if ($indicador->semaforizacion_validada)
                                    @php
                                    $estado = $indicador->semaforizacion_validada;
                                    $icono = '';
                                    $colorClase = 'text-muted';
                                    $explicacionDetallada =
                                    'El estado de semaforización se basa en el porcentaje de avance respecto a la meta.';

                                    switch (strtolower($estado)) {
                                    case 'excedido':
                                    $icono = 'fas fa-star text-primary';
                                    $colorClase = 'text-primary fw-bold';
                                    $explicacionDetallada =
                                    'El valor logrado del indicador supera en 10% a la meta programada, es decir, el resultado del indicador se desvió significativamente de la meta establecida';
                                    break;
                                    case 'aceptable':
                                    $icono = 'fas fa-check-circle text-success';
                                    $colorClase = 'text-success fw-bold';
                                    $explicacionDetallada =
                                    'El valor logrado del indicador se encuentra entre -9% y +10% por debajo y por encima de la meta programada, es decir, se mantiene dentro de los rangos establecidos como aceptables.';
                                    break;
                                    case 'moderado':
                                    $icono = 'fas fa-exclamation-triangle text-warning';
                                    $colorClase = 'text-warning fw-bold';
                                    $explicacionDetallada =
                                    'El valor logrado del indicador es menor que la meta programada, representa un avance significativo, pero deficiente o moderado para alcanzar la meta establecida.';
                                    break;
                                    case 'insuficiente':
                                    $icono = 'fas fa-times-circle text-danger';
                                    $colorClase = 'text-danger fw-bold';
                                    $explicacionDetallada =
                                    'El valor alcanzado del indicador está muy por debajo de la meta programada, lo que representa un avance insuficiente para alcanzar la meta establecida.';
                                    break;
                                    case 'no clasificado':
                                    default:
                                    $estado = 'No Clasificado';
                                    $icono = 'fas fa-question-circle text-muted';
                                    $colorClase = 'text-muted fw-bold';
                                    $explicacionDetallada =
                                    'No se pudo clasificar el indicador, usualmente por falta de datos actuales, línea base o meta no definida.';
                                    break;
                                    }
                                    @endphp

                                    <div class="d-flex align-items-center justify-content-center mb-1">
                                        <span style="font-size: 1.5rem; margin-right: 8px;"
                                            class="{{ $colorClase }}">
                                            <i class="{{ $icono }}"></i>
                                        </span>
                                        <span class="text-black" style="font-size: 1.1rem;">
                                            {{ $estado }}
                                        </span>
                                    </div>
                                    <p class="text-muted" style="font-size: 0.85rem; margin-top: 5px;">
                                        {{ $explicacionDetallada }}
                                    </p>
                                    @else
                                    <span class="text-muted">Semaforización: N/D</span>
                                    <p class="text-muted" style="font-size: 0.85rem; margin-top: 5px;">
                                        No hay datos disponibles para determinar el estado de semaforización.
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row my-2" style="margin:-20px;">
            <h2 style="color:{{ $indicador->color }}; margin-left:20px;">Principales Resultados</h2>
            <div class="col-xs-12 col-sm-12 col-md-12 my-2">
                <div class="card">
                    <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 ficha_datos">
                                    {!! nl2br(e($indicador->getResultadosParaVista('Sin resultados registrados.', true))) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row ocultar_datos line-break">
            <h2 style="color:{{ $indicador->color }}; margin-left:20px;" class="ocultar_tabla">Resultados Históricos
            </h2>
            <div class="col-xs-12 col-sm-12 col-md-12 ocultar_tabla">
                <div class="panel-body table-responsive">
                    <table class="table_resultados">
                        <thead style="background-color:{{ $indicador->color }}">
                            <tr>
                                @php
                                $anioActual = \Carbon\Carbon::now()->year;
                                $anioInicio = 2015;
                                @endphp

                                @for ($year = $anioInicio; $year <= $anioActual; $year++)
                                    @php
                                    $isFirst=$year==$anioInicio;
                                    $isLast=$year==$anioActual;
                                    @endphp

                                    @if ($isFirst)
                                    <th style="border-radius: 20px 0px 0px 0px;">{{ $year }}</th>
                                    @elseif ($isLast)
                                    <th style="border-radius: 0px 20px 0px 0px;">{{ $year }}</th>
                                    @else
                                    <th>{{ $year }}</th>
                                    @endif
                                    @endfor
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @for ($year = $anioInicio; $year <= $anioActual; $year++)
                                    <td>
                                    {{ $indicador->getValorDatoAnual($year, 'N/D', true) }}
                                    </td>
                                    @endfor
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            &nbsp;
        </div>
        @php
        // 1. Configuración de Rango
        $anioInicio = 2015;
        $anioFin = 2030;

        // 2. Preparar valores de referencia (fuera del bucle para mayor velocidad)
        $anioLB = $indicador->linea_base ?? null;

        // Limpieza de datos: extraemos solo números y puntos para evitar errores de formato
        $valorLB = !empty(trim((string)$indicador->dato_linea_base))
        ? (float) preg_replace('/[^0-9.-]/', '', $indicador->dato_linea_base)
        : null;

        $valorMeta = !empty(trim((string)$indicador->meta_2024))
        ? (float) preg_replace('/[^0-9.-]/', '', $indicador->meta_2024)
        : null;

        // 3. Inicializar arrays para la gráfica
        $categoriasEjeX_php = [];
        $datosParaGraficaPrincipal_php = [];
        $datosLineaBasePunto_php = [];
        $datosMetaPunto_php = [];

        // 4. Bucle Único: Construimos las 3 series simultáneamente
        for ($year = $anioInicio; $year <= $anioFin; $year++) {
            // Eje X
            $categoriasEjeX_php[]=(string) $year;

            // Serie Histórica (Datos Reales VALIDADOS)
            $datoAnual=$indicador->datos_anuales_validados->firstWhere('anio', $year);
            if ($datoAnual && !empty(trim((string)$datoAnual->valor_dato))) {
            $limpio = preg_replace('/[^0-9.-]/', '', $datoAnual->valor_dato);
            $datosParaGraficaPrincipal_php[] = is_numeric($limpio) ? (float)$limpio : null;
            } else {
            $datosParaGraficaPrincipal_php[] = null;
            }

            // Serie Línea Base: solo pone valor en el año que corresponde
            $datosLineaBasePunto_php[] = ($year == $anioLB) ? $valorLB : null;

            // Serie Meta: solo pone valor en el año 2030
            $datosMetaPunto_php[] = ($year == 2030) ? $valorMeta : null;
            }

            // 5. Variables de texto para JS
            $nombreIndicadorParaGrafica_js = str_replace(["\r", "\n"], ' ', $indicador->nombre ?? 'Indicador');
            $unidadMedidaParaGrafica_js = $indicador->unidad_medida ?? 'Valor';
            $colorIndicadorParaGrafica_js = $indicador->color ?? '#008FFB';
            $nombreSerieLineaBase_php = 'Línea Base ' . ($anioLB ?? '');
            $anioLB_js = $anioLB ? (string)$anioLB : null;
            @endphp
            <div class="row">
                <h2 style="color:{{ $indicador->color }}; margin-left:20px;" class="ocultar_grafica">Gráfico</h2>
                <div class="col-xs-12 col-sm-12 col-md-12 ocultar_grafica">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                            <div class="card-body">
                                <div class="d-flex justify-items-center justify-content-center">
                                    <div id="grafica" style="width:900px">
                                    </div>
                                </div>
                                <p style="text-align: center; font-size: 12px; color: #777;">
                                    Fuente: {{ $indicador->fuente ?? 'Sin fuente disponible' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
&nbsp;
<div class="container ocultar_grafica">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-5" style="text-align:right; color:{{ $indicador->color }};">
        </div>
        {{-- <div class="col-xs-12 col-sm-12 col-md-2 hvr-grow my-2">
                <a href="{{ route('generarFicha', $indicador->id) }}" target="_blank">
        <img src="{{ asset('/img/Ficha_Tecnica.jpg') }}"
            style=" float:left; border-radius: 5px 5px 5px 5px; width: 100%; cursor:pointer;">
        </a>
    </div> --}}

    <div class="col-xs-12 col-sm-12 col-md-2 hvr-grow my-2" onclick="printDiv('imprimir')">
        <img src="{{ asset('/img/Ficha_Tecnica.jpg') }}"
            style=" float:left; border-radius: 5px 5px 5px 5px; width: 100%; cursor:pointer;">
    </div>
</div>
</div>
@section('jss-final')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Función de formateo optimizada
        function formatNumber(value, decimalPlaces = 2) {
            if (value === null || value === undefined || isNaN(parseFloat(value))) return "N/D";
            return parseFloat(value).toLocaleString('en-US', {
                minimumFractionDigits: decimalPlaces,
                maximumFractionDigits: decimalPlaces
            });
        }

        // Variables de Blade/PHP
        const nombreIndicadorJS = @json($nombreIndicadorParaGrafica_js);
        const unidadMedidaJS = @json($unidadMedidaParaGrafica_js);
        const colorIndicadorJS = @json($colorIndicadorParaGrafica_js);

        const options = {
            series: [{
                    name: @json($nombreSerieLineaBase_php),
                    data: @json($datosLineaBasePunto_php),
                    type: 'line',
                    zIndex: 10
                },
                {
                    name: unidadMedidaJS,
                    data: @json($datosParaGraficaPrincipal_php),
                    type: 'line'
                },
                {
                    name: 'Meta 2030',
                    data: @json($datosMetaPunto_php),
                    type: 'line',
                    dataLabels: {
                        enabled: true,
                        formatter: (val) => formatNumber(val),
                        offsetY: -10,
                        style: {
                            fontSize: '12px',
                            colors: ["#FF0000"]
                        }
                    }
                }
            ],
            chart: {
                id: 'grafica_indicador',
                height: 380,
                type: 'line',
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true,
                    speed: 400
                }
            },
            colors: ['#00E396', colorIndicadorJS, '#FF0000'],
            stroke: {
                curve: 'smooth',
                width: [2, 4, 2],
                dashArray: [5, 0, 5]
            },
            tooltip: {
                shared: false,
                intersect: true,
                theme: 'light',
                y: {
                    formatter: (val) => formatNumber(val) + ' ' + unidadMedidaJS
                }
            },
            markers: {
                size: [6, 4, 7],
                hover: {
                    sizeOffset: 3
                }
            },
            xaxis: {
                categories: @json($categoriasEjeX_php),
                title: {
                    text: 'Año'
                }
            },
            yaxis: {
                labels: {
                    formatter: (val) => formatNumber(val, 0)
                },
                title: {
                    text: unidadMedidaJS
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center'
            },
            dataLabels: {
                enabled: true
            }
        };

        // Renderizado seguro
        const chartContainer = document.querySelector("#grafica");
        if (chartContainer) {
            const chart = new ApexCharts(chartContainer, options);
            chart.render();
        }
    });
</script>

<script>
    function printDiv(imprimir) {
        var contenido = document.getElementById(imprimir).innerHTML;
        var contenidoOriginal = document.body.innerHTML;
        document.body.innerHTML = contenido;
        encabezado.style.display = 'block';
        // grafica.style.width = '900px';
        window.print();
        document.body.innerHTML = contenidoOriginal;
        location.reload();
    }
</script>
@endsection
@endsection
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
<link rel="stylesheet" href="{{ asset('css/ficha-tecnica.css') }}">
@endsection
@section('jss-inicial')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endsection
@section('content')
@php
// 1. Deducir si es PED o Derivado
$esPED = $indicador->programa_derivado == 'Plan Estatal de Desarrollo';
$tipoNav = $esPED ? 'ped' : 'derivados';

// 2. Deducir el ítem activo y el banner
$itemActivo = null;
$bannerImg = null;

if ($esPED) {
// Mapeamos el nombre del programa al número de eje
$mapaEjes = [
'Humanismo con Bienestar' => 1,
'Prosperidad y Estabilidad Económica' => 2,
'Estado de Derecho, Seguridad y Justicia' => 3,
'Desarrollo Urbano y Crecimiento Sostenible' => 4,
'Gobierno Transformador y de Resultados' => 5,
'Por Amor a Puebla' => 6,
];
$itemActivo = $mapaEjes[$indicador->programa] ?? null;
if ($itemActivo) {
$bannerImg = 'img/Banners/Banner_PED/Eje_' . $itemActivo . '.jpg';
}
} else {
// Para derivados usamos su namespace completo
$itemActivo = $indicador->indicadorable_type;
// Si tienes banners para derivados, los puedes asignar aquí
// $bannerImg = 'img/Banners/Banner_Derivados.jpg';
}
@endphp

@include('partials.nav-unificada', [
'tipoNav' => $tipoNav,
'itemActivo'=> $itemActivo,
'bannerImg' => $bannerImg,
'colorTema' => $indicador->color ?? '#691A32'
])
<div class="container mt-4" id="imprimir">
    <div class="row" id="encabezado" style="display:none;">
        <img class="img-fluid w-100" src="{{ asset('img/logos_sped.png') }}" title="Pleca ficha">
    </div>
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <h1 class="fw-bold mb-0" style="color: {{ $indicador->color ?? '#2b2b2b' }};">
            {{ $indicador->nombre }}
        </h1>
        <div class="d-flex gap-2 ocultar_impresion">
            @foreach ($indicador->ods->unique('id') as $ods)
            <img src="{{ asset('/img/Icons_ODS/' . $ods->id . '.png') }}" alt="ODS {{ $ods->id }}" style="height: 45px; border-radius: 4px;" class="shadow-sm">
            @endforeach
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card card-ficha-moderna h-100 p-4">
                <h3 class="ficha-section-title" style="color: {{ $indicador->color ?? '#484747' }};">Planeación</h3>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="ficha-label">Institución Responsable</div>
                        <div class="ficha-value">{{ $indicador->institucion->nombre ?? 'Sin institución responsable' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="ficha-label">Instrumento de Planeación</div>
                        <div class="ficha-value">
                            {{ $indicador->programa_derivado == 'Programa Regional' ? $indicador->programa_derivado . ' de ' . $indicador->tematica : $indicador->programa_derivado }}
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="ficha-label">{{ $indicador->programa_derivado == 'Programa Regional' ? 'Temática' : 'Eje o Programa' }}</div>
                        <div class="ficha-value">{{ $indicador->programa }}</div>
                    </div>
                    @if($indicador->programa_derivado != 'Programa Regional')
                    <div class="col-12">
                        <div class="ficha-label">Temática</div>
                        <div class="ficha-value">{{ $indicador->tematica }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 mt-3 mt-lg-0">
            <div class="card card-ficha-moderna h-100 p-4">
                <h3 class="ficha-section-title" style="color: {{ $indicador->color ?? '#484747' }};">Detalles Técnicos</h3>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="ficha-label">Descripción</div>
                        <div class="ficha-value text-muted fs-95-justify">{{ $indicador->descripcion }}</div>
                    </div>
                    <div class="col-sm-12">
                        <div class="ficha-label">Fórmula</div>
                        <div class="ficha-value text-muted ws-pre fs-95-justify">{{ $indicador->formula }}</div>
                    </div>
                    <div class="col-sm-12">
                        <div class="ficha-label">Unidad de Medida</div>
                        <div class="ficha-value">{{ $indicador->unidad_medida }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-ficha-moderna p-4 mt-2">
        <h3 class="ficha-section-title" style="color: {{ $indicador->color ?? '#484747' }};">Gestión de Gobierno</h3>
        @php
        // Lógica de semaforización (Sección Gestión de Gobierno de la Ficha Técnica)
        $semText = $indicador->semaforizacion_validada ?: 'No Clasificado';
        $colorSemaforo = '#6c757d';
        $esDatoLineaBase = false;
        $explicacionDetallada='Sin datos suficientes para clasificar.' ;

        switch(strtolower($semText)){
        case 'excedido' :
        $colorSemaforo='#0d6efd' ;
        $explicacionDetallada='El valor logrado del indicador supera en 10% a la meta programada, es decir, el resultado del indicador se desvió significativamente de la meta establecida.' ;
        break;
        case 'aceptable' :
        $colorSemaforo='#198754' ;
        $explicacionDetallada='El valor logrado del indicador se encuentra entre -9% y +10% por debajo y por encima de la meta programada, es decir, se mantiene dentro de los rangos establecidos como aceptables.' ;
        break;
        case 'moderado' :
        $colorSemaforo='#ffc107' ;
        $explicacionDetallada='El valor logrado del indicador es menor que la meta programada, representa un avance significativo, pero deficiente o moderado para alcanzar la meta establecida.' ;
        break;
        case 'insuficiente' :
        $colorSemaforo='#dc3545' ;
        $explicacionDetallada='El valor alcanzado del indicador está muy por debajo de la meta programada, lo que representa un avance insuficiente para alcanzar la meta establecida.' ;
        break;
        case 'solo línea base' :
        $colorSemaforo='#adb5bd' ;
        $esDatoLineaBase=true;
        $explicacionDetallada='El indicador sólo cuenta con el dato de línea base, por lo que está a la espera de un nuevo periodo de medición.' ;
        break;
        case 'no clasificado' :
        default:
        // Si hay error, usamos el diseño de pendiente o dejamos oculto
        $esDatoLineaBase=true;
        break;
        }

        $avanceReal=$indicador->avance_validado ?? $indicador->avance ?? 0;
        $avanceVal = $avanceReal;
        $chartVal = $avanceVal > 100 ? 100 : $avanceVal;

        $ultimoDatoValidado = $indicador->datos_anuales_validados->filter(function($d) {
        return !empty(trim((string)$d->valor_dato));
        })->sortByDesc('anio')->first();
        $anioReciente = $ultimoDatoValidado ? $ultimoDatoValidado->anio : '';
        $valorReciente = $ultimoDatoValidado ? $ultimoDatoValidado->valor_dato : 'N/D';
        @endphp

        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="row g-4 text-center text-md-start">
                    <div class="col-sm-4 border-end-sm">
                        <div class="ficha-label">Línea Base {{ $indicador->linea_base }}</div>
                        <div class="fs-4 fw-bold text-truncate" style="color: {{ $indicador->color ?? '#2b2b2b' }};">
                            {{ isset($indicador->dato_linea_base) ? number_format((float)str_replace(',', '', $indicador->dato_linea_base), $indicador->id == 100 ? 6 : 2, '.', ',') : 'N/D' }}
                        </div>
                    </div>
                    <div class="col-sm-4 border-end-sm">
                        <div class="ficha-label">Meta 2030</div>
                        <div class="fs-4 fw-bold text-truncate" style="color: {{ $indicador->color ?? '#2b2b2b' }};">
                            {{ isset($indicador->meta_2024) ? number_format((float)str_replace(',', '', $indicador->meta_2024), $indicador->id == 100 ? 6 : 2, '.', ',') : 'N/D' }}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="ficha-label">Tendencia</div>
                        <div class="fs-5 fw-semibold text-dark mt-1">
                            {{ $indicador->tendencia }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mt-4 mt-md-0 border-start-md text-center d-flex flex-column align-items-center">

                @if(!$esDatoLineaBase)
                <div class="ficha-label mb-2">Último Dato {{ $anioReciente }}</div>
                <div class="fs-4 fw-bold text-truncate mb-3" style="color: {{ $indicador->color ?? '#2b2b2b' }};">
                    {{ $valorReciente !== 'N/D' ? number_format((float)str_replace(',', '', $valorReciente), $indicador->id == 100 ? 6 : 2, '.', ',') : 'N/D' }}
                </div>
                @endif
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <span class="badge rounded-pill px-3 py-2 shadow-sm fs-90r" style="background-color: {{ $colorSemaforo }}; cursor: help;"
                        data-bs-toggle="popover"
                        data-bs-trigger="hover focus"
                        data-bs-placement="top"
                        title="Estado: {{ $semText }}"
                        data-bs-content="{{ $explicacionDetallada }}">
                        {{ $semText }}
                    </span>
                </div>

                @if($esDatoLineaBase)
                <div class="py-4 d-flex flex-column align-items-center justify-content-center">
                    <i class="fas fa-clock text-muted opacity-50 mb-2 fs-35rem"></i>
                    <div class="small text-muted mt-2 fw-semibold text-center text-uppercase tracking-wide">
                        Medición Pendiente
                    </div>
                </div>
                @else
                <div id="gauge-ficha" class="grafico-gauge-ficha" style="cursor: help;"
                    data-bs-toggle="popover"
                    data-bs-trigger="hover focus"
                    data-bs-placement="top"
                    title="Estado: {{ $semText }}"
                    data-bs-content="{{ $explicacionDetallada }}"></div>
                <div class="fw-bold fs-4 text-dark mt-35px">{{ number_format($avanceVal, 1) }}%</div>
                <div class="small text-muted mt-1 fw-semibold">Avance Meta</div>
                @endif

            </div>
        </div>
    </div>

    <div class="card card-ficha-moderna p-4 mt-2">
        <h3 class="ficha-section-title" style="color: {{ $indicador->color ?? '#484747' }};">Calidad de la Información</h3>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="ficha-label">Fuente</div>
                <div class="ficha-value">
                    {{ $indicador->fuente }}
                    @if ($indicador->liga && $indicador->liga != '0')
                    <a href="{{ $indicador->liga }}" target="_blank" class="ms-2 text-primary ocultar_impresion" title="Abrir fuente">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    @endif
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="ficha-label">Cobertura Geográfica</div>
                <div class="ficha-value">{{ $indicador->cobertura }}</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="ficha-label">Periodicidad</div>
                <div class="ficha-value">{{ $indicador->periodicidad }}</div>
            </div>
        </div>
    </div>

    <div class="card card-ficha-moderna p-4 mt-2 mb-5">
        <h3 class="ficha-section-title" style="color: {{ $indicador->color ?? '#484747' }};">Evolución Histórica</h3>
        <div class="row align-items-center">

            <div class="col-md-4 mb-4 mb-md-0 pe-md-4 border-end-md">
                <div class="table-responsive rounded shadow-sm border table-datos">
                    <table class="table table-hover table-historicos text-center mb-0">
                        <thead class="sticky-top">
                            <tr>
                                <th class="text-start" style="background-color: {{ $indicador->color ?? '#6c757d' }};">Año</th>
                                <th style="background-color: {{ $indicador->color ?? '#6c757d' }};">Valor Alcanzado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $anioActual = \Carbon\Carbon::now()->year;
                            $minHistorico = $indicador->datos_anuales_validados->min('anio');
                            $minLB = $indicador->linea_base ? (int)$indicador->linea_base : 2015;
                            $anioInicio = min($minLB, $minHistorico ?: 2015);
                            $anioFin = 2030;
                            @endphp
                            @for ($year = $anioInicio; $year <= $anioActual; $year++)
                                @php $valorDato=$indicador->getValorDatoAnual($year, 'N/D', true); @endphp
                                @if($valorDato !== 'N/D' || $year == $indicador->linea_base)
                                <tr @if($year==$indicador->linea_base) class="table-info-linea-base" @endif>

                                    <td class="text-muted d-flex justify-content-between align-items-center">
                                        @if($year == $indicador->linea_base)
                                        <span class="fw-bold">{{ $year }}</span>
                                        @else
                                        <span>{{ $year }}</span>
                                        @endif

                                        @if($year == $indicador->linea_base)
                                        <span class="badge rounded-pill bg-success badge-linea-base">L. BASE</span>
                                        @endif
                                    </td>
                                    <!-- <td class="text-muted">
                                        {{ $year }}
                                        @if($year == $indicador->linea_base)
                                        <br>
                                        <span class="badge rounded-pill bg-success ms-1 badge-linea-base">L. BASE</span>
                                        @endif
                                    </td> -->
                                    <td class="fw-bold @if($year == $indicador->linea_base) text-success @else text-dark @endif">
                                        {{ $valorDato }}
                                    </td>
                                </tr>
                                @endif
                                @endfor

                                @if(isset($indicador->meta_2024))
                                <tr class="table-info-meta">
                                    <td class="fw-bold d-flex justify-content-between align-items-center">
                                        <span>2030</span>
                                        <span class="badge rounded-pill bg-danger badge-meta-2030">META</span>
                                    </td>
                                    <td class="fw-bold text-danger">
                                        {{ isset($indicador->meta_2024) ? number_format((float)str_replace(',', '', $indicador->meta_2024), $indicador->id == 100 ? 6 : 2, '.', ',') : 'N/D' }}
                                    </td>
                                </tr>
                                @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-8 ps-md-4 text-center">
                <div id="grafica-historica" class="w-100 grafico-historico-ficha"></div>
            </div>

        </div>
    </div>
</div>
<div class="container pb-5 text-end ocultar_impresion">
    <button id="btnImprimirFicha" class="btn btn-lg shadow text-white fw-bold px-4 br-50px" style="background-color: {{ $indicador->color ?? '#6c757d' }};">
        <i class="fas fa-print me-2"></i> Imprimir Ficha
    </button>
</div>

@section('jss-final')
@php
// =========================================================================
// 1. PREPARACIÓN DE DATOS PARA LA GRÁFICA HISTÓRICA (LÍNEAS)
// =========================================================================
$minHistorico = $indicador->datos_anuales_validados->min('anio');
$minLB = $indicador->linea_base ? (int)$indicador->linea_base : 2015;
$anioInicio = min($minLB, $minHistorico ?: 2015);
$anioFin = 2030;

$anioLB = $indicador->linea_base ?? null;

// Limpieza estricta: extraemos solo números y puntos
$valorLB = !empty(trim((string)$indicador->dato_linea_base))
? (float) preg_replace('/[^0-9.-]/', '', $indicador->dato_linea_base)
: null;

$valorMeta = !empty(trim((string)$indicador->meta_2024))
? (float) preg_replace('/[^0-9.-]/', '', $indicador->meta_2024)
: null;

$categoriasEjeX_php = [];
$datosParaGraficaPrincipal_php = [];
$datosLineaBasePunto_php = [];
$datosMetaPunto_php = [];

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

    // Serie Línea Base (solo punto en su año)
    $datosLineaBasePunto_php[] = ($year == $anioLB) ? $valorLB : null;

    // Serie Meta (solo punto en 2030)
    $datosMetaPunto_php[] = ($year == 2030) ? $valorMeta : null;
    }

    $nombreIndicadorJS = str_replace(["\r", "\n"], ' ', $indicador->nombre ?? 'Indicador');
    $unidadMedidaJS = $indicador->unidad_medida ?? 'Valor';
    $colorIndicadorJS = $indicador->color ?? '#008FFB';
    $nombreSerieLineaBase_php = 'Línea Base ' . ($anioLB ?? '');
    @endphp

    <script>
        window.fichaConfig = {
            chartVal: @json($chartVal),
            colorSemaforo: @json($colorSemaforo),
            nombreSerieLineaBase: @json($nombreSerieLineaBase_php),
            datosLineaBasePunto: @json($datosLineaBasePunto_php),
            unidadMedida: @json($unidadMedidaJS),
            datosParaGraficaPrincipal: @json($datosParaGraficaPrincipal_php),
            datosMetaPunto: @json($datosMetaPunto_php),
            colorIndicador: @json($colorIndicadorJS),
            categoriasEjeX: @json($categoriasEjeX_php),
            esDatoLineaBase: @json($esDatoLineaBase),
            idIndicador: @json($indicador -> id)
        };
    </script>
    <script src="{{ asset('js/ficha-tecnica.js') }}"></script>
    @endsection
    @endsection
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
    @endsection
    @section('jss-inicial')
    @endsection
    @section('content')
        @php
            $esEjePED = $indicador->indicadorable instanceof \App\Models\CatEje;
            $esPED = $esEjePED || $indicador->programa_derivado == 'Plan Estatal de Desarrollo';
            $tipoNav = $esPED ? 'ped' : 'derivados';

            $itemActivo = null;
            $bannerImg = null;

            if ($esPED) {

            $mapaEjes = [
            'Humanismo con Bienestar' => 1,
            'Prosperidad y Estabilidad Económica' => 2,
            'Estado de Derecho, Seguridad y Justicia' => 3,
            'Desarrollo Urbano y Crecimiento Sostenible' => 4,
            'Gobierno Transformador y de Resultados' => 5,
            'Por Amor a Puebla' => 6,
            ];
            $itemActivo = $esEjePED
                ? (int) $indicador->indicadorable->numero
                : ($mapaEjes[$indicador->programa] ?? null);
            if ($itemActivo) {
            $bannerImg = 'img/Banners/Banner_PED/Eje_' . $itemActivo . '.jpg';
            }
            } else {
            $itemActivo = $indicador->indicadorable_type;
            if (!$itemActivo) {
                $itemActivo = match (true) {
                    \Illuminate\Support\Str::startsWith($indicador->programa_derivado, 'Programa Sectorial') => 'App\\Models\\CatProgramaDerivadoSectorial',
                    \Illuminate\Support\Str::startsWith($indicador->programa_derivado, 'Programa Especial') => 'App\\Models\\CatProgramaDerivadoEspecial',
                    \Illuminate\Support\Str::startsWith($indicador->programa_derivado, 'Programa Regional') => 'App\\Models\\CatProgramaDerivadoRegional',
                    \Illuminate\Support\Str::startsWith($indicador->programa_derivado, 'Programa Institucional') => 'App\\Models\\CatProgramaDerivadoInstitucional',
                    default => null,
                };
            }
            }
        @endphp

        @include('partials.nav-unificada', [
        'tipoNav' => $tipoNav,
        'itemActivo'=> $itemActivo,
        'bannerImg' => $bannerImg,
        'colorTema' => $indicador->color ?? '#0c312d'
        ])

        <div class="ficha-background">
            <div class="container mt-4 contenedor__ficha ficha-page" id="imprimir">
                <div class="row" id="encabezado" style="display:none;">
                    <img class="img-fluid w-100" src="{{ asset('img/logos_sped.png') }}" title="Pleca ficha">
                </div>
                <header class="ficha-hero">
                    <div class="ficha-hero__content">
                        <div class="ficha-kicker"><i class="fas fa-chart-line me-2"></i>Ficha técnica del indicador</div>
                        <h1 class="fw-bold mb-0" style="--ficha-accent: {{ $indicador->color ?? '#9d2449' }};">
                            {{ $indicador->nombre }}
                        </h1>
                    </div>
                    <div class="ficha-hero__ods ocultar_impresion">
                        @foreach ($indicador->ods->unique('id') as $ods)
                                        <img src="{{ asset('/img/Icons_ODS/' . $ods->id . '.png') }}" alt="ODS {{ $ods->id }}" title="ODS {{ $ods->id }}">
                        @endforeach
                    </div>
                </header>

                <div class="row ficha-layout-two-col">
                    <div class="col-lg-6">
                        <div class="card card-ficha-moderna ficha-panel ficha-panel--planning h-100 p-4">
                            <h3 class="ficha-section-title" style="color: {{ $indicador->color ?? '#484747' }};"><span class="ficha-section-icon"><i class="fas fa-compass"></i></span>Planeación</h3>
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
                                @if($indicador->programasInstitucionales && $indicador->programasInstitucionales->isNotEmpty())
                                <div class="col-12 mt-2">
                                    <div class="ficha-label">Vinculado a Programas Institucionales</div>
                                    <div class="d-flex flex-wrap gap-2 mt-1">
                                        @foreach($indicador->programasInstitucionales as $progInst)
                                            <a href="{{ url('/ped-programas/institucionales/' . Illuminate\Support\Str::slug($progInst->nombre)) }}" 
                                            class="badge text-white px-3 py-2 rounded-pill text-decoration-none shadow-sm transition" 
                                            style="background-color: {{ $progInst->color ?? '#0c312d' }}; font-size: 0.8rem;"
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top"
                                            title="{{ $progInst->nombre }}">
                                                {{ $progInst->siglas }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mt-3 mt-lg-0">
                        <div class="card card-ficha-moderna ficha-panel ficha-panel--technical h-100 p-4">
                            <h3 class="ficha-section-title" style="color: {{ $indicador->color ?? '#484747' }};"><span class="ficha-section-icon"><i class="fas fa-sliders-h"></i></span>Detalles Técnicos</h3>
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

                <div class="card card-ficha-moderna ficha-panel ficha-panel--performance p-4 mt-2">
                    <h3 class="ficha-section-title" style="color: {{ $indicador->color ?? '#484747' }};">
                        <span class="ficha-section-icon"><i class="fas fa-bullseye"></i></span>Gestión de Gobierno
                    </h3>
                    @php
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

                    <div class="ficha-management-metrics">
                        <div class="ficha-metric-card">
                            <div class="ficha-metric-card__icon"><i class="fas fa-flag"></i></div>
                            <div>
                                <div class="ficha-label">Línea Base {{ $indicador->linea_base }}</div>
                                <div class="ficha-metric-card__value" style="color: {{ $indicador->color ?? '#2b2b2b' }};">
                                    {{ isset($indicador->dato_linea_base) ? number_format((float)str_replace(',', '', $indicador->dato_linea_base), $indicador->id == 100 ? 6 : 2, '.', ',') : 'N/D' }}
                                </div>
                            </div>
                        </div>
                        <div class="ficha-metric-card">
                            <div class="ficha-metric-card__icon"><i class="fas fa-chart-line"></i></div>
                            <div>
                                <div class="ficha-label">Tendencia</div>
                                <div class="ficha-metric-card__value ficha-metric-card__value--text">{{ $indicador->tendencia }}</div>
                            </div>
                        </div>
                        <div class="ficha-metric-card">
                            <div class="ficha-metric-card__icon"><i class="fas fa-bullseye"></i></div>
                            <div>
                                <div class="ficha-label">Meta 2030</div>
                                <div class="ficha-metric-card__value" style="color: {{ $indicador->color ?? '#2b2b2b' }};">
                                    {{ isset($indicador->meta_2024) ? number_format((float)str_replace(',', '', $indicador->meta_2024), $indicador->id == 100 ? 6 : 2, '.', ',') : 'N/D' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ficha-performance-board">
                        <div class="ficha-performance-board__latest">
                            @if(!$esDatoLineaBase)
                            <div class="ficha-label">Último Dato {{ $anioReciente }}</div>
                            <div class="ficha-performance-board__value" style="color: {{ $indicador->color ?? '#2b2b2b' }};">
                                {{ $valorReciente !== 'N/D' ? number_format((float)str_replace(',', '', $valorReciente), $indicador->id == 100 ? 6 : 2, '.', ',') : 'N/D' }}
                            </div>
                            @else
                            <div class="ficha-label">Último Dato</div>
                            <div class="ficha-performance-board__value">N/D</div>
                            @endif
                        </div>

                        <div class="ficha-performance-board__status">
                            <div class="ficha-label">Semaforización</div>
                            <span class="badge rounded-pill px-3 py-2 shadow-sm fs-90r" style="background-color: {{ $colorSemaforo }}; cursor: help;"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="top"
                                title="Estado: {{ $semText }}"
                                data-bs-content="{{ $explicacionDetallada }}">
                                {{ $semText }}
                            </span>
                        </div>

                        <div class="ficha-performance-board__gauge">
                            @if($esDatoLineaBase)
                            <div class="ficha-performance-board__pending">
                                <i class="fas fa-clock"></i>
                                <span>Medición pendiente</span>
                            </div>
                            @else
                            <div id="gauge-ficha" class="grafico-gauge-ficha" style="cursor: help;"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="top"
                                title="Estado: {{ $semText }}"
                                data-bs-content="{{ $explicacionDetallada }}"></div>
                            <div class="ficha-performance-board__percent">{{ number_format($avanceVal, 2) }}%</div>
                            <div class="ficha-performance-board__caption">Avance Meta</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card card-ficha-moderna ficha-panel ficha-panel--quality p-4 mt-2">
                    <h3 class="ficha-section-title" style="color: {{ $indicador->color ?? '#484747' }};"><span class="ficha-section-icon"><i class="fas fa-database"></i></span>Calidad de la Información</h3>
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

                <div class="card card-ficha-moderna ficha-panel ficha-panel--history p-4 mt-2 mb-5 pdf-page-break">
                    <h3 class="ficha-section-title" style="color: {{ $indicador->color ?? '#484747' }};"><span class="ficha-section-icon"><i class="fas fa-chart-area"></i></span>Evolución Histórica</h3>
                    <div class="row align-items-center">

                        <div class="col-md-4 mb-4 mb-md-0 pe-md-4 border-end-md">
                            <div class="table-responsive rounded shadow-sm border table-datos">
                                <table class="table table-hover table-historicos text-center mb-0">
                                    <thead class="sticky-top">
                                        <tr>
                                            <th class="text-start text-white" style="background-color: {{ $indicador->color ?? '#6c757d' }};">Año</th>
                                            <th class="text-white" style="background-color: {{ $indicador->color ?? '#6c757d' }};">Valor Alcanzado</th>
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
            <div class="container ficha-actions pb-5 text-end ocultar_impresion">
                <a href="{{ route('ficha-tecnica.download', $indicador) }}" class="btn ficha-action ficha-action--primary" style="--ficha-accent: {{ $indicador->color ?? '#9d2449' }};">
                    <i class="fas fa-download me-2"></i> Descargar ficha
                </a>
                {{-- <a href="{{ route('ficha-tecnica.preview', $indicador) }}" class="ficha-preview-link ms-3" target="_blank" rel="noopener">
                    Vista previa de impresión
                </a> --}}
            </div>
        </div>

        @section('jss-final')
            @php
                $minHistorico = $indicador->datos_anuales_validados->min('anio');
                $minLB = $indicador->linea_base ? (int)$indicador->linea_base : 2015;
                $anioInicio = min($minLB, $minHistorico ?: 2015);
                $anioFin = 2030;

                $anioLB = $indicador->linea_base ?? null;

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
                    $categoriasEjeX_php[]=(string) $year;

                    $datoAnual=$indicador->datos_anuales_validados->firstWhere('anio', $year);
                    if ($datoAnual && !empty(trim((string)$datoAnual->valor_dato))) {
                    $limpio = preg_replace('/[^0-9.-]/', '', $datoAnual->valor_dato);
                    $datosParaGraficaPrincipal_php[] = is_numeric($limpio) ? (float)$limpio : null;
                    } else {
                    $datosParaGraficaPrincipal_php[] = null;
                    }

                    $datosLineaBasePunto_php[] = ($year == $anioLB) ? $valorLB : null;

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

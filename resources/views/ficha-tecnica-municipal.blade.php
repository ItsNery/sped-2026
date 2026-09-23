@extends('layouts.plantilla')

@section('title', 'Ficha técnica ' . $indicador->indicador)
@section('meta-description', 'Ficha técnica del indicador municipal ' . $indicador->indicador)
@section('canonical-url', url()->current())
@section('og-title', $indicador->indicador . ' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('og-description', $indicador->descripcion)
@section('og:url', url()->current())
@section('twitter-title', $indicador->indicador . ' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('twitter-description', $indicador->descripcion)

@section('css')
    <link href="{{ asset('css/municipales.css') }}?v={{ filemtime(public_path('css/municipales.css')) }}" rel="stylesheet">
@endsection

@section('content')
    <div class="ficha-background municipales-ficha">
        <div class="container mt-4 contenedor__ficha ficha-page ficha-municipal-page" id="imprimir">
            <header class="ficha-hero">
                <div class="ficha-hero__content">
                    <div class="ficha-kicker"><i class="fas fa-chart-line me-2"></i>Ficha técnica municipal</div>
                    <h1 class="fw-bold mb-0">{{ $indicador->indicador }}</h1>
                    <div class="ficha-hero__context">
                        <span><i class="fas fa-location-dot"></i>{{ $nombreMunicipio }}</span>
                        <span><i class="fas fa-landmark"></i>{{ $indicador->instrumento ?: 'Plan Municipal de Desarrollo' }}</span>
                    </div>
                </div>
                <div class="ficha-hero__ods ocultar_impresion">
                    @foreach ($indicador->ods->unique('id') as $ods)
                        <img src="{{ asset('/img/Icons_ODS/' . $ods->id . '.png') }}" alt="ODS {{ $ods->id }}" title="ODS {{ $ods->id }}">
                    @endforeach
                </div>
            </header>

            <div class="row ficha-layout-two-col">
                <div class="col-lg-6">
                    <section class="card card-ficha-moderna ficha-panel ficha-panel--planning h-100 p-4">
                        <h3 class="ficha-section-title"><span class="ficha-section-icon"><i class="fas fa-compass"></i></span>Alineación a la planeación</h3>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="ficha-label">Dependencia responsable</div>
                                <div class="ficha-value">{{ $indicador->dependencia ?: 'Sin dependencia responsable' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="ficha-label">Instrumento de planeación</div>
                                <div class="ficha-value">{{ $indicador->instrumento ?: 'Plan Municipal de Desarrollo' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="ficha-label">Eje</div>
                                <div class="ficha-value">{{ $indicador->eje_indicador ?? 'N/D' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="ficha-label">Temática</div>
                                <div class="ficha-value">{{ $indicador->tematica ?? 'N/D' }}</div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-lg-6 mt-3 mt-lg-0">
                    <section class="card card-ficha-moderna ficha-panel ficha-panel--technical h-100 p-4">
                        <h3 class="ficha-section-title"><span class="ficha-section-icon"><i class="fas fa-sliders-h"></i></span>Detalle técnico del indicador</h3>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="ficha-label">Descripción</div>
                                <div class="ficha-value text-muted fs-95-justify">{{ $indicador->descripcion ?? 'N/D' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="ficha-label">Fórmula</div>
                                <div class="ficha-value text-muted ws-pre fs-95-justify">{{ $indicador->formula ?? 'N/D' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="ficha-label">Unidad de medida</div>
                                <div class="ficha-value">{{ $indicador->unidad_medida ?? 'N/D' }}</div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <section class="card card-ficha-moderna ficha-panel ficha-panel--quality p-4 mt-2">
                <h3 class="ficha-section-title"><span class="ficha-section-icon"><i class="fas fa-database"></i></span>Características del indicador</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="ficha-label">Fuente</div>
                        <div class="ficha-value">
                            {{ $indicador->fuente ?? 'N/D' }}
                            @if ($indicador->liga && $indicador->liga !== '0')
                                <a href="{{ $indicador->liga }}" target="_blank" rel="noopener" class="ms-2 text-primary ocultar_impresion" title="Abrir fuente">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="ficha-label">Cobertura geográfica</div>
                        <div class="ficha-value">{{ $indicador->cobertura ?? 'N/D' }}</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="ficha-label">Periodicidad</div>
                        <div class="ficha-value">{{ optional($indicador->periodicidad)->nombre ?? 'N/D' }}</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="ficha-label">Próxima actualización</div>
                        <div class="ficha-value">{{ $indicador->proxima_actualizacion ?? 'N/D' }}</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="ficha-label">Tipo</div>
                        <div class="ficha-value">{{ optional($indicador->tipo)->nombre ?? 'N/D' }}</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="ficha-label">Nivel</div>
                        <div class="ficha-value">{{ optional($indicador->nivel)->nombre ?? 'N/D' }}</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="ficha-label">Dimensión</div>
                        <div class="ficha-value">{{ optional($indicador->dimension)->nombre ?? 'N/D' }}</div>
                    </div>
                </div>
            </section>

            <section class="card card-ficha-moderna ficha-panel ficha-panel--performance p-4 mt-2">
                <h3 class="ficha-section-title"><span class="ficha-section-icon"><i class="fas fa-bullseye"></i></span>Seguimiento al indicador</h3>
                <div class="ficha-management-metrics">
                    <div class="ficha-metric-card">
                        <div class="ficha-metric-card__icon"><i class="fas fa-flag"></i></div>
                        <div>
                            <div class="ficha-label">Línea base {{ $indicador->linea_base }}</div>
                            <div class="ficha-metric-card__value">{{ $indicador->dato_linea ?? 'N/D' }}</div>
                        </div>
                    </div>
                    <div class="ficha-metric-card">
                        <div class="ficha-metric-card__icon"><i class="fas fa-chart-line"></i></div>
                        <div>
                            <div class="ficha-label">Tendencia</div>
                            <div class="ficha-metric-card__value ficha-metric-card__value--text">{{ $indicador->tendencia ?? 'N/D' }}</div>
                        </div>
                    </div>
                    <div class="ficha-metric-card">
                        <div class="ficha-metric-card__icon"><i class="fas fa-bullseye"></i></div>
                        <div>
                            <div class="ficha-label">Meta {{ $anioMeta }}</div>
                            <div class="ficha-metric-card__value">{{ $indicador->meta_2024 ?? 'N/D' }}</div>
                        </div>
                    </div>
                </div>
                <div class="ficha-performance-board">
                    <div class="ficha-performance-board__latest">
                        <div class="ficha-label">Último dato disponible{{ $ultimoDato ? ' - ' . $ultimoDato->año : '' }}</div>
                        <div class="ficha-performance-board__value">{{ $ultimoDato?->dato ?? 'N/D' }}</div>
                    </div>
                    <div class="ficha-performance-board__status">
                        <div class="ficha-label">Próxima actualización</div>
                        <div class="ficha-performance-board__value">{{ $indicador->proxima_actualizacion ?? 'N/D' }}</div>
                    </div>
                    <div class="ficha-performance-board__gauge">
                        <div class="ficha-label">Periodicidad</div>
                        <div class="ficha-performance-board__value">{{ optional($indicador->periodicidad)->nombre ?? 'N/D' }}</div>
                    </div>
                </div>
            </section>

            <section class="card card-ficha-moderna ficha-panel ficha-panel--quality p-4 mt-2">
                <h3 class="ficha-section-title"><span class="ficha-section-icon"><i class="fas fa-file-lines"></i></span>Principales resultados</h3>
                <p class="municipales-ficha__result">{{ $ultimoResultado?->resultado ?? 'Sin resultados registrados para el último periodo.' }}</p>
            </section>

            <section class="card card-ficha-moderna ficha-panel ficha-panel--history p-4 mt-2 mb-5 pdf-page-break">
                <h3 class="ficha-section-title"><span class="ficha-section-icon"><i class="fas fa-chart-area"></i></span>Comportamiento histórico del indicador</h3>
                <div class="row">
                    <div class="col-12 text-center">
                        <div id="grafica-historica" class="w-100 grafico-historico-ficha"></div>
                    </div>
                </div>
            </section>
        </div>

        <div class="container ficha-actions pb-5 text-end ocultar_impresion">
            <a href="{{ route('mostrarFicha.download', ['indicador' => $indicador]) }}" class="btn ficha-action ficha-action--primary">
                <i class="fas fa-download me-2"></i> Descargar ficha
            </a>
        </div>
    </div>
@endsection

@section('jss-final')
    @php
        $anioLineaBase = $indicador->linea_base ? (int) $indicador->linea_base : null;
        $aniosGrafica = range($anioInicioGrafica, $anioFinGrafica);
        $valorParaGrafica = function ($valor) {
            $valor = preg_replace('/[^0-9.-]/', '', (string) $valor);
            return $valor !== '' && is_numeric($valor) ? (float) $valor : null;
        };
        $datosGrafica = collect($aniosGrafica)
            ->map(fn ($year) => $valorParaGrafica(data_get($indicador, 'dato_' . $year)))
            ->all();
        $datosLineaBase = collect($aniosGrafica)
            ->map(fn ($year) => $year === $anioLineaBase ? $valorParaGrafica($indicador->dato_linea) : null)
            ->all();
        $datosMeta = collect($aniosGrafica)
            ->map(fn ($year) => $year === $anioMeta ? $valorParaGrafica($indicador->meta_2024) : null)
            ->all();
        $unidadMedida = $indicador->unidad_medida ?? 'Valor';
    @endphp
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var categorias = @json($aniosGrafica);
            var datosPrincipales = @json($datosGrafica);
            var datosLineaBase = @json($datosLineaBase);
            var datosMeta = @json($datosMeta);
            var unidadMedida = @json($unidadMedida);
            var fuenteGrafica = 'Corra Montserra';

            function formatNumber(value) {
                if (value === null || value === undefined || value === '' || !Number.isFinite(Number(value))) {
                    return '';
                }

                return Number(value).toLocaleString('en-US', {
                    maximumFractionDigits: 2
                });
            }

            function valueLabel(color, position) {
                return {
                    show: true,
                    position: position,
                    distance: 8,
                    formatter: function(params) {
                        return formatNumber(params.value);
                    },
                    color: color,
                    fontSize: 10,
                    fontWeight: 'bold',
                    backgroundColor: 'rgba(255, 255, 255, 0.94)',
                    borderColor: color,
                    borderWidth: 1,
                    borderRadius: 4,
                    padding: [2, 3]
                };
            }

            var chart = echarts.init(document.getElementById('grafica-historica'));

            chart.setOption({
                textStyle: { fontFamily: fuenteGrafica },
                tooltip: {
                    trigger: 'axis',
                    formatter: function(params) {
                        var res = params[0].axisValue;
                        params.forEach(function(p) {
                            if (p.value !== null && p.value !== undefined) res += '<br/>' + p.marker + ' ' + p.seriesName + ': ' + formatNumber(p.value);
                        });
                        return res;
                    }
                },
                legend: { data: [unidadMedida, @json('Línea Base ' . ($anioLineaBase ?? '')), @json('Meta ' . $anioMeta)], top: 'top' },
                grid: { left: 54, right: 72, top: 54, bottom: 58, containLabel: true },
                xAxis: { type: 'category', data: categorias, name: 'Año' },
                yAxis: { type: 'value', name: 'Rango de valores de medición' },
                series: [
                    {
                        name: unidadMedida,
                        type: 'line',
                        data: datosPrincipales,
                        smooth: true,
                        lineStyle: { width: 3, color: '#246257' },
                        itemStyle: { color: '#246257' },
                        areaStyle: { color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            { offset: 0, color: 'rgba(36,98,87,0.25)' },
                            { offset: 1, color: 'rgba(36,98,87,0.04)' }
                        ]) },
                        showSymbol: true,
                        symbolSize: 7,
                        connectNulls: true,
                        label: valueLabel('#246257', 'top')
                    },
                    {
                        name: @json('Línea Base ' . ($anioLineaBase ?? '')),
                        type: 'scatter',
                        data: datosLineaBase,
                        symbolSize: 14,
                        symbol: 'diamond',
                        itemStyle: { color: '#198754' },
                        label: valueLabel('#198754', 'top')
                    },
                    {
                        name: @json('Meta ' . $anioMeta),
                        type: 'scatter',
                        data: datosMeta,
                        symbolSize: 18,
                        symbol: 'diamond',
                        itemStyle: { color: '#b94149' },
                        label: valueLabel('#b94149', 'top')
                    }
                ],
                labelLayout: {
                    hideOverlap: false,
                    moveOverlap: 'shiftY'
                }
            });

            chart.resize();
            window.addEventListener('resize', function() { chart.resize(); });
        });

    </script>
@endsection

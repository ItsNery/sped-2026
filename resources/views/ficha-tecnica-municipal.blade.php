@extends('layouts.plantilla')

@section('title', 'Ficha técnica ' . $indicador->nombre)
@section('meta-description', 'Ficha técnica del indicador municipal ' . $indicador->nombre)
@section('canonical-url', url()->current())
@section('og-title', $indicador->nombre . ' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('og-description', $indicador->descripcion)
@section('og:url', url()->current())
@section('twitter-title', $indicador->nombre . ' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('twitter-description', $indicador->descripcion)

@section('css')
    <link href="{{ asset('css/municipales.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="ficha-background municipales-ficha">
        <div class="container ficha-page ficha-municipal-page" id="imprimir">
            <header class="ficha-hero">
                <div class="ficha-hero__content">
                    <div class="ficha-kicker"><i class="fas fa-chart-line me-2"></i>Ficha técnica municipal</div>
                    <h1>{{ $indicador->indicador }}</h1>
                    <div class="ficha-hero__context">
                        <span><i class="fas fa-location-dot"></i>{{ $municipio->municipio->nombre }}</span>
                        <span><i class="fas fa-landmark"></i>Plan Municipal de Desarrollo</span>
                    </div>
                </div>
                <div class="ficha-hero__ods ocultar_impresion">
                    @foreach ($indicador->ods->unique('id') as $ods)
                        <img src="{{ asset('/img/Icons_ODS/' . $ods->id . '.png') }}" alt="ODS {{ $ods->id }}" title="ODS {{ $ods->id }}">
                    @endforeach
                </div>
            </header>

            <section class="card card-ficha-moderna ficha-panel ficha-panel--planning p-4">
                <h2 class="ficha-section-title"><span class="ficha-section-icon"><i class="fas fa-compass"></i></span>Identificación del indicador</h2>
                <div class="municipales-ficha__fields">
                    <div class="municipales-ficha__field">
                        <span class="ficha-label">Eje</span>
                        <strong class="ficha-value">{{ $indicador->eje_indicador ?? 'N/D' }}</strong>
                    </div>
                    <div class="municipales-ficha__field">
                        <span class="ficha-label">Temática</span>
                        <strong class="ficha-value">{{ $indicador->tematica ?? 'N/D' }}</strong>
                    </div>
                    <div class="municipales-ficha__field municipales-ficha__field--wide">
                        <span class="ficha-label">Descripción</span>
                        <strong class="ficha-value">{{ $indicador->descripcion ?? 'N/D' }}</strong>
                    </div>
                </div>
            </section>

            <section class="card card-ficha-moderna ficha-panel ficha-panel--technical p-4 mt-2">
                <h2 class="ficha-section-title"><span class="ficha-section-icon"><i class="fas fa-sliders-h"></i></span>Detalles técnicos</h2>
                <div class="municipales-ficha__fields">
                    <div class="municipales-ficha__field municipales-ficha__field--wide">
                        <span class="ficha-label">Fuente</span>
                        <strong class="ficha-value">
                            {{ $indicador->fuente ?? 'N/D' }}
                            @if ($indicador->liga && $indicador->liga !== '0')
                                <a href="{{ $indicador->liga }}" target="_blank" class="ms-2 ocultar_impresion" title="Abrir fuente">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            @endif
                        </strong>
                    </div>
                    <div class="municipales-ficha__field">
                        <span class="ficha-label">Periodicidad</span>
                        <strong class="ficha-value">{{ optional($indicador->periodicidad)->nombre ?? 'N/D' }}</strong>
                    </div>
                    <div class="municipales-ficha__field">
                        <span class="ficha-label">Próxima actualización</span>
                        <strong class="ficha-value">{{ $indicador->proxima_actualizacion ?? 'N/D' }}</strong>
                    </div>
                    <div class="municipales-ficha__field">
                        <span class="ficha-label">Tendencia</span>
                        <strong class="ficha-value">{{ $indicador->tendencia ?? 'N/D' }}</strong>
                    </div>
                    <div class="municipales-ficha__field">
                        <span class="ficha-label">Cobertura geográfica</span>
                        <strong class="ficha-value">{{ $indicador->cobertura ?? 'N/D' }}</strong>
                    </div>
                    <div class="municipales-ficha__field">
                        <span class="ficha-label">Unidad de medida</span>
                        <strong class="ficha-value">{{ $indicador->unidad_medida ?? 'N/D' }}</strong>
                    </div>
                    <div class="municipales-ficha__field">
                        <span class="ficha-label">Tipo</span>
                        <strong class="ficha-value">{{ optional($indicador->tipo)->nombre ?? 'N/D' }}</strong>
                    </div>
                    <div class="municipales-ficha__field">
                        <span class="ficha-label">Nivel</span>
                        <strong class="ficha-value">{{ optional($indicador->nivel)->nombre ?? 'N/D' }}</strong>
                    </div>
                    <div class="municipales-ficha__field">
                        <span class="ficha-label">Dimensión</span>
                        <strong class="ficha-value">{{ optional($indicador->dimension)->nombre ?? 'N/D' }}</strong>
                    </div>
                </div>
            </section>

            <section class="card card-ficha-moderna ficha-panel ficha-panel--performance p-4 mt-2">
                <h2 class="ficha-section-title"><span class="ficha-section-icon"><i class="fas fa-file-lines"></i></span>Principales resultados</h2>
                <p class="municipales-ficha__result">{{ $indicador->resultado_mas_reciente ?? 'Sin resultados registrados.' }}</p>
            </section>

            <section class="card card-ficha-moderna ficha-panel ficha-panel--history p-4 mt-2 mb-5">
                <h2 class="ficha-section-title"><span class="ficha-section-icon"><i class="fas fa-chart-area"></i></span>Resultados históricos</h2>
                <div class="municipales-ficha__history">
                    <div class="row align-items-center">
                        <div class="col-md-4 mb-4 mb-md-0 pe-md-4 border-end-md">
                            <div class="table-responsive rounded shadow-sm border table-datos">
                                <table class="table table-hover table-historicos text-center mb-0">
                                    <thead class="sticky-top">
                                        <tr>
                                            <th class="text-start">Año</th>
                                            <th>Valor Alcanzado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $anioActual = now()->year;
                                            $lineaBase = $indicador->linea_base ? (int) $indicador->linea_base : null;
                                        @endphp
                                        @for ($year = 2015; $year <= $anioActual; $year++)
                                            @php
                                                $valorDato = data_get($indicador, 'dato_' . $year);
                                                if ($valorDato === null && $year === $lineaBase) {
                                                    $valorDato = $indicador->dato_linea;
                                                }
                                            @endphp
                                            @if ($valorDato !== null || $year === $lineaBase)
                                                <tr @if ($year === $lineaBase) class="table-info-linea-base" @endif>
                                                    <td class="text-muted d-flex justify-content-between align-items-center">
                                                        <span class="fw-bold">{{ $year }}</span>
                                                        @if ($year === $lineaBase)
                                                            <span class="badge rounded-pill bg-success badge-linea-base">L. BASE</span>
                                                        @endif
                                                    </td>
                                                    <td class="fw-bold @if ($year === $lineaBase) text-success @else text-dark @endif">
                                                        {{ $valorDato ?? 'N/D' }}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endfor
                                        @if ($indicador->meta_2024 !== null)
                                            <tr class="table-info-meta">
                                                <td class="fw-bold d-flex justify-content-between align-items-center">
                                                    <span>2030</span>
                                                    <span class="badge rounded-pill bg-danger badge-meta-2030">META</span>
                                                </td>
                                                <td class="fw-bold text-danger">{{ $indicador->meta_2024 }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-8 ps-md-4 text-center">
                            <div id="grafica-historica" class="municipales-ficha__chart"></div>
                        </div>
                    </div>
                </div>
                <p class="municipales-ficha__source">Fuente: {{ $indicador->fuente ?? 'Sin fuente disponible' }}</p>
            </section>

            <div class="ficha-actions pb-5 text-end ocultar_impresion">
                <a href="{{ route('pm.show', ['municipioConvenio' => $municipio]) }}" class="btn ficha-action ficha-action--secondary">
                    <i class="fas fa-arrow-left me-2"></i> Volver a indicadores
                </a>
                <a href="{{ route('mostrarFicha.download', ['indicador' => $indicador]) }}" class="btn ficha-action ficha-action--primary">
                    <i class="fas fa-download me-2"></i> Descargar ficha
                </a>
                <button type="button" class="btn ficha-action ficha-action--primary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i> Imprimir ficha
                </button>
            </div>
        </div>
    </div>
@endsection

@section('jss-final')
    @php
        $anioLineaBase = $indicador->linea_base ? (int) $indicador->linea_base : null;
        $aniosIniciales = array_filter([$anioLineaBase, $indicador->resultados->min('año')]);
        $anioInicioGrafica = min($aniosIniciales ?: [2015]);
        $aniosGrafica = range($anioInicioGrafica, 2030);
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
            ->map(fn ($year) => $year === 2030 ? $valorParaGrafica($indicador->meta_2024) : null)
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
            var chart = echarts.init(document.getElementById('grafica-historica'));

            chart.setOption({
                textStyle: { fontFamily: fuenteGrafica },
                tooltip: {
                    trigger: 'axis',
                    formatter: function(params) {
                        var res = params[0].axisValue;
                        params.forEach(function(p) {
                            if (p.value !== null && p.value !== undefined) res += '<br/>' + p.marker + ' ' + p.seriesName + ': ' + p.value;
                        });
                        return res;
                    }
                },
                legend: { data: [unidadMedida, @json('Línea Base ' . ($anioLineaBase ?? '')), 'Meta 2030'], top: 'top' },
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
                        symbolSize: 6,
                        connectNulls: true
                    },
                    {
                        name: @json('Línea Base ' . ($anioLineaBase ?? '')),
                        type: 'scatter',
                        data: datosLineaBase,
                        symbolSize: 14,
                        symbol: 'diamond',
                        itemStyle: { color: '#198754' }
                    },
                    {
                        name: 'Meta 2030',
                        type: 'scatter',
                        data: datosMeta,
                        symbolSize: 18,
                        symbol: 'diamond',
                        itemStyle: { color: '#b94149' }
                    }
                ]
            });

            chart.resize();
            window.addEventListener('resize', function() { chart.resize(); });
        });

    </script>
@endsection

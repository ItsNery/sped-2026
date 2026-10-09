<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Ficha técnica municipal - {{ $indicador->indicador }}</title>
    <style>
        @font-face {
            font-family: 'Corra Montserra';
            src: url('{{ $pdfAsset('css/fuentes/Corra-Montserra/TTF/Corra_Montserra_Regular.ttf') }}') format('truetype');
            font-weight: 400;
        }

        @font-face {
            font-family: 'Corra Montserra';
            src: url('{{ $pdfAsset('css/fuentes/Corra-Montserra/TTF/Corra_Montserra_Bold.ttf') }}') format('truetype');
            font-weight: 700;
        }

        @page {
            size: 210mm 297mm;
            margin: 5mm 5mm 16mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #253936;
            font-family: 'Corra Montserra', Arial, sans-serif;
            font-size: 10pt;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .sheet {
            width: 100%;
            max-width: 190mm;
            margin: 0 auto;
        }

        .logos {
            display: block;
            width: 100%;
            height: 18mm;
            margin-bottom: 3mm;
            object-fit: contain;
        }

        .hero {
            padding: 0 0 3mm;
            border-radius: 0;
            background: transparent;
            color: #253936;
        }

        .kicker {
            margin-bottom: 2mm;
            color: #484747;
            font-size: 12pt;
            font-weight: 700;
        }

        h1 {
            margin: 0;
            color: #0c312d;
            font-size: 18pt;
            line-height: 1.2;
        }

        .context {
            display: flex;
            gap: 18px;
            margin-top: 2mm;
            color: #688078;
            font-size: 9.5pt;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 8px;
        }

        .panel {
            padding: 13px 15px;
            border: 1px solid #dce8e5;
            border-radius: 8px;
            break-inside: avoid;
        }

        .panel--full {
            grid-column: 1 / -1;
        }

        h2 {
            margin: 0 0 9px;
            color: #246257;
            font-size: 12pt;
        }

        .fields {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px 12px;
        }

        .field--wide {
            grid-column: span 2;
        }

        .metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .metric {
            padding: 9px 11px;
            background: #f3f8f6;
            border-radius: 6px;
        }

        .label {
            color: #688078;
            font-size: 8pt;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .value {
            margin-top: 2px;
            color: #253936;
            font-size: 10pt;
            line-height: 1.35;
        }

        .result {
            margin: 0;
            padding: 9px 11px;
            background: #f3f8f6;
            font-size: 10.5pt;
            line-height: 1.45;
        }

        .history {
            min-width: 0;
        }

        .chart {
            width: 100%;
            height: 62mm;
            min-width: 0;
        }

        .footer-pleca {
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            height: 6mm;
        }

        .footer-pleca img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    </style>
</head>

<body>
    @php
        $anioLineaBase = $indicador->linea_base ? (int) $indicador->linea_base : null;
        $aniosGrafica = range($anioInicioGrafica, $anioFinGrafica);
        $valorParaGrafica = function ($valor) {
            $valor = preg_replace('/[^0-9.-]/', '', (string) $valor);
            return $valor !== '' && is_numeric($valor) ? (float) $valor : null;
        };
        $datosGrafica = collect($aniosGrafica)
            ->map(fn($year) => $valorParaGrafica(data_get($indicador, 'dato_' . $year)))
            ->all();
        $datosLineaBase = collect($aniosGrafica)
            ->map(fn($year) => $year === $anioLineaBase ? $valorParaGrafica($indicador->dato_linea) : null)
            ->all();
        $datosMeta = collect($aniosGrafica)
            ->map(fn($year) => $year === $anioMeta ? $valorParaGrafica($indicador->meta_2024) : null)
            ->all();
        $unidadMedida = $indicador->unidad_medida ?? 'Valor';
    @endphp

    <main class="sheet">
        <img class="logos" src="{{ $pdfAsset('img/Cintillos-SPED-35.png') }}" alt="Gobierno de Puebla">

        <header class="hero">
            <div class="kicker">Ficha técnica municipal</div>
            <h1>{{ $indicador->indicador }}</h1>
            <div class="context">
                <span>{{ $nombreMunicipio }}</span>
                <span>{{ $indicador->instrumento ?: 'Plan Municipal de Desarrollo' }}</span>
            </div>
        </header>

        <section class="grid">
            <article class="panel">
                <h2>Alineación a la planeación</h2>
                <div class="fields">
                    <div class="field field--wide">
                        <div class="label">Dependencia responsable</div>
                        <div class="value">{{ $indicador->dependencia ?: 'Sin dependencia responsable' }}</div>
                    </div>
                    <div class="field field--wide">
                        <div class="label">Instrumento de planeación</div>
                        <div class="value">{{ $indicador->instrumento ?: 'Plan Municipal de Desarrollo' }}</div>
                    </div>
                    <div class="field field--wide">
                        <div class="label">Eje</div>
                        <div class="value">{{ $indicador->eje_indicador ?? 'N/D' }}</div>
                    </div>
                    <div class="field field--wide">
                        <div class="label">Temática</div>
                        <div class="value">{{ $indicador->tematica ?? 'N/D' }}</div>
                    </div>
                </div>
            </article>

            <article class="panel">
                <h2>Detalle técnico del indicador</h2>
                <div class="fields">
                    <div class="field field--wide">
                        <div class="label">Descripción</div>
                        <div class="value">{{ $indicador->descripcion ?? 'N/D' }}</div>
                    </div>
                    <div class="field field--wide">
                        <div class="label">Fórmula</div>
                        <div class="value">{{ $indicador->formula ?? 'N/D' }}</div>
                    </div>
                    <div class="field field--wide">
                        <div class="label">Unidad de medida</div>
                        <div class="value">{{ $indicador->unidad_medida ?? 'N/D' }}</div>
                    </div>
                </div>
            </article>

            <article class="panel panel--full">
                <h2>Características del indicador</h2>
                <div class="fields">
                    <div class="field field--wide">
                        <div class="label">Fuente</div>
                        <div class="value">{{ $indicador->fuente ?? 'N/D' }}</div>
                    </div>
                    <div class="field">
                        <div class="label">Cobertura geográfica</div>
                        <div class="value">{{ $indicador->cobertura ?? 'N/D' }}</div>
                    </div>
                    <div class="field">
                        <div class="label">Periodicidad</div>
                        <div class="value">{{ optional($indicador->periodicidad)->nombre ?? 'N/D' }}</div>
                    </div>
                    <div class="field">
                        <div class="label">Próxima actualización</div>
                        <div class="value">{{ $indicador->proxima_actualizacion ?? 'N/D' }}</div>
                    </div>
                    <div class="field">
                        <div class="label">Tipo</div>
                        <div class="value">{{ optional($indicador->tipo)->nombre ?? 'N/D' }}</div>
                    </div>
                    <div class="field">
                        <div class="label">Nivel</div>
                        <div class="value">{{ optional($indicador->nivel)->nombre ?? 'N/D' }}</div>
                    </div>
                    <div class="field">
                        <div class="label">Dimensión</div>
                        <div class="value">{{ optional($indicador->dimension)->nombre ?? 'N/D' }}</div>
                    </div>
                </div>
            </article>

            <article class="panel panel--full">
                <h2>Seguimiento al indicador</h2>
                <div class="metrics">
                    <div class="metric">
                        <div class="label">Línea base {{ $indicador->linea_base }}</div>
                        <div class="value">{{ $indicador->dato_linea ?? 'N/D' }}</div>
                    </div>
                    <div class="metric">
                        <div class="label">Tendencia</div>
                        <div class="value">{{ $indicador->tendencia ?? 'N/D' }}</div>
                    </div>
                    <div class="metric">
                        <div class="label">Meta {{ $anioMeta }}</div>
                        <div class="value">{{ $indicador->meta_2024 ?? 'N/D' }}</div>
                    </div>
                    <div class="metric">
                        <div class="label">Último dato{{ $ultimoDato ? ' - ' . $ultimoDato->año : '' }}</div>
                        <div class="value">{{ $ultimoDato?->dato ?? 'N/D' }}</div>
                    </div>
                    <div class="metric">
                        <div class="label">Próxima actualización</div>
                        <div class="value">{{ $indicador->proxima_actualizacion ?? 'N/D' }}</div>
                    </div>
                </div>
            </article>

            <article class="panel panel--full">
                <h2>Principales resultados</h2>
                <p class="result">
                    {{ $ultimoResultado?->resultado ?? 'Sin resultados registrados para el último periodo.' }}</p>
            </article>

            <article class="panel panel--full">
                <h2>Comportamiento histórico del indicador</h2>
                <div class="history">
                    <div id="grafica-historica" class="chart"></div>
                </div>
            </article>
        </section>
    </main>

    <div class="footer-pleca" aria-hidden="true">
        <img src="{{ $pdfAsset('img/pleca-nueva.png') }}" alt="">
    </div>

    <script>
        {!! $pdfEcharts !!}
    </script>
    <script>
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

        function renderChart() {
            var chart = echarts.init(document.getElementById('grafica-historica'));
            chart.setOption({
                animation: false,
                textStyle: {
                    fontFamily: fuenteGrafica
                },
                legend: {
                    data: [unidadMedida, @json('Línea Base ' . ($anioLineaBase ?? '')), @json('Meta ' . $anioMeta)]
                },
                tooltip: {
                    trigger: 'axis'
                },
                grid: {
                    left: 54,
                    right: 72,
                    top: 54,
                    bottom: 58,
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: categorias,
                    name: 'Año',
                    boundaryGap: true,
                    axisLabel: {
                        interval: 0,
                        margin: 12,
                        rotate: 25,
                        fontSize: 11
                    }
                },
                yAxis: {
                    type: 'value',
                    name: 'Valor'
                },
                series: [{
                        name: unidadMedida,
                        type: 'line',
                        data: datosPrincipales,
                        smooth: true,
                        connectNulls: true,
                        lineStyle: {
                            width: 3,
                            color: '#246257'
                        },
                        itemStyle: {
                            color: '#246257'
                        },
                        showSymbol: true,
                        symbolSize: 7,
                        label: valueLabel('#246257', 'top')
                    },
                    {
                        name: @json('Línea Base ' . ($anioLineaBase ?? '')),
                        type: 'scatter',
                        data: datosLineaBase,
                        symbol: 'diamond',
                        symbolSize: 12,
                        itemStyle: {
                            color: '#198754'
                        },
                        label: valueLabel('#198754', 'top')
                    },
                    {
                        name: @json('Meta ' . $anioMeta),
                        type: 'scatter',
                        data: datosMeta,
                        symbol: 'diamond',
                        symbolSize: 14,
                        itemStyle: {
                            color: '#b94149'
                        },
                        label: valueLabel('#b94149', 'top')
                    }
                ],
                labelLayout: {
                    hideOverlap: false,
                    moveOverlap: 'shiftY'
                }
            });
            chart.resize();
            requestAnimationFrame(function() {
                window.pdfReady = true;
            });
        }

        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(renderChart);
        } else {
            renderChart();
        }
    </script>
</body>

</html>

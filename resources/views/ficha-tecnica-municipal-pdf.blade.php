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

        @page { size: 210mm 297mm; margin: 5mm 5mm 16mm; }

        * { box-sizing: border-box; }

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

        .panel--full { grid-column: 1 / -1; }

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

        .field--wide { grid-column: span 2; }

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
            display: grid;
            grid-template-columns: minmax(0, 34%) minmax(0, 1fr);
            gap: 12px;
            align-items: center;
        }

        .history > * {
            min-width: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        th {
            padding: 6px;
            background: #246257;
            color: #fff;
            text-align: left;
        }

        td {
            padding: 5px 6px;
            border-bottom: 1px solid #e7efed;
        }

        .base { background: #eef8f2; color: #198754; font-weight: 700; }
        .meta { background: #fff1f2; color: #b94149; font-weight: 700; }
        .chart {
            width: 100%;
            height: 50mm;
            min-width: 0;
        }

        .source {
            margin: 8px 0 0;
            color: #688078;
            font-size: 7.5pt;
            text-align: right;
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

    <main class="sheet">
        <img class="logos" src="{{ $pdfAsset('img/Cadena_SPED.png') }}" alt="Gobierno de Puebla">

        <header class="hero">
            <div class="kicker">Ficha técnica municipal</div>
            <h1>{{ $indicador->indicador }}</h1>
            <div class="context">
                <span>{{ $municipio->municipio->nombre }}</span>
                <span>Plan Municipal de Desarrollo</span>
            </div>
        </header>

        <section class="grid">
            <article class="panel panel--full">
                <h2>Identificación del indicador</h2>
                <div class="fields">
                    <div class="field"><div class="label">Eje</div><div class="value">{{ $indicador->eje_indicador ?? 'N/D' }}</div></div>
                    <div class="field"><div class="label">Temática</div><div class="value">{{ $indicador->tematica ?? 'N/D' }}</div></div>
                    <div class="field field--wide"><div class="label">Descripción</div><div class="value">{{ $indicador->descripcion ?? 'N/D' }}</div></div>
                </div>
            </article>

            <article class="panel panel--full">
                <h2>Detalles técnicos</h2>
                <div class="fields">
                    <div class="field field--wide"><div class="label">Fuente</div><div class="value">{{ $indicador->fuente ?? 'N/D' }}</div></div>
                    <div class="field"><div class="label">Periodicidad</div><div class="value">{{ optional($indicador->periodicidad)->nombre ?? 'N/D' }}</div></div>
                    <div class="field"><div class="label">Próxima actualización</div><div class="value">{{ $indicador->proxima_actualizacion ?? 'N/D' }}</div></div>
                    <div class="field"><div class="label">Unidad de medida</div><div class="value">{{ $indicador->unidad_medida ?? 'N/D' }}</div></div>
                    <div class="field"><div class="label">Tendencia</div><div class="value">{{ $indicador->tendencia ?? 'N/D' }}</div></div>
                    <div class="field"><div class="label">Cobertura</div><div class="value">{{ $indicador->cobertura ?? 'N/D' }}</div></div>
                    <div class="field"><div class="label">Tipo</div><div class="value">{{ optional($indicador->tipo)->nombre ?? 'N/D' }}</div></div>
                    <div class="field"><div class="label">Nivel</div><div class="value">{{ optional($indicador->nivel)->nombre ?? 'N/D' }}</div></div>
                    <div class="field"><div class="label">Dimensión</div><div class="value">{{ optional($indicador->dimension)->nombre ?? 'N/D' }}</div></div>
                </div>
            </article>

            <article class="panel panel--full">
                <h2>Principales resultados</h2>
                <p class="result">{{ $indicador->resultado_mas_reciente ?? 'Sin resultados registrados.' }}</p>
            </article>

            <article class="panel panel--full">
                <h2>Resultados históricos</h2>
                <div class="history">
                    <table>
                        <thead><tr><th>Año</th><th>Valor alcanzado</th></tr></thead>
                        <tbody>
                            @for ($year = $anioInicioGrafica; $year <= now()->year; $year++)
                                @php
                                    $valorDato = data_get($indicador, 'dato_' . $year);
                                    if ($valorDato === null && $year === $anioLineaBase) {
                                        $valorDato = $indicador->dato_linea;
                                    }
                                @endphp
                                @if ($valorDato !== null || $year === $anioLineaBase)
                                    <tr class="{{ $year === $anioLineaBase ? 'base' : '' }}">
                                        <td>{{ $year }}{{ $year === $anioLineaBase ? ' (L. base)' : '' }}</td>
                                        <td>{{ $valorDato ?? 'N/D' }}</td>
                                    </tr>
                                @endif
                            @endfor
                            @if ($indicador->meta_2024 !== null)
                                <tr class="meta"><td>2030 (Meta)</td><td>{{ $indicador->meta_2024 }}</td></tr>
                            @endif
                        </tbody>
                    </table>
                    <div id="grafica-historica" class="chart"></div>
                </div>
                <p class="source">Fuente: {{ $indicador->fuente ?? 'Sin fuente disponible' }}</p>
            </article>
        </section>
    </main>

    <div class="footer-pleca" aria-hidden="true">
        <img src="{{ $pdfAsset('img/pleca-nueva.png') }}" alt="">
    </div>

    <script>{!! $pdfEcharts !!}</script>
    <script>
        var categorias = @json($aniosGrafica);
        var datosPrincipales = @json($datosGrafica);
        var datosLineaBase = @json($datosLineaBase);
        var datosMeta = @json($datosMeta);
        var unidadMedida = @json($unidadMedida);
        var fuenteGrafica = 'Corra Montserra';

        function renderChart() {
            var chart = echarts.init(document.getElementById('grafica-historica'));
            chart.setOption({
                animation: false,
                textStyle: { fontFamily: fuenteGrafica },
                legend: { data: [unidadMedida, @json('Línea Base ' . ($anioLineaBase ?? '')), 'Meta 2030'] },
                tooltip: { trigger: 'axis' },
                grid: { left: 54, right: 72, top: 38, bottom: 58, containLabel: true },
                xAxis: {
                    type: 'category',
                    data: categorias,
                    name: 'Año',
                    boundaryGap: true,
                    axisLabel: { interval: 0, margin: 12, rotate: 25, fontSize: 11 }
                },
                yAxis: { type: 'value', name: 'Valor' },
                series: [
                    { name: unidadMedida, type: 'line', data: datosPrincipales, smooth: true, connectNulls: true, lineStyle: { width: 3, color: '#246257' }, itemStyle: { color: '#246257' } },
                    { name: @json('Línea Base ' . ($anioLineaBase ?? '')), type: 'scatter', data: datosLineaBase, symbol: 'diamond', symbolSize: 12, itemStyle: { color: '#198754' } },
                    { name: 'Meta 2030', type: 'scatter', data: datosMeta, symbol: 'diamond', symbolSize: 14, itemStyle: { color: '#b94149' } }
                ]
            });
            chart.resize();
            requestAnimationFrame(function () { window.pdfReady = true; });
        }

        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(renderChart);
        } else {
            renderChart();
        }
    </script>
</body>

</html>

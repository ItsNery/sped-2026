<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ficha técnica - {{ $indicador->nombre }}</title>
    <style>
        {!! $pdfCss !!}
    </style>
</head>

<body class="ficha-pdf__document">
    <main class="ficha-pdf__sheet">
        <img class="ficha-pdf__logos" src="{{ $pdfAsset('img/Cadena_SPED_.png') }}" alt="Gobierno de Puebla">
        <h2 class="ficha-pdf__subtitle">Ficha técnica del indicador</h2>
        <h1 class="ficha-pdf__title">{{ $indicador->nombre }}</h1>

        <section class="ficha-pdf__grid">
            <article class="ficha-pdf__panel">
                <h2 class="ficha-pdf__heading">Planeación</h2>
                <div class="ficha-pdf__item">
                    <div class="ficha-pdf__label">Institución responsable</div>
                    <div class="ficha-pdf__value">{{ $indicador->institucion->nombre ?? 'Sin institución responsable' }}
                    </div>
                </div>
                <div class="ficha-pdf__item">
                    <div class="ficha-pdf__label">Instrumento de planeación</div>
                    <div class="ficha-pdf__value">{{ $indicador->programa_derivado }}</div>
                </div>
                <div class="ficha-pdf__item">
                    <div class="ficha-pdf__label">Eje o programa</div>
                    <div class="ficha-pdf__value">{{ $indicador->programa }}</div>
                </div>
                <div class="ficha-pdf__item">
                    <div class="ficha-pdf__label">Temática</div>
                    <div class="ficha-pdf__value">{{ $indicador->tematica }}</div>
                </div>
            </article>
            <article class="ficha-pdf__panel">
                <h2 class="ficha-pdf__heading">Detalles técnicos</h2>
                <div class="ficha-pdf__item">
                    <div class="ficha-pdf__label">Descripción</div>
                    <div class="ficha-pdf__value">{{ $indicador->descripcion }}</div>
                </div>
                <div class="ficha-pdf__item">
                    <div class="ficha-pdf__label">Fórmula</div>
                    <div class="ficha-pdf__value">{{ $indicador->formula }}</div>
                </div>
                <div class="ficha-pdf__item">
                    <div class="ficha-pdf__label">Unidad de medida</div>
                    <div class="ficha-pdf__value">{{ $indicador->unidad_medida }}</div>
                </div>
            </article>

            <article class="ficha-pdf__panel ficha-pdf__panel--full">
                <h2 class="ficha-pdf__heading">Gestión de Gobierno</h2>
                <div class="ficha-pdf__metrics">
                    <div class="ficha-pdf__metric">
                        <div class="ficha-pdf__label">Línea base {{ $indicador->linea_base }}</div>
                        <div class="ficha-pdf__value">{{ $indicador->dato_linea_base ?? 'N/D' }}</div>
                    </div>
                    <div class="ficha-pdf__metric">
                        <div class="ficha-pdf__label">Tendencia</div>
                        <div class="ficha-pdf__value">{{ $indicador->tendencia }}</div>
                    </div>
                    <div class="ficha-pdf__metric">
                        <div class="ficha-pdf__label">Meta 2030</div>
                        <div class="ficha-pdf__value">{{ $indicador->meta_2024 ?? 'N/D' }}</div>
                    </div>
                </div>
                <div class="ficha-pdf__status">
                    <div class="ficha-pdf__status-item">
                        <div class="ficha-pdf__label">Último dato {{ $chartConfig['anioUltimoDato'] ?? '' }}</div>
                        <div class="ficha-pdf__status-value">
                            {{ $esDatoLineaBase ? 'N/D' : ($chartConfig['ultimoDato'] ?? 'N/D') }}</div>
                    </div>
                    <div class="ficha-pdf__status-item">
                        <div class="ficha-pdf__label">Semaforización</div><span class="ficha-pdf__badge"
                            style="background: {{ $colorSemaforo }}">{{ $esDatoLineaBase ? 'N/D' : $semaforizacion }}</span>
                    </div>
                    <div class="ficha-pdf__status-item">
                        <div class="ficha-pdf__label">Avance meta</div>
                        <div class="ficha-pdf__status-value">
                            {{ $esDatoLineaBase ? 'N/D' : number_format($chartConfig['chartVal'], 2) . '%' }}</div>
                    </div>
                </div>
            </article>

            <article class="ficha-pdf__panel ficha-pdf__panel--full">
                <h2 class="ficha-pdf__heading">Calidad de la información</h2>
                <div class="ficha-pdf__grid">
                    <div class="ficha-pdf__item">
                        <div class="ficha-pdf__label">Fuente</div>
                        <div class="ficha-pdf__value">{{ $indicador->fuente }}</div>
                    </div>
                    <div class="ficha-pdf__item">
                        <div class="ficha-pdf__label">Cobertura geográfica</div>
                        <div class="ficha-pdf__value">{{ $indicador->cobertura }}</div>
                    </div>
                </div>
            </article>

            <article class="ficha-pdf__panel ficha-pdf__panel--full">
                <h2 class="ficha-pdf__heading">Evolución histórica</h2>
                <div class="ficha-pdf__history">
                    <table class="ficha-pdf__table">
                        <thead>
                            <tr>
                                <th>Año</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($indicador->datos_anuales_validados->sortBy('anio') as $dato)
                                <tr>
                                    <td>{{ $dato->anio }}</td>
                                    <td>{{ $dato->valor_dato }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div id="grafica-historica" class="ficha-pdf__chart"></div>
                </div>
            </article>
        </section>
    </main>

    <script>{!! $pdfEcharts !!}</script>
    <script>
        window.fichaConfig = @json($chartConfig);
    </script>
    <script>{!! $pdfFichaJs !!}</script>
</body>

</html>
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
        <img class="ficha-pdf__logos" src="{{ $pdfAsset('img/Cadena_SPED.png') }}" alt="Gobierno de Puebla"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
        <div class="ficha-pdf__logos-fallback">
            Gobierno del Estado de Puebla<br>
            Secretaría de Planeación, Finanzas y Administración<br>
            Sistema Estatal de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo.
        </div>
        <h2 class="ficha-pdf__subtitle">Ficha técnica del indicador</h2>
        <h1 class="ficha-pdf__title">{{ $indicador->nombre }}</h1>

        <section class="ficha-pdf__grid">
            <article class="ficha-pdf__panel">
                <h2 class="ficha-pdf__heading">Alineación a la planeación</h2>
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
                @if ($indicador->programasInstitucionales && $indicador->programasInstitucionales->isNotEmpty())
                    <div class="ficha-pdf__item">
                        <div class="ficha-pdf__label">Vinculado a Programas Institucionales</div>
                        <div class="ficha-pdf__value">
                            {{ $indicador->programasInstitucionales->map(fn ($programa) => $programa->siglas ?: $programa->nombre)->join(', ') }}
                        </div>
                    </div>
                @endif
            </article>
            <article class="ficha-pdf__panel">
                <h2 class="ficha-pdf__heading">Detalle técnico del indicador</h2>
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

            <article class="ficha-pdf__panel ficha-pdf__panel--full ficha-pdf__panel--history">
                <h2 class="ficha-pdf__heading">Seguimiento al indicador</h2>
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
                        <div class="ficha-pdf__label">Meta {{ $indicador->meta_anio }}</div>
                        <div class="ficha-pdf__value">{{ $indicador->meta ?? 'N/D' }}</div>
                    </div>
                </div>
                @php
                    $ultimoDatoPdf = $chartConfig['ultimoDato'] ?? null;
                    $ultimoDatoNumericoPdf = $ultimoDatoPdf !== null
                        ? filter_var($ultimoDatoPdf, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND)
                        : null;
                @endphp
                <div class="ficha-pdf__status">
                    <div class="ficha-pdf__status-item">
                        <div class="ficha-pdf__label">Último dato {{ $chartConfig['anioUltimoDato'] ?? '' }}</div>
                        <div class="ficha-pdf__status-value">
                            {{ $esDatoLineaBase || !is_numeric($ultimoDatoNumericoPdf) ? 'N/D' : number_format((float) str_replace(',', '', $ultimoDatoNumericoPdf), 2, '.', ',') }}</div>
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
                <h2 class="ficha-pdf__heading">Características del indicador</h2>
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

            <article class="ficha-pdf__panel ficha-pdf__panel--full ficha-pdf__panel--history">
                <h2 class="ficha-pdf__heading">Comportamiento histórico del indicador</h2>
                @php
                    $ultimoDatoConResultadosPdf = $indicador->datos_anuales_validados
                        ->filter(fn ($dato) => trim((string) $dato->valor_dato) !== '')
                        ->sortByDesc('anio')
                        ->first();
                    $resultadosUltimoAnioPdf = trim((string) ($ultimoDatoConResultadosPdf?->resultados ?? ''));
                @endphp
                <div class="ficha-pdf__history">
                    <div id="grafica-historica" class="ficha-pdf__chart"></div>
                    <div class="ficha-pdf__result">
                        <div>
                            <div class="ficha-pdf__label">
                                Últimos resultados
                                @if ($ultimoDatoConResultadosPdf)
                                    <span class="ficha-pdf__result-year">({{ $ultimoDatoConResultadosPdf->anio }})</span>
                                @endif
                            </div>
                            <div class="ficha-pdf__value ficha-pdf__result-text">
                                {{ $resultadosUltimoAnioPdf !== '' ? $resultadosUltimoAnioPdf : 'Sin resultados registrados para el último año.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </section>
    </main>

    <div class="ficha-pdf__footer-pleca" aria-hidden="true">
        <img src="{{ $pdfAsset('img/pleca-nueva.png') }}" alt="">
    </div>

    <script>{!! $pdfEcharts !!}</script>
    <script>
        window.fichaConfig = @json($chartConfig);
    </script>
    <script>{!! $pdfFichaJs !!}</script>
</body>

</html>

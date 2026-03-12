<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ficha técnica del indicador {{ $indicador->nombre }} - Sistema de Información para el Seguimiento a la
        Planeación y Evaluación del
        Desarrollo
        del Estado de Puebla
    </title>
    <!-- Favicon -->
    <link href="{{ asset('imagenes/favicon.png') }}" rel="icon">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/fontAwesome.js') }}" crossorigin="anonymous" defer></script>
    <link rel="stylesheet" href="{{ asset('css/estilos_impresion.css') }}">
    <style>
        .hoja {
            max-width: 100% !important;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>
    <div class="navbar">
        <div class="titulo-hoja">
            {{ $indicador->nombre }}
        </div>
        {{--
        <button class="btn" onclick="history.back()">
            <i class="fa-solid fa-home" style="color: #ffffff;"></i>
        </button> --}}

        <button class="btn" onclick="window.print()">
            <i class="fa-solid fa-print" style="color: #ffffff;"></i>
        </button>

        <button class="btn" onclick="zoomIn()">
            <i class="fa-solid fa-plus" style="color: #ffffff;"></i>
        </button>

        <button class="btn" onclick="zoomOut()">
            <i class="fa-solid fa-minus" style="color: #ffffff;"></i>
        </button>

        <button class="btn" onclick="resetZoom()">
            <i class="fa-solid fa-search-minus" style="color: #ffffff;"></i>
        </button>

        <button class="btn" onclick="searchText()">
            <i class="fa-solid fa-search" style="color: #ffffff;"></i>
        </button>

        <button class="btn" onclick="toggleFullScreen()">
            <i class="fa-solid fa-expand" style="color: #ffffff;"></i>
        </button>
    </div>

    <div class="hoja">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <img src="{{ asset('/img/logos_sped.png') }}" alt="Logo Gobierno" class="w-100">
            </div>
        </div>
        <div class="container-fluid">
            <div class="row div-ficha">
                <h2 style="color:{{ $indicador->color }}" class="text-center">
                    Indicador
                </h2>
                <div class="col-12 my-2">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 text-center"
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
                        <div class="card-content" style="border-top: 12px solid {{ $indicador->color }};">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_titulo"
                                        style="color:{{ $indicador->color }};">
                                        Institución responsable
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_datos">
                                        {{ $indicador->instituciones->nombre ?? 'Sin institución asignada' }}
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
                                    <div class="col-md-12 ficha_titulo" style="color:{{ $indicador->color }};">
                                        @if ($indicador->programa_derivado == 'Programa Regional')
                                        Temática
                                        @else
                                        Eje
                                        @endif
                                    </div>
                                    <div class="col-md-12 ficha_datos">
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
                                    <div class="col-md-12 ficha_titulo" style="color:{{ $indicador->color }};">
                                        Temática
                                    </div>
                                    <div class="col-md-12 ficha_datos">
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
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_titulo"
                                        style="color:{{ $indicador->color }};">
                                        Descripción
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_datos">
                                        {{ $indicador->descripcion }}
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
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_titulo"
                                        style="color:{{ $indicador->color }};">
                                        Fórmula
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_datos">
                                        {{ $indicador->formula }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3 col-md-3 my-2">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_titulo"
                                        style="color:{{ $indicador->color }};">
                                        Periodicidad
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_datos">
                                        {{ $indicador->periodicidad }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3 col-md-3 my-2">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_titulo"
                                        style="color:{{ $indicador->color }};">
                                        Unidad de Medida
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_datos">
                                        {{ $indicador->unidad_medida }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3 col-md-4 my-2 ">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_titulo"
                                        style="color:{{ $indicador->color }};">
                                        Tendencia
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_datos">
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
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_titulo"
                                        style="color:{{ $indicador->color }};">
                                        Línea base {{ $indicador->linea_base }}
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_datos">
                                        {{ $indicador->dato_linea_base }}
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
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_titulo"
                                        style="color:{{ $indicador->color }};">
                                        Meta 2030
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_datos">
                                        {{ $indicador->meta_2024 }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3 col-md-3 my-2">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_titulo"
                                        style="color:{{ $indicador->color }};">
                                        Próxima Actualización
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_datos">
                                        {{ $indicador->getProximaFechaActualizacionParaVista('N/D', true) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-9 my-2">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_titulo"
                                        style="color:{{ $indicador->color }};">
                                        Fuente
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_datos">
                                        {{ $indicador->fuente }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4 my-2">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_titulo"
                                        style="color:{{ $indicador->color }};">
                                        Cobertura Geográfica
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_datos">
                                        {{ $indicador->cobertura }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-8 my-2"> {{-- Ajustado para que quizás ocupe más espacio si tiene más info --}}
                    <div class="card h-100"> {{-- h-100 para que todas las cards en una fila tengan la misma altura --}}
                        <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_titulo"
                                        style="color:{{ $indicador->color }};">
                                        Semaforización
                                    </div>
                                    <div class="col-12 ficha_datos_semaforizacion">
                                        @if ($indicador->semaforizacion)
                                        @php
                                        $estado = $indicador->semaforizacion_validada;
                                        $icono = '';
                                        $colorClase = 'text-muted';
                                        $explicacionDetallada =
                                        'El estado de semaforización se basa en el porcentaje de avance respecto a la meta.';

                                        switch (strtolower($estado)) {
                                        case 'excedido':
                                        // Podrías usar un ícono diferente si 'check-double' no te parece el mejor para "Excedido"
                                        $icono = 'fas fa-star text-primary'; // Estrella azul
                                        $colorClase = 'text-primary fw-bold';
                                        $explicacionDetallada =
                                        'El valor logrado del indicador supera en 10% a la meta programada, es decir, el resultado del indicador se desvió significativamente de la meta establecida';
                                        break;
                                        case 'aceptable':
                                        $icono = 'fas fa-check-circle text-success'; // Círculo con check verde
                                        $colorClase = 'text-success fw-bold';
                                        $explicacionDetallada =
                                        'El valor logrado del indicador se encuentra entre -9% y +10% por debajo y por encima de la meta programada, es decir, se mantiene dentro de los rangos establecidos como aceptables.';
                                        break;
                                        case 'moderado':
                                        $icono = 'fas fa-exclamation-triangle text-warning'; // Triángulo de advertencia amarillo
                                        $colorClase = 'text-warning fw-bold';
                                        $explicacionDetallada =
                                        'El valor logrado del indicador es menor que la meta programada, representa un avance significativo, pero deficiente o moderado para alcanzar la meta establecida.';
                                        break;
                                        case 'insuficiente':
                                        $icono = 'fas fa-times-circle text-danger'; // Círculo con X roja
                                        $colorClase = 'text-danger fw-bold';
                                        $explicacionDetallada =
                                        'El valor alcanzado del indicador está muy por debajo de la meta programada, lo que representa un avance insuficiente para alcanzar la meta establecida.';
                                        break;
                                        case 'no clasificado':
                                        default:
                                        $estado = 'No Clasificado';
                                        $icono = 'fas fa-question-circle text-muted'; // Interrogación gris
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
                                            No hay datos disponibles para determinar el estado de
                                            semaforización.
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <h2 style="color:{{ $indicador->color }}; margin-left:20px;">Principales Resultados</h2>
                <div class="col-xs-12 col-sm-12 col-md-12 my-2">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_datos">
                                        {{-- Llamar al nuevo método del modelo Indicador --}}
                                        {{-- Usa getResultadosParaVista() o getResultadosParaVistaAlternativo() según prefieras --}}
                                        {!! nl2br(e($indicador->getResultadosParaVista('Sin resultados registrados.', true))) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <h2 style="color:{{ $indicador->color }}; margin-left:20px;" class="ocultar_tabla">Resultados
                    Históricos
                </h2>
                <div class="col-xs-12 col-sm-12 col-md-12">
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
                                        {{ $indicador->getValorDatoAnual($year, 'N/D', true) }} {{-- Solo validados --}}
                                        </td>
                                        @endfor
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @php
            // ======================================================================
            // Bloque PHP para preparar los datos para la gráfica (Simplificado)
            // ======================================================================

            // 1. Definir el Rango de Años para la Gráfica
            $anioInicioGrafica = 2020; // Nuevo inicio
            $anioFinGrafica = 2030;
            $categoriasEjeX_php = []; // Para las etiquetas del eje X (años)

            // 2. Preparar Datos Históricos del Indicador para la Serie Principal
            $datosParaGraficaPrincipal_php = []; // Array de objetos {x, y}

            for ($year = $anioInicioGrafica; $year <= $anioFinGrafica; $year++) {
                $categoriasEjeX_php[]=(string) $year; // Añadir año a las categorías del eje X

                $datoAnualParaGrafica=$indicador->datos_anuales_validados->firstWhere('anio', $year);
                $valorNumericoParaGrafica = null; // Valor por defecto si no hay dato o no es numérico

                if (
                $datoAnualParaGrafica &&
                isset($datoAnualParaGrafica->valor_dato) &&
                trim((string) $datoAnualParaGrafica->valor_dato) !== ''
                ) {
                // Intentar limpiar y convertir el valor a float
                $valorParseado = filter_var(
                $datoAnualParaGrafica->valor_dato,
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND,
                );
                if (is_numeric($valorParseado)) {
                $valorNumericoParaGrafica = (float) str_replace(',', '', $valorParseado);
                } else {
                Log::warning(
                "Grafica (PHP Block) Indicador ID {$indicador->id}, Año {$year}: valor_dato '{$datoAnualParaGrafica->valor_dato}' no es numérico y se tratará como null en la gráfica.",
                );
                }
                }
                // Formato {x,y} para la serie principal
                $datosParaGraficaPrincipal_php[] = ['x' => (string) $year, 'y' => $valorNumericoParaGrafica];
                }

                // 3. Strings para la configuración de la gráfica
                $nombreIndicadorParaGrafica_js = str_replace(
                ["\r", "\n"],
                ' ',
                $indicador->nombre ?? 'Indicador sin Nombre',
                );
                $unidadMedidaParaGrafica_js = $indicador->unidad_medida ?? 'Valor';
                $colorIndicadorParaGrafica_js = $indicador->color ?? '#008FFB'; // Color por defecto si no hay

                @endphp
                <div class="row line-break">
                    <h2 style="color:{{ $indicador->color }}; margin-left:20px;" class="">Gráfico</h2>
                    <div class="col-xs-12 col-sm-12 col-md-12 ">
                        <div class="card">
                            <div class="card-content card_ficha" style="border-top: 12px solid {{ $indicador->color }};">
                                <div class="card-body">
                                    <div id="grafica" style="height: 400px; " class="container-fluid"></div>
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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            //======================================================================
            // Función de Formateo Numérico para JavaScript (la misma que antes)
            //======================================================================
            function formatNumberForApex(value, decimalPlaces = 2) {
                if (value === null || value === undefined) {
                    return "";
                } // Devuelve cadena vacía para dataLabels
                const num = parseFloat(value);
                if (isNaN(num)) {
                    return "";
                } // Devuelve cadena vacía
                try {
                    return num.toLocaleString('en-US', { // Formato: 1,234.56
                        minimumFractionDigits: decimalPlaces,
                        maximumFractionDigits: decimalPlaces
                    });
                } catch (e) { // Fallback
                    const parts = num.toFixed(decimalPlaces).split('.');
                    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    return parts.join('.');
                }
            }

            //======================================================================
            // Variables JavaScript con datos de PHP
            //======================================================================
            const nombreIndicadorJS = @json($nombreIndicadorParaGrafica_js);
            const unidadMedidaJS = @json($unidadMedidaParaGrafica_js);
            const colorIndicadorJS = @json($colorIndicadorParaGrafica_js);

            const datosPrincipalesJS = @json($datosParaGraficaPrincipal_php); // Array de [{x,y}]
            const categoriasEjeXJS = @json($categoriasEjeX_php); // Array de años string 'YYYY'

            // console.log("Categorías X:", categoriasEjeXJS);
            // console.log("Datos Principales:", datosPrincipalesJS);

            //======================================================================
            // Opciones de ApexCharts
            //======================================================================
            var options = {
                series: [{
                    name: unidadMedidaJS, // Nombre de la serie principal
                    data: datosPrincipalesJS, // Datos para la línea principal
                    type: 'line'
                }],
                chart: {
                    height: 380,
                    type: 'line',
                    zoom: {
                        enabled: false
                    },
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                colors: [colorIndicadorJS], // Solo el color para la serie principal
                dataLabels: {
                    enabled: true, // HABILITAR ETIQUETAS DE DATOS
                    formatter: function(val, opts) {
                        return val !== null ? formatNumberForApex(val) :
                            ''; // Formatear y no mostrar "N/D" o null
                    },
                    offsetY: -10, // Ajustar la posición vertical de la etiqueta
                    style: {
                        fontSize: '10px',
                        colors: ["#fafafa"] // Color del texto de la etiqueta
                    },
                    background: { // Fondo para las etiquetas para mejor legibilidad
                        enabled: true,
                        foreColor: '#304758', // Color del texto sobre el fondo
                        padding: 4,
                        borderRadius: 2,
                        borderWidth: 1,
                        borderColor: 'rgba(180,180,180,0.6)',
                        opacity: 0.9,
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                markers: {
                    size: 5,
                    strokeColors: '#fff',
                    strokeWidth: 2,
                    hover: {
                        size: 7
                    }
                },
                title: {
                    text: nombreIndicadorJS,
                    align: 'left',
                    style: {
                        fontSize: '16px',
                        fontWeight: 'bold'
                    }
                },
                tooltip: {
                    enabled: true, // Puedes dejar el tooltip habilitado o deshabilitarlo
                    shared: true, // Si está habilitado, 'shared' puede ser útil
                    intersect: false,
                    x: {
                        format: 'yyyy'
                    },
                    y: {
                        formatter: function(value) {
                            return formatNumberForApex(value);
                        }
                    }
                },
                xaxis: {
                    categories: categoriasEjeXJS, // Años 2020-2030
                    type: 'category',
                    title: {
                        text: 'Año'
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                yaxis: {
                    title: {
                        text: unidadMedidaJS
                    },
                    labels: {
                        formatter: function(val) {
                            return formatNumberForApex(val);
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    floating: true,
                    offsetY: -25,
                    offsetX: -5
                }
            };

            // Destruir gráfica anterior si existe
            var existingChart = ApexCharts.exec("grafica", 'destroy');

            var chart = new ApexCharts(document.querySelector("#grafica"), options);
            chart.render();
        });
    </script>
    <script>
        // Zoom In
        function zoomIn() {
            document.body.style.zoom = (parseFloat(document.body.style.zoom) || 1) + 0.1;
        }

        // Zoom Out
        function zoomOut() {
            document.body.style.zoom = (parseFloat(document.body.style.zoom) || 1) - 0.1;
        }

        // Reset Zoom
        function resetZoom() {
            document.body.style.zoom = 1;
        }

        // Búsqueda de texto
        function searchText() {
            const searchTerm = prompt('Ingrese el texto a buscar:');
            if (searchTerm) {
                window.find(searchTerm);
            }
        }

        // Pantalla completa
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
    </script>
</body>

</html>
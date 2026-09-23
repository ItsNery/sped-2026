<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Indicadores de {{ $programaDerivado->nombre }} - SPED</title>
    <link href="{{ asset('imagenes/favicon.png') }}" rel="icon">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/fontAwesome.js') }}" crossorigin="anonymous" defer></script>
    <link rel="stylesheet" href="{{ asset('css/estilos_impresion.css') }}">
    <style>
        .hoja {
            max-width: 100% !important;
        }

        .reporte-programa .indicador-bloque {
            margin-bottom: .65rem;
            padding: .65rem .8rem;
        }

        .reporte-programa .indicador-header {
            align-items: center;
            gap: .75rem;
            margin-bottom: .55rem;
            padding-bottom: .45rem;
        }

        .reporte-programa .indicador-titulo {
            flex: 1;
            width: auto;
        }

        .reporte-programa .nombre-indicador {
            font-size: 8.5pt;
            line-height: 1.2;
        }

        .reporte-programa .indicador-resumen {
            display: flex;
            flex-shrink: 0;
            gap: .4rem;
        }

        .reporte-programa .indicador-chip {
            border-radius: 3px;
            font-size: 6.8pt;
            font-weight: 700;
            line-height: 1.2;
            padding: .28rem .42rem;
            text-align: center;
        }

        .reporte-programa .indicador-chip small {
            display: block;
            font-size: 5.8pt;
            font-weight: 600;
            margin-bottom: .1rem;
            text-transform: uppercase;
        }

        .reporte-programa .semaforo-excedido {
            background: #dbeafe;
            color: #1e429f;
        }

        .reporte-programa .semaforo-aceptable {
            background: #d1fae5;
            color: #065f46;
        }

        .reporte-programa .semaforo-moderado {
            background: #fef3c7;
            color: #92400e;
        }

        .reporte-programa .semaforo-insuficiente {
            background: #fee2e2;
            color: #991b1b;
        }

        .reporte-programa .semaforo-no-clasificado {
            background: #e5e7eb;
            color: #374151;
        }

        .reporte-programa .info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .35rem .7rem;
            margin: 0;
        }

        .reporte-programa .info-item {
            align-items: baseline;
            font-size: 7pt !important;
            line-height: 1.2;
            min-width: 0;
        }

        .reporte-programa .info-item--institucion {
            grid-column: 1 / -1;
            white-space: nowrap;
        }

        .reporte-programa .info-item .label {
            flex-shrink: 0;
            margin-right: .25rem;
        }

        .reporte-programa .info-item .label,
        .reporte-programa .info-item .value {
            font-size: 7pt !important;
            line-height: 1.2;
        }

        .reporte-programa .datos-anuales-adicionales {
            margin-top: .32rem;
            padding-top: .28rem;
            border-top: 1px solid #edf0f2;
        }

        .reporte-programa .datos-anuales-adicionales__titulo {
            display: block;
            margin-bottom: .10rem;
            color: #6b7280;
            font-size: 5.4pt;
            font-weight: 700;
            letter-spacing: .32px;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .reporte-programa .datos-anuales-adicionales__tabla {
            width: 100%;
            table-layout: fixed;
        }

        .reporte-programa .datos-anuales-adicionales__tabla td {
            width: 16.666%;
            padding: .07rem .16rem !important;
            border-left: 1px solid #edf0f2;
            vertical-align: middle;
        }

        .reporte-programa .datos-anuales-adicionales__tabla td:first-child {
            border-left: 0;
        }

        .reporte-programa .dato-anual-par {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: .16rem;
            line-height: 1.15;
        }

        .reporte-programa .dato-anual-par__anio {
            color: #6b7280;
            font-size: 6pt;
            font-weight: 700;
        }

        .reporte-programa .dato-anual-par__valor {
            color: #1f2937;
            font-size: 6.5pt;
            font-weight: 700;
            white-space: nowrap;
        }

        .reporte-programa .reporte-indicadores {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .reporte-programa .reporte-indicadores th,
        .reporte-programa .reporte-indicadores td {
            padding: 0;
        }

        @media print {
            @page {
                margin: 10mm 10mm 18mm;
                size: letter portrait;
            }

            .reporte-programa .reporte-indicadores thead {
                display: table-header-group;
            }

            .reporte-programa .reporte-encabezado {
                margin: 0 0 5mm !important;
            }

            .reporte-programa .reporte-encabezado .logo-header {
                margin: 0 0 2mm !important;
                max-height: 16mm;
                width: auto;
            }

            .reporte-programa .reporte-encabezado h5 {
                font-size: 8pt;
            }

            .reporte-programa .reporte-encabezado h6 {
                font-size: 7pt;
                margin: 0 !important;
            }

            .reporte-programa .reporte-encabezado .header-line {
                margin-top: 1mm !important;
            }

            .reporte-programa .indicador-bloque {
                break-inside: avoid-page !important;
                display: block;
                margin-bottom: 3mm;
                page-break-inside: avoid !important;
                padding: 3mm 3.5mm;
                width: 100%;
            }

            .reporte-programa .reporte-indicadores .reporte-indicador-grupo {
                break-inside: avoid-page !important;
                page-break-inside: avoid !important;
            }

            .reporte-programa .info-grid {
                gap: 2mm 4mm;
            }

            .reporte-programa .datos-anuales-adicionales {
                margin-top: 1mm;
                padding-top: 1mm;
            }
        }
    </style>
</head>

<body>
    <div class="navbar">
        <div class="titulo-hoja">{{ $programaDerivado->nombre }}</div>

        <button class="btn" type="button" onclick="window.print()" title="Imprimir">
            <i class="fa-solid fa-print" style="color: #ffffff;"></i>
        </button>
        <button class="btn" type="button" onclick="zoomIn()" title="Acercar">
            <i class="fa-solid fa-plus" style="color: #ffffff;"></i>
        </button>
        <button class="btn" type="button" onclick="zoomOut()" title="Alejar">
            <i class="fa-solid fa-minus" style="color: #ffffff;"></i>
        </button>
        <button class="btn" type="button" onclick="resetZoom()" title="Restablecer zoom">
            <i class="fa-solid fa-search-minus" style="color: #ffffff;"></i>
        </button>
        <button class="btn" type="button" onclick="searchText()" title="Buscar">
            <i class="fa-solid fa-search" style="color: #ffffff;"></i>
        </button>
        <button class="btn" type="button" onclick="toggleFullScreen()" title="Pantalla completa">
            <i class="fa-solid fa-expand" style="color: #ffffff;"></i>
        </button>
    </div>

    <div class="hoja reporte-programa">
        <table class="reporte-indicadores">
            <thead>
                <tr>
                    <th scope="col">
                        <div class="header-section reporte-encabezado text-center mb-4">
                            <img src="{{ asset('img/Cintillos-SPED-35.png') }}" alt="SPED" class="logo-header mb-3">
                            <h5 class="text-uppercase text-dark font-weight-bold m-0"
                                style="color: var(--colorGobierno);">
                                Seguimiento a Indicadores
                            </h5>
                            <h6 class="text-muted mt-1">{{ $programaDerivado->nombre }}</h6>
                            <div class="header-line mt-3"></div>
                        </div>
                    </th>
                </tr>
            </thead>
            @forelse ($indicadores as $index => $indicador)
                @php
                    $semaforo = $indicador->semaforizacion_validada;
                    $semaforoClass = match ($semaforo) {
                        'Excedido' => 'semaforo-excedido',
                        'Aceptable' => 'semaforo-aceptable',
                        'Moderado' => 'semaforo-moderado',
                        'Insuficiente' => 'semaforo-insuficiente',
                        default => 'semaforo-no-clasificado',
                    };
                    $ultimoAnio = $indicador->anio_reciente_validado;
                @endphp
                <tbody class="reporte-indicador-grupo">
                    <tr>
                        <td>
                            <div class="indicador-bloque">
                                <div class="indicador-header">
                                    <div class="indicador-titulo">
                                        <span class="badge-numero">{{ $index + 1 }}</span>
                                        <span class="nombre-indicador">{{ $indicador->nombre }}</span>
                                    </div>
                                    <div class="indicador-resumen">
                                        <span class="indicador-chip {{ $semaforoClass }}">
                                            <small>Semáforo</small>{{ $semaforo }}
                                        </span>
                                        <span class="indicador-chip semaforo-no-clasificado">
                                            <small>Último dato</small>
                                            {{ $ultimoAnio ? $ultimoAnio . ': ' . $indicador->getValorDatoAnual($ultimoAnio, 'N/D', true) : 'N/D' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="info-grid">
                                    <div class="info-item info-item--institucion">
                                        <span class="label">Institución responsable:</span>
                                        <span
                                            class="value">{{ $indicador->institucion?->nombre ?? 'Sin institución' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="label">Unidad de Medida:</span>
                                        <span class="value">{{ $indicador->unidad_medida }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="label">Tendencia:</span>
                                        <span class="value">{{ $indicador->tendencia }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="label">Línea Base {{ $indicador->linea_base }}:</span>
                                        <span class="value">{{ $indicador->dato_linea_base }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="label">Meta {{ $indicador->meta_anio }}:</span>
                                        <span class="value font-weight-bold">{{ $indicador->meta }}</span>
                                    </div>
                                </div>

                                @php
                                    $aniosDeReferencia = [
                                        $indicador->linea_base !== null ? (int) $indicador->linea_base : null,
                                        $indicador->meta_anio !== null ? (int) $indicador->meta_anio : null,
                                    ];
                                    $datosAnualesAdicionales = $indicador->datos_anuales_validados
                                        ->filter(
                                            fn($dato) => $dato->valor_dato !== null &&
                                                trim((string) $dato->valor_dato) !== '' &&
                                                !in_array((int) $dato->anio, $aniosDeReferencia, true),
                                        )
                                        ->values();
                                @endphp

                                @if ($datosAnualesAdicionales->isNotEmpty())
                                    <div class="datos-anuales-adicionales">
                                        <span class="datos-anuales-adicionales__titulo">
                                            Datos anuales
                                        </span>
                                        <table class="datos-anuales-adicionales__tabla"
                                            aria-label="Datos anuales validados">
                                            <tbody>
                                                @foreach ($datosAnualesAdicionales->chunk(6) as $filaDatos)
                                                    <tr>
                                                        @foreach ($filaDatos as $datoAnual)
                                                            <td>
                                                                <div class="dato-anual-par">
                                                                    <span class="dato-anual-par__anio">
                                                                        {{ $datoAnual->anio }}
                                                                    </span>
                                                                    <span class="dato-anual-par__valor">
                                                                        {{ $indicador->getValorDatoAnual($datoAnual->anio, 'N/D', true) }}
                                                                    </span>
                                                                </div>
                                                            </td>
                                                        @endforeach
                                                        @for ($celda = $filaDatos->count(); $celda < 6; $celda++)
                                                            <td aria-hidden="true"></td>
                                                        @endfor
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                </tbody>
            @empty
                <tbody>
                    <tr>
                        <td>
                            <p class="text-center text-muted">Este programa no tiene indicadores asociados.</p>
                        </td>
                    </tr>
                </tbody>
            @endforelse
        </table>

        <div class="footer-legal">
            <div class="footer-content">
                <span class="sistema-nombre">SPED</span>
                <span class="fecha-impresion">Impreso el: {{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <script>
        function zoomIn() {
            document.body.style.zoom = (parseFloat(document.body.style.zoom) || 1) + 0.1;
        }

        function zoomOut() {
            document.body.style.zoom = Math.max((parseFloat(document.body.style.zoom) || 1) - 0.1, 0.5);
        }

        function resetZoom() {
            document.body.style.zoom = 1;
        }

        function searchText() {
            const searchTerm = prompt('Ingrese el texto a buscar:');
            if (searchTerm) {
                window.find(searchTerm);
            }
        }

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    </script>
</body>

</html>

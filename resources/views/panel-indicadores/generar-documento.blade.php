<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Indicadores de
        {{ $user->name }} - SPED
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
</head>

<body>
    <div class="navbar">
        <div class="titulo-hoja">
            {{ $user->name }}
        </div>

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
        <div class="header-section text-center mb-4">
            <img src="{{ asset('img/Cadena_SPED.png') }}" alt="Logo Gobierno" class="logo-header mb-3">
            <h5 class="text-uppercase text-dark font-weight-bold m-0" style="color: var(--colorGobierno);">
                Reporte de Indicadores
            </h5>
            <h6 class="text-muted mt-1">{{ $user->institucion->nombre }}</h6>
            <div class="header-line mt-3"></div>
        </div>

        <div class="indicadores-container">
            @foreach ($user->indicadores as $index => $indicador)
                <div class="indicador-bloque">
                    <div class="indicador-header">
                        <div class="indicador-titulo">
                            <span class="badge-numero">{{ $index + 1 }}</span>
                            <span class="nombre-indicador">{{ $indicador->nombre }}</span>
                        </div>
                    </div>

                    <div class="info-grid">
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
                            <span class="value"> {{ $indicador->dato_linea_base }}</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Meta {{ $indicador->meta_anio }}:</span>
                            <span class="value font-weight-bold">{{ $indicador->meta_anio }}:
                                {{ $indicador->meta }}</span>
                        </div>
                    </div>

                    <div class="historico-section">
                        <div class="historico-grid">
                            @for ($year = 2020; $year <= 2029; $year++)
                                <div class="dato-anio">
                                    <div class="anio-label">{{ $year }}</div>
                                    <div class="anio-valor">{{ $indicador->getValorDatoAnual($year) ?? '-' }}</div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="firma-section mt-5">
            </div>
            <div class="footer-legal">
                <div class="footer-content">
                    <span class="sistema-nombre">
                        SPED
                    </span>
                    <span class="fecha-impresion">Impreso el: {{ date('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <div class="firma-section mt-5">
            <p class="text-center text-muted small mb-5">
                <em>Nota:</em> Hago de mi entero conocimiento que la información mostrada en este documento ha sido
                validada.
            </p>
            <div class="firma-box">
                <div class="linea-firma"></div>
                <p class="nombre-firma">{{ $user->institucion->titular ?? 'Persona titular' }}</p>
                <p class="cargo-firma">Firma del Titular</p>
            </div>
        </div>
    </div>

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

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
            <img src="{{ asset('assets-administrador/img/logos_sped.png') }}" alt="Logo Gobierno" class="logo-header mb-3">
            <h5 class="text-uppercase text-dark font-weight-bold m-0" style="color: var(--colorGobierno);">
                Reporte de Indicadores
            </h5>
            <h6 class="text-muted mt-1">{{ $user->name }}</h6>
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
                        <span class="label">Meta 2030:</span>
                        <span class="value font-weight-bold">{{ $indicador->meta_2024 }}</span>
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
            <em>Nota:</em> Hago de mi entero conocimiento que la información mostrada en este documento ha sido validada.
        </p>
        <div class="firma-box">
            <div class="linea-firma"></div>
            <p class="nombre-firma">{{ $user->institucion->titular ?? 'Persona titular' }}</p>
            <p class="cargo-firma">Firma del Titular</p>
        </div>
    </div>
    </div>
    <!-- <div class="hoja">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <img src="{{ asset('assets-administrador/img/logos_gobierno.png') }}" alt="Logo Gobierno"
                    class="w-50">
            </div>
        </div>
        <div class="row text-center my-2">
            <h6 class="text-muted mb-1" style="font-size: 1rem;">Información de los Indicadores de la Institución</h6>
            <h6 class="font-weight-bold text-muted" style="font-size: 1.1rem;">{{ $user->name }}</h6>
        </div>

        <div class="row">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>
                            #</th>
                        <th>
                            Indicador</th>
                        <th>
                            Linea base</th>
                        <th>
                            Unidad de medida</th>
                        <th>
                            Tendencia</th>
                        <th>
                            Estatus</th>
                        <th>
                            2020</th>
                        <th>
                            2021</th>
                        <th>
                            2022</th>
                        <th>
                            2023</th>
                        <th>
                            2024</th>
                        <th>
                            2025</th>
                        <th>
                            2026</th>
                        <th>
                            2027</th>
                        <th>
                            2028</th>
                        <th>
                            2029</th>
                        <th>
                            Meta 2030
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($user->indicadores as $index => $indicador)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $indicador->nombre }}</td>
                        <td>{{ $indicador->linea_base }} <br /> {{ $indicador->dato_linea_base }}</td>
                        <td>{{ $indicador->unidad_medida }}</td>
                        <td>{{ $indicador->tendencia }}</td>
                        <td>
                            @if ($indicador->indicador_validado === 1)
                            Validado
                            @elseif ($indicador->indicador_validado === 0)
                            No validado
                            @else
                            Sin actualizar
                            @endif
                        </td>
                        <td>{{ $indicador->getValorDatoAnual(2020) ?? 'N/A' }}</td>
                        <td>{{ $indicador->getValorDatoAnual(2021) ?? 'N/A' }}</td>
                        <td>{{ $indicador->getValorDatoAnual(2022) ?? 'N/A' }}</td>
                        <td>{{ $indicador->getValorDatoAnual(2023) ?? 'N/A' }}</td>
                        <td>{{ $indicador->getValorDatoAnual(2024) ?? 'N/A' }}</td>
                        <td>{{ $indicador->getValorDatoAnual(2025) ?? 'N/A' }}</td>
                        <td>{{ $indicador->getValorDatoAnual(2026) ?? 'N/A' }}</td>
                        <td>{{ $indicador->getValorDatoAnual(2027) ?? 'N/A' }}</td>
                        <td>{{ $indicador->getValorDatoAnual(2028) ?? 'N/A' }}</td>
                        <td>{{ $indicador->getValorDatoAnual(2029) ?? 'N/A' }}</td>
                        <td>{{ $indicador->meta_2024 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="row mt-5">
            <p class="text-muted text-center">
                <em>Nota:</em> Hago de mi entero conocimiento que la información mostrada en este documento ha sido
                validada.
            </p>

            <div class="text-center mt-4">
                <p class="mb-5">Firma del titular</p>

                <div style="border-bottom: 1px solid #333; width: 250px; margin: 0 auto;"></div>
                <p class="mt-2 fw-bold">{{ $user->institucion->titular ?? 'Persona titular' }}</p>
            </div>
        </div>

    </div> -->

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


{{-- <h1>Reporte de Indicadores para {{ $user->name }}</h1>

@foreach ($indicadores as $indicador)
<h2>Indicador: {{ $indicador->nombre }}</h2>
<p>Descripción: {{ $indicador->descripcion }}</p>
<hr>
@endforeach

{{ $user->institucion->titular }} --}}
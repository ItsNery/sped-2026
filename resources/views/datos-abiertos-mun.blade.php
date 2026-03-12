@extends('layouts.plantilla')
@section('title', 'Datos Abiertos: Indicadores Municipales')
@section('meta-description',
    'Sección de los Datos Abiertos los Indicadores Municipales dentro del
    Sistema de Información para el
    Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Datos Abiertos: Indicadores Municipales - Sistema de Información para el Seguimiento a la Planeación y Evaluación del
    Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Sección de los Datos Abiertos los Indicadores Municipales dentro del
    Sistema de Información para el
    Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    'Datos Abiertos: Indicadores Municipales - Sistema de Información para el Seguimiento a la Planeación y Evaluación del
    Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección de los Datos Abiertos los Indicadores Municipales dentro del
    Sistema de Información para el
    Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla.')
@section('content')
    <div class="container">
        <h1 class="my-3">Datos Abiertos: Indicadores Municipales</h1>
        <img class="w-100 block-auto" src="{{ asset('img/pleca-nueva.png') }}" title="Pleca"
            alt="Pleca conformada por una línea partida por cuatro colores">
        &nbsp;
        <h4 class="text-justify my-3">Los datos abiertos disponibles pueden ser utilizados, reutilizados y
            redistribuidos libremente por cualquier persona, se encuentran sujetos al requerimiento de atribución de
            la misma manera en que aparecen.</h4>
        <section id="mun-section" class="mb-5">
            <div class="row g-4">
                {{-- Columna para los Botones de Navegación (Tabs) --}}
                <div class="col-md-3">
                    <div class="nav flex-column nav-pills" id="ped-pills-tab" role="tablist" aria-orientation="vertical">

                        {{-- Recorremos la colección de municipios (que ya tienen la propiedad 'slug') --}}
                        @foreach ($municipios as $municipio)
                            <button class="nav-link @if ($loop->first) active @endif" {{-- Marca el primero como activo --}}
                                id="{{ $municipio->slug }}-tab" {{-- ID único para el botón (slug + '-tab') --}} data-bs-toggle="pill"
                                data-bs-target="#{{ $municipio->slug }}" {{-- Apunta al ID del panel de contenido --}} type="button" role="tab"
                                aria-controls="{{ $municipio->slug }}" {{-- Controla el panel con este slug --}}
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}"> {{-- Indica si está seleccionado (el primero) --}}

                                {{-- Muestra el nombre del municipio relacionado. Usa ?? por si acaso --}}
                                {{ $municipio->municipio->nombre ?? 'Nombre no disponible' }}
                            </button>
                        @endforeach

                    </div> {{-- Fin nav --}}
                </div> {{-- Fin col-md-3 --}}
                {{-- Columna para el Contenido de las Pestañas --}}
                <div class="col-md-9">
                    <div class="tab-content" id="ped-pills-tabContent">

                        {{-- Recorremos la colección de municipios (que ya tienen la propiedad 'slug') --}}
                        @foreach ($municipios as $municipio)
                            {{-- El div del panel de contenido. 'show active' solo para el primero --}}
                            <div class="tab-pane fade @if ($loop->first) show active @endif"
                                id="{{ $municipio->slug }}" {{-- Usa el slug directamente desde el objeto --}} role="tabpanel"
                                aria-labelledby="{{ $municipio->slug }}-tab"> {{-- Referencia al ID del botón (slug + '-tab') --}}

                                {{-- Contenido específico para este municipio --}}
                                <h4 class="h5 mb-3 fw-semibold" style="color: var(--color-primary);">
                                    {{-- Accede al nombre del municipio relacionado. Usa ?? por si acaso no existe --}}
                                    {{ $municipio->municipio->nombre ?? 'Nombre no disponible' }}
                                </h4>
                                <p class="mb-4 text-muted">
                                    Indicadores municipales
                                </p>
                                <table class="table table-bordered data-table">
                                    <thead>
                                        <tr>
                                            <th>Nombre del Conjunto</th>
                                            <th>Formatos de Descarga</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            {{-- Nombre del conjunto usando el nombre del municipio --}}
                                            <td>Base - Total de Indicadores: {{ $municipio->municipio->nombre ?? 'N/A' }}
                                            </td>
                                            <td class="download-icons">
                                                <a href="{{ route('datos.municipio.descargar', ['municipioId' => $municipio->municipio->id, 'formato' => 'json']) }}"
                                                    title="Descargar JSON">
                                                    <img src="{{ asset('img/js.png') }}" alt="Icono de JSON">
                                                </a>
                                                <a href="{{ route('datos.municipio.descargar', ['municipioId' => $municipio->municipio->id, 'formato' => 'csv']) }}"
                                                    title="Descargar CSV">
                                                    <img src="{{ asset('img/csv.png') }}" alt="Icono de CSV">
                                                </a>
                                                <a href="{{ route('datos.municipio.descargar', ['municipioId' => $municipio->municipio->id, 'formato' => 'xlsx']) }}"
                                                    title="Descargar XLS">
                                                    <img src="{{ asset('img/xls.png') }}" alt="Icono de XLS">
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div> {{-- Fin tab-pane --}}
                        @endforeach

                    </div> {{-- Fin tab-content --}}
                </div> {{-- Fin col-md-9 --}}
            </div>
        </section>
    </div>
@endsection

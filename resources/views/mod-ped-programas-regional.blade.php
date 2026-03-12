@extends('layouts.plantilla')
@section('title', 'Programa Derivado Regional de la Modificación y Adecuación del Plan Estatal de Desarrollo 2024-2030')
@section('meta-description',
    'Sección del Programa Derivado Regional de la Modificación y Adecuación del Plan Estatal de Desarrollo 2024-2030 del
    Sistema de Información para el Seguimiento a la Planeación y Evaluación del
    Desarrollo
    del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Programa Derivado Regional de la Modificación y Adecuación del Plan Estatal de Desarrollo 2024-2030 - Sistema de
    Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Sección del Programa Derivado Regional de la Modificación y Adecuación del Plan Estatal de Desarrollo 2024-2030 del
    Sistema de Información para el Seguimiento a la Planeación y Evaluación del
    Desarrollo
    del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    'Programa Derivado Regional de la Modificación y Adecuación del Plan Estatal de Desarrollo 2024-2030 - Sistema de
    Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección del Programa Derivado Regional de la Modificación y Adecuación del Plan Estatal de Desarrollo 2024-2030 del
    Sistema de Información para el Seguimiento a la Planeación y Evaluación del
    Desarrollo
    del Estado de Puebla')
@section('css')
@endsection
@section('jss-inicial')
@endsection
@section('content')
    <img src="" class="w-100 px-0"
        loading="lazy" />
    <div class="row nav_derivados mx-0">
        <div class="col-md-3 nav_derivados1 ocultar_submenu"
            onclick="location.href='{{ url('/ped-programas/sectoriales') }}';">
            <button class="dropbtn">Sectoriales</button>
        </div>
        <div class="col-md-3 nav_derivados2 ocultar_submenu"
            onclick="location.href='{{ url('/ped-programas/especiales') }}';">
            <button class="dropbtn">Especiales</button>
        </div>
        <div class="col-md-3 nav_derivados3 ocultar_submenu"
            onclick="location.href='{{ url('/ped-programas/institucionales') }}';">
            <button class="dropbtn">Institucionales</button>
        </div>
        <div class="col-md-3 nav_derivados4_active ocultar_submenu"
            onclick="location.href='{{ url('/ped-programas/regional') }}';">
            <button class="dropbtn">Regional</button>
        </div>
    </div>
    &nbsp;
    <div class="row contenido" style="margin-left: auto; margin-right: auto;">
        <div class="col-md-1"></div>
        <div class="col-md-3 objetivo_regional">
            <img class="img-fluid w-100" src="{{ asset('img/what4-2.png') }}">
        </div>
        <div class="col-md-7 objetivo">
            <p>Define las directrices que buscan potenciar las actividades en las distintas regiones del estado, con
                el propósito de fomentar un desarrollo equilibrado en consonancia con las características
                geográficas y económicas específicas de cada territorio, abarcando tanto regiones como municipios.
            </p>
            <a target="_blank" href="https://planeader.puebla.gob.mx/progderi/PD2ProgramaRegional (1)20240408203409.pdf"
                class="a-simple">
                <button class="Documents-btn" style="background-color:gray">
                    <span class="folderContainer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 146 113" height="113"
                            width="146" class="fileBack">
                            <path fill="url(#paint0_linear_117_4)"
                                d="M0 4C0 1.79086 1.79086 0 4 0H50.3802C51.8285 0 53.2056 0.627965 54.1553 1.72142L64.3303 13.4371C65.2799 14.5306 66.657 15.1585 68.1053 15.1585H141.509C143.718 15.1585 145.509 16.9494 145.509 19.1585V109C145.509 111.209 143.718 113 141.509 113H3.99999C1.79085 113 0 111.209 0 109V4Z">
                            </path>
                            <defs>
                                <linearGradient gradientUnits="userSpaceOnUse" y2="95.4804" x2="72.93" y1="0"
                                    x1="0" id="paint0_linear_117_4">
                                    <stop stop-color="#8F88C2"></stop>
                                    <stop stop-color="#5C52A2" offset="1"></stop>
                                </linearGradient>
                            </defs>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 88 99" height="99"
                            width="88" class="filePage">
                            <rect fill="url(#paint0_linear_117_6)" height="99" width="88"></rect>
                            <defs>
                                <linearGradient gradientUnits="userSpaceOnUse" y2="160.5" x2="81" y1="0"
                                    x1="0" id="paint0_linear_117_6">
                                    <stop stop-color="white"></stop>
                                    <stop stop-color="#686868" offset="1"></stop>
                                </linearGradient>
                            </defs>
                        </svg>

                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 160 79" height="79"
                            width="160" class="fileFront">
                            <path fill="url(#paint0_linear_117_5)"
                                d="M0.29306 12.2478C0.133905 9.38186 2.41499 6.97059 5.28537 6.97059H30.419H58.1902C59.5751 6.97059 60.9288 6.55982 62.0802 5.79025L68.977 1.18034C70.1283 0.410771 71.482 0 72.8669 0H77H155.462C157.87 0 159.733 2.1129 159.43 4.50232L150.443 75.5023C150.19 77.5013 148.489 79 146.474 79H7.78403C5.66106 79 3.9079 77.3415 3.79019 75.2218L0.29306 12.2478Z">
                            </path>
                            <defs>
                                <linearGradient gradientUnits="userSpaceOnUse" y2="82.8317" x2="66.9106" y1="8.71323"
                                    x1="38.7619" id="paint0_linear_117_5">
                                    <stop stop-color="#C3BBFF"></stop>
                                    <stop stop-color="#51469A" offset="1"></stop>
                                </linearGradient>
                            </defs>
                        </svg>
                    </span>
                    <p class="text">Consultar documento</p>
                </button>
            </a>
        </div>
        <div class="col-md-1"></div>
    </div>
    &nbsp;
    <div class="row container" style="margin-left: auto; margin-right: auto;">
        <div class="col-md-4">
            <article class="leaderboard" style="height:700px; overflow: scroll; overflow-x: hidden; overflow-y: none;">
                <header>
                    <img src="{{ asset('img/Regiones/mapas/Mapa_puebla_gris.png') }}" width="20%"
                        style="padding-left:40px; padding-top:10px;">
                    <h1 class="leaderboard__title"><span class="leaderboard__title--top">Regiones</span></h1>
                </header>
                <table class="table" style="border-spacing: 0 !important; border-collapse: collapse !important;">
                    <main class="leaderboard__profiles">
                        <tr>
                            <td>
                                @foreach ($regionesCompleto as $region)
                                    <article class="leaderboard__profile" onclick="clickaction(this)"
                                        id="{{ str_replace(' ', '_', $region->nombre_region) }}">
                                        <img src="{{ asset('img/Regiones/regiones_' . str_pad($region->id, 2, '0', STR_PAD_LEFT) . '.png') }}"
                                            width="100%" alt="Region {{ $region->id }}" class="leaderboard__picture">
                                        <span class="leaderboard__name">Región {{ $region->id }}:
                                            {{ $region->nombre_region }}</span>
                                    </article>
                                @endforeach
                            </td>
                        </tr>
                    </main>
                </table>
            </article>
        </div>

        <div class="col-md-8" id="mapa">
            <div class="row">
                <div class="col-md-12">
                    <img class="img-fluid" src="{{ asset('img/Regiones/mapas/Mapa_puebla.png') }}" width="100%"
                        id="mapa_general" />
                    @foreach ($regionesCompleto as $region)
                        <img class="img-fluid"
                            src="{{ asset('img/Regiones/mapas/region_' . str_pad($region->id, 2, '0', STR_PAD_LEFT) . '.png') }}"
                            id="mapa_{{ str_pad($region->id, 2, '0', STR_PAD_LEFT) }}" style="display:none;" />
                    @endforeach
                </div>
            </div>
        </div>
        @foreach ($regionesCompleto as $region)
            <div class="col-md-8 regiones" id="region{{ $region->id }}" style="display: none;">
                <div class="row">
                    <div class="col-md-8">
                        <img class="img-fluid"
                            src="{{ asset('img/Regiones/mapas/Mapa_region' . str_pad($region->id, 2, '0', STR_PAD_LEFT) . '.png') }}"
                            width="100%" />
                    </div>
                    <div class="col-md-4">
                        <h1>Región {{ $region->id }}. {{ $region->nombre_region }}</h1>
                        &nbsp;
                        <h3 style="text-align:justify;">
                            {{ $region->descripcion ?? 'No hay descripción disponible para esta región.' }}
                        </h3>
                        &nbsp;
                        <ol class="gradient-list">
                            @foreach ($region->municipios as $municipio)
                                <li>
                                    {{ $municipio->municipio }} {{ $municipio->nombre }}
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
    &nbsp;
    @include('layouts.ind_region2', ['regionesConIndicadores' => $regionesConIndicadores])
    {{-- @include('layouts.ind_region2') --}}
@section('jss-final')
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/mostrar_regiones.js') }}"></script>
@endsection
@endsection

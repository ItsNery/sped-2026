@extends('layouts.plantilla')
@section('title', 'Eje 5 del Plan Estatal de Desarrollo 2019-2024')
@section('meta-description',
    'Sección dedicada al Eje 5 del Plan Estatal de Desarrollo 2019-2024 dentro del Sistema
    de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Eje 5 del Plan Estatal de Desarrollo 2019-2024 - Sistema de Información para el Seguimiento a la Planeación y
    Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Sección dedicada al Eje 5 del Plan Estatal de Desarrollo 2019-2024 dentro del Sistema
    de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    'Eje 5 del Plan Estatal de Desarrollo 2019-2024 - Sistema de Información para el Seguimiento a la Planeación y
    Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección dedicada al Eje 5 del Plan Estatal de Desarrollo 2019-2024 dentro del Sistema
    de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('content')
    <img src="{{ asset('img/Banners/Banner_PED/banner_eje5.jpg') }}" class="w-100 px-0">
    <div class="row nav_ejes">
        <div class="col-md-2 nav_eje1 ocultar_submenu" onclick="location.href='{{ url('/ped/eje-1') }}';"><button
                class="dropbtn">Eje
                1</button></div>
        <div class="col-md-2 nav_eje2 ocultar_submenu" onclick="location.href='{{ url('/ped/eje-2') }}';"><button
                class="dropbtn">Eje
                2</button></div>
        <div class="col-md-2 nav_eje3 ocultar_submenu" onclick="location.href='{{ url('/ped/eje-3') }}';"><button
                class="dropbtn">Eje
                3</button></div>
        <div class="col-md-2 nav_eje4 ocultar_submenu" onclick="location.href='{{ url('/ped/eje-4') }}';"><button
                class="dropbtn">Eje
                4</button></div>
        <div class="col-md-2 nav_eje5_active ocultar_submenu" onclick="location.href='{{ url('/ped/eje-5') }}';"><button
                class="dropbtn">Eje Especial</button></div>
        <div class="col-md-2 nav_eje6 ocultar_submenu" onclick="location.href='{{ url('/ped/eje-6') }}';"><button
                class="dropbtn">Transversales</button></div>
    </div>
    <div class="row contenido">
        <div class="col-sm-12 col-md-3 offset-md-1 objetivo_5">
            <h2>Objetivo</h2>
        </div>
        <div class="col-sm-12 col-md-7 objetivo_5">
            <p>Dotar de herramientas a las instituciones de la Administración Pública Estatal (APE) para un correcto
                actuar, basado en la efectividad, la transparencia y la democracia, en donde se propicie la
                participación ciudadana, se combata a la corrupción y se impulse la modernización.</p>
        </div>
    </div>
    <div class="row indicador_5">
        <div class="container">
            <h2>{{ $indicadores->count() }} INDICADORES</h2>
            @foreach ($indicadores as $indicador)
                <div class="row mx-auto">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div class="card-content card_indicador5">
                                <div class="card-body">
                                    <a href="{{ route('ficha-tecnica.show', $indicador->id) }}"
                                        class="text-decoration-none">
                                        <div class="row">
                                            <!-- Título -->
                                            <div class="col-12 col-md-10">
                                                <div class="titulo">
                                                    {{ $indicador->nombre }}
                                                </div>
                                            </div>
                                            <!-- Resultados -->
                                            <div class="col-12 col-md-2 text-md-end">
                                                <div class="ultimo">
                                                    Resultado: {{ $indicador->anio_reciente ?? 'Sin datos' }}
                                                </div>
                                                <div class="datos_eje5">
                                                    {{ $indicador->dato_reciente ?? 'Sin datos' }}
                                                </div>
                                            </div>
                                            <!-- ODS -->
                                            <div class="col-12 ods mt-3">
                                                <div class="d-flex flex-wrap align-items-center ods">
                                                    @foreach ($indicador->ods->unique('id') as $ods)
                                                        <img src="{{ asset('/img/Icons_ODS/' . $ods->id . '.png') }}"
                                                            alt="Imagen de ODS {{ $ods->id }}"
                                                            class="hvr-wobble-top img-fluid rounded">
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mx-auto mb-3">
                    <!-- Unidad de Medida -->
                    <div class="col-xl-3 col-12 hvr-grow">
                        <div class="card">
                            <div class="card-content card_indicador5">
                                <div class="card-body text-center">
                                    <div class="datos">Unidad de Medida</div>
                                    <div class="datos_eje5">{{ $indicador->unidad_medida }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Tendencia -->
                    <div class="col-xl-3 col-12 hvr-grow">
                        <div class="card">
                            <div class="card-content card_indicador5">
                                <div class="card-body text-center">
                                    <div class="datos">Tendencia</div>
                                    <div class="datos_eje5">{{ $indicador->tendencia }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Línea Base -->
                    <div class="col-xl-3 col-12 hvr-grow">
                        <div class="card">
                            <div class="card-content card_indicador5">
                                <div class="card-body text-center">
                                    <div class="datos">Línea Base {{ $indicador->linea_base }}</div>
                                    <div class="datos_eje5">{{ $indicador->dato_linea_base }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Meta 2024 -->
                    <div class="col-xl-3 col-12 hvr-grow">
                        <div class="card">
                            <div class="card-content card_indicador5">
                                <div class="card-body text-center">
                                    <div class="datos">Meta 2024</div>
                                    <div class="datos_eje5">{{ $indicador->meta_2024 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

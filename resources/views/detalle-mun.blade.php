@extends('layouts.plantilla')
@section('title', 'Indicadores del Plan Municipal de Desarrollo de ' . $municipio->municipio->nombre)
@section('meta-description',
    'Sección dedicada al seguimiento a los Indicadores del Plan Municipal de Desarollo de ' .
    $municipio->municipio->nombre .
    ' dentro del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Indicadores del Plan Municipal de Desarrollo de ' .
    $municipio->municipio->nombre .
    ' - Sistema de Información para el Seguimiento a la Planeación y
    Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Sección dedicada al seguimiento a los Indicadores del Plan Municipal de Desarollo de ' .
    $municipio->municipio->nombre .
    ' dentro del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    'Indicadores del Plan Municipal de Desarrollo de ' .
    $municipio->municipio->nombre .
    ' - Sistema de Información para el Seguimiento a la Planeación y
    Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección dedicada al seguimiento a los Indicadores del Plan Municipal de Desarollo de ' .
    $municipio->municipio->nombre .
    ' dentro del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('css')
@endsection
@section('jss-inicial')
@endsection
@section('content')
    <img src="{{ asset($municipio->banner) }}" width="100%" class="px-0">
    <div class="row contenido" style="margin-left: auto; margin-right: auto;">
        <div class="col-md-1"></div>
        <div class="col-md-3 objetivo">
            <img class="img-fluid" src="{{ asset($municipio->icono) }}" width="70%" />
        </div>
        <div class="col-md-7 objetivo">
            <h2 style="color:#691C32">Objetivo</h2>
            <p>
                {{ $municipio->objetivo ?? 'No disponible' }}
            </p>
            <a target="_blank" href="{{ asset($municipio->convenio) }}" class="a-simple">
                @php
                    $color = '#C1B999';
                @endphp
                @include('layouts.boton-documento')
            </a>
        </div>
        <div class="col-md-1"></div>
    </div>
    <br>
    &nbsp;
    <div class="row ficha" style="background-color:rgba(207, 199, 186, 0.4)">
        <div class="container">
            <h2 style="color:#691C32">{{ $totalIndicadores }} INDICADORES</h2> <br>
            @foreach ($indicadores as $indicador)
                <div class="row" style="margin-left: auto; margin-right: auto;">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 mb-3">
                        <div class="card overflow-hidden">
                            <div class="card-content card_indicador">
                                <div class="card-body">
                                    <a href="{{ route('mostrarFicha', $indicador->id) }}" style="text-decoration:none;">
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-10">
                                                <div class="titulo">
                                                    {{ $indicador->indicador }}
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="ultimo" style="">
                                                    Resultado
                                                    {{ $indicador->aniomasreciente }}
                                                </div>
                                                <div class="datos_eje1">
                                                    {{ $indicador->datoaniomasreciente }}
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="align-self-left" style="text-align: left">
                                                    @foreach ($indicador->ods->unique('id') as $ods)
                                                        <img src="{{ asset('/img/Icons_ODS/' . $ods->id . '.png') }}"
                                                            alt="Imagen de ODS {{ $ods->id }}" class="hvr-wobble-top"
                                                            style="width:60px; border-radius: 5px 5px 5px 5px;">
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
                <div class="row" style="margin-left: auto; margin-right: auto;">
                    <div class="col-xl-3 col-xs-12 col-sm-12 col-12 hvr-grow">
                        <div class="card">
                            <div class="card-content card_indicador1">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos" style="">
                                            Unidad de Medida
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos_eje2">
                                            {{ $indicador->unidad_medida }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-xs-12 col-sm-12 col-12 hvr-grow">
                        <div class="card">
                            <div class="card-content card_indicador1">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos" style="">
                                            Tendencia<br>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos_eje2">
                                            {{ $indicador->tendencia }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-xs-12 col-sm-12 col-12 hvr-grow">
                        <div class="card">
                            <div class="card-content card_indicador1">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos" style="">
                                            Línea Base {{ $indicador->linea_base }}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos_eje2">
                                            {{ $indicador->dato_linea }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-xs-12 col-sm-12 col-12 hvr-grow">
                        <div class="card">
                            <div class="card-content card_indicador1">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos" style="">
                                            Meta 2024
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos_eje2">
                                            {{ $indicador->meta_2024 }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                &nbsp;
            @endforeach

        </div>
        &nbsp;
        <div class="row">
            <p style="font-size:15px;">
                Nota: La información contenida en esta sección es responsabilidad de los municipios de acuerdo a
                lo establecido en sus Planes Municipales de Desarrollo.
            </p>
        </div>
    </div>


    </div>

@section('jss-final')

@endsection
@endsection

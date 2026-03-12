@extends('layouts.plantilla')
@section('title', 'Programas Derivados Especiales del Plan Estatal de Desarrollo 2024-2030')
@section('meta-description',
    'Sección de los Programas Derivados Especiales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
    Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del
    Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Programas Derivados Especiales del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el Seguimiento a
    la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Sección de los Programas Derivados Especiales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
    Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del
    Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    'Programas Derivados Especiales del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el Seguimiento a
    la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección de los Programas Derivados Especiales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
    Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del
    Estado de Puebla')
@section('content')
    <img src="" width="100%" class="px-0">
    <div class="row nav_derivados mx-0">
        <div class="col-md-3 nav_derivados1 ocultar_submenu">
            <a href="{{ url('/ped-programas/sectoriales') }}" class="dropbtn nav_eje_link">Sectoriales</a>
        </div>
        <div class="col-md-3 nav_derivados2 nav_derivados2_active ocultar_submenu">
            <a href="{{ url('/ped-programas/especiales') }}" class="dropbtn nav_eje_link">Especiales</a>
        </div>
        <div class="col-md-3 nav_derivados3 ocultar_submenu">
            <a href="{{ url('/ped-programas/institucionales') }}" class="dropbtn nav_eje_link">Institucionales</a>
        </div>
        <div class="col-md-3 nav_derivados4 ocultar_submenu">
            <a href="{{ url('/ped-programas/regionales') }}" class="dropbtn nav_eje_link">Regionales</a>
        </div>
    </div>
    <div class="row contenido">
        <div class="col-sm-12 col-md-3 offset-md-1 objetivo_especial">
            <img class="img-fluid" src="{{ asset('img/what2.png') }}" width="100%">
        </div>
        <div class="col-sm-12 col-md-7 objetivo">
            <p>Orientan el esfuerzo institucional para atender las temáticas prioritarias que se identifican a
                partir de las principales problemáticas y necesidades que van surgiendo en el estado, la
                particularidad de estos programas radica en la naturaleza de los temas, pues se consideran como
                transversales ya que tienen injerencia en todos los demás programas, en donde la sinergia
                interinstitucional es la base para para poder alcanzar un desarrollo equilibrado, inclusivo y
                sostenible.</p>
        </div>
    </div>
    <div class="container" style="margin-left: auto; margin-right: auto;">
        <div class="row">
            @foreach ($especiales as $especial)
                <div class="col-6 col-sm-3 hvr-grow my-3">
                    <a href="{{ url('/ped-programas/especiales/' . $especial->nombre) }}" style="text-decoration:none;">
                        <img class="img-fluid" src="{{ asset($especial->imagen) }}" />
                    </a>
                </div>
            @endforeach
        </div>
        &nbsp;
    </div>
@section('jss-final')

@endsection
@endsection

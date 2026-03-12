@extends('layouts.plantilla')
@section('title', 'Programas Derivados Regionales del Plan Estatal de Desarrollo 2024-2030')
@section('meta-description',
    'Sección de los Programas Derivados Regionales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
    Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Programas Derivados Regionales del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el Seguimiento a
    la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Sección de los Programas Derivados Regionales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
    Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    'Programas Derivados Regionales del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el Seguimiento a
    la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección de los Programas Derivados Regionales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
    Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/tab_puebla.css') }}">
@endsection
@section('jss-inicial')
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
@endsection
@section('content')
    <img src="" class="w-100 px-0">
    <div class="row nav_derivados ms-0">
        <div class="col-md-3 nav_derivados1 ocultar_submenu">
            <a href="{{ url('/ped-programas/sectoriales') }}" class="dropbtn nav_eje_link">Sectoriales</a>
        </div>
        <div class="col-md-3 nav_derivados2 ocultar_submenu">
            <a href="{{ url('/ped-programas/especiales') }}" class="dropbtn nav_eje_link">Especiales</a>
        </div>
        <div class="col-md-3 nav_derivados3 ocultar_submenu">
            <a href="{{ url('/ped-programas/institucionales') }}" class="dropbtn nav_eje_link">Institucionales</a>
        </div>
        <div class="col-md-3 nav_derivados4 nav_derivados4_active ocultar_submenu">
            <a href="{{ url('/ped-programas/regionales') }}" class="dropbtn nav_eje_link">Regionales</a>
        </div>
    </div>
    <div class="row contenido">
        <div class="col-sm-12 col-md-3 offset-md-1 objetivo_regional">
            <img class="img-fluid" src="{{ asset('img/what4.png') }}" width="100%">
        </div>
        <div class="col-sm-12 col-md-7 objetivo">
            <p>
                Establecer las políticas para potencializar las actividades de las regiones del Estado y tienen por objeto
                impulsar el desarrollo equilibrado de los Municipios de acuerdo a las características geográficas y
                económicas del territorio.
            </p>
        </div>
    </div>
    <div class="container" style="margin-left: auto; margin-right: auto;">
        <div class="row">
            @foreach ($regionales as $regional)
                <div class="col-6 col-sm-3 hvr-grow my-3">
                    <a href="{{ url('/ped-programas/regionales/' . Illuminate\Support\Str::slug($regional->nombre)) }}"
                        style="text-decoration:none;">
                        <img class="img-fluid" src="{{ asset($regional->imagen) }}" title="{{ $regional->nombre }}"
                            alt="{{ $regional->nombre }}" />
                    </a>
                </div>
            @endforeach
        </div>
        &nbsp;
    </div>
@section('jss-final')
@endsection
@endsection

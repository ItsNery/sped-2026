@extends('layouts.plantilla')
@section('title', 'Programas Derivados del Plan Estatal de Desarrollo 2019-2024')
@section('meta-description',
    'Sección de los Programas Derivados del Plan Estatal de Desarrollo 2019-2024 dentro del Sistema de
    Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Programas Derivados del Plan Estatal de Desarrollo 2019-2024 - Sistema de Información para el Seguimiento a la
    Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Sección de los Programas Derivados del Plan Estatal de Desarrollo 2019-2024 dentro del Sistema de
    Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    'Programas Derivados del Plan Estatal de Desarrollo 2019-2024 - Sistema de Información para el Seguimiento a la
    Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección de los Programas Derivados del Plan Estatal de Desarrollo 2019-2024 dentro del Sistema de
    Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('content')
    <img class="w-100 px-0" src="{{ asset('img/Banners/Banner_Derivados/Banners_SPED_Derivados.jpg') }}" title="Banner SPED"
        alt="Banner de los Programas Derivados">
    <div class="row nav_derivados ms-0">
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
        <div class="col-md-3 nav_derivados4 ocultar_submenu"
            onclick="location.href='{{ url('/ped-programas/regionales') }}';">
            <button class="dropbtn">Regionales</button>
        </div>
    </div>
    &nbsp;
    <div class="row contenido" style="margin:auto;">
        <div class="col-md-1"></div>
        <div class="col-md-5">
            <img class="w-100" src="{{ asset('img/esquema_pd.png') }}"
                alt="Esquema con la transversalidad de los programas derivados" title="Esquema Programas Derivados">

        </div>
        <div class="col-md-5">
            <p>El Sistema Estatal de Planeación Democrática se refiere al proceso de planeación y sus productos
                intermedios y finales, incluyendo los procedimientos técnicos y a la estructura orgánica de la APE
                para realizar y promover el proceso de planeación.</p>
            <p>Los Programas Derivados, que son los documentos de planeación que elaboran las dependencias y
                entidades, en los cuales se establecen objetivos específicos, estrategias y líneas de acción
                derivadas del PED.</p>
        </div>
        <div class="col-md-1"></div>
    </div>
@section('jss-final')

@endsection
@endsection

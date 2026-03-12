@extends('layouts.plantilla')
@section('title', 'Programas Derivados de la Modificación y Adecuación del Plan Estatal de Desarrollo 2019-2024')
@section('meta-description',
    'Página principal de los Programas Derivados de la Modificación y Adecuación del Plan
    Estatal de Desarrollo 2019-2024')
@section('canonical-url', url()->current())
@section('og-title',
    'Programas Derivados de la Modificación y Adecuación del Plan Estatal de Desarrollo 2019-2024 - Sistema de Información
    para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Página principal de los Programas Derivados de la Modificación y Adecuación del Plan Estatal
    de Desarrollo 2019-2024')
@section('og:url', url()->current())
@section('twitter-title',
    'Programas Derivados de la Modificación y Adecuación del Plan Estatal de Desarrollo 2019-2024 - Sistema de Información
    para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Página principal de los Programas Derivados de la Modificación y Adecuación del Plan
    Estatal de Desarrollo 2019-2024')
@section('css')
@endsection
@section('jss-inicial')
@endsection
@section('content')
    <img src="{{ asset('img/Banners/Banner_Derivados/Banner_PDs2.png') }}" width="100%" class=" px-0">
    <div class="row nav_derivados ms-0">
        <div class="col-md-3 nav_derivados1 ocultar_submenu"
            onclick="location.href='{{ url('/ped-programas/sectoriales') }}';">
            <button class="dropbtn">Sectoriales</button>
        </div>
        <div class="col-md-3 nav_derivados2 ocultar_submenu"
            onclick="location.href='{{ url('/ped-programas/especiales') }}';"><button class="dropbtn">Especiales</button>
        </div>
        <div class="col-md-3 nav_derivados3 ocultar_submenu"
            onclick="location.href='{{ url('/ped-programas/institucionales') }}';">
            <button class="dropbtn">Institucionales</button>
        </div>
        <div class="col-md-3 nav_derivados4 ocultar_submenu"
            onclick="location.href='{{ url('/ped-programas/regional') }}';">
            <button class="dropbtn">Regional</button>
        </div>
    </div>
    &nbsp;
    <div class="row contenido" style="margin:auto;">
        <div class="col-md-1"></div>
        <div class="col-md-6">
            <img src="{{ asset('img/proder2.png') }}" width="100%">

        </div>
        <div class="col-md-4">
            <p>El Sistema Estatal de Planeación Democrática engloba tanto el desarrollo del proceso de planeación
                como sus resultados intermedios y finales. Esto abarca los procedimientos técnicos y la estructura
                organizativa de la APE, encargada de llevar a cabo y fomentar el proceso de planificación.</p>
            <p>Los Programas Derivados, por su parte, constituyen los documentos de planificación generados por
                dependencias y entidades. En estos programas se delinean objetivos concretos, estrategias y líneas
                de acción que se derivan del Plan Estatal de Desarrollo.</p>
        </div>
        <div class="col-md-1"></div>
    </div>
@section('jss-final')

@endsection
@endsection

@extends('layouts.plantilla')
@section('title',
    'Programas Derivados Institucionales de la Modificación y Adecuación del Plan Estatal de Desarrollo
    2024-2030')
@section('meta-description',
    'Sección de los Programas Derivados Institucionales de la Modificación y Adecuación del Plan Estatal de Desarrollo
    2024-2030 dentro del Sistema de Información para el Seguimiento a la Planeación y Evaluación del
    Desarrollo
    del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Programas Derivados Institucionales de la Modificación y Adecuación del Plan Estatal de Desarrollo
    2024-2030 - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Sección de los Programas Derivados Institucionales de la Modificación y Adecuación del Plan Estatal de Desarrollo
    2024-2030 dentro del Sistema de Información para el Seguimiento a la Planeación y Evaluación del
    Desarrollo
    del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    'Programas Derivados Institucionales de la Modificación y Adecuación del Plan Estatal de Desarrollo
    2024-2030 - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección de los Programas Derivados Institucionales de la Modificación y Adecuación del Plan Estatal de Desarrollo
    2024-2030 dentro del Sistema de Información para el Seguimiento a la Planeación y Evaluación del
    Desarrollo
    del Estado de Puebla')
@section('css')
@endsection
@section('jss-inicial')
@endsection
@section('content')
    <img class="w-100" src="" alt="Banner de los Programas Institucionales" title="Programas Institucionales">
    <div class="row nav_derivados mx-0">
        <div class="col-md-3 nav_derivados1 ocultar_submenu">
            <a href="{{ url('/ped-programas/sectoriales') }}" class="dropbtn nav_eje_link">Sectoriales</a>
        </div>
        <div class="col-md-3 nav_derivados2  ocultar_submenu">
            <a href="{{ url('/ped-programas/especiales') }}" class="dropbtn nav_eje_link">Especiales</a>
        </div>
        <div class="col-md-3 nav_derivados3 nav_derivados3_active ocultar_submenu">
            <a href="{{ url('/ped-programas/institucionales') }}" class="dropbtn nav_eje_link">Institucionales</a>
        </div>
        <div class="col-md-3 nav_derivados4 ocultar_submenu">
            <a href="{{ url('/ped-programas/regionales') }}" class="dropbtn nav_eje_link">Regionales</a>
        </div>
    </div>
    &nbsp;
    <div class="row contenido" style="margin-left: auto; margin-right: auto;">
        <div class="col-md-1"></div>
        <div class="col-md-3 objetivo_institucional">
            <img class="img-fluid w-100" src="{{ asset('img/what3.png') }}" title="¿Qué son los Programas Institucionales?"
                alt="Imagen con texto ¿Qué son los Programas Institucionales?">
        </div>
        <div class="col-md-7 objetivo">
            <p>Facilitan la identificación, organización y orientación precisa de los instrumentos de política
                disponibles para las Instituciones de la Administración Pública Estatal. De esta manera, en
                consonancia con sus atribuciones y funciones dentro de su ámbito competencial, contribuyen al logro
                de los objetivos y metas establecidos en el Plan Estatal de Desarrollo, así como en sus respectivos
                sectores.</p>
        </div>
        <div class="col-md-1"></div>
    </div>
    &nbsp;
    <div class="container" style="margin-left: auto; margin-right: auto;">
        <div class="row">
            @foreach ($programas as $programa)
                <div class="col-md-3 hvr-grow my-3">
                    <a href="{{ url('/ped-programas/institucionales/' . $programa->nombre) }}"
                        style="text-decoration:none;">
                        <img class="img-fluid" src="{{ asset($programa->imagen) }}"
                            alt="Icono de lal programa derivado institucional {{ $programa->nombre }}"
                            title="{{ $programa->nombre }}">
                    </a>
                </div>
            @endforeach
        </div>
        &nbsp;
    </div>
@section('jss-final')

@endsection
@endsection

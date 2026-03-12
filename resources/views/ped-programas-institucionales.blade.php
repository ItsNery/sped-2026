@extends('layouts.plantilla')
@section('title', 'Programas Derivados Institucionales del Plan Estatal de Desarrollo 2024-2030')
@section('meta-description',
'Sección de los Programas Derivados Institucionales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del
Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
'Programas Derivados Institucionales del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el
Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description',
'Sección de los Programas Derivados Institucionales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del
Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
'Programas Derivados Institucionales del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el
Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description',
'Sección de los Programas Derivados Institucionales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del
Estado de Puebla')
@section('css')
@endsection
@section('jss-inicial')
@endsection
@section('content')
<img src="" alt="Banner de los Programas Derivados Institucionales"
    title="Banner de los Programas Derivados Institucionales" width="100%" class="px-0">
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
<div class="row contenido">
    <div class="col-sm-12 col-md-3 offset-md-1 objetivo_institucional">
        <img class="img-fluid" src="{{ asset('img/what3.png') }}" width="100%">
    </div>
    <div class="col-sm-12 col-md-7 objetivo">
        <p>
            Identificar, organizar y orientar los instrumentos de política con los que cuentan las Dependencias y
            Entidades de la Administración Pública Estatal, para fortalecer sus capacidades y de acuerdo a sus
            atribuciones y funciones, coadyuvar al cumplimiento de los objetivos y metas del Plan Estatal de Desarrollo.
        </p>
    </div>
</div>
<div class="container" style="margin-left: auto; margin-right: auto;">
    <div class="row">
        @foreach ($programas as $programa)
        <div class="col-md-3 hvr-grow my-3">
            <a href="{{ url('/ped-programas/institucionales/' . Illuminate\Support\Str::slug($programa->nombre)) }}"
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
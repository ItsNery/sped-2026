@extends('layouts.plantilla')
@section('title',
    'Programas Derivados Sectoriales de la Modificación y Adecuación del Plan Estatal de Desarrollo
    2019-2024')
@section('meta-description',
    'Sección de los Programas Derivados Sectoriales de la Modificación y Adecuación del Plan Estatal de Desarrollo
    2019-2024 del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de
    Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Programas Derivados Sectoriales de la Modificación y Adecuación del Plan Estatal de Desarrollo - Sistema de Información
    para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('og-description',
    'Sección de los Programas Derivados Sectoriales de la Modificación y Adecuación del Plan Estatal de Desarrollo
    2019-2024 del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de
    Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    ' 2019-2024 - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección de los Programas Derivados Sectoriales de la Modificación y Adecuación del Plan Estatal de Desarrollo
    2019-2024 del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de
    Puebla')
@section('content')
    <img src="{{ asset('img/Banners/Banner_Derivados/Banner_Programas_Sectoriales2.png') }}" class="w-100 px-0">
    <div class="row nav_derivados mx-0">
        <div class="col-md-3 nav_derivados1_active ocultar_submenu"
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
            onclick="location.href='{{ url('/ped-programas/regional') }}';">
            <button class="dropbtn">Regional</button>
        </div>
    </div>
    <div class="row contenido">
        <div class="col-sm-12 col-md-3 offset-md-1 objetivo_sectorial">
            <img class="img-fluid" src="{{ asset('img/what1.png') }}" width="100%" />
        </div>
        <div class="col-sm-12 col-md-7 objetivo">
            <p>Representan una guía estratégica para el avance de los sectores abordados por la Administración
                Pública Estatal. En este contexto, las Dependencias líderes de cada sector asumen la responsabilidad
                de coordinar y dirigir los esfuerzos institucionales, asegurando un desarrollo adecuado en sus áreas
                respectivas.</p>
        </div>
    </div>
    <div class="container" style="margin-left: auto; margin-right: auto;">
        <div class="row">
            @foreach ($sectoriales as $sectorial)
                <div class="col-6 col-md-4 col-lg-3 hvr-grow my-3">
                    <a href="{{ url('/ped-programas/sectoriales/' . $sectorial->nombre) }}"
                        style="text-decoration:none;">
                        <img class="img-fluid" src="{{ asset($sectorial->imagen) }}" title="{{ $sectorial->nombre }}"
                            alt="Icono del programa derivado sectorial {{ $sectorial->nombre }}">
                    </a>
                </div>
            @endforeach
        </div>
        &nbsp;
    </div>
@endsection

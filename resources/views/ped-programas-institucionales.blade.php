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
@include('partials.nav-unificada', [
'tipoNav' => 'derivados',
'itemActivo' => 'App\Models\CatProgramaDerivadoInstitucional',
'colorTema' => '#B72D33'
])
<div class="row contenido mb-5 mt-3">
    <div class="col-sm-12 col-md-3 offset-md-1 d-flex justify-content-center align-items-center">
        <img class="img-fluid" src="{{ asset('img/what3.png') }}" style="max-height: 120px;">
    </div>
    <div class="col-sm-12 col-md-7 d-flex align-items-center">
        <p class="fs-4 text-muted border-start ps-4" style="border-width: 4px !important; border-color: #B72D33 !important;">
            Son instrumentos que identifican, organizan y orientan los instrumentos de política con los que cuentan las Dependencias y
            Entidades de la Administración Pública Estatal, para fortalecer sus capacidades y de acuerdo a sus
            atribuciones y funciones, coadyuvar al cumplimiento de los objetivos y metas del Plan Estatal de Desarrollo.
        </p>
    </div>
</div>
<div class="container mb-5">
    <div class="row g-4 justify-content-center">
        @foreach ($programas as $programa)
        <div class="col-6 col-md-4 col-lg-3 hvr-grow">
            <a href="{{ url('/ped-programas/institucionales/' . Illuminate\Support\Str::slug($programa->nombre)) }}"
                class="card shadow-sm border-0 rounded-4 overflow-hidden text-decoration-none h-100 d-flex flex-column">

                <img class="card-img-top w-100 portadas-derivados"
                    src="{{ asset($programa->imagen) }}"
                    title="{{ $programa->nombre }}"
                    alt="Portada de {{ $programa->nombre }}">
            </a>
        </div>
        @endforeach
    </div>
</div>
@section('jss-final')
@endsection
@endsection
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
@include('partials.nav-unificada', [
'tipoNav' => 'derivados',
'itemActivo' => 'App\Models\CatProgramaDerivadoRegional',
'colorTema' => '#512E6A'
])
<div class="row contenido mb-5 mt-3">
    <div class="col-sm-12 col-md-3 offset-md-1 d-flex justify-content-center align-items-center">
        <img class="img-fluid" src="{{ asset('img/what4.png') }}" style="max-height: 120px;">
    </div>
    <div class="col-sm-12 col-md-7 d-flex align-items-center">
        <p class="fs-4 text-muted border-start ps-4" style="border-width: 4px !important; border-color: #512E6A !important;">
            Son instrumentos que establecen las políticas para potencializar las actividades de las regiones del estado y tienen por objeto
            impulsar el desarrollo equilibrado de los municipios de acuerdo a las características geográficas y
            económicas del territorio.
        </p>
    </div>
</div>
<div class="container mb-5">
    <div class="row g-4 justify-content-center">
        @foreach ($regionales as $regional)
        <div class="col-6 col-md-4 col-lg-3 hvr-grow">
            <a href="{{ url('/ped-programas/regionales/' . Illuminate\Support\Str::slug($regional->nombre)) }}"
                class="card shadow-sm border-0 rounded-4 overflow-hidden text-decoration-none h-100 d-flex flex-column">

                <img class="card-img-top w-100 portadas-derivados"
                    src="{{ asset($regional->imagen) }}"
                    title="{{ $regional->nombre }}"
                    alt="Portada de {{ $regional->nombre }}">
            </a>
        </div>
        @endforeach
    </div>
</div>
@section('jss-final')
@endsection
@endsection
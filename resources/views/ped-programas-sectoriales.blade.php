@extends('layouts.plantilla')
@section('title', 'Programas Derivados Sectoriales del Plan Estatal de Desarrollo 2024-2030')
@section('meta-description',
'Sección de los Programas Derivados Sectoriales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
'Programas Derivados Sectoriales del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el Seguimiento a
la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description',
'Sección de los Programas Derivados Sectoriales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
'Programas Derivados Sectoriales del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el Seguimiento a
la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description',
'Sección de los Programas Derivados Sectoriales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('content')
@include('partials.nav-unificada', [
'tipoNav' => 'derivados',
'itemActivo' => 'App\Models\CatProgramaDerivadoSectorial',
'colorTema' => '#BF9A24'
])
<div class="row contenido mb-5 mt-3">
    <div class="col-sm-12 col-md-3 offset-md-1 d-flex justify-content-center align-items-center">
        <img class="img-fluid" src="{{ asset('img/what1.png') }}" style="max-height: 120px;">
    </div>
    <div class="col-sm-12 col-md-7 d-flex align-items-center">
        <p class="fs-4 text-muted border-start ps-4" style="border-width: 4px !important; border-color: #BF9A24 !important;">
            Son instrumentos que constituyen una expresión especializada de fines comunes para la atención de los sectores de la Administración Pública Estatal.
        </p>
    </div>
</div>
<div class="container mb-5">
    <div class="row g-4 justify-content-center">
        @foreach ($sectoriales as $sectorial)
        <div class="col-6 col-md-4 col-lg-3 hvr-grow">
            <a href="{{ url('/ped-programas/sectoriales/' . Illuminate\Support\Str::slug($sectorial->nombre)) }}"
                class="card shadow-sm border-0 rounded-4 overflow-hidden text-decoration-none h-100 d-flex flex-column">
                <img class="card-img-top w-100 portadas-derivados"
                    src="{{ asset($sectorial->imagen) }}"
                    title="{{ $sectorial->nombre }}"
                    alt="Portada de {{ $sectorial->nombre }}">
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection
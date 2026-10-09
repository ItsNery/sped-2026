@extends('layouts.plantilla')
@section('title', 'Planes Municipales de Desarrollo')
@section('meta-description',
'Sección de los Planes Municipales de Desarrollo dentro del Sistema de Información para el
Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
'Planes Municipales de Desarrollo - Sistema de Información para el Seguimiento a la Planeación y Evaluación del
Desarrollo
del Estado de Puebla')
@section('og-description',
'Sección de los Planes Municipales de Desarrollo dentro del Sistema de Información para el
Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
'Planes Municipales de Desarrollo - Sistema de Información para el Seguimiento a la Planeación y Evaluación del
Desarrollo
del Estado de Puebla')
@section('twitter-description',
'Sección de los Planes Municipales de Desarrollo dentro del Sistema de Información para el
Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('css')
    <link href="{{ asset('css/municipales.css') }}" rel="stylesheet">
@endsection
@section('jss-inicial')
@endsection
@section('content')
<div class="municipales">
    <section class="municipales__hero">
        <div class="municipales__hero-container">
            <div class="municipales__hero-content">
                <span class="municipales__hero-tag">Planeación municipal</span>
                <h1 class="municipales__hero-title">Planes Municipales de Desarrollo</h1>
                <p class="municipales__hero-desc">Consulta el seguimiento de los indicadores de los municipios que participan en el Sistema de Información.</p>
            </div>
        </div>
    </section>
    <main class="municipales__main">
        <div class="container municipales__container">
            <p class="municipales__intro">Selecciona un municipio para consultar sus indicadores y documentos relacionados.</p>
            <div class="municipales__grid">
                @foreach ($municipiosConvenio as $municipio)
                    <a class="municipales__card" href="{{ route('pm.show', ['municipioConvenio' => $municipio]) }}">
                        <div class="municipales__card-icon">
                            <img src="{{ asset($municipio->icono) }}" alt="{{ $municipio->municipio->nombre ?? 'Municipio' }}">
                        </div>
                        <div class="municipales__card-body">
                            <h2>{{ $municipio->municipio->nombre ?? 'Municipio' }}</h2>
                            <span>Consultar indicadores <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </a>
                @endforeach
            </div>
            <p class="municipales__note"><i class="fas fa-circle-info me-2"></i>La información contenida en esta sección es responsabilidad de los municipios de acuerdo con lo establecido en sus Planes Municipales de Desarrollo.</p>
        </div>
    </main>
</div>
@section('jss-final')

@endsection
@endsection

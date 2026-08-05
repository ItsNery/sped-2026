@extends('layouts.plantilla')
@section('title', 'Indicadores del Plan Municipal de Desarrollo de ' . $municipio->municipio->nombre)
@section('meta-description',
    'Sección dedicada al seguimiento a los Indicadores del Plan Municipal de Desarollo de ' .
    $municipio->municipio->nombre .
    ' dentro del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Indicadores del Plan Municipal de Desarrollo de ' .
    $municipio->municipio->nombre .
    ' - Sistema de Información para el Seguimiento a la Planeación y
    Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Sección dedicada al seguimiento a los Indicadores del Plan Municipal de Desarollo de ' .
    $municipio->municipio->nombre .
    ' dentro del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    'Indicadores del Plan Municipal de Desarrollo de ' .
    $municipio->municipio->nombre .
    ' - Sistema de Información para el Seguimiento a la Planeación y
    Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección dedicada al seguimiento a los Indicadores del Plan Municipal de Desarollo de ' .
    $municipio->municipio->nombre .
    ' dentro del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('css')
    <link href="{{ asset('css/municipales.css') }}" rel="stylesheet">
@endsection
@section('jss-inicial')
@endsection
@section('content')
<div class="municipales municipales-detalle">
    <section class="municipales__hero municipales__hero--municipio" style="background-image: linear-gradient(rgba(0, 0, 0, 0.72), rgba(0, 0, 0, 0.72)), url('{{ asset($municipio->banner) }}');">
        <div class="municipales__hero-container">
            <div class="municipales__hero-content">
                <span class="municipales__hero-tag">Plan Municipal de Desarrollo</span>
                <h1 class="municipales__hero-title">{{ $municipio->municipio->nombre }}</h1>
                <p class="municipales__hero-desc">Seguimiento a los indicadores del Plan Municipal de Desarrollo.</p>
            </div>
        </div>
    </section>
    <main class="municipales__main">
        <div class="municipales-detalle__container">
            <section class="municipales-detalle__overview" aria-labelledby="municipal-objetivo-title">
                <div class="municipales-detalle__municipio-icon">
                    <img src="{{ asset($municipio->icono) }}" alt="{{ $municipio->municipio->nombre }}">
                </div>
                <div class="municipales-detalle__overview-content">
                    <span class="municipales__hero-tag">Seguimiento municipal</span>
                    <h2 id="municipal-objetivo-title">Objetivo</h2>
                    <p>{{ $municipio->objetivo ?? 'No disponible' }}</p>
                    <a target="_blank" href="{{ asset($municipio->convenio) }}" class="municipales-detalle__document">
                        <i class="fas fa-file-arrow-down"></i> Consultar convenio
                    </a>
                </div>
            </section>

            <section class="municipales-detalle__indicators" aria-labelledby="municipal-indicators-title">
                <div class="municipales-detalle__section-heading">
                    <div>
                        <span class="municipales__hero-tag">Datos disponibles</span>
                        <h2 id="municipal-indicators-title">{{ $totalIndicadores }} indicadores</h2>
                    </div>
                    <span class="municipales-detalle__section-icon"><i class="fas fa-chart-line"></i></span>
                </div>

                <div class="municipales-detalle__list">
                    @foreach ($indicadores as $indicador)
                        <article class="municipales-indicador-card">
                            <a href="{{ route('mostrarFicha', ['indicador' => $indicador->slug]) }}" class="municipales-indicador-card__main">
                                <div class="municipales-indicador-card__heading">
                                    <h3>{{ $indicador->indicador }}</h3>
                                </div>
                                <div class="municipales-indicador-card__result">
                                    <span>Resultado {{ $indicador->aniomasreciente }}</span>
                                    <strong>{{ $indicador->datoaniomasreciente ?? 'N/D' }}</strong>
                                </div>
                                <i class="fas fa-arrow-up-right-from-square municipales-indicador-card__arrow"></i>
                            </a>

                            <div class="municipales-indicador-card__ods">
                                @foreach ($indicador->ods->unique('id') as $ods)
                                    <img src="{{ asset('/img/Icons_ODS/' . $ods->id . '.png') }}"
                                        alt="ODS {{ $ods->id }}">
                                @endforeach
                            </div>

                            <div class="municipales-indicador-card__metrics">
                                <div><span>Unidad de medida</span><strong>{{ $indicador->unidad_medida ?? 'N/D' }}</strong></div>
                                <div><span>Tendencia</span><strong>{{ $indicador->tendencia ?? 'N/D' }}</strong></div>
                                <div><span>Línea base {{ $indicador->linea_base }}</span><strong>{{ $indicador->dato_linea ?? 'N/D' }}</strong></div>
                                <div><span>Meta 2027</span><strong>{{ $indicador->meta_2024 ?? 'N/D' }}</strong></div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <p class="municipales__note">
                    <i class="fas fa-circle-info"></i>
                    La información contenida en esta sección es responsabilidad de los municipios, de acuerdo con lo establecido en sus Planes Municipales de Desarrollo.
                </p>
            </section>
        </div>
    </main>
</div>

@section('jss-final')

@endsection
@endsection

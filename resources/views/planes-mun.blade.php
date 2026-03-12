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
@endsection
@section('jss-inicial')
@endsection
@section('content')
<img class="w-100" src="{{ asset('img/Banners/Banner_Municipales/bannerPMD.jpg') }}"
    title="Indicadores Planes Municipales" alt="Imagen con texto que dice Indicadores de los Planes Municipales">
<div class="container" style="margin-left: auto; margin-right: auto;">
    <div class="row">
        @foreach ($municipiosConvenio as $municipio)
        <div class="col-6 col-md-3 col-lg-3 hvr-grow" style="margin-bottom:30px; margin-top:30px;">
            <div class="avatar">
                <div class="avatar__content">
                    <a href="{{ route('pm.show', ['municipioConvenio' => $municipio]) }}" style="text-decoration:none;">
                        <img class="profile-pic" src="{{ $municipio->icono }}" alt="Profile Pic">
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="row">
        <p class="p-planes-mun">
            Nota: La información contenida en esta sección es responsabilidad de los municipios de acuerdo a lo
            establecido en sus Planes Municipales de Desarrollo.
        </p>
    </div>
</div>
@section('jss-final')

@endsection
@endsection
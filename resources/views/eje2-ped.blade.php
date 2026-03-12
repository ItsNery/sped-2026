@extends('layouts.plantilla')
@section('title', 'Eje 2 del Plan Estatal de Desarrollo 2024-2030')
@section('meta-description',
'Sección dedicada al Eje 2 del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema
de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
'Eje 2 del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el
Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description',
'Sección dedicada al Eje 2 del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema
de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
'Eje 2 del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el
Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description',
'Sección dedicada al Eje 2 del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema
de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('jss-inicial')
@endsection
@section('content')
    @include('partials.contenido-ejes', [
        'numEje' => 2,
        'textoEnfoque' => 'Para que Puebla crezca con equidad y sostenibilidad,
            apostaremos por la generación de empleos bien remunerados, el
            impulso a los emprendedores y el fortalecimiento de nuestros
            sectores estratégicos para dinamizar la economía local.'
    ])
@endsection
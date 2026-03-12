@extends('layouts.plantilla')
@section('title', 'Eje Transversal del Plan Estatal de Desarrollo 2024-2030')
@section('meta-description',
'Sección dedicada al Eje 5 del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema
de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
'Eje 5 del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el
Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description',
'Sección dedicada al Eje 5 del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema
de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
'Eje 5 del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el
Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description',
'Sección dedicada al Eje 5 del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema
de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('content')
    @include('partials.contenido-ejes', [
        'numEje' => 6,
        'textoEnfoque' => 'Abarca temas que no se circunscriben a un problema en
            concreto, sino que atañe a toda la administración pública. Su
            carácter interdisciplinario se verá reflejado en todos los
            ejes rectores del desarrollo.'
    ])
@endsection
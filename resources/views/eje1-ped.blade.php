{{-- eje1-ped.blade.php --}}
@extends('layouts.plantilla')
@section('title', 'Eje 1 del Plan Estatal de Desarrollo 2024-2030')
@section('meta-description',
'Sección dedicada al Eje 1 del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema
de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
'Eje 1 del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el Seguimiento a la Planeación y
Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description',
'Sección dedicada al Eje 1 del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema
de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
'Eje 1 del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el Seguimiento a la Planeación y
Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description',
'Sección dedicada al Eje 1 del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema
de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('content')
    @include('partials.contenido-ejes', [
        'numEje' => 1,
        'textoEnfoque' => 'La prioridad es la gente, trabajaremos para garantizar su salud, educación, vivienda y un desarrollo social equitativo que permita a cada persona vivir con dignidad y alcanzar sus sueños.'
    ])
@endsection

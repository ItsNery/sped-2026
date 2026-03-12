@extends('layouts.plantilla')
@section('title', 'Eje 5 del Plan Estatal de Desarrollo 2024-2030')
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
        'numEje' => 5,
        'textoEnfoque' => 'Seremos un gobierno abierto, inclusivo y eficiente,
            comprometido con la rendición de cuentas y capaz de responder
            a las exigencias de la ciudadanía. Fomentaremos la confianza
            en las autoridades a través de la transparencia, la honestidad
            y un trabajo constante y responsable.'
    ])
@endsection
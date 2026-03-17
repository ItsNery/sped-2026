@extends('layouts.plantilla')
@section('title', 'Programa Derivado Especial ' . $programa->nombre)
@section('meta-description', $descripcion)
@section('canonical-url', url()->current())
@section('og-title',
' Programa Derivado Especial ' .
$programa->nombre .
' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description', $descripcion)
@section('og:url', url()->current())
@section('twitter-title',
' Programa Derivado Especial ' .
$programa->nombre .
' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description', $descripcion)

@section('content')
    @include('partials.programa-derivado-contenido', [
        'itemActivoNav' => 'App\Models\CatProgramaDerivadoEspecial',
        'tituloBadge' => 'Programa Especial',
        // Variables pre-existentes necesarias para el partial (programa, indicadores, imagen, descripcion, avancePrograma)
    ])
@endsection
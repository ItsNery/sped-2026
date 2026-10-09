@extends('layouts.plantilla')
@section('title', 'Programa Derivado Regional ' . $programa->nombre)
@section('meta-description', $programaData->descripcion)
@section('canonical-url', url()->current())
@section('og-title',
'Programa Derivado Regional ' .
$programa->nombre .
' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description', $programaData->descripcion)
@section('og:url', url()->current())
@section('twitter-title',
$programa->nombre .
' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description', $programaData->descripcion)
@section('jss-inicial')
@endsection
@section('css')
@endsection
@section('content')
    @include('partials.programa-derivado-contenido', [
        'itemActivoNav' => 'App\Models\CatProgramaDerivadoRegional',
        'tituloBadge' => 'Programa Regional',
        // Variables pre-existentes disponibles en el controlador para la vista
    ])
@endsection


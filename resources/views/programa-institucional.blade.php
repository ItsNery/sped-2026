@extends('layouts.plantilla')
@section('title', 'Programa Derivado Institucional ' . $programa->nombre)
@section('meta-description', $programaData->descripcion)
@section('canonical-url', url()->current())
@section('og-title',
'Programa Derivado Institucional ' .
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
@section('css')
@endsection
@section('jss-inicial')
@endsection

@section('content')
    @include('partials.programa-derivado-contenido', [
        'itemActivoNav' => 'App\Models\CatProgramaDerivadoInstitucional',
        'tituloBadge' => 'Programa Institucional',
        // Variables pre-existentes disponibles en el controlador para la vista (programa, programaData, indicadores, imagen, avancePrograma)
    ])
@endsection
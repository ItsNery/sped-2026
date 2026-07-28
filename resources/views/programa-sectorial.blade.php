@extends('layouts.plantilla')
@section('title', 'Programa Derivado Sectorial ' . $programa->nombre)
@section('meta-description', $descripcion)
@section('canonical-url', url()->current())
@section('og-title',
' Programa Derivado Sectorial ' .
$programa->nombre .
' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description', $descripcion)
@section('og:url', url()->current())
@section('twitter-title',
' Programa Derivado Sectorial ' .
$programa->nombre .
' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description', $descripcion)
@section('css')
@endsection
@section('content')
@include('partials.programa-derivado-contenido', [
'itemActivoNav' => 'App\Models\CatProgramaDerivadoSectorial',
'tituloBadge' => 'Programa Sectorial',
])
@endsection

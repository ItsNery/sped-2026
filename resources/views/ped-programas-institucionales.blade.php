@extends('layouts.plantilla')

@section('title', 'Programas Derivados Institucionales del Plan Estatal de Desarrollo 2024-2030')
@section('meta-description', 'Consulta los Programas Derivados Institucionales del Plan Estatal de Desarrollo 2024-2030 del Estado de Puebla.')
@section('canonical-url', url()->current())
@section('css')
@endsection

@section('content')
    @include('partials.programas-derivados-listado', [
        'programas' => $programas,
        'grupos' => $grupos,
        'tipoNombre' => 'Programas institucionales',
        'tipoSlug' => 'institucionales',
        'modeloActivo' => 'App\\Models\\CatProgramaDerivadoInstitucional',
        'colorTema' => '#B72D33',
        'descripcion' => 'Instrumentos que organizan y orientan las políticas de las Dependencias y Entidades de la Administración Pública Estatal para contribuir al cumplimiento del PED.',
    ])
@endsection

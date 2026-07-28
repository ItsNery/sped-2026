@extends('layouts.plantilla')

@section('title', 'Programas Derivados Especiales del Plan Estatal de Desarrollo 2024-2030')
@section('meta-description', 'Consulta los Programas Derivados Especiales del Plan Estatal de Desarrollo 2024-2030 del Estado de Puebla.')
@section('canonical-url', url()->current())
@section('css')
@endsection

@section('content')
    @include('partials.programas-derivados-listado', [
        'programas' => $especiales,
        'tipoNombre' => 'Programas derivados especiales',
        'tipoSlug' => 'especiales',
        'modeloActivo' => 'App\\Models\\CatProgramaDerivadoEspecial',
        'colorTema' => '#8BA59D',
        'descripcion' => 'Instrumentos que atienden los objetivos prioritarios para el desarrollo del Estado de Puebla.',
    ])
@endsection

@extends('layouts.plantilla')

@section('title', 'Programas Derivados Regionales del Plan Estatal de Desarrollo 2024-2030')
@section('meta-description', 'Consulta los Programas Derivados Regionales del Plan Estatal de Desarrollo 2024-2030 del Estado de Puebla.')
@section('canonical-url', url()->current())
@section('css')
@endsection

@section('content')
    @include('partials.programas-derivados-listado', [
        'programas' => $regionales,
        'tipoNombre' => 'Programas derivados regionales',
        'tipoSlug' => 'regionales',
        'modeloActivo' => 'App\\Models\\CatProgramaDerivadoRegional',
        'colorTema' => '#512E6A',
        'descripcion' => 'Instrumentos que establecen políticas para potencializar las actividades de las regiones e impulsar el desarrollo equilibrado de los municipios.',
    ])
@endsection

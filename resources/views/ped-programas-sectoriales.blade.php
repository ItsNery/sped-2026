@extends('layouts.plantilla')

@section('title', 'Programas Derivados Sectoriales del Plan Estatal de Desarrollo 2024-2030')
@section('meta-description', 'Consulta los Programas Derivados Sectoriales del Plan Estatal de Desarrollo 2024-2030 del Estado de Puebla.')
@section('canonical-url', url()->current())
@section('css')
@endsection

@section('content')
    @include('partials.programas-derivados-listado', [
        'programas' => $sectoriales,
        'tipoNombre' => 'Programas derivados sectoriales',
        'tipoSlug' => 'sectoriales',
        'modeloActivo' => 'App\\Models\\CatProgramaDerivadoSectorial',
        'colorTema' => '#BF9A24',
        'descripcion' => 'Instrumentos que constituyen una expresión especializada de fines comunes para la atención de los sectores de la Administración Pública Estatal.',
    ])
@endsection

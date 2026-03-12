@extends('layouts.plantilla')
@section('title', 'Programa Derivado Sectorial ' . $programa->nombre)
@section('meta-description',
'Sección de los Programas Derivados Sectoriales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
'Programas Derivados Sectoriales del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el Seguimiento a
la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description',
'Sección de los Programas Derivados Sectoriales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
'Programas Derivados Sectoriales del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el Seguimiento a
la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description',
'Sección de los Programas Derivados Sectoriales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('css')
@endsection
@section('jss-inicial')
@endsection
@section('content')
<img src="" class="w-100 px-0">
<div class="row nav_derivados mx-0">
    <div class="col-md-3 nav_derivados1 nav_derivados1_active ocultar_submenu">
        <a href="{{ url('/ped-programas/sectoriales') }}" class="dropbtn nav_eje_link">Sectoriales</a>
    </div>
    <div class="col-md-3 nav_derivados2 ocultar_submenu">
        <a href="{{ url('/ped-programas/especiales') }}" class="dropbtn nav_eje_link">Especiales</a>
    </div>
    <div class="col-md-3 nav_derivados3 ocultar_submenu">
        <a href="{{ url('/ped-programas/institucionales') }}" class="dropbtn nav_eje_link">Institucionales</a>
    </div>
    <div class="col-md-3 nav_derivados4 ocultar_submenu">
        <a href="{{ url('/ped-programas/regionales') }}" class="dropbtn nav_eje_link">Regional</a>
    </div>
</div>
<div class="row contenido">
    <div class="col-sm-12 col-md-3 offset-md-1 objetivo_sectorial">
        <img class="img-fluid" src="{{ asset($programa->imagen) }}" title="Imagen de {{ $programa->nombre }}"
            alt="Imagen de {{ $programa->nombre }}">
    </div>
    <div class="col-sm-12 col-md-7 objetivo">
        <h2 style="color:{{ $programa->color }}">Objetivo</h2>
        <p>{{ $programa->descripcion }}</p>
        @if ($programa->documento)
        <a target="_blank" href="{{ $programa->documento }}" class="a-simple">
            @include('layouts.boton-documento')
        </a>
        @else
        <p>Documento no disponible</p>
        @endif
    </div>
</div>

{{-- Design homologado con eje1-ped.blade.php --}}
<div class="row ficha" style="background-color:{{ $programa->color }}">
    <div class="container">
        <h2 class="text-white mb-4">{{ $programa->indicadores->count() }} INDICADORES</h2>

        @forelse ($programa->indicadores as $indicador)

        <div class="row mx-auto">
            <div class="col-12">
                <div class="card overflow-hidden mb-3">
                    <div class="card-content card_indicador">
                        <div class="card-body">
                            <a href="{{ route('ficha-tecnica.show', $indicador) }}"
                                class="text-decoration-none">
                                <div class="row">
                                    <div class="col-12 col-md-10">
                                        <div class="titulo">
                                            {{ $indicador->nombre }}
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-2 text-md-end">
                                        <div class="ultimo" style="color:{{ $programa->color }}">
                                            Resultado: {{ $indicador->anio_reciente_validado ?? 'Sin datos' }}
                                        </div>
                                        <div class="datos_eje1" style="color:{{ $programa->color }}">
                                            {{-- number_format ya se aplicó en el controlador si era numérico, o se devolvió string --}}
                                            {{ $indicador->dato_reciente_validado ?? 'Sin datos' }}
                                        </div>
                                    </div>

                                    {{-- ODS y Semáforo --}}
                                    @if ($indicador->ods->isNotEmpty() || $indicador->semaforizacion_validada)
                                    {{-- Condición más simple --}}
                                    <div class="col-md-8 ods mt-3"> {{-- Ajustado col para que ocupe más si semáforo no está o viceversa --}}
                                        @if ($indicador->ods->isNotEmpty())
                                        <div class="d-flex flex-wrap align-items-center ods">
                                            @foreach ($indicador->ods->unique('id') as $ods_item)
                                            {{-- Cambiado $ods a $ods_item para evitar conflicto de nombres si tienes una variable $ods fuera --}}
                                            <img src="{{ asset('/img/Icons_ODS/' . $ods_item->id . '.png') }}"
                                                alt="Imagen de ODS {{ $ods_item->id }}"
                                                class="hvr-wobble-top img-fluid rounded me-1 mb-1"
                                                style="height: 24px;">
                                            {{-- Estilo directo para tamaño y margen --}}
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                    <div class="col-md-4 text-md-end mt-3"> {{-- Contenedor para la semaforización --}}
                                        @if ($indicador->semaforizacion_validada)
                                        @php
                                        $semaforoTexto = $indicador->semaforizacion_validada;
                                        $semaforoBadgeClase = 'bg-secondary'; // Clase de color del badge por defecto
                                        $semaforoExplicacion =
                                        'No hay información detallada para este estado.';

                                        switch (strtolower($indicador->semaforizacion_validada)) {
                                        case 'excedido':
                                        $semaforoBadgeClase = 'bg-primary'; // Azul Bootstrap
                                        $semaforoExplicacion =
                                        'El indicador ha superado la meta establecida de forma significativa.';
                                        break;
                                        case 'aceptable':
                                        $semaforoBadgeClase = 'bg-success'; // Verde Bootstrap
                                        $semaforoExplicacion =
                                        'El indicador ha alcanzado o está muy cerca de la meta (91% o más).';
                                        break;
                                        case 'moderado':
                                        $semaforoBadgeClase = 'bg-warning text-dark'; // Amarillo Bootstrap (text-dark para contraste)
                                        $semaforoExplicacion =
                                        'El indicador muestra un avance parcial hacia la meta (entre 71% y 90%).';
                                        break;
                                        case 'insuficiente':
                                        $semaforoBadgeClase = 'bg-danger'; // Rojo Bootstrap
                                        $semaforoExplicacion =
                                        'El indicador está considerablemente por debajo de la meta (70% o menos).';
                                        break;
                                        case 'no clasificado':
                                        default:
                                        $semaforoTexto = 'No Clasificado';
                                        $semaforoBadgeClase = 'bg-light text-dark'; // Un gris claro con texto oscuro
                                        $semaforoExplicacion =
                                        'No se pudo clasificar el indicador, posiblemente por falta de datos o meta no definida.';
                                        break;
                                        }
                                        @endphp

                                        <div class="d-flex justify-content-end align-items-center">
                                            {{-- El Badge con el estado de la semaforización --}}
                                            <span class="badge rounded-pill {{ $semaforoBadgeClase }}"
                                                style="font-size: 0.85rem; padding: 0.4em 0.7em;">
                                                {{ $semaforoTexto }}
                                            </span>

                                            {{-- Botón de Información con Popover --}}
                                            <button type="button"
                                                class="btn btn-sm btn-link text-muted p-0 ms-2"
                                                {{-- Botón sin bordes, como un enlace --}} data-bs-toggle="popover"
                                                data-bs-placement="left" data-bs-trigger="hover focus"
                                                data-bs-html="true"
                                                title="<strong class='text-{{ str_replace('bg-', '', explode(' ', $semaforoBadgeClase)[0]) }}'>{{ $semaforoTexto }}</strong>"
                                                {{-- Usar la clase de color del badge para el título del popover --}}
                                                data-bs-content="{{ htmlspecialchars($semaforoExplicacion, ENT_QUOTES) }}">
                                                <i class="fas fa-info-circle"></i>
                                                {{-- Icono de FontAwesome --}}
                                            </button>
                                        </div>
                                        @else
                                        <span class="badge rounded-pill bg-secondary"
                                            style="font-size: 0.85rem; padding: 0.4em 0.7em;">N/D</span>
                                        @endif
                                    </div>
                                    @else
                                    {{-- Si no hay ni ODS ni semáforo, pero quieres mantener la estructura o añadir un placeholder --}}
                                    @endif
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cards Pequeñas --}}
        <div class="row mx-auto mb-5">
            <!-- Unidad de Medida -->
            <div class="col-xl-3 col-md-6 col-12 mb-2 hvr-grow">
                <div class="card h-100">
                    <div class="card-content card_indicador1">
                        <div class="card-body text-center">
                            <div class="datos" style="color:{{ $programa->color }}">Unidad de Medida</div>
                            <div class="datos_eje1">{{ $indicador->unidad_medida }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tendencia -->
            <div class="col-xl-3 col-md-6 col-12 mb-2 hvr-grow">
                <div class="card h-100">
                    <div class="card-content card_indicador1">
                        <div class="card-body text-center">
                            <div class="datos" style="color:{{ $programa->color }}">Tendencia</div>
                            <div class="datos_eje1">{{ $indicador->tendencia }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Línea Base -->
            <div class="col-xl-3 col-md-6 col-12 mb-2 hvr-grow">
                <div class="card h-100">
                    <div class="card-content card_indicador1">
                        <div class="card-body text-center">
                            <div class="datos" style="color:{{ $programa->color }}">Línea Base
                                {{ $indicador->linea_base }}
                            </div>
                            <div class="datos_eje1">
                                @if (is_numeric(str_replace(',', '', $indicador->dato_linea_base)))
                                {{ number_format((float) str_replace(',', '', $indicador->dato_linea_base), 2, '.', ',') }}
                                @else
                                {{ $indicador->dato_linea_base ?? 'Sin datos' }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Meta 2024/2030 -->
            <div class="col-xl-3 col-md-6 col-12 mb-2 hvr-grow">
                <div class="card h-100">
                    <div class="card-content card_indicador1">
                        <div class="card-body text-center">
                            <div class="datos" style="color:{{ $programa->color }}">
                                Meta 2030
                                {{-- {{ isset($indicador->meta_2024) && Str::contains($indicador->meta_2024, '2030') ? '2030' : '2024' }} --}}
                            </div>
                            <div class="datos_eje1">
                                @if (isset($indicador->meta_2024) && is_numeric(str_replace(',', '', $indicador->meta_2024)))
                                {{ number_format((float) str_replace(',', '', $indicador->meta_2024), 2, '.', ',') }}
                                @else
                                {{ $indicador->meta_2024 ?? 'Sin datos' }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                No hay indicadores registrados para este programa actualmente.
            </div>
        </div>
        @endforelse
    </div>
</div>

@section('jss-final')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl, {
                sanitize: false
            });
        });

        document.addEventListener('click', function(e) {
            popoverList.forEach(function(popover) {
                if (!popover._element.contains(e.target) && !popover._element.isSameNode(e
                        .target) && popover._tip && popover._tip.classList.contains('show')) {
                    if (!popover._tip.contains(e.target)) {
                        popover.hide();
                    }
                }
            });
        });
    });
</script>
@endsection
@endsection
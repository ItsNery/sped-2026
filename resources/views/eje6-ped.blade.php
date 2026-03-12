@extends('layouts.plantilla')
@section('title', 'Eje Transversal del Plan Estatal de Desarrollo 2024-2030')
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
{{-- <img src="{{ asset('img/Banners/Banner_PED/banner_eje5_plan2.jpg') }}" class="w-100 px-0"> --}}
<img src="{{ asset('img/Banners/Banner_PED/Eje_6.webp') }}" alt="banner del Eje 1" class="w-100 px-0">
<div class="row nav_ejes">
    <div class="col-md-2 nav_eje_item nav_eje1 ocultar_submenu">
        <a href="{{ url('/ped/eje-1') }}" class="dropbtn nav_eje_link">Eje 1</a>
    </div>
    <div class="col-md-2 nav_eje_item nav_eje2 ocultar_submenu">
        <a href="{{ url('/ped/eje-2') }}" class="dropbtn nav_eje_link">Eje 2</a>
    </div>
    <div class="col-md-2 nav_eje_item nav_eje3 ocultar_submenu">
        <a href="{{ url('/ped/eje-3') }}" class="dropbtn nav_eje_link">Eje 3</a>
    </div>
    <div class="col-md-2 nav_eje_item nav_eje4 ocultar_submenu">
        <a href="{{ url('/ped/eje-4') }}" class="dropbtn nav_eje_link">Eje 4</a>
    </div>
    <div class="col-md-2 nav_eje_item nav_eje5 ocultar_submenu">
        <a href="{{ url('/ped/eje-5') }}" class="dropbtn nav_eje_link">Eje 5</a>
    </div>
    <div class="col-md-2 nav_eje_item nav_eje6_active ocultar_submenu">
        <a href="{{ url('/ped/eje-6') }}" class="dropbtn nav_eje_link">Eje Transversal</a>
    </div>
</div>
<div class="row contenido">
    <div class="col-sm-12 col-md-3 offset-md-1 objetivo_6">
        <h2>Enfoque</h2>
    </div>
    <div class="col-sm-12 col-md-7 objetivo_5">
        <p>

            Abarca temas que no se circunscriben a un problema en
            concreto, sino que atañe a toda la administración pública. Su
            carácter interdisciplinario se verá reflejado en todos los
            ejes rectores del desarrollo.

        </p>
    </div>
</div>
<div class="row indicador_6">
    <div class="container">
        @php
        // Calcular el total de indicadores en todos los grupos para el título general (opcional)
        $totalIndicadoresGeneral = 0;
        if (isset($indicadoresAgrupados) && $indicadoresAgrupados->count() > 0) {
        foreach ($indicadoresAgrupados as $grupoDeIndicadores) {
        $totalIndicadoresGeneral += $grupoDeIndicadores->count();
        }
        }
        @endphp
        <h2>{{ $totalIndicadoresGeneral }} INDICADORES</h2> {{-- O el título que prefieras --}}

        {{-- Iterar sobre el grupo de temáticas --}}
        @forelse ($indicadoresAgrupados as $nombreTematica => $listaIndicadoresDeLaTematica)
        <div class="tematica-group mt-4 mb-3"> {{-- Contenedor para cada temática --}}
            <h3 class="titulo-tematica"
                style="border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 15px;">
                {{-- Puedes añadir un icono si quieres --}}
                {{-- <i class="fas fa-tag"></i> --}}
                Temática: {{ $nombreTematica ?: 'Indicadores Sin Temática Específica' }}
            </h3>

            {{-- Iterar sobre los indicadores de ESTA temática, usando TU DISEÑO DE CARD ORIGINAL --}}
            @if ($listaIndicadoresDeLaTematica->isNotEmpty())
            @foreach ($listaIndicadoresDeLaTematica as $indicador)
            {{-- ================================================================ --}}
            {{-- AQUÍ EMPIEZA TU CÓDIGO ORIGINAL PARA MOSTRAR UNA CARD DE INDICADOR --}}
            {{-- ================================================================ --}}
            <div class="row mx-auto"> {{-- Podrías necesitar ajustar mb-3 aquí si el div.tematica-group ya tiene margen inferior --}}
                <div class="col-12">
                    <div class="card overflow-hidden mb-3"> {{-- Añadido mb-3 a la card para separación --}}
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
                                            <div class="ultimo">
                                                Resultado: {{ $indicador->anio_reciente ?? 'Sin datos' }}
                                            </div>
                                            <div class="datos_eje1">
                                                @isset($indicador->dato_reciente)
                                                {{ number_format($indicador->dato_reciente, 2, '.', ',') }}
                                                @else
                                                Sin datos
                                                @endisset
                                            </div>
                                        </div>
                                        {{-- Código de ODS y Semáforo --}}
                                        @if ($indicador->ods->isNotEmpty() || $indicador->semaforizacion)
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
                                            @if ($indicador->semaforizacion)
                                            @php
                                            $semaforoTexto = $indicador->semaforizacion;
                                            $semaforoBadgeClase = 'bg-secondary'; // Clase de color del badge por defecto
                                            $semaforoExplicacion =
                                            'No hay información detallada para este estado.';

                                            switch (
                                            strtolower($indicador->semaforizacion)
                                            ) {
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
                                            $semaforoBadgeClase =
                                            'bg-warning text-dark'; // Amarillo Bootstrap (text-dark para contraste)
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
                                            $semaforoBadgeClase =
                                            'bg-light text-dark'; // Un gris claro con texto oscuro
                                            $semaforoExplicacion =
                                            'No se pudo clasificar el indicador, posiblemente por falta de datos o meta no definida.';
                                            break;
                                            }
                                            @endphp

                                            <div
                                                class="d-flex justify-content-end align-items-center">
                                                {{-- El Badge con el estado de la semaforización --}}
                                                <span
                                                    class="badge rounded-pill {{ $semaforoBadgeClase }}"
                                                    style="font-size: 0.85rem; padding: 0.4em 0.7em;">
                                                    {{ $semaforoTexto }}
                                                </span>

                                                {{-- Botón de Información con Popover --}}
                                                <button type="button"
                                                    class="btn btn-sm btn-link text-muted p-0 ms-2"
                                                    {{-- Botón sin bordes, como un enlace --}} data-bs-toggle="popover"
                                                    data-bs-placement="left"
                                                    data-bs-trigger="hover focus"
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
            <div class="row mx-auto mb-3"> {{-- Esta es la fila de las 4 cards pequeñas --}}
                <div class="col-xl-3 col-md-6 col-12 mb-2 hvr-grow"> {{-- Ajuste de columnas para responsividad y mb-2 --}}
                    <div class="card h-100">
                        <div class="card-content card_indicador1">
                            <div class="card-body text-center">
                                <div class="datos">Unidad de Medida</div>
                                <div class="datos_eje1">{{ $indicador->unidad_medida }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-12 mb-2 hvr-grow">
                    <div class="card h-100">
                        <div class="card-content card_indicador1">
                            <div class="card-body text-center">
                                <div class="datos">Tendencia</div>
                                <div class="datos_eje1">{{ $indicador->tendencia }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-12 mb-2 hvr-grow">
                    <div class="card h-100">
                        <div class="card-content card_indicador1">
                            <div class="card-body text-center">
                                <div class="datos">Línea Base {{ $indicador->linea_base }}</div>
                                <div class="datos_eje1">
                                    @isset($indicador->dato_linea_base)
                                    {{ number_format($indicador->dato_linea_base, 2, '.', ',') }}
                                    @else
                                    Sin datos
                                    @endisset
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-12 mb-2 hvr-grow">
                    <div class="card h-100">
                        <div class="card-content card_indicador1">
                            <div class="card-body text-center">
                                <div class="datos">Meta 2030
                                    {{-- {{ $indicador->meta_2024 && Str::contains($indicador->meta_2024, '2030') ? '2030' : '2024' }} --}}
                                </div> {{-- Ajuste tentativo del año de la meta --}}
                                <div class="datos_eje1">
                                    @isset($indicador->meta_2024)
                                    {{ number_format($indicador->meta_2024, 2, '.', ',') }}
                                    @else
                                    Sin datos
                                    @endisset
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            @else
            <p>No hay indicadores disponibles para la temática "{{ $nombreTematica }}".</p>
            @endif
        </div>
        @empty
        <div class="alert alert-info mt-4" role="alert">
            No se encontraron indicadores que cumplan con los criterios de búsqueda.
        </div>
        @endforelse
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl, {
                sanitize: false // Si usas data-bs-html="true" y confías en el contenido.
            });
        });

        // Opcional: para que los popovers se cierren si se hace clic fuera de ellos
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
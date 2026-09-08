<x-app-layout>
    @section('title', 'Municipios: Detalle Indicador Municipal')
    <x-slot name="header">
        <div class="exec-header indicator-detail-header">
            <div>
                <span class="exec-eyebrow">Indicador municipal</span>
                <h2 class="exec-header__title">Detalle del indicador</h2>
            </div>
            <span class="exec-header__plan">Solo lectura</span>
        </div>
    </x-slot>
    @if ($message = Session::get('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: '{{ $message }}'
                });
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: '¡Error!',
                text: '{{ session('error') }}',
                confirmButtonText: 'Aceptar'
            });
        </script>
    @endif

    <div class="indicator-detail mx-auto">
        <div class="contenedor-principal mx-auto indicator-detail__surface">
            <div class="encabezado-lista my-2">
                <h2>Vista de Indicador Municipal (Solo Lectura)</h2>
            </div>
            <div class="indicator-detail__actions">
                @can('ver-indicador')
                    <a href="{{ url()->previous() }}" class="button-action button-back text-decoration-none">
                        <span class="button__text">Regresar</span>
                        <span class="button__icon">
                            <svg class="svg" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                        </span>
                    </a>
                @endcan
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="custom-card">
                        <div class="custom-header text-center">
                            <h1 class="text-2xl font-bold">{{ $indicador->indicador }}</h1>
                        </div>
                        <div class="custom-title text-center py-2">
                            <h4>Descripción</h4>
                            <p>{{ $indicador->descripcion }}</p>
                        </div>
                        <div class="custom-title text-center py-2 td-ods">
                            @foreach ($indicador->ods->unique('id') as $ods)
                                <img src="{{ asset('assets-administrador/img/ods/' . $ods->id . '.png') }}"
                                    alt="ODS {{ $ods->id }}" class="img-fluid ods-icono">
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-regular fa-building" aria-hidden="true"></i>Municipio
                                    Responsable
                                </div>
                                <p>{{ $indicador->municipio->nombre ?? 'Sin asignar' }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-book" aria-hidden="true"></i>Instrumento de
                                    Planeación
                                </div>
                                <p>{{ $indicador->instrumento }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-book-open" aria-hidden="true"></i>Eje</div>
                                <p>{{ $indicador->eje_indicador }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-signal" aria-hidden="true"></i>Estatus</div>
                                <div class="indicator-detail__status">
                                    @if ($indicador->validado === 1)
                                        <span class="indicator-detail-status indicator-detail-status--validated">
                                            <i class="fa-solid fa-check" aria-hidden="true"></i> Validado
                                        </span>
                                    @elseif ($indicador->validado === null)
                                        <span class="indicator-detail-status indicator-detail-status--unavailable">
                                            <i class="fa-solid fa-times" aria-hidden="true"></i> Sin actualizar
                                        </span>
                                    @else
                                        <span class="indicator-detail-status indicator-detail-status--updated">
                                            <i class="fa-solid fa-minus" aria-hidden="true"></i> Actualizado
                                        </span>
                                        <span class="indicator-detail-status indicator-detail-status--pending">
                                            <i class="fa-solid fa-exclamation-triangle" aria-hidden="true"></i> No validado
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-section-title"><i class="fa-regular fa-bookmark" aria-hidden="true"></i>Temática</div>
                                <p>{{ $indicador->tematica }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-regular fa-chart-bar" aria-hidden="true"></i>
                                    Línea Base
                                    {{ $indicador->linea_base }}
                                </div>
                                <p>
                                    {{ number_format($indicador->dato_linea, 2, '.', ',') }}
                                </p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-bullseye" aria-hidden="true"></i>Meta 2027</div>
                                <p> {{ $indicador->meta_2024 }}</p>
                            </div>
                            <div class="col-md-2">
                                <div class="custom-section-title"><i class="fa-solid fa-chart-area" aria-hidden="true"></i>Cobertura</div>
                                <p> {{ $indicador->cobertura }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-bullseye" aria-hidden="true"></i>Tipo</div>
                                <p> {{ $indicador->tipo->nombre }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-bullseye" aria-hidden="true"></i>Nivel</div>
                                <p> {{ $indicador->nivel->nombre }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-bullseye" aria-hidden="true"></i>Dimensión</div>
                                <p> {{ $indicador->dimension->nombre }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-bullseye" aria-hidden="true"></i>¿La información es
                                    pública?</div>
                                <p> {{ $indicador->publica == 1 ? 'Si' : 'No' }}</p>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-section-title"><i class="fa-solid fa-tower-broadcast" aria-hidden="true"></i>Fuente
                                </div>
                                <p> {{ $indicador->fuente }}</p>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-section-title"><i class="fa-solid fa-link" aria-hidden="true"></i>Enlace de la fuente
                                </div>
                                <p>
                                    @if ($indicador->liga)
                                        <a href="{{ $indicador->liga }}" target="_blank" rel="noopener noreferrer"
                                            title="Fuente de {{ $indicador->indicador }}">
                                            Enlace
                                        </a>
                                    @else
                                        Sin enlace
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-section-title"><i class="fas fa-sync-alt" aria-hidden="true"></i>Periodicidad</div>
                                <p> {{ $indicador->periodicidad->nombre }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-ruler" aria-hidden="true"></i>Unidad de medida
                                </div>
                                <p> {{ $indicador->unidad_medida }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-chart-line" aria-hidden="true"></i>Tendencia</div>
                                <p> {{ $indicador->tendencia }}</p>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-section-title"><i
                                        class="fa-solid fa-square-root-variable" aria-hidden="true"></i>Fórmula</div>
                                <p> {{ $indicador->formula }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="custom-section-title">
                        Resultados Registrados
                    </div>
                    @if ($indicador->resultados->isNotEmpty())

                        {{-- 1. Agrupa los resultados por 'año' y recorre cada grupo --}}
                        @foreach ($indicador->resultados->groupBy('año') as $año => $resultadosDelAño)
                            {{-- 2. Muestra el año como un título principal para el grupo --}}
                            <h3 class="text-center my-4">{{ $año }}</h3>

                            {{-- 3. Crea una fila (row) para las tarjetas de este año --}}
                            <div class="row justify-content-center g-3">

                                {{-- 4. Ahora recorre los resultados que pertenecen a ESE año --}}
                                @foreach ($resultadosDelAño as $resultado)
                                    <div class="col-md-4">
                                        <div class="card border-primary h-100"> {{-- h-100 para alinear tarjetas --}}
                                            <div class="card-header bg-gobierno text-white text-center">
                                                <strong>Periodo: {{ $resultado->periodo ?? 'N/A' }}</strong>
                                            </div>
                                            <div class="card-body">
                                                <p><strong>Dato:</strong>
                                                    {{-- Es bueno verificar que el dato no es nulo antes de formatear --}}
                                                    @if (isset($resultado->dato))
                                                        {{ number_format($resultado->dato, 2) }}
                                                    @else
                                                        'Sin dato'
                                                    @endif
                                                </p>
                                                <p><strong>Resultado:</strong>
                                                    {{ $resultado->resultado ?? 'Sin resultado' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <div class="indicator-detail__empty" role="status">
                            <i class="fa-regular fa-calendar-xmark" aria-hidden="true"></i>
                            No hay resultados registrados para este indicador.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

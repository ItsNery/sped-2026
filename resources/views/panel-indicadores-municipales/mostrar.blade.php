<x-indicador-municipal-layout>
    @section('title', 'Indicadores: Detalle')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Indicador') }}
        </h2>
    </x-slot>
    {{-- {{$errors}} --}}
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
    @section('jss-inicial')
        <script src="{{ asset('assets-administrador/js/popper.min.js') }}"></script>
    @endsection
    <div class="container py-12 mx-auto">
        <div class="mx-auto contenedor-principal">
            <div class="encabezado-lista">
                <h2>Detalles del indicador</h2>
            </div>
            {{-- <img src="{{ asset('assets-administrador/img/detalle_indicador.png') }}" alt="" class="w-100"> --}}
            <div class="d-flex justify-content-end gap-3 pb-2">
                @can('ver-indicador-municipal')
                    <a href="{{ route('panel-indicadores-municipales.index') }}" class="btn btn-secondary mt-2">
                        <i class="fa-solid fa-left-long"></i> Regresar
                    </a>
                @endcan
                @can('editar-indicador-municipal')
                    <a href="{{ route('panel-indicadores-municipales.edit', $indicador->id) }}"
                        class="btn bg-gobierno-4 mt-2 mx-2">
                        <i class="fa-solid fa-pen"></i> Editar
                    </a>
                @endcan
                @can('validar-indicador-municipal')
                    <form action="{{ route('indicadores-municipales.toggleValidacion', $indicador->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="btn {{ $indicador->validado ? 'btn-danger' : 'btn-success' }} mt-2 mx-2 text-white">
                            <i class="fa-solid {{ $indicador->validado ? 'fa-xmark' : 'fa-check' }}"></i>
                            {{ $indicador->validado ? 'Invalidar' : 'Validar' }}
                        </button>
                    </form>
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
                                    alt="Imagen de ODS {{ $ods->id }}" class="img-fluid ods-icono">
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-regular fa-building"></i>Municipio
                                    Responsable
                                </div>
                                <p>{{ $indicador->municipio->nombre ?? 'Sin asignar' }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-book"></i>Instrumento de
                                    Planeación
                                </div>
                                <p>{{ $indicador->instrumento }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-book-open"></i>Eje</div>
                                <p>{{ $indicador->eje_indicador }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-signal"></i>Estatus</div>
                                <p class="d-flex justify-content-center">
                                    @if ($indicador->validado === 1)
                                        <div class="btn btn-sm btn-success text-white">
                                            <i class="fa-solid fa-check"></i> Validado
                                        </div>
                                    @elseif ($indicador->validado === null)
                                        <div class="btn btn-sm btn-danger">
                                            <i class="fa-solid fa-times"></i> Sin Actualizar
                                        </div>
                                    @else
                                        <div class="btn btn-sm btn-info">
                                            <i class="fa-solid fa-minus"></i> Actualizado
                                        </div>
                                        <div class="btn btn-sm btn-warning">
                                            <i class="fa-solid fa-exclamation-triangle"></i> No Validado
                                        </div>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-section-title"><i class="fa-regular fa-bookmark"></i>Temática</div>
                                <p>{{ $indicador->tematica }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-regular fa-chart-bar"></i>
                                    Línea Base
                                    {{ $indicador->linea_base }}
                                </div>
                                <p>
                                    {{ number_format($indicador->dato_linea, 2, '.', ',') }}
                                </p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-bullseye"></i>Meta 2027</div>
                                <p> {{ $indicador->meta_2024 }}</p>
                            </div>
                            <div class="col-md-2">
                                <div class="custom-section-title"><i class="fa-solid fa-chart-area"></i>Cobertura</div>
                                <p> {{ $indicador->cobertura }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-bullseye"></i>Tipo</div>
                                <p> {{ $indicador->tipo->nombre }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-bullseye"></i>Nivel</div>
                                <p> {{ $indicador->nivel->nombre }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-bullseye"></i>Dimensión</div>
                                <p> {{ $indicador->dimension->nombre }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-bullseye"></i>¿La información es
                                    pública?</div>
                                <p> {{ $indicador->publica == 1 ? 'Si' : 'No' }}</p>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-section-title"><i class="fa-solid fa-tower-broadcast"></i>Fuente
                                </div>
                                <p> {{ $indicador->fuente }}</p>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-section-title"><i class="fa-solid fa-link"></i>Enlace de la fuente
                                </div>
                                <p>
                                    @if ($indicador->liga)
                                        <a href="{{ $indicador->liga }}" target="_blank"
                                            title="Fuente de {{ $indicador->nombre }}">
                                            Enlace
                                        </a>
                                    @else
                                        Sin enlace
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-section-title"><i class="fas fa-sync-alt"></i>Periodicidad</div>
                                <p> {{ $indicador->periodicidad->nombre }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-ruler"></i>Unidad de medidad
                                </div>
                                <p> {{ $indicador->unidad_medida }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-section-title"><i class="fa-solid fa-chart-line"></i>Tendencia</div>
                                <p> {{ $indicador->tendencia }}</p>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-section-title"><i
                                        class="fa-solid fa-square-root-variable"></i>Fórmula</div>
                                <p> {{ $indicador->formula }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="encabezado-lista">
                <h2>Resultados {{ $indicador->periodicidad->nombre }}es</h2>
            </div>
            @can('subir-resultados-indicador-municipal')
                <div class="container text-end">
                    <button class="btn btn-success bg-exito-gobierno my-3 align-items-end" data-bs-toggle="modal"
                        data-bs-target="#modalAgregarNuevoAño">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
            @endcan
            <ul class="nav nav-tabs custom-tab-nav" id="myTab" role="tablist">
                @foreach ($añosDisponibles as $index => $anio)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}" id="tab-{{ $anio }}"
                            data-bs-toggle="tab" data-bs-target="#content-{{ $anio }}" type="button"
                            role="tab" aria-controls="content-{{ $anio }}"
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}">{{ $anio }}</button>
                    </li>
                @endforeach
            </ul>
            <div class="tab-content custom-tab-content" id="myTabContent">
                @foreach ($añosDisponibles as $index => $anio)
                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                        id="content-{{ $anio }}" role="tabpanel" aria-labelledby="tab-{{ $anio }}">
                        @php
                            $resultadosPorAño = $indicador->resultados->where('año', $anio);
                        @endphp

                        @if ($resultadosPorAño->isEmpty())
                            <p class="text-muted">No hay resultados disponibles para este año.</p>
                        @else
                            <div class="row g-3">
                                @foreach ($resultadosPorAño as $resultado)
                                    <div class="col-md-4">
                                        <div class="card border-primary">
                                            <div class="card-header bg-gobierno text-white text-center">
                                                <strong>Periodo: {{ $resultado->periodo ?? 'N/A' }}</strong>
                                            </div>
                                            <div class="card-body">
                                                <p><strong>Dato:</strong>
                                                    {{ number_format($resultado->dato, 2) ?? 'Sin dato' }}</p>
                                                <p><strong>Resultado:</strong>
                                                    {{ $resultado->resultado ?? 'Sin resultado' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @can('editar-resultados-indicador-municipal')
                                <!-- Botón para abrir el modal de edición por año -->
                                <button class="btn btn-primary bg-gobierno-8 mx-2 my-2" data-bs-toggle="modal"
                                    data-bs-target="#modalEditarResultados_{{ $anio }}">
                                    Editar resultados de {{ $anio }}
                                </button>
                            @endcan
                        @endif
                    </div>
                    <!-- Modal para editar resultados por año -->
                    <div class="modal fade" id="modalEditarResultados_{{ $anio }}" tabindex="-1"
                        aria-labelledby="editarResultadosLabel_{{ $anio }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <form action="{{ route('actualizarResultadosIndMun', $anio) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editarResultadosLabel_{{ $anio }}">Editar
                                            Resultados de {{ $anio }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            @foreach ($resultadosPorAño as $resultado)
                                                <div class="col-md-4">
                                                    <div class="card border-primary">
                                                        <div class="card-header bg-gobierno text-white text-center">
                                                            <strong>Periodo: {{ $resultado->periodo }}</strong>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="mb-3">
                                                                <label for="dato-{{ $resultado->id }}"
                                                                    class="form-label">Dato</label>
                                                                <input type="number" step="0.01"
                                                                    id="dato-{{ $resultado->id }}"
                                                                    name="resultados[{{ $resultado->id }}][dato]"
                                                                    class="form-control"
                                                                    value="{{ $resultado->dato }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="resultado-{{ $resultado->id }}"
                                                                    class="form-label">Resultado</label>
                                                                <textarea type="text" id="resultado-{{ $resultado->id }}" name="resultados[{{ $resultado->id }}][resultado]"
                                                                    class="form-control" value="{{ $resultado->resultado }}">{{ $resultado->resultado }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-success">Guardar cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="modal fade" id="modalAgregarNuevoAño" tabindex="-1" aria-labelledby="agregarNuevoAñoLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
                    <div class="modal-content">
                        <form action="{{ route('agregarResultadosNuevoAnio') }}" method="POST" novalidate>
                            @csrf
                            <!-- Agregar el campo oculto para id_indicador -->
                            <input type="hidden" name="id_indicador" value="{{ $indicador->id }}">
                            <div class="modal-header">
                                <h5 class="modal-title" id="agregarNuevoAñoLabel">Agregar Resultados de Nuevo Año</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="custom-section-title">
                                        Agregar nuevos resultados
                                    </div>

                                    <!-- Selección de Año -->
                                    <div class="col-md-5 mb-3">
                                        <div class="custom-section-title"><i class="fa-solid fa-calendar"></i>
                                            Año *
                                        </div>
                                        <select id="ano" name="ano"
                                            class="form-control @error('ano') is-invalid @enderror" required>
                                            <option value="" disabled selected>Seleccione un año</option>
                                            @for ($i = 2010; $i <= date('Y'); $i++)
                                                <option value="{{ $i }}"
                                                    {{ in_array($i, $datosResultadosIndicador->pluck('año')->toArray()) ? 'disabled' : '' }}>
                                                    {{ $i }}
                                                </option>
                                            @endfor
                                        </select>
                                        @error('ano')
                                            <small class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </small>
                                        @enderror
                                    </div>

                                    <!-- Selección de Periodicidad -->
                                    <div class="col-md-5 mb-3">
                                        <div class="custom-section-title"><i class="fas fa-sync-alt"></i>
                                            Periodicidad *
                                        </div>
                                        <select id="periodicidad_id" name="periodicidad_id"
                                            class="form-control @error('periodicidad_id') is-invalid @enderror"
                                            disabled required>
                                            <option value="" disabled selected>Seleccione</option>
                                            @foreach ($periodicidades as $periodicidad)
                                                <option value="{{ $periodicidad->id }}"
                                                    {{ $indicador->periodicidad_id == $periodicidad->id ? 'selected' : '' }}>
                                                    {{ $periodicidad->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <!-- Agregar campo oculto con el valor de periodicidad_id -->
                                        <input type="hidden" name="periodicidad_id"
                                            value="{{ $indicador->periodicidad_id }}">

                                        @error('periodicidad_id')
                                            <small class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </small>
                                        @enderror
                                    </div>

                                    <!-- Botón para agregar nuevo periodo -->
                                    <div class="col-md-2 mb-3">
                                        <button type="button" id="agregar-nuevo" class="btn btn-primary">
                                            Nuevo año
                                        </button>
                                    </div>

                                    <!-- Contenedor para los campos dinámicos -->
                                    <div id="input-periodicidad" class="row"></div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('agregar-nuevo').addEventListener('click', function() {
            const ano = document.getElementById('ano').value;
            const periodicidadId = document.getElementById('periodicidad_id').value;
            const nuevosRegistros = document.getElementById('input-periodicidad');

            // Validar si se ha seleccionado año y periodicidad
            if (!ano || !periodicidadId) {
                Swal.fire({
                    icon: 'warning',
                    title: '¡Cuidado!',
                    text: 'Debes seleccionar un año y una periodicidad antes de agregar un nuevo registro.',
                    confirmButtonText: 'Entendido',
                });
                return;
            }

            // Definir etiquetas y cantidad de campos según periodicidad
            const periodos = {
                1: 'Anual',
                2: 'Bimestral',
                3: 'Cuatrimestral',
                4: 'Mensual',
                5: 'Semestral',
                6: 'Trimestral'
            };

            const numPeriodos = {
                1: 1, // Anual
                2: 6, // Bimestral
                3: 3, // Cuatrimestral
                4: 12, // Mensual
                5: 2, // Semestral
                6: 4 // Trimestral
            };

            // Generar los campos para el nuevo registro
            if (periodicidadId && numPeriodos[periodicidadId]) {
                for (let i = 1; i <= numPeriodos[periodicidadId]; i++) {
                    const nuevoId = `${ano}_${periodicidadId}_${i}_${Date.now()}`; // Identificador único

                    nuevosRegistros.innerHTML += `
                        <div class="row align-items-end mb-3" id="registro_${nuevoId}">
                            <div class="col-md-2">
                                <label for="nuevo_año_${nuevoId}" class="form-label">Año</label>
                                <input type="number" id="nuevo_año_${nuevoId}" name="nuevos_registros[${nuevoId}][año]" class="form-control form-control-sm text-center" value="${ano}" readonly>
                            </div>
                            <div class="col-md-2">
                                <label for="nuevo_periodo_${nuevoId}" class="form-label">Periodo ${periodos[periodicidadId]} ${i}</label>
                                <input type="number" id="nuevo_periodo_${nuevoId}" name="nuevos_registros[${nuevoId}][periodo]" class="form-control form-control-sm text-center" value="${i}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="nuevo_dato_${nuevoId}" class="form-label">Dato</label>
                                <input type="number"  step="0.01" id="nuevo_dato_${nuevoId}" name="nuevos_registros[${nuevoId}][dato]" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4">
                                <label for="nuevo_resultado_${nuevoId}" class="form-label">Resultado</label>
                                <input type="text" id="nuevo_resultado_${nuevoId}" name="nuevos_registros[${nuevoId}][resultado]" class="form-control form-control-sm">
                            </div>
                        </div>
                    `;
                }
                document.getElementById('agregar-nuevo').disabled = true;
            }
        });
    </script>
</x-indicador-municipal-layout>

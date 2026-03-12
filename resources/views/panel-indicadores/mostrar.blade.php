<x-app-layout>
    @section('title', 'Indicadores: Detalle')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Indicador') }}
        </h2>
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
    @if ($message = Session::get('status'))
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
            {{-- @php
            dd($indicador);
            @endphp --}}
            {{-- <img src="{{ asset('assets-administrador/img/detalle_indicador.png') }}" alt="" class="w-100"> --}}

            <div class="d-flex justify-content-end gap-3 pb-2 mx-2">
                @can('ver-indicador')
                <a href="{{ route('panel-indicadores.index') }}" class="text-decoration-none mt-2">
                    <button type="button" class="button-action button-back">
                        <span class="button__text">Regresar</span>
                        <span class="button__icon">
                            <svg class="svg" viewBox="0 0 24 24">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                        </span>
                    </button>
                </a>
                @endcan

                @can('editar-indicador')
                <a href="{{ route('panel-indicadores.edit', $indicador->id) }}" class="text-decoration-none mt-2">
                    <!-- Botón Editar -->
                    <button type="button" class="button-action button-edit">
                        <span class="button__text">Editar</span>
                        <span class="button__icon">
                            <svg class="svg" viewBox="0 0 24 24">
                                <path
                                    d="M3 17.25V21h3.75l11.06-11.06-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 000-1.41l-2.34-2.34a1 1 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                            </svg>
                        </span>
                    </button>
                </a>
                @endcan
                @can('validar-indicador')
                <form action="{{ route('indicadores.toggleValidacion', $indicador->id) }}" method="POST"
                    style="display:inline;">
                    @csrf
                    @method('PATCH')

                    <!-- Botón Validar -->
                    <button type="submit"
                        class="button-action button-validate {{ $indicador->indicador_validado ? 'd-none' : '' }} mt-2">
                        <span class="button__text">Validar</span>
                        <span class="button__icon">
                            <svg class="svg" viewBox="0 0 24 24">
                                <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </button>

                    <!-- Botón Desvalidar -->
                    <button type="submit"
                        class="button-action button-unvalidate {{ $indicador->indicador_validado ? '' : 'd-none' }} mt-2">
                        <span class="button__text">Invalidar</span>
                        <span class="button__icon">
                            <svg class="svg" viewBox="0 0 24 24">
                                <path d="M6 6l12 12M6 18L18 6" stroke="currentColor" stroke-width="2" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </button>
                </form>
                @endcan

            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="custom-card">
                        <div class="custom-header text-center">
                            <h1 class="text-2xl font-bold">{{ $indicador->nombre }}</h1>
                        </div>
                        <div class="custom-title text-center py-2">
                            <h4>Descripción</h4>
                            <p>{{ $indicador->descripcion }}</p>
                        </div>

                        <div class="row">
                            {{-- Se comentan ods, por que no hay --}}
                            {{-- <div class="col-md-8">
                                <div class="custom-title text-center py-2 td-ods">
                                    @foreach ($indicador->ods->unique('id') as $ods)
                                    <img src="{{ asset('assets-administrador/img/ods/' . $ods->id . '.png') }}"
                            alt="Imagen de ODS {{ $ods->id }}" class="img-fluid ods-icono">
                            @endforeach
                        </div>
                    </div> --}}
                    <div class="col-md-4">
                        <div class="custom-section-title">
                            <i class="fa-solid fa-traffic-light"></i>
                            Semaforización
                        </div>
                        <p class="d-flex justify-content-center">
                            @if ($indicador->semaforizacion === 'Excedido')
                            <span class="badge bg-excedido">{{ $indicador->semaforizacion }}
                            </span>
                            @elseif ($indicador->semaforizacion === 'Aceptable')
                            <span class="badge bg-aceptable">{{ $indicador->semaforizacion }}
                            </span>
                            @elseif ($indicador->semaforizacion === 'Moderado')
                            <span class="badge bg-moderado">{{ $indicador->semaforizacion }}
                            </span>
                            @elseif ($indicador->semaforizacion === 'Insuficiente')
                            <span class="badge bg-insuficiente">{{ $indicador->semaforizacion }}
                            </span>
                            @else
                            {{ $indicador->semaforizacion }}
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-section-title"><i class="fa-regular fa-building"></i>Institución
                            responsable
                        </div>
                        <p>{{ $indicador->institucion->nombre ?? 'Sin asignar' }}</p>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-section-title"><i class="fa-solid fa-book"></i>Programa Derivado
                        </div>
                        <p>{{ $indicador->programa_derivado }}</p>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-section-title"><i class="fa-solid fa-book-open"></i>Eje del Programa
                        </div>
                        <p>{{ $indicador->programa }}</p>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-section-title"><i class="fa-solid fa-signal"></i>Estatus</div>
                        <p class="d-flex justify-content-center">
                            @if ($indicador->indicador_validado === 1)
                            {{-- @can('validar-indicador') --}}
                        <div class="btn btn-sm btn-success text-white">
                            <i class="fa-solid fa-check"></i> Validado
                        </div>
                        {{-- @endcan --}}
                        @elseif ($indicador->indicador_validado === null)
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
                            {{ number_format($indicador->dato_linea_base, 2, '.', ',') }}
                        </p>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-section-title"><i class="fa-solid fa-bullseye"></i>Meta 2030</div>
                        <p> {{ number_format($indicador->meta_2024, 2, '.', ',') }}</p>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-section-title"><i class="fa-solid fa-chart-area"></i>Cobertura
                        </div>
                        <p> {{ $indicador->cobertura }}</p>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-section-title"><i class="fas fa-sync-alt"></i>Periodicidad</div>
                        <p> {{ $indicador->periodicidad }}</p>
                    </div>
                    <div class="col-md-5">
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

                    {{-- <div class="col-md-2">
                                <div class="custom-section-title"><i class="fa-solid fa-timeline"></i>Periodo</div>
                                <p> {{ $indicador->periodo }}</p>
                </div> --}}
                <div class="col-md-3">
                    <div class="custom-section-title"><i class="fa-solid fa-ruler"></i>Unidad de Medida
                    </div>
                    <p> {{ $indicador->unidad_medida }}</p>
                </div>
                <div class="col-md-3">
                    <div class="custom-section-title"><i class="fa-solid fa-chart-line"></i>Tendencia
                    </div>
                    <p> {{ $indicador->tendencia }}</p>
                </div>
                <div class="col-md-6">
                    <div class="custom-section-title"><i
                            class="fa-solid fa-square-root-variable"></i>Fórmula</div>
                    <p> {{ $indicador->formula }}</p>
                </div>
                <div class="col-md-3">
                    <div class="custom-section-title"><i class="fa-solid fa-square-root-variable"></i>Fecha
                        de actualización inicial
                    </div>
                    <p> {{ $indicador->fecha_actualizacion ?? 'N/D' }}</p>
                </div>
            </div>
        </div>
    </div>

    </div>
    <div class="encabezado-lista d-flex justify-content-between align-items-center">
        <h2>Resultados Históricos Anuales</h2>
        @can('agregar-dato-anual')
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addYearModal">
            <i class="fa fa-plus"></i> Añadir Año
        </button>
        @endcan
    </div>

    {{-- @php
            $aniosParaMostrar = range(2020, Carbon\Carbon::now()->year);
            $aniosConDatos = $indicador->datosAnuales->pluck('anio')->toArray();
            $aniosFuturosParaEdicion = range(Carbon\Carbon::now()->year, Carbon\Carbon::now()->year);
            $aniosTabs = collect(array_merge($aniosConDatos, $aniosFuturosParaEdicion))
            ->unique()
            ->sort()
            ->values()
            ->all();
            if (empty($aniosTabs)) {
            $aniosTabs = range(Carbon\Carbon::now()->year - 2, Carbon\Carbon::now()->year);
            }
            @endphp --}}
    @php
    $aniosTabs = $indicador->datosAnuales
    ->pluck('anio')
    ->filter(fn($anio) => !is_null($anio)) // Sin el filtro de <= now()->year
        ->unique()
        ->sort()
        ->values()
        ->all();
        @endphp

        <ul class="nav nav-tabs custom-tab-nav" id="myTab" role="tablist">
            @foreach ($aniosTabs as $index => $year)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $index == 0 ? 'active' : '' }}" id="tab-{{ $year }}" data-bs-toggle="tab"
                    data-bs-target="#content-{{ $year }}" type="button" role="tab"
                    aria-controls="content-{{ $year }}"
                    aria-selected="{{ $index == 0 ? 'true' : 'false' }}">{{ $year }}</button>
            </li>
            @endforeach
        </ul>

        <div class="tab-content custom-tab-content" id="myTabContent">
            @foreach ($aniosTabs as $index => $year)
            @php
            // Busca el objeto DatoAnual específico para este año en la colección
            $datoAnualDelAnio = $indicador->datosAnuales->firstWhere('anio', $year);
            @endphp
            <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="content-{{ $year }}"
                role="tabpanel" aria-labelledby="tab-{{ $year }}">
                <div class="row">
                    <div class="col-md-8">
                        <div>
                            <div class="custom-section-title-year">Dato {{ $year }}</div>
                            <p>{{ $datoAnualDelAnio->valor_dato ?? 'Sin dato registrado para este año' }}</p>
                        </div>
                        <div>
                            <div class="custom-section-title-year">Próxima fecha de actualización
                                ({{ $year }})
                            </div>
                            <p>
                                {{ $datoAnualDelAnio && $datoAnualDelAnio->fecha_actualizacion ? Carbon\Carbon::parse($datoAnualDelAnio->fecha_actualizacion)->format('d-m-Y') : 'Sin fecha de actualización para este año' }}
                            </p>
                        </div>
                        <div>
                            <div class="custom-section-title-year">Principales resultados
                                ({{ $year }})</div>
                            <p>{{ $datoAnualDelAnio->resultados ?? 'Sin resultados registrados para este año' }}
                            </p>
                        </div>
                        <div>
                            <div class="custom-section-title-year">Observaciones ({{ $year }})</div>
                            <p>{{ $datoAnualDelAnio->observaciones ?? 'Sin observaciones registradas para este año' }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div>
                            <div class="custom-section-title-year">Documento de Evidencia
                                ({{ $year }})</div>
                            <div>
                                @if ($datoAnualDelAnio && $datoAnualDelAnio->evidencia && $datoAnualDelAnio->evidencia !== 'N/D')
                                <div class="pdfFound">
                                    <a href="{{ asset('assets-administrador/docs/' . $datoAnualDelAnio->evidencia) }}"
                                        download class="cursor-pointer">
                                        <img src="{{ asset('assets-administrador/img/Iconos captura-PDF_D.png') }}">
                                    </a>
                                    <a href="{{ asset('assets-administrador/docs/' . $datoAnualDelAnio->evidencia) }}"
                                        title="Consultar evidencia" target="_blank" class="cursor-pointer">
                                        <label class="control-label cursor-pointer">
                                            Consultar Evidencia
                                            {{ $year }}
                                        </label>
                                    </a>
                                </div>
                                @else
                                <div class="notFound">
                                    <img src="{{ asset('assets-administrador/img/sin_PDF.png') }}"
                                        class="downnotfound">
                                    <label class="control-label">Sin Evidencia para
                                        {{ $year }}</label>
                                </div>
                                @endif
                            </div>
                        </div>
                        @php
                        $user = auth()->user();

                        // 1. Verificar si es Admin (Ajusta 'administrador' al nombre real de tu rol o usa $user->id == 1)
                        $esAdmin = $user->hasRole('Administrador');

                        // 2. Verificar restricciones
                        $indicadorValidado = $indicador->indicador_validado == 1;
                        $usuarioFinalizado = $user->finalizado == 1;

                        // 3. Determinar si puede editar
                        // Puede editar SI: Es Admin O (No está validado Y No ha finalizado)
                        $puedeEditar = $esAdmin || (!$indicadorValidado && !$usuarioFinalizado);
                        @endphp

                        @if ($puedeEditar)
                        {{-- Muestra el botón --}}
                        @can('editar-indicador-anual')
                        <button type="button" class="btn btn-sm btn-warning mt-3" data-bs-toggle="modal"
                            data-bs-target="#editModal-{{ $year }}">
                            <i class="fa-regular fa-pen-to-square"></i> Editar datos {{ $year }}
                        </button>
                        @endcan
                        @else
                        {{-- Si NO puede editar, mostramos la razón (Solo para no-admins) --}}
                        @if ($indicadorValidado)
                        <span class="badge text-bg-info"> No se puede editar (Validado) </span>
                        @elseif ($usuarioFinalizado)
                        <span class="badge text-bg-danger"> Periodo Finalizado </span>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Modales para editar datos anuales --}}
        @foreach ($aniosTabs as $year)
        @php
        // Busca el objeto DatoAnual específico para este año para el modal
        $datoAnualParaModal = $indicador->datosAnuales->firstWhere('anio', $year);
        @endphp
        <div class="modal fade" id="editModal-{{ $year }}" tabindex="-1"
            aria-labelledby="editModalLabel-{{ $year }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel-{{ $year }}">Editar Datos Anuales
                            {{ $year }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('indicador.updateAnual', ['id' => $indicador->id, 'year' => $year]) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="modal_valor_dato_{{ $year }}" class="form-label">Resultado/Dato
                                    {{ $year }}</label>
                                <input type="number" step="any"
                                    class="form-control @error('valor_dato', " updateAnualValidation_{$year}") is-invalid @enderror"
                                    name="valor_dato"
                                    value="{{ old('valor_dato', $datoAnualParaModal ? number_format((float) $datoAnualParaModal->valor_dato, 2, '.', '') : '') }}">
                                @error('valor_dato', "updateAnualValidation_{$year}")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="modal_resultados_anual_{{ $year }}" class="form-label">Principales
                                    Resultados ({{ $year }})</label>
                                <textarea
                                    class="form-control @error('resultados_anual', " updateAnualValidation_{$year}") is-invalid @enderror"
                                    name="resultados_anual"
                                    rows="3">{{ old('resultados_anual', $datoAnualParaModal ? $datoAnualParaModal->resultados : '') }}</textarea>
                                @error('resultados_anual', "updateAnualValidation_{$year}")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="modal_observaciones_anual_{{ $year }}"
                                    class="form-label">Consideraciones Generales ({{ $year }})</label>
                                <textarea
                                    class="form-control @error('observaciones_anual', " updateAnualValidation_{$year}") is-invalid @enderror"
                                    name="observaciones_anual"
                                    rows="3">{{ old('observaciones_anual', $datoAnualParaModal ? $datoAnualParaModal->observaciones : '') }}</textarea>
                                @error('observaciones_anual', "updateAnualValidation_{$year}")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="modal_evidencia_anual_{{ $year }}" class="form-label">Documento
                                    Evidencia ({{ $year }})</label>
                                <input type="file"
                                    class="form-control @error('evidencia_anual', " updateAnualValidation_{$year}") is-invalid @enderror"
                                    name="evidencia_anual" accept="application/pdf">
                                @error('evidencia_anual', "updateAnualValidation_{$year}")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @if ($datoAnualParaModal && $datoAnualParaModal->evidencia)
                                <div class="mt-2">
                                    Archivo actual:
                                    <a href="{{ asset('assets-administrador/docs/' . $datoAnualParaModal->evidencia) }}"
                                        target="_blank">
                                        Ver archivo ({{ $datoAnualParaModal->evidencia }})
                                    </a>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox"
                                            name="eliminar_evidencia_anual" value="1"
                                            id="modal_eliminar_evidencia_{{ $year }}">
                                        <label class="form-check-label" for="modal_eliminar_evidencia_{{ $year }}">
                                            Eliminar archivo actual
                                        </label>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label for="modal_fecha_actualizacion_anual_{{ $year }}" class="form-label">Próxima
                                    Fecha de Actualización
                                    ({{ $year }})
                                </label>
                                <input type="date"
                                    class="form-control @error('fecha_actualizacion_anual', " updateAnualValidation_{$year}") is-invalid @enderror"
                                    name="fecha_actualizacion_anual"
                                    value="{{ old('fecha_actualizacion_anual', $datoAnualParaModal && $datoAnualParaModal->fecha_actualizacion ? Carbon\Carbon::parse($datoAnualParaModal->fecha_actualizacion)->format('Y-m-d') : '') }}">
                                @error('fecha_actualizacion_anual', "updateAnualValidation_{$year}")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Activar el modal si hay errores específicos para este año en el request que vuelve --}}
        @if (session("updateAnualValidationErrors_{$year}"))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var editModal = new bootstrap.Modal(document.getElementById('editModal-{{ $year }}'));
                editModal.show();
            });
        </script>
        @endif
        @endforeach

        {{-- Modal para añadir nuevo año --}}
        @can('agregar-dato-anual')
        <div class="modal fade" id="addYearModal" tabindex="-1" aria-labelledby="addYearModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addYearModalLabel">Añadir Nuevo Año al Histórico</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('indicador.storeAnual', $indicador->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="modal_anio" class="form-label">Año <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="1"
                                    class="form-control @error('anio') is-invalid @enderror" name="anio"
                                    id="modal_anio" value="{{ old('anio', date('Y')) }}" required>
                                @error('anio')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="modal_valor_dato" class="form-label">Resultado/Dato</label>
                                <input type="number" step="any"
                                    class="form-control @error('valor_dato') is-invalid @enderror" name="valor_dato"
                                    id="modal_valor_dato" value="{{ old('valor_dato') }}">
                                @error('valor_dato')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="modal_resultados_anual" class="form-label">Principales
                                    Resultados</label>
                                <textarea class="form-control @error('resultados_anual') is-invalid @enderror"
                                    name="resultados_anual" id="modal_resultados_anual"
                                    rows="3">{{ old('resultados_anual') }}</textarea>
                                @error('resultados_anual')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="modal_observaciones_anual" class="form-label">Consideraciones
                                    Generales</label>
                                <textarea class="form-control @error('observaciones_anual') is-invalid @enderror"
                                    name="observaciones_anual" id="modal_observaciones_anual"
                                    rows="3">{{ old('observaciones_anual') }}</textarea>
                                @error('observaciones_anual')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="modal_evidencia_anual" class="form-label">Documento Evidencia
                                    (PDF)</label>
                                <input type="file"
                                    class="form-control @error('evidencia_anual') is-invalid @enderror"
                                    name="evidencia_anual" id="modal_evidencia_anual" accept="application/pdf">
                                @error('evidencia_anual')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="modal_fecha_actualizacion_anual" class="form-label">Próxima Fecha de
                                    Actualización</label>
                                <input type="date"
                                    class="form-control @error('fecha_actualizacion_anual') is-invalid @enderror"
                                    name="fecha_actualizacion_anual" id="modal_fecha_actualizacion_anual"
                                    value="{{ old('fecha_actualizacion_anual') }}">
                                @error('fecha_actualizacion_anual')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
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
        @endcan
        </div>
</x-app-layout>
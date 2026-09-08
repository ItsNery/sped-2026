<x-app-layout>
    @section('title', 'Indicadores: Inicio')
    @section('jss-inicial')
        <link rel="stylesheet" href="{{ asset('vendor/tom-select/tom-select.bootstrap5.min.css') }}">
        <script src="{{ asset('vendor/tom-select/tom-select.complete.min.js') }}" defer></script>
    @endsection
    <x-slot name="header">
        <div class="exec-header admin-index-header">
            <div>
                <span class="exec-eyebrow">Seguimiento operativo</span>
                <h2 class="exec-header__title">Gestión de indicadores</h2>
            </div>
            <span class="exec-header__plan">Consulta y actualización</span>
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
    <div class="admin-index">
        <div class="contenedor-principal admin-index__surface mx-auto">
            <div class="admin-index__heading">
                <div>
                    <span class="exec-eyebrow">Universo registrado</span>
                    <h1>Listado de indicadores</h1>
                </div>
                <span class="admin-index__count">{{ count($indicadores) }} registros</span>
            </div>
            @auth
            @if (auth()->user()->isAdministrator())
            @if (isset($instituciones) && isset($tiposPrograma))
            <section class="admin-index-filter" aria-labelledby="indicator-filter-title">
                <div class="admin-index-filter__heading">
                    <div>
                        <span class="exec-eyebrow">Filtros de consulta</span>
                        <h2 id="indicator-filter-title">Acota el listado</h2>
                    </div>
                    <span>Busca por institución o programa derivado</span>
                </div>
                <div class="admin-index-filter__grid">
                <div class="admin-index-filter__field">
                    <!-- Select de Instituciones -->
                    <label for="institucionSelect" class="label-select">Institución:</label>
                    <select id="institucionSelect" name="institucion" autocomplete="off">
                        <option value="todos">Selecciona una Institución</option>
                        @foreach ($instituciones as $institucion)
                        <option value="{{ $institucion->id }}">{{ $institucion->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-index-filter__field">
                    <!-- Select de Programas -->
                    <label for="programa" class="label-select">Programa Derivado:</label>
                    <select id="programa" name="programa" autocomplete="off">
                        <option value="">Selecciona un Programa</option>
                        @foreach ($tiposPrograma as $programa)
                        <option value="{{ $programa }}">{{ $programa }}</option>
                        @endforeach
                    </select>
                </div>
                </div>
            </section>
            @can('crear-indicador')
            <div class="admin-index__actions">
                <a href="{{ route('panel-indicadores.create') }}" class="button-add-new text-decoration-none">
                    <span class="button__text">Agregar</span>
                    <span class="button__icon">
                        @include('components.svg-add')
                    </span>
                </a>
                @can('subida-masiva-indicador')
                <a href="{{ url('subir-indicadores-masivo') }}" class="button-add-new text-decoration-none">
                    <span class="button__text">Masivo</span>
                    <span class="button__icon">
                        @include('components.svg-add')
                    </span>
                </a>
                @endcan
            </div>
            @endcan
            <div class="table-responsive admin-index-table-wrap" id="contenedor-tabla-indicadores">
                <table id="tabla-indicadores" class="table table-striped table-bordered admin-index-table">
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th>Indicador</th>
                            <th>Institución responsable</th>
                            <th>Programa Derivado</th>
                            <th>Programa</th>
                            <th>Periodicidad</th>
                            <th>Tendencia</th>
                            <th>Año ultimo dato</th>
                            <th>Ultimo dato</th>
                            <th>Avance</th>
                            <th>Fecha Actualización</th>
                            <th>Acciones</th>
                            <th>Semaforo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($indicadores as $indicador)
                        <tr>
                            <td scope="row">
                                {{ $indicador->id }}
                            </td>
                            <td>
                                <a href="{{ route('panel-indicadores.show', $indicador->id) }}">
                                    {{ $indicador->nombre }}
                                </a>
                            </td>
                            <td>{{ $indicador->institucion?->nombre ?? 'Sin institución' }}</td>
                            <td>
                                {{ $indicador->programa_derivado }}
                            </td>
                            <td>
                                {{ $indicador->programa }}
                            </td>
                            <td>
                                {{ $indicador->periodicidad }}
                            </td>
                            <td>
                                {{ $indicador->tendencia }}
                            </td>
                            <td>{{ $indicador->anio_ultimo_dato }}</td>
                            {{-- <td>{{ $indicador->ultimo_dato }}</td> --}}
                            <td>{{ number_format($indicador->ultimo_dato, 2, '.', ',') }}</td>
                            {{-- <th>{{ $indicador->avance }}</th> --}}
                            {{-- <th>{{ number_format($indicador->avance, 2) }}%</th> --}}
                            <th>{{ number_format($indicador->avance, 2, '.', ',') }}%</th>
                            <td>
                                {{ $indicador->fecha_actualizacion }}
                            </td>

                            <td>
                                <div class="admin-index-table-actions" role="group" aria-label="Acciones del indicador">
                                    <!-- botón editar -->
                                    @if ($indicador->indicador_validado == 1)
                                    <span class="admin-index-table-action admin-index-table-action--validated">Validado</span>
                                    @else
                                    <a href="{{ route('panel-indicadores.show', $indicador->id) }}"
                                        class="admin-index-table-action admin-index-table-action--review">
                                        Revisar
                                    </a>
                                    @endif
                                    @can('delete', $indicador)
                                    <!-- botón borrar -->
                                    <form action="{{ route('panel-indicadores.destroy', $indicador) }}"
                                        method="POST" class="formEliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="admin-index-table-action admin-index-table-action--delete">Borrar</button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                            <th>{{ $indicador->semaforizacion }}</th>
                            <td>
                                {{-- Primero, verifica si la colección datosAnuales no está vacía --}}
                                @if ($indicador->datosAnuales && $indicador->datosAnuales->isNotEmpty())
                                {{--
                                                    Luego, verifica si ALGUNO de los registros DatoAnual en la colección
                                                    tiene la propiedad 'modificado' establecida en true (o 1).
                                                    Usamos el método 'contains' de la colección con un callback,
                                                    o el método 'where' para filtrar y luego 'isNotEmpty'.
                                                    --}}
                                @if ($indicador->datosAnuales->where('modificado', true)->isNotEmpty())
                                <span class="badge bg-warning text-dark">Indicador modificado</span>
                                @else
                                <span class="badge bg-success">Sin cambios</span>
                                @endif
                                @else
                                <span class="badge bg-secondary">Sin datos anuales</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
            @elseif(auth()->user()->hasRole('Enlace'))
            <div>
                <section class="admin-index-filter" aria-labelledby="indicator-filter-title">
                    <div class="admin-index-filter__heading">
                        <div>
                            <span class="exec-eyebrow">Filtros de consulta</span>
                            <h2 id="indicator-filter-title">Acota el listado</h2>
                        </div>
                        <span>Busca por institución o programa derivado</span>
                    </div>
                    <div class="admin-index-filter__grid">
                    <div class="admin-index-filter__field">
                        <!-- Select de Instituciones -->
                        <label for="institucionSelect">Institución:</label>
                        <select id="institucionSelect" name="institucion" autocomplete="off">
                            <option value="todos">Selecciona una Institución</option>
                            @foreach ($instituciones as $institucion)
                            <option value="{{ $institucion->id }}">{{ $institucion->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-index-filter__field">
                        <!-- Select de Programas -->
                        <label for="programa">Programa Derivado:</label>
                        <select id="programa" name="programa" autocomplete="off">
                            <option value="">Selecciona un Programa</option>
                            @foreach ($tiposPrograma as $programa)
                            <option value="{{ $programa }}">{{ $programa }}</option>
                            @endforeach
                        </select>
                    </div>
                    </div>
                </section>
                <div class="table-responsive admin-index-table-wrap" id="contenedor-tabla-indicadores">
                    <table id="tabla-indicadores" class="table table-striped table-bordered admin-index-table">
                        <thead>
                            <tr>
                                <th scope="col">No.</th>
                                <th>Indicador</th>
                                <th>Institución responsable</th>
                                <th>Programa Derivado</th>
                                <th>Programa</th>
                                <th>Periodicidad</th>
                                <th>Fecha Actualización</th>
                                <th>Acciones</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($indicadores as $indicador)
                            <tr>
                                <td scope="row">
                                    {{ $indicador->id }}
                                </td>
                                <td>
                                    <a href="{{ route('panel-indicadores.show', $indicador->id) }}">
                                        {{ $indicador->nombre }}
                                    </a>

                                </td>
                                <td>
                                    {{ $indicador->institucion?->nombre ?? 'Sin institución' }}
                                    @if (isset($institucionesDirectas) && !$institucionesDirectas->contains((int) $indicador->id_institucion))
                                        <span class="badge text-bg-light border">Sectorizada</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $indicador->programa_derivado }}
                                </td>
                                <td>
                                    {{ $indicador->programa }}
                                </td>
                                <td>
                                    {{ $indicador->periodicidad }}
                                </td>
                                <td>
                                    {{ $indicador->fecha_actualizacion }}
                                </td>
                                <td>
                                    <div class="admin-index-table-actions" role="group" aria-label="Acciones del indicador">
                                        <!-- botón editar -->
                                        @if ($indicador->indicador_validado == 1)
                                        <span class="admin-index-table-action admin-index-table-action--validated">Validado</span>
                                        @else
                                        <a href="{{ route('panel-indicadores.show', $indicador->id) }}"
                                            class="admin-index-table-action admin-index-table-action--review">
                                            Pendiente
                                        </a>

                                        <!-- botón borrar -->
                                        {{-- @if (auth()->user()->id === 1)
                                                        <form action="{{ route('panel-indicadores.destroy', $indicador->id) }}"
                                        method="POST" class="formEliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button style="color: black" type="submit" class="btn btn-danger">
                                            Borrar
                                        </button>
                                        </form>
                                        @endif --}}
                                        @endif
                                        {{-- <form action="{{ route('panel-indicadores.destroy', $indicador->id) }}"
                                        method="POST" class="formEliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button style="color: black" type="submit"
                                            class="btn btn-danger">Borrar</button>
                                        </form> --}}
                                    </div>
                                </td>
                                {{-- <td>
                                                
                                                @if ($indicador->datosAnuales)
                                                    @if ($indicador->datosAnuales->modificado === 1)
                                                        <span class="badge bg-warning text-dark">Indicador
                                                            modificado</span>
                                                    @else
                                                        <span class="badge bg-success">Sin cambios</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">Sin datos</span>
                                                @endif
                                            </td> --}}
                                <td>
                                    {{-- Primero, verifica si la colección datosAnuales no está vacía --}}
                                    @if ($indicador->datosAnuales && $indicador->datosAnuales->isNotEmpty())
                                    {{--
                                                    Luego, verifica si ALGUNO de los registros DatoAnual en la colección
                                                    tiene la propiedad 'modificado' establecida en true (o 1).
                                                    Usamos el método 'contains' de la colección con un callback,
                                                    o el método 'where' para filtrar y luego 'isNotEmpty'.
                                                    --}}
                                    @if ($indicador->datosAnuales->where('modificado', true)->isNotEmpty())
                                    <span class="badge bg-warning text-dark">Indicador modificado</span>
                                    @else
                                    <span class="badge bg-success">Sin cambios</span>
                                    @endif
                                    @else
                                    <span class="badge bg-secondary">Sin datos anuales</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            @if ($mostrarBotonFinalizar)
            <div class="d-flex justify-content-end me-4 mt-1">
                <button id="finalizarCapturaBtn" class="btn btn-success text-white"><i
                        class="fa-solid fa-floppy-disk"></i> Finalizar Captura</button>
            </div>
            @elseif ($mostrarBotonGenerarReporte)
            <div class="d-flex justify-content-end me-4 mt-1">
                <a href="{{ route('generarReporte', $user->id) }}" class="btn btn-danger text-white"
                    target="_blank">
                    <i class="fa-solid fa-print"></i>
                    Generar Reporte
                </a>
            </div>
            @else
            {{-- No mostrar nada --}}
            @endif

            <div class="table-responsive admin-index-table-wrap mt-2">
                <table id="myTable" class="table table-striped admin-index-table" style="width:100%">
                    <thead>
                        <tr>
                            <td scope="col">No.</td>
                            <th>Indicador</th>
                            <th>Institución responsable</th>
                            <th>Programa Derivado</th>
                            <th>Programa</th>
                            <th>Periodicidad</th>
                            <th>Fecha Actualización</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($indicadores)
                        @foreach ($indicadores as $indicador)
                        <tr>
                            <td scope="row">
                                {{ $indicador->id }}
                            </td>
                            <td>
                                <a href="{{ route('panel-indicadores.show', $indicador->id) }}">
                                    {{ $indicador->nombre }}
                                </a>
                            </td>
                            <td>
                                {{ $indicador->institucion?->nombre ?? 'Sin institución' }}
                                @if (isset($institucionesDirectas) && !$institucionesDirectas->contains((int) $indicador->id_institucion))
                                    <span class="badge text-bg-light border">Sectorizada</span>
                                @endif
                            </td>
                            <td>
                                {{ $indicador->programa_derivado }}
                            </td>
                            <td>
                                {{ $indicador->programa }}
                            </td>
                            <td>
                                {{ $indicador->periodicidad }}
                            </td>
                            <td>
                                {{ $indicador->fecha_actualizacion }}
                            </td>
                            <td>

                                <div class="admin-index-table-actions" role="group" aria-label="Acciones del indicador">
                                    <!-- botón editar -->
                                    @if ($indicador->indicador_validado == 1)
                                    <span class="admin-index-table-action admin-index-table-action--validated">Validado</span>
                                    @elseif($indicador->indicador_validado == null)
                                    <a href="{{ route('panel-indicadores.show', $indicador->id) }}"
                                        class="admin-index-table-action admin-index-table-action--review">
                                        Revisar
                                    </a>
                                    @else
                                    <span class="admin-index-table-action admin-index-table-action--updated">Actualizado</span>
                                    <span class="admin-index-table-action admin-index-table-action--pending">Sin validar</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td scope="col">No.</td>
                            <th>Indicador</th>
                            <th>Institución responsable</th>
                            <th>Programa Derivado</th>
                            <th>Programa</th>
                            <th>Periodicidad</th>
                            <th>Fecha Actualización</th>
                            <th>Acciones</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
            @endauth
        </div>
    </div>

    @push('scripts')
<script>
    (function() {
        'use strict'
        //debemos crear la clase formEliminar dentro del form del boton borrar
        //recordar que cada registro a eliminar esta contenido en un form  
        var forms = document.querySelectorAll('.formEliminar')
        Array.prototype.slice.call(forms)
            .forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    event.preventDefault()
                    event.stopPropagation()
                    Swal.fire({
                        title: '¿Confirma la eliminación del registro?',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#20c997',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Confirmar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                            Swal.fire('¡Eliminado!',
                                'El registro ha sido eliminado exitosamente.', 'success');
                        }
                    })
                }, false)
            })
    })()
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const language = {
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ entradas',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ entradas',
            paginate: { previous: 'Anterior', next: 'Siguiente' }
        };
        const buttons = [
            { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'admin-index-export-button admin-index-export-button--excel' },
            { extend: 'csvHtml5', text: '<i class="fas fa-file-csv"></i> CSV', className: 'admin-index-export-button admin-index-export-button--csv' },
            { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'admin-index-export-button admin-index-export-button--pdf' },
            { extend: 'copy', text: '<i class="fas fa-copy"></i> Copiar', className: 'admin-index-export-button admin-index-export-button--copy' }
        ];

        function initializeTable(selector, stateSave) {
            if (!document.querySelector(selector) || $.fn.DataTable.isDataTable(selector)) return;

            $(selector).DataTable({
                pagingType: 'simple_numbers',
                stateSave: stateSave,
                order: [],
                dom: 'Bfrtip',
                buttons: buttons,
                language: language
            });
        }

        initializeTable('#tabla-indicadores', true);
        initializeTable('#myTable', false);

        const institucionSelect = document.getElementById('institucionSelect');
        const programaSelect = document.getElementById('programa');
        const tableContainer = document.getElementById('contenedor-tabla-indicadores');

        if (!institucionSelect || !programaSelect || !tableContainer) return;

        const storedInstitution = localStorage.getItem('institucionSeleccionada');
        const storedProgram = localStorage.getItem('programaSeleccionado');
        const hasOption = (select, value) => Array.from(select.options).some((option) => option.value === value);

        if (storedInstitution && hasOption(institucionSelect, storedInstitution)) {
            institucionSelect.value = storedInstitution;
        }
        if (storedProgram && hasOption(programaSelect, storedProgram)) {
            programaSelect.value = storedProgram;
        }

        let institutionControl = null;
        let programControl = null;

        if (typeof TomSelect !== 'undefined') {
            const selectSettings = {
                create: false,
                allowEmptyOption: true,
                render: {
                    no_results: () => '<div class="no-results">Sin opciones compatibles</div>'
                }
            };
            institutionControl = new TomSelect(institucionSelect, {
                ...selectSettings,
                placeholder: 'Buscar una institución...'
            });
            programControl = new TomSelect(programaSelect, {
                ...selectSettings,
                placeholder: 'Buscar un programa...'
            });
        }

        let currentRequest;
        let optionsRequest;

        function replaceOptions(select, control, options, selectedValue, fallbackValue) {
            const nextValue = options.some((option) => option.value === selectedValue)
                ? selectedValue
                : fallbackValue;

            if (control) {
                control.clear(true);
                control.clearOptions();
                control.addOptions(options);
                control.setValue(nextValue, true);
                return;
            }

            select.replaceChildren(...options.map((option) => new Option(option.text, option.value)));
            select.value = nextValue;
        }

        function syncDependentOptions(source) {
            if (optionsRequest) optionsRequest.abort();
            optionsRequest = new AbortController();

            const params = new URLSearchParams();
            if (source !== 'programa') params.set('institucion', institucionSelect.value || 'todos');
            if (source !== 'institucion' && programaSelect.value) params.set('programa', programaSelect.value);

            return fetch(@json(route('filtros-indicadores.opciones')) + '?' + params.toString(), {
                headers: { Accept: 'application/json' },
                signal: optionsRequest.signal
            })
                .then((response) => {
                    if (!response.ok) throw new Error('No fue posible actualizar las opciones de filtro.');
                    return response.json();
                })
                .then((data) => {
                    if (source !== 'institucion') {
                        const institutionOptions = [
                            { value: 'todos', text: 'Todas las instituciones' },
                            ...data.instituciones.map((institution) => ({
                                value: String(institution.id),
                                text: institution.nombre
                            }))
                        ];
                        replaceOptions(
                            institucionSelect,
                            institutionControl,
                            institutionOptions,
                            institucionSelect.value,
                            'todos'
                        );
                    }

                    if (source !== 'programa') {
                        const programOptions = [
                            { value: '', text: 'Todos los programas' },
                            ...data.programas.map((program) => ({ value: program, text: program }))
                        ];
                        replaceOptions(
                            programaSelect,
                            programControl,
                            programOptions,
                            programaSelect.value,
                            ''
                        );
                    }

                    return true;
                })
                .catch((error) => {
                    if (error.name !== 'AbortError') console.error(error);
                    return false;
                });
        }

        function refreshIndicators() {
            const institution = institucionSelect.value || 'todos';
            const program = programaSelect.value;
            const url = @json(url('/filtrar-indicadores')) + '/' + encodeURIComponent(institution)
                + (program ? '/' + encodeURIComponent(program) : '');

            localStorage.setItem('institucionSeleccionada', institution);
            localStorage.setItem('programaSeleccionado', program);

            if (currentRequest) currentRequest.abort();
            currentRequest = new AbortController();
            tableContainer.setAttribute('aria-busy', 'true');

            fetch(url, { signal: currentRequest.signal })
                .then((response) => {
                    if (!response.ok) throw new Error('No fue posible actualizar los indicadores.');
                    return response.text();
                })
                .then((html) => {
                    if ($.fn.DataTable.isDataTable('#tabla-indicadores')) {
                        $('#tabla-indicadores').DataTable().destroy();
                    }
                    tableContainer.innerHTML = html;
                    document.getElementById('tabla-indicadores')?.classList.add('admin-index-table');
                    initializeTable('#tabla-indicadores', false);
                })
                .catch((error) => {
                    if (error.name !== 'AbortError') console.error(error);
                })
                .finally(() => tableContainer.removeAttribute('aria-busy'));
        }

        institucionSelect.addEventListener('change', function () {
            syncDependentOptions('institucion').then((updated) => {
                if (updated) refreshIndicators();
            });
        });
        programaSelect.addEventListener('change', function () {
            syncDependentOptions('programa').then((updated) => {
                if (updated) refreshIndicators();
            });
        });

        syncDependentOptions().then(function (updated) {
            if (updated && (institucionSelect.value !== 'todos' || programaSelect.value !== '')) {
                refreshIndicators();
            }
        });
    });
</script>
<script>
    const finalizarCapturaBtn = document.getElementById('finalizarCapturaBtn');

    if (finalizarCapturaBtn) {
        finalizarCapturaBtn.addEventListener('click', function() {
            Swal.fire({
                title: '¿Está seguro?',
                text: "Esta acción finalizará la captura de información de los indicadores. Una vez finalizado no se podrá modificar los datos capturados.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Realizar la solicitud AJAX para finalizar
                    fetch("{{ route('finalizar.captura') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            userId: "{{ auth()->id() }}"
                        })
                    }).then(response => {
                        if (response.ok) {
                            Swal.fire(
                                '¡Finalizado!',
                                'La captura ha sido finalizada.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', 'Ocurrió un problema al finalizar la captura.',
                                'error');
                        }
                    });
                }
            });
        });
    }
</script>
    @endpush
</x-app-layout>

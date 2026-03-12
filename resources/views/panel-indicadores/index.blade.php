<x-app-layout>
    @section('title', 'Indicadores: Inicio')
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            {{ __('Indicadores') }}
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
    <div class="container mx-auto">
        <div class="contenedor-principal mx-auto">
            <div class="encabezado-lista">
                <h2>Listado de Indicadores </h2>
            </div>
            @auth
            @if (auth()->user()->hasRole('Administrador'))
            @if (isset($instituciones) && isset($tiposPrograma))
            <div class="container row py-2">
                <div class="col-md-6">
                    <!-- Select de Instituciones -->
                    <label for="institucionSelect" class="label-select">Institución:</label>
                    <select id="institucionSelect" class="form-control" name="institucion">
                        <option value="todos">Selecciona una Institución</option>
                        @foreach ($instituciones as $institucion)
                        <option value="{{ $institucion->id }}">{{ $institucion->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <!-- Select de Programas -->
                    <label for="programa" class="label-select">Programa Derivado:</label>
                    <select id="programa" name="programa" class="form-control">
                        <option value="">Selecciona un Programa</option>
                        @foreach ($tiposPrograma as $programa)
                        <option value="{{ $programa }}">{{ $programa }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @can('crear-indicador')
            <div class="boton d-flex justify-content-end mx-3 my-3">
                <a href="{{ route('panel-indicadores.create') }}" class="text-decoration-none">
                    <button class="button-add-new" type="button">
                        <span class="button__text">Agregar</span>
                        <span class="button__icon">
                            @include('components.svg-add')
                        </span>
                    </button>
                </a>
                @can('subida-masiva-indicador')
                <a href="{{ url('pruebas-indicadores') }}" class="text-decoration-none">
                    <button class="button-add-new" type="button">
                        <span class="button__text">Masivo</span>
                        <span class="button__icon">
                            @include('components.svg-add')
                        </span>
                    </button>
                </a>
                @endcan
            </div>
            @endcan
            <div class="container table-responsive" id="contenedor-tabla-indicadores">
                <table id="tabla-indicadores" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th>Indicador</th>
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
                                <div class="d-flex justify-content-center rounded-lg text-lg"
                                    role="group">
                                    <!-- botón editar -->
                                    @if ($indicador->indicador_validado == 1)
                                    <span class="badge text-bg-success"> Validado </span>
                                    @else
                                    <a href="{{ route('panel-indicadores.show', $indicador->id) }}"
                                        class="badge text-bg-warning py-2">
                                        Revisar
                                    </a>
                                    @endif
                                    @can('borrar-indicador')
                                    <!-- botón borrar -->
                                    <form action="{{ route('panel-indicadores.destroy', $indicador) }}"
                                        method="POST" class="formEliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button style="color: black" type="submit"
                                            class="badge text-bg-danger">Borrar</button>
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
            <div class="container table-responsive">
                <div class="container row py-2">
                    <div class="col-6">
                        <!-- Select de Instituciones -->
                        <label for="institucion">Institución:</label>
                        <select id="institucionSelect" class="form-control" name="institucion">
                            <option value="todos">Selecciona una Institución</option>
                            @foreach ($instituciones as $institucion)
                            <option value="{{ $institucion->id }}">{{ $institucion->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <!-- Select de Programas -->
                        <label for="programa">Programa Derivado:</label>
                        <select id="programa" name="programa" class="form-control">
                            <option value="">Selecciona un Programa</option>
                            @foreach ($tiposPrograma as $programa)
                            <option value="{{ $programa }}">{{ $programa }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="container" id="contenedor-tabla-indicadores">
                    <table id="tabla-indicadores" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">No.</th>
                                <th>Indicador</th>
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
                                    <div class="flex justify-center rounded-lg text-lg" role="group">
                                        <!-- botón editar -->
                                        @if ($indicador->indicador_validado == 1)
                                        <span class="badge text-bg-success"> Validado </span>
                                        @else
                                        <a href="{{ route('panel-indicadores.show', $indicador->id) }}"
                                            class="">
                                            <button class="badge text-bg-warning">
                                                Pendiente
                                            </button>
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

            <div class="container table-responsive mt-2">
                <table id="myTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <td scope="col">No.</td>
                            <th>Indicador</th>
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

                                <div class="flex justify-center rounded-lg text-lg" role="group">
                                    <!-- botón editar -->
                                    @if ($indicador->indicador_validado == 1)
                                    <span class="badge text-bg-success"> Validado </span>
                                    @elseif($indicador->indicador_validado == null)
                                    <a href="{{ route('panel-indicadores.show', $indicador->id) }}"
                                        class="">
                                        <button class="badge text-bg-warning">
                                            Revisar
                                        </button>
                                    </a>
                                    @else
                                    <span class="badge text-bg-warning"> Actualizado </span>
                                    <span class="badge text-bg-info"> Sin Validar </span>
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

</x-app-layout>

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
    $(document).ready(function() {
        $('#tabla-indicadores').DataTable({
            "pagingType": "simple_numbers",
            stateSave: true,
            "order": [],
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success'
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'btn btn-primary'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger'
                },
                {
                    extend: 'copy',
                    text: '<i class="fas fa-copy"></i> Copiar',
                    className: 'btn btn-info'
                }
            ],

            "language": {
                "search": "Buscar:",
                "lengthMenu": "Mostrar _MENU_ entradas",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                "paginate": {
                    "previous": "Anterior",
                    "next": "Siguiente"
                }
            }
        });
        const institucionSelect = document.getElementById("institucionSelect");
        const programaSelect = document.getElementById("programa");

        // Función para guardar filtros en localStorage
        function guardarFiltros() {
            localStorage.setItem("institucionSeleccionada", institucionSelect.value);
            localStorage.setItem("programaSeleccionado", programaSelect.value);
        }

        // Función para recuperar y aplicar filtros guardados
        function aplicarFiltrosGuardados() {
            let institucionGuardada = localStorage.getItem("institucionSeleccionada");
            let programaGuardado = localStorage.getItem("programaSeleccionado");

            if (institucionGuardada) {
                institucionSelect.value = institucionGuardada;
            }
            if (programaGuardado) {
                programaSelect.value = programaGuardado;
            }

            if (institucionGuardada !== "todos" || programaGuardado !== "") {
                // Disparar manualmente los eventos de cambio para aplicar los filtros
                institucionSelect.dispatchEvent(new Event("change"));
                programaSelect.dispatchEvent(new Event("change"));
            }
        }

        // Guardar filtros cuando el usuario cambia los selects
        institucionSelect.addEventListener("change", () => {
            guardarFiltros();
        });

        programaSelect.addEventListener("change", () => {
            guardarFiltros();
        });

        // Aplicar filtros guardados al cargar la página
        aplicarFiltrosGuardados();
    });
    $(document).ready(function() {
        $('#myTable').DataTable({
            "pagingType": "simple_numbers",
            "order": [],
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success'
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'btn btn-primary'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger'
                },
                {
                    extend: 'copy',
                    className: 'btn btn-info',
                    text: '<i class="fas fa-copy"></i> Copiar',
                }
            ],
            "language": {
                "search": "Buscar:",
                "lengthMenu": "Mostrar _MENU_ entradas",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                "paginate": {
                    "previous": "Anterior",
                    "next": "Siguiente"
                }
            }
        });
    });

    const institucionSelect = document.getElementById("institucionSelect")
    const contenedorTablaIndicadores = document.getElementById("contenedor-tabla-indicadores")
    const programa = document.getElementById("programa")

    function selectIndicadores(url) {
        fetch(url)
            .then((response) => {
                if (response.ok) {
                    return response.text()
                }
            })
            .then((data) => {
                // contenedorTablaIndicadores.innerHTML = data
                // Destruye DataTables si ya está inicializado
                if ($.fn.DataTable.isDataTable('#tabla-indicadores')) {
                    $('#tabla-indicadores').DataTable().destroy();
                }

                // Reemplaza el contenido de la tabla
                contenedorTablaIndicadores.innerHTML = data;

                // Inicializa DataTables nuevamente
                $('#tabla-indicadores').DataTable({
                    paging: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    language: {
                        "search": "Buscar:",
                        "lengthMenu": "Mostrar _MENU_ entradas",
                        "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                        "paginate": {
                            "previous": "Anterior",
                            "next": "Siguiente"
                        }
                    },
                    dom: 'Bfrtip',
                    buttons: [{
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel"></i> Excel',
                            className: 'btn btn-success',
                            filename: 'indicadores_excel'
                        },
                        {
                            extend: 'csv',
                            className: 'btn btn-success',
                            text: '<i class="fas fa-file-csv"></i> CSV',
                            className: 'btn btn-primary',
                            filename: 'indicadores_csv'
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fas fa-file-pdf"></i> PDF',
                            className: 'btn btn-danger',
                            filename: 'indicadores_pdf'
                        },
                        {
                            extend: 'copy',
                            className: 'btn btn-info',
                            text: '<i class="fas fa-copy"></i> Copiar',
                        }
                    ]
                });
            })
            .catch((err) => {
                console.err(err)
            })
    }

    institucionSelect.addEventListener("change", (e) => {
        let idInstitucion = e.target.value
        let url = `filtrar-indicadores/${idInstitucion}`
        selectIndicadores(url)
    })

    programa.addEventListener("change", (e) => {
        let idInstitucion = institucionSelect.value
        let programa = e.target.value

        if (idInstitucion != '') {
            let url = `filtrar-indicadores/${idInstitucion}/${programa}`
            selectIndicadores(url)
        } else {
            // Si no se selecciona una institución, muestra una alerta
            Swal.fire({
                icon: 'warning',
                title: '¡Atención!',
                text: 'Por favor, selecciona una institución primero.',
                confirmButtonText: 'Entendido'
            }).then(() => {
                institucionSelect.focus(); // Enfoca el select de institución
            });
            // console.log("selecciona institución")
            // institucionSelect.focus()
        }
    })
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
                            userId: "{{auth() -> id()}}"
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
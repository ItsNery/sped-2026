<x-indicador-municipal-layout>
    @section('title', 'Administración Indicadores Municipales: Inicio')
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('Inicio') }}
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
    @if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: '{{ session('
            error ') }}',
            confirmButtonText: 'Aceptar'
        });
    </script>
    @endif
    <div class="container contenedor-tarjetas">
        <div class="contenedor-principal mx-auto">
            <div class="encabezado-lista">
                <h2>Listado de Indicadores </h2>
            </div>
            @can('crear-indicador-municipal')
            <div class="boton d-flex justify-content-end mx-3 my-2">
                <a href="{{ route('panel-indicadores-municipales.create') }}" class="btn btn-success text-white">
                    <i class="fa-solid fa-plus"></i> Subir nuevo
                </a>
            </div>
            @endcan
            <div class="container table-responsive" id="contenedor-tabla-indicadores">
                <table id="tabla-indicadores" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Indicador</th>
                            <th>Instrumento</th>
                            <th>Eje</th>
                            <th>Dependencia</th>
                            <th>2019</th>
                            <th>2020</th>
                            <th>2021</th>
                            <th>2022</th>
                            <th>2023</th>
                            <th>2024</th>
                            <th>2025</th>
                            <th>Meta 2027</th>
                            <th>Periodicidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($indicadores as $indicador)
                        <tr>
                            <td>
                                <a href="{{ route('panel-indicadores-municipales.show', $indicador->id) }}">
                                    {{ $indicador->indicador }}
                                </a>
                                {{-- {{ $indicador->indicador }} --}}
                            </td>
                            <td>
                                {{ $indicador->instrumento }}
                            </td>
                            <td>
                                {{ $indicador->eje_indicador }}
                            </td>
                            <td>
                                {{ $indicador->dependencia }}
                            </td>
                            <td>
                                {{ $indicador->valoresPorAño['dato_2019'] ?? 'N/D' }}
                            </td>
                            <td>
                                {{ $indicador->valoresPorAño['dato_2020'] ?? 'N/D' }}
                            </td>
                            <td>
                                {{ $indicador->valoresPorAño['dato_2021'] ?? 'N/D' }}
                            </td>
                            <td>
                                {{ $indicador->valoresPorAño['dato_2022'] ?? 'N/D' }}
                            </td>
                            <td>
                                {{ $indicador->valoresPorAño['dato_2023'] ?? 'N/D' }}
                            </td>
                            <td>
                                {{ $indicador->valoresPorAño['dato_2024'] ?? 'N/D' }}
                            </td>
                            <td>
                                {{ $indicador->valoresPorAño['dato_2025'] ?? 'N/D' }}
                            </td>
                            <td>
                                {{ $indicador->meta_2024 }}
                            </td>
                            <td>
                                {{ $indicador->periodicidad->nombre }}
                            </td>
                            <td>
                                <div class="d-flex justify-content-center align-items-center flex-column gap-2 rounded-lg text-lg"
                                    role="group">
                                    <!-- Estado del indicador -->
                                    @if ($indicador->validado == 1)
                                    <span class="badge bg-success px-3 py-2">Validado</span>
                                    @else
                                    <span class="badge bg-warning text-dark px-3 py-2">Revisión Pendiente</span>
                                    <a href="{{ route('panel-indicadores-municipales.show', $indicador->id) }}"
                                        class="badge bg-warning btn-sm text-dark" title="Revisar">
                                        <i class="bi bi-pencil-square"></i> Revisar
                                    </a>
                                    @endif

                                    <!-- Botón Borrar -->
                                    @can('borrar-indicador-municipal')
                                    <form
                                        action="{{ route('panel-indicadores-municipales.destroy', $indicador->id) }}"
                                        method="POST" class="m-0 p-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="badge bg-danger btn-sm btn-eliminar"
                                            title="Eliminar">
                                            <i class="bi bi-trash"></i> Borrar
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Indicador</th>
                            <th>Instrumento</th>
                            <th>Eje</th>
                            <th>Dependencia</th>
                            <th>2019</th>
                            <th>2020</th>
                            <th>2021</th>
                            <th>2022</th>
                            <th>2023</th>
                            <th>2024</th>
                            <th>2025</th>
                            <th>Meta 2027</th>
                            <th>Periodicidad</th>
                            <th>Acciones</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <script>
        (function() {
            'use strict'
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
                "order": [],
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-regular fa-file-excel"></i>',
                        title: 'Reporte de los Indicadores de {{ $municipio_nombre }} ',
                        className: 'btn btn-success',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        },
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fa-solid fa-file-csv"></i>',
                        title: 'Reporte de los Indicadores de {{ $municipio_nombre }} ',
                        className: 'btn btn-primary',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        },
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fa-regular fa-file-pdf"></i>',
                        title: 'Reporte de los Indicadores de {{ $municipio_nombre }} ',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        },
                        className: 'btn btn-danger',
                        customize: function(doc) {
                            doc.footer = function(currentPage, pageCount) {
                                return {
                                    text: `Página ${currentPage} de ${pageCount}`,
                                    alignment: 'center',
                                    margin: [0, 10, 0, 0]
                                };
                            };
                        }
                    },
                    {
                        extend: 'copyHtml5',
                        className: 'btn btn-info',
                        text: '<i class="fa-regular fa-copy"></i>',
                        exportOptions: {
                            columns: ':not(:last-child)' // Excluye la última columna (Acciones)
                        }
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
        document.querySelectorAll('.btn-eliminar').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // Evitar envío inmediato del formulario

                Swal.fire({
                    title: '¿Estás seguro de eliminar este indicador?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, borrar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Enviar el formulario si el usuario confirma
                        this.closest('form').submit();
                    }
                });
            });
        });
    </script>
</x-indicador-municipal-layout>
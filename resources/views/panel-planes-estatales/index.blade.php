<x-app-layout>
    @section('title', 'Planes Estatales')
    <x-slot name="header">
        <div class="exec-header admin-index-header">
            <div>
                <span class="exec-eyebrow">Catálogo de planeación</span>
                <h2 class="exec-header__title">Gestión de planes estatales</h2>
            </div>
            <span class="exec-header__plan">Plan de Desarrollo del Estado</span>
        </div>
    </x-slot>
    @if ($message = Session::get('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: '{{ $message }}'
            })
        </script>
    @endif
    <div class="admin-index">
        <div class="contenedor-principal admin-index__surface mx-auto">
            <div class="admin-index__heading">
                <div>
                    <span class="exec-eyebrow">Universo registrado</span>
                    <h1>Listado de planes estatales</h1>
                </div>
                <span class="admin-index__count">{{ count($planes) }} registros</span>
            </div>

            <div class="admin-index__actions">
                <a href="{{ route('panel-cat-planes.create') }}" class="button-add-new text-decoration-none">
                    <span class="button__text">Agregar</span>
                    <span class="button__icon">
                        @include('components.svg-add')
                    </span>
                </a>
            </div>

            <div class="table-responsive admin-index-table-wrap">
                <table class="table table-striped table-bordered admin-index-table" id="table-planes" style="width:100%">
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Gobernador</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($planes as $plan)
                            <tr>
                                <td>{{ $plan->nombre }}</td>
                                <td>{{ $plan->gobernador }}</td>
                                <td class="text-center">
                                    <div class="admin-index-table-actions" role="group" aria-label="Acciones del plan">
                                        <a href="{{ route('panel-cat-planes.edit', $plan->id) }}"
                                            class="admin-index-table-action admin-index-table-action--edit"
                                            title="Editar">
                                            <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                                            <span class="visually-hidden">Editar</span>
                                        </a>
                                        <form action="{{ route('panel-cat-planes.destroy', $plan->id) }}"
                                            method="POST" class="confirmable-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="admin-index-table-action admin-index-table-action--delete"
                                                data-action="borrar" title="Eliminar">
                                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                <span class="visually-hidden">Eliminar</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
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

                $('#table-planes').DataTable({
                    pagingType: 'simple_numbers',
                    order: [],
                    dom: 'Bfrtip',
                    buttons: buttons,
                    language: language
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.confirmable-form').forEach((form) => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();

                        Swal.fire({
                            title: '¿Estás seguro de eliminar este plan?',
                            text: '¡No se podrán revertir los cambios!',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Sí, eliminar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>

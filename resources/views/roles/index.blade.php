<x-app-layout>
    @section('title', 'Roles')
    <x-slot name="header">
        <div class="exec-header admin-index-header">
            <div>
                <span class="exec-eyebrow">Administración del sistema</span>
                <h2 class="exec-header__title">Gestión de roles</h2>
            </div>
            <span class="exec-header__plan">Permisos y perfiles</span>
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
                    <h1>Listado de roles</h1>
                </div>
                <span class="admin-index__count">{{ count($roles) }} registros</span>
            </div>

            @can('crear-rol')
                <div class="admin-index__actions">
                    <a href="{{ route('panel-roles.create') }}" class="button-add-new text-decoration-none">
                        <span class="button__text">Agregar</span>
                        <span class="button__icon">
                            @include('components.svg-add')
                        </span>
                    </a>
                </div>
            @endcan

            <div class="table-responsive admin-index-table-wrap">
                <table class="table table-striped table-bordered admin-index-table" id="table-roles" style="width:100%">
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td class="text-center">
                                    <div class="admin-index-table-actions" role="group" aria-label="Acciones del rol">
                                        @can('editar-rol')
                                            <a href="{{ route('panel-roles.edit', $role->id) }}"
                                                class="admin-index-table-action admin-index-table-action--edit"
                                                title="Editar">
                                                <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                                                <span class="visually-hidden">Editar</span>
                                            </a>
                                        @endcan
                                        @can('borrar-rol')
                                            <form action="{{ route('panel-roles.destroy', $role->id) }}" method="POST"
                                                class="confirmable-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="admin-index-table-action admin-index-table-action--delete"
                                                    data-action="borrar" title="Eliminar">
                                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                    <span class="visually-hidden">Eliminar</span>
                                                </button>
                                            </form>
                                        @endcan
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

                $('#table-roles').DataTable({
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
                            title: '¿Estás seguro de eliminar este rol?',
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

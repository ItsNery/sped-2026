<x-app-layout>
    @section('title', 'Usuarios')
    <x-slot name="header">
        <div class="exec-header admin-index-header">
            <div>
                <span class="exec-eyebrow">Administración del sistema</span>
                <h2 class="exec-header__title">Gestión de usuarios</h2>
            </div>
            <span class="exec-header__plan">Usuarios y permisos</span>
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
                    <h1>Listado de usuarios</h1>
                </div>
                <span class="admin-index__count">{{ count($users) }} registros</span>
            </div>

            @can('crear-usuario')
                <div class="admin-index__actions">
                    <a href="{{ route('panel-usuarios.create') }}" class="button-add-new text-decoration-none">
                        <span class="button__text">Agregar</span>
                        <span class="button__icon">
                            @include('components.svg-add')
                        </span>
                    </a>
                </div>
            @endcan

            <div class="table-responsive admin-index-table-wrap">
                <table class="table table-striped table-bordered admin-index-table" id="table-users" style="width:100%">
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Correo</th>
                            <th scope="col">Rol</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if (!empty($user->getRoleNames()))
                                        @foreach ($user->getRoleNames() as $rolName)
                                            <span class="admin-index-role">{{ $rolName }}</span>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="admin-index-table-actions" role="group" aria-label="Acciones del usuario">
                                        @can('editar-usuario')
                                            <a href="{{ route('panel-usuarios.edit', $user->id) }}"
                                                class="admin-index-table-action admin-index-table-action--edit"
                                                title="Editar">
                                                <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                                                <span class="visually-hidden">Editar</span>
                                            </a>
                                        @endcan

                                        @if (!$user->isSystemAccount())
                                            @can('des-activar-usuario')
                                                @if ($user->is_active)
                                                    <form action="{{ route('users.deactivate', $user->id) }}" method="POST"
                                                        class="confirmable-form">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="admin-index-table-action admin-index-table-action--delete"
                                                            data-action="desactivar" title="Desactivar">
                                                            <i class="fa-solid fa-toggle-off" aria-hidden="true"></i>
                                                            <span class="visually-hidden">Desactivar</span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('users.activate', $user->id) }}" method="POST"
                                                        class="confirmable-form">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="admin-index-table-action admin-index-table-action--validated"
                                                            data-action="activar" title="Activar">
                                                            <i class="fa-solid fa-toggle-on" aria-hidden="true"></i>
                                                            <span class="visually-hidden">Activar</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan

                                            @can('borrar-usuario')
                                                <form action="{{ route('panel-usuarios.destroy', $user->id) }}" method="POST"
                                                    class="confirmable-form" id="form-delete-{{ $user->id }}">
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
                                        @endif
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

                $('#table-users').DataTable({
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

                        let actionButton = e.submitter;
                        let action = '';

                        if (actionButton && actionButton.hasAttribute('data-action')) {
                            action = actionButton.getAttribute('data-action');
                        } else {
                            const buttonInsideForm = form.querySelector('button[data-action]');
                            if (buttonInsideForm) {
                                action = buttonInsideForm.getAttribute('data-action');
                            }
                        }

                        let title, textMessage, iconType, confirmButtonColor, confirmButtonTextValue;

                        if (action === 'desactivar') {
                            title = '¿Estás seguro de desactivar este usuario?';
                            textMessage = 'El usuario no podrá acceder hasta que sea reactivado.';
                            iconType = 'warning';
                            confirmButtonColor = '#3085d6';
                            confirmButtonTextValue = 'Sí, desactivar';
                        } else if (action === 'activar') {
                            title = '¿Estás seguro de activar este usuario?';
                            textMessage = 'El usuario podrá acceder a su cuenta nuevamente.';
                            iconType = 'warning';
                            confirmButtonColor = '#3085d6';
                            confirmButtonTextValue = 'Sí, activar';
                        } else if (action === 'borrar') {
                            title = '¿Estás seguro de eliminar este usuario?';
                            textMessage = '¡Esta acción no se puede deshacer! El usuario será eliminado permanentemente.';
                            iconType = 'error';
                            confirmButtonColor = '#d33';
                            confirmButtonTextValue = 'Sí, eliminar';
                        } else {
                            console.warn('Acción desconocida o no especificada en data-action:', action);
                            return;
                        }

                        Swal.fire({
                            title: title,
                            text: textMessage,
                            icon: iconType,
                            showCancelButton: true,
                            confirmButtonColor: confirmButtonColor,
                            cancelButtonColor: (action === 'borrar' ? '#3085d6' : '#d33'),
                            confirmButtonText: confirmButtonTextValue,
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

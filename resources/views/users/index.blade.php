<x-app-layout>
    @section('title', 'Usuarios')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Usuarios
        </h2>
    </x-slot>
    @if ($message = Session::get('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: '{{ $message }}'
            })
        </script>
    @endif
    <div class="container py-12 mx-auto">
        <div class="contenedor-principal mx-auto">
            <div class="encabezado-lista my-2">
                <h2>Gestión de usuarios</h2>
            </div>
            @can('crear-usuario')
                <div class="d-flex justify-content-end mx-4">
                    <a href="{{ route('panel-usuarios.create') }}" class="text-decoration-none">
                        <button class="button-add-new" type="button" data-bs-toggle="modal"
                            data-bs-target="#modalSliderInicio" data-action="create">
                            <span class="button__text">Agregar</span>
                            <span class="button__icon"><svg class="svg" fill="none" height="24"
                                    stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                    <line x1="12" x2="12" y1="5" y2="19"></line>
                                    <line x1="5" x2="19" y1="12" y2="12"></line>
                                </svg></span>
                        </button>
                    </a>
                </div>
            @endcan
            {{-- <div class="row align-items-center py-2">
                <div class="col-md-12 text-md-end text-start">
                    @can('crear-usuario')
                        <a class="btn btn-primary my-2 mx-2 text-white" href="{{ route('panel-usuarios.create') }}"><i
                                class="fa-solid fa-plus"></i> Crear usuario</a>
                    @endcan
                </div>
            </div> --}}
            <div class="container table-responsive">
                <table class="table table-striped table-users" id="table-users">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th class="text-center">Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $key => $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if (!empty($user->getRoleNames()))
                                        @foreach ($user->getRoleNames() as $rolName)
                                            <span class="badge text-bg-warning">{{ $rolName }}</span>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="text-center">
                                    @can('editar-usuario')
                                        <a class="btn btn-warning btn-sm text-black" title="Editar"
                                            href="{{ route('panel-usuarios.edit', $user->id) }}"><i
                                                class="fa-regular fa-pen-to-square"></i> </a>
                                    @endcan
                                    {{-- @can('des-activar-usuario')
                                        @if ($user->is_active)
                                            <form action="{{ route('users.deactivate', $user->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-danger btn-sm text-white">
                                                    <i class="fa-solid fa-toggle-off"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('users.activate', $user->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-success btn-sm text-white">
                                                    <i class="fa-solid fa-toggle-on"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan --}}
                                    @if ($user->id != 1)
                                        @can('des-activar-usuario')
                                            @if ($user->is_active)
                                                <form action="{{ route('users.deactivate', $user->id) }}" method="POST"
                                                    class="d-inline confirmable-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-danger btn-sm text-white"
                                                        data-action="desactivar" title="Desactivar">
                                                        <i class="fa-solid fa-toggle-off"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('users.activate', $user->id) }}" method="POST"
                                                    class="d-inline confirmable-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-success btn-sm text-white"
                                                        data-action="activar" title="Activar">
                                                        <i class="fa-solid fa-toggle-on"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    @endif

                                    @can('borrar-usuario')
                                        @if ($user->id != 1)
                                            <form action="{{ route('panel-usuarios.destroy', $user->id) }}" method="POST"
                                                class="d-inline confirmable-form" id="form-delete-{{ $user->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" data-action="borrar"><i
                                                        class="fa-solid fa-trash-can" style="color: white;"></i></button>

                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</x-app-layout>
<script>
    $(document).ready(function() {
        $('#table-users').DataTable({
            "pagingType": "simple_numbers",
            "order": [],
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
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".confirmable-form").forEach((form) => {
            form.addEventListener("submit", function(e) {
                e.preventDefault(); // Prevenir el envío automático del formulario

                // Intentamos obtener el botón que fue clickeado si es un submit button
                // o el primer botón con data-action dentro del formulario.
                let actionButton = e.submitter; // El elemento que causó el submit
                let action = '';

                if (actionButton && actionButton.hasAttribute('data-action')) {
                    action = actionButton.getAttribute("data-action");
                } else {
                    // Fallback por si el submit no fue por un botón con data-action (ej. Enter en un input)
                    // o si e.submitter no está disponible/no es el botón esperado.
                    const buttonInsideForm = form.querySelector("button[data-action]");
                    if (buttonInsideForm) {
                        action = buttonInsideForm.getAttribute("data-action");
                    }
                }

                let title, textMessage, iconType, confirmButtonColor, confirmButtonTextValue;

                if (action === "desactivar") {
                    title = "¿Estás seguro de desactivar este usuario?";
                    textMessage = "El usuario no podrá acceder hasta que sea reactivado.";
                    iconType = "warning";
                    confirmButtonColor = "#3085d6";
                    confirmButtonTextValue = "Sí, desactivar";
                } else if (action === "activar") {
                    title = "¿Estás seguro de activar este usuario?";
                    textMessage = "El usuario podrá acceder a su cuenta nuevamente.";
                    iconType = "warning";
                    confirmButtonColor = "#3085d6";
                    confirmButtonTextValue = "Sí, activar";
                } else if (action === "borrar") { // Nuevo caso para "borrar"
                    title = "¿Estás seguro de eliminar este usuario?";
                    textMessage =
                        "¡Esta acción no se puede deshacer! El usuario será eliminado permanentemente.";
                    iconType = "error"; // "warning" o "error" son buenas opciones para borrar
                    confirmButtonColor = "#d33"; // Rojo para la acción de borrar
                    confirmButtonTextValue = "Sí, eliminar";
                } else {
                    // Si la acción no se reconoce, podrías enviar el formulario directamente o mostrar un error.
                    // Por seguridad, no haremos nada o mostraremos un error genérico.
                    console.warn("Acción desconocida o no especificada en data-action:",
                    action);
                    // Opcionalmente, enviar el formulario si es una acción no cubierta por SweetAlert:
                    // form.submit();
                    return; // No mostrar SweetAlert si la acción no está definida
                }

                Swal.fire({
                    title: title,
                    text: textMessage,
                    icon: iconType,
                    showCancelButton: true,
                    confirmButtonColor: confirmButtonColor,
                    cancelButtonColor: (action === "borrar" ? "#3085d6" :
                    "#d33"), // Color del botón cancelar
                    confirmButtonText: confirmButtonTextValue,
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Si el usuario confirma, enviar el formulario
                    }
                });
            });
        });
    });
</script>

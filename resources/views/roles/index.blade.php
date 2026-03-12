<x-app-layout>
    @section('title', 'Roles')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Roles') }}
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
    <div class="contenedor-principal">
        <div class="encabezado-lista my-2">
            <h2>Gestión de Roles</h2>
        </div>
        @can('crear-rol')
            <div class="d-flex justify-content-end mx-4 my-4">
                <a href="{{ route('panel-roles.create') }}" class="text-decoration-none">
                    <button class="button-add-new" type="button" data-bs-toggle="modal" data-bs-target="#modalSliderInicio"
                        data-action="create">
                        <span class="button__text">Agregar</span>
                        <span class="button__icon"><svg class="svg" fill="none" height="24" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                width="24" xmlns="http://www.w3.org/2000/svg">
                                <line x1="12" x2="12" y1="5" y2="19"></line>
                                <line x1="5" x2="19" y1="12" y2="12"></line>
                            </svg></span>
                    </button>
                </a>
            </div>
        @endcan
        <div class="container table-responsive">
            {{-- <div class="row">
                <div class="col-12 mb-3">
                    <div class="float-end my-2">
                        @can('crear-rol')
                            <a class="btn btn-primary text-white" href="{{ route('panel-roles.create') }}"><i class="fa-solid fa-plus"></i> Crear rol</a>
                        @endcan
                    </div>
                </div>
            </div> --}}

            <table class="table table-striped table-users" id="table-roles">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th class="text-center">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td class="text-center">
                                @can('editar-rol')
                                    <a class="btn btn-warning btn-sm text-black" title="Editar"
                                        href="{{ route('panel-roles.edit', $role->id) }}">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                @endcan
                                @can('borrar-rol')
                                    <form action="{{ route('panel-roles.destroy', $role->id) }}" method="POST"
                                        class="d-inline" id="form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm text-white" title="Eliminar">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</x-app-layout>
<script>
    $(document).ready(function() {
        $('#table-roles').DataTable({
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
    document.addEventListener("submit", (e) => {
        e.preventDefault()
        Swal.fire({
            title: "¿Estás seguro de eliminarlo?",
            text: "¡No se podrán revertir los cambios!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "¡Si, borralo!",
        }).then((result) => {
            if (result.isConfirmed) {
                e.target.submit()
            }
        })
    })
</script>

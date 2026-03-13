<x-app-layout>
    @section('title', 'Planes Estatales')
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            {{ __('Planes Estatales') }}
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
            <h2>Gestión de Planes Estatales</h2>
        </div>
        <div class="d-flex justify-content-end mx-4 my-4">
            <a href="{{ route('panel-cat-planes.create') }}" class="text-decoration-none">
                <button class="button-add-new" type="button">
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
        <div class="container table-responsive">
            <table class="table table-striped table-users" id="table-planes">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Gobernador</th>
                        <th class="text-center">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($planes as $plan)
                        <tr>
                            <td>{{ $plan->nombre }}</td>
                            <td>{{ $plan->gobernador }}</td>
                            <td class="text-center">
                                <a class="btn btn-warning btn-sm text-black" title="Editar"
                                    href="{{ route('panel-cat-planes.edit', $plan->id) }}">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('panel-cat-planes.destroy', $plan->id) }}" method="POST"
                                    class="d-inline" id="form-delete-{{ $plan->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm text-white" title="Eliminar">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
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
        $('#table-planes').DataTable({
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

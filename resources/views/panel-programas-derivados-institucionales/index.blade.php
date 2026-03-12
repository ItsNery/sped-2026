<x-app-layout>
    @section('title', 'Programas Derivados Institucionales')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Programas Derivados Institucionales') }}
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
            <h2>Gestión de Programas Derivados Institucionales</h2>
        </div>
        <div class="d-flex justify-content-end mx-4 my-4">
            <a href="{{ route('panel-cat-prog-der-instit.create') }}" class="text-decoration-none">
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
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Plan Estatal</th>
                        <th>Color</th>
                        <th>Imagen</th>
                        <th class="text-center">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($programas as $programa)
                        <tr>
                            <td> {{ $programa->id }}</td>
                            <td>{{ $programa->nombre }}</td>
                            <td>
                                {{ $programa->catPlanEstatalDesarrollo->nombre ?? 'N/A' }}
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded border border-gray-200 mr-2"
                                        style="background-color: {{ $programa->color }};"></div>
                                    <span>{{ $programa->color }}</span>
                                </div>
                            </td>
                            <td>
                                @if ($programa->imagen)
                                    <img src="{{ asset($programa->imagen) }}" alt="Imagen"
                                        class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <span class="text-gray-400">Sin imagen</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a class="btn btn-warning btn-sm text-black" title="Editar"
                                    href="{{ route('panel-cat-prog-der-instit.edit', $programa->id) }}">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('panel-cat-prog-der-instit.destroy', $programa->id) }}"
                                    method="POST" class="d-inline" id="form-delete-{{ $programa->id }}">
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

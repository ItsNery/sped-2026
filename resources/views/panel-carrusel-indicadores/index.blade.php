<x-app-layout>
    @section('title', 'Carrusel de Indicadores')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Carrusel') }}
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
    <link rel="stylesheet" href="{{ asset('css/choices.min.css') }}">
    <script src="{{ asset('js/choices.min.js') }}"></script>
    <div class="container mx-auto">
        <div class="contenedor-principal mx-auto">
            <div class="encabezado-lista my-2">
                <h2>Carrusel de indicadores</h2>
            </div>
            @can('crear-ind-carrusel')
                <div class="d-flex justify-content-end mx-4 mb-3">
                    <button class="button-add-new" type="button" data-bs-toggle="modal" data-bs-target="#modalIndicador"
                        data-action="create">
                        <span class="button__text">Agregar</span>
                        <span class="button__icon"><svg class="svg" fill="none" height="24" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                width="24" xmlns="http://www.w3.org/2000/svg">
                                <line x1="12" x2="12" y1="5" y2="19"></line>
                                <line x1="5" x2="19" y1="12" y2="12"></line>
                            </svg></span>
                    </button>
                    {{-- <button class="btn btn-success text-white" data-bs-toggle="modal" data-bs-target="#modalIndicador"
                        data-action="create">
                        <i class="fa-solid fa-plus"></i> Subir Nuevo
                    </button> --}}
                </div>
            @endcan
            <div class="container table-responsive">
                <table class="table table-striped" id="myTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre del Indicador</th>
                            <th>Miniatura</th>
                            <th>Último Dato</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($carrusel as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->indicador->nombre }}</td>
                                <td>
                                    <img src="{{ asset('img/iconos_indicadores/' . $item->imagen) }}" alt="Miniatura"
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td>
                                    {{ $item->ultimo_dato_formateado ?? 'N/A' }}
                                    ({{ $item->anio_mas_reciente ?? 'N/A' }})
                                </td>
                                <td>
                                    @can('editar-ind-carrusel')
                                        <button class="btn btn-warning btn-sm edit-indicator text-black" title="Editar"
                                            data-id="{{ $item->id }}" data-indicador="{{ $item->id_indicador }}"
                                            data-imagen="{{ $item->imagen }}" data-action="edit">
                                            <i class="fa-regular fa-pen-to-square"></i></a>
                                        </button>
                                    @endcan
                                    {{-- @can('borrar-ind-carrusel')
                                        <form action="{{ route('panel-carrusel-indicadores.destroy', $item->id) }}"
                                            method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm text-white">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endcan --}}
                                    @can('borrar-ind-carrusel')
                                        <form id="delete-form-{{ $item->id }}"
                                            action="{{ route('panel-carrusel-indicadores.destroy', $item->id) }}"
                                            method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm text-white delete-button"
                                                title="Eliminar" data-form-id="delete-form-{{ $item->id }}">
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
            <div class="modal fade" id="modalIndicador" tabindex="-1" aria-labelledby="modalIndicadorLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form id="formIndicador" action="{{ route('panel-carrusel-indicadores.store') }}"
                            method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title encabezado-lista w-100 text-white" id="modalIndicadorLabel">Nuevo
                                    Indicador</h5>
                                {{-- <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button> --}}
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="id_indicador" class="form-label custom-section-title"><i
                                            class="fa-solid fa-chart-line"></i> Indicador</label>
                                    <select id="id_indicador" name="id_indicador" required>
                                        <option value="">Selecciona un indicador</option>
                                        @foreach ($indicadores as $indicador)
                                            <option value="{{ $indicador->id }}">{{ $indicador->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="imagen" class="form-label custom-section-title"><i
                                            class="fa-solid fa-image"></i> Imagen</label>
                                    <div class="image-selector">
                                        @foreach ($imagenes as $imagen)
                                            <label>
                                                <input type="radio" name="imagen" value="{{ $imagen }}"
                                                    required>
                                                <img src="{{ asset('img/iconos_indicadores/' . $imagen) }}"
                                                    alt="Imagen {{ $imagen }}"
                                                    style="width: 100px; height: 100px; object-fit: cover; border: 2px solid transparent;">
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="button-save" type="submit">
                                    <span class="button__text">Guardar</span>
                                    @include('components.svg-save')
                                </button>
                                <button class="button-cancel" type="button" data-bs-dismiss="modal">
                                    <span class="button__text">Cancelar</span>
                                    @include('components.svg-cancel')
                                </button>
                                {{-- <button type="submit" class="boton-guardar">Guardar</button>
                            <button type="button" class="boton-cancelar" data-bs-dismiss="modal">Cancelar</button> --}}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Inicialización global de Choices.js
            const choicesInstance = new Choices("#id_indicador", {
                searchEnabled: true,
                removeItemButton: false,
                placeholderValue: "Selecciona un indicador...",
            });

            // Manejar apertura del modal para edición
            $('.edit-indicator').on('click', function() {
                const id = $(this).data('id');
                const indicador = $(this).data('indicador');
                const imagen = $(this).data('imagen');

                // Configurar formulario para editar
                $('#formIndicador').attr('action', `{{ url('panel-carrusel-indicadores') }}/${id}`);
                $('#formIndicador').attr('method', 'POST'); // Laravel espera POST
                $('#formIndicador').prepend(
                    '<input type="hidden" name="_method" value="PUT">'); // Agregar método PUT
                $('#indicadorId').val(id);

                // Actualizar Choices.js con el indicador seleccionado
                let indicadorString = indicador.toString();
                choicesInstance.setChoiceByValue(indicadorString);

                // Seleccionar imagen
                $(`input[name="imagen"][value="${imagen}"]`).prop('checked', true);

                // Cambiar título del modal
                $('#modalIndicadorLabel').text('Editar Indicador');
                $('#modalIndicador').modal('show');
            });

            // Manejar apertura del modal para creación
            $('button[data-bs-target="#modalIndicador"]').on('click', function() {
                // Restablecer el formulario a su estado inicial
                $('#formIndicador').attr('action', '{{ route('panel-carrusel-indicadores.store') }}');
                $('#formIndicador').attr('method', 'POST'); // Crear siempre usa POST
                $('#formIndicador').find('input[name="_method"]').remove(); // Eliminar método PUT si existe

                $('#indicadorId').val('');
                choicesInstance.setChoiceByValue(''); // Limpiar selección en Choices.js
                $('input[name="imagen"]').prop('checked', false);

                // Cambiar título del modal
                $('#modalIndicadorLabel').text('Nuevo Indicador');
            });
        });

        $(document).ready(function() {
            $('#myTable').DataTable({
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
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Seleccionar todos los botones de eliminación
            const deleteButtons = document.querySelectorAll('.delete-button');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const formId = this.getAttribute('data-form-id');
                    const form = document.getElementById(formId);

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "Esta acción no se puede deshacer.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit(); // Envía el formulario si el usuario confirma
                        }
                    });
                });
            });
        });
    </script>

</x-app-layout>

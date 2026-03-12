<x-app-layout>
    <!-- local -->
    @section('title', 'Instituciones')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Instituciones') }}
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
    @if (session('error'))
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
    </div>
    @endif
    <div class="contenedor-principal">
        <div class="encabezado-lista my-2">
            <h2>Gestión de Instituciones</h2>
        </div>
        <div class="d-flex justify-content-end mx-4 my-4">
            {{-- Use data-bs-toggle and data-bs-target for Bootstrap 5 --}}
            <button class="button-add-new" type="button" id="btnAddInstitucion" data-bs-toggle="modal"
                data-bs-target="#modalInstitucion">
                <span class="button__text">Agregar</span>
                <span class="button__icon"><svg class="svg" fill="none" height="24" stroke="currentColor"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                        width="24" xmlns="http://www.w3.org/2000/svg">
                        <line x1="12" x2="12" y1="5" y2="19"></line>
                        <line x1="5" x2="19" y1="12" y2="12"></line>
                    </svg></span>
            </button>
        </div>
        <div class="container table-responsive">
            <table class="table table-striped table-users" id="table-instituciones">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Titular</th>
                        <th class="text-center">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($instituciones as $institucion)
                    <tr>
                        <td>{{ $institucion->nombre }}</td>
                        <td>{{ $institucion->titular }}</td>
                        <td class="text-center">
                            {{-- Use distinct class for edit buttons to attach listeners --}}
                            <button class="btn btn-warning btn-sm text-black btn-edit-institucion" title="Editar"
                                data-bs-toggle="modal" data-bs-target="#modalInstitucion"
                                data-institucion='@json($institucion)'>
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>
                            <form action="{{ route('panel-cat-instituciones.destroy', $institucion->id) }}"
                                method="POST" class="d-inline" id="form-delete-{{ $institucion->id }}">
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

    {{-- MODAL --}}
    <div class="modal fade" id="modalInstitucion" tabindex="-1" aria-labelledby="modalInstitucionLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInstitucionLabel">Agregar Institución</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formInstitucion" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="methodField" value="POST">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="nombre">Nombre de la Institución <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="titular">Titular <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="titular" name="titular" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // DataTable (keeps jQuery because DataTables usually requires it unless using the vanilla version, and app.js loads jquery)
        if (typeof $ !== 'undefined' && $.fn.dataTable) {
            $('#table-instituciones').DataTable({
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
        }

        // Vanilla JS for Modal Logic
        const modalElement = document.getElementById('modalInstitucion');
        const form = document.getElementById('formInstitucion');
        const methodField = document.getElementById('methodField');
        const modalTitle = document.getElementById('modalInstitucionLabel');
        const inputNombre = document.getElementById('nombre');
        const inputTitular = document.getElementById('titular');

        // Add Button Listener
        const btnAdd = document.getElementById('btnAddInstitucion');
        if (btnAdd) {
            btnAdd.addEventListener('click', function() {
                modalTitle.innerText = 'Agregar Institución';
                form.action = "{{ route('panel-cat-instituciones.store') }}";
                methodField.value = 'POST';
                inputNombre.value = '';
                inputTitular.value = '';
            });
        }

        // Edit Buttons Listener - Delegation might be better if DT redraws, but direct attach works for initial page
        // Using delegation to handle DataTable pages if necessary (though simple loop works if no ajax pagination)
        document.body.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-edit-institucion');
            if (btn) {
                const data = JSON.parse(btn.getAttribute('data-institucion'));

                modalTitle.innerText = 'Editar Institución';
                let url = "{{ route('panel-cat-instituciones.update', ':id') }}";
                url = url.replace(':id', data.id);
                form.action = url;
                methodField.value = 'PUT';

                inputNombre.value = data.nombre;
                inputTitular.value = data.titular;
            }
        });
    });

    // Validacion Eliminar (Vanilla-ish, uses Swal global)
    document.addEventListener("submit", (e) => {
        if (e.target.id && e.target.id.startsWith('form-delete-')) {
            e.preventDefault();
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
                    e.target.submit();
                }
            });
        }
    });
</script>
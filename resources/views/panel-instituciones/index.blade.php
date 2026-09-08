<x-app-layout>
    @section('title', 'Instituciones')
    <x-slot name="header">
        <div class="exec-header admin-index-header">
            <div>
                <span class="exec-eyebrow">Catálogo institucional</span>
                <h2 class="exec-header__title">Gestión de instituciones</h2>
            </div>
            <span class="exec-header__plan">Sectorización y dependencias</span>
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
    @if (session('error'))
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ session('error') }}
        </div>
    @endif
    <div class="admin-index">
        <div class="contenedor-principal admin-index__surface mx-auto">
            <div class="admin-index__heading">
                <div>
                    <span class="exec-eyebrow">Universo registrado</span>
                    <h1>Listado de instituciones</h1>
                </div>
                <span class="admin-index__count">{{ count($instituciones) }} registros</span>
            </div>

            <div class="admin-index__actions">
                <button class="button-add-new" type="button" id="btnAddInstitucion" data-bs-toggle="modal"
                    data-bs-target="#modalInstitucion">
                    <span class="button__text">Agregar</span>
                    <span class="button__icon">
                        @include('components.svg-add')
                    </span>
                </button>
            </div>

            <div class="table-responsive admin-index-table-wrap">
                <table class="table table-striped table-bordered admin-index-table" id="table-instituciones" style="width:100%">
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Titular</th>
                            <th scope="col">Dependencia sectorizadora</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($instituciones as $institucion)
                            <tr>
                                <td>{{ $institucion->nombre }}</td>
                                <td>{{ $institucion->titular }}</td>
                                <td>{{ $institucion->sectorizadora?->nombre ?? 'Sin sectorización' }}</td>
                                <td class="text-center">
                                    <div class="admin-index-table-actions" role="group" aria-label="Acciones de la institución">
                                        <button class="admin-index-table-action admin-index-table-action--edit btn-edit-institucion"
                                            title="Editar" data-bs-toggle="modal" data-bs-target="#modalInstitucion"
                                            data-institucion='@json($institucion)'>
                                            <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                                            <span class="visually-hidden">Editar</span>
                                        </button>
                                        <form action="{{ route('panel-cat-instituciones.destroy', $institucion->id) }}"
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

    {{-- MODAL --}}
    <div class="modal fade admin-index-modal" id="modalInstitucion" tabindex="-1" aria-labelledby="modalInstitucionLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header admin-index-modal__header">
                    <h5 class="modal-title" id="modalInstitucionLabel">Agregar Institución</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
                        <div class="form-group mb-3">
                            <label for="institucion_sectorizadora_id">Dependencia sectorizadora</label>
                            <select class="form-select" id="institucion_sectorizadora_id" name="institucion_sectorizadora_id">
                                <option value="">Sin sectorización</option>
                                @foreach ($institucionesSectorizadoras as $sectorizadora)
                                    <option value="{{ $sectorizadora->id }}">{{ $sectorizadora->nombre }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Solo se permite un nivel de sectorización.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="indicator-detail-button indicator-detail-button--neutral"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="indicator-detail-button indicator-detail-button--primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
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

                if (typeof $ !== 'undefined' && $.fn.dataTable) {
                    $('#table-instituciones').DataTable({
                        pagingType: 'simple_numbers',
                        order: [],
                        dom: 'Bfrtip',
                        buttons: buttons,
                        language: language
                    });
                }

                const modalElement = document.getElementById('modalInstitucion');
                const form = document.getElementById('formInstitucion');
                const methodField = document.getElementById('methodField');
                const modalTitle = document.getElementById('modalInstitucionLabel');
                const inputNombre = document.getElementById('nombre');
                const inputTitular = document.getElementById('titular');
                const sectorizadoraSelect = document.getElementById('institucion_sectorizadora_id');

                const btnAdd = document.getElementById('btnAddInstitucion');
                if (btnAdd) {
                    btnAdd.addEventListener('click', function() {
                        modalTitle.innerText = 'Agregar Institución';
                        form.action = "{{ route('panel-cat-instituciones.store') }}";
                        methodField.value = 'POST';
                        inputNombre.value = '';
                        inputTitular.value = '';
                        sectorizadoraSelect.value = '';
                    });
                }

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
                        sectorizadoraSelect.value = data.institucion_sectorizadora_id || '';
                    }
                });

                document.querySelectorAll('.confirmable-form').forEach((form) => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();

                        Swal.fire({
                            title: '¿Estás seguro de eliminar esta institución?',
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

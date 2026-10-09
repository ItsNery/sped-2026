<x-app-layout>
    @php
        $isEdit = isset($plan);
        $title = $isEdit ? 'Editar Plan Estatal' : 'Crear Plan Estatal';
        $route = $isEdit ? route('panel-cat-planes.update', $plan->id) : route('panel-cat-planes.store');
    @endphp

    @section('title', $title)
    <x-slot name="header">
        <div class="exec-header admin-index-header">
            <div>
                <span class="exec-eyebrow">Catálogo de planeación</span>
                <h2 class="exec-header__title">{{ $title }}</h2>
            </div>
            <span class="exec-header__plan">Plan de Desarrollo del Estado</span>
        </div>
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
    <div class="admin-index">
        <div class="contenedor-principal admin-index__surface mx-auto">
            <form action="{{ $route }}" method="POST">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label class="form-label custom-section-title" for="nombre">
                        <i class="fa-solid fa-file-signature" aria-hidden="true"></i> Nombre del Plan:
                    </label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" name="nombre"
                        id="nombre" value="{{ old('nombre', $plan->nombre ?? '') }}" required autofocus>
                    @error('nombre')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label custom-section-title" for="gobernador">
                        <i class="fa-solid fa-user-tie" aria-hidden="true"></i> Gobernador:
                    </label>
                    <input type="text" class="form-control @error('gobernador') is-invalid @enderror"
                        name="gobernador" id="gobernador" value="{{ old('gobernador', $plan->gobernador ?? '') }}"
                        required>
                    @error('gobernador')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>

                <div class="mb-3 pb-3 d-flex justify-content-end">
                    <button class="button-save" type="submit">
                        <span class="button__text">{{ $isEdit ? 'Actualizar' : 'Guardar' }}</span>
                        @include('components.svg-save')
                    </button>
                    <a href="{{ route('panel-cat-planes.index') }}" class="text-decoration-none">
                        <button class="button-cancel" type="button">
                            <span class="button__text">Cancelar</span>
                            @include('components.svg-cancel')
                        </button>
                    </a>
                </div>
            </form>

            @if ($isEdit)
                <hr class="my-5">
                <div class="admin-index__heading">
                    <div>
                        <span class="exec-eyebrow">Configuración del plan</span>
                        <h1>Ejes del Plan</h1>
                    </div>
                    <button class="button-add-new" type="button" data-bs-toggle="modal"
                        data-bs-target="#modalAddEje">
                        <span class="button__text">Agregar Eje</span>
                        <span class="button__icon">@include('components.svg-add')</span>
                    </button>
                </div>

                <div class="table-responsive admin-index-table-wrap">
                    <table class="table table-striped admin-index-table" style="width:100%">
                        <thead>
                            <tr>
                                <th scope="col">Número</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Color</th>
                                <th scope="col" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($plan->catEjes as $eje)
                                <tr>
                                    <td>{{ $eje->numero }}</td>
                                    <td>{{ $eje->nombre }}</td>
                                    <td>
                                        <div
                                            style="width: 20px; height: 20px; background-color: {{ $eje->color }}; border: 1px solid #ccc; border-radius: 3px;">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="admin-index-table-actions" role="group" aria-label="Acciones del eje">
                                            <button class="admin-index-table-action admin-index-table-action--edit btn-edit-eje"
                                                title="Editar" data-id="{{ $eje->id }}"
                                                data-nombre="{{ $eje->nombre }}" data-numero="{{ $eje->numero }}"
                                                data-color="{{ $eje->color }}" data-bs-toggle="modal"
                                                data-bs-target="#modalEditEje">
                                                <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                                                <span class="visually-hidden">Editar</span>
                                            </button>
                                            <form action="{{ route('panel-cat-ejes.destroy', $eje->id) }}"
                                                method="POST" class="formEliminarEje">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="admin-index-table-action admin-index-table-action--delete"
                                                    title="Eliminar">
                                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                    <span class="visually-hidden">Eliminar</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay ejes registrados para
                                        este plan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Modal Agregar Eje -->
                <div class="modal fade admin-index-modal" id="modalAddEje" tabindex="-1" aria-labelledby="modalAddEjeLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('panel-cat-ejes.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <div class="modal-header admin-index-modal__header">
                                    <h5 class="modal-title" id="modalAddEjeLabel">Agregar Nuevo Eje</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="numero" class="form-label">Número:</label>
                                        <input type="number" name="numero" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre del Eje:</label>
                                        <input type="text" name="nombre" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="color" class="form-label">Color:</label>
                                        <input type="color" name="color"
                                            class="form-control form-control-color" value="#563d7c">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="indicator-detail-button indicator-detail-button--neutral"
                                        data-bs-dismiss="modal">Cerrar</button>
                                    <button type="submit" class="indicator-detail-button indicator-detail-button--primary">Guardar Eje</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Editar Eje -->
                <div class="modal fade admin-index-modal" id="modalEditEje" tabindex="-1" aria-labelledby="modalEditEjeLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="formEditEje" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header admin-index-modal__header">
                                    <h5 class="modal-title" id="modalEditEjeLabel">Editar Eje</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="edit_numero" class="form-label">Número:</label>
                                        <input type="number" name="numero" id="edit_numero"
                                            class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_nombre" class="form-label">Nombre del Eje:</label>
                                        <input type="text" name="nombre" id="edit_nombre"
                                            class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_color" class="form-label">Color:</label>
                                        <input type="color" name="color" id="edit_color"
                                            class="form-control form-control-color">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="indicator-detail-button indicator-detail-button--neutral"
                                        data-bs-dismiss="modal">Cerrar</button>
                                    <button type="submit" class="indicator-detail-button indicator-detail-button--primary">Actualizar Eje</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modalEditEje = document.getElementById('modalEditEje');
                if (modalEditEje) {
                    modalEditEje.addEventListener('show.bs.modal', function(event) {
                        const button = event.relatedTarget;
                        const id = button.getAttribute('data-id');
                        const nombre = button.getAttribute('data-nombre');
                        const numero = button.getAttribute('data-numero');
                        const color = button.getAttribute('data-color');

                        const form = modalEditEje.querySelector('#formEditEje');
                        form.action = `/panel-cat-ejes/${id}`;

                        modalEditEje.querySelector('#edit_nombre').value = nombre;
                        modalEditEje.querySelector('#edit_numero').value = numero;
                        modalEditEje.querySelector('#edit_color').value = color;
                    });
                }

                const deleteForms = document.querySelectorAll('.formEliminarEje');
                deleteForms.forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        Swal.fire({
                            title: '¿Estás seguro?',
                            text: "El eje se eliminará permanentemente.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
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

<x-app-layout>
    @section('title', 'Programas Derivados Especiales')
    <x-slot name="header">
        <div class="exec-header admin-index-header">
            <div>
                <span class="exec-eyebrow">Catálogo de planeación</span>
                <h2 class="exec-header__title">Programas derivados especiales</h2>
            </div>
            <span class="exec-header__plan">Planes e instrumentos</span>
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
                    <h1>Listado de programas</h1>
                </div>
                <span class="admin-index__count">{{ count($programas) }} registros</span>
            </div>

            <div class="admin-index__actions">
                <a href="{{ route('panel-cat-prog-der-esp.create') }}" class="button-add-new text-decoration-none">
                    <span class="button__text">Agregar</span>
                    <span class="button__icon">
                        @include('components.svg-add')
                    </span>
                </a>
            </div>

            <div class="table-responsive admin-index-table-wrap">
                <table class="table table-striped table-bordered admin-index-table" id="table-programas" style="width:100%">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Plan Estatal</th>
                            <th scope="col">Color</th>
                            <th scope="col">Imagen</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($programas as $programa)
                            <tr>
                                <td>{{ $programa->id }}</td>
                                <td>{{ $programa->nombre }}</td>
                                <td>{{ $programa->catPlanEstatalDesarrollo->nombre ?? 'N/A' }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded border"
                                            style="width: 1.5rem; height: 1.5rem; background-color: {{ $programa->color }};"></div>
                                        <span>{{ $programa->color }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if ($programa->imagen)
                                        <img src="{{ asset($programa->imagen) }}" alt="Imagen de {{ $programa->nombre }}"
                                            class="rounded" style="width: 2.5rem; height: 2.5rem; object-fit: cover;">
                                    @else
                                        <span class="text-muted">Sin imagen</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="admin-index-table-actions" role="group" aria-label="Acciones del programa">
                                        <a href="{{ route('panel-cat-prog-der-esp.edit', $programa->id) }}"
                                            class="admin-index-table-action admin-index-table-action--edit"
                                            title="Editar">
                                            <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                                            <span class="visually-hidden">Editar</span>
                                        </a>
                                        <form action="{{ route('panel-cat-prog-der-esp.destroy', $programa->id) }}"
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

                $('#table-programas').DataTable({
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
                            title: '¿Estás seguro de eliminar este programa?',
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

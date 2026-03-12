<x-app-layout>
    @section('title', 'Municipios: Inicio')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Municipios con convenio') }}
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
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: '¡Error!',
                text: '{{ session('error') }}',
                confirmButtonText: 'Aceptar'
            });
        </script>
    @endif
    <link rel="stylesheet" href="{{ asset('css/choices.min.css') }}">
    <script src="{{ asset('js/choices.min.js') }}"></script>

    <div class="container mx-auto">
        <div class="contenedor-principal mx-auto">
            <div class="encabezado-lista my-2">
                <h2>Listado de municipios con convenio</h2>
            </div>
            @can('crear-municipios-convenio ')
                <div class="d-flex justify-content-end mx-4 my-2">
                    <button class="button-add-new" type="button" data-bs-toggle="modal" data-bs-target="#modalMunConvenio"
                        data-action="create">
                        <span class="button__text">Agregar</span>
                        <span class="button__icon"><svg class="svg" fill="none" height="24" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                width="24" xmlns="http://www.w3.org/2000/svg">
                                <line x1="12" x2="12" y1="5" y2="19"></line>
                                <line x1="5" x2="19" y1="12" y2="12"></line>
                            </svg></span>
                    </button>
                </div>
            @endcan
            {{-- <div class="d-flex justify-content-end mx-4">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalMunConvenio"
                    data-action="create">
                    <i class="fa-solid fa-plus"></i> Subir nuevo
                </button>
            </div> --}}
            <div class="container table-responsive" id="contenedor-tabla-indicadores">
                <table id="tabla-municipios" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th>Municipio</th>
                            <th>Objetivo</th>
                            <th>Convenio</th>
                            <th>Ícono</th>
                            <th>Indicadores</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($municipiosConConvenio as $municipioConConvenio)
                            <tr>
                                <td scope="row">
                                    {{ $municipioConConvenio->id }}
                                </td>
                                <td>
                                    {{ $municipioConConvenio->municipio->nombre }}
                                </td>
                                <td>
                                    {{ $municipioConConvenio->objetivo }}
                                </td>
                                <td>
                                    <a href="{{ $municipioConConvenio->convenio }}" target="_blank">
                                        <i class="fa-regular fa-file-pdf fs-2 text-danger"></i>

                                    </a>
                                </td>
                                <td>
                                    <img src="{{ $municipioConConvenio->icono }}" alt="Miniatura"
                                        style="width: 100px; height: 100px; object-fit: cover;">
                                    {{-- {{ $municipioConConvenio->icono }} --}}
                                </td>
                                <td>
                                    <ul>
                                        @if ($municipioConConvenio->municipio && $municipioConConvenio->municipio->indicadores->count() > 0)
                                            @foreach ($municipioConConvenio->municipio->indicadores as $indicador)
                                                <details class="indicador-details">

                                                    {{-- Lo que está en <summary> es el título visible --}}
                                                    <summary>
                                                        {{-- Usamos Str::limit para mantener el título corto y limpio --}}
                                                        <strong>{{ \Illuminate\Support\Str::limit($indicador->indicador, 80, '...') }}</strong>
                                                    </summary>

                                                    {{-- Este es el contenido que se expande/contrae --}}
                                                    <div class="indicador-contenido-completo">
                                                        <p>
                                                            <strong>Nombre completo:</strong>
                                                            {{ $indicador->indicador }}
                                                        </p>
                                                        <p>
                                                            <strong>Descripción:</strong>
                                                            {{ $indicador->descripcion ?? 'No disponible' }}
                                                        </p>
                                                        <p>
                                                            <strong>Fuente:</strong> {{ $indicador->fuente }}
                                                        </p>
                                                        <p>
                                                            <a
                                                                href="{{ route('indicadores.show_municipal', $indicador->id) }}">Ver
                                                                detalle</a>
                                                        </p>
                                                    </div>
                                                </details>
                                            @endforeach
                                        @else
                                            <li>Sin indicadores públicos creados</li>
                                        @endif
                                    </ul>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center rounded-lg text-lg " role="group">
                                        <!-- botón editar -->
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalMunConvenio" data-action="edit"
                                            data-id="{{ $municipioConConvenio->id }}"
                                            data-municipio="{{ $municipioConConvenio->id_municipio }}"
                                            data-objetivo="{{ $municipioConConvenio->objetivo }}"
                                            data-convenio="{{ $municipioConConvenio->convenio }}"
                                            data-icono="{{ $municipioConConvenio->icono }}"
                                            data-banner="{{ $municipioConConvenio->banner }}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <form id="delete-form-{{ $municipioConConvenio->id }}"
                                            action="{{ route('panel-municipios-convenio.destroy', $municipioConConvenio->id) }}"
                                            method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="btn btn-danger btn-sm text-white delete-button"
                                                data-form-id="delete-form-{{ $municipioConConvenio->id }}">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th scope="col">No.</th>
                            <th>Municipio</th>
                            <th>Objetivo</th>
                            <th>Convenio</th>
                            <th>Ícono</th>
                            <th>Indicadores</th>
                            <th>Opciones</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalMunConvenio" tabindex="-1" aria-labelledby="modalMunConvenioLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formMunConvenio" action="{{ route('panel-municipios-convenio.store') }}" method="POST"
                    enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" id="method" name="_method" value="POST">
                    <input type="hidden" id="id_municipio_convenio" name="id_municipio_convenio" value="">

                    <div class="modal-header">
                        <h5 class="modal-title encabezado-lista w-100 text-white" id="modalMunConvenioLabel">Nuevo
                            Municipio</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-4 mb-3">
                                <label for="id_municipio" class="form-label custom-section-title"><i
                                        class="fa-solid fa-building-wheat"></i> Municipio</label>
                                <select id="id_municipio" name="id_municipio" class="form-select" required>
                                    <option value="">Selecciona un municipio</option>
                                    @foreach ($municipios as $municipio)
                                        <option value="{{ $municipio->id }}">{{ $municipio->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('id_municipio')
                                    <small class="invalid-feedback d-block">
                                        <strong>{{ $message }}</strong>
                                    </small>
                                @enderror
                            </div>
                            <div class="col-8 mb-3">
                                <label for="objetivo" class="form-label custom-section-title"><i
                                        class="fa-solid fa-file-lines"></i> Objetivo</label>
                                <textarea id="objetivo" name="objetivo" placeholder="Ej. El gobierno del municipio..."
                                    class="form-control @error('objetivo') is-invalid @enderror" required>{{ old('objetivo') }}</textarea>
                                @error('objetivo')
                                    <small class="invalid-feedback d-block">
                                        <strong>{{ $message }}</strong>
                                    </small>
                                @enderror
                            </div>
                            <div class="col-8 mb-3">
                                <label for="convenio" class="form-label custom-section-title"><i
                                        class="fa-solid fa-file-contract"></i> Convenio</label>
                                <input type="file" id="convenio" class="form-control" name="convenio"
                                    accept="application/pdf">
                                <small class="form-text text-muted">Deja este campo vacío si no deseas actualizar
                                    el
                                    archivo.</small>
                                @error('convenio')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4 mb-3">
                                <label for="convenioActual" class="form-text text-muted custom-section-title"><i
                                        class="fa-solid fa-folder-open"></i> Convenio Actual</label>
                                <div id="convenioActual">
                                    <!-- El enlace se agregará dinámicamente aquí -->
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="icono" class="form-label custom-section-title"><i
                                        class="fa-solid fa-images"></i> Ícono</label>
                                <input type="file" id="icono" class="form-control" name="icono"
                                    accept="image/png, image/jpeg">
                                <small class="form-text text-muted">Deja este campo vacío si no deseas actualizar
                                    el
                                    archivo.</small>
                                @error('icono')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4 mb-3">
                                <label for="previewIcono" class="form-label custom-section-title"><i
                                        class="fa-solid fa-file-image"></i> Vista Previa del Ícono</label>
                                <div id="previewIcono">
                                    <!-- Vista previa del ícono se mostrará aquí -->
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="banner" class="form-label custom-section-title"><i
                                        class="fa-solid fa-panorama"></i> Banner</label>
                                <input type="file" id="banner" class="form-control" name="banner"
                                    accept="image/png, image/jpeg">
                                <small class="form-text text-muted">
                                    Deja este campo vacío si no deseas actualizar
                                    el
                                    archivo.
                                </small>
                                @error('banner')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4 mb-3">
                                <label for="previewBanner" class="form-label custom-section-title"><i
                                        class="fa-solid fa-panorama"></i> Vista Previa del Banner</label>
                                <div id="previewBanner">
                                    <!-- Vista previa del ícono se mostrará aquí -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="button-save" type="submit">
                            <span class="button__text">Guardar</span>
                            @include('components.svg-save')
                        </button>
                        {{-- <button type="submit" class="boton-guardar">Guardar</button> --}}
                        <button class="button-cancel" type="button" data-bs-dismiss="modal">
                            <span class="button__text">Cancelar</span>
                            @include('components.svg-cancel')
                        </button>
                        {{-- <button type="button" class="boton-cancelar" data-bs-dismiss="modal">Cancelar</button> --}}
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicialización global de Choices.js
        let choicesInstance = new Choices("#id_municipio", {
            searchEnabled: true,
            removeItemButton: false,
            placeholderValue: "Selecciona un municipio...",
        });

        // Inicialización de DataTables
        $('#tabla-municipios').DataTable({
            pagingType: "simple_numbers",
            order: [],
            buttons: [{
                    extend: "excelHtml5",
                    text: '<i class="fa-regular fa-file-excel text-white"></i>',
                    className: "btn btn-success",
                },
                {
                    extend: "csvHtml5",
                    text: '<i class="fa-solid fa-file-csv text-white"></i>',
                    className: "btn btn-primary",
                },
                {
                    extend: "pdfHtml5",
                    text: '<i class="fa-regular fa-file-pdf text-white"></i>',
                    className: "btn btn-danger",
                },
                {
                    extend: "copy",
                    className: "btn btn-info",
                    text: '<i class="fa-regular fa-copy text-white"></i>',
                },
            ],
            language: {
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ entradas",
                info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                paginate: {
                    previous: "Anterior",
                    next: "Siguiente",
                },
            },
        });

        const modal = document.getElementById("modalMunConvenio");
        const form = document.getElementById("formMunConvenio");
        const iconoInput = document.getElementById("icono");
        const previewIcono = document.getElementById("previewIcono");
        const inputBanner = document.getElementById("banner");
        const previewBanner = document.getElementById("previewBanner");

        // Actualizar vista previa del ícono seleccionado
        iconoInput.addEventListener("change", () => {
            const file = iconoInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewIcono.innerHTML = `
                <img src="${e.target.result}" alt="Vista Previa" class="img-fluid" style="max-height: 150px; object-fit: cover;">
            `;
                };
                reader.readAsDataURL(file);
            } else {
                previewIcono.innerHTML =
                    '<small class="text-muted">No se seleccionó ningún ícono.</small>';
            }
        });

        // Actualizar vista previa del banner seleccionado
        inputBanner.addEventListener("change", () => {
            const file = inputBanner.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewBanner.innerHTML = `
                <img src="${e.target.result}" alt="Vista Previa" class="img-fluid" style="max-height: 150px; object-fit: cover;">
            `;
                };
                reader.readAsDataURL(file);
            } else {
                previewBanner.innerHTML =
                    '<small class="text-muted">No se seleccionó ningún ícono.</small>';
            }
        });

        modal.addEventListener("show.bs.modal", (event) => {
            const button = event.relatedTarget;
            const action = button.getAttribute("data-action");

            if (action === "create") {
                form.action = "{{ route('panel-municipios-convenio.store') }}";
                form.reset();
                modal.querySelector("#method").value = "POST";
                modal.querySelector("#modalMunConvenioLabel").textContent =
                    "Nuevo Municipio";
                modal.querySelector("#convenioActual").innerHTML = "";
                modal.querySelector("#previewIcono").innerHTML = "";
                modal.querySelector("#previewBanner").innerHTML = "";
                // choicesInstance.clearChoices(); 
                choicesInstance.setChoiceByValue(
                    '');
            } else if (action === "edit") {
                const id = button.getAttribute("data-id");
                const municipio = button.getAttribute("data-municipio");
                const objetivo = button.getAttribute("data-objetivo");
                const convenio = button.getAttribute("data-convenio");
                const icono = button.getAttribute("data-icono");
                const banner = button.getAttribute("data-banner");

                form.action = `/panel-municipios-convenio/${id}`;
                modal.querySelector("#method").value = "PUT";
                modal.querySelector("#id_municipio_convenio").value = id;
                modal.querySelector("#objetivo").value = objetivo;
                modal.querySelector("#modalMunConvenioLabel").textContent =
                    "Editar Municipio";

                // Actualizar convenio actual
                if (convenio) {
                    modal.querySelector("#convenioActual").innerHTML = `
                <a href="${convenio}" target="_blank">
                    <i class="fa-regular fa-file-pdf fs-1 text-danger"></i>
                </a>`;
                } else {
                    modal.querySelector("#convenioActual").innerHTML =
                        '<small class="text-muted">No hay convenio actual.</small>';
                }

                // Actualizar vista previa del ícono
                if (icono) {
                    modal.querySelector("#previewIcono").innerHTML = `
                <img src="${icono}" alt="Vista Previa" class="img-fluid" style="max-height: 100px; object-fit: cover;">
            `;
                } else {
                    modal.querySelector("#previewIcono").innerHTML =
                        '<small class="text-muted">No hay ícono actual.</small>';
                }

                // Actualizar vista previa del banner
                if (banner) {
                    modal.querySelector("#previewBanner").innerHTML = `
                <img src="${banner}" alt="Vista Previa" class="img-fluid" style="max-height: 100px; object-fit: cover;">
            `;
                } else {
                    modal.querySelector("#previewBanner").innerHTML =
                        '<small class="text-muted">No hay banner actual.</small>';
                }

                // Establecer el municipio actual
                choicesInstance.setChoiceByValue(municipio);
            }
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Script cargado'); // Verifica que este mensaje aparezca en la consola

        // Delegación de eventos
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('delete-button')) {
                console.log('Botón clickeado'); // Verifica que este mensaje aparezca al hacer clic
                const formId = event.target.getAttribute('data-form-id');
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
            }
        });
    });
</script>

<x-app-layout>
    @section('title', 'Slider: Inicio')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Diapositivas de carrusel de inicio') }}
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
                <h2>Sliders</h2>
            </div>
            <div class="d-flex justify-content-end mx-4 mb-3">
                <button class="button-add-new" type="button" data-bs-toggle="modal" data-bs-target="#modalSliderInicio"
                    data-action="create">
                    <span class="button__text">Agregar</span>
                    <span class="button__icon">
                        @include('components.svg-add')
                    </span>
                </button>
                {{-- <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalSliderInicio"
                    data-action="create">
                    <i class="fa-solid fa-plus"></i> Subir nuevo
                </button> --}}
            </div>
            <div class="container table-responsive" id="contenedor-tabla-indicadores">
                <table id="tabla-municipios" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Enlace</th>
                            <th>Imagen</th>
                            <th>Orden</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sliders as $slider)
                            <tr>
                                <td scope="row">
                                    {{ $slider->id }}
                                </td>
                                <td>
                                    {{ $slider->titulo }}
                                </td>
                                <td>
                                    {{ $slider->descripcion }}
                                </td>
                                <td>
                                    <a href="{{ $slider->enlace }}" target="_blank">
                                        Ver enlace
                                    </a>
                                <td>
                                    <img src="{{ $slider->imagen }}" alt="{{ $slider->descripcion }}"
                                        style="width: 100px; height: 100px; object-fit: cover;">
                                </td>
                                <td>
                                    {{ $slider->orden }}
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center rounded-lg text-lg " role="group">
                                        <!-- botón editar -->
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalSliderInicio" data-action="edit"
                                            data-id="{{ $slider->id }}" data-titulo="{{ $slider->titulo }}"
                                            data-descripcion="{{ $slider->descripcion }}"
                                            data-enlace="{{ $slider->enlace }}" data-imagen="{{ $slider->imagen }}"
                                            data-orden="{{ $slider->orden }}" data-activo="{{ $slider->activo }}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <form id="delete-form-{{ $slider->id }}"
                                            action="{{ route('panel-slider-inicio.destroy', $slider->id) }}"
                                            method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="btn btn-danger btn-sm text-white delete-button"
                                                data-form-id="delete-form-{{ $slider->id }}">
                                                <i class="fa-solid fa-trash-can"></i>
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
    <div class="modal fade" id="modalSliderInicio" tabindex="-1" aria-labelledby="modalSliderInicioLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formSliderInicio" action="{{ route('panel-slider-inicio.store') }}" method="POST"
                    enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" id="method" name="_method" value="POST">
                    <input type="hidden" id="id" name="id" value="">

                    <div class="modal-header">
                        <h5 class="modal-title encabezado-lista w-100 text-white" id="modalSliderInicioLabel">Nuevo
                            Slide</h5>
                        {{-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> --}}
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="titulo" class="form-label custom-section-title"><i
                                        class="fa-solid fa-file-signature"></i> Título*</label>
                                <input id="titulo" name="titulo" placeholder="Ej. Slider del Número de..."
                                    class="form-control @error('titulo') is-invalid @enderror" required
                                    value="{{ old('titulo') }}" />
                                @error('titulo')
                                    <small class="invalid-feedback d-block">
                                        <strong>{{ $message }}</strong>
                                    </small>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="descripcion" class="form-label custom-section-title"><i
                                        class="fa-solid fa-file-lines"></i> Descripción</label>
                                <textarea id="descripcion" name="descripcion" placeholder="Ej. Este slide muestra el número de..."
                                    class="form-control @error('descripcion') is-invalid @enderror" required>{{ old('descripcion') }}</textarea>
                                @error('descripcion')
                                    <small class="invalid-feedback d-block">
                                        <strong>{{ $message }}</strong>
                                    </small>
                                @enderror
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="imagen" class="form-label custom-section-title"><i
                                        class="fa-solid fa-images"></i> Slide*</label>
                                <input type="file" id="imagen" class="form-control" name="imagen"
                                    accept="image/png, image/jpeg">
                                <small class="form-text text-muted">Deja este campo vacío si no deseas actualizar
                                    el
                                    archivo.</small>
                                @error('imagen')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="previewSlide" class="form-label custom-section-title"><i
                                        class="fa-solid fa-image-portrait"></i> Vista previa del slide</label>
                                <div id="previewSlide">
                                    <!-- Vista previa del ícono se mostrará aquí -->
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="orden" class="form-label custom-section-title"><i
                                        class="fa-solid fa-sort"></i>Orden*</label>
                                <select name="orden" class="form-select @error('orden') is-invalid @enderror"
                                    required>
                                    <option value="" disabled selected>Elija una opción</option>
                                    @foreach (range(1, $totalSlides + 1) as $order)
                                        <option value="{{ $order }}"
                                            {{ in_array($order, $usedOrders) ? 'disabled' : '' }}>
                                            {{ $order }}
                                            {{ in_array($order, $usedOrders) ? '(Ocupado)' : '' }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('orden')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="activo" class="form-label custom-section-title"><i
                                        class="fa-solid fa-user-check"></i> ¿Está activo?*</label>
                                <select name="activo" class="form-select @error('activo') is-invalid @enderror"
                                    required>
                                    <option value="" disabled selected>Elija una opción</option>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>

                                @error('activo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="enlace" class="form-label custom-section-title"><i
                                        class="fa-solid fa-link"></i> Enlace</label>
                                <input id="enlace" type="url" name="enlace"
                                    placeholder="Ej. https:/www.google.com..."
                                    class="form-control @error('enlace') is-invalid @enderror" required
                                    value="{{ old('enlace') }}" />
                                @error('enlace')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">* Indica campo obligatorio.</small>
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

</x-app-layout>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicialización de DataTables
        new DataTable('#tabla-municipios', {
            pagingType: "simple_numbers",
            order: [],
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
        const modal = document.getElementById("modalSliderInicio");
        const form = document.getElementById("formSliderInicio");
        const imagenInput = document.getElementById("imagen");
        const previewSlide = document.getElementById("previewSlide");

        // Actualizar vista previa de la imagen seleccionada
        imagenInput.addEventListener("change", () => {
            const file = imagenInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewSlide.innerHTML = `
                    <img src="${e.target.result}" alt="Vista Previa" class="img-fluid" style="max-height: 150px; object-fit: cover;">
                `;
                };
                reader.readAsDataURL(file);
            } else {
                previewSlide.innerHTML =
                    '<small class="text-muted">No se seleccionó ninguna imagen.</small>';
            }
        });
        modal.addEventListener("show.bs.modal", (event) => {
            const button = event.relatedTarget;
            const action = button.getAttribute("data-action");
            const form = document.getElementById(
            "formSliderInicio"); // Asegúrate de que 'form' esté definido aquí
            const modalTitle = modal.querySelector("#modalSliderInicioLabel");
            const methodInput = modal.querySelector("#method");
            const idInput = modal.querySelector("#id");
            const tituloInput = modal.querySelector("#titulo");
            const descripcionInput = modal.querySelector("#descripcion");
            const enlaceInput = modal.querySelector("#enlace");
            const ordenSelect = modal.querySelector(
            "select[name='orden']"); // Referencia al select de orden
            const activoSelect = modal.querySelector("select[name='activo']");
            const previewSlide = document.getElementById("previewSlide");
            const imagenInput = document.getElementById("imagen"); // Referencia al input de imagen


            if (action === "create") {
                form.action = "{{ route('panel-slider-inicio.store') }}";
                form.reset(); // Esto también resetea el select de orden
                methodInput.value = "POST";
                modalTitle.textContent = "Nueva diapositiva";
                idInput.value = "";
                // Limpiar explícitamente los campos por si form.reset() no es suficiente o para consistencia
                tituloInput.value = "";
                descripcionInput.value = "";
                enlaceInput.value = "";
                ordenSelect.value = ""; // Resetear el select a la opción "Elija una opción"
                activoSelect.value = "";
                imagenInput.value = ""; // Limpiar el input de archivo
                previewSlide.innerHTML =
                    '<small class="text-muted">No se ha seleccionado ninguna imagen.</small>';


                // Habilitar todas las opciones de 'orden' que podrían haber sido deshabilitadas
                // por una instancia previa de edición, excepto las que están en $usedOrders (PHP).
                // Esto es un poco más complejo porque $usedOrders es PHP.
                // Lo más simple es que el controlador envíe $usedOrders como un array JS.
                // Por ahora, simplemente reseteamos la selección. La lógica de 'disabled' de Blade
                // se aplica al cargar la página.
                // Si un nuevo slide puede elegir un orden ya ocupado (y la validación del backend lo maneja),
                // entonces no necesitas deshabilitar nada aquí.
                // Si quieres mantener la lógica de deshabilitar opciones usadas:
                const phpUsedOrders = @json($usedOrders ?? []); // Obtener $usedOrders en JS
                Array.from(ordenSelect.options).forEach(option => {
                    if (option.value !== "") { // No tocar la opción "Elija una opción"
                        // Si el valor de la opción está en phpUsedOrders, deshabilitarla.
                        // Esto recrea la lógica de Blade para $usedOrders.
                        option.disabled = phpUsedOrders.includes(parseInt(option.value));
                    }
                });


            } else if (action === "edit") {
                const id = button.getAttribute("data-id");
                const titulo = button.getAttribute("data-titulo");
                const descripcion = button.getAttribute("data-descripcion");
                const enlace = button.getAttribute("data-enlace");
                const imagen = button.getAttribute("data-imagen");
                const activo = button.getAttribute("data-activo");
                const orden = button.getAttribute("data-orden"); // El 'orden' actual del slider

                form.action =
                `/panel-slider-inicio/${id}`; // Asegúrate que esta ruta sea correcta para tu PUT
                methodInput.value = "PUT";
                idInput.value = id;
                modalTitle.textContent = "Editar diapositiva";

                tituloInput.value = titulo;
                descripcionInput.value = descripcion;
                enlaceInput.value = enlace;
                activoSelect.value = activo;

                // --- AJUSTE CRUCIAL PARA 'ORDEN' ---
                // Primero, iteramos sobre las opciones del select 'orden'
                const phpUsedOrdersForEdit = @json($usedOrders ?? []);
                Array.from(ordenSelect.options).forEach(option => {
                    if (option.value === "") return; // Saltar la opción placeholder

                    // Si el valor de la opción es el 'orden' actual del slider que estamos editando,
                    // NOS ASEGURAMOS DE QUE ESTÉ HABILITADA, incluso si estaba en $usedOrders.
                    if (option.value === orden) {
                        option.disabled = false;
                    } else {
                        // Para las otras opciones, mantenemos la lógica de deshabilitar si están en $usedOrders.
                        option.disabled = phpUsedOrdersForEdit.includes(parseInt(option.value));
                    }
                });
                // Luego, establecemos el valor
                ordenSelect.value = orden;
                // --- FIN DEL AJUSTE PARA 'ORDEN' ---

                imagenInput.value =
                ""; // Limpiar el input de archivo, el usuario subirá una nueva si quiere cambiarla
                if (imagen && imagen !== 'null' && imagen !==
                    '') { // Verificar que 'imagen' no sea la string "null" o vacía
                    previewSlide.innerHTML = `
            <img src="${imagen}" alt="Vista Previa" class="img-fluid" style="max-height: 100px; object-fit: cover;">
            <small class="form-text text-muted d-block mt-1">Imagen actual. Seleccione un nuevo archivo para reemplazar.</small>
        `;
                } else {
                    previewSlide.innerHTML =
                        '<small class="text-muted">No hay imagen actual. Seleccione una para agregar.</small>';
                }
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

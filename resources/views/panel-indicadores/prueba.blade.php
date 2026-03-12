<x-app-layout>
    @section('title', 'Indicadores: Crear')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Subir nuevos indicadores') }}
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
    <div class="container bg-white">
        <div class="py-3">
            <div class="encabezado-lista py-3">
                <h2>Subir indicadores masivamente</h2>
            </div>
            <div class="form-container py-3">
                <div class="d-flex flex-column align-items-center gap-3">

                    {{-- Visual Guide Table --}}
                    <div class="table-responsive w-100 mb-4" style="max-width: 1000px;">
                        <h4 class="text-center mb-3 font-weight-bold">Guía de Columnas para Plantilla Excel</h4>
                        <table class="table table-bordered table-sm text-center" style="font-size: 0.85rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>A</th>
                                    <th>B</th>
                                    <th>C</th>
                                    <th>D</th>
                                    <th>E</th>
                                    <th>F</th>
                                    <th>G - S</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>ID</strong><br><small>(Opcional)</small></td>
                                    <td><strong>Nombre Indicador</strong><br><small>(Texto)</small></td>
                                    <td class="table-primary"><strong>Plan Estatal</strong><br><small>(Nombre
                                            Exacto)</small></td>
                                    <td class="table-primary"><strong>Tipo Programa</strong><br><small>(Sectorial,
                                            Especial...)</small></td>
                                    <td class="table-primary"><strong>Nombre Prog. Derivado</strong><br><small>(Nombre
                                            Exacto)</small></td>
                                    <td><strong>Eje / Programa</strong><br><small>(Texto)</small></td>
                                    <td>... Resto de datos<br><small>(Temática, Meta, etc.)</small></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="alert alert-info py-2" style="font-size: 0.9rem;">
                            <i class="fa-solid fa-circle-info"></i> <strong>Nota Importante:</strong> Las columnas
                            <strong>C, D y E</strong> son obligatorias para asignar correctamente el indicador a su
                            programa correspondiente.
                        </div>
                    </div>

                    {{-- Catalog Download Buttons --}}
                    <div class="d-flex gap-2 mb-3">
                        <a href="{{ route('excel.downloadUsuarios') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-users"></i> Catálogo Usuarios
                        </a>
                        <a href="{{ route('excel.downloadInstituciones') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-building"></i> Catálogo Instituciones
                        </a>
                    </div>

                    <a href="{{ route('excel.downloadTemplate') }}" class="button-excel mb-3">
                        <span class="button__text">Descargar plantilla</span>
                        <span class="button__icon">
                            @include('components.svg-download')
                        </span>
                    </a>

                    <form id="file-form" action="{{ route('excel.import') }}" method="POST"
                        enctype="multipart/form-data" class="d-flex flex-column align-items-center gap-3">
                        @csrf
                        <input type="file" name="file" id="file-upload" accept=".xlsx, .xls"
                            style="display: none;" required>

                        <div class="d-flex gap-3">
                            <button type="button" class="button-upload"
                                onclick="document.getElementById('file-upload').click()">
                                <span class="button__text">Subir Archivo</span>
                                <span class="button__icon">
                                    @include('components.svg-upload')
                                </span>
                            </button>
                            <span id="file-name" style="font-weight: bold;"></span>
                            @can('subida-masiva-indicador')
                                <button type="submit" class="button-save text-white">Procesar</button>
                            @endcan
                        </div>
                    </form>

                    <!-- Contenedor para mostrar errores dinámicamente -->
                    <div id="error-container"></div>

                    <!-- Mensajes de error/success que puedan venir desde la sesión -->
                    @if ($errors->has('file'))
                        <div class="alert alert-danger text-center mt-2">
                            {{ $errors->first('file') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger text-center mt-2">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success text-center mt-2">
                            {{ session('success') }}
                        </div>
                    @endif
                </div>
            </div>


            {{-- <form action="{{ route('excel.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" accept=".xlsx, .xls" required>

                <button type="submit">Subir archivo</button>
            </form> --}}
        </div>
    </div>
    {{-- <script>
        document.getElementById('file-upload').addEventListener('change', function() {
            let fileName = this.files[0] ? this.files[0].name : "Ningún archivo seleccionado";
            document.getElementById('file-name').textContent = fileName;
        });
    </script> --}}
    <script>
        // Mostrar el nombre del archivo seleccionado
        document.getElementById('file-upload').addEventListener('change', function() {
            let fileName = this.files[0] ? this.files[0].name : "Ningún archivo seleccionado";
            document.getElementById('file-name').textContent = fileName;
        });

        // Manejar el envío del formulario vía AJAX
        document.getElementById('file-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Limpiar el contenedor de errores
            document.getElementById('error-container').innerHTML = '';

            // Crear FormData
            let formData = new FormData(this);

            // Mostrar loader al procesar el archivo
            Swal.fire({
                title: 'Procesando archivo...',
                html: 'Por favor, espere un momento.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar petición para validar el archivo
            fetch("{{ route('excel.validateFile') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();

                    if (data.error) {
                        // Mostrar error en Sweet Alert
                        Swal.fire('Error', data.error, 'error')
                            .then(() => {
                                // Mostrar el mensaje de error en el contenedor
                                document.getElementById('error-container').innerHTML =
                                    `<div class="alert alert-danger text-center mt-2">${data.error}</div>`;
                            });
                    } else if (data.success) {
                        // Mostrar confirmación para continuar con la importación
                        Swal.fire({
                            title: '¿Está seguro?',
                            text: data.message,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, confirmar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Mostrar loader para la importación final
                                Swal.fire({
                                    title: 'Procesando...',
                                    html: 'Por favor, espere un momento.',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                // Enviar petición para confirmar la importación
                                fetch("{{ route('excel.confirmImport') }}", {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector(
                                                'input[name="_token"]').value,
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({})
                                    })
                                    .then(response => response.json())
                                    .then(result => {
                                        Swal.close();
                                        if (result.error) {
                                            Swal.fire('Error', result.error, 'error')
                                                .then(() => {
                                                    document.getElementById('error-container')
                                                        .innerHTML =
                                                        `<div class="alert alert-danger text-center mt-2">${result.error}</div>`;
                                                });
                                        } else {
                                            Swal.fire('Éxito', result.message, 'success');
                                            // Opcional: reiniciar el formulario
                                            document.getElementById('file-form').reset();
                                            document.getElementById('file-name').textContent = "";
                                        }
                                    })
                                    .catch(err => {
                                        Swal.close();
                                        Swal.fire('Error', 'Ocurrió un error en la importación.',
                                            'error');
                                    });
                            }
                        });
                    }
                })
                .catch(error => {
                    Swal.close();
                    Swal.fire('Error', 'Ocurrió un error al procesar el archivo.', 'error');
                });
        });
    </script>
</x-app-layout>

<x-indicador-municipal-layout>
    @section('title', 'Administración Indicadores Municipales: Inicio')
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('Cargar') }}
        </h2>
    </x-slot>
    <div class="container contenedor-tarjetas contenedor-principal">
        <div class="encabezado-lista w-100">
            <h2>Agregar indicador municipal</h2>
        </div>
        {{-- {{ $errors }} --}}
        <form action="{{ route('panel-indicadores-municipales.store') }}" method="POST" enctype="multipart/form-data"
            novalidate class="w-100">
            @csrf
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-file-signature"></i>
                        Nombre del indicador: *
                    </div>
                    <input name="indicador" type="text" id="indicador" placeholder="Ej. Número de personas que..."
                        class="form-control @error('indicador') is-invalid @enderror" value="{{ old('indicador') }}"
                        @error('indicador') autofocus @enderror autofocus required>
                    @error('indicador')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-calendar-week"></i>
                        Eje del Plan Municipal de Desarrollo: *
                    </div>
                    <input name="eje_indicador" type="text" id="eje_indicador" placeholder="Ej. Eje 1"
                        class="form-control @error('eje_indicador') is-invalid @enderror"
                        value="{{ old('eje_indicador') }}" @error('eje_indicador') autofocus @enderror autofocus
                        required>
                    @error('eje_indicador')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-calendar-week"></i>
                        Temática: *
                    </div>
                    <input name="tematica" type="text" id="tematica" placeholder="Ej. Gobierno Eficiente"
                        class="form-control @error('tematica') is-invalid @enderror" value="{{ old('tematica') }}"
                        @error('tematica') autofocus @enderror autofocus required>
                    @error('tematica')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        Descripción del indicador *
                    </div>
                    <textarea id="descripcion" name="descripcion" placeholder="Ej. Mide el número de personas que..."
                        class="form-control @error('descripcion') is-invalid @enderror" required>{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <small class="invalid-feedback d-block">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        Unidad de medida *
                    </div>
                    <input name="unidad_medida" type="text" id="unidad_medida" placeholder="Ej. Número de personas"
                        class="form-control @error('unidad_medida') is-invalid @enderror"
                        value="{{ old('unidad_medida') }}" @error('unidad_medida') autofocus @enderror autofocus
                        required>
                    @error('unidad_medida')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        Año de la línea base *
                    </div>
                    <input name="linea_base" type="number" id="linea_base" placeholder="Ej. 2021"
                        class="form-control @error('linea_base') is-invalid @enderror" value="{{ old('linea_base') }}"
                        @error('linea_base') autofocus @enderror autofocus required min="1900" max="2099"
                        step="1">

                    @error('linea_base')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        Dato de la linea base *
                    </div>
                    <input name="dato_linea" type="number" id="dato_linea" placeholder="Ej. 5000"
                        class="form-control @error('dato_linea') is-invalid @enderror" value="{{ old('dato_linea') }}"
                        @error('dato_linea') autofocus @enderror autofocus required>
                    @error('dato_linea')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        Meta 2027 *
                    </div>
                    <input name="meta_2024" type="number" id="meta_2024" 
                    
                        class="form-control @error('meta_2024') is-invalid @enderror" value="{{ old('meta_2024') }}"
                        @error('meta_2024') autofocus @enderror autofocus required>
                    @error('meta_2024')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        Fuente del indicador *
                    </div>
                    <textarea id="fuente" name="fuente" placeholder="Ej. Dirección de Recursos Humanos..."
                        class="form-control @error('fuente') is-invalid @enderror" required>{{ old('fuente') }}</textarea>
                    @error('fuente')
                        <small class="invalid-feedback d-block">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        Enlace de la fuente del indicador
                    </div>
                    <input id="liga" name="liga" placeholder="Ej. https://chatgpt.com/" type="url"
                        value="{{ old('liga') }}" class="form-control @error('liga') is-invalid @enderror"
                        required>
                    @error('liga')
                        <small class="invalid-feedback d-block">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        Próxima fecha de actualización
                    </div>
                    <input name="proxima_actualizacion" type="date" id="proxima_actualizacion"
                        class="form-control @error('proxima_actualizacion') is-invalid @enderror"
                        value="{{ old('proxima_actualizacion') }}"
                        @error('proxima_actualizacion') autofocus @enderror autofocus required>
                    @error('proxima_actualizacion')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        Cobertura *
                    </div>
                    <select id="cobertura" name="cobertura"
                        class="form-control @error('cobertura') is-invalid @enderror"
                        @error('cobertura') autofocus @enderror required>
                        <option value="" disabled selected>Seleccione</option>
                        <option value="Municipal">Municipal</option>
                        <option value="Localidad">Localidad</option>
                    </select>
                    @error('cobertura')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        Tendencia *
                    </div>
                    <select id="tendencia" name="tendencia"
                        class="form-control @error('tendencia') is-invalid @enderror"
                        @error('tendencia') autofocus @enderror required>
                        <option value="" disabled selected>Seleccione</option>
                        <option value="Constante">Constante</option>
                        <option value="Mayor es mejor">Mayor es mejor</option>
                        <option value="Menor es mejor">Menor es mejor</option>
                    </select>
                    @error('tendencia')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        Tipo *
                    </div>
                    <select id="id_tipo" name="id_tipo"
                        class="form-control @error('id_tipo') is-invalid @enderror"
                        @error('id_tipo') autofocus @enderror required>
                        <option value="" disabled selected>Seleccione</option>
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                    @error('id_tipo')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        Nivel *
                    </div>
                    <select id="id_nivel" name="id_nivel"
                        class="form-control @error('id_nivel') is-invalid @enderror"
                        @error('id_nivel') autofocus @enderror required disabled>
                        <option value="" disabled selected>Seleccione</option>
                    </select>
                    @error('id_nivel')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        Dimensión *
                    </div>
                    <select id="id_dimension" name="id_dimension"
                        class="form-control @error('id_dimension') is-invalid @enderror"
                        @error('id_dimension') autofocus @enderror required disabled>
                        <option value="" disabled selected>Seleccione</option>
                    </select>
                    @error('id_dimension')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        Formula del indicador *
                    </div>
                    <textarea id="formula" name="formula" placeholder="Ej. (Número de personas/Poblacion total)..."
                        class="form-control @error('formula') is-invalid @enderror" required>{{ old('formula') }}</textarea>
                    @error('formula')
                        <small class="invalid-feedback d-block">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-8 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        Dependencia y/o área responsable *
                    </div>
                    <input name="dependencia" type="text" id="dependencia"
                        placeholder="Ej. Dirección de Presupuesto..."
                        class="form-control @error('dependencia') is-invalid @enderror"
                        value="{{ old('dependencia') }}" @error('dependencia') autofocus @enderror autofocus
                        required>
                    @error('dependencia')
                        <small class="invalid-feedback d-block">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                        ¿La información es pública? *
                    </div>
                    <select class="form-control @error('publica') is-invalid @enderror" id="publica"
                        name="publica" required>
                        <option value="" disabled selected>Seleccione</option>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                    @error('publica')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <div class="custom-section-title"><i class="fas fa-bullseye"></i> ODS *</div>
                    <select name="id_ods[]" id="id_ods" class="form-control" multiple>
                        <option value="" disabled>Seleccione</option>
                        @foreach ($odes as $ods)
                            <option value="{{ $ods->id }}">
                                {{ $ods->id }} - {{ $ods->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_ods')
                        <small class="invalid-feedback d-block">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="row">
                    <div class="custom-section-title"><i class="fa-solid fa-calendar"></i>
                        Datos del indicador
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="custom-section-title"><i class="fa-solid fa-calendar"></i>
                            Año *
                        </div>
                        <select id="ano" name="ano"
                            class="form-control @error('ano') is-invalid @enderror" required>
                            <option value="" disabled selected>Seleccione un año</option>
                            @for ($i = 2010; $i <= 2024; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                        @error('ano')
                            <small class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </small>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="custom-section-title"><i class="fa-solid fa-check-circle"></i>
                            Periodicidad *
                        </div>
                        <select id="periodicidad_id" name="periodicidad_id"
                            class="form-control @error('periodicidad_id') is-invalid @enderror"
                            @error('periodicidad_id') autofocus @enderror required>
                            <option value="" disabled selected>Seleccione</option>
                            @foreach ($periodicidades as $periodicidad)
                                <option value="{{ $periodicidad->id }}">{{ $periodicidad->nombre }}</option>
                            @endforeach
                        </select>
                        @error('periodicidad_id')
                            <small class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </small>
                        @enderror
                    </div>

                    <div id="input-periodicidad" class="row"></div>
                </div>
            </div>

            <div class="mb-3">
                <div class="col-md-12 d-flex justify-content-evenly">
                    <a href="{{ route('panel-indicadores-municipales.index') }}" class="btn btn-secondary w-100">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-success w-100">
                        Subir
                    </button>
                </div>
            </div>

        </form>
    </div>
    <script>
        $(document).ready(function() {
            // Cuando se cambia la selección de Tipo
            $('#id_tipo').change(function() {
                var tipoId = $(this).val();

                // Si hay una selección de tipo, cargamos los niveles relacionados
                if (tipoId) {
                    $.ajax({
                        url: '/niveles/' + tipoId, // La ruta que devolverá los niveles
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            // Limpiar el select de Nivel
                            $('#id_nivel').empty().prop('disabled', false);
                            $('#id_nivel').append(
                                '<option value="" disabled selected>Seleccione</option>');

                            // Agregar las opciones de Nivel
                            $.each(data, function(key, value) {
                                $('#id_nivel').append('<option value="' + value.id +
                                    '">' + value.nombre + '</option>');
                            });
                        }
                    });
                } else {
                    $('#id_nivel').empty().prop('disabled', true);
                    $('#id_dimension').empty().prop('disabled', true);
                }
            });

            // Cuando se cambia la selección de Nivel
            $('#id_nivel').change(function() {
                var nivelId = $(this).val();

                // Si hay una selección de nivel, cargamos las dimensiones relacionadas
                if (nivelId) {
                    $.ajax({
                        url: '/dimensiones/' + nivelId, // La ruta que devolverá las dimensiones
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            // Limpiar el select de Dimensión
                            $('#id_dimension').empty().prop('disabled', false);
                            $('#id_dimension').append(
                                '<option value="" disabled selected>Seleccione</option>');

                            // Agregar las opciones de Dimensión
                            $.each(data, function(key, value) {
                                $('#id_dimension').append('<option value="' + value.id +
                                    '">' + value.nombre + '</option>');
                            });
                        }
                    });
                } else {
                    $('#id_dimension').empty().prop('disabled', true);
                }
            });
        });
        // Limitar la selección a un máximo de 3 opciones usando SweetAlert2
        $('#id_ods').on('change', function() {
            var selectedOptions = $(this).val();
            if (selectedOptions.length > 3) {
                // Usar SweetAlert2 para mostrar el mensaje
                Swal.fire({
                    icon: 'warning',
                    title: '¡Cuidado!',
                    text: 'Puedes seleccionar un máximo de 3 opciones.',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#3085d6',
                });

                // Limitar la selección a las primeras 3 opciones
                $(this).val(selectedOptions.slice(0, 3)); // Limitar la selección a las primeras 3 opciones
            }
        });
    </script>
    <script>
        document.getElementById('periodicidad_id').addEventListener('change', function() {
            var periodicidadId = this.value;
            var inputContainer = document.getElementById('input-periodicidad');
            inputContainer.innerHTML = ''; // Limpiar los inputs anteriores

            // Obtener el año seleccionado
            var ano = document.getElementById('ano').value;

            // Verificar si se ha seleccionado un año
            if (ano === "") {
                Swal.fire({
                    icon: 'warning',
                    title: '¡Cuidado!',
                    text: 'Primero debes elegir el año.',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#3085d6',
                });
                return;
            }

            // Definir un array con etiquetas para los periodos según la periodicidad
            var periodos = {
                1: 'Anual',
                2: 'Bimestral',
                3: 'Cuatrimestral',
                4: 'Mensual',
                5: 'Semestral',
                6: 'Trimestral'
            };

            // Número de periodos según la periodicidad
            var numPeriodos = {
                1: 1, // Anual
                2: 6, // Bimestral
                3: 3, // Cuatrimestral
                4: 12, // Mensual
                5: 2, // Semestral
                6: 4 // Trimestral
            };

            // Generar los campos dinámicos
            if (periodicidadId && numPeriodos[periodicidadId]) {
                for (let i = 1; i <= numPeriodos[periodicidadId]; i++) {
                    inputContainer.innerHTML += `
                <div class="col-md-4 mb-3">
                    <label for="dato_${i}">Dato ${periodos[periodicidadId]} ${i} *</label>
                    <input type="number" id="dato_${i}" name="datos_periodos[${i - 1}][dato]" class="form-control">
                </div>
                <div class="col-md-8 mb-3">
                    <label for="resultado_${i}">Resultado ${periodos[periodicidadId]} ${i}</label>
                    <input type="text" id="resultado_${i}" name="datos_periodos[${i - 1}][resultado]" class="form-control">
                </div>
                `;
                }
            }
        });
    </script>

</x-indicador-municipal-layout>

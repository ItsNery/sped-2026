<x-app-layout>
    @section('title', 'Indicadores: Crear')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Subir nuevo indicador') }}
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
        <div class="form-container py-3">
            <div class="encabezado-lista py-3">
                <h2>Subir indicador</h2>
            </div>
            <form action="{{ route('panel-indicadores.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="container mb-5">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-file-signature"></i>
                                Nombre del indicador: <span class="text-danger">*</span>
                            </div>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                id="nombre" name="nombre" value="{{ old('nombre') }}"
                                placeholder="Ej. Número de personas que recibieron...">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="custom-section-title"><i class="fa-regular fa-building"></i>
                                Institución responsable: <span class="text-danger">*</span>
                            </div>
                            <select name="id_institucion" id="id_institucion" class="form-control">
                                <option value="" disabled {{ old('id_institucion') ? '' : 'selected' }}>
                                    Seleccione
                                </option>
                                @foreach ($instituciones as $institucion)
                                    <option value="{{ $institucion->id }}"
                                        {{ old('id_institucion') == $institucion->id ? 'selected' : '' }}>
                                        {{ $institucion->id }} - {{ $institucion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_institucion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-user-plus"></i>
                                Usuario a cargo:
                            </div>
                            <select name="id_usuario" id="id_usuario" class="form-control">
                                <option value="" disabled {{ old('id_usuario') ? '' : 'selected' }}>Seleccione
                                </option>
                                @foreach ($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}"
                                        {{ old('id_usuario') == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->id }} - {{ $usuario->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_usuario')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-sitemap"></i>
                                Alineación: <span class="text-danger">*</span>
                            </div>
                        </div>

                        {{-- SELECCIÓN DE PLAN ESTATAL --}}
                        <div class="col-md-6 mb-2">
                            <label>Plan Estatal de Desarrollo <span class="text-danger">*</span></label>
                            <select name="plan_id" id="plan_id" class="form-control" required>
                                <option value="" disabled selected>Seleccione un Plan...</option>
                                @foreach ($planes as $plan)
                                    <option value="{{ $plan->id }}"
                                        {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- SWITCH PARA PROGRAMA DERIVADO --}}
                        <div class="col-md-6 mb-2 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="es_programa_derivado"
                                    name="es_programa_derivado" value="1"
                                    {{ old('es_programa_derivado') ? 'checked' : '' }}>
                                <label class="form-check-label font-weight-bold" for="es_programa_derivado">
                                    ¿Pertenece a un Programa Derivado?
                                </label>
                            </div>
                        </div>

                        {{-- CONTENEDOR DE PROGRAMAS DERIVADOS (OCULTO POR DEFECTO) --}}
                        <div class="col-md-12 row mb-2" id="programa_derivado_container" style="display: none;">
                            <div class="col-md-6 mb-2">
                                <label>Tipo de Programa Derivado <span class="text-danger">*</span></label>
                                <select name="tipo_programa" id="tipo_programa" class="form-control">
                                    <option value="" disabled selected>Seleccione Tipo...</option>
                                    <option value="Programa Especial"
                                        {{ old('tipo_programa') == 'Programa Especial' ? 'selected' : '' }}>Programa
                                        Especial</option>
                                    <option value="Programa Institucional"
                                        {{ old('tipo_programa') == 'Programa Institucional' ? 'selected' : '' }}>
                                        Programa Institucional</option>
                                    <option value="Programa Regional"
                                        {{ old('tipo_programa') == 'Programa Regional' ? 'selected' : '' }}>Programa
                                        Regional</option>
                                    <option value="Programa Sectorial"
                                        {{ old('tipo_programa') == 'Programa Sectorial' ? 'selected' : '' }}>Programa
                                        Sectorial</option>
                                </select>
                                @error('tipo_programa')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-2">
                                <label>Programa Específico <span class="text-danger">*</span></label>
                                <select name="programa_id" id="programa_id" class="form-control" disabled>
                                    <option value="">Seleccione primero el tipo...</option>
                                </select>
                                @error('programa_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- EJE (ANTES 'PROGRAMA') --}}
                        <div class="col-md-6 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-book"></i>
                                Eje del Plan / Programa: <span class="text-danger">*</span>
                            </div>
                            <input type="text" class="form-control @error('eje_app') is-invalid @enderror"
                                id="eje_app" name="eje_app" value="{{ old('eje_app') }}"
                                placeholder="Eje al que pertenece">
                            @error('eje_app')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- Comentado el 20 de mayo, debido a que no hay cod_tematica    --}}
                        {{-- <div class="col-md-2 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-barcode"></i>
                                Cod. Temática:
                            </div>
                            <input type="text" class="form-control @error('cod_tematica') is-invalid @enderror"
                                id="cod_tematica" name="cod_tematica" value="{{ old('cod_tematica') }}"
                                placeholder="Ej. S01.1">
                            @error('cod_tematica')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div> --}}
                        <div class="col-md-6 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-bookmark"></i>
                                Temática: <span class="text-danger">*</span>
                            </div>
                            <input type="text" class="form-control @error('tematica') is-invalid @enderror"
                                id="tematica" name="tematica" value="{{ old('tematica') }}"
                                placeholder="Ej. Certeza Jurídica">
                            @error('tematica')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-text-width"></i>
                                Definición o descripción: <span class="text-danger">*</span>
                            </div>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion"
                                rows="1" placeholder="Ej. El indicador tiene en cuenta el número de personas que...">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="custom-section-title"><i class="fas fa-sync-alt"></i>
                                Periodicidad o Frecuencia de medición: <span class="text-danger">*</span>
                            </div>
                            <select name="periodicidad" id="periodicidad" class="form-control">
                                <option value="" disabled>Seleccione</option>
                                @foreach ($periodicidades as $periodicidad)
                                    <option value="{{ $periodicidad }}"
                                        {{ old('periodicidad') == $periodicidad ? 'selected' : '' }}>
                                        {{ $periodicidad }}
                                    </option>
                                @endforeach
                            </select>
                            @error('periodicidad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-square-root-variable"></i>
                                Fórmula: <span class="text-danger">*</span>
                            </div>
                            <textarea class="form-control @error('formula') is-invalid @enderror" id="formula" name="formula" rows="1"
                                placeholder="Ej. Número de personas/100...">{{ old('formula') }}</textarea>
                            @error('formula')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-ruler"></i>
                                Unidad de medida: <span class="text-danger">*</span>
                            </div>
                            <input type="text" class="form-control @error('unidad_medida') is-invalid @enderror"
                                id="unidad_medida" name="unidad_medida" value="{{ old('unidad_medida') }}"
                                placeholder="Ej. Personas">
                            @error('unidad_medida')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-chart-line"></i>
                                Tendencia o sentido: <span class="text-danger">*</span>
                            </div>
                            <select name="tendencia" id="tendencia" class="form-control">
                                <option value="" disabled>Seleccione</option>
                                @foreach ($tendencias as $tendencia)
                                    <option value="{{ $tendencia }}"
                                        {{ old('tendencia') == $tendencia ? 'selected' : '' }}>
                                        {{ $tendencia }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tendencia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="custom-section-title"><i class="fa-regular fa-chart-bar"></i>
                                Año de Línea Base: <span class="text-danger">*</span>
                            </div>
                            <input type="number" min="1900" max="2099"
                                class="form-control @error('linea_base') is-invalid @enderror" id="linea_base"
                                name="linea_base" value="{{ old('linea_base') }}" placeholder="Ej. 2025">
                            @error('linea_base')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-arrow-up-1-9"></i>
                                Dato de la Línea Base: <span class="text-danger">*</span>
                            </div>
                            <input type="number" class="form-control @error('dato_linea_base') is-invalid @enderror"
                                id="dato_linea_base" name="dato_linea_base" value="{{ old('dato_linea_base') }}"
                                placeholder="Ej. 100">
                            @error('dato_linea_base')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-bullseye"></i>
                                Meta 2030: <span class="text-danger">*</span>
                            </div>
                            <input type="number" class="form-control @error('meta_2024') is-invalid @enderror"
                                id="meta_2024" name="meta_2024" value="{{ old('meta_2024') }}"
                                placeholder="Ej. 200">
                            @error('meta_2024')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-tower-broadcast"></i>
                                Fuente: <span class="text-danger">*</span>
                            </div>
                            <textarea class="form-control @error('fuente') is-invalid @enderror" id="fuente" name="fuente" rows="1"
                                placeholder="Ej. Secretariado Ejecutivo de...">{{ old('fuente') }}</textarea>
                            @error('fuente')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-link"></i>
                                URL de consulta:
                            </div>
                            <input type="url" class="form-control @error('liga') is-invalid @enderror"
                                id="liga" name="liga" value="{{ old('liga') }}"
                                placeholder="Ej. https://www.gob.mx/">
                            {{-- <textarea class="form-control @error('liga') is-invalid @enderror" id="liga" name="liga" rows="3">{{ old('liga', $indicador->liga) }}</textarea> --}}
                            @error('liga')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-chart-area"></i>
                                Cobertura geográfica: <span class="text-danger">*</span>
                            </div>
                            <select name="cobertura" id="cobertura" class="form-control">
                                <option value="" disabled>Seleccione</option>
                                @foreach ($coberturas as $cobertura)
                                    <option value="{{ $cobertura }}"
                                        {{ old('cobertura') == $cobertura ? 'selected' : '' }}>
                                        {{ $cobertura }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cobertura')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-calendar-day"></i>
                                Fecha de próxima actualización:
                            </div>
                            <input type="date"
                                class="form-control @error('fecha_actualizacion') is-invalid @enderror"
                                id="fecha_actualizacion" name="fecha_actualizacion"
                                value="{{ old('fecha_actualizacion') }}">
                            @error('fecha_actualizacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        {{-- <div class="col-md-3 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-timeline"></i>
                                Periodo:
                            </div>
                            <input type="text" class="form-control @error('periodo') is-invalid @enderror"
                                id="periodo" name="periodo" value="{{ old('periodo') }}"
                                placeholder="Ej. 1er Semestre">
                            @error('periodo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div> --}}










                        {{-- <div class="col-md-3 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-building-un"></i>
                                ODS: *
                            </div>
                            <select name="odses[]" id="odses" class="form-control" multiple>
                                @foreach ($odses as $item)
                                    <option value="{{ $item->id }}"
                                        {{ in_array($item->id, old('ods', [])) ? 'selected' : '' }}>
                                        {{ $item->id }} - {{ $item->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('odses')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div> --}}
                        {{-- <div class="col-md-6 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-quote-right"></i>
                                Principales Resultados:
                            </div>
                            <textarea class="form-control @error('resultados') is-invalid @enderror" id="resultados" name="resultados"
                                rows="4" placeholder="Ej. A lo largo del 2025 se ha logrado...">{{ old('resultados') }}</textarea>
                            @error('resultados')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div> --}}
                        <div class="col-md-12 mt-4"> {{-- Añadido mt-4 para un poco de espacio --}}
                            <div class="custom-section-title">
                                <i class="fa-solid fa-box-archive"></i> Histórico de Datos Anuales:
                            </div>

                            {{-- Contenedor para los bloques de datos anuales --}}
                            <div id="datos-anuales-container">
                                {{-- En el formulario de creación, este contenedor estará vacío inicialmente --}}
                                {{-- Se poblará dinámicamente con JavaScript --}}
                            </div>

                            <div class="form-group mt-3">
                                <button type="button" id="add-dato-anual-button" class="btn btn-success">
                                    <i class="fa fa-plus"></i> Añadir Año al Histórico
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="d-flex justify-content-center">
                    <button class="button-save" type="submit">
                        <span class="button__text">Guardar</span>
                        @include('components.svg-save')
                    </button>
                    {{-- <button type="submit" class="boton-guardar w-100">Subir</button> --}}
                    <a href="{{ route('panel-indicadores.index') }}" class="text-decoration-none ">
                        {{-- <button class="boton-cancelar w-100" type="button">
                            Cancelar
                        </button> --}}
                        <button class="button-cancel" type="button">
                            <span class="button__text">Cancelar</span>
                            @include('components.svg-cancel')
                        </button>
                    </a>
                </div>
            </form>
            <div id="dato-anual-template" style="display: none;">
                <div class="dato-anual-block card mb-3"> {{-- Usamos la clase 'card' de Bootstrap para mejor presentación --}}
                    <div class="card-body">
                        <h5 class="card-title">Nuevo Año</h5>
                        <div class="form-group row mb-2"> {{-- Añadido mb-2 para espaciado interno --}}
                            <label class="col-sm-3 col-form-label">Año del Dato <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control anio-input"
                                    name="datos_anuales[__INDEX__][anio]" {{-- Correcto --}}
                                    placeholder="Ej: {{ date('Y') - 1 }}" required>
                                {{-- Aquí podrías añadir un div para mensajes de error de validación si es necesario --}}
                                <div class="invalid-feedback" data-input-name="datos_anuales.__INDEX__.anio"></div>
                            </div>
                        </div>

                        <div class="form-group row mb-2">
                            <label class="col-sm-3 col-form-label">Valor del Dato <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="number" step="any" class="form-control valor-dato-input"
                                    name="datos_anuales[__INDEX__][valor_dato]"
                                    placeholder="Valor numérico (ej: 123.45)">
                            </div>
                        </div>

                        <div class="form-group row mb-2">
                            <label class="col-sm-3 col-form-label">Fecha de Próxima Actualización</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control fecha-actualizacion-input"
                                    name="datos_anuales[__INDEX__][fecha_actualizacion]">
                            </div>
                        </div>

                        <div class="form-group row mb-2">
                            <label class="col-sm-3 col-form-label">Resultados (anual)</label>
                            <div class="col-sm-9">
                                <textarea class="form-control resultados-input" name="datos_anuales[__INDEX__][resultados]" rows="2"
                                    placeholder="Resultados específicos de este año"></textarea>
                            </div>
                        </div>

                        <div class="form-group row mb-2">
                            <label class="col-sm-3 col-form-label">Evidencia (anual)</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control evidencia-input"
                                    name="datos_anuales[__INDEX__][evidencia]"
                                    placeholder="URL o referencia a la evidencia de este año">
                            </div>
                        </div>

                        <div class="form-group row mb-2">
                            <label class="col-sm-3 col-form-label">Observaciones (anual)</label>
                            <div class="col-sm-9">
                                <textarea class="form-control observaciones-input" name="datos_anuales[__INDEX__][observaciones]" rows="2"
                                    placeholder="Observaciones específicas de este año"></textarea>
                            </div>
                        </div>
                        <div class="text-right mt-2"> {{-- Añadido mt-2 para espaciar el botón --}}
                            <button type="button" class="btn btn-danger btn-sm remove-dato-anual">Eliminar
                                Año</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // -- Elements --
            const planSelect = document.getElementById('plan_id');
            const derivedSwitch = document.getElementById('es_programa_derivado');
            const derivedContainer = document.getElementById('programa_derivado_container');
            const tipoProgramaSelect = document.getElementById('tipo_programa');
            const programaSelect = document.getElementById('programa_id');

            // -- Logic --

            // 1. Toggle Derived Program Container
            function toggleDerivedContainer() {
                if (derivedSwitch.checked) {
                    derivedContainer.style.display = 'flex'; // Use flex because it's a row
                    tipoProgramaSelect.setAttribute('required', 'required');
                    programaSelect.setAttribute('required', 'required');
                } else {
                    derivedContainer.style.display = 'none';
                    tipoProgramaSelect.removeAttribute('required');
                    programaSelect.removeAttribute('required');

                    // Reset selections when hiding
                    tipoProgramaSelect.value = "";
                    programaSelect.innerHTML = '<option value="">Seleccione primero el tipo...</option>';
                    programaSelect.setAttribute('disabled', 'disabled');
                }
            }

            derivedSwitch.addEventListener('change', toggleDerivedContainer);
            toggleDerivedContainer(); // Run on load (for validation errors)

            // 2. Fetch Programs when Type Changes
            async function fetchPrograms() {
                const planId = planSelect.value;
                const tipo = tipoProgramaSelect.value;

                if (!planId) {
                    alert('Por favor seleccione primero un Plan Estatal.');
                    tipoProgramaSelect.value = ""; // Reset type if no plan
                    return;
                }

                if (!tipo) {
                    programaSelect.innerHTML = '<option value="">Seleccione primero el tipo...</option>';
                    programaSelect.setAttribute('disabled', 'disabled');
                    return;
                }

                // Show loading state
                programaSelect.innerHTML = '<option value="">Cargando programas...</option>';
                programaSelect.setAttribute('disabled', 'disabled');

                try {
                    const response = await fetch(
                        `{{ route('api.programas_derivados') }}?plan_id=${planId}&tipo=${tipo}`);
                    const programs = await response.json();

                    // Clear options
                    programaSelect.innerHTML =
                        '<option value="" disabled selected>Seleccione un Programa...</option>';

                    if (programs.length > 0) {
                        programaSelect.removeAttribute('disabled');
                        programs.forEach(program => {
                            const option = document.createElement('option');
                            option.value = program.id;
                            option.textContent = program.nombre;

                            // Pre-select if old input exists
                            const oldProgramaId = "{{ old('programa_id') }}";
                            if (oldProgramaId && oldProgramaId == program.id) {
                                option.selected = true;
                            }

                            programaSelect.appendChild(option);
                        });
                    } else {
                        programaSelect.innerHTML =
                            '<option value="">No hay programas disponibles para este plan/tipo.</option>';
                    }

                } catch (error) {
                    console.error('Error fetching programs:', error);
                    programaSelect.innerHTML = '<option value="">Error al cargar programas.</option>';
                }
            }

            tipoProgramaSelect.addEventListener('change', fetchPrograms);
            planSelect.addEventListener('change', function() {
                // If plan changes, and we are in derived mode, we might need to reset or refresh programs
                if (derivedSwitch.checked && tipoProgramaSelect.value) {
                    fetchPrograms();
                }
            });

            // Trigger fetch if we have old values (validation fail)
            if (derivedSwitch.checked && tipoProgramaSelect.value && planSelect.value) {
                fetchPrograms();
            }


            // --- Existing JavaScript for Datos Anuales ---
            const container = document.getElementById('datos-anuales-container');
            const addButton = document.getElementById('add-dato-anual-button');
            const templateElement = document.getElementById('dato-anual-template');

            if (!templateElement) {
                console.error('Error: No se encontró el elemento #dato-anual-template.');
                return;
            }
            const templateHtml = templateElement.innerHTML;
            console.log('Contenido de la plantilla capturada (templateHtml):', templateHtml); // <--- AÑADE ESTO

            let datoAnualIndex = 0;

            addButton.addEventListener('click', function() {
                console.log('Botón "Añadir Año" clickeado. Índice actual:',
                    datoAnualIndex); // <--- AÑADE ESTO

                const newBlockHtml = templateHtml.replace(/__INDEX__/g, datoAnualIndex);
                console.log('HTML después de reemplazar __INDEX__ (newBlockHtml):',
                    newBlockHtml); // <--- AÑADE ESTO

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = newBlockHtml;
                const newBlockElement = tempDiv.firstElementChild;

                if (newBlockElement) {
                    console.log('Elemento a añadir:', newBlockElement); // <--- AÑADE ESTO
                    container.appendChild(newBlockElement);
                } else {
                    console.error(
                        'Error: No se pudo crear el nuevo bloque de dato anual desde la plantilla procesada.'
                    );
                }

                datoAnualIndex++;
            });

            // Delegación de eventos para el botón "Eliminar Año"
            container.addEventListener('click', function(event) {
                if (event.target && event.target.classList.contains('remove-dato-anual')) {
                    // Encontrar el div 'dato-anual-block' padre y eliminarlo
                    const blockToRemove = event.target.closest('.dato-anual-block');
                    if (blockToRemove) {
                        blockToRemove.remove();
                    }
                    // Nota: No necesitamos re-indexar los 'name' de los inputs restantes.
                    // El backend de Laravel manejará correctamente los arrays con índices no secuenciales
                    // si se eliminan elementos intermedios.
                }
            });

            // Opcional: Si quieres que se añada un primer año automáticamente al cargar la página
            // addButton.click(); // Descomenta esta línea si quieres un bloque de año por defecto
        });
    </script>
    {{-- Cometado el 20 de mayo mientras no hay alineación a ODS --}}
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectElement = document.getElementById('odses');

            selectElement.addEventListener('change', function() {
                const selectedOptions = Array.from(selectElement.selectedOptions).length;

                if (selectedOptions > 3) {
                    // Mostrar SweetAlert
                    Swal.fire({
                        icon: 'warning',
                        title: '¡Límite excedido!',
                        text: 'Solo puedes seleccionar hasta 3 opciones.',
                    });

                    // Deshacer la última selección
                    while (selectElement.selectedOptions.length > 3) {
                        selectElement.selectedOptions[selectElement.selectedOptions.length - 1].selected =
                            false;
                    }
                }
            });
        });
    </script> --}}
</x-app-layout>

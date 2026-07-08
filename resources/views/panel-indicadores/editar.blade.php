<x-app-layout>
    @section('title', 'Indicadores: Editar')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Indicador') }}
        </h2>
    </x-slot>

    <div class="container bg-white">
        <div class="form-container py-3">
            @if ($indicador->programa_derivado === 'Programa Sectorial')
            <img src="{{ asset('assets-administrador/img/editar_sectorial.png') }}" alt="" class="w-100">
            @elseif ($indicador->programa_derivado === 'Programa Especial')
            <img src="{{ asset('assets-administrador/img/editar_especial.png') }}" alt="" class="w-100">
            @elseif ($indicador->programa_derivado === 'Programa Institucional')
            <img src="{{ asset('assets-administrador/img/editar_institucional.png') }}" alt=""
                class="w-100">
            @elseif ($indicador->programa_derivado === 'Programa Regional')
            <img src="{{ asset('assets-administrador/img/editar_regional.png') }}" alt="" class="w-100">
            @endif
            <form action="{{ route('panel-indicadores.update', $indicador) }}" method="POST" novalidate
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="container mb-3">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-file-signature"></i>
                                Nombre del indicador: <span class="text-danger">*</span>
                            </div>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                id="nombre" name="nombre" value="{{ old('nombre', $indicador->nombre) }}">
                            @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @if (auth()->user()->id === 1)
                        <div class="col-md-6 mb-2">
                            <div class="custom-section-title">
                                Institución responsable: <span class="text-danger">*</span>
                            </div>
                            <select name="id_institucion" id="id_institucion" class="form-control">
                                <option value="" disabled>Seleccione</option>
                                @foreach ($instituciones as $institucion)
                                <option value="{{ $institucion->id }}"
                                    {{ $indicador->id_institucion == $institucion->id ? 'selected' : '' }}>
                                    {{ $institucion->id }} - {{ $institucion->nombre }}
                                </option>
                                @endforeach
                            </select>
                            @error('id_institucion')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif
                        @if (auth()->user()->id === 1)
                        <div class="col-md-6 mb-2">
                            <div class="custom-section-title">
                                Usuario a cargo:
                            </div>
                            <select name="id_usuario" id="id_usuario" class="form-control">
                                <option value="" disabled>Seleccione</option>
                                @foreach ($usuarios as $usuario)
                                <option value="{{ $usuario->id }}"
                                    {{ $indicador->id_usuario == $usuario->id ? 'selected' : '' }}>
                                    {{ $usuario->id }} - {{ $usuario->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('id_usuario')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif
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
                                    {{ old('plan_id') == $plan->id || (isset($indicador) && $indicador->indicadorable_type == 'App\Models\CatPlanEstatalDesarrollo' && $indicador->indicadorable_id == $plan->id) || (isset($indicador) && $indicador->indicadorable_type != 'App\Models\CatPlanEstatalDesarrollo' && $indicador->indicadorable && ($indicador->indicadorable->plan_estatal ?? $indicador->indicadorable->plan_id) == $plan->id) ? 'selected' : '' }}>
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
                                    {{ old('es_programa_derivado') || (isset($indicador) && $indicador->indicadorable_type != 'App\Models\CatPlanEstatalDesarrollo' && $indicador->indicadorable_type != 'App\Models\CatEje' && $indicador->indicadorable_type != null) ? 'checked' : '' }}>
                                <label class="form-check-label font-weight-bold" for="es_programa_derivado">
                                    ¿Pertenece a un Programa Derivado?
                                </label>
                            </div>
                        </div>

                        {{-- CONTENEDOR DE PROGRAMAS DERIVADOS --}}
                        @php
                        $isDerived = old('es_programa_derivado');
                        if ($isDerived === null) {
                        $isDerived = isset($indicador) &&
                        $indicador->indicadorable_type != 'App\Models\CatPlanEstatalDesarrollo' &&
                        $indicador->indicadorable_type != 'App\Models\CatEje' &&
                        $indicador->indicadorable_type != null;
                        }

                        $currentType = old('tipo_programa');
                        if (!$currentType && isset($indicador) && $isDerived) {
                        switch ($indicador->indicadorable_type) {
                        case 'App\Models\CatProgramaDerivadoEspecial':
                        $currentType = 'Programa Especial';
                        break;
                        case 'App\Models\CatProgramaDerivadoInstitucional':
                        $currentType = 'Programa Institucional';
                        break;
                        case 'App\Models\CatProgramaDerivadoRegional':
                        $currentType = 'Programa Regional';
                        break;
                        case 'App\Models\CatProgramaDerivadoSectorial':
                        $currentType = 'Programa Sectorial';
                        break;
                        }
                        }
                        $currentProgramId = old('programa_id', $isDerived ? $indicador->indicadorable_id : '');
                        $currentEjeId = old('eje_id', (!$isDerived && $indicador->indicadorable_type == 'App\Models\CatEje') ? $indicador->indicadorable_id : '');
                        @endphp

                        <div class="col-md-12 row mb-2" id="programa_derivado_container"
                            style="display: {{ $isDerived ? 'flex' : 'none' }};">
                            <div class="col-md-6 mb-2">
                                <label>Tipo de Programa Derivado <span class="text-danger">*</span></label>
                                <select name="tipo_programa" id="tipo_programa" class="form-control">
                                    <option value="" disabled {{ !$currentType ? 'selected' : '' }}>Seleccione
                                        Tipo...</option>
                                    <option value="Programa Especial"
                                        {{ $currentType == 'Programa Especial' ? 'selected' : '' }}>Programa Especial
                                    </option>
                                    <option value="Programa Regional"
                                        {{ $currentType == 'Programa Regional' ? 'selected' : '' }}>Programa Regional
                                    </option>
                                    <option value="Programa Sectorial"
                                        {{ $currentType == 'Programa Sectorial' ? 'selected' : '' }}>Programa Sectorial
                                    </option>
                                </select>
                                @error('tipo_programa')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-2">
                                <label>Programa Específico <span class="text-danger">*</span></label>
                                <select name="programa_id" id="programa_id" class="form-control"
                                    data-selected-id="{{ $currentProgramId }}">
                                    <option value="">Cargando...</option>
                                </select>
                                @error('programa_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- CONTENEDOR DE EJES DEL PLAN (VISIBLE CUANDO NO ES PROGRAMA DERIVADO) --}}
                        <div class="col-md-12 row mb-2" id="eje_plan_container" style="display: {{ !$isDerived ? 'flex' : 'none' }};">
                            <div class="col-md-6 mb-2">
                                <label>Eje del Plan Estatal <span class="text-danger">*</span></label>
                                <select name="eje_id" id="eje_id" class="form-control" data-selected-id="{{ $currentEjeId }}">
                                    <option value="" disabled selected>Seleccione un Eje...</option>
                                </select>
                                @error('eje_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 mb-2" style="display: none;">
                            <div class="custom-section-title"><i class="fa-solid fa-book"></i>
                                Eje del Plan / Programa: <span class="text-danger">*</span>
                            </div>
                            <input type="text" class="form-control @error('eje_app') is-invalid @enderror"
                                id="eje_app" name="eje_app" value="{{ old('eje_app', $indicador->programa) }}"
                                placeholder="Eje al que pertenece">
                            @error('eje_app')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- VINCULACIÓN CON PROGRAMAS INSTITUCIONALES (RELACIÓN MUCHOS A MUCHOS) --}}
                        <div class="col-md-12 mb-3 mt-3">
                            <div class="custom-section-title"><i class="fa-solid fa-hotel"></i>
                                Vinculación con Programas Institucionales:
                            </div>
                            <div class="mb-2">
                                <input type="text" class="form-control form-control-sm buscar-prog-inst" 
                                    placeholder="🔍 Buscar por nombre o siglas..." 
                                    data-target="container-prog-inst-editar">
                            </div>
                            <div class="card p-3 container-prog-inst-editar" style="max-height: 250px; overflow-y: auto; border: 1px solid #ced4da; border-radius: 0.25rem;">
                                <div class="row">
                                    @php
                                        $linkedIds = is_array(old('programas_institucionales')) 
                                            ? old('programas_institucionales') 
                                            : (isset($indicador) ? $indicador->programasInstitucionales->pluck('id')->toArray() : []);
                                    @endphp
                                    @foreach ($programasInstitucionales as $progInst)
                                    <div class="col-md-6 mb-2 item-prog-inst" data-nombre="{{ strtolower($progInst->nombre) }}" data-siglas="{{ strtolower($progInst->siglas) }}">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="programas_institucionales[]" 
                                                value="{{ $progInst->id }}" id="prog_inst_{{ $progInst->id }}"
                                                {{ in_array($progInst->id, $linkedIds) ? 'checked' : '' }}>
                                            <label class="form-check-label text-muted" for="prog_inst_{{ $progInst->id }}" style="font-size: 0.9rem;">
                                                <span class="badge text-white mr-1" style="background-color: {{ $progInst->color ?? '#691A32' }};">{{ $progInst->siglas }}</span>
                                                {{ $progInst->nombre }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @error('programas_institucionales')
                            <div class="text-danger mt-1" style="font-size: 0.875em;">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- Comentado por que no habrá código tematica --}}
                        {{-- <div class="col-md-2 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-barcode"></i>
                                Cod. Temática:
                            </div>
                            <input type="text" class="form-control @error('cod_tematica') is-invalid @enderror"
                                id="cod_tematica" name="cod_tematica"
                                value="{{ old('cod_tematica', $indicador->cod_tematica) }}">
                        @error('cod_tematica')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div> --}}
                    <div class="col-md-6 mb-2">
                        <div class="custom-section-title"><i class="fa-solid fa-bookmark"></i>
                            Temática: <span class="text-danger">*</span>
                        </div>
                        <input type="text" class="form-control @error('tematica') is-invalid @enderror"
                            id="tematica" name="tematica" value="{{ old('tematica', $indicador->tematica) }}">
                        @error('tematica')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="custom-section-title"><i class="fa-solid fa-text-width"></i>
                            Definición o descripción: <span class="text-danger">*</span>
                        </div>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion"
                            rows="3">{{ old('descripcion', $indicador->descripcion) }}</textarea>
                        @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="custom-section-title"><i class="fas fa-sync-alt"></i>
                            Periodicidad o frecuencia de medición: <span class="text-danger">*</span>
                        </div>
                        <select name="periodicidad" id="periodicidad" class="form-control">
                            <option value="" disabled>Seleccione</option>
                            @foreach ($periodicidades as $periodicidad)
                            <option value="{{ $periodicidad }}"
                                {{ $indicador->periodicidad == $periodicidad ? 'selected' : '' }}>
                                {{ $periodicidad }}
                            </option>
                            @endforeach
                        </select>
                        @error('periodicidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="custom-section-title">
                            <i class="fa-solid fa-square-root-variable"></i>
                            Fórmula: <span class="text-danger">*</span>
                        </div>
                        <textarea class="form-control @error('formula') is-invalid @enderror" id="formula" name="formula" rows="4">{{ old('formula', $indicador->formula) }}</textarea>
                        @error('formula')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="custom-section-title"><i class="fa-solid fa-ruler"></i>
                            Unidad de medida: <span class="text-danger">*</span>
                        </div>
                        <input type="text" class="form-control @error('unidad_medida') is-invalid @enderror"
                            id="unidad_medida" name="unidad_medida"
                            value="{{ old('unidad_medida', $indicador->unidad_medida) }}">
                        @error('unidad_medida')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="custom-section-title"><i class="fa-solid fa-chart-line"></i>
                            Tendencia: <span class="text-danger">*</span>
                        </div>
                        <select name="tendencia" id="tendencia" class="form-control">
                            <option value="" disabled>Seleccione</option>
                            @foreach ($tendencias as $tendencia)
                            <option value="{{ $tendencia }}"
                                {{ $indicador->tendencia == $tendencia ? 'selected' : '' }}>
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
                            name="linea_base" value="{{ old('linea_base', $indicador->linea_base) }}">
                        @error('linea_base')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="custom-section-title"><i class="fa-solid fa-arrow-up-1-9"></i>
                            Dato de la Línea Base: <span class="text-danger">*</span>
                        </div>
                        <input type="number" class="form-control @error('dato_linea_base') is-invalid @enderror"
                            id="dato_linea_base" name="dato_linea_base"
                            value="{{ old('dato_linea_base', $indicador->dato_linea_base) }}">
                        @error('dato_linea_base')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="custom-section-title"><i class="fa-solid fa-bullseye"></i>
                            Meta 2030: <span class="text-danger">*</span>
                        </div>
                        <input type="number" class="form-control @error('meta_2024') is-invalid @enderror"
                            id="meta_2024" name="meta_2024"
                            value="{{ old('meta_2024', $indicador->meta_2024) }}">
                        @error('meta_2024')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="custom-section-title"><i class="fa-solid fa-tower-broadcast"></i>
                            Fuente: <span class="text-danger">*</span>
                        </div>
                        <textarea class="form-control @error('fuente') is-invalid @enderror" id="fuente" name="fuente" rows="3">{{ old('fuente', $indicador->fuente) }}</textarea>
                        @error('fuente')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="custom-section-title"><i class="fa-solid fa-link"></i>
                            URL de consulta:
                        </div>
                        <input type="url" class="form-control @error('liga') is-invalid @enderror"
                            id="liga" name="liga" value="{{ old('liga', $indicador->liga) }}">
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
                                {{ $indicador->cobertura == $cobertura ? 'selected' : '' }}>
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
                            Próxima fecha de actualización:
                        </div>
                        <input type="date"
                            class="form-control @error('fecha_actualizacion') is-invalid @enderror"
                            id="fecha_actualizacion" name="fecha_actualizacion"
                            value="{{ old('fecha_actualizacion', $indicador->fecha_actualizacion) }}">
                        @error('fecha_actualizacion')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Se comenta por que no hay ods por ahora --}}
                    <!-- <div class="col-md-3 mb-2">
                        <div class="custom-section-title"><i class="fa-solid fa-building-un"></i>
                            Ods: *
                        </div>
                        <select name="odses[]" id="odses" class="form-control" multiple>
                            @foreach ($odeses as $ods)
                            <option value="{{ $ods->id }}"
                                {{ in_array($ods->id, $indicador->ods->pluck('id')->toArray()) ? 'selected' : '' }}>
                                {{ $ods->id }} - {{ $ods->nombre }}
                            </option>
                            @endforeach
                        </select>
                        @error('odses')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div> -->
                    {{-- Se eliminará el campo de resultados --}}
                    {{-- <div class="col-md-6 mb-2">
                            <div class="custom-section-title"><i class="fa-solid fa-quote-right"></i>
                                Principales Resultados:
                            </div>
                            <textarea class="form-control @error('resultados') is-invalid @enderror" id="resultados" name="resultados"
                                rows="4">{{ old('resultados', $indicador->resultados) }}</textarea>
                    @error('resultados')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mt-4">
                    <div class="custom-section-title">
                        <i class="fa-solid fa-box-archive"></i> Histórico de Datos Anuales:
                    </div>

                    {{-- Contenedor para los bloques de datos anuales --}}
                    <div id="datos-anuales-container" class="accordion mb-3" id="accordionDatosAnuales">
                        {{--
            CASO DE EDICIÓN:
            Iteramos sobre los datos anuales existentes del indicador y los mostramos.
            El índice $loop->index nos ayudará a nombrar los campos correctamente.
        --}}
                        @if (isset($indicador) && $indicador->datosAnuales->isNotEmpty())
                        @foreach ($indicador->datosAnuales->sortBy('anio') as $datoAnual)
                        {{-- Usamos $loop->index o una variable manual para el índice --}}
                        @php $currentIndex = $loop->index; @endphp
                        <div class="accordion-item dato-anual-block" id="dato-anual-item-{{ $currentIndex }}">
                            <h2 class="accordion-header" id="heading-{{ $currentIndex }}">
                                <button class="accordion-button collapsed d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse-{{ $currentIndex }}" aria-expanded="false"
                                    aria-controls="collapse-{{ $currentIndex }}">
                                    <div class="w-100 d-flex justify-content-between pr-3 align-items-center">
                                        <div>
                                            <span class="fw-bold me-3 text-dark badge bg-light text-dark border mr-2" style="font-size: 0.95rem;">
                                                Año: <span class="header-anio">{{ $datoAnual->anio }}</span>
                                            </span>
                                            <span class="text-muted">
                                                Valor: <span class="header-valor fw-semibold text-primary">{{ $datoAnual->valor_dato ?? 'Sin registrar' }}</span>
                                            </span>
                                        </div>
                                        @if ($datoAnual->evidencia)
                                        <span class="badge bg-success mr-4"><i class="fa-solid fa-file-pdf"></i> Evidencia</span>
                                        @endif
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse-{{ $currentIndex }}" class="accordion-collapse collapse"
                                aria-labelledby="heading-{{ $currentIndex }}" data-bs-parent="#accordionDatosAnuales">
                                <div class="accordion-body bg-light">
                                    {{-- Campo oculto para el ID del DatoAnual existente, crucial para el update --}}
                                    <input type="hidden" name="datos_anuales[{{ $currentIndex }}][id]"
                                        value="{{ $datoAnual->id }}">

                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label">Año del dato <span
                                                class="text-danger">*</span></label>
                                        <div class="col-sm-9">
                                            <input type="number"
                                                class="form-control anio-input @error('datos_anuales.' . $currentIndex . '.anio') is-invalid @enderror"
                                                name="datos_anuales[{{ $currentIndex }}][anio]"
                                                value="{{ old('datos_anuales.' . $currentIndex . '.anio', $datoAnual->anio) }}"
                                                placeholder="Ej: {{ date('Y') - 1 }}" required>
                                            @error('datos_anuales.' . $currentIndex . '.anio')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label">Valor del dato</label>
                                        <div class="col-sm-9">
                                            <input type="number" step="any"
                                                class="form-control valor-dato-input @error('datos_anuales.' . $currentIndex . '.valor_dato') is-invalid @enderror"
                                                name="datos_anuales[{{ $currentIndex }}][valor_dato]"
                                                value="{{ old('datos_anuales.' . $currentIndex . '.valor_dato', $datoAnual->valor_dato) }}"
                                                placeholder="Valor numérico (ej: 123.45)">
                                            @error('datos_anuales.' . $currentIndex . '.valor_dato')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label">Próxima fecha de
                                            actualización</label>
                                        <div class="col-sm-9">
                                            <input type="date"
                                                class="form-control @error('datos_anuales.' . $currentIndex . '.fecha_actualizacion') is-invalid @enderror"
                                                name="datos_anuales[{{ $currentIndex }}][fecha_actualizacion]"
                                                value="{{ old('datos_anuales.' . $currentIndex . '.fecha_actualizacion', $datoAnual->fecha_actualizacion ? Carbon\Carbon::parse($datoAnual->fecha_actualizacion)->format('Y-m-d') : '') }}">
                                            @error('datos_anuales.' . $currentIndex .
                                            '.fecha_actualizacion')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label">Resultados (anual)</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control @error('datos_anuales.' . $currentIndex . '.resultados') is-invalid @enderror"
                                                name="datos_anuales[{{ $currentIndex }}][resultados]" rows="2">{{ old('datos_anuales.' . $currentIndex . '.resultados', $datoAnual->resultados) }}</textarea>
                                            @error('datos_anuales.' . $currentIndex . '.resultados')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label">Evidencia (PDF)</label>
                                        <div class="col-sm-9">
                                            @if ($datoAnual->evidencia)
                                            <div class="mb-2">
                                                Archivo actual:
                                                <a href="{{ asset('assets-administrador/docs/' . $datoAnual->evidencia) }}"
                                                    target="_blank" class="fw-bold">
                                                    {{ $datoAnual->evidencia }}
                                                </a>
                                                <div class="form-check mt-1">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="datos_anuales[{{ $currentIndex }}][eliminar_evidencia]"
                                                        id="eliminar_evidencia_{{ $currentIndex }}"
                                                        value="1">
                                                    <label class="form-check-label"
                                                        for="eliminar_evidencia_{{ $currentIndex }}">
                                                        Eliminar evidencia actual (si sube uno nuevo,
                                                        este se ignorará)
                                                    </label>
                                                </div>
                                            </div>
                                            @else
                                            <p class="text-muted mb-2">No hay evidencia cargada para este
                                                año.</p>
                                            @endif
                                            <input type="file"
                                                class="form-control @error('datos_anuales.' . $currentIndex . '.evidencia_file') is-invalid @enderror"
                                                name="datos_anuales[{{ $currentIndex }}][evidencia_file]"
                                                accept=".pdf">
                                            <small class="form-text text-muted">Seleccione un nuevo archivo
                                                PDF si desea reemplazar o agregar evidencia.</small>
                                            @error('datos_anuales.' . $currentIndex . '.evidencia_file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            {{-- Guardaremos el nombre del archivo de evidencia existente en un campo oculto si no se elimina y no se sube uno nuevo --}}
                                            @if ($datoAnual->evidencia)
                                            <input type="hidden"
                                                name="datos_anuales[{{ $currentIndex }}][evidencia_actual]"
                                                value="{{ $datoAnual->evidencia }}">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label">Observaciones
                                            (anual)
                                        </label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control @error('datos_anuales.' . $currentIndex . '.observaciones') is-invalid @enderror"
                                                name="datos_anuales[{{ $currentIndex }}][observaciones]" rows="2">{{ old('datos_anuales.' . $currentIndex . '.observaciones', $datoAnual->observaciones) }}</textarea>
                                            @error('datos_anuales.' . $currentIndex . '.observaciones')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="text-right mt-2 d-flex justify-content-end">
                                        <button type="button"
                                            class="btn btn-danger btn-sm remove-dato-anual">
                                            <i class="fa-solid fa-trash-can"></i> Eliminar Año
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>

        <div class="form-group mt-3">
            <button type="button" id="add-dato-anual-button" class="btn btn-success">
                <i class="fa fa-plus"></i> Añadir Nuevo Año al Histórico
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
        <a href="{{ route('panel-indicadores.show', $indicador->id) }}" class="text-decoration-none ">
            <button class="button-cancel" type="button">
                <span class="button__text">Cancelar</span>
                @include('components.svg-cancel')
            </button>
        </a>
    </div>
    </form>
    <div id="dato-anual-template" style="display: none;">
        <div class="accordion-item dato-anual-block" id="dato-anual-item-__INDEX__">
            <h2 class="accordion-header" id="heading-__INDEX__">
                <button class="accordion-button d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapse-__INDEX__" aria-expanded="true"
                    aria-controls="collapse-__INDEX__">
                    <div class="w-100 d-flex justify-content-between pr-3 align-items-center">
                        <div>
                            <span class="fw-bold me-3 text-dark badge bg-light text-dark border mr-2" style="font-size: 0.95rem;">
                                Año: <span class="header-anio">Nueva Entrada</span>
                            </span>
                            <span class="text-muted">
                                Valor: <span class="header-valor fw-semibold text-primary">Sin registrar</span>
                            </span>
                        </div>
                        <span class="badge bg-warning mr-4">Nuevo Año</span>
                    </div>
                </button>
            </h2>
            <div id="collapse-__INDEX__" class="accordion-collapse collapse show"
                aria-labelledby="heading-__INDEX__" data-bs-parent="#accordionDatosAnuales">
                <div class="accordion-body bg-light">
                    <div class="form-group row mb-2">
                        <label class="col-sm-3 col-form-label">Año del dato <span
                                class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control anio-input"
                                name="datos_anuales[__INDEX__][anio]" placeholder="Ej: {{ date('Y') }}"
                                required>
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-sm-3 col-form-label">Valor del dato</label>
                        <div class="col-sm-9">
                            <input type="number" step="any" class="form-control valor-dato-input"
                                name="datos_anuales[__INDEX__][valor_dato]"
                                placeholder="Valor numérico (ej: 123.45)">
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-sm-3 col-form-label">Próxima fecha de actualización</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control"
                                name="datos_anuales[__INDEX__][fecha_actualizacion]">
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-sm-3 col-form-label">Resultados (anual)</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="datos_anuales[__INDEX__][resultados]" rows="2"
                                placeholder="Resultados específicos de este año"></textarea>
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-sm-3 col-form-label">Evidencia (PDF)</label>
                        <div class="col-sm-9">
                            <input type="file" class="form-control"
                                name="datos_anuales[__INDEX__][evidencia_file]" accept=".pdf">
                            <small class="form-text text-muted">Seleccione un archivo PDF.</small>
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-sm-3 col-form-label">Observaciones (anual)</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="datos_anuales[__INDEX__][observaciones]" rows="2"
                                placeholder="Observaciones específicas de este año"></textarea>
                        </div>
                    </div>
                    <div class="text-right mt-2 d-flex justify-content-end">
                        <button type="button" class="btn btn-danger btn-sm remove-dato-anual">
                            <i class="fa-solid fa-trash-can"></i> Eliminar Año
                        </button>
                    </div>
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

            // 1. Toggle Containers
            const ejePlanContainer = document.getElementById('eje_plan_container');
            const ejeIdSelect = document.getElementById('eje_id');
            const ejeAppInput = document.getElementById('eje_app');

            function toggleContainers() {
                if (derivedSwitch.checked) {
                    derivedContainer.style.display = 'flex';
                    ejePlanContainer.style.display = 'none';

                    tipoProgramaSelect.setAttribute('required', 'required');
                    programaSelect.setAttribute('required', 'required');
                    ejeIdSelect.removeAttribute('required');
                } else {
                    derivedContainer.style.display = 'none';
                    ejePlanContainer.style.display = 'flex';

                    tipoProgramaSelect.removeAttribute('required');
                    programaSelect.removeAttribute('required');
                    ejeIdSelect.setAttribute('required', 'required');

                    if (planSelect.value && !ejeIdSelect.value) {
                        fetchEjes(true);
                    }
                }
            }

            derivedSwitch.addEventListener('change', toggleContainers);
            // toggleDerivedContainer(); // Handled by inline style server-side, but good to run to set 'required' attrs

            // 2. Fetch Programs
            async function fetchPrograms(keepSelected = false) {
                const planId = planSelect.value;
                const tipo = tipoProgramaSelect.value;
                const currentSelectedId = programaSelect.getAttribute('data-selected-id');

                if (!planId) {
                    // alert('Por favor seleccione primero un Plan Estatal.');
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

                            // Logic to select correct option
                            if (keepSelected && currentSelectedId && currentSelectedId == program.id) {
                                option.selected = true;
                            }

                            programaSelect.appendChild(option);
                        });
                    } else {
                        programaSelect.innerHTML = '<option value="" disabled selected>No hay programas disponibles para este plan/tipo.</option>';
                        programaSelect.setAttribute('disabled', 'disabled');
                    }

                } catch (error) {
                    console.error('Error fetching programs:', error);
                    programaSelect.innerHTML = '<option value="">Error al cargar programas.</option>';
                }
            }

            // 3. Fetch Ejes
            async function fetchEjes(keepSelected = false) {
                const planId = planSelect.value;
                const currentSelectedId = ejeIdSelect.getAttribute('data-selected-id');

                if (!planId) return;

                ejeIdSelect.innerHTML = '<option value="">Cargando ejes...</option>';
                ejeIdSelect.setAttribute('disabled', 'disabled');

                try {
                    const response = await fetch(`{{ route('api.programas_derivados') }}?plan_id=${planId}&tipo=Eje`);
                    const ejes = await response.json();

                    ejeIdSelect.innerHTML = '<option value="" disabled selected>Seleccione un Eje...</option>';
                    if (ejes.length > 0) {
                        ejeIdSelect.removeAttribute('disabled');
                        ejes.forEach(eje => {
                            const option = document.createElement('option');
                            option.value = eje.id;
                            option.textContent = eje.nombre;

                            if (keepSelected && currentSelectedId && currentSelectedId == eje.id) {
                                option.selected = true;
                                if (!ejeAppInput.value) ejeAppInput.value = eje.nombre;
                            }

                            ejeIdSelect.appendChild(option);
                        });
                    } else {
                        ejeIdSelect.innerHTML = '<option value="" disabled selected>No hay ejes disponibles para este plan.</option>';
                        ejeIdSelect.setAttribute('disabled', 'disabled');
                    }
                } catch (error) {
                    console.error('Error fetching ejes:', error);
                    ejeIdSelect.innerHTML = '<option value="">Error al cargar ejes.</option>';
                }
            }

            ejeIdSelect.addEventListener('change', function() {
                const selectedOption = ejeIdSelect.options[ejeIdSelect.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    ejeAppInput.value = selectedOption.textContent;
                }
            });

            if (programaSelect) {
                programaSelect.addEventListener('change', function() {
                    const selectedOption = programaSelect.options[programaSelect.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        ejeAppInput.value = selectedOption.textContent;
                    }
                });
            }

            tipoProgramaSelect.addEventListener('change', () => fetchPrograms(false));

            // If plan changes, we might need to refresh programs logic
            planSelect.addEventListener('change', function() {
                if (derivedSwitch.checked) {
                    if (tipoProgramaSelect.value) fetchPrograms(false);
                } else {
                    fetchEjes(false);
                }
            });

            // Initial Run for Edit Mode
            toggleContainers();
            if (derivedSwitch.checked && tipoProgramaSelect.value && planSelect.value) {
                fetchPrograms(true);
            } else if (!derivedSwitch.checked && planSelect.value) {
                fetchEjes(true);
            }
            // --- Programas Institucionales Checklist Filter ---
            const searchInput = document.querySelector('.buscar-prog-inst');
            if (searchInput) {
                const targetClass = searchInput.getAttribute('data-target');
                const listContainer = document.querySelector('.' + targetClass);
                if (listContainer) {
                    const items = listContainer.querySelectorAll('.item-prog-inst');
                    searchInput.addEventListener('input', function() {
                        const query = searchInput.value.toLowerCase().trim();
                        items.forEach(item => {
                            const nombre = item.getAttribute('data-nombre') || '';
                            const siglas = item.getAttribute('data-siglas') || '';
                            if (nombre.includes(query) || siglas.includes(query)) {
                                item.style.display = '';
                            } else {
                                item.style.display = 'none';
                            }
                        });
                    });
                }
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

            // Índice inicial para nuevos elementos.
            // Si hay elementos existentes (en modo edición), empezamos después del último índice usado por Blade.
            // Contamos cuántos bloques .dato-anual-block ya existen en el contenedor.
            let datoAnualIndex = container.querySelectorAll('.dato-anual-block').length;
            console.log('Índice inicial para nuevos datos anuales:', datoAnualIndex);


            addButton.addEventListener('click', function() {
                // Collapsing all other accordion items before adding a new one
                const openItems = container.querySelectorAll('.accordion-collapse.show');
                openItems.forEach(item => {
                    const bsCollapse = bootstrap.Collapse.getInstance(item) || new bootstrap.Collapse(item, {toggle: false});
                    bsCollapse.hide();
                });

                const newBlockHtml = templateHtml.replace(/__INDEX__/g, datoAnualIndex);
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = newBlockHtml;
                const newBlockElement = tempDiv.firstElementChild;

                if (newBlockElement) {
                    container.appendChild(newBlockElement);
                } else {
                    console.error(
                        'Error: No se pudo crear el nuevo bloque de dato anual desde la plantilla.');
                }
                datoAnualIndex++;
            });

            container.addEventListener('click', function(event) {
                const removeBtn = event.target.closest('.remove-dato-anual');
                if (removeBtn) {
                    const blockToRemove = removeBtn.closest('.dato-anual-block');
                    if (blockToRemove) {
                        // Si el bloque que se va a eliminar contiene un input con name="...[id]",
                        // podrías querer marcarlo para eliminación en el backend en lugar de solo quitarlo del DOM.
                        // Por ahora, simplemente lo quitamos del DOM. El backend (método update)
                        // tendrá que manejar qué hacer con los IDs que no se reenvían.
                        blockToRemove.remove();
                    }
                }
            });

            // Sync accordion header text on input change
            container.addEventListener('input', function(event) {
                if (event.target) {
                    if (event.target.classList.contains('anio-input')) {
                        const block = event.target.closest('.dato-anual-block');
                        if (block) {
                            const headerAnio = block.querySelector('.header-anio');
                            if (headerAnio) {
                                headerAnio.textContent = event.target.value || '___';
                            }
                        }
                    }
                    if (event.target.classList.contains('valor-dato-input')) {
                        const block = event.target.closest('.dato-anual-block');
                        if (block) {
                            const headerValor = block.querySelector('.header-valor');
                            if (headerValor) {
                                headerValor.textContent = event.target.value || 'Sin registrar';
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
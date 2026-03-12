<x-app-layout>
    @php
        $isEdit = isset($programa) && $programa->exists;
        $title = $isEdit ? 'Editar Programa Derivado Institucional' : 'Crear Programa Derivado Institucional';
        $action = $isEdit
            ? route('panel-cat-prog-der-instit.update', $programa->id)
            : route('panel-cat-prog-der-instit.store');
    @endphp

    @section('title', $title)
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __($title) }}
        </h2>
    </x-slot>

    <div class="contenedor-principal">
        <div class="encabezado-lista my-2">
            <h2>{{ $title }}</h2>
        </div>
        <div class="container">
            <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label class="form-label custom-section-title" for="nombre">
                        <i class="fa-solid fa-file-signature"></i> Nombre del Programa
                    </label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" name="nombre"
                        id="nombre" value="{{ old('nombre', $programa->nombre ?? '') }}" required autofocus>
                    @error('nombre')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label custom-section-title" for="plan_estatal">
                                <i class="fa-solid fa-file-signature"></i> Plan Estatal de Desarrollo
                            </label>
                            <select name="plan_estatal" id="plan_estatal"
                                class="form-select @error('plan_estatal') is-invalid @enderror" required>
                                <option value="">Seleccione un plan...</option>
                                @foreach ($planes as $plan)
                                    <option value="{{ $plan->id }}"
                                        {{ (old('plan_estatal') ?? ($programa->plan_estatal ?? '')) == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_estatal')
                                <small class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label custom-section-title" for="descripcion">
                                <i class="fa-solid fa-file-signature"></i> Descripción
                            </label>
                            <textarea name="descripcion" id="descripcion" rows="4"
                                class="form-control @error('descripcion') is-invalid @enderror" required>{{ old('descripcion', $programa->descripcion ?? '') }}</textarea>
                            @error('descripcion')
                                <small class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label custom-section-title" for="color">
                                <i class="fa-solid fa-file-signature"></i> Color
                            </label>
                            <div class="d-flex items-center flex-row">
                                <input type="color" name="color_picker" id="color_picker" class=""
                                    onchange="document.getElementById('color').value = this.value"
                                    value="{{ old('color', $programa->color ?? '#000000') }}">
                                <input type="text" name="color" id="color"
                                    class="form-control @error('color') is-invalid @enderror"
                                    value="{{ old('color', $programa->color ?? '#000000') }}" maxlength="7" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label custom-section-title" for="imagen">
                            <i class="fa-solid fa-file-signature"></i> Imagen de Portada
                        </label>
                        @if ($isEdit && !empty($programa->imagen))
                            <div class="mb-2">
                                <img src="{{ asset($programa->imagen) }}" alt="Imagen actual"
                                    style="max-height: 100px; max-width: 100px; object-fit: cover;">
                                <small class="text-muted d-block">Imagen actual</small>
                            </div>
                        @endif
                        <input type="file" name="imagen" id="imagen" accept="image/*"
                            class="form-control @error('imagen') is-invalid @enderror" accept="image/*"
                            {{ $isEdit ? '' : 'required' }}>
                        @error('imagen')
                            <small class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </small>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label custom-section-title" for="documento">
                                <i class="fa-solid fa-link"></i> Link del Documento
                            </label>
                            <input type="url" name="documento" id="documento"
                                class="form-control @error('documento') is-invalid @enderror"
                                value="{{ old('documento', $programa->documento ?? '') }}" required
                                placeholder="https://ejemplo.com/documento.pdf">
                            @error('documento')
                                <small class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="mb-3 pb-3 d-flex justify-content-end">
                    <button class="button-save" type="submit">
                        <span class="button__text">{{ $isEdit ? 'Actualizar' : 'Guardar' }}</span>
                        @include('components.svg-save')
                    </button>
                    <a href="{{ route('panel-cat-prog-der-sect.index') }}" class="text-decoration-none">
                        <button class="button-cancel" type="button">
                            <span class="button__text">Cancelar</span>
                            @include('components.svg-cancel')
                        </button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

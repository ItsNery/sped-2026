<x-app-layout>
    @section('title', 'Crear Plan Estatal')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Plan Estatal') }}
        </h2>
    </x-slot>

    <div class="contenedor-principal">
        <div class="encabezado-lista my-2">
            <h2>Crear Plan Estatal</h2>
        </div>
        <div class="container">
            <form action="{{ route('panel-cat-planes.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label custom-section-title" for="nombre">
                        <i class="fa-solid fa-file-signature"></i> Nombre del Plan:
                    </label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" name="nombre"
                        id="nombre" value="{{ old('nombre') }}" required autofocus>
                    @error('nombre')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label custom-section-title" for="gobernador">
                        <i class="fa-solid fa-user-tie"></i> Gobernador:
                    </label>
                    <input type="text" class="form-control @error('gobernador') is-invalid @enderror"
                        name="gobernador" id="gobernador" value="{{ old('gobernador') }}" required>
                    @error('gobernador')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>

                <div class="mb-3 pb-3 d-flex justify-content-end">
                    <button class="button-save" type="submit">
                        <span class="button__text">Guardar</span>
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
        </div>
    </div>
</x-app-layout>

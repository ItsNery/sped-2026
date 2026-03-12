<x-app-layout>

    @if (isset($role))
        @php $action = 'Editar Roles' @endphp
    @else
        @php $action = 'Crear Roles' @endphp
    @endif

    @section('title', $action)
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $action }}
        </h2>
    </x-slot>

    <div class="contenedor-principal">
        <div class="encabezado-lista my-2">
            <h2>{{ $action }}</h2>
        </div>
        <div class="container">
            <form action="{{ isset($role) ? route('panel-roles.update', $role->id) : route('panel-roles.store') }}"
                method="POST" novalidate>
                @if (isset($role))
                    @method('PATCH')
                @endif
                @csrf

                <div class="mb-3">
                    <label class="form-label custom-section-title" for="name"><i
                            class="fa-solid fa-chalkboard-user"></i> Nombre del Rol:</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                        id="name" value="{{ old('name') ?? @$role->name }}" @error('name') autofocus @enderror
                        required>
                    @error('name')
                        <small class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label custom-section-title" for="permisos"><i class="fa-solid fa-user-lock"></i>
                        Permisos para este Rol:</label>
                    @error('permission')
                        <small class="invalid-feedback d-block">
                            <strong>{{ $message }}</strong>
                        </small>
                        {{-- <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>{{ $message }}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div> --}}
                    @enderror
                    <div class="row">
                        @foreach ($permission as $value)
                            <div class="col-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permission[]"
                                        id="permiso_{{ $value->id }}" value="{{ $value->id }}"
                                        {{ isset($rolePermissions) ? (in_array($value->id, $rolePermissions) ? 'checked' : '') : '' }}>
                                    <label class="form-check-label" for="permiso_{{ $value->id }}">
                                        {{ $value->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3 pb-3 d-flex justify-content-end">
                    <button class="button-save" type="submit">
                        <span class="button__text"> {{ isset($user) ? 'Modificar' : 'Guardar' }}</span>
                        @include('components.svg-save')
                    </button>
                    <a href="{{ route('panel-roles.index') }}" class="text-decoration-none">
                        <button class="button-cancel" type="button">
                            <span class="button__text">Cancelar</span>
                            @include('components.svg-cancel')
                        </button>
                    </a>
                    {{-- <button class="boton-guardar me-2"
                        type="submit">
                        {{ isset($role) ? 'Modificar' : 'Guardar' }}
                    </button>
                    <a class="text-decoration-none" href="{{ route('panel-roles.index') }}">
                        <button class="boton-cancelar" type="button">
                            Atrás
                        </button>
                    </a> --}}
                </div>
            </form>
        </div>

    </div>


</x-app-layout>
<script></script>

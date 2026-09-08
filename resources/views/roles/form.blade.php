<x-app-layout>
    @if (isset($role))
        @php $action = 'Editar Rol' @endphp
    @else
        @php $action = 'Crear Rol' @endphp
    @endif

    @section('title', $action)
    <x-slot name="header">
        <div class="exec-header admin-index-header">
            <div>
                <span class="exec-eyebrow">Administración del sistema</span>
                <h2 class="exec-header__title">{{ $action }}</h2>
            </div>
            <span class="exec-header__plan">Permisos y perfiles</span>
        </div>
    </x-slot>
    <div class="admin-index">
        <div class="contenedor-principal admin-index__surface mx-auto">
            <form action="{{ isset($role) ? route('panel-roles.update', $role->id) : route('panel-roles.store') }}"
                method="POST" novalidate>
                @if (isset($role))
                    @method('PATCH')
                @endif
                @csrf

                <div class="mb-3">
                    <label class="form-label custom-section-title" for="name"><i
                            class="fa-solid fa-chalkboard-user" aria-hidden="true"></i> Nombre del Rol:</label>
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
                    <label class="form-label custom-section-title" for="permisos"><i
                            class="fa-solid fa-user-lock" aria-hidden="true"></i>
                        Permisos para este Rol:</label>
                    @error('permission')
                        <small class="invalid-feedback d-block">
                            <strong>{{ $message }}</strong>
                        </small>
                    @enderror
                    <div class="row">
                        @foreach ($permission as $value)
                            <div class="col-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permission[]"
                                        id="permiso_{{ $value->id }}" value="{{ $value->name }}"
                                        {{ isset($rolePermissions) ? (in_array($value->name, $rolePermissions, true) ? 'checked' : '') : '' }}>
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
                        <span class="button__text"> {{ isset($role) ? 'Modificar' : 'Guardar' }}</span>
                        @include('components.svg-save')
                    </button>
                    <a href="{{ route('panel-roles.index') }}" class="text-decoration-none">
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

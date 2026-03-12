<x-app-layout>
    @if (isset($user))
        @php $action = 'Editar usuario' @endphp
    @else
        @php $action = 'Crear usuario' @endphp
    @endif

    @section('title', $action)
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $action }}
        </h2>
    </x-slot>

    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="float-start">
                <h2>{{ $action }} de usuario</h2>
            </div>
        </div>
    </div>

    <form action="{{ isset($user) ? route('panel-usuarios.update', $user->id) : route('panel-usuarios.store') }}"
        method="POST" novalidate>
        @if (isset($user))
            @method('PATCH')
        @endif
        @csrf

        <div class="mb-3">
            <label class="form-label" for="name">Nombre</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name"
                value="{{ isset($user->name) ? $user->name : old('name') }}" @error('name') autofocus @enderror
                required>
            @error('name')
                <small class="invalid-feedback">
                    <strong>{{ $message }}</strong>
                </small>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="email">Correo</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                id="email" value="{{ isset($user->email) ? $user->email : old('email') }}"
                @error('email') autofocus @enderror required>
            @error('email')
                <small class="invalid-feedback">
                    <strong>{{ $message }}</strong>
                </small>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Contraseña</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password"
                id="password" value="{{ old('password') }}" @error('password') autofocus @enderror required>
            @error('password')
                <small class="invalid-feedback">
                    <strong>{{ $message }}</strong>
                </small>
            @enderror
        </div>
        <div class="mb-3">
            <label for="password_confirmation">Confirmación de Contraseña</label>
            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                name="password_confirmation" id="password_confirmation">
            @error('password_confirmation')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="roles">Roles</label>
            <select class="form-select @error('roles') is-invalid @enderror" name="roles" id="roles"
                @error('roles') autofocus @enderror required>
                <option value="" disabled>Selecciona un Rol</option>
                @if (isset($user))
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->name }}"
                            {{ old('roles', $user->roles->first()->name) == $rol->name ? 'selected' : '' }}>
                            {{ $rol->name }}
                        </option>
                    @endforeach
                @else
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->name }}" {{ old('roles') == $rol->name ? 'selected' : '' }}>
                            {{ $rol->name }}
                        </option>
                    @endforeach
                @endif
            </select>
            @error('roles')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="finalizado">¿Finalizado?</label>
            <select class="form-select @error('finalizado') is-invalid @enderror" name="finalizado" id="finalizado"
                @error('finalizado') autofocus @enderror required>
                <option value="" disabled
                    {{ old('finalizado', isset($user) ? $user->finalizado : '') === null ? 'selected' : '' }}>
                    Selecciona una opción</option>
                <option value="1"
                    {{ old('finalizado', isset($user) ? $user->finalizado : '') == 1 ? 'selected' : '' }}>Sí</option>
                <option value="0"
                    {{ old('finalizado', isset($user) ? $user->finalizado : '') == 0 ? 'selected' : '' }}>No</option>
            </select>

            @error('finalizado')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>



        {{-- <div class="mb-3">
            <label class="form-label" for="id_institucion">Institución</label>
            <select class="form-select @error('id_institucion') is-invalid @enderror" name="id_institucion"
                id="id_institucion" @error('id_institucion') autofocus @enderror required>
                <option value="" disabled selected>Selecciona un Rol</option>
                @foreach ($instituciones as $institucion)
                    <option value="{{ $institucion->id }}">
                        {{ $institucion->nombre }}
                    </option>
                @endforeach
            </select>
            @error('id_institucion')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div> --}}
        {{-- <div class="mb-3">
            <label class="form-label" for="id_institucion">Institución</label>
            <select class="form-select @error('id_institucion') is-invalid @enderror" name="id_institucion"
                id="id_institucion" @error('id_institucion') autofocus @enderror required>
                <option value="" disabled
                    {{ old('id_institucion', $user->id_institucion ?? '') == '' ? 'selected' : '' }}>Selecciona una
                    Institución</option>
                @foreach ($instituciones as $institucion)
                    <option value="{{ $institucion->id }}"
                        {{ old('id_institucion', $user->id_institucion ?? '') == $institucion->id ? 'selected' : '' }}>
                        {{ $institucion->nombre }}
                    </option>
                @endforeach
            </select>
            @error('id_institucion')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div> --}}
        <!-- Campo de instituciones (se ocultará o mostrará según el rol) -->
        <!-- Select de Instituciones (cuando no es Enlace) -->
        <div id="institucion-select-container">
            <label class="form-label" for="id_institucion">Institución</label>
            <select class="form-select @error('id_institucion') is-invalid @enderror" name="id_institucion"
                id="id_institucion" required>
                <option value="" disabled {{ old('id_institucion') === null ? 'selected' : '' }}>
                    Selecciona una institución
                </option>
                @foreach ($instituciones as $institucion)
                    <option value="{{ $institucion->id }}"
                        {{ old('id_institucion') == $institucion->id ? 'selected' : '' }}>
                        {{ $institucion->nombre }}
                    </option>
                @endforeach
            </select>
        </div>


        <!-- Select de Instituciones Múltiples (cuando es Enlace) -->
        <div id="instituciones-multiple-container" style="display: none;">
            <label class="form-label" for="instituciones">Instituciones</label>
            <select class="form-select @error('instituciones') is-invalid @enderror" name="instituciones[]"
                id="instituciones" multiple>
                @foreach ($instituciones as $institucion)
                    <option value="{{ $institucion->id }}"
                        {{ in_array($institucion->id, old('instituciones', $userInstituciones ?? [])) ? 'selected' : '' }}>
                        {{ $institucion->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- <div id="institucion-select" class="mb-3">
            <label class="form-label" for="id_institucion">Institución</label>
            <select class="form-select @error('id_institucion') is-invalid @enderror" name="id_institucion"
                id="id_institucion" @error('id_institucion') autofocus @enderror required>
                <option value="" disabled>Selecciona una institución</option>
                @foreach ($instituciones as $institucion)
                    <option value="{{ $institucion->id }}"
                        {{ old('id_institucion', $user->id_institucion ?? '') == $institucion->id ? 'selected' : '' }}>
                        {{ $institucion->nombre }}
                    </option>
                @endforeach
            </select>
            @error('id_institucion')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div> --}}

        <!-- Campo de instituciones múltiple (solo si el rol es "Enlace") -->
        {{-- <div id="instituciones-multiple" class="mb-3" style="display: none;">
            <label class="form-label" for="instituciones">Instituciones</label>
            <select class="form-select @error('instituciones') is-invalid @enderror" name="instituciones[]"
                id="instituciones" multiple>
                <option value="" disabled>Selecciona las instituciones</option>
                @foreach ($instituciones as $institucion)
                    <option value="{{ $institucion->id }}"
                        {{ old('instituciones', $user->instituciones ?? []) && in_array($institucion->id, old('instituciones', [])) ? 'selected' : '' }}>
                        {{ $institucion->nombre }}
                    </option>
                @endforeach
            </select>
            @error('instituciones')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div> --}}

        {{-- @endrole --}}
        <div class="mb-3 float-end">
            <a class="btn btn-outline-secondary me-2" href="{{ route('panel-usuarios.index') }}"> Atrás</a>
            <button class="btn btn-primary" type="submit">{{ isset($user) ? 'Modificar' : 'Guardar' }}</button>
        </div>
    </form>
    <script>
        // Mostrar u ocultar el select de instituciones dependiendo del rol
        function toggleInstitucionesSelect(select) {
            const institucionSelect = document.getElementById('institucion-select-container');
            const institucionesMultiple = document.getElementById('instituciones-multiple-container');

            // Si el rol seleccionado es 'Enlace', ocultamos el select de institución y mostramos el select múltiple
            if (select.value === 'Enlace') {
                institucionSelect.style.display = 'none';
                institucionesMultiple.style.display = 'block';
            } else {
                institucionSelect.style.display = 'block';
                institucionesMultiple.style.display = 'none';
            }
        }

        // Llamar la función para que el comportamiento inicial esté correcto según el rol actual
        document.addEventListener('DOMContentLoaded', function() {
            const selectRol = document.getElementById('roles');

            // Llamar a la función para establecer el estado inicial según el valor del rol
            toggleInstitucionesSelect(selectRol);

            // Añadir un listener para que se ejecute la función cuando el rol cambie
            selectRol.addEventListener('change', function() {
                toggleInstitucionesSelect(selectRol);
            });
        });
    </script>

</x-app-layout>
<script></script>

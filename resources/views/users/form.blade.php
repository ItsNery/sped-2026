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
    <div class="container py-12 mx-auto">
        <div class="contenedor-principal mx-auto">
            <div class="encabezado-lista my-2">
                <h2>{{ $action }}</h2>
            </div>
            <div class="row">
                <div class="col-lg-12 margin-tb">
                    <div class="float-start my-2 mx-2">
                        <h2></h2>
                    </div>
                </div>
            </div>

            <form class="mx-2 my-2"
                action="{{ isset($user) ? route('panel-usuarios.update', $user->id) : route('panel-usuarios.store') }}"
                method="POST" novalidate>
                @if (isset($user))
                    @method('PATCH')
                @endif
                @csrf
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label class="form-label custom-section-title" for="name"><i
                                    class="fa-solid fa-file-signature"></i> Nombre</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" id="name"
                                value="{{ isset($user->name) ? $user->name : old('name') }}"
                                @error('name') autofocus @enderror required>
                            @error('name')
                                <small class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label class="custom-section-title form-label" for="email"><i class="fa-solid fa-at"></i>
                                Correo</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                name="email" id="email"
                                value="{{ isset($user->email) ? $user->email : old('email') }}"
                                @error('email') autofocus @enderror required>
                            @error('email')
                                <small class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label class="custom-section-title form-label" for="password"><i
                                    class="fa-solid fa-key"></i> Contraseña</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                name="password" id="password" value="{{ old('password') }}"
                                @error('password') autofocus @enderror required>
                            @error('password')
                                <small class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label class="custom-section-title" for="password_confirmation"><i
                                    class="fa-solid fa-key"></i> Confirmación de
                                Contraseña</label>
                            <input type="password"
                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                name="password_confirmation" id="password_confirmation">
                            @error('password_confirmation')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="mb-3">
                            <label class="custom-section-title form-label" for="roles"><i
                                    class="fa-solid fa-user-lock"></i> Roles</label>
                            <select class="form-select @error('roles') is-invalid @enderror" name="roles"
                                id="roles" @error('roles') autofocus @enderror required>
                                <option value="" disabled>Selecciona un Rol</option>
                                @if (isset($user))
                                    @foreach ($roles as $rol)
                                        <option value="{{ $rol->name }}"
                                            {{ old('roles', optional($user->roles->first())->name) == $rol->name ? 'selected' : '' }}>
                                            {{ $rol->name }}
                                        </option>
                                    @endforeach
                                @else
                                    @foreach ($roles as $rol)
                                        <option value="{{ $rol->name }}"
                                            {{ old('roles') == $rol->name ? 'selected' : '' }}>
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
                    </div>
                    <div class="col-4">
                        <div class="mb-3">
                            <label class="form-label custom-section-title" for="finalizado"><i
                                    class="fa-solid fa-check"></i> ¿Finalizado?</label>
                            <select class="form-select @error('finalizado') is-invalid @enderror" name="finalizado"
                                id="finalizado" @error('finalizado') autofocus @enderror required>
                                <option value="" disabled
                                    {{ old('finalizado', isset($user) ? $user->finalizado : '') === null ? 'selected' : '' }}>
                                    Selecciona una opción</option>
                                <option value="1"
                                    {{ old('finalizado', isset($user) ? $user->finalizado : '') == 1 ? 'selected' : '' }}>
                                    Sí
                                </option>
                                <option value="0"
                                    {{ old('finalizado', isset($user) ? $user->finalizado : '') == 0 ? 'selected' : '' }}>
                                    No
                                </option>
                            </select>

                            @error('finalizado')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="custom-section-title form-label d-block" for="tipo_usuario"><i
                                    class="fa-solid fa-users"></i> Tipo de
                                Usuario</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="tipo_usuario" name="tipo_usuario"
                                    value="municipio"
                                    {{ old('tipo_usuario', isset($user->id_municipio) ? 'municipio' : 'institucion') == 'municipio' ? 'checked' : '' }}>
                                <label class="form-check-label" for="tipo_usuario">
                                    <span id="label-tipo-usuario">
                                        {{ old('tipo_usuario', isset($user->id_municipio) ? 'Municipio' : 'Institución') }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div id="municipio-select-container" style="display: none;" class="pb-3">
                            <label class="custom-section-title form-label" for="id_municipio"><i
                                    class="fa-solid fa-building-wheat"></i> Municipio</label>
                            <select class="form-select @error('id_municipio') is-invalid @enderror"
                                name="id_municipio" id="id_municipio">
                                <option value="" disabled
                                    {{ old('id_municipio', $user->id_municipio ?? '') === '' ? 'selected' : '' }}>
                                    Selecciona un municipio
                                </option>
                                @foreach ($municipios as $municipio)
                                    <option value="{{ $municipio->id }}"
                                        {{ old('id_municipio', $user->id_municipio ?? '') == $municipio->id ? 'selected' : '' }}>
                                        {{ $municipio->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_municipio')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div id="institucion-select-container" class="pb-3">
                            <label class="custom-section-title form-label" for="id_institucion"><i
                                    class="fa-solid fa-building-ngo"></i> Institución</label>
                            <select class="form-select @error('id_institucion') is-invalid @enderror"
                                name="id_institucion" id="id_institucion" required>
                                <option value="" disabled
                                    {{ old('id_institucion', $user->id_institucion ?? '') === '' ? 'selected' : '' }}>
                                    Selecciona una institución
                                </option>
                                @foreach ($instituciones as $institucion)
                                    <option value="{{ $institucion->id }}"
                                        {{ old('id_institucion', $user->id_institucion ?? '') == $institucion->id ? 'selected' : '' }}>
                                        {{ $institucion->nombre }}
                                    </option>
                                @endforeach
                                {{-- SOLO AÑADIR ESTA OPCIÓN SI NO SE ESTÁ EDITANDO UN USUARIO --}}
                                @if (!isset($user))
                                    <option value="nueva_institucion">** La institución no está en la lista (Añadir
                                        Nueva) **</option>
                                @endif
                            </select>
                            @error('id_institucion')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        {{-- Campos para Nueva Institución (inicialmente ocultos) --}}
                        <div id="nueva-institucion-fields" style="display: none;" class="pb-3 border p-3 mb-3">
                            <h5 class="mb-3">Datos de la Nueva Institución</h5>
                            <div class="mb-3">
                                <label for="nueva_institucion_nombre" class="form-label">Nombre de la Nueva
                                    Institución*</label>
                                <input type="text"
                                    class="form-control @error('nueva_institucion_nombre') is-invalid @enderror"
                                    id="nueva_institucion_nombre" name="nueva_institucion_nombre"
                                    value="{{ old('nueva_institucion_nombre') }}">
                                @error('nueva_institucion_nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="nueva_institucion_titular" class="form-label">Titular de la Nueva
                                    Institución*</label>
                                <input type="text"
                                    class="form-control @error('nueva_institucion_titular') is-invalid @enderror"
                                    id="nueva_institucion_titular" name="nueva_institucion_titular"
                                    value="{{ old('nueva_institucion_titular') }}">
                                @error('nueva_institucion_titular')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Select de Instituciones Múltiples (cuando es Enlace) -->
                        <div id="instituciones-multiple-container" style="display: none;" class="pb-3">
                            <label class="custom-section-title form-label" for="instituciones"><i
                                    class="fa-solid fa-building-columns"></i> Instituciones</label>
                            <select class="form-select @error('instituciones') is-invalid @enderror"
                                name="instituciones[]" id="instituciones" multiple>
                                @foreach ($instituciones as $institucion)
                                    <option value="{{ $institucion->id }}"
                                        {{ in_array($institucion->id, old('instituciones', $userInstituciones ?? [])) ? 'selected' : '' }}>
                                        {{ $institucion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('instituciones')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="my-3 py-3 d-flex justify-content-end">
                    <button class="button-save" type="submit">
                        <span class="button__text"> {{ isset($user) ? 'Modificar' : 'Guardar' }}</span>
                        @include('components.svg-save')
                    </button>
                    <a href="{{ route('panel-usuarios.index') }}" class="text-decoration-none">
                        <button class="button-cancel" type="button">
                            <span class="button__text">Cancelar</span>
                            @include('components.svg-cancel')
                        </button>
                    </a>
                </div>
            </form>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const tipoUsuarioSwitch = document.getElementById('tipo_usuario');
                    const labelTipoUsuario = document.getElementById('label-tipo-usuario');
                    const municipioSelectContainer = document.getElementById('municipio-select-container');
                    const institucionSelectContainer = document.getElementById('institucion-select-container');
                    const institucionMultipleContainer = document.getElementById('instituciones-multiple-container');
                    const municipioSelect = document.getElementById('id_municipio');
                    const institucionSelect = document.getElementById('id_institucion');
                    const institucionesMultiple = document.getElementById('instituciones');
                    const rolesSelect = document.getElementById('roles');
                    const nuevaInstitucionFields = document.getElementById('nueva-institucion-fields');
                    const nuevaInstitucionNombreInput = document.getElementById('nueva_institucion_nombre');
                    const nuevaInstitucionTitularInput = document.getElementById('nueva_institucion_titular');

                    function toggleNuevaInstitucionFields() {
                        if (institucionSelect.value === 'nueva_institucion') {
                            nuevaInstitucionFields.style.display = 'block';
                            // Hacer los nuevos campos requeridos
                            nuevaInstitucionNombreInput.required = true;
                            nuevaInstitucionTitularInput.required = true;
                            // El select original de institución ya no es 'requerido' en este caso,
                            // ya que se va a crear una nueva. La validación del backend se encargará.
                            institucionSelect.required = false;
                        } else {
                            nuevaInstitucionFields.style.display = 'none';
                            // Limpiar y quitar 'required' de los nuevos campos
                            nuevaInstitucionNombreInput.value = '';
                            nuevaInstitucionTitularInput.value = '';
                            nuevaInstitucionNombreInput.required = false;
                            nuevaInstitucionTitularInput.required = false;
                            // Determinar si institucionSelect debe ser 'required'
                            // (solo si no es Enlace y no es Municipio)
                            const tipoUsuario = tipoUsuarioSwitch.checked ? 'municipio' : 'institucion';
                            const rolSeleccionado = rolesSelect.value;
                            if (tipoUsuario === 'institucion' && rolSeleccionado !== 'Enlace') {
                                institucionSelect.required = true;
                            } else {
                                institucionSelect.required = false;
                            }
                        }
                    }

                    // Escuchar cambios en el select de institución (solo si existe)
                    if (institucionSelect) {
                        institucionSelect.addEventListener('change', toggleNuevaInstitucionFields);
                    }

                    function toggleTipoUsuario() {
                        const tipoUsuario = tipoUsuarioSwitch.checked ? 'municipio' : 'institucion';
                        const rolSeleccionado = rolesSelect.value;
                        // Resetear campos de nueva institución al cambiar de tipo
                        nuevaInstitucionFields.style.display = 'none';
                        nuevaInstitucionNombreInput.required = false;
                        nuevaInstitucionTitularInput.required = false;

                        if (tipoUsuario === 'municipio') {
                            // Mostrar municipio y ocultar todo lo relacionado con instituciones
                            labelTipoUsuario.textContent = 'Municipio';
                            municipioSelectContainer.style.display = 'block';
                            institucionSelectContainer.style.display = 'none';
                            institucionMultipleContainer.style.display = 'none';

                            municipioSelect.disabled = false;
                            municipioSelect.required = true;
                            institucionSelect.disabled = true;
                            institucionSelect.required = false;
                            institucionesMultiple.disabled = true;
                            institucionesMultiple.required = false;
                        } else {
                            // Mostrar instituciones (único o múltiple según el rol)
                            labelTipoUsuario.textContent = 'Institución';
                            municipioSelectContainer.style.display = 'none';
                            municipioSelect.disabled = true;
                            municipioSelect.required = false;

                            if (rolSeleccionado === 'Enlace') {
                                // Rol "Enlace" => mostrar select múltiple
                                institucionSelectContainer.style.display = 'none';
                                institucionMultipleContainer.style.display = 'block';
                                institucionSelect.disabled = true;
                                institucionSelect.required = false;
                                institucionesMultiple.disabled = false;
                                institucionesMultiple.required = true;
                            } else {
                                // Rol diferente => mostrar select único
                                institucionSelectContainer.style.display = 'block';
                                institucionMultipleContainer.style.display = 'none';
                                institucionSelect.disabled = false;
                                // institucionSelect.required se maneja por toggleNuevaInstitucionFields
                                institucionesMultiple.disabled = true;
                                institucionesMultiple.required = false;
                            }
                        }
                        toggleNuevaInstitucionFields();
                    }

                    // Configurar estado inicial
                    toggleTipoUsuario();

                    if (tipoUsuarioSwitch) {
                        tipoUsuarioSwitch.addEventListener('change', toggleTipoUsuario);
                    }

                    // Escuchar cambios en el select de roles
                    if (rolesSelect) {
                        rolesSelect.addEventListener('change', toggleTipoUsuario);
                    }

                    // (Mover el listener del select de institución aquí si no estaba ya)
                    if (institucionSelect) {
                        institucionSelect.addEventListener('change', toggleNuevaInstitucionFields);
                    }
                });
            </script>
        </div>
    </div>
</x-app-layout>

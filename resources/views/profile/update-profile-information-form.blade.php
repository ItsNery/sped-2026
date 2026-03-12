<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('Información del perfil') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Actualiza la información de tu perfil y dirección de correo.') }}
    </x-slot>

    <x-slot name="form">

        <x-action-message on="saved">
            {{ __('Guardado.') }}
        </x-action-message>

        <div class="w-md-75">
            <!-- Name -->
            <div class="mb-3">
                <x-label for="name" value="{{ __('Nombre') }}" />
                <x-input id="name" type="text" class="{{ $errors->has('name') ? 'is-invalid' : '' }}" wire:model.defer="state.name" autocomplete="name" />
                <x-input-error for="name" />
            </div>

            <!-- Email -->
            <div class="mb-3">
                <x-label for="email" value="{{ __('Correo electrónico') }}" />
                <x-input id="email" type="email" class="{{ $errors->has('email') ? 'is-invalid' : '' }}" wire:model.defer="state.email" />
                <x-input-error for="email" />
            </div>
        </div>
    </x-slot>

    <x-slot name="actions">
		<div class="d-flex align-items-baseline">
			<x-button>
                <div wire:loading class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>

				{{ __('Guardar') }}
			</x-button>
		</div>
    </x-slot>
</x-form-section>
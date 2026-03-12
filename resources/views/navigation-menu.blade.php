<nav class="navbar navbar-expand-md navbar-light bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="/">
            <img src="{{ asset('img/logo_sped.png') }}" alt="Logo SPED" style="width: 150px;"> {{-- Ajusté el width a px o rem para evitar errores, w-40 no es estándar bootstrap --}}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            {{-- CAMBIO: Usamos align-items-center para que todo esté centrado verticalmente --}}
            <ul class="navbar-nav me-auto align-items-center">
                <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                    {{ __('Inicio') }}
                </x-nav-link>

                @auth
                @if(auth()->user()->can('ver-indicador') || auth()->user()->can('ver-municipios-convenio'))
                <x-dropdown id="gestionDropdown" class="ms-3">
                    <x-slot name="trigger">
                        <span class="d-inline-flex align-items-center text-muted fw-bold {{ (request()->is('panel-indicadores*') || request()->is('pruebas-indicadores*') || request()->routeIs('panel-municipios-convenio*', 'indicadores-municipales*')) ? 'text-primary border-bottom border-primary border-2' : '' }}"
                            style="cursor: pointer; font-size: 0.9rem;">
                            {{ __('Gestión') }}
                    </x-slot>

                    <x-slot name="content">
                        @can('ver-indicador')
                        <x-dropdown-link href="{{ route('panel-indicadores.index') }}">
                            {{ __('Indicadores') }}
                        </x-dropdown-link>
                        @endcan
                        @can('ver-municipios-convenio')
                        <x-dropdown-link href="{{ route('panel-municipios-convenio.index') }}">
                            {{ __('Municipios') }}
                        </x-dropdown-link>
                        @endcan
                    </x-slot>
                </x-dropdown>
                @endif
                @endauth

                @auth
                @if(auth()->user()->can('ver-ind-carrusel') || auth()->user()->can('ver-slider-inicio'))
                <x-dropdown id="difusionDropdown" class="ms-3">
                    <x-slot name="trigger">
                        <span class="d-inline-flex align-items-center text-muted fw-bold {{ (request()->routeIs('panel-carrusel-indicadores*', 'panel-slider-inicio*')) ? 'text-primary border-bottom border-primary border-2' : '' }}"
                            style="cursor: pointer; font-size: 0.9rem;">
                            {{ __('Difusión') }}
                        </span>
                    </x-slot>

                    <x-slot name="content">
                        @can('ver-ind-carrusel')
                        <x-dropdown-link href="{{ route('panel-carrusel-indicadores.index') }}">
                            {{ __('Carrusel') }}
                        </x-dropdown-link>
                        @endcan
                        @can('ver-slider-inicio')
                        <x-dropdown-link href="{{ route('panel-slider-inicio.index') }}">
                            {{ __('Slider') }}
                        </x-dropdown-link>
                        @endcan
                    </x-slot>
                </x-dropdown>
                @endif
                @endauth

                @auth
                @if (auth()->user()->id === 1)
                <x-dropdown id="adminDropdown" class="ms-3">
                    <x-slot name="trigger">
                        <span class="d-inline-flex align-items-center text-muted fw-bold {{ (request()->routeIs('panel-usuarios*', 'usuarios*', 'panel-roles*', 'panel-logs*', 'panel-accesos*')) ? 'text-primary border-bottom border-primary border-2' : '' }}"
                            style="cursor: pointer; font-size: 0.9rem;">
                            {{ __('Administración') }}
                        </span>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link href="{{ route('panel-usuarios.index') }}">
                            {{ __('Usuarios') }}
                        </x-dropdown-link>
                        <x-dropdown-link href="{{ route('panel-roles.index') }}">
                            {{ __('Roles') }}
                        </x-dropdown-link>
                        <x-dropdown-link href="{{ route('panel-logs.index') }}">
                            {{ __('Bitácora') }}
                        </x-dropdown-link>
                        <x-dropdown-link href="{{ route('panel-accesos.index') }}">
                            {{ __('Accesos') }}
                        </x-dropdown-link>
                    </x-slot>
                </x-dropdown>
                @endif
                @endauth

                <x-dropdown id="manualesDropdown" class="ms-3">
                    <x-slot name="trigger">
                        <span class="d-inline-flex align-items-center text-muted fw-bold"
                            style="cursor: pointer; font-size: 0.9rem;">
                            {{ __('Manuales') }}
                        </span>
                    </x-slot>

                    <x-slot name="content">
                        @auth
                        @if (auth()->user()->hasRole('Enlace dependencia'))
                        <x-dropdown-link href="{{ asset('docs/normatividad/Manual-Instituciones.pdf') }}"
                            target="_blank">
                            {{ __('Manual de usuario') }}
                        </x-dropdown-link>
                        <x-dropdown-link
                            href="{{ asset('docs/normatividad/Guia_para_la_modificacion_de_Indicadores.zip') }}"
                            target="_blank">
                            {{ __('Manual Mod. Ind. y Metas') }}
                        </x-dropdown-link>
                        @else
                        <x-dropdown-link
                            href="{{ asset('docs/normatividad/Guia_para_la_modificacion_de_Indicadores.zip') }}"
                            target="_blank">
                            {{ __('Manual Mod. Ind. y Metas') }}
                        </x-dropdown-link>
                        @endif
                        @endauth
                    </x-slot>
                </x-dropdown>

                @auth
                @if (auth()->user()->hasRole('Administrador'))
                <x-dropdown id="catalogosDropdown" class="ms-3">
                    <x-slot name="trigger">
                        <span class="d-inline-flex align-items-center text-muted fw-bold {{ (request()->routeIs('panel-cat-instituciones*', 'panel-cat-planes*', 'panel-cat-prog-der-*')) ? 'text-primary border-bottom border-primary border-2' : '' }}"
                            style="cursor: pointer; font-size: 0.9rem;">
                            {{ __('Catálogos') }}
                        </span>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link
                            href="{{ route('panel-cat-instituciones.index') }}">{{ __('Instituciones') }}</x-dropdown-link>
                        <x-dropdown-link
                            href="{{ route('panel-cat-planes.index') }}">{{ __('PEDs') }}</x-dropdown-link>
                        <x-dropdown-link
                            href="{{ route('panel-cat-prog-der-sect.index') }}">{{ __('PDs. Sectoriales') }}</x-dropdown-link>
                        <x-dropdown-link
                            href="{{ route('panel-cat-prog-der-esp.index') }}">{{ __('PDs. Especiales') }}</x-dropdown-link>
                        <x-dropdown-link
                            href="{{ route('panel-cat-prog-der-reg.index') }}">{{ __('PDs. Regionales') }}</x-dropdown-link>
                        <x-dropdown-link
                            href="{{ route('panel-cat-prog-der-instit.index') }}">{{ __('PDs. Institucionales') }}</x-dropdown-link>
                    </x-slot>
                </x-dropdown>
                @endif
                @endauth
            </ul>

            <ul class="navbar-nav align-items-center ms-auto"> {{-- ms-auto empuja a la derecha --}}
                @auth
                <li class="nav-item dropdown">
                    <x-dropdown id="settingsDropdown">
                        <x-slot name="trigger">
                            {{-- APLICANDO LA CORRECCIÓN AL PERFIL --}}
                            <span class="d-inline-flex align-items-center" style="cursor: pointer;">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <img class="rounded-circle border border-2 border-light shadow-sm" width="35"
                                    height="35" src="{{ Auth::user()->profile_photo_url }}"
                                    alt="{{ Auth::user()->name }}" />
                                @else
                                <span class="text-muted fw-bold">{{ Auth::user()->name }}</span>
                                @endif
                            </span>
                        </x-slot>

                        <x-slot name="content">
                            <h6 class="dropdown-header small text-muted">{{ __('Gestionar cuenta') }}</h6>
                            <x-dropdown-link
                                href="{{ route('profile.show') }}">{{ __('Perfil') }}</x-dropdown-link>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <x-dropdown-link
                                href="{{ route('api-tokens.index') }}">{{ __('API Tokens') }}</x-dropdown-link>
                            @endif

                            <hr class="dropdown-divider">
                            <x-dropdown-link href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                {{ __('Cerrar sesión') }}
                            </x-dropdown-link>
                            <form method="POST" id="logout-form" action="{{ route('logout') }}">@csrf</form>
                        </x-slot>
                    </x-dropdown>
                </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
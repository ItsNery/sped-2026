<nav class="navbar navbar-expand-md navbar-light bg-white border-bottom sticky-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand me-4" href="/">
            {{-- <x-application-mark width="36" /> --}}
            <img src="{{ asset('img/logo_sped.png') }}" alt="" class="w-40">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav me-auto">
                <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                    {{ __('Inicio') }}
                </x-nav-link>
                <x-nav-link href="{{ route('panel-indicadores-municipales.index') }}" :active="request()->routeIs('panel-indicadores-municipales*')">
                    {{ __('Indicadores') }}
                </x-nav-link>
                <ul class="navbar-nav align-items-baseline">
                    <!-- Dropdown para 'Manuales' -->
                    <x-dropdown id="manualesDropdown">
                        <x-slot name="trigger">
                            {{ __('Manuales') }}

                            <svg class="ms-2" width="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </x-slot>

                        <x-slot name="content">
                            <!-- Opciones de Manuales -->
                            <x-dropdown-link href="{{ asset('docs/normatividad/Manual-Municipios.pdf') }}"
                                target="_blank">
                                {{ __('Manual de Usuario') }}
                            </x-dropdown-link> 
                        </x-slot>
                    </x-dropdown>
                </ul>
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav align-items-baseline">
                <!-- Settings Dropdown -->
                @auth
                    <x-dropdown id="settingsDropdown">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <img class="rounded-circle" width="32" height="32"
                                    src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                            @else
                                {{ Auth::user()->name }}

                                <svg class="ms-2" width="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            @endif
                        </x-slot>

                        <x-slot name="content">
                            <!-- Account Management -->
                            {{-- <h6 class="dropdown-header small text-muted">
                                {{ __('Gestionar cuenta') }}
                            </h6> --}}

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                    {{ __('API Tokens') }}
                                </x-dropdown-link>
                            @endif

                            {{-- <hr class="dropdown-divider"> --}}

                            <!-- Authentication -->
                            <x-dropdown-link href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                                                         document.getElementById('logout-form').submit();">
                                {{ __('Cerrar sesión') }}
                            </x-dropdown-link>
                            <form method="POST" id="logout-form" action="{{ route('logout') }}">
                                @csrf
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endauth
            </ul>
        </div>
    </div>
</nav>

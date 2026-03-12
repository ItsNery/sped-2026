<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
        del Estado de Puebla</title>
    <meta content='width=device-width, initial-scale=1' name='viewport'>
    <meta name="description" content="@yield('meta-description', 'El Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla (SPED) es una herramienta que permite monitorear el avance de los indicadores establecidos en los documentos programáticos vigentes del estado.')">
    <meta name="keywords" content="@yield('keywords', 'sistema de información, seguimiento, planeación, evaluación, desarrollo, estado de puebla, gobierno del estado de puebla, transparencia, rendición de cuentas, participación ciudadana')">
    <meta name="author" content="Ing. Nery Pozos">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="google" content="notranslate">
    <meta name="googlebot" content="index, follow">
    <meta name="google-site-verification" content="Código de verificación de Google Search Console">
    <meta name="bingbot" content="index, follow">
    <meta name="referrer" content="no-referrer-when-downgrade">
    <meta name="format-detection" content="telephone=no">
    <meta name="HandheldFriendly" content="True">
    {{-- <meta name="apple-mobile-web-app-capable" content="yes"> --}}
    {{-- <meta name="mobile-web-app-capable" content="yes" --}}
    <meta name="theme-color" content="#a0153e">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta property="og:title" content="@yield('og-title', 'Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')">
    <meta property="og:description" content="@yield('og-description', 'El Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla (SPED) es una herramienta que permite monitorear el avance de los indicadores establecidos en los documentos programáticos vigentes del estado.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="@yield('og:url', 'https://sped.puebla.gob.mx')">
    <meta property="og:image" content="@yield('og:image', '')">
    <meta property="og:site_name"
        content="Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter-title', 'Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')">
    <meta name="twitter:description" content="@yield('twitter-description', 'El Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla (SPED) es una herramienta que permite monitorear el avance de los indicadores establecidos en los documentos programáticos vigentes del estado.')">
    <meta name="twitter:image" content="@yield('twitter:image', '')">
    <link rel="canonical" href="@yield('canonical-url', 'https://sped.puebla.gob.mx')">

    <!-- Favicon -->
    <link href="{{ asset('img/favicon.svg') }}" rel="icon" />

    <!-- Scripts -->
    <script src="{{ asset('js/scripts.js') }}" defer></script>
    <script src="{{ asset('js/app.js') }}" defer></script>

    {{-- Font Awesome --}}
    <link href="{{ asset('fontAwesome/css/fontawesome.css') }}" rel="stylesheet">
    <link href="{{ asset('fontAwesome/css/brands.css') }}" rel="stylesheet">
    <link href="{{ asset('fontAwesome/css/solid.css') }}" rel="stylesheet">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">

    <!-- Styles -->
    <link href="{{ asset('css/media_queries.css') }}" rel="stylesheet">
    <link href="{{ asset('css/efectos.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/estilos.css') }}" rel="stylesheet">

    @yield('jss-inicial')
    @yield('css')
</head>

<body>
    @auth
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}" rel="noopener" target="_self" title="Inicio">
                    <img src="{{ asset('img/logo_sped.png') }}" alt="" class="w-10p">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent1" aria-controls="navbarSupportedContent1" aria-expanded="false"
                    aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent1">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                    </ul>
                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle m-w-150" href="#"
                                    role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('dashboard') }}" target="_self" title="Panel"
                                        rel="noopener">
                                        {{ __('Panel') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                        document.getElementById('logout-form').submit();"
                                        title="Cerrar Sesión" rel="noopener">
                                        {{ __('Cerrar Sesión') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
    @endauth
    <main>
        @include('layouts.header')
        <div id="customSearchModal" class="custom-modal-search">
            <div class="custom-modal-content">
                <div class="custom-modal-header">
                    <span class="custom-close" onclick="closeSearchModal()">&times;</span>
                    <h5 class="custom-modal-title">Buscar con Google</h5>
                </div>
                <div class="custom-modal-body">
                    <script async src="https://cse.google.com/cse.js?cx=031f16cfb8b5845ab"></script>
                    <div class="gcse-searchbox-only"></div>
                </div>
            </div>
        </div>
        @yield('content')
        @include('layouts.footer')
    </main>
    @yield('jss-final')
    <script>
        window.addEventListener("load", function() {
            let modal = document.getElementById("customSearchModal");

            window.openSearchModal = function() {
                modal.classList.add("show");
            };

            window.closeSearchModal = function() {
                modal.classList.remove("show");
            };

            window.onclick = function(event) {
                if (event.target === modal) {
                    closeSearchModal();
                }
            };
        });
    </script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-PZQY1MBD1G"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-PZQY1MBD1G');
    </script>

</body>

</html>

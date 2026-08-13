<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- <title>{{ config('app.name', 'Laravel') }}</title> --}}
    <title>
        @yield('title') | Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    </title>

    <link href="{{ asset('img/favicon.svg') }}" rel="icon" />
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('fontAwesome/css/fontawesome.css') }}" rel="stylesheet">
    <link href="{{ asset('fontAwesome/css/brands.css') }}" rel="stylesheet">
    <link href="{{ asset('fontAwesome/css/solid.css') }}" rel="stylesheet">
    <link href="{{ asset('css/datatables.min.css') }}" rel="stylesheet">
    {{-- <link
        href="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.2.2/af-2.7.0/b-3.2.2/b-colvis-3.2.2/b-html5-3.2.2/b-print-3.2.2/fh-4.0.1/kt-2.12.1/r-3.0.4/sp-2.3.3/sr-1.4.1/datatables.min.css"
        rel="stylesheet" integrity="sha384-gpuleN0pr2254JOUdWW+d17m35r+Iw3jqSQKVMv8BdPxGYTBghlZnU/9V0hfmSrY"
        crossorigin="anonymous"> --}}

    <link rel="stylesheet" href="{{ asset('css/estilos-admin.css') }}">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    @livewireStyles

    <!-- Scripts -->
    @yield('jss-inicial')
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ mix('js/app.js') }}" defer></script>
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>
</head>

<body class="font-sans antialiased bg-light">
    <x-banner />
    <div class="admin-wrapper">
        @include('layouts.admin-navigation')

        <div class="admin-content">
            <nav class="admin-topbar" aria-label="Barra superior">
                <button class="admin-sidebar-toggle" type="button" data-sidebar-toggle aria-label="Abrir o contraer menú">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </button>
                <span class="admin-topbar-title">@yield('title', 'Panel de administración')</span>
                <span class="admin-topbar-user">{{ Auth::user()->name ?? '' }}</span>
            </nav>

            <header class="admin-page-heading">
                <div class="container-fluid">{{ $header }}</div>
            </header>

            <main class="container-fluid my-2 content">
                {{ $slot }}
            </main>
            @include('layouts.footer-admin')
        </div>
    </div>

    @stack('modals')

    @livewireScripts

    @stack('scripts')

    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"
        integrity="sha384-VFQrHzqBh5qiJIU0uGU5CIW3+OWpdGGJM9LBnGbuIH2mkICcFZ7lPd/AAtI7SNf7" crossorigin="anonymous">
    </script> --}}
    <script src="{{ asset('js/pdfmake.min.js') }}"></script>
    <script src="{{ asset('js/vfs_fonts.js') }}"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"
        integrity="sha384-/RlQG9uf0M2vcTw3CX7fbqgbj/h8wKxw7C3zu9/GxcBPRKOEcESxaxufwRXqzq6n" crossorigin="anonymous">
    </script> --}}
    <script
        src="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.2.2/af-2.7.0/b-3.2.2/b-colvis-3.2.2/b-html5-3.2.2/b-print-3.2.2/fh-4.0.1/kt-2.12.1/r-3.0.4/sp-2.3.3/sr-1.4.1/datatables.min.js"
        integrity="sha384-2KVVYSM6hFzM8i2jOn9yON6kgel4/a/gwaHwNzjT1Z4RmkPWRmqqQk7VU1s+wcqS" crossorigin="anonymous">
    </script>

    <script src="{{ asset('js/scripts-admin.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.querySelector('.admin-wrapper');
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const storageKey = 'spedAdminSidebarCollapsed';
            const isDesktop = () => window.innerWidth >= 992;

            if (!wrapper || !sidebar || !overlay) return;

            const applyState = () => {
                if (!isDesktop()) {
                    wrapper.classList.remove('sidebar-collapsed');
                    return;
                }

                wrapper.classList.toggle('sidebar-collapsed', localStorage.getItem(storageKey) === 'true');
            };

            applyState();

            document.querySelectorAll('[data-sidebar-toggle]').forEach((toggle) => {
                toggle.addEventListener('click', () => {
                    if (isDesktop()) {
                        wrapper.classList.toggle('sidebar-collapsed');
                        localStorage.setItem(storageKey, wrapper.classList.contains('sidebar-collapsed'));
                    } else {
                        sidebar.classList.toggle('show');
                        overlay.classList.toggle('show');
                    }
                });
            });

            window.addEventListener('resize', applyState);
        });
    </script>
</body>

</html>

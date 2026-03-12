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
    @livewire('navigation-menu')

    <!-- Page Heading -->
    <header class="d-flex py-3 bg-white shadow-sm border-bottom">
        <div class="container">
            {{ $header }}
        </div>
    </header>

    <!-- Page Content -->
    <main class="container my-2 content">
        {{ $slot }}
    </main>
    @include('layouts.footer-admin')

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
</body>

</html>

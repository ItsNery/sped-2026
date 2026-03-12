<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- <title>{{ config('app.name', 'Laravel') }}</title> --}}
    <title>@yield('title') | Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    </title>

    <link href="{{ asset('img/logo.ico') }}" rel="icon" />
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('fontAwesome/css/fontawesome.css') }}" rel="stylesheet">
    <link href="{{ asset('fontAwesome/css/brands.css') }}" rel="stylesheet">
    <link href="{{ asset('fontAwesome/css/solid.css') }}" rel="stylesheet">
    <link href="{{ asset('css/datatables.min.css') }}" rel="stylesheet">
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
    @livewire('indicadores-municipales-navigation-menu')

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
    <script src="{{ asset('js/datatables.min.js') }}"></script>
    <!-- Botones de exportación JS -->
    <script src="{{ asset('js/dataTables.buttons.min.js') }}"></script>

    <!-- JSZip para exportar a Excel -->
    <script src="{{ asset('js/jszip.min.js') }}"></script>

    <!-- PDFMake para exportar a PDF -->
    <script src="{{ asset('js/pdfmake.min.js') }}"></script>
    <script src="{{ asset('js/vfs_fonts.js') }}"></script>

    <!-- Botones para exportar a Excel, CSV y PDF -->
    <script src="{{ asset('js/buttons.html5.min.js') }}"></script>
</body>

</html>

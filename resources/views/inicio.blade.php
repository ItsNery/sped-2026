@extends('layouts.plantilla')
@section('title', 'Inicio')
@section('meta-description',
'Página principal del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
'Inicio - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description',
'Bienvenido a la página de inicio del Sistema de Información para el Seguimiento a la Planeación y Evaluación del
Desarrollo
del Estado de Puebla.')
@section('og:url', url()->current())
@section('twitter-title',
'Inicio - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description',
'Bienvenido a la página de inicio del Sistema de Información para el Seguimiento a la Planeación y Evaluación del
Desarrollo
del Estado de Puebla.')
@section('css')
<link rel="stylesheet" href="{{ asset('css/swiper-bundle.min.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css" />
@endsection
@section('jss-inicial')
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
<script src="{{ asset('js/swiper-bundle.min.js') }}"></script>
@endsection

@section('content')
@if ($carruselItems && count($carruselItems) > 0)
<section class="seccion-slider">
    <div class="slider slider_indicadores">
        <div class="slide-track">
            @foreach ($carruselItems as $item)
            <a class="text-decoration-none text-black"
                href="{{ route('ficha-tecnica.show', $item->indicador) }}"
                title="{{ $item->indicador->nombre }}" rel="noopener noreferrer">
                <div class="slide">
                    <div class="row">
                        <div class="col-md-2 imagen">
                            <img src="{{ asset('img/iconos_indicadores/' . $item->imagen) }}"
                                alt="Icono del indicador {{ $item->indicador->nombre }}"
                                title="{{ $item->indicador->nombre }}" loading="lazy">
                        </div>
                        <div class="col-md-9 col-sm-12 informacion">
                            <div class="row">
                                <h3>
                                    {{ $item->indicador->nombre }}
                                </h3>
                            </div>
                            <div class="row">
                                <h2>
                                    @isset($item->ultimo_dato)
                                    {{ number_format($item->ultimo_dato, 2, '.', ',') }}
                                    @else
                                    Sin datos
                                    @endisset
                                </h2>
                            </div>
                            <div class="col-12">
                                <h4>
                                    {{ $item->anio_mas_reciente ?? 'N/A' }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
<img src="{{asset('img/pleca-nueva.png')}}" alt="" class="w-100 py-2" title="Pleca de separación de secciones" loading="lazy">
@endif
<section class="seccion-inicial">
    <div class="custom-container">
        <div class="left-panel">
            <img src="{{ asset('img/que-es-nuevo.png') }}"
                alt="Panel izquierdo con diseño gráfico que indica 'Qué es el Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo'" loading="lazy">
        </div>
        <div class="right-panel">
            <p>Es una herramienta integradora para el seguimiento puntual al avance de los Indicadores Estratégicos, así
                como los Indicadores Sectoriales, Especiales, Institucionales y Regionales, establecidos en los
                documentos programáticos vigentes.</p>
        </div>
    </div>

</section>

<!-- Swiper -->
@if ($sliders && count($sliders) > 0)
<section class="seccion-banners">
    <div class="contenido container swiper mySwiper">
        <div class="swiper-wrapper">
            @foreach ($sliders as $slide)
            <div class="swiper-slide">
                <img src="{{ asset($slide->imagen) }}" alt="{{ $slide->descripcion }}"
                    title="{{ $slide->titulo }}" loading="lazy">
            </div>
            @endforeach
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
    </div>
</section>
@endif
@if ($indicadoresRecientes->count() > 0)
<section class="seccion-ultimos">
    <div class="container actualizaciones">
        <h2>
            Últimas actualizaciones de indicadores
        </h2>
        <ul class="lista-indicadores">
            @foreach ($indicadoresRecientes as $indicador)
            <li>
                <a href="{{ route('ficha-tecnica.show', $indicador) }}">
                    <h4>
                        {{ $indicador->nombre }}
                    </h4>
                </a>
            </li>
            @endforeach
        </ul>
    </div>
</section>
@endif

<section class="seccion-rejilla">
    <div class="container">
        <div class="row my-5">
            <div class="col-md-4">
                <div class="row hvr-shrink mb-4">
                    <a style="text-decoration:none;" href="https://ped2024-2030.puebla.gob.mx/" target="_blank">
                        <img class="imagen-noticias" src="{{ asset('img/Banners/General/Baby_1.jpg') }}" alt="PED"
                            title="Banner Plan Estatal de Desarrollo" loading="lazy" />
                    </a>
                </div>
            </div>
            <!-- <div class="col-md-4">
                    <div class="row hvr-shrink mb-4">
                        <a style="text-decoration:none;" href="{{ url('/agenda-mod') }}">
                            <img class="imagen-noticias" src="{{ asset('img/Banners/General/Baby_3.jpg') }}" alt="DATOS"
                                title="Banner Datos Abiertos" loading="lazy">
                        </a>
                    </div>
                    <div class="row hvr-shrink">
                        <a style="text-decoration:none;" href="{{ url('/mod-ped-programas') }}">
                            <img class="imagen-noticias" src="{{ asset('img/Banners/General/Baby_4.jpg') }}" alt="DERIVADOS"
                                title="Banner Programas Derivados" loading="lazy">
                        </a>
                    </div>

                    <div class="row hvr-shrink">
                        <a style="text-decoration:none;" href="{{ url('/datos-abiertos-ped') }}">
                            <img class="imagen-noticias" src="{{ asset('img/Banners/General/Baby_2.jpg') }}" alt="AGENDA"
                                title="Banner Datos Abiertos SPED" loading="lazy">
                        </a>
                    </div>
                </div> -->
            <div class="col-md-4">
                <div class="row hvr-shrink">
                    <a style="text-decoration:none;" href="{{ url('/datos-abiertos-ped') }}">
                        <img class="imagen-noticias" src="{{ asset('img/Banners/General/Baby_2.jpg') }}" alt="AGENDA"
                            title="Banner Datos Abiertos SPED" loading="lazy">
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="row hvr-shrink mb-4">
                    <a style="text-decoration:none;" href="https://sei.puebla.gob.mx/" target="_blank">
                        <img class="imagen-noticias" src="{{ asset('img/Banners/General/Banner-SEI.webp') }}" alt="PED"
                            title="Sistema Estatal de Información" loading="lazy">
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="seccion-fuentes">
    <div class="container ligas" style="margin:auto;">
        &nbsp;
        <div class="col-md-12" style="margin-left: -50px; margin-right: auto;">
            <h2>Fuentes de Consulta</h2>
        </div>
        &nbsp;
        <div class="row">
            <img class="pleca-img" src="{{ asset('img/linea-1.png') }}" title="Pleca de titulo Fuentes de Consulta"
                alt="Pleca compuesta por una barra de cuatro colores en forma horizontal" loading="lazy">
        </div>
        <div class="logo-slider">
            <div class="logo-slide-track">
                <div class="slide">
                    <a href="https://www.iadb.org/es" target="blank_" style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/BID.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://www.coneval.org.mx/Paginas/principal.aspx" target="blank_"
                        style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/CONEVAL.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://imco.org.mx/" target="blank_" style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/IMCO.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://www.inegi.org.mx/" target="blank_" style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/INEGI.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://www.transparenciapresupuestaria.gob.mx/" target="blank_"
                        style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/OBSERVATORIO.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://www1.undp.org/content/undp/es/home.html" target="blank_"
                        style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/PNUD.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://www.gob.mx/siap" target="blank_" style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/SADER.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://www.gob.mx/sep/acciones-y-programas/estadistica-educativa-15782?state=published"
                        target="blank_" style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/SEP.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://www.gob.mx/sesnsp/acciones-y-programas/informacion-de-incidencia-delictiva-nacional?state=published"
                        target="blank_" style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/SESNSP.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="http://www.stps.gob.mx/gobmx/estadisticas/" target="blank_"
                        style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/STPS.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://www.iadb.org/es" target="blank_" style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/BID.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://www.coneval.org.mx/Paginas/principal.aspx" target="blank_"
                        style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/CONEVAL.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://imco.org.mx/" target="blank_" style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/IMCO.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://www.inegi.org.mx/" target="blank_" style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/INEGI.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://www.transparenciapresupuestaria.gob.mx/" target="blank_"
                        style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/OBSERVATORIO.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://www1.undp.org/content/undp/es/home.html" target="blank_"
                        style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/PNUD.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://www.gob.mx/siap" target="blank_" style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/SADER.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://www.gob.mx/sep/acciones-y-programas/estadistica-educativa-15782?state=published"
                        target="blank_" style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/SEP.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="https://www.gob.mx/sesnsp/acciones-y-programas/informacion-de-incidencia-delictiva-nacional?state=published"
                        target="blank_" style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/SESNSP.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
                <div class="slide">
                    <a href="http://www.stps.gob.mx/gobmx/estadisticas/" target="blank_"
                        style="text-decoration:none; color:#fff;">
                        <img src="{{ asset('img/sitios_interes/STPS.png') }}" class="w-100" alt="" loading="lazy">
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@section('jss-final')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var slideTrack = document.querySelector(".slide-track");
        var swiperContainer = document.querySelector(".mySwiper");

        // Si existe slide-track, duplicamos su contenido
        if (slideTrack) {
            slideTrack.innerHTML += slideTrack.innerHTML;
        }

        // Si existe un Swiper, lo inicializamos
        if (swiperContainer) {
            var swiper = new Swiper(".mySwiper", {
                spaceBetween: 30,
                centeredSlides: true,
                autoplay: {
                    delay: 4500,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
            });
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tourCompletadoKey = 'miAppTourPrincipal_v1_completado';

        if (localStorage.getItem(tourCompletadoKey) !== 'true') {

            const driver = window.driver.js.driver;

            const driverObj = driver({
                showProgress: true,
                allowClose: true,
                nextBtnText: 'Siguiente',
                prevBtnText: 'Anterior',
                doneBtnText: 'Hecho',
                closeBtnText: 'Saltar',
                steps: [{
                        element: '#navbar', // Asegúrate que este ID exista
                        popover: {
                            title: 'Nueva barra superior',
                            description: 'Ahora solo te enfocarás en lo importante, el contenido.',
                        }
                    },
                    {
                        element: '.seccion-slider', // Asegúrate que esta clase exista y sea el elemento deseado
                        popover: {
                            title: 'Carrusel de indicadores',
                            description: '¿No sabes por donde empezar? Comienza por esta selección de indicadores.',
                        }
                    },
                    {
                        element: '#menuButton', // Asegúrate que este ID exista
                        popover: {
                            title: 'Menú de navegación compacto',
                            description: 'El menú de navegación es más compacto y fácil de usar.',
                        }
                        // Aquí necesitarás lógica para abrir el menú si el siguiente paso está dentro de él,
                        // usando onNextClick o onHighlightStarted en el siguiente paso.
                    },
                    {
                        element: '#mobileMenu', // Asumiendo que cambiaste '.mobile-menu' a '#mobileMenu' si es un ID
                        popover: {
                            title: 'Enfócate en dónde estás',
                            description: 'La parte del sitio que visites se resaltará.',
                        },
                        onHighlightStarted: () => { // Hook para ejecutar ANTES de mostrar este paso
                            // Aquí deberías tener la lógica para ABRIR tu menú móvil si está cerrado. Ejemplo:
                            const menuButton = document.getElementById('menuButton');
                            const mobileMenu = document.getElementById('mobileMenu');
                            if (menuButton && mobileMenu && !mobileMenu.classList.contains(
                                    'active')) { // 'active' o la clase que uses
                                menuButton.click(); // Simula el clic para abrir
                            }
                        }
                    },
                    {
                        element: '#mobilePedDropdownTrigger', // Asegúrate que este ID exista
                        popover: {
                            title: '¿Buscas los indicadores del Plan Estatal de Desarrollo?',
                            description: 'Puedes acceder a ellos dando clic aqui, y se mostrarán por eje.',
                        },
                        onHighlightStarted: () => { // Hook para ejecutar ANTES de mostrar este paso
                            // Aquí deberías tener la lógica para ABRIR tu menú móvil si está cerrado. Ejemplo:
                            const menuButton = document.getElementById('menuButton');
                            const mobileMenu = document.getElementById('mobileMenu');
                            if (menuButton && mobileMenu && !mobileMenu.classList.contains(
                                    'active')) { // 'active' o la clase que uses
                                menuButton.click(); // Simula el clic para abrir
                            }
                        }
                    },
                    {
                        element: '#buscador', // Asegúrate que este ID exista
                        popover: {
                            title: 'Nuevo buscador integrado',
                            description: '¿No encuentras lo que buscas? Utiliza este botón para encontrar rápidamente la información que necesitas.',
                        },
                        onHighlightStarted: () => { // Hook para ejecutar ANTES de mostrar este paso
                            // Aquí deberías tener la lógica para ABRIR tu menú móvil si está cerrado. Ejemplo:
                            const menuButton = document.getElementById('menuButton');
                            const mobileMenu = document.getElementById('mobileMenu');
                            if (menuButton && mobileMenu && !mobileMenu.classList.contains(
                                    'active')) { // 'active' o la clase que uses
                                menuButton.click(); // Simula el clic para abrir
                            }
                        }
                    },
                    {
                        element: '#closeMenuButton', // Asegúrate que este ID exista y sea visible
                        popover: {
                            title: 'Cierra el menú de navegación',
                            description: 'Puedes cerrar el menú de navegación en cualquier momento para tener más espacio en pantalla.',
                        },
                        onHighlightStarted: () => { // Hook para ejecutar ANTES de mostrar este paso
                            // Aquí deberías tener la lógica para ABRIR tu menú móvil si está cerrado. Ejemplo:
                            const menuButton = document.getElementById('menuButton');
                            const mobileMenu = document.getElementById('mobileMenu');
                            if (menuButton && mobileMenu && !mobileMenu.classList.contains(
                                    'active')) { // 'active' o la clase que uses
                                menuButton.click(); // Simula el clic para abrir
                            }
                        }
                    },
                    {
                        element: '.seccion-ultimos', // Asegúrate que esta clase exista
                        popover: {
                            title: '¿Quieres saber qué indicadores se han actualizado recientemente?',
                            description: 'En esta sección encontrarás los últimos indicadores que han sido actualizados, para que siempre estés al tanto de la información más reciente.'
                        },
                        onHighlightStarted: () => {
                            // Aquí podrías necesitar CERRAR el menú móvil si el buscador no está dentro de él.
                            const menuButton = document.getElementById('menuButton');
                            const mobileMenu = document.getElementById('mobileMenu');
                            const closeMenuButton = document.getElementById('closeMenuButton');
                            if (menuButton && mobileMenu && mobileMenu.classList.contains(
                                    'active')) {
                                if (closeMenuButton) closeMenuButton.click();
                                else menuButton.click(); // Si menuButton es un toggle
                            }
                        }
                    },
                    {
                        element: '.footer', // Asegúrate que esta clase exista
                        popover: {
                            title: 'Nuevo diseño del pie de página',
                            description: 'El pie de página ha sido rediseñado para ser más informativo y fácil de navegar.',
                        },
                    }
                ],
                onDestroyStarted: () => {
                    localStorage.setItem(tourCompletadoKey, 'true');
                    location.reload();
                }
            });

            // 3. Inicia el tour
            driverObj.drive();

        } else {
            // Opcional: puedes registrar en la consola que el tour no se muestra.
            console.log('El tour "' + tourCompletadoKey + '" ya fue completado y no se mostrará.');
        }
    });
</script>
@endsection
@endsection
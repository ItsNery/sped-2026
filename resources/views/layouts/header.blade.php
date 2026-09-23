<header class="site-header">
    <div class="header-logos-container">
        <div class="container">
            <div class="logos-wrapper">
                <div class="logos-group left">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('img/Cintillos-SPED-35.png') }}" alt="Logo SPED">
                    </a>
                </div>
            </div>

            <button id="hamburger-menu" class="hamburger-menu" aria-label="Abrir menú de navegación"
                aria-controls="main-nav" aria-expanded="false">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </div>
</header>

<nav id="main-nav" class="main-nav" aria-label="Navegación principal">
    <div class="container nav-container">
        <ul class="nav-links">
            <li>
                <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Inicio</a>
            </li>

            <li class="dropdown">
                <a href="#" data-menu-toggle
                    class="{{ request()->is('normatividad', 'reportes-anuales', 'datos-abiertos*', 'informacion-general/*') ? 'active' : '' }}">
                    Datos generales <span class="arrow">▾</span>
                </a>
                <ul class="dropdown-content">
                    <li><a href="{{ url('/informacion-general/api') }}">API</a></li>
                    <li><a href="{{ url('/normatividad') }}">Normatividad</a></li>
                    <li><a href="{{ route('reportes-anuales') }}">Reportes anuales</a></li>
                    <li class="dropdown-nested">
                        <a href="#" data-menu-toggle>Datos abiertos <span class="arrow-right">▸</span></a>
                        <ul class="dropdown-content-nested">
                            <li><a href="{{ url('/datos-abiertos-ped') }}">Plan Estatal de Desarrollo 2024-2030</a></li>
                            <li><a href="{{ url('/datos-abiertos-hist-ped') }}">Hist. Planes Estatales de Desarrollo</a>
                            </li>
                            <li><a href="{{ url('/datos-abiertos-hist-mun') }}">Hist. Indicadores Municipales</a></li>
                        </ul>
                    </li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" data-menu-toggle
                    class="{{ request()->is('ped', 'ped/eje-1', 'ped/eje-2', 'ped/eje-3', 'ped/eje-4', 'ped/eje-5', 'ped/eje-6') ? 'active' : '' }}">
                    Plan Estatal de Desarrollo <span class="arrow">▾</span>
                </a>
                <ul class="dropdown-content">
                    <li><a href="{{ url('/ped') }}">Visión general</a></li>
                    <li><a href="{{ url('/ped/eje-1') }}">Eje 1 - Humanismo con Bienestar</a></li>
                    <li><a href="{{ url('/ped/eje-2') }}">Eje 2 - Prosperidad y Estabilidad Económica</a></li>
                    <li><a href="{{ url('/ped/eje-3') }}">Eje 3 - Estado de Derecho, Seguridad y Justicia</a></li>
                    <li><a href="{{ url('/ped/eje-4') }}">Eje 4 - Desarrollo Urbano y Crecimiento Sostenible</a></li>
                    <li><a href="{{ url('/ped/eje-5') }}">Eje 5 - Gobierno Transformador y de Resultados</a></li>
                    <li><a href="{{ url('/ped/eje-6') }}">Eje Transversal - Por Amor a Puebla</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" data-menu-toggle class="{{ request()->is('ped-programas*') ? 'active' : '' }}">
                    Programas Derivados <span class="arrow">▾</span>
                </a>
                <ul class="dropdown-content">
                    <li><a href="{{ url('/ped-programas/sectoriales') }}">Programas Sectoriales</a></li>
                    <li><a href="{{ url('/ped-programas/especiales') }}">Programas Especiales</a></li>
                    <li><a href="{{ url('/ped-programas/institucionales') }}">Programas Institucionales</a></li>
                </ul>
            </li>

            <li>
                <a href="{{ url('/pm') }}"
                    class="{{ request()->is('pm*', 'ficha-tecnica-municipal/*') ? 'active' : '' }}">
                    Planes Municipales de Desarrollo
                </a>
            </li>
            <li>
                <a href="#customSearchModal" onclick="openSearchModal(event);" aria-label="Buscar indicadores"
                    title="Buscar indicadores">
                    <i class="fas fa-search" aria-hidden="true"></i><span class="visually-hidden">Buscar indicadores</span>
                </a>
            </li>
            <li>
                <a href="{{ route('login') }}" aria-label="Iniciar sesión" title="Iniciar sesión">
                    <i class="fas fa-right-to-bracket" aria-hidden="true"></i><span class="visually-hidden">Iniciar sesión</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

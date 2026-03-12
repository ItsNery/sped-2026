<header id="navbar">
    <div class="encabezado-movil" id="movil-navbar">
        <div class="logo">
            <a href="{{ url('/') }}">
                <img src="{{ asset('img/logos_sped.png') }}" alt="Logo SPED" class="w-100">
            </a>
        </div>
        <div class="menu">
            <button class="menu-button" id="menuButton" aria-label="Abrir menú" aria-expanded="false"
                aria-controls="mobileMenu" title="Mostrar menú">☰</button>
        </div>
    </div>

    <div class="menu-overlay" id="menuOverlay"></div>

    <div class="mobile-menu" id="mobileMenu" role="dialog" aria-modal="true" aria-labelledby="mobileMenuTitle">
        <div class="logo mb-3 d-flex justify-content-between align-items-center">
            <h2 id="mobileMenuTitle" class="h5">Menú Principal</h2> <button class="btn-close btn-close-white"
                aria-label="Cerrar menú" id="closeMenuButton"></button>
        </div>
        <ul>
            <li class="nav-item {{ request()->is('/') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/') }}">Inicio</a>
            </li>

            <li
                class="nav-item dropdown {{ request()->is('informacion-general', 'normatividad', 'datos-abiertos*') ? 'active' : '' }}">
                <a class="nav-link dropdown-toggle" href="#" id="mobileInicioDropdownTrigger" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside"> Datos generales
                </a>
                <div class="dropdown-menu" aria-labelledby="mobileInicioDropdownTrigger">
                    <a class="dropdown-item" href="{{ url('/informacion-general') }}">Información general</a>
                    <a class="dropdown-item" href="{{ url('/normatividad') }}">Normatividad</a>

                    <div class="dropdown segundo-dropdown">
                        <a class="dropdown-item dropdown-toggle" href="#" id="mobileDatosAbiertosSubmenuTrigger"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Datos abiertos
                        </a>
                        <div class="dropdown-menu segundo-dropdown-menu"
                            aria-labelledby="mobileDatosAbiertosSubmenuTrigger">
                            <a class="dropdown-item" href="{{ url('/datos-abiertos-ped') }}">Plan Estatal de Desarrollo
                                2024-2030</a>
                            <a class="dropdown-item" href="{{ url('/datos-abiertos-hist-ped') }}">Hist. Planes Estatales
                                de Desarrollo</a>
                            <a class="dropdown-item" href="{{ url('/datos-abiertos-hist-mun') }}">Hist. Indicadores
                                Municipales</a>
                        </div>
                    </div>
                </div>
            </li>

            <li
                class="nav-item dropdown {{ request()->is('ped', 'ped/eje-1', 'ped/eje-2', 'ped/eje-3', 'ped/eje-4', 'ped/eje-5') ? 'active' : '' }}">
                <a class="nav-link dropdown-toggle" href="#" id="mobilePedDropdownTrigger" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Plan Estatal de Desarrollo
                </a>
                <div class="dropdown-menu" aria-labelledby="mobilePedDropdownTrigger">
                    <a class="dropdown-item" href="{{ url('/ped') }}">Visión general</a>
                    <a class="dropdown-item" href="{{ url('/ped/eje-1') }}">Eje 1 - Humanismo con Bienestar</a>
                    <a class="dropdown-item" href="{{ url('/ped/eje-2') }}">Eje 2 - Prosperidad y Estabilidad
                        Económica</a>
                    <a class="dropdown-item" href="{{ url('/ped/eje-3') }}">Eje 3 - Estado de Derecho, Seguridad y
                        Justicia</a>
                    <a class="dropdown-item" href="{{ url('/ped/eje-4') }}">Eje 4 - Desarrollo Urbano y Crecimiento
                        Sostenible</a>
                    <a class="dropdown-item" href="{{ url('/ped/eje-5') }}">Eje 5 - Gobierno Transformador y de
                        Resultados</a>
                    <a class="dropdown-item" href="{{ url('/ped/eje-6') }}">Eje Transversal - Por Amor a Puebla</a>
                </div>
            </li>
            {{-- Comentado el 15 de mayo en desarrollo  a la espera de que se defina la alineación de los futuros indicadores, que exista algun municipio con convenio y programas derivados --}}
            <li class="nav-item dropdown {{ request()->is('ped-programas*') ? 'active' : '' }}">
                <a class="nav-link dropdown-toggle" href="#" id="mobileProgramasDropdownTrigger" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Programas Derivados
                </a>
                <div class="dropdown-menu" aria-labelledby="mobileProgramasDropdownTrigger">
                    <a class="dropdown-item" href="{{ url('/ped-programas/sectoriales') }}">Programas Sectoriales</a>
                    <a class="dropdown-item" href="{{ url('/ped-programas/especiales') }}">Programas Especiales</a>
                    <a class="dropdown-item" href="{{ url('/ped-programas/institucionales') }}">Programas
                        Institucionales</a>
                    <a class="dropdown-item" href="{{ url('/ped-programas/regionales') }}">Programa Regional</a>
                </div>
            </li>

            <li class="nav-item {{ request()->is('pm*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/pm') }}">Planes Municipales de Desarrollo</a>
            </li>

            {{-- <li class="nav-item {{ request()->is('agenda') ? 'active' : '' }}">
            <a class="nav-link" href="{{ url('/agenda') }}">Agenda 2030</a>
            </li> --}}
            {{-- Fin de comentario --}}
            <li class="nav-item" id="buscador">
                <a class="nav-link" href="#" onclick="openSearchModal();">
                    <i class="fas fa-search" aria-hidden="true"></i> Buscar
                </a>
            </li>
            <li class="nav-item nav-item-login">
                <a class="nav-link" href="{{ route('login') }}" title="Iniciar sesión" rel="noopener"
                    target="_self">
                    <i class="fas fa-right-to-bracket" aria-hidden="true"></i> Entrar
                </a>
            </li>
        </ul>
    </div>
</header>
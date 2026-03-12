{{-- <header>
    <div class="row index-header">
        <div class="col-xs-12 col-sm-12 col-12 col-md-1"></div>
        <div class="col-xs-12 col-sm-12 col-12 col-md-3 logo_sped">
            <a href="{{ url('/') }}">
                <img src="{{ asset('img/logo_sped.png') }}" title="Logo de SPED">
            </a>
        </div>
        <div class="col-xs-12 col-sm-12 col-12 col-md-2"></div>
        <div class="col-xs-12 col-sm-12 col-12 col-md-3 gobierno_logos">
            <img class="w-120p" src="{{ asset('img/logos_gobierno.png') }}" title="Logo de Gobierno"
                alt="Logo del Gobierno de Puebla con los colores institucionales">
        </div>
        <div class="col-xs-12 col-sm-12 col-12 col-md-1 sped_admin">
            <a href="{{ url('/administrador') }}">
                <img class="login-image" src="{{ asset('img/login_1.png') }}" title="Imagen de Login"
                    alt="Imagen de una puerta y el texto Entrar">
            </a>
        </div>
        @php
            use Carbon\Carbon;
            $currentDate = Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY');
        @endphp
        <div class="col-md-12 date">
            {{ ucfirst($currentDate) }}
        </div>

    </div>
    <button class="hamburger" onclick="toggleMenu()">&#9776;</button>
    <div class="nav menu">
        <div class="nav1 dropdown">
            <button class="dropbtn" onclick="location.href='{{ url('/') }}';">Inicio</button>
            <div class="dropdown-content">
                <a href="{{ url('/informacion-general') }}">Información General</a>
                <a href="{{ url('/normatividad') }}">Normatividad</a>

                <div class="submenu">
                    <button class="subdropbtn">Datos Abiertos</button>
                    <div class="submenu-content">
                        <a href="{{ url('/datos-abiertos-ped') }}">Plan Estatal de Desarrollo 2019-2024</a>
                        <a href="{{ url('/datos-abiertos-mod-ped') }}" class="guinda-hover">Modificación y
                            Adecuación del PED 2019-2024</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="nav2 dropdown">
            <button class="dropbtn">Planes Estatales de Desarrollo</button>
            <div class="dropdown-content">

                <!-- Plan Estatal 1 -->
                <div class="submenu plan1">
                    <button class="subdropbtn">
                        <span class="textplan">Plan Estatal de Desarrollo 2019-2024</span>
                        <span class="no-vigente-indicator" style="color:white;">No Vigente</span></button>
                    <div class="submenu-content">
                        <a href="{{ url('/ped') }}">Visión general</a>
                        <a href="{{ url('/ped/eje-1') }}">Eje 1 - Seguridad Pública, Justicia y Estado de
                            Derecho</a>
                        <a href="{{ url('/ped/eje-2') }}">Eje 2 - Recuperación del Campo Poblano</a>
                        <a href="{{ url('/ped/eje-3') }}">Eje 3 - Desarrollo Económico para Todas y Todos</a>
                        <a href="{{ url('/ped/eje-4') }}">Eje 4 - Disminución de las Desigualdades</a>
                        <a href="{{ url('/ped/eje-5') }}">Eje Especial - Gobierno Democrático, Innovador y
                            Transparente</a>
                        <a href="{{ url('/ped/eje-6') }}">Enfoques Transversales</a>
                    </div>
                </div>

                <!-- Plan Estatal 2 -->
                <div class="submenu plan2">
                    <button class="subdropbtn">
                        <span class="textplan">Modificación y Adecuación del PED 2019-2024</span>
                        <span class="vigente-indicator" style="color:white;">Vigente</span></button>
                    <div class="submenu-content">
                        <a href="{{ url('/mod-ped') }}">Visión general</a>
                        <a href="{{ url('/mod-ped/eje-1') }}" class="guinda-hover">Eje 1 - Justicia Social y
                            Fortalecimiento del Estado de Derecho</a>
                        <a href="{{ url('/mod-ped/eje-2') }}" class="guinda-hover">Eje 2 - Sostenibilidad
                            Territorial y Desarrollo Integral</a>
                        <a href="{{ url('/mod-ped/eje-3') }}" class="guinda-hover">Eje 3 - Fortalecimiento del
                            Campo e Impulso a la Economía Justa y Social</a>
                        <a href="{{ url('/mod-ped/eje-4') }}" class="guinda-hover">Eje 4 - Desarrollo Integral,
                            Educación y Diversidad Cultural</a>
                        <a href="{{ url('/mod-ped/eje-5') }}" class="guinda-hover">Eje 5 - Transparencia,
                            Participación Ciudadana y Combate a la Corrupción</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="nav3 dropdown">
            <button class="dropbtn">Programas Derivados</button>
            <div class="dropdown-content">

                <!-- Plan Estatal 1 -->
                <div class="submenu plan1">
                    <button class="subdropbtn">
                        <span class="textplan">Plan Estatal de Desarrollo 2019-2024</span>
                        <span class="no-vigente-indicator" style="color:white;">No Vigente</span></button>
                    <div class="submenu-content">
                        <a href="{{ url('/ped-programas') }}">Visión general</a>
                        <a href="{{ url('/ped-programas/sectoriales') }}">Programas Sectoriales</a>
                        <a href="{{ url('/ped-programas/especiales') }}">Programas Especiales</a>
                        <a href="{{ url('/ped-programas/institucionales') }}">Programas Institucionales</a>
                        <a href="{{ url('/ped-programas/regionales') }}">Programas Regionales</a>
                    </div>
                </div>

                <!-- Plan Estatal 2 -->
                <div class="submenu plan2">
                    <button class="subdropbtn">
                        <span class="textplan">Modificación y Adecuación del PED 2019-2024 </span>
                        <span class="vigente-indicator" style="color:white;">Vigente</span></button>
                    <div class="submenu-content">
                        <a href="{{ url('/mod-ped-programas') }}" class="guinda-hover">
                            Visión general
                        </a>
                        <a href="{{ url('/mod-ped-programas/sectoriales') }}" class="guinda-hover">
                            1. Programas
                            Sectoriales
                        </a>
                        <a href="{{ url('/mod-ped-programas/especiales') }}" class="guinda-hover">
                            2. Programas
                            Especiales
                        </a>
                        <a href="{{ url('/mod-ped-programas/institucionales') }}" class="guinda-hover">
                            3. Programas
                            Institucionales
                        </a>
                        <a href="{{ url('/mod-ped-programas/regional') }}" class="guinda-hover">
                            4. Programa Regional
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="nav5 dropdown" onclick="location.href='{{ url('/pm') }}';">
            <button class="dropbtn">Histórico de los Planes Municipales de Desarrollo</button>
        </div>
        <div class="nav4 dropdown">
            <button class="dropbtn">Agenda 2030</button>
            <div class="dropdown-content">
                <a href="{{ url('/agenda') }}">Plan Estatal de Desarrollo 2019-2024</a>
                <a href="{{ url('/agenda-mod') }}" class="guinda-hover">Modificación y Adecuación del PED
                    2019-2024</a>
            </div>
        </div>
    </div>
</header> --}}

<header>
    <div class="index-header">
        <a href="{{ url('/') }}">
            <img src="{{ asset('img/logo_sped.png') }}" title="Logo de SPED">
        </a>
        <div class="gobierno_logos">
            <img class="w-120p" src="{{ asset('img/logos_gobierno.png') }}" title="Logo de Gobierno"
                alt="Logo del Gobierno de Puebla con los colores institucionales">
            <a href="{{ url('/administrador') }}">
                <img class="login-image" src="{{ asset('img/login_1.png') }}" title="Imagen de Login"
                    alt="Imagen de una puerta y el texto Entrar">
            </a>
        </div>
        @php
            use Carbon\Carbon;
            $currentDate = Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY');
        @endphp
        <div class="date">
            {{ ucfirst($currentDate) }}
        </div>

    </div>
    <ul class="menu">
        <li class="opcion1">
            <a href="#">Inicio</a>
            <ul>
                <li><a href="{{ url('/informacion-general') }}">Información general</a></li>
                <li><a href="{{ url('/normatividad') }}">Normatividad</a></li>
                <li class="segundoli">
                    <a href="#">Datos abiertos</a>
                    <ul>
                        <li><a href="{{ url('/datos-abiertos-ped') }}">Plan Estatal de Desarrollo 2024-2030</a></li>
                        <li><a href="{{ url('/datos-abiertos-hist-ped') }}">Histórico de los Planes Estatales</a></li>
                        <li><a href="{{ url('/') }}">Histórico de los Indicadores Municipales</a></li>
                    </ul>
                </li>
            </ul>
        </li>
        <li class="opcion2">
            <a href="#">Plan Estatal de Desarrollo</a>
            <ul>
                <li><a href="{{ url('/ped') }}">Visión general</a></li>
                <li><a href="{{ url('/ped/eje-1') }}">Eje 1 - Justicia Social y
                        Fortalecimiento del Estado de Derecho</a></li>
                <li><a href="{{ url('/ped/eje-2') }}">Eje 2 - Eje 2 - Sostenibilidad
                        Territorial y Desarrollo Integral</a></li>
                <li><a href="{{ url('/ped/eje-3') }}">Eje 3 - Fortalecimiento del
                        Campo e Impulso a la Economía Justa y Social</a></li>
                <li><a href="{{ url('/ped/eje-4') }}">Eje 4 - Desarrollo Integral,
                        Educación y Diversidad Cultural</a></li>
                <li><a href="{{ url('/ped/eje-5') }}">Eje 5 - Transparencia,
                        Participación Ciudadana y Combate a la Corrupción</a></li>
                {{-- <li class="segundoli">
                    <a href="#">Plan Estatal de Desarrollo</a>
                    <ul>
                        <li><a href="{{ url('/ped') }}">Visión general</a></li>
                        <li><a href="{{ url('/ped/eje-1') }}">Eje 1 - Seguridad Pública, Justicia y Estado de
                                Derecho</a></li>
                        <li><a href="{{ url('/ped/eje-2') }}">Eje 2 - Eje 2 - Recuperación del Campo Poblano</a></li>
                        <li><a href="{{ url('/ped/eje-3') }}">Eje 3 - Desarrollo Económico para Todas y Todos</a></li>
                        <li><a href="{{ url('/ped/eje-4') }}">Eje 4 - Disminución de las Desigualdades</a></li>
                        <li><a href="{{ url('/ped/eje-5') }}">Eje Especial - Gobierno Democrático, Innovador y
                                Transparente</a></li>
                        <li><a href="{{ url('/ped/eje-6') }}">Enfoques Transversales</a></li>
                    </ul>
                </li> --}}
                {{-- <li class="segundoli">
                    <a href="#">Modificación y Adecuación del PED 2019-2024</a>
                    <ul>
                        <li><a href="{{ url('/mod-ped') }}">Visión general</a></li>
                        <li><a href="{{ url('/mod-ped/eje-1') }}">Eje 1 - Justicia Social y
                                Fortalecimiento del Estado de Derecho</a></li>
                        <li><a href="{{ url('/mod-ped/eje-2') }}">Eje 2 - Eje 2 - Sostenibilidad
                                Territorial y Desarrollo Integral</a></li>
                        <li><a href="{{ url('/mod-ped/eje-3') }}">Eje 3 - Fortalecimiento del
                                Campo e Impulso a la Economía Justa y Social</a></li>
                        <li><a href="{{ url('/mod-ped/eje-4') }}">Eje 4 - Desarrollo Integral,
                                Educación y Diversidad Cultural</a></li>
                        <li><a href="{{ url('/mod-ped/eje-5') }}">Eje 5 - Transparencia,
                                Participación Ciudadana y Combate a la Corrupción</a></li>
                    </ul>
                </li> --}}
            </ul>
        </li>
        <li class="opcion3">
            <a href="#">Programas Derivados</a>
            <ul>
                <li><a href="{{ url('/ped-programas') }}">Visión general</a></li>
                <li><a href="{{ url('/ped-programas/sectoriales') }}">Programas Sectoriales</a></li>
                <li><a href="{{ url('/ped-programas/especiales') }}">Programas Especiales</a></li>
                <li><a href="{{ url('/ped-programas/institucionales') }}">Programas Institucionales</a>
                </li>
                <li><a href="{{ url('/ped-programas/regional') }}">Programa Regional</a></li>
                {{-- <li class="segundoli">
                    <a href="#">Plan Estatal de Desarrollo</a>
                    <ul>
                        <li><a href="{{ url('/ped-programas') }}">Visión general</a></li>
                        <li><a href="{{ url('/ped-programas/sectoriales') }}">Programas Sectoriales</a></li>
                        <li><a href="{{ url('/ped-programas/especiales') }}">Programas Especiales</a></li>
                        <li><a href="{{ url('/ped-programas/institucionales') }}">Programas Institucionales</a></li>
                        <li><a href="{{ url('/ped-programas/regionales') }}">Programas Regionales</a></li>
                    </ul>
                </li> --}}
                {{-- <li class="segundoli">
                    <a href="#">Modificación y Adecuación del PED 2019-2024</a>
                    <ul>
                        <li><a href="{{ url('/mod-ped-programas') }}">Visión general</a></li>
                        <li><a href="{{ url('/mod-ped-programas/sectoriales') }}">Programas Sectoriales</a></li>
                        <li><a href="{{ url('/mod-ped-programas/especiales') }}">Programas Especiales</a></li>
                        <li><a href="{{ url('/mod-ped-programas/institucionales') }}">Programas Institucionales</a>
                        </li>
                        <li><a href="{{ url('/mod-ped-programas/regional') }}">Programa Regional</a></li>
                    </ul>
                </li> --}}
            </ul>
        </li>
        <li class="opcion4">
            <a href="{{ url('/pm') }}">Planes Municipales de Desarrollo</a>
        </li>
        <li class="opcion5">
            <a href="{{ url('/agenda') }}">Agenda 2030</a>
            {{-- <ul> --}}
            {{-- <li><a href="{{ url('/agenda') }}">Plan Estatal de Desarrollo 2019-2024</a></li> --}}
            {{-- <li><a href="{{ url('/agenda-mod') }}">Modificación y Adecuación del PED 2019-2024</a></li> --}}
            {{-- </ul> --}}
        </li>
    </ul>

    <button class="menu-toggle">☰</button>
    {{-- <nav class="mobile-menu" id="mobileMenu">
        <ul>
            <li>
                <a href="{{url('/')}}">Inicio</a>
                <ul class="submenu">
                    <li><a href="{{ url('/informacion-general') }}">Información general</a></li>
                    <li><a href="{{ url('/normatividad') }}">Normatividad</a></li>
                    <li class="segundoli has-submenu">
                        <a href="#">Datos abiertos</a>
                        <ul>
                            <li><a href="{{ url('/datos-abiertos-ped') }}">Plan Estatal de Desarrollo 2019-2024</a>
                            </li>
                            <li><a href="{{ url('/datos-abiertos-mod-ped') }}">Modificación y Adecuación del PED
                                    2019-2024</a></li>
                            <li><a href="{{ url('/') }}">Histórico de los Indicadores Municipales</a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#">Planes Estatales de Desarrollo</a>
                <ul>
                    <li>
                        <a href="#">Plan Estatal de Desarrollo 2019-2024</a>
                        <ul>
                            <li><a href="{{ url('/ped') }}">Visión general</a></li>
                            <li><a href="{{ url('/ped/eje-1') }}">Eje 1 - Seguridad Pública, Justicia y Estado de
                                    Derecho</a></li>
                            <li><a href="{{ url('/ped/eje-2') }}">Eje 2 - Eje 2 - Recuperación del Campo Poblano</a>
                            </li>
                            <li><a href="{{ url('/ped/eje-3') }}">Eje 3 - Desarrollo Económico para Todas y Todos</a>
                            </li>
                            <li><a href="{{ url('/ped/eje-4') }}">Eje 4 - Disminución de las Desigualdades</a></li>
                            <li><a href="{{ url('/ped/eje-5') }}">Eje Especial - Gobierno Democrático, Innovador y
                                    Transparente</a></li>
                            <li><a href="{{ url('/ped/eje-6') }}">Enfoques Transversales</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#">Modificación y Adecuación del PED 2019-2024</a>
                        <ul>
                            <li><a href="{{ url('/mod-ped') }}">Visión general</a></li>
                            <li><a href="{{ url('/mod-ped/eje-1') }}">Eje 1 - Justicia Social y
                                    Fortalecimiento del Estado de Derecho</a></li>
                            <li><a href="{{ url('/mod-ped/eje-2') }}">Eje 2 - Eje 2 - Sostenibilidad
                                    Territorial y Desarrollo Integral</a></li>
                            <li><a href="{{ url('/mod-ped/eje-3') }}">Eje 3 - Fortalecimiento del
                                    Campo e Impulso a la Economía Justa y Social</a></li>
                            <li><a href="{{ url('/mod-ped/eje-4') }}">Eje 4 - Desarrollo Integral,
                                    Educación y Diversidad Cultural</a></li>
                            <li><a href="{{ url('/mod-ped/eje-5') }}">Eje 5 - Transparencia,
                                    Participación Ciudadana y Combate a la Corrupción</a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#">Programas Derivados</a>
                <ul>
                    <li>
                        <a href="#">Plan Estatal de Desarrollo 2019-2024</a>
                        <ul>
                            <li><a href="{{ url('/ped-programas') }}">Visión general</a></li>
                            <li><a href="{{ url('/ped-programas/sectoriales') }}">Programas Sectoriales</a></li>
                            <li><a href="{{ url('/ped-programas/especiales') }}">Programas Especiales</a></li>
                            <li><a href="{{ url('/ped-programas/institucionales') }}">Programas Institucionales</a>
                            </li>
                            <li><a href="{{ url('/ped-programas/regionales') }}">Programas Regionales</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#">Modificación y Adecuación del PED 2019-2024</a>
                        <ul>
                            <li><a href="{{ url('/mod-ped-programas') }}">Visión general</a></li>
                            <li><a href="{{ url('/mod-ped-programas/sectoriales') }}">Programas Sectoriales</a></li>
                            <li><a href="{{ url('/mod-ped-programas/especiales') }}">Programas Especiales</a></li>
                            <li><a href="{{ url('/mod-ped-programas/institucionales') }}">Programas
                                    Institucionales</a>
                            </li>
                            <li><a href="{{ url('/mod-ped-programas/regional') }}">Programa Regional</a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#">Histórico de los Planes Municipales de Desarrollo</a>
            </li>
            <li>
                <a href="#">Agenda 2030</a>
                <ul>
                    <li><a href="{{ url('/agenda') }}">Plan Estatal de Desarrollo 2019-2024</a></li>
                    <li><a href="{{ url('/agenda-mod') }}">Modificación y Adecuación del PED 2019-2024</a></li>
                </ul>
            </li>
        </ul>
    </nav> --}}
    <nav class="mobile-menu" id="mobileMenu">
        <ul class="list-unstyled">
            <li>
                <a href="#" class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                    data-bs-target="#submenuInicio" aria-expanded="false" aria-controls="submenuInicio">
                    Inicio
                    <i class="fa fa-chevron-down"></i>
                </a>
                <ul class="collapse" id="submenuInicio">
                    <li><a href="{{ url('/') }}">Página principal</a></li>
                    <li><a href="{{ url('/informacion-general') }}">Información general</a></li>
                    <li><a href="{{ url('/normatividad') }}">Normatividad</a></li>
                    <li>
                        <a href="#" class="d-flex justify-content-between align-items-center"
                            data-bs-toggle="collapse" data-bs-target="#submenuDatosAbiertos" aria-expanded="false"
                            aria-controls="submenuDatosAbiertos">
                            Datos abiertos
                            <i class="fa fa-chevron-down"></i>
                        </a>
                        <ul class="collapse" id="submenuDatosAbiertos">
                            <li><a href="{{ url('/datos-abiertos-ped') }}">Plan Estatal de Desarrollo 2019-2024</a>
                            </li>
                            <li><a href="{{ url('/datos-abiertos-mod-ped') }}">Modificación y Adecuación del PED</a>
                            </li>
                            <li><a href="{{ url('/') }}">Histórico de los Indicadores Municipales</a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#" class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                    data-bs-target="#submenuPlanes" aria-expanded="false" aria-controls="submenuPlanes">
                    Planes Estatales de Desarrollo
                    <i class="fa fa-chevron-down"></i>
                </a>
                <ul class="collapse" id="submenuPlanes">
                    <li>
                        <a href="#" class="d-flex justify-content-between align-items-center"
                            data-bs-toggle="collapse" data-bs-target="#submenuPlanEstatal" aria-expanded="false"
                            aria-controls="submenuPlanEstatal">
                            Plan Estatal de Desarrollo 2019-2024
                            <i class="fa fa-chevron-down"></i>
                        </a>
                        <ul class="collapse" id="submenuPlanEstatal">
                            <li><a href="{{ url('/ped') }}">Visión general</a></li>
                            <li><a href="{{ url('/ped/eje-1') }}">Eje 1 - Seguridad Pública, Justicia y Estado de
                                    Derecho</a></li>
                            <li><a href="{{ url('/ped/eje-2') }}">Eje 2 - Eje 2 - Recuperación del Campo Poblano</a>
                            </li>
                            <li><a href="{{ url('/ped/eje-3') }}">Eje 3 - Desarrollo Económico para Todas y Todos</a>
                            </li>
                            <li><a href="{{ url('/ped/eje-4') }}">Eje 4 - Disminución de las Desigualdades</a></li>
                            <li><a href="{{ url('/ped/eje-5') }}">Eje Especial - Gobierno Democrático, Innovador y
                                    Transparente</a></li>
                            <li><a href="{{ url('/ped/eje-6') }}">Enfoques Transversales</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#" class="d-flex justify-content-between align-items-center"
                            data-bs-toggle="collapse" data-bs-target="#submenuPlanEstatal2" aria-expanded="false"
                            aria-controls="submenuPlanEstatal2">
                            Modificación y Adecuación del PED 2019-2024
                            <i class="fa fa-chevron-down"></i>
                        </a>
                        <ul class="collapse" id="submenuPlanEstatal2">
                            <li><a href="{{ url('/mod-ped') }}">Visión general</a></li>
                            <li><a href="{{ url('/mod-ped/eje-1') }}">Eje 1 - Justicia Social y
                                    Fortalecimiento del Estado de Derecho</a></li>
                            <li><a href="{{ url('/mod-ped/eje-2') }}">Eje 2 - Eje 2 - Sostenibilidad
                                    Territorial y Desarrollo Integral</a></li>
                            <li><a href="{{ url('/mod-ped/eje-3') }}">Eje 3 - Fortalecimiento del
                                    Campo e Impulso a la Economía Justa y Social</a></li>
                            <li><a href="{{ url('/mod-ped/eje-4') }}">Eje 4 - Desarrollo Integral,
                                    Educación y Diversidad Cultural</a></li>
                            <li><a href="{{ url('/mod-ped/eje-5') }}">Eje 5 - Transparencia,
                                    Participación Ciudadana y Combate a la Corrupción</a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#" class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                    data-bs-target="#submenuProgDer" aria-expanded="false" aria-controls="submenuProgDer">
                    Programas Derivados
                    <i class="fa fa-chevron-down"></i>
                </a>
                <ul class="collapse" id="submenuProgDer">
                    <li>
                        <a href="#" class="d-flex justify-content-between align-items-center"
                            data-bs-toggle="collapse" data-bs-target="#submenuProgDer1" aria-expanded="false"
                            aria-controls="submenuProgDer1">
                            Plan Estatal de Desarrollo 2019-2024
                            <i class="fa fa-chevron-down"></i>
                        </a>
                        <ul class="collapse" id="submenuProgDer1">
                            <li><a href="{{ url('/ped-programas') }}">Visión general</a></li>
                            <li><a href="{{ url('/ped-programas/sectoriales') }}">Programas Sectoriales</a></li>
                            <li><a href="{{ url('/ped-programas/especiales') }}">Programas Especiales</a></li>
                            <li><a href="{{ url('/ped-programas/institucionales') }}">Programas Institucionales</a>
                            </li>
                            <li><a href="{{ url('/ped-programas/regionales') }}">Programas Regionales</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#" class="d-flex justify-content-between align-items-center"
                            data-bs-toggle="collapse" data-bs-target="#submenuProgDer2" aria-expanded="false"
                            aria-controls="submenuProgDer2">
                            Modificación y Adecuación del PED 2019-2024
                            <i class="fa fa-chevron-down"></i>
                        </a>
                        <ul class="collapse" id="submenuProgDer2">
                            <li><a href="{{ url('/mod-ped-programas') }}">Visión general</a></li>
                            <li><a href="{{ url('/mod-ped-programas/sectoriales') }}">Programas Sectoriales</a></li>
                            <li><a href="{{ url('/mod-ped-programas/especiales') }}">Programas Especiales</a></li>
                            <li><a href="{{ url('/mod-ped-programas/institucionales') }}">Programas
                                    Institucionales</a>
                            </li>
                            <li><a href="{{ url('/mod-ped-programas/regional') }}">Programa Regional</a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li>
                <a href="{{ url('pm') }}" class="d-flex justify-content-between align-items-center">
                    Histórico de los Planes Municipales de Desarrollo
                </a>
            </li>
            <li>
                <a href="#" class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                    data-bs-target="#submenuAgenda" aria-expanded="false" aria-controls="submenuAgenda">
                    Agenda 2030
                    <i class="fa fa-chevron-down"></i>
                </a>
                <ul class="collapse" id="submenuAgenda">
                    <li><a href="{{ url('/agenda') }}">Plan Estatal de Desarrollo 2019-2024</a></li>
                    <li><a href="{{ url('/agenda-mod') }}">Modificación y Adecuación del PED 2019-2024</a></li>
                </ul>
            </li>
        </ul>
    </nav>

</header>

@php
    $sidebarUser = auth()->user();
    $sidebarInicio = route('dashboard');

    if ($sidebarUser
        && !$sidebarUser->isAdministrator()
        && !$sidebarUser->can('ver-panel-avance-general')
        && $sidebarUser->can('ver-indicador')) {
        $sidebarInicio = route('panel-indicadores.index');
    }
@endphp

<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="{{ $sidebarInicio }}" aria-label="Inicio del SPED">
            <img src="{{ asset('img/logo-sped-new.png') }}" alt="SPED" height="36">
        </a>
        <button class="sidebar-close d-lg-none" type="button" data-sidebar-toggle aria-label="Cerrar menú">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
    </div>

    @auth
        <div class="sidebar-user">
            <div class="sidebar-user__identity">
                <div class="avatar-circle">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
                    <div class="sidebar-user-role">{{ Auth::user()->getRoleNames()->join(', ') ?: 'Sin rol' }}</div>
                </div>
            </div>
        </div>
    @endauth

    <nav aria-label="Navegación administrativa">
        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a class="sidebar-link {{ request()->routeIs('dashboard', 'panel-indicadores.index') ? 'active' : '' }}" href="{{ $sidebarInicio }}" title="Inicio">
                    <i class="fas fa-home" aria-hidden="true"></i><span>Inicio</span>
                </a>
            </li>

            @if (auth()->user()->can('ver-indicador') || auth()->user()->can('ver-municipios-convenio'))
                <li class="sidebar-item"><span class="sidebar-label">Gestión</span></li>
                @can('ver-indicador')
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('panel-indicadores*') ? 'active' : '' }}" href="{{ route('panel-indicadores.index') }}" title="Indicadores">
                            <i class="fas fa-chart-line" aria-hidden="true"></i><span>Indicadores</span>
                        </a>
                    </li>
                @endcan
                @can('ver-municipios-convenio')
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('panel-municipios-convenio*', 'indicadores-municipales*') ? 'active' : '' }}" href="{{ route('panel-municipios-convenio.index') }}" title="Municipios">
                            <i class="fas fa-city" aria-hidden="true"></i><span>Municipios</span>
                        </a>
                    </li>
                @endcan
            @endif

    @if ($sidebarUser?->isAdministrator())
                <li class="sidebar-divider" role="separator"></li>
                <li class="sidebar-item"><span class="sidebar-label">Administración</span></li>
                <li class="sidebar-item">
                    <a class="sidebar-link {{ request()->routeIs('panel-usuarios*', 'usuarios*') ? 'active' : '' }}" href="{{ route('panel-usuarios.index') }}" title="Usuarios">
                        <i class="fas fa-users" aria-hidden="true"></i><span>Usuarios</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link {{ request()->routeIs('panel-roles*') ? 'active' : '' }}" href="{{ route('panel-roles.index') }}" title="Roles">
                        <i class="fas fa-user-shield" aria-hidden="true"></i><span>Roles</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link {{ request()->routeIs('panel-logs*') ? 'active' : '' }}" href="{{ route('panel-logs.index') }}" title="Bitácora">
                        <i class="fas fa-clipboard-list" aria-hidden="true"></i><span>Bitácora</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link {{ request()->routeIs('panel-accesos*') ? 'active' : '' }}" href="{{ route('panel-accesos.index') }}" title="Accesos">
                        <i class="fas fa-shield-halved" aria-hidden="true"></i><span>Accesos</span>
                    </a>
                </li>
            @endif

            <li class="sidebar-divider" role="separator"></li>
            <li class="sidebar-item"><span class="sidebar-label">Manuales</span></li>
            @auth
                @if (auth()->user()->hasRole('Enlace dependencia'))
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ asset('docs/normatividad/Manual-Instituciones.pdf') }}" target="_blank" rel="noopener" title="Manual de usuario">
                            <i class="fas fa-book" aria-hidden="true"></i><span>Manual de usuario</span>
                        </a>
                    </li>
                @endif
            @endauth
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ asset('docs/normatividad/Guia_para_la_modificacion_de_Indicadores.zip') }}" target="_blank" rel="noopener" title="Manual de indicadores">
                    <i class="fas fa-file-arrow-down" aria-hidden="true"></i><span>Manual de indicadores</span>
                </a>
            </li>

            @if (auth()->user()->isAdministrator())
                <li class="sidebar-divider" role="separator"></li>
                <li class="sidebar-item"><span class="sidebar-label">Catálogos</span></li>
                <li class="sidebar-item">
                    <a class="sidebar-link {{ request()->routeIs('panel-cat-instituciones*') ? 'active' : '' }}" href="{{ route('panel-cat-instituciones.index') }}" title="Instituciones">
                        <i class="fas fa-building" aria-hidden="true"></i><span>Instituciones</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link {{ request()->routeIs('panel-cat-planes*') ? 'active' : '' }}" href="{{ route('panel-cat-planes.index') }}" title="PEDs">
                        <i class="fas fa-map" aria-hidden="true"></i><span>PEDs</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link {{ request()->routeIs('panel-cat-prog-der-sect*') ? 'active' : '' }}" href="{{ route('panel-cat-prog-der-sect.index') }}" title="Programas sectoriales">
                        <i class="fas fa-layer-group" aria-hidden="true"></i><span>PDs. Sectoriales</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link {{ request()->routeIs('panel-cat-prog-der-esp*') ? 'active' : '' }}" href="{{ route('panel-cat-prog-der-esp.index') }}" title="Programas especiales">
                        <i class="fas fa-star" aria-hidden="true"></i><span>PDs. Especiales</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link {{ request()->routeIs('panel-cat-prog-der-reg*') ? 'active' : '' }}" href="{{ route('panel-cat-prog-der-reg.index') }}" title="Programas regionales">
                        <i class="fas fa-map-location-dot" aria-hidden="true"></i><span>PDs. Regionales</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link {{ request()->routeIs('panel-cat-prog-der-instit*') ? 'active' : '' }}" href="{{ route('panel-cat-prog-der-instit.index') }}" title="Programas institucionales">
                        <i class="fas fa-sitemap" aria-hidden="true"></i><span>PDs. Institucionales</span>
                    </a>
                </li>
            @endif
        </ul>
    </nav>

    @auth
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form">
                @csrf
                <a class="sidebar-link text-danger" href="{{ route('logout') }}" title="Cerrar sesión" onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i><span>Cerrar sesión</span>
                </a>
            </form>
        </div>
    @endauth
</aside>
<div class="sidebar-overlay" id="sidebarOverlay" data-sidebar-toggle></div>

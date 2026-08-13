@extends('layouts.plantilla')
@section('title', 'Inicio')
@section('meta-description',
    'Página principal del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla. Consulta el avance de los indicadores del PED 2024-2030.')
@section('canonical-url', url()->current())
@section('og-title',
    'Inicio - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Dashboard de seguimiento al Plan Estatal de Desarrollo 2024-2030 del Estado de Puebla.
     Consulta el avance de indicadores estratégicos, sectoriales, especiales e institucionales.')
@section('og:url', url()->current())
@section('twitter-title',
    'Inicio - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Dashboard de seguimiento al Plan Estatal de Desarrollo 2024-2030 del Estado de
    Puebla.')
@section('css')
@endsection
@section('jss-inicial')
@endsection

@section('content')

    {{-- ============================================================
     1. HERO SECTION
     ============================================================ --}}
    <section class="inicio-hero @if($heroVideo) inicio-hero--video @endif">
        @if ($heroVideo)
            <video class="inicio-hero__video" autoplay muted loop playsinline preload="metadata"
                poster="{{ asset('img/puebla_hero_bg.png') }}" aria-hidden="true">
                <source src="{{ asset($heroVideo) }}" type="video/webm">
            </video>
        @endif
        <div class="inicio-hero__container">
            <div class="inicio-hero__content">
                <span class="inicio-hero__tag">Acerca del SPED</span>
                <h1 class="inicio-hero__title">
                    Sistema Estatal de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
                </h1>
                <p class="inicio-hero__subtitle">
                    Plataforma oficial e integradora que organiza y difunde el seguimiento puntual al avance de los
                    Indicadores
                     Estratégicos, Sectoriales, Especiales e Institucionales del Estado de Puebla, fortaleciendo
                    la planeación democrática y la toma de decisiones públicas.
                </p>
                <a href="#vision-estrategica" target="_self" rel="noopener noreferrer" class="inicio-hero__cta">
                    Ver más
                </a>
            </div>
            <div class="inicio-hero__logo-wrapper">
                <div class="inicio-hero__logo">
                    <img src="{{ asset('img/isologo-sped-blanco.png') }}"
                        alt="Logo del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo">
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
     2. DASHBOARD RESUMEN (Avance General del PED)
     ============================================================ --}}
    <section class="inicio-dashboard" id="avance-general">
        <div class="inicio-dashboard__container">
            <div class="row align-items-center g-5">
                <div class="col-lg-5 col-md-12 text-center text-lg-start border-end-lg">
                    <div class="inicio-dashboard__brand-block">
                        <img src="{{ asset('img/logo-ped.webp') }}" alt="Plan Estatal de Desarrollo 2024-2030"
                            class="inicio-dashboard__mide-logo">

                        <div class="inicio-dashboard__mide-ejes-wrapper">
                            <span class="inicio-dashboard__mide-ejes-label">Ejes:</span>
                            <div class="inicio-dashboard__mide-ejes">
                                @foreach ($ejesData as $eje)
                                    <a href="{{ url('/ped/eje-' . $eje['numero']) }}" class="inicio-eje-pill"
                                        style="background-color: var(--color-eje{{ $eje['numero'] }});"
                                        title="Eje {{ $eje['numero'] }} — {{ $eje['nombre'] }}" target="_self">
                                        <img src="{{ asset('img/iconos/eje-' . $eje['numero'] . '.png') }}"
                                            alt="Icono del Eje {{ $eje['numero'] }}">
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="https://ped2024-2030.puebla.gob.mx/" target="_blank" rel="noopener noreferrer"
                                class="inicio-dashboard__cta">
                                Consulta el Plan Estatal <i class="fas fa-external-link-alt ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 col-md-12">
                    <div class="inicio-dashboard__stats-block">
                        <div
                            class="d-flex align-items-center gap-4 flex-wrap flex-sm-nowrap justify-content-center justify-content-sm-start mb-3">
                            <div class="inicio-dashboard__mide-gauge-wrapper">
                                <div id="mainGaugeInicio" class="inicio-dashboard__gauge"></div>
                                <div class="inicio-dashboard__gauge-label" style="color: {{ $colorPlan }};">
                                    {{ number_format($avancePlan, 2) }}%
                                </div>
                            </div>

                            <div class="text-center text-sm-start">
                                <h3 class="inicio-dashboard__stats-title">
                                    <strong>{{ $metricasPlan['total_registrados'] }}</strong> Indicadores registrados
                                </h3>
                                <p class="inicio-dashboard__stats-subtitle">
                                    Avance promedio de indicadores evaluables
                                </p>
                                <p class="mb-0 text-muted small">
                                    {{ $metricasPlan['total_evaluables'] }} evaluables ·
                                    {{ number_format($metricasPlan['cobertura_evaluacion'], 2) }}% de cobertura
                                </p>
                            </div>
                        </div>

                        <div class="inicio-dashboard__composition" aria-labelledby="inicio-composition-title">
                            <div class="inicio-dashboard__composition-heading">
                                <span id="inicio-composition-title">Estructura del seguimiento</span>
                                <small>{{ $composicionPlan['total'] }} indicadores</small>
                            </div>
                            <div class="inicio-dashboard__composition-split">
                                <div class="inicio-dashboard__composition-item">
                                    <strong>{{ $composicionPlan['estrategicos'] }}</strong>
                                    <span>Estratégicos</span>
                                </div>
                                <div class="inicio-dashboard__composition-item">
                                    <strong>{{ $composicionPlan['derivados'] }}</strong>
                                    <span>De programas derivados</span>
                                </div>
                            </div>
                        </div>

                        <p class="inicio-dashboard__stats-desc">
                            El avance promedio se calcula con indicadores que cuentan con datos validados y condiciones suficientes para compararse contra una meta. Los indicadores no evaluables se muestran por separado en el desglose.
                            <a href="{{ url('/ped#metodologia') }}" class="inicio-dashboard__methodology-link">¿Cómo se calcula?</a>
                        </p>

                        <div class="inicio-dashboard__semaforo-bar mt-4">
                            @php
                                $totalRegistrados =
                                    $distribucionGeneral['rojo'] +
                                    $distribucionGeneral['amarillo'] +
                                    $distribucionGeneral['verde'] +
                                    $distribucionGeneral['azul'] +
                                    $distribucionGeneral['sin_datos'];
                                $pctRojo =
                                    $totalRegistrados > 0 ? ($distribucionGeneral['rojo'] / $totalRegistrados) * 100 : 0;
                                $pctAmarillo =
                                    $totalRegistrados > 0
                                        ? ($distribucionGeneral['amarillo'] / $totalRegistrados) * 100
                                        : 0;
                                $pctVerde =
                                    $totalRegistrados > 0 ? ($distribucionGeneral['verde'] / $totalRegistrados) * 100 : 0;
                                $pctAzul =
                                    $totalRegistrados > 0 ? ($distribucionGeneral['azul'] / $totalRegistrados) * 100 : 0;
                                $pctGris =
                                    $totalRegistrados > 0
                                        ? ($distribucionGeneral['sin_datos'] / $totalRegistrados) * 100
                                        : 0;
                            @endphp
                            <div class="progress rounded-pill shadow-sm"
                                style="height: 14px; overflow: hidden; background-color: #e9ecef;">
                                @if ($pctRojo > 0)
                                    <div class="progress-bar"
                                        style="width: {{ $pctRojo }}%; background-color: var(--color-insuficiente);"
                                        title="Rezago: {{ $distribucionGeneral['rojo'] }} ({{ round($pctRojo, 1) }}%)">
                                    </div>
                                @endif
                                @if ($pctAmarillo > 0)
                                    <div class="progress-bar"
                                        style="width: {{ $pctAmarillo }}%; background-color: var(--color-moderado);"
                                        title="En proceso: {{ $distribucionGeneral['amarillo'] }} ({{ round($pctAmarillo, 1) }}%)">
                                    </div>
                                @endif
                                @if ($pctVerde > 0)
                                    <div class="progress-bar"
                                        style="width: {{ $pctVerde }}%; background-color: var(--color-aceptable);"
                                        title="Meta cumplida: {{ $distribucionGeneral['verde'] }} ({{ round($pctVerde, 1) }}%)">
                                    </div>
                                @endif
                                @if ($pctAzul > 0)
                                    <div class="progress-bar"
                                        style="width: {{ $pctAzul }}%; background-color: var(--color-excedido);"
                                        title="Superó meta: {{ $distribucionGeneral['azul'] }} ({{ round($pctAzul, 1) }}%)">
                                    </div>
                                @endif
                                @if ($pctGris > 0)
                                    <div class="progress-bar"
                                        style="width: {{ $pctGris }}%; background-color: var(--color-nulas);"
                                        title="Sin datos: {{ $distribucionGeneral['sin_datos'] }} ({{ round($pctGris, 1) }}%)">
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex justify-content-between flex-wrap gap-2 mt-3 text-muted"
                                style="font-size: 0.78rem;">
                                <div><span class="d-inline-block rounded-circle me-1"
                                        style="width:10px; height:10px; background-color:var(--color-insuficiente);"></span>
                                    Rezago:
                                    <strong>{{ $distribucionGeneral['rojo'] }}</strong>
                                </div>
                                <div><span class="d-inline-block rounded-circle me-1"
                                        style="width:10px; height:10px; background-color:var(--color-moderado);"></span> En
                                    proceso: <strong>{{ $distribucionGeneral['amarillo'] }}</strong></div>
                                <div><span class="d-inline-block rounded-circle me-1"
                                        style="width:10px; height:10px; background-color:var(--color-aceptable);"></span>
                                    Cumplido: <strong>{{ $distribucionGeneral['verde'] }}</strong></div>
                                <div><span class="d-inline-block rounded-circle me-1"
                                        style="width:10px; height:10px; background-color:var(--color-excedido);"></span>
                                    Superado: <strong>{{ $distribucionGeneral['azul'] }}</strong></div>
                                @if ($distribucionGeneral['sin_datos'] > 0)
                                    <div><span class="d-inline-block rounded-circle me-1"
                                            style="width:10px; height:10px; background-color:var(--color-nulas);"></span>
                                        Sin
                                        datos: <strong>{{ $distribucionGeneral['sin_datos'] }}</strong></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
     3. AVANCE POR EJES Y PROGRAMAS
     ============================================================ --}}
    <section class="inicio-cumplimiento" id="cumplimiento">
        <div class="inicio-cumplimiento__container">
            <div class="inicio-cumplimiento__card">

                <div class="inicio-cumplimiento__header">
                    <h3>Cumplimiento por Eje del PED 2024-2030</h3>
                    <span class="inicio-cumplimiento__header-sub">Distribución de indicadores por rango de cumplimiento</span>
                </div>

                <div class="inicio-cumplimiento__body">
                    <div class="inicio-ejes__list">
                        @foreach ($ejesData as $eje)
                            <div class="inicio-ejes__row" id="eje-{{ $eje['numero'] }}"
                                style="border-left-color: var(--color-eje{{ $eje['numero'] }});">
                                <img src="{{ asset('img/iconos/eje-' . $eje['numero'] . '.png') }}"
                                    class="inicio-ejes__icon"
                                    alt="Icono del {{ $eje['nombre'] }}">
                                <div class="inicio-ejes__info">
                                    <div class="inicio-ejes__name">{{ $eje['nombre'] }}</div>
                                    <div class="inicio-ejes__meta">
                                        <span class="inicio-ejes__badge"
                                            style="background-color: var(--color-eje{{ $eje['numero'] }});">
                                            {{ $eje['total_indicadores'] }} indicadores
                                        </span>
                                        <span class="inicio-ejes__avance-text">
                                             Avance promedio: <strong
                                                 style="color: {{ $eje['semaforo_color'] }};">{{ number_format($eje['avance'], 2) }}%</strong>
                                            <span class="ms-2">{{ $eje['indicadores_evaluables'] }}/{{ $eje['total_indicadores'] }} evaluables</span>
                                        </span>
                                    </div>
                                </div>

                                <div class="inicio-ejes__rangos">
                                    <div class="inicio-ejes__rango inicio-ejes__rango--rojo">
                                        <span class="inicio-ejes__rango-count">{{ $eje['distribucion']['rojo'] }}</span>
                                        <span class="inicio-ejes__rango-label">menos de 71%</span>
                                    </div>
                                    <div class="inicio-ejes__rango inicio-ejes__rango--amarillo">
                                        <span
                                            class="inicio-ejes__rango-count">{{ $eje['distribucion']['amarillo'] }}</span>
                                        <span class="inicio-ejes__rango-label">71% a 90%</span>
                                    </div>
                                    <div class="inicio-ejes__rango inicio-ejes__rango--verde">
                                        <span class="inicio-ejes__rango-count">{{ $eje['distribucion']['verde'] }}</span>
                                        <span class="inicio-ejes__rango-label">91% a 109%</span>
                                    </div>
                                    <div class="inicio-ejes__rango inicio-ejes__rango--azul">
                                        <span class="inicio-ejes__rango-count">{{ $eje['distribucion']['azul'] }}</span>
                                        <span class="inicio-ejes__rango-label">110% o más</span>
                                    </div>
                                </div>

                                <a href="{{ url('/ped/eje-' . $eje['numero']) }}" class="inicio-ejes__link">
                                    + información
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if ($programasData->count() > 0)
                    <div class="inicio-cumplimiento__header inicio-cumplimiento__header--programas">
                        <h3>Avance por Programas Derivados</h3>
                        <span class="inicio-cumplimiento__header-sub">Indicadores agrupados por tipo de programa</span>
                    </div>

                    <div class="inicio-cumplimiento__body">
                        @php
                            $programasAgrupados = $programasData->groupBy('tipo');
                             $ordenDeseado = ['Sectoriales', 'Especiales', 'Institucionales'];
                            $programasOrdenados = $programasAgrupados->sortBy(function ($programas, $tipo) use (
                                $ordenDeseado,
                            ) {
                                $posicion = array_search($tipo, $ordenDeseado);
                                return $posicion !== false ? $posicion : 999;
                            });
                        @endphp

                        <div class="row mb-4 justify-content-center">
                            <div class="col-md-8 col-lg-6">
                                <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                                    <span class="input-group-text bg-white border-0 ps-3">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" id="buscador-programas" class="form-control border-0 ps-2"
                                        placeholder="Buscar programa derivado por nombre..." style="box-shadow: none;">
                                </div>
                            </div>
                        </div>

                        <ul class="nav nav-pills" id="programasTab" role="tablist">
                            @foreach ($programasOrdenados as $tipo => $programas)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link @if ($loop->first) active @endif"
                                        id="tab-{{ Illuminate\Support\Str::slug($tipo) }}" data-bs-toggle="pill"
                                        data-bs-target="#pane-{{ Illuminate\Support\Str::slug($tipo) }}" type="button"
                                        role="tab" aria-controls="pane-{{ Illuminate\Support\Str::slug($tipo) }}"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                        {{ $tipo }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content" id="programasTabContent">
                            @foreach ($programasOrdenados as $tipo => $programas)
                                <div class="tab-pane fade @if ($loop->first) show active @endif"
                                    id="pane-{{ Illuminate\Support\Str::slug($tipo) }}" role="tabpanel"
                                    aria-labelledby="tab-{{ Illuminate\Support\Str::slug($tipo) }}">

                                    @if ($tipo === 'Institucionales' && count($gruposInstitucionales) > 0)
                                        <div class="d-flex justify-content-center flex-wrap gap-2 mb-4"
                                            id="grupo-filters">
                                            <button
                                                class="btn btn-danger btn-sm rounded-pill px-3 py-1 group-filter-btn active"
                                                data-group-filter="all">
                                                Todos
                                            </button>
                                            @foreach ($gruposInstitucionales as $grupo)
                                                <button
                                                    class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1 group-filter-btn"
                                                    data-group-filter="{{ Illuminate\Support\Str::slug($grupo) }}">
                                                    {{ $grupo }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="inicio-programas__grid">
                                        @foreach ($programas as $programa)
                                            <a href="{{ url('/ped-programas/' . $programa['tipo_slug'] . '/' . Illuminate\Support\Str::slug($programa['nombre'])) }}"
                                                class="inicio-programa-card"
                                                data-nombre="{{ strtolower($programa['nombre']) }}"
                                                @if ($tipo === 'Institucionales') data-grupo="{{ Illuminate\Support\Str::slug($programa['grupo'] ?? '') }}" @endif
                                                 style="border-left-color: {{ $programa['color'] ?? '#0c312d' }};">
                                                <div class="inicio-programa-card__indicator"
                                                     style="background-color: {{ $programa['color'] ?? '#0c312d' }};">
                                                    <i class="fas {{ $programa['icono'] ?? 'fa-layer-group' }}"
                                                        aria-hidden="true"></i>
                                                    <span class="visually-hidden">Icono de {{ $programa['nombre'] }}</span>
                                                </div>
                                                <div class="inicio-programa-card__body">
                                                    <div class="inicio-programa-card__name">{{ $programa['nombre'] }}
                                                    </div>
                                                    <div class="inicio-programa-card__avance"
                                                        style="color: {{ $programa['semaforo_color'] }};">
                                                         {{ number_format($programa['avance'], 2) }}%
                                                    </div>
                                                    <div class="inicio-programa-card__count">
                                                        {{ $programa['indicadores_evaluables'] }}/{{ $programa['total_indicadores'] }} evaluables
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </section>

    {{-- ============================================================
     5. VISIÓN ESTRATÉGICA
     ============================================================ --}}
     
    <section class="inicio-vision" id="vision-estrategica">
        <div class="inicio-vision__container">
            <p class="inicio-section-subtitle">Plan Estatal de Desarrollo 2024-2030</p>
            <h2 class="inicio-section-title">Visión Estratégica</h2>


            <div class="inicio-vision__grid">
                <div class="inicio-vision__image-wrapper">
                    <img src="{{ asset('img/esquemas/esuqema-about.png') }}" alt="Esquema PED 2024-2030"
                        class="inicio-vision__image">
                </div>
                <div class="inicio-vision__text">
                    <h2>Enfoque</h2>
                    <p>
                        El PED 2024-2030 se distingue por su carácter innovador y por mantener
                        plena observancia de las disposiciones jurídicas. Su rasgo más sobresaliente
                        es la colaboración inédita de los poderes jurisdiccionales en su elaboración.
                        Este hecho marca un punto de inflexión en el estado porque se adopta una
                        gobernanza inclusiva como elemento eficaz de planeación, con lo cual se
                        fortalece la visión integral del desarrollo y se consolida el modelo de gobierno que
                        se habrá de seguir. Este modelo es el del Humanismo Mexicano, planteado en el
                        Plan Nacional de Desarrollo 2025-2030, el cual se verá reflejado en la entidad bajo
                        un enfoque de Bioética Social que se cimienta en tres dimensiones:
                    </p>
                    <ul>
                        <li><strong>a) Seguridad.</strong> Mediante un trabajo coordinado, se garantizarán entornos seguros
                            que permitan tener condiciones de vida dignas y la protección ante adversidades.</li>
                        <li><strong>b) Justicia.</strong> Este término representará equidad en el acceso a la salud,
                            educación, oportunidades laborales y mecanismos eficaces para corregir discrepancias
                            estructurales.</li>
                        <li><strong>c) Riqueza Comunitaria.</strong> Se instrumentará una nueva forma de gobernar, que
                            implicará la priorización de los derechos sociales, reconociendo al ser humano desde su
                            integridad como un agente capaz de fortalecer la solidaridad, la cultura, la participación, el
                            sentido de pertenencia y los saberes ancestrales.</li>
                    </ul>
                    <p>
                        Como se puede observar, este documento trasciende la mera gestión
                        administrativa. Se erige como una guía que nos orientará en la superación de
                        los desafíos que enfrentamos, con el objetivo de reducir las desigualdades
                        sociales, fortalecer la seguridad, impulsar el desarrollo económico, asegurar la
                        sostenibilidad ambiental y consolidar un gobierno eficiente y transparente.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
     6. ESQUEMA INSTITUCIONAL
     ============================================================ --}}
    <section class="inicio-esquema" id="esquema">
        <div class="inicio-esquema__container">
            <p class="inicio-section-subtitle">Sistema Estatal de Planeación Democrática</p>
            <h2 class="inicio-section-title">Esquema Integral de Planeación</h2>

            <div class="inicio-esquema__grid">
                <div class="inicio-esquema__block">
                    <img src="{{ asset('img/Banners/General/sepd.png') }}"
                        alt="Sistema Estatal de Planeación Democrática">
                    <p>
                        El esquema integral define el conjunto de procedimientos y actividades mediante las cuales
                        las instituciones de la Administración Pública Estatal y Municipal, entre sí, y en colaboración con
                        los sectores de la sociedad, toman decisiones para llevar de forma coordinada el proceso de
                        planeación a fin de garantizar el desarrollo integral y sostenible del estado.
                    </p>
                </div>

                <div class="inicio-esquema__block">
                    <img src="{{ asset('img/esquemas/piramide.png') }}"
                        alt="Esquema de pirámide de los programas derivados">
                    <p>
                        Posteriormente, a través de la vinculación con el Sistema de Evaluación del Desempeño, el
                        esquema integral de seguimiento se articula entre toda la APE, la sociedad, las regiones y los
                        municipios.
                    </p>
                </div>

                <div class="inicio-esquema__block">
                    <img src="{{ asset('img/Banners/General/esquema_sed.png') }}"
                        alt="Esquema de Seguimiento del Sistema de Evaluación del Desempeño">
                    <p>
                        De tal forma el SPED automatiza el proceso de seguimiento de las acciones y metas de los
                        instrumentos
                        de planeación, el Informe de Gobierno, los programas presupuestales y los ODS de la Agenda 2030.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
     7. FUENTES DE CONSULTA
     ============================================================ --}}
    <section class="inicio-fuentes" id="fuentes">
        <div class="inicio-fuentes__container">
            <p class="inicio-section-subtitle"></p>
            <h2 class="inicio-section-title">Sitios de Interés</h2>

            <div class="inicio-fuentes__track-wrapper">
                <div class="inicio-fuentes__track" id="fuentesTrack">
                    @php
                        $fuentes = [
                            ['url' => 'https://www.iadb.org/es', 'img' => 'BID.png', 'alt' => 'BID'],
                            [
                                'url' => 'https://www.coneval.org.mx/Paginas/principal.aspx',
                                'img' => 'CONEVAL.png',
                                'alt' => 'CONEVAL',
                            ],
                            ['url' => 'https://imco.org.mx/', 'img' => 'IMCO.png', 'alt' => 'IMCO'],
                            ['url' => 'https://www.inegi.org.mx/', 'img' => 'INEGI.png', 'alt' => 'INEGI'],
                            [
                                'url' => 'https://www.transparenciapresupuestaria.gob.mx/',
                                'img' => 'OBSERVATORIO.png',
                                'alt' => 'Observatorio',
                            ],
                            [
                                'url' => 'https://www1.undp.org/content/undp/es/home.html',
                                'img' => 'PNUD.png',
                                'alt' => 'PNUD',
                            ],
                            ['url' => 'https://www.gob.mx/siap', 'img' => 'SADER.png', 'alt' => 'SADER'],
                            [
                                'url' =>
                                    'https://www.gob.mx/sep/acciones-y-programas/estadistica-educativa-15782?state=published',
                                'img' => 'SEP.png',
                                'alt' => 'SEP',
                            ],
                            [
                                'url' =>
                                    'https://www.gob.mx/sesnsp/acciones-y-programas/informacion-de-incidencia-delictiva-nacional?state=published',
                                'img' => 'SESNSP.png',
                                'alt' => 'SESNSP',
                            ],
                            [
                                'url' => 'http://www.stps.gob.mx/gobmx/estadisticas/',
                                'img' => 'STPS.png',
                                'alt' => 'STPS',
                            ],
                        ];
                    @endphp
                    @foreach ($fuentes as $fuente)
                        <div class="inicio-fuentes__item">
                            <a href="{{ $fuente['url'] }}" target="_blank" rel="noopener noreferrer"
                                title="{{ $fuente['alt'] }}">
                                <img src="{{ asset('img/sitios_interes/' . $fuente['img']) }}"
                                    alt="{{ $fuente['alt'] }}">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

@section('jss-final')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Gauge principal
            var mainGaugeEl = document.getElementById('mainGaugeInicio');
            if (mainGaugeEl) {
                var mainChart = echarts.init(mainGaugeEl);
                var avancePlan = Number("{{ $avancePlan }}");
                var colorPlan = "{{ $colorPlan }}";

                mainChart.setOption({
                    series: [{
                        type: 'gauge',
                        startAngle: 180,
                        endAngle: 0,
                        min: 0,
                        max: 100,
                        progress: {
                            show: true,
                            width: 18,
                            roundCap: true,
                            itemStyle: {
                                color: colorPlan
                            }
                        },
                        axisLine: {
                            lineStyle: {
                                width: 18,
                                color: [
                                    [1, '#e7e7e7']
                                ]
                            }
                        },
                        axisTick: {
                            show: false
                        },
                        splitLine: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        pointer: {
                            show: false
                        },
                        detail: {
                            show: false
                        },
                        data: [{
                            value: avancePlan > 100 ? 100 : avancePlan
                        }]
                    }]
                });

                window.addEventListener('resize', function() {
                    mainChart.resize();
                });
            }

            // 2. Duplicar track de fuentes para scroll infinito
            var fuentesTrack = document.getElementById('fuentesTrack');
            if (fuentesTrack) {
                fuentesTrack.innerHTML += fuentesTrack.innerHTML;
            }

             // 3. Smooth scroll para pills de ejes
             document.querySelectorAll('.inicio-eje-pill').forEach(function(pill) {
                 pill.addEventListener('click', function(e) {
                     var href = this.getAttribute('href');

                     // Las rutas de detalle deben continuar con la navegación normal.
                     if (!href || !href.startsWith('#')) {
                         return;
                     }

                     e.preventDefault();
                     var target = document.querySelector(href);
                     if (target) {
                         target.scrollIntoView({
                             behavior: 'smooth',
                            block: 'center'
                        });
                    }
                });
            });

            // 4. Buscador y Filtros de Grupo para Programas Derivados
            var buscador = document.getElementById('buscador-programas');
            var groupFilterBtns = document.querySelectorAll('.group-filter-btn');
            var programCards = document.querySelectorAll('.inicio-programa-card');

            var searchVal = '';
            var activeGroupVal = 'all';

            function filterPrograms() {
                programCards.forEach(function(card) {
                    var nombre = card.getAttribute('data-nombre') || '';
                    var grupo = card.getAttribute('data-grupo') || '';

                    var matchesSearch = nombre.includes(searchVal);
                    var matchesGroup = (activeGroupVal === 'all' || grupo === activeGroupVal);

                    if (matchesSearch && matchesGroup) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            if (buscador) {
                buscador.addEventListener('input', function(e) {
                    searchVal = e.target.value.toLowerCase().trim();
                    filterPrograms();
                });
            }

            groupFilterBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    groupFilterBtns.forEach(function(b) {
                        b.classList.remove('btn-danger', 'active');
                        b.classList.add('btn-outline-danger');
                    });

                    this.classList.add('btn-danger', 'active');
                    this.classList.remove('btn-outline-danger');

                    activeGroupVal = this.getAttribute('data-group-filter');
                    filterPrograms();
                });
            });

            // Al cambiar de pestaña (Sectoriales, Especiales, etc.) reseteamos filtros
            var tabTriggers = document.querySelectorAll('button[data-bs-toggle="pill"]');
            tabTriggers.forEach(function(tabTrigger) {
                tabTrigger.addEventListener('shown.bs.tab', function(e) {
                    if (buscador) {
                        buscador.value = '';
                    }
                    searchVal = '';
                    activeGroupVal = 'all';

                    groupFilterBtns.forEach(function(b) {
                        if (b.getAttribute('data-group-filter') === 'all') {
                            b.classList.add('btn-danger', 'active');
                            b.classList.remove('btn-outline-danger');
                        } else {
                            b.classList.remove('btn-danger', 'active');
                            b.classList.add('btn-outline-danger');
                        }
                    });

                    filterPrograms();
                });
            });
        });
    </script>
@endsection
@endsection

@extends('layouts.plantilla')

@section('title', 'Plan Estatal de Desarrollo 2024 - 2030')
@section('meta-description', 'Sección del Plan Estatal de Desarrollo 2024 - 2030 dentro del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title', 'Plan Estatal de Desarrollo 2024 - 2030 - Sistema de Información para la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('og-description', 'Consulta el avance de los ejes y programas derivados del Plan Estatal de Desarrollo 2024 - 2030 del Estado de Puebla.')
@section('og:url', url()->current())
@section('twitter-title', 'Plan Estatal de Desarrollo 2024 - 2030')
@section('twitter-description', 'Consulta el avance de los ejes y programas derivados del Plan Estatal de Desarrollo 2024 - 2030.')

@section('css')
@endsection

@section('content')
    @include('partials.nav-unificada', [
        'tipoNav' => 'ped',
        'itemActivo' => null,
        'bannerImg' => 'img/Banners/Banner_PED/PED.jpg',
        'colorTema' => '#9d2449'
    ])

    <main class="ped-dashboard">
        <section class="ped-dashboard__intro">
            <div class="ped-dashboard__container">
                <span class="ped-dashboard__eyebrow">Seguimiento estratégico</span>
                <h1 class="ped-dashboard__title">Plan Estatal de Desarrollo 2024-2030</h1>
                <p class="ped-dashboard__intro-text">
                    Consulta el avance de los ejes de desarrollo y de los programas derivados que articulan la visión
                    estratégica del Estado de Puebla.
                </p>
            </div>
        </section>

        <section class="ped-dashboard__container" aria-labelledby="ped-resumen-title">
            <div class="ped-dashboard__summary">
                <div class="ped-dashboard__summary-brand">
                    <span class="ped-dashboard__summary-label">Avance general del PED</span>
                    <h2 id="ped-resumen-title" class="ped-dashboard__summary-title">
                        Una visión compartida para el desarrollo de Puebla
                    </h2>
                    <a href="https://ped2024-2030.puebla.gob.mx/" target="_blank" rel="noopener noreferrer"
                        class="ped-dashboard__summary-link">
                        Conoce el Plan Estatal <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>

                <div class="ped-dashboard__summary-data">
                    <div class="ped-dashboard__summary-gauge">
                        <div id="mainGauge" class="ped-dashboard__summary-gauge-chart"></div>
                        <div class="ped-dashboard__summary-gauge-value">{{ number_format($avancePlan, 2) }}%</div>
                    </div>
                    <div>
                        <div class="ped-dashboard__summary-number">{{ $totalIndicadores }}</div>
                        <div class="ped-dashboard__summary-caption">Indicadores registrados</div>
                        <p class="ped-dashboard__summary-copy">
                            Cumplimiento promedio de las metas evaluables del Plan Estatal de Desarrollo.
                        </p>
                    </div>
                </div>
            </div>

            <div class="ped-dashboard__tabs" role="tablist" aria-label="Contenido del Plan Estatal de Desarrollo">
                <button class="ped-dashboard__tab active" id="pills-home-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home"
                    aria-selected="true">
                    Ejes de desarrollo
                </button>
                <button class="ped-dashboard__tab" id="pills-profile-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile"
                    aria-selected="false">
                    Programas derivados
                </button>
            </div>

            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                    <div class="ped-dashboard__section-heading">
                        <h2 class="ped-dashboard__section-title">Avance por ejes de desarrollo</h2>
                        <p class="ped-dashboard__section-subtitle">Consulta el desempeño agregado de cada eje estratégico.</p>
                    </div>

                    <div class="ped-dashboard__ejes-grid">
                        @foreach($ejesData as $eje)
                            <article class="ped-dashboard__eje-card"
                                style="--card-accent: {{ $eje['color'] }}; --card-status: {{ $eje['semaforo_color'] }};">
                                <div class="ped-dashboard__card-top">
                                    <span class="ped-dashboard__card-index">{{ $eje['numero'] }}</span>
                                    <a href="{{ url('/ped/eje-' . $eje['numero']) }}" class="ped-dashboard__card-link">
                                        Ver detalle <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                                <h3 class="ped-dashboard__card-title">{{ $eje['nombre'] }}</h3>
                                <div id="gauge-eje-{{ $eje['id'] }}" class="ped-dashboard__card-gauge"></div>
                                <div class="ped-dashboard__card-footer">
                                    <span class="ped-dashboard__card-footer-label">Avance promedio</span>
                                    <strong class="ped-dashboard__card-value">{{ number_format($eje['avance'], 2) }}%</strong>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                    <div class="ped-dashboard__section-heading">
                        <h2 class="ped-dashboard__section-title">Avance por programas derivados</h2>
                        <p class="ped-dashboard__section-subtitle">Explora los programas que instrumentan las prioridades del PED.</p>
                    </div>

                    @php
                        $programasAgrupados = $programasData->groupBy('tipo');
                        $ordenDeseado = ['Sectoriales', 'Especiales', 'Regionales', 'Institucionales'];
                        $programasOrdenados = $programasAgrupados->sortBy(function ($programas, $tipo) use ($ordenDeseado) {
                            $posicion = array_search($tipo, $ordenDeseado);
                            return $posicion !== false ? $posicion : 999;
                        });
                    @endphp

                    @if($programasOrdenados->isNotEmpty())
                        <div class="ped-dashboard__program-search">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="search" id="ped-program-search" class="form-control"
                                    placeholder="Buscar programa derivado por nombre..." autocomplete="off">
                            </div>
                        </div>

                        <ul class="nav nav-pills ped-dashboard__program-tabs" id="pedProgramasTabs" role="tablist">
                            @foreach($programasOrdenados as $tipo => $programas)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="ped-tab-{{ Illuminate\Support\Str::slug($tipo) }}"
                                        data-bs-toggle="pill" data-bs-target="#ped-pane-{{ Illuminate\Support\Str::slug($tipo) }}"
                                        type="button" role="tab" aria-controls="ped-pane-{{ Illuminate\Support\Str::slug($tipo) }}"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                        {{ $tipo }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content" id="pedProgramasTabContent">
                            @foreach($programasOrdenados as $tipo => $programas)
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                    id="ped-pane-{{ Illuminate\Support\Str::slug($tipo) }}" role="tabpanel"
                                    aria-labelledby="ped-tab-{{ Illuminate\Support\Str::slug($tipo) }}">
                                    <div class="ped-dashboard__programas-grid">
                                        @foreach($programas as $programa)
                                            <article class="ped-dashboard__programa-card ped-program-card"
                                                data-nombre="{{ strtolower($programa['nombre']) }}"
                                                style="--card-accent: {{ $programa['color'] }}; --card-status: {{ $programa['semaforo_color'] }};">
                                                <div class="ped-dashboard__card-top">
                                                    <span class="ped-dashboard__card-index">{{ $programa['id'] }}</span>
                                                    <a href="{{ url('/ped-programas/' . $programa['tipo_slug'] . '/' . Illuminate\Support\Str::slug($programa['nombre'])) }}"
                                                        class="ped-dashboard__card-link">
                                                        Ver detalle <i class="fas fa-arrow-right ms-1"></i>
                                                    </a>
                                                </div>
                                                <h3 class="ped-dashboard__card-title">{{ $programa['nombre'] }}</h3>
                                                <div id="gauge-prog-{{ $programa['tipo_slug'] }}-{{ $programa['id'] }}" class="ped-dashboard__card-gauge"></div>
                                                <div class="ped-dashboard__card-footer">
                                                    <span class="ped-dashboard__card-footer-label">Avance promedio</span>
                                                    <strong class="ped-dashboard__card-value">{{ number_format($programa['avance'], 2) }}%</strong>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                    <p class="ped-dashboard__empty ped-dashboard__empty--search d-none">No hay programas que coincidan con la búsqueda.</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="ped-dashboard__empty">
                            <i class="fas fa-info-circle me-2"></i>No hay programas derivados disponibles.
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </main>
@endsection

@section('jss-final')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const createdCharts = [];

            function createGauge(domId, value, color) {
                const element = document.getElementById(domId);
                if (!element) return null;

                const chart = echarts.init(element);
                chart.setOption({
                    series: [{
                        type: 'gauge',
                        startAngle: 180,
                        endAngle: 0,
                        min: 0,
                        max: 100,
                        progress: {
                            show: true,
                            width: 15,
                            roundCap: true,
                            itemStyle: { color: color }
                        },
                        axisLine: {
                            lineStyle: { width: 15, color: [[1, '#e7e7e7']] }
                        },
                        axisTick: { show: false },
                        splitLine: { show: false },
                        axisLabel: { show: false },
                        pointer: { show: false },
                        detail: { show: false },
                        data: [{ value: Math.min(Number(value) || 0, 100) }]
                    }]
                });
                chart.resize();
                createdCharts.push(chart);
                return chart;
            }

            createGauge('mainGauge', @json($avancePlan), @json($colorPlan));

            @foreach($ejesData as $eje)
                createGauge('gauge-eje-{{ $eje['id'] }}', @json($eje['avance']), @json($eje['semaforo_color']));
            @endforeach

            @foreach($programasData as $programa)
                createGauge('gauge-prog-{{ $programa['tipo_slug'] }}-{{ $programa['id'] }}', @json($programa['avance']), @json($programa['semaforo_color']));
            @endforeach

            document.querySelectorAll('[data-bs-toggle="pill"]').forEach(function(tab) {
                tab.addEventListener('shown.bs.tab', function() {
                    createdCharts.forEach(function(chart) {
                        chart.resize();
                    });
                });
            });

            const programSearch = document.getElementById('ped-program-search');

            if (programSearch) {
                programSearch.addEventListener('input', function() {
                    const searchValue = this.value.toLowerCase().trim();

                    document.querySelectorAll('#pedProgramasTabContent .tab-pane').forEach(function(pane) {
                        let visibleCards = 0;

                        pane.querySelectorAll('.ped-program-card').forEach(function(card) {
                            const matches = (card.dataset.nombre || '').includes(searchValue);
                            card.style.display = matches ? '' : 'none';
                            if (matches) visibleCards++;
                        });

                        const emptyMessage = pane.querySelector('.ped-dashboard__empty--search');
                        if (emptyMessage) {
                            emptyMessage.classList.toggle('d-none', visibleCards > 0);
                        }
                    });
                });
            }

            window.addEventListener('resize', function() {
                createdCharts.forEach(function(chart) {
                    chart.resize();
                });
            });
        });
    </script>
@endsection

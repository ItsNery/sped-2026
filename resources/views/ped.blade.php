@extends('layouts.plantilla')

@section('title', 'Plan Estatal de Desarrollo')
@section('meta-description', 'Consulta el avance de los ejes y programas derivados del Plan Estatal de Desarrollo.')
@section('canonical-url', url()->current())

@section('content')
    @include('partials.nav-unificada', [
        'tipoNav' => 'ped',
        'itemActivo' => null,
        'bannerImg' => 'img/Banners/Banner_PED/PED.jpg',
        'colorTema' => '#9d2449',
    ])

    <main class="ped-dashboard">
        <section class="ped-dashboard__intro">
            <div class="ped-dashboard__container">
                <span class="ped-dashboard__eyebrow">Seguimiento estrategico</span>
                <h1 class="ped-dashboard__title">{{ $plan->nombre }}</h1>
                <p class="ped-dashboard__intro-text">
                    Consulta el avance de los ejes de desarrollo y de los programas derivados del plan.
                </p>
                <a class="ped-dashboard__search-link" href="#customSearchModal" onclick="openSearchModal(event);">
                    <i class="fas fa-search me-2" aria-hidden="true"></i>Buscar un indicador
                </a>
            </div>
        </section>

        <section class="ped-dashboard__container" aria-labelledby="ped-resumen-title">
            <div class="ped-dashboard__summary">
                <div class="ped-dashboard__summary-brand">
                    <span class="ped-dashboard__summary-label">Avance general del PED</span>
                    <h2 id="ped-resumen-title" class="ped-dashboard__summary-title">Seguimiento de resultados</h2>
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
                        <div class="ped-dashboard__summary-number">{{ $metricasPlan['total_registrados'] }}</div>
                        <div class="ped-dashboard__summary-caption">Indicadores registrados</div>
                        <p class="ped-dashboard__summary-copy">
                            {{ $metricasPlan['total_evaluables'] }} evaluables ·
                            {{ number_format($metricasPlan['cobertura_evaluacion'], 2) }}% de cobertura.
                        </p>
                    </div>
                </div>
            </div>

            <section class="ped-dashboard__composition" aria-labelledby="ped-composition-title">
                <div class="ped-dashboard__section-heading ped-dashboard__section-heading--compact">
                    <div>
                        <h2 id="ped-composition-title" class="ped-dashboard__section-title">Estructura del seguimiento</h2>
                        <p class="ped-dashboard__section-subtitle">Indicadores que integran el plan seleccionado.</p>
                    </div>
                    <span class="ped-dashboard__composition-total">{{ $composicionPlan['total'] }} indicadores</span>
                </div>
                <div class="ped-dashboard__composition-grid">
                    @foreach ($composicionPlan['por_tipo'] as $tipo => $total)
                        <div class="ped-dashboard__composition-item">
                            <strong>{{ $total }}</strong>
                            <span>{{ $tipo }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="ped-dashboard__composition-footer">
                    <span><i class="fas fa-check-circle me-1"></i>{{ $composicionPlan['validados'] }} validados</span>
                    <span><i class="fas fa-clock me-1"></i>{{ $composicionPlan['pendientes'] }} pendientes</span>
                    <span><i class="fas fa-building me-1"></i>{{ $composicionPlan['instituciones'] }} instituciones responsables</span>
                </div>
            </section>

            <section class="ped-dashboard__methodology" id="metodologia" aria-labelledby="ped-methodology-title">
                <div class="ped-dashboard__section-heading ped-dashboard__section-heading--compact">
                    <div>
                        <h2 id="ped-methodology-title" class="ped-dashboard__section-title">Cómo leer este seguimiento</h2>
                        <p class="ped-dashboard__section-subtitle">Criterios para interpretar correctamente las cifras del tablero.</p>
                    </div>
                    <span class="ped-dashboard__methodology-tag"><i class="fas fa-info-circle me-1"></i>Metodología</span>
                </div>

                <div class="ped-dashboard__methodology-grid">
                    <article class="ped-dashboard__methodology-item">
                        <i class="fas fa-chart-line"></i>
                        <div>
                            <h3>Avance promedio</h3>
                            <p>Es el promedio simple del cumplimiento de los indicadores que tienen datos validados y condiciones suficientes para compararse contra su meta.</p>
                        </div>
                    </article>
                    <article class="ped-dashboard__methodology-item">
                        <i class="fas fa-filter"></i>
                        <div>
                            <h3>Indicadores evaluables</h3>
                            <p>Los indicadores sin datos validados, sin meta o con información insuficiente se reportan por separado y no alteran el promedio.</p>
                        </div>
                    </article>
                    <article class="ped-dashboard__methodology-item">
                        <div>
                            <h3>Semáforo de cumplimiento</h3>
                            <div class="ped-dashboard__semaforo-table-wrapper">
                                <table class="ped-dashboard__semaforo-table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Rango</th>
                                            <th scope="col">Resultado</th>
                                            <th scope="col">Interpretación</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>X &ge; 110%</td>
                                            <td><span class="ped-dashboard__semaforo-result ped-dashboard__semaforo-result--excedido">Excedido</span></td>
                                            <td>El valor logrado del indicador supera a la meta, lo que puede interpretarse como una falla en el proceso de planeación o influencia de factores externos.</td>
                                        </tr>
                                        <tr>
                                            <td>91% &le; X &lt; 110%</td>
                                            <td><span class="ped-dashboard__semaforo-result ped-dashboard__semaforo-result--aceptable">Aceptable</span></td>
                                            <td>El valor logrado del indicador se encuentra entre -9% y +10% por debajo o por encima de la meta; es decir, da cumplimiento a la meta del PED.</td>
                                        </tr>
                                        <tr>
                                            <td>71% &le; X &lt; 91%</td>
                                            <td><span class="ped-dashboard__semaforo-result ped-dashboard__semaforo-result--moderado">Moderado</span></td>
                                            <td>El valor logrado del indicador es menor que la meta; representa un avance significativo, pero deficiente para alcanzar la meta.</td>
                                        </tr>
                                        <tr>
                                            <td>X &lt; 71%</td>
                                            <td><span class="ped-dashboard__semaforo-result ped-dashboard__semaforo-result--insuficiente">Insuficiente</span></td>
                                            <td>El valor alcanzado del indicador está muy por debajo de la meta y representa un incumplimiento, por lo que se sugiere revisar y analizar las estrategias propuestas para alcanzar el objetivo.</td>
                                        </tr>
                                        <tr>
                                            <td>NE</td>
                                            <td><span class="ped-dashboard__semaforo-result ped-dashboard__semaforo-result--no-evaluable">No Evaluable</span></td>
                                            <td>No es posible determinar el nivel de avance del indicador en el periodo de seguimiento debido a que no se cuenta con información actualizada, comparable y validada respecto de la meta.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="ped-dashboard__semaforo-note">
                                <strong>Nota:</strong> Los resultados superiores a 110% no deben interpretarse automáticamente como un desempeño favorable, dado que pueden reflejar una meta subestimada, una modificación en la programación, una variación extraordinaria o un cambio en el método de cálculo; por ello, deben contar con una justificación técnica.
                            </p>
                        </div>
                    </article>
                </div>

                <p class="ped-dashboard__methodology-note">
                    La vista pública utiliza información validada. Los porcentajes reflejan el último dato disponible de cada indicador y pueden corresponder a distintos años de referencia.
                </p>
                <a href="{{ asset('docs/normatividad/nota-metodologica-semaforizacion.pdf') }}"
                    class="ped-dashboard__methodology-link" target="_blank" rel="noopener noreferrer">
                    <i class="fas fa-file-pdf me-1"></i>Consultar la nota metodológica completa
                </a>
            </section>

            <div class="ped-dashboard__tabs" role="tablist" aria-label="Contenido del Plan Estatal de Desarrollo">
                <button class="ped-dashboard__tab active" id="ped-ejes-tab" data-bs-toggle="pill"
                    data-bs-target="#ped-ejes" type="button" role="tab" aria-controls="ped-ejes" aria-selected="true">
                    Ejes de desarrollo
                </button>
                <button class="ped-dashboard__tab" id="ped-programas-tab" data-bs-toggle="pill"
                    data-bs-target="#ped-programas" type="button" role="tab" aria-controls="ped-programas" aria-selected="false">
                    Programas derivados
                </button>
            </div>

            <div class="tab-content" id="ped-dashboard-content">
                <div class="tab-pane fade show active" id="ped-ejes" role="tabpanel" aria-labelledby="ped-ejes-tab">
                    <div class="ped-dashboard__section-heading">
                        <h2 class="ped-dashboard__section-title">Avance por ejes de desarrollo</h2>
                        <p class="ped-dashboard__section-subtitle">Consulta el desempeño agregado de cada eje estratégico.</p>
                    </div>
                    <div class="ped-dashboard__ejes-grid">
                        @foreach ($ejesData as $eje)
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
                                    <span class="ped-dashboard__card-footer-label">
                                        Avance promedio · {{ $eje['indicadores_evaluables'] }}/{{ $eje['total_indicadores'] }} evaluables
                                    </span>
                                    <strong class="ped-dashboard__card-value">{{ number_format($eje['avance'], 2) }}%</strong>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="tab-pane fade" id="ped-programas" role="tabpanel" aria-labelledby="ped-programas-tab">
                    <div class="ped-dashboard__section-heading">
                        <h2 class="ped-dashboard__section-title">Avance por programas derivados</h2>
                        <p class="ped-dashboard__section-subtitle">Explora los programas que instrumentan las prioridades del PED.</p>
                    </div>
                    @php
                        $programasAgrupados = $programasData->groupBy('tipo');
                        $ordenDeseado = ['Sectoriales', 'Especiales', 'Institucionales'];
                        $programasOrdenados = $programasAgrupados->sortBy(function ($programas, $tipo) use ($ordenDeseado) {
                            $posicion = array_search($tipo, $ordenDeseado);
                            return $posicion !== false ? $posicion : 999;
                        });
                    @endphp
                    @if ($programasData->isNotEmpty())
                        <div class="ped-dashboard__program-search">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="search" id="ped-program-search" class="form-control"
                                    placeholder="Buscar programa derivado por nombre..." autocomplete="off">
                            </div>
                        </div>

                        <ul class="nav nav-pills ped-dashboard__program-tabs" id="pedProgramasTabs" role="tablist">
                            @foreach ($programasOrdenados as $tipo => $programas)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link @if ($loop->first) active @endif"
                                        id="ped-tab-{{ Illuminate\Support\Str::slug($tipo) }}"
                                        data-bs-toggle="pill"
                                        data-bs-target="#ped-pane-{{ Illuminate\Support\Str::slug($tipo) }}"
                                        type="button" role="tab"
                                        aria-controls="ped-pane-{{ Illuminate\Support\Str::slug($tipo) }}"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                        {{ $tipo }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content" id="pedProgramasTabContent">
                            @foreach ($programasOrdenados as $tipo => $programas)
                                <div class="tab-pane fade @if ($loop->first) show active @endif ped-program-pane"
                                    id="ped-pane-{{ Illuminate\Support\Str::slug($tipo) }}" role="tabpanel"
                                    aria-labelledby="ped-tab-{{ Illuminate\Support\Str::slug($tipo) }}">
                                    @if ($tipo === 'Institucionales' && $gruposInstitucionales->isNotEmpty())
                                        <div class="d-flex justify-content-center flex-wrap gap-2 mb-4"
                                            id="ped-grupo-filters">
                                            <button class="btn btn-danger btn-sm rounded-pill px-3 py-1 ped-group-filter-btn active"
                                                data-group-filter="all" type="button">
                                                Todos
                                            </button>
                                            @foreach ($gruposInstitucionales as $grupo)
                                                <button class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1 ped-group-filter-btn"
                                                    data-group-filter="{{ Illuminate\Support\Str::slug($grupo) }}" type="button">
                                                    {{ $grupo }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="ped-dashboard__programas-grid">
                                        @foreach ($programas as $programa)
                                            <article class="ped-dashboard__programa-card ped-program-card"
                                                data-nombre="{{ strtolower($programa['nombre']) }}"
                                                @if ($tipo === 'Institucionales') data-grupo="{{ Illuminate\Support\Str::slug($programa['grupo'] ?? '') }}" @endif
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
                                                    <span class="ped-dashboard__card-footer-label">
                                                        Avance promedio · {{ $programa['indicadores_evaluables'] }}/{{ $programa['total_indicadores'] }} evaluables
                                                    </span>
                                                    <strong class="ped-dashboard__card-value">{{ number_format($programa['avance'], 2) }}%</strong>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                    <p class="ped-dashboard__empty ped-program-empty d-none">No hay programas que coincidan con la búsqueda.</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="ped-dashboard__empty">No hay programas derivados disponibles.</div>
                    @endif
                </div>
            </div>
        </section>
    </main>
@endsection

@section('jss-final')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const charts = [];

            function createGauge(id, value, color) {
                const element = document.getElementById(id);
                if (!element || typeof echarts === 'undefined') return;
                const chart = echarts.init(element);
                chart.setOption({
                    series: [{
                        type: 'gauge', startAngle: 180, endAngle: 0, min: 0, max: 100,
                        progress: { show: true, width: 15, roundCap: true, itemStyle: { color } },
                        axisLine: { lineStyle: { width: 15, color: [[1, '#e7e7e7']] } },
                        axisTick: { show: false }, splitLine: { show: false }, axisLabel: { show: false },
                        pointer: { show: false }, detail: { show: false },
                        data: [{ value: Math.min(Number(value) || 0, 100) }]
                    }]
                });
                charts.push(chart);
            }

            createGauge('mainGauge', @json($avancePlan), @json($colorPlan));
            @foreach ($ejesData as $eje)
                createGauge('gauge-eje-{{ $eje['id'] }}', @json($eje['avance']), @json($eje['semaforo_color']));
            @endforeach
            @foreach ($programasData as $programa)
                createGauge('gauge-prog-{{ $programa['tipo_slug'] }}-{{ $programa['id'] }}', @json($programa['avance']), @json($programa['semaforo_color']));
            @endforeach

            const search = document.getElementById('ped-program-search');
            const groupFilterButtons = document.querySelectorAll('.ped-group-filter-btn');
            let activeGroup = 'all';

            function filterPrograms() {
                const term = search ? search.value.toLowerCase().trim() : '';

                document.querySelectorAll('.ped-program-pane').forEach(function (pane) {
                    let visible = 0;
                    pane.querySelectorAll('.ped-program-card').forEach(function (card) {
                        const matchesSearch = (card.dataset.nombre || '').includes(term);
                        const matchesGroup = activeGroup === 'all' || (card.dataset.grupo || '') === activeGroup;
                        const isVisible = matchesSearch && matchesGroup;

                        card.style.display = isVisible ? '' : 'none';
                        if (isVisible) visible++;
                    });

                    pane.querySelector('.ped-program-empty')?.classList.toggle('d-none', visible > 0);
                });
            }

            search?.addEventListener('input', filterPrograms);
            groupFilterButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    groupFilterButtons.forEach(function (filterButton) {
                        const isActive = filterButton === button;
                        filterButton.classList.toggle('btn-danger', isActive);
                        filterButton.classList.toggle('active', isActive);
                        filterButton.classList.toggle('btn-outline-danger', !isActive);
                    });
                    activeGroup = button.dataset.groupFilter || 'all';
                    filterPrograms();
                });
            });

            document.querySelectorAll('#pedProgramasTabs [data-bs-toggle="pill"]').forEach(function (tab) {
                tab.addEventListener('shown.bs.tab', function () {
                    activeGroup = 'all';
                    groupFilterButtons.forEach(function (button) {
                        const isAll = button.dataset.groupFilter === 'all';
                        button.classList.toggle('btn-danger', isAll);
                        button.classList.toggle('active', isAll);
                        button.classList.toggle('btn-outline-danger', !isAll);
                    });
                    filterPrograms();
                    charts.forEach(chart => chart.resize());
                });
            });

            window.addEventListener('resize', function () {
                charts.forEach(chart => chart.resize());
            });
        });
    </script>
@endsection

<x-app-layout>
    @section('title', 'Administración: Inicio')
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('Inicio') }}
        </h2>
    </x-slot>


    @auth
    @if (auth()->user()->hasRole('Administrador'))
    <div class="contenedor-principal">
        <div class="dashboard-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="fw-bold mb-0 text-dark">Dashboard</h1>
                    <p class="text-muted">Estado de los Indicadores del Plan Estatal de Desarrollo 2024-2030</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-white text-dark shadow-sm p-2 px-3">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        {{ \Carbon\Carbon::now()->isoFormat('D [de] MMMM, YYYY') }}
                    </span>
                </div>
            </div>

            <!-- Zona 1: Signos Vitales (KPIs Principales) -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <a href="{{ route('admin.avance-general') }}" class="text-decoration-none h-100">
                        <div class="card card-modern h-100 p-3" style="border-top: 5px solid {{ $colorAvanceGlobal }}">
                            <div class="card-body text-center">
                                <span class="kpi-title">Avance promedio evaluable</span>
                                <div class="kpi-value" style="color: {{ $colorAvanceGlobal }}">
                                    {{ $avanceGlobalPromedio }}%
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 4px;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ $avanceGlobalPromedio }}%; background-color: {{ $colorAvanceGlobal }};"
                                        aria-valuenow="{{ $avanceGlobalPromedio }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    {{ $metricasGlobal['total_evaluables'] }} de {{ $metricasGlobal['total_registrados'] }} evaluables
                                    ({{ number_format($metricasGlobal['cobertura_evaluacion'], 2) }}% de cobertura)
                                </small>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <div class="card card-modern h-100 p-3" style="border-top: 5px solid #198754">
                        <div class="card-body text-center">
                            <span class="kpi-title">Indicadores Validados</span>
                            <div class="kpi-value text-success">
                                {{ $totalIndicadoresValidados }}
                            </div>
                            <span class="badge bg-success-subtle text-success p-2">
                                {{ round($porcentajeValidado, 1) }}% del Total
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-modern h-100 p-3" style="border-top: 5px solid #dc3545">
                        <div class="card-body text-center">
                            <span class="kpi-title">Indicadores sin Datos Anuales</span>
                            <div class="kpi-value text-danger">
                                {{ $totalIndicadoresIncompletos }}
                            </div>
                            <span class="badge bg-danger-subtle text-danger p-2">
                                {{ round($porcentajeIncompletos, 1) }}% en Rezago
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zona 2: Salud del Plan y Metodología -->
            <div class="row g-4 mb-5">
                <div class="col-lg-6">
                    <div class="card card-modern p-4 h-100">
                        <h3 class="zone-title">Semaforización de Avances</h3>
                        <span class="text-muted mb-3">
                            Gráfico de la distribución de la semaforización de los indicadores.
                        </span>
                        <div id="chart-semaforizacion" role="img" aria-label="Distribución de indicadores por semáforo"
                            style="height: 350px; width: 100%;"></div>
                        <p class="visually-hidden">La gráfica muestra la distribución de indicadores por estado de avance.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card card-modern p-4 h-100">
                        <h3 class="zone-title">Distribución por Tendencia</h3>
                        <span class="text-muted mb-3">
                            Gráfico de la distribución de la tendencia de los indicadores.
                        </span>
                        <div id="chart-tendencia" role="img" aria-label="Distribución de indicadores por tendencia"
                            style="height: 350px; width: 100%;"></div>
                        <p class="visually-hidden">La gráfica muestra la distribución de indicadores según su tendencia.</p>
                    </div>
                </div>
            </div>

            <!-- Zona 3: Inteligencia de Riesgos (Focos Rojos e Instituciones Críticas) -->
            <div class="row g-4 mb-5">
                <div class="col-lg-6">
                    <div class="card card-modern p-4 h-100">
                        <h3 class="zone-title">Focos rojos: Menor avance</h3>
                        <span class="text-muted mb-3">
                            Indicadores con menor avance en el semáforo de indicadores.
                        </span>
                        @if($focosRojos->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                            <p class="text-muted">No hay indicadores en estado insuficiente con datos.</p>
                        </div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Indicador</th>
                                        <th>Avance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($focosRojos as $ind)
                                    <tr>
                                        <td>
                                            <a href="{{ route('panel-indicadores.show', $ind->id) }}" class="text-decoration-none">
                                                <div class="fw-bold text-truncate" style="max-width: 300px;" title="{{ $ind->nombre }}" title="{{ $ind->nombre }}">
                                                    {{ $ind->nombre }}
                                                </div>
                                            </a>
                                            <small class="text-muted">{{ $ind->institucion->nombre ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">{{ $ind->avance }}%</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card card-modern p-4 h-100">
                        <h3 class="zone-title">Instituciones con rezago</h3>
                        <span class="text-muted mb-3">
                            Instituciones con indicadores en estado insuficiente o caducados.
                        </span>
                        @if($institucionesCriticas->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-thumbs-up text-primary fa-3x mb-3"></i>
                            <p class="text-muted">Todas las instituciones están al día.</p>
                        </div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Institución</th>
                                        <th>Estado Crítico</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($institucionesCriticas as $inst)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $inst->nombre }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-danger mb-1">{{ $inst->conteo_insuficientes }} Insuficientes</span>
                                                <span class="badge bg-secondary">{{ $inst->conteo_caducados }} Caducados</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Zona 4: Operación y Seguimiento -->
            <div class="card card-modern p-4 mb-5">
                <h3 class="zone-title">Operación y Seguimiento Anual</h3>
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-enlace-tab" data-bs-toggle="pill" data-bs-target="#pills-enlace" type="button" role="tab">Por Enlace</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-anual-tab" data-bs-toggle="pill" data-bs-target="#pills-anual" type="button" role="tab">Desempeño Anual</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-periodo-tab" data-bs-toggle="pill" data-bs-target="#pills-periodo" type="button" role="tab">Periodicidad</button>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-enlace" role="tabpanel">
                        <span class="text-muted mb-3">
                            Gráfico del estado de validación de los indicadores por enlace.
                        </span>
                        <div id="chart-avance-enlace" style="height: 400px; width: 100%;"></div>
                    </div>
                    <div class="tab-pane fade" id="pills-anual" role="tabpanel">
                        <span class="text-muted mb-3">
                            Gráfico de la cantidad de datos anuales de los indicadores.
                        </span>
                        <div id="chart-avance-anual" style="height: 400px; width: 100%;"></div>
                    </div>
                    <div class="tab-pane fade" id="pills-periodo" role="tabpanel">
                        <span class="text-muted mb-3">
                            Gráfico de la periodicidad de los indicadores.
                        </span>
                        <div id="chart-avance-periodo" style="height: 400px; width: 100%;"></div>
                    </div>
                </div>
            </div>

            <!-- Sección de Notificaciones y Alertas Rápidas -->
            <div class="row g-4 mb-5">
                <!-- Ya Caducados -->
                <div class="col-md-4">
                    <div class="card card-modern">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger-subtle p-3 rounded-circle me-3">
                                        <i class="fas fa-clock text-danger fa-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="h6 mb-0">Ya caducados</h4>
                                        <span class="text-muted mb-3">
                                            Indicadores cuya fecha de actualización registrada ya pasó.
                                        </span>
                                        <p class="h4 mb-0 fw-bold">{{ $indicadoresCaducados->count() }}</p>
                                    </div>
                                </div>
                                <button class="btn btn-light btn-toggle-alert border" type="button" data-bs-toggle="collapse" data-bs-target="#listCaducados">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="collapse mt-3" id="listCaducados">
                                <div class="alert-scroll-container pe-2">
                                    @foreach($indicadoresCaducados as $ind)
                                    <div class="alert-item-compact shadow-sm">
                                        <a href="{{ route('panel-indicadores.show', $ind->id) }}" class="text-decoration-none text-dark">
                                            <div class="fw-bold">{{ Str::limit($ind->nombre, 50) }}</div>
                                            <small class="text-muted d-block">{{ $ind->institucion->nombre ?? 'N/A' }}</small>
                                            <small class="text-muted d-block">{{ $ind->fecha_actualizacion ?? 'N/D' }}</small>
                                        </a>
                                    </div>
                                    @endforeach
                                    @if($indicadoresCaducados->isEmpty())
                                    <div class="text-center py-3 text-muted">No hay indicadores caducados</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Próximos a Caducar -->
                <div class="col-md-4">
                    <div class="card card-modern">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning-subtle p-3 rounded-circle me-3">
                                        <i class="fas fa-hourglass-half text-warning fa-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="h6 mb-0">Próximos a caducar</h4>
                                        <span class="text-muted mb-3">
                                            Indicadores cuya fecha de actualización está próxima a vencer.
                                        </span>
                                        <p class="h4 mb-0 fw-bold">{{ $indicadoresProximos->count() }}</p>
                                    </div>
                                </div>
                                <button class="btn btn-light btn-toggle-alert border" type="button" data-bs-toggle="collapse" data-bs-target="#listProximos">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="collapse mt-3" id="listProximos">
                                <div class="alert-scroll-container pe-2">
                                    @foreach($indicadoresProximos as $ind)
                                    <div class="alert-item-compact shadow-sm">
                                        <a href="{{ route('panel-indicadores.show', $ind->id) }}" class="text-decoration-none text-dark">
                                            <div class="fw-bold">{{ Str::limit($ind->nombre, 50) }}</div>
                                            <small class="text-muted d-block">{{ $ind->institucion->nombre ?? 'N/A' }}</small>
                                        </a>
                                    </div>
                                    @endforeach
                                    @if($indicadoresProximos->isEmpty())
                                    <div class="text-center py-3 text-muted">No hay indicadores próximos a caducar</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pendientes a Tiempo -->
                <div class="col-md-4">
                    <div class="card card-modern">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success-subtle p-3 rounded-circle me-3">
                                        <i class="fas fa-calendar-check text-success fa-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="h6 mb-0">Pendientes a tiempo</h4>
                                        <span class="text-muted mb-3">
                                            Indicadores cuya fecha de actualización es hoy.
                                        </span>
                                        <p class="h4 mb-0 fw-bold">{{ $indicadoresATiempo->count() }}</p>
                                    </div>
                                </div>
                                <button class="btn btn-light btn-toggle-alert border" type="button" data-bs-toggle="collapse" data-bs-target="#listATiempo">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="collapse mt-3" id="listATiempo">
                                <div class="alert-scroll-container pe-2">
                                    @foreach($indicadoresATiempo as $ind)
                                    <div class="alert-item-compact shadow-sm">
                                        <a href="{{ route('panel-indicadores.show', $ind->id) }}" class="text-decoration-none text-dark">
                                            <div class="fw-bold">{{ Str::limit($ind->nombre, 50) }}</div>
                                            <small class="text-muted d-block">{{ $ind->institucion->nombre ?? 'N/A' }}</small>
                                        </a>
                                    </div>
                                    @endforeach
                                    @if($indicadoresATiempo->isEmpty())
                                    <div class="text-center py-3 text-muted">No hay indicadores pendientes a tiempo</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Programas Derivados (Agrupados en Tabs) -->
            <div class="card card-modern p-4 mb-4">
                <h3 class="zone-title">Programas derivados</h3>
                <span class="text-muted mb-3">
                    Avance de los indicadores por programa derivado.
                </span>
                <ul class="nav nav-pills mb-4" id="programs-tab" role="tablist">
                    @foreach($programasData as $tipoSlug => $grupo)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                            id="tab-{{ $tipoSlug }}"
                            data-bs-toggle="pill"
                            data-bs-target="#content-{{ $tipoSlug }}"
                            type="button" role="tab">
                            {{ $grupo['tipo'] }}
                        </button>
                    </li>
                    @endforeach
                </ul>

                <div class="tab-content" id="programs-tabContent">
                    @foreach($programasData as $tipoSlug => $grupo)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                        id="content-{{ $tipoSlug }}" role="tabpanel">
                        <div class="row g-3">
                            @foreach($grupo['programas'] as $prog)
                            <div class="col-md-6 col-lg-3">
                                <div class="card h-100 border-0 shadow-sm p-3 bg-light transition-hover">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="fw-bold text-wrap" style="max-width: 150px;" title="{{ $prog['nombre'] }}">{{ $prog['nombre'] }}</small>
                                        <span class="badge" style="background-color: {{ $prog['semaforo_color'] }}">{{ $prog['avance'] }}%</span>
                                    </div>
                                    <div class="progress" style="height: 6px; border-radius: 10px;">
                                        <div class="progress-bar" style="width: {{ $prog['avance'] }}%; background-color: {{ $prog['semaforo_color'] }}"></div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <!-- Footer Lists: Cambios Recientes y Top Instituciones -->
            <div class="row g-4 mb-5">
                <div class="col-lg-8">
                    <div class="card card-modern p-4 h-100">
                        <h3 class="zone-title">Actividad reciente</h3>
                        <span class="text-muted mb-3">
                            Indicadores que han sido actualizados recientemente.
                        </span>
                        <div class="scroll-container-modern d-flex flex-column gap-2" style="max-height: 400px; overflow-y: auto;">
                            @foreach ($indicadoresRecientes as $indicador)
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded shadow-sm">
                                <a href="{{ route('panel-indicadores.show', $indicador['id']) }}" class="text-dark fw-medium text-decoration-none">
                                    {{ Str::limit($indicador['nombre'], 70) }}
                                </a>
                                <div class="text-end">
                                    <span class="badge {{ $indicador['tipo'] == 'Nuevo' ? 'bg-success' : 'bg-primary' }} mb-1 d-block">{{ $indicador['tipo'] }}</span>
                                    <small class="text-muted d-block">{{ $indicador['updated_at'] }}</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-modern p-4 h-100">
                        <h3 class="zone-title">Top desempeño</h3>
                        <span class="text-muted mb-3">
                            Instituciones con el mayor número de indicadores validados.
                        </span>
                        <div class="list-group list-group-flush">

                            @foreach ($instituciones as $index => $institucion)
                            <div class="list-group-item bg-transparent d-flex align-items-center px-0 border-bottom">
                                <div class="bg-primary text-white p-2 rounded-circle me-3 flex-shrink-0 d-flex align-items-center justify-content-center" style="width:30px; height:30px; font-size: 0.8rem;">
                                    #{{ $index + 1 }}
                                </div>
                                <div class="flex-grow-1 text-truncate">
                                    <div class="fw-bold small">{{ $institucion->nombre }}</div>
                                    <div class="progress mt-1" style="height: 4px;">
                                        @php
                                        $total = $totalIndicadores > 0 ? $totalIndicadores : 1;
                                        $perc = ($institucion->indicadores_validados_count / $total) * 100;
                                        @endphp
                                        <div class="progress-bar bg-success" style="width: {{ $perc }}%"></div>
                                    </div>
                                </div>
                                @if($index === 0) <span class="ms-2">🏆</span> @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endauth

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
        <script>
            // --- GRÁFICO DE SEMAFORIZACIÓN (DONA) ---
            var semaforizacionLabels = @json(array_keys($semaforizacionCounts));
            var semaforizacionValues = @json(array_values($semaforizacionCounts));
            var semaforizacionColors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#adb5bd'];
            var semaforizacionData = semaforizacionLabels.map(function(label, i) {
                return { value: semaforizacionValues[i], name: label, itemStyle: { color: semaforizacionColors[i % semaforizacionColors.length] } };
            });
            var chartSemaforizacion = echarts.init(document.getElementById('chart-semaforizacion'));
            chartSemaforizacion.setOption({
                tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
                legend: {
                    bottom: '0',
                    left: 'center'
                },
                series: [{
                    type: 'pie',
                    radius: ['35%', '60%'],
                    avoidLabelOverlap: true,
                    label: {
                        show: true,
                        position: 'outside',
                        formatter: '{b}: {d}%'
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: '14',
                            fontWeight: 'bold'
                        }
                    },
                    data: semaforizacionData
                }]
            });
            chartSemaforizacion.on('click', function(params) {
                window.location.href = "{{ route('indicadores.semaforizacion', ['categoria' => ':cat']) }}".replace(':cat', params.name);
            });

            // --- GRÁFICO DE TENDENCIA (BARRAS HORIZONTALES) ---
            var tendenciaLabels = @json(array_keys($tendenciaCounts));
            var tendenciaValues = @json(array_values($tendenciaCounts));
            var tendenciaColors = ['#4f46e5', '#34d399', '#f59e0b', '#94a3b8'];
            var chartTendencia = echarts.init(document.getElementById('chart-tendencia'));
            chartTendencia.setOption({
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                grid: { left: '3%', right: '8%', bottom: '3%', containLabel: true },
                xAxis: { type: 'value' },
                yAxis: { type: 'category', data: tendenciaLabels },
                series: [{
                    type: 'bar',
                    data: tendenciaValues.map(function(v, i) {
                        return { value: v, itemStyle: { color: tendenciaColors[i % tendenciaColors.length] } };
                    }),
                    label: {
                        show: true,
                        position: 'right',
                        formatter: function(params) {
                            var total = tendenciaValues.reduce(function(a, b) { return a + b; }, 0);
                            var pct = total > 0 ? ((params.value / total) * 100).toFixed(1) + '%' : '0%';
                            return params.value + ' (' + pct + ')';
                        }
                    }
                }]
            });

            // --- GRÁFICOS DE OPERACIÓN (TABS) ---
            var dataGraficas = @json($datosGraficas);

            // Avance por Enlace
            var chartAvanceEnlace = echarts.init(document.getElementById('chart-avance-enlace'));
            chartAvanceEnlace.setOption({
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                legend: { data: ['Validados', 'No Validados'], top: 0 },
                xAxis: {
                    type: 'category',
                    data: dataGraficas.map(function(d) { return d.nombre; }),
                    axisLabel: { rotate: 45, fontSize: 10 }
                },
                yAxis: { type: 'value' },
                series: [
                    {
                        name: 'Validados',
                        type: 'bar',
                        stack: 'total',
                        data: dataGraficas.map(function(d) { return d.validados; }),
                        itemStyle: { color: '#22c55e' },
                        label: {
                            show: true,
                            position: 'inside',
                            formatter: function(params) {
                                return params.value > 0 ? params.value : '';
                            }
                        }
                    },
                    {
                        name: 'No Validados',
                        type: 'bar',
                        stack: 'total',
                        data: dataGraficas.map(function(d) { return d.no_validados; }),
                        itemStyle: { color: '#e2e8f0' },
                        label: {
                            show: true,
                            position: 'inside',
                            formatter: function(params) {
                                return params.value > 0 ? params.value : '';
                            }
                        }
                    }
                ]
            });
            chartAvanceEnlace.on('click', function(params) {
                var idUsuario = dataGraficas[params.dataIndex].id_usuario;
                var filtro = params.seriesName === 'Validados' ? 'validados' : 'no-validados';
                var url = "{{ route('usuarios.indicadores', ['id' => ':id']) }}".replace(':id', idUsuario);
                window.location.href = url + "?filtro=" + filtro;
            });

            // Desempeño Anual
            var chartAvanceAnual = echarts.init(document.getElementById('chart-avance-anual'));
            chartAvanceAnual.setOption({
                tooltip: { trigger: 'axis' },
                legend: {
                    data: ['Avance Anual'],
                    bottom: '0'
                },
                xAxis: { type: 'category', data: @json($years) },
                yAxis: { type: 'value' },
                series: [{
                    name: 'Avance Anual',
                    type: 'bar',
                    data: @json($datosPorAnio),
                    itemStyle: { color: '#6366f1', borderRadius: [4, 4, 0, 0] },
                    barWidth: '50%',
                    label: {
                        show: true,
                        position: 'top',
                        formatter: function(params) {
                            return params.value > 0 ? params.value : '';
                        }
                    }
                }]
            });

            // Periodicidad
            var periodoLabels = @json($etiquetas_periodicidades);
            var periodoValues = @json($values_periodicidades);
            var periodoColors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#94a3b8'];
            var chartAvancePeriodo = echarts.init(document.getElementById('chart-avance-periodo'));
            chartAvancePeriodo.setOption({
                tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
                legend: {
                    bottom: '0',
                    left: 'center'
                },
                series: [{
                    type: 'pie',
                    radius: ['35%', '60%'],
                    avoidLabelOverlap: true,
                    label: {
                        show: true,
                        position: 'outside',
                        formatter: '{b}: {d}%'
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: '14',
                            fontWeight: 'bold'
                        }
                    },
                    data: periodoLabels.map(function(label, i) {
                        return { value: periodoValues[i], name: label, itemStyle: { color: periodoColors[i % periodoColors.length] } };
                    })
                }]
            });

            // Redimensionar las gráficas cuando se cambia de pestaña para que se estiren al 100% de ancho
            document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(function(tabEl) {
                tabEl.addEventListener('shown.bs.tab', function() {
                    if (typeof chartAvanceEnlace !== 'undefined') chartAvanceEnlace.resize();
                    if (typeof chartAvanceAnual !== 'undefined') chartAvanceAnual.resize();
                    if (typeof chartAvancePeriodo !== 'undefined') chartAvancePeriodo.resize();
                });
            });
        </script>
        @endpush
</x-app-layout>

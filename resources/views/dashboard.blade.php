<x-app-layout>
    @section('title', 'Administración: Inicio')
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('Inicio') }}
        </h2>
    </x-slot>
    {{--
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script> --}}
    <script src="{{ asset('js/apexcharts.js') }}"></script>

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
                                <span class="kpi-title">Avance Global Promedio</span>
                                <div class="kpi-value" style="color: {{ $colorAvanceGlobal }}">
                                    {{ $avanceGlobalPromedio }}%
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 4px;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ $avanceGlobalPromedio }}%; background-color: {{ $colorAvanceGlobal }};"
                                        aria-valuenow="{{ $avanceGlobalPromedio }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
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
                        <div id="chart-semaforizacion"></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card card-modern p-4 h-100">
                        <h3 class="zone-title">Distribución por Tendencia</h3>
                        <span class="text-muted mb-3">
                            Gráfico de la distribución de la tendencia de los indicadores.
                        </span>
                        <div id="chart-tendencia"></div>
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
                        <div id="chart-avance-enlace"></div>
                    </div>
                    <div class="tab-pane fade" id="pills-anual" role="tabpanel">
                        <span class="text-muted mb-3">
                            Gráfico de la cantidad de datos anuales de los indicadores.
                        </span>
                        <div id="chart-avance-anual"></div>
                    </div>
                    <div class="tab-pane fade" id="pills-periodo" role="tabpanel">
                        <span class="text-muted mb-3">
                            Gráfico de la periodicidad de los indicadores.
                        </span>
                        <div id="chart-avance-periodo"></div>
                    </div>
                </div>
            </div>

            <!-- Sección de Notificaciones y Alertas Rápidas -->
            <div class="row g-4 mb-5">
                <!-- Ya Caducados -->
                <div class="col-md-4">
                    <div class="card card-modern border-start border-danger border-4">
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
                                    <div class="alert-item-compact shadow-sm" style="border-left-color: #dc3545;">
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
                    <div class="card card-modern border-start border-warning border-4">
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
                                    <div class="alert-item-compact shadow-sm" style="border-left-color: #ffc107;">
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
                    <div class="card card-modern border-start border-success border-4">
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
                                    <div class="alert-item-compact shadow-sm" style="border-left-color: #198754;">
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
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded shadow-sm border-start border-primary border-4">
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

        <script>
            // --- GRÁFICO DE SEMAFORIZACIÓN (DONA) ---
            var optionsSemaforizacion = {
                series: @json(array_values($semaforizacionCounts)),
                labels: @json(array_keys($semaforizacionCounts)),
                chart: {
                    type: 'donut',
                    height: 380,
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            var categoria = config.w.config.labels[config.dataPointIndex];
                            window.location.href = "{{ route('indicadores.semaforizacion', ['categoria' => ':cat']) }}".replace(':cat', categoria);
                        }
                    }
                },
                colors: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#adb5bd'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Indicadores',
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    position: 'bottom'
                }
            };
            new ApexCharts(document.querySelector("#chart-semaforizacion"), optionsSemaforizacion).render();

            // --- GRÁFICO DE TENDENCIA (BARRAS HORIZONTALES) ---
            var optionsTendencia = {
                series: [{
                    name: 'Indicadores',
                    data: @json(array_values($tendenciaCounts))
                }],
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 8,
                        barHeight: '60%',
                        distributed: true
                    }
                },
                colors: ['#4f46e5', '#34d399', '#f59e0b', '#94a3b8'],
                xaxis: {
                    categories: @json(array_keys($tendenciaCounts)),
                },
                legend: {
                    show: false
                },
                dataLabels: {
                    enabled: true
                }
            };
            new ApexCharts(document.querySelector("#chart-tendencia"), optionsTendencia).render();

            // --- GRÁFICOS DE OPERACIÓN (TABS) ---

            // Avance por Enlace
            var dataGraficas = @json($datosGraficas);
            var optionsEnlace = {
                series: [{
                    name: 'Validados',
                    data: dataGraficas.map(d => d.validados)
                }, {
                    name: 'No Validados',
                    data: dataGraficas.map(d => d.no_validados)
                }],
                chart: {
                    type: 'bar',
                    height: 400,
                    stacked: true,
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            var idUsuario = dataGraficas[config.dataPointIndex].id_usuario;
                            var isValidados = config.seriesIndex === 0;
                            var filtro = isValidados ? 'validados' : 'no-validados';
                            var url = "{{ route('usuarios.indicadores', ['id' => ':id']) }}".replace(':id', idUsuario);
                            window.location.href = url + "?filtro=" + filtro;
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4
                    }
                },
                xaxis: {
                    categories: dataGraficas.map(d => d.nombre),
                    labels: {
                        rotate: -45,
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                colors: ['#22c55e', '#e2e8f0'],
                legend: {
                    position: 'top'
                }
            };
            new ApexCharts(document.querySelector("#chart-avance-enlace"), optionsEnlace).render();

            // Desempeño Anual
            var optionsAnual = {
                series: [{
                    name: 'Indicadores con Datos',
                    data: @json($datosPorAnio)
                }],
                chart: {
                    type: 'bar',
                    height: 400,
                    toolbar: {
                        show: false
                    }
                },
                xaxis: {
                    categories: @json($years)
                },
                colors: ['#6366f1'],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '50%'
                    }
                }
            };
            new ApexCharts(document.querySelector("#chart-avance-anual"), optionsAnual).render();

            // Periodicidad
            var optionsPeriodo = {
                series: @json($values_periodicidades),
                labels: @json($etiquetas_periodicidades),
                chart: {
                    type: 'donut',
                    height: 400
                },
                colors: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#94a3b8'],
                stroke: {
                    show: false
                },
                legend: {
                    position: 'bottom'
                }
            };
            new ApexCharts(document.querySelector("#chart-avance-periodo"), optionsPeriodo).render();
        </script>
</x-app-layout>
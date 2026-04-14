<x-app-layout>
    @section('title', 'Avance General del PED')
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold">
                {{ __('Avance General: ' . ($plan->nombre ?? 'Plan Estatal')) }}
            </h2>
        </div>
    </x-slot>

    <script src="{{ asset('js/apexcharts.js') }}"></script>

    <div class="dashboard-container container">
        <!-- AVANCE GENERAL -->
        <div class="row mb-5 justify-content-center">
            <div class="col-md-8">
                <div class="card main-card shadow">
                    <div class="card-body text-center py-5">
                        <h3 class="fw-bold mb-4">Avance General</h3>
                        <div class="gauge-container">
                            <div id="mainGauge"></div>
                            <div class="gauge-value" style="color: {{ $colorPlan }}">{{ number_format($avancePlan, 2) }}%</div>
                        </div>
                        <p class="text-muted mt-3">Promedio general ponderado de todos los indicadores del Plan Estatal.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- AVANCE POR EJE -->
        <h3 class="section-title">Ejes</h3>
        <div class="row g-4 mb-5">
            @foreach($ejesData as $eje)
            <div class="col-md-4">
                <div class="eje-card">
                    <div class="eje-number" style="background-color: {{ $eje['semaforo_color'] ?? '#666' }}">
                        {{ $eje['numero'] }}
                    </div>
                    <h5 class="fw-bold mb-3">{{ $eje['nombre'] }}</h5>
                    <div id="gauge-eje-{{ $eje['id'] }}" style="height: 180px;"></div>
                    <div class="text-center mt-2">
                        <span class="h4 fw-bold" style="color: {{ $eje['semaforo_color'] ?? '#333' }}">{{ number_format($eje['avance'], 2) }}%</span>
                    </div>
                    <div class="mt-3 text-center">
                        <small class="text-muted">{{ $eje['total_indicadores'] }} indicadores</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- AVANCE POR PROGRAMAS -->
        <h3 class="section-title">Programas Derivados</h3>
        
        <ul class="nav nav-tabs mb-4" id="programasTabs" role="tablist">
            @foreach($programasDerivadosAgrupados as $tipo => $programas)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}" 
                    id="tab-{{ Str::slug($tipo) }}" 
                    data-bs-toggle="tab" 
                    data-bs-target="#content-{{ Str::slug($tipo) }}" 
                    type="button" role="tab" 
                    aria-controls="content-{{ Str::slug($tipo) }}" 
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    {{ $tipo }}
                </button>
            </li>
            @endforeach
        </ul>

        <div class="tab-content" id="programasTabsContent">
            @foreach($programasDerivadosAgrupados as $tipo => $programas)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                id="content-{{ Str::slug($tipo) }}" 
                role="tabpanel" 
                aria-labelledby="tab-{{ Str::slug($tipo) }}">
                
                <div class="row g-3">
                    @foreach($programas as $prog)
                    <div class="col-md-4 col-lg-3">
                        <div class="card h-100 shadow-sm border-0 program-card">
                            <div class="card-body p-3 text-center">
                                <h6 class="fw-bold mb-3 text-truncate-2" title="{{ $prog['nombre'] }}" style="min-height: 2.5rem;">
                                    {{ $prog['nombre'] }}
                                </h6>
                                <div id="gauge-prog-{{ $prog['id'] }}-{{ $loop->parent->index }}" style="height: 150px;"></div>
                                <div class="mt-2">
                                    <span class="h5 fw-bold" style="color: {{ $prog['semaforo_color'] }}">{{ number_format($prog['avance'], 1) }}%</span>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">{{ $prog['total_indicadores'] }} indicadores</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const getGaugeOptions = (value, color, size = '100%') => {
                return {
                    series: [value > 100 ? 100 : value],
                    chart: {
                        type: 'radialBar',
                        offsetY: -20,
                        sparkline: {
                            enabled: true
                        }
                    },
                    plotOptions: {
                        radialBar: {
                            startAngle: -90,
                            endAngle: 90,
                            track: {
                                background: "#e7e7e7",
                                strokeWidth: '97%',
                                margin: 5,
                            },
                            dataLabels: {
                                name: {
                                    show: false
                                },
                                value: {
                                    offsetY: -2,
                                    fontSize: '22px',
                                    show: false
                                }
                            }
                        }
                    },
                    grid: {
                        padding: {
                            top: -10
                        }
                    },
                    fill: {
                        colors: [color],
                        type: 'solid',
                    },
                    labels: ['Avance'],
                };
            };

            // Gauge Principal
            var chartValAvancePlan = Number("{{ $avancePlan }}");
            const mainOptions = getGaugeOptions(chartValAvancePlan, "{{ $colorPlan }}");
            mainOptions.chart.height = 350;
            new ApexCharts(document.querySelector("#mainGauge"), mainOptions).render();

            @foreach($ejesData as $eje)
                (function() {
                    let color = "{{ $eje['semaforo_color'] ?? '#333' }}";
                    let avanceVal = Number("{{ $eje['avance'] }}");
                    let opts = getGaugeOptions(avanceVal, color);
                    opts.chart.height = 220;
                    new ApexCharts(document.querySelector("#gauge-eje-{{ $eje['id'] }}"), opts).render();
                })();
            @endforeach

            @foreach($programasDerivadosAgrupados as $tipo => $programas)
                @foreach($programas as $prog)
                (function() {
                    let color = "{{ $prog['semaforo_color'] }}";
                    let avanceVal = Number("{{ $prog['avance'] }}");
                    let opts = getGaugeOptions(avanceVal, color);
                    opts.chart.height = 180;
                    new ApexCharts(document.querySelector("#gauge-prog-{{ $prog['id'] }}-{{ $loop->parent->index }}"), opts).render();
                })();
                @endforeach
            @endforeach
        });
    </script>
</x-app-layout>
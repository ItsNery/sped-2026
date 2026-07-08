<x-app-layout>
    @section('title', 'Avance General del PED')
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold">
                {{ __('Avance General: ' . ($plan->nombre ?? 'Plan Estatal')) }}
            </h2>
        </div>
    </x-slot>


    <div class="dashboard-container container">
        <!-- AVANCE GENERAL -->
        <div class="row mb-5 justify-content-center">
            <div class="col-md-8">
                <div class="card main-card shadow">
                    <div class="card-body text-center py-5">
                        <h3 class="fw-bold mb-4">Avance General</h3>
                        <div class="gauge-container">
                            <div id="mainGauge" style="height: 100%; width: 100%;"></div>
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

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
    <script>
            var createdCharts = [];
            document.addEventListener('DOMContentLoaded', function() {
                function createGauge(domId, value, color) {
                var chart = echarts.init(document.getElementById(domId));
                chart.setOption({
                    series: [{
                        type: 'gauge',
                        startAngle: 180, endAngle: 0,
                        min: 0, max: 100,
                        progress: { show: true, width: 15, roundCap: true, itemStyle: { color: color } },
                        axisLine: { lineStyle: { width: 15, color: [[1, '#e7e7e7']] } },
                        axisTick: { show: false }, splitLine: { show: false },
                        axisLabel: { show: false }, pointer: { show: false },
                        detail: { show: false },
                        data: [{ value: value > 100 ? 100 : value }]
                    }]
                });
                chart.resize();
                createdCharts.push(chart);
                return chart;
            }

            // Gauge Principal
            var chartValAvancePlan = Number("{{ $avancePlan }}");
            createGauge("mainGauge", chartValAvancePlan, "{{ $colorPlan }}");

            @foreach($ejesData as $eje)
                (function() {
                    let color = "{{ $eje['semaforo_color'] ?? '#333' }}";
                    let avanceVal = Number("{{ $eje['avance'] }}");
                    createGauge("gauge-eje-{{ $eje['id'] }}", avanceVal, color);
                })();
            @endforeach

            @foreach($programasDerivadosAgrupados as $tipo => $programas)
                @foreach($programas as $prog)
                (function() {
                    let color = "{{ $prog['semaforo_color'] }}";
                    let avanceVal = Number("{{ $prog['avance'] }}");
                    createGauge("gauge-prog-{{ $prog['id'] }}-{{ $loop->parent->index }}", avanceVal, color);
                })();
                @endforeach
            @endforeach

            // Redimensionar todas las gráficas cuando se cambie de pestaña para evitar que queden estrechas
            document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function(tabEl) {
                tabEl.addEventListener('shown.bs.tab', function() {
                    createdCharts.forEach(function(chart) {
                        chart.resize();
                    });
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
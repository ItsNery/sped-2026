<x-app-layout>
    @section('title', 'Avance General del PED')
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold">
                {{ __('Avance General: ' . ($plan->nombre ?? 'Plan Estatal')) }}
            </h2>
            <div class="d-flex gap-2">
                <form action="{{ route('admin.avance-general') }}" method="GET" id="filterForm">
                    <div class="form-check form-switch bg-white px-3 py-1 rounded shadow-sm border">
                        <input class="form-check-input" type="checkbox" id="soloValidadosSwitch" name="solo_validados" value="1" {{ $soloValidados ? 'checked' : '' }} onchange="document.getElementById('filterForm').submit()">
                        <label class="form-check-label fw-bold text-dark" for="soloValidadosSwitch">
                            Solo datos validados
                        </label>
                    </div>
                </form>
            </div>
        </div>
    </x-slot>

    <script src="{{ asset('js/apexcharts.js') }}"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-excedido: #3E8CEE;
            --color-aceptable: #43B383;
            --color-moderado: #F5E35B;
            --color-insuficiente: #B94149;
            --color-nulas: #D3D3D3;
        }

        .dashboard-container {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
            padding: 20px 0;
        }

        .main-card {
            border-radius: 15px;
            border: none;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .gauge-container {
            position: relative;
            height: 250px;
        }

        .gauge-value {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
        }

        .eje-card {
            border-radius: 12px;
            border: none;
            background: white;
            padding: 20px;
            height: 100%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .eje-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .eje-number {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            margin-bottom: 15px;
        }

        .programas-table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .progress-sm {
            height: 8px;
            border-radius: 4px;
        }

        .section-title {
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 4px;
            background: #9d2449;
            /* Color guinda institucional */
            border-radius: 2px;
        }
    </style>

    <div class="dashboard-container container">
        <!-- AVANCE GENERAL -->
        <div class="row mb-5 justify-content-center">
            <div class="col-md-8">
                <div class="card main-card shadow">
                    <div class="card-body text-center py-5">
                        <h3 class="fw-bold mb-4">Avance General del Estado</h3>
                        <div class="gauge-container">
                            <div id="mainGauge"></div>
                            <div class="gauge-value">{{ number_format($avancePlan, 1) }}%</div>
                        </div>
                        <p class="text-muted mt-3">Promedio general ponderado de todos los indicadores del Plan Estatal.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- AVANCE POR EJE -->
        <h3 class="section-title">Ejes Rectores</h3>
        <div class="row g-4 mb-5">
            @foreach($ejesData as $eje)
            <div class="col-md-4">
                <div class="eje-card">
                    <div class="eje-number" style="background-color: {{ $eje['color'] ?? '#666' }}">
                        {{ $eje['numero'] }}
                    </div>
                    <h5 class="fw-bold mb-3">{{ $eje['nombre'] }}</h5>
                    <div id="gauge-eje-{{ $eje['id'] }}" style="height: 180px;"></div>
                    <div class="text-center mt-2">
                        <span class="h4 fw-bold" style="color: {{ $eje['color'] ?? '#333' }}">{{ number_format($eje['avance'], 1) }}%</span>
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
        <div class="programas-table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre del Programa</th>
                            <th>Tipo</th>
                            <th style="width: 250px;">Avance</th>
                            <th class="text-center">Indicadores</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($programasData as $prog)
                        <tr>
                            <td class="fw-semibold">{{ $prog['nombre'] }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $prog['tipo'] }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1 progress-sm">
                                        @php
                                        $barColor = '#dc3545';
                                        if($prog['avance'] >= 90) $barColor = '#43B383';
                                        elseif($prog['avance'] >= 70) $barColor = '#3E8CEE';
                                        elseif($prog['avance'] >= 50) $barColor = '#F5E35B';
                                        @endphp
                                        <div class="progress-bar" role="progressbar"
                                            style="width: {{ min(100, $prog['avance']) }}%; background-color: {{ $barColor }};"
                                            aria-valuenow="{{ $prog['avance'] }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <span class="fw-bold" style="min-width: 45px;">{{ number_format($prog['avance'], 1) }}%</span>
                                </div>
                            </td>
                            <td class="text-center">{{ $prog['total_indicadores'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuración base para tacómetros
            const getGaugeOptions = (value, color, size = '100%') => {
                return {
                    series: [value > 100 ? 100 : value], // ApexCharts radialBar handles up to 100
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
                                    show: false // We show it manually for more control
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
                        type: 'gradient',
                        gradient: {
                            shade: 'light',
                            shadeIntensity: 0.4,
                            inverseColors: false,
                            opacityFrom: 1,
                            opacityTo: 1,
                            stops: [0, 50, 53, 91],
                            colorStops: [{
                                    offset: 0,
                                    color: color,
                                    opacity: 1
                                },
                                {
                                    offset: 100,
                                    color: color,
                                    opacity: 1
                                }
                            ]
                        },
                    },
                    labels: ['Avance'],
                };
            };

            // Gauge Principal
            var chartValAvancePlan = Number("{{ $avancePlan }}");
            const mainOptions = getGaugeOptions(chartValAvancePlan, '#9d2449');
            mainOptions.chart.height = 350;
            new ApexCharts(document.querySelector("#mainGauge"), mainOptions).render();

            // Gauges de Ejes
            @foreach($ejesData as $eje)
                (function() {
                    let color = "{{ $eje['color'] ?? '#333' }}";
                    let avanceVal = Number("{{ $eje['avance'] }}");
                    let opts = getGaugeOptions(avanceVal, color);
                    opts.chart.height = 220;
                    new ApexCharts(document.querySelector("#gauge-eje-{{ $eje['id'] }}"), opts).render();
                })();
            @endforeach
        });
    </script>
</x-app-layout>
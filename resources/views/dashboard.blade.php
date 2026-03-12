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
            <div class="contenedor-principal container my-1">
                <div class="encabezado-lista">
                    <h2>Resumen de Indicadores</h2>
                </div>
                <a href="{{ route('admin.avance-general') }}">
                    <div class="row g-4">
                        <!-- Total de Indicadores Validados e Incompletos -->
                        <div class="col-md-6">
                            <div class="card text-center shadow-sm mb-3">
                                <div class="card-body">
                                    <h3 class="card-title text-success fw-bold">{{ $totalIndicadoresValidados }}</h3>
                                    <p class="card-text">Indicadores validados</p>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                            style="width: {{ $porcentajeValidado }}%;" aria-valuenow="{{ $porcentajeValidado }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ number_format($porcentajeValidado, 2) }}% del total</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card text-center shadow-sm">
                                <div class="card-body">
                                    <h3 class="card-title text-danger fw-bold">{{ $totalIndicadoresIncompletos }}</h3>
                                    <p class="card-text">Indicadores incompletos</p>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-danger" role="progressbar"
                                            style="width: {{ $porcentajeIncompletos }}%;"
                                            aria-valuenow="{{ $porcentajeIncompletos }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ number_format($porcentajeIncompletos, 2) }}% del total</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                <div class="row g-4">
                    @if (count($indicadoresRecientes) > 0)
                        <!-- Indicadores Recientes -->
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">🆕 Indicadores Recientes</h5>
                                    <div class="scroll-container" id="indicadoresRecientes">
                                        @foreach ($indicadoresRecientes as $indicador)
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <a href="{{ route('panel-indicadores.show', $indicador['id']) }}"><span
                                                        class="fw-bold">{{ $indicador['nombre'] }}</span></a>
                                                <small
                                                    class="badge 
                                                                                {{ $indicador['tipo'] == 'Nuevo' ? 'bg-success' : 'bg-warning' }} text-black">
                                                    {{ $indicador['tipo'] }}
                                                </small>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <span class="text-muted">No hay datos</span>
                    @endif


                    <!-- Instituciones sin Indicadores Validados -->
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">⚠️ Instituciones sin Indicadores Validados</h5>
                                <div class="scroll-container" id="institucionesSinIndicadores">
                                    @if ($institucionesSinIndicadores->isEmpty())
                                        <p class="text-muted">✅ Todas las instituciones tienen al menos un indicador válido.
                                        </p>
                                    @else
                                        @foreach ($institucionesSinIndicadores as $institucion)
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold">{{ $institucion->nombre }}</span>
                                                <small class="badge bg-danger">Sin indicadores completos</small>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <!-- Top Instituciones -->
                    <div class="col-md-12">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title">Top Instituciones</h3>
                                <p class="card-text">Las instituciones con más indicadores validados.</p>
                                <div class="list-group">
                                    @foreach ($instituciones as $index => $institucion)
                                        <div class="list-group-item d-flex align-items-center">
                                            <div class="me-3 d-flex align-items-center justify-content-center bg-primary text-white fw-bold rounded-circle"
                                                style="width: 40px; height: 40px;">
                                                {{ $index + 1 }}
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1"> {{ $institucion->nombre }} </h5>
                                                <p class="text-muted mb-1">
                                                    {{ $institucion->indicadores_validados_count }}
                                                    indicadores validados
                                                </p>
                                                <div class="progress" style="height: 10px;">
                                                    @php
                                                        $totalIndicadores = $institucion->total_indicadores ?? 1;
                                                        $progreso =
                                                            ($institucion->indicadores_validados_count / $totalIndicadores) * 100;
                                                    @endphp
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ $progreso }}%;" aria-valuenow="{{ $progreso }}"
                                                        aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </div>
                                            @if ($index === 0)
                                                <span class="ms-3 text-warning" style="font-size: 1.5rem;">🏆</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <!-- Indicadores Caducados -->
                    <div class="col-md-4">
                        <div class="card border-danger mb-3">
                            <div class="card-header bg-danger text-white">Indicadores Caducados
                                <span class="badge text-bg-light mx-3">
                                    {{ count($indicadoresCaducados) }}
                                </span>
                            </div>
                            <div class="card-body">
                                @if ($indicadoresCaducados->isEmpty())
                                    <p class="text-muted">No hay indicadores caducados.</p>
                                @else
                                    <input type="text" class="form-control mb-2 search-input" placeholder="Buscar indicador..."
                                        data-target="indicadoresCaducados">
                                    <ul class="list-group indicadoresCaducados" style="max-height: 350px; overflow-y: scroll;">
                                        @foreach ($indicadoresCaducados as $indicador)
                                            <li class="list-group-item">
                                                <a href="{{ route('panel-indicadores.show', $indicador->id) }}">
                                                    <strong>{{ $indicador->nombre }}</strong>
                                                </a>
                                                <span class="badge bg-danger">Expiró:
                                                    {{ $indicador->fecha_actualizacion }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Indicadores Próximos a Actualizar -->
                    <div class="col-md-4">
                        <div class="card border-warning mb-3">
                            <div class="card-header bg-warning text-black">Próximos a Actualizar
                                <span class="badge text-bg-light mx-3">
                                    {{ count($indicadoresProximos) }}
                                </span>
                            </div>
                            <div class="card-body">
                                @if ($indicadoresProximos->isEmpty())
                                    <p class="text-muted">🎯 No hay indicadores próximos a actualizar.</p>
                                @else
                                    <input type="text" class="form-control mb-2 search-input" placeholder="Buscar indicador..."
                                        data-target="indicadoresProximos">
                                    <ul class="list-group indicadoresProximos" style="max-height: 350px; overflow-y: scroll;">
                                        @foreach ($indicadoresProximos as $indicador)
                                            <li class="list-group-item">
                                                <a href="{{ route('panel-indicadores.show', $indicador->id) }}">
                                                    <strong>{{ $indicador->nombre }}</strong>
                                                </a>
                                                <span class="badge bg-warning text-black">Actualiza:
                                                    {{ $indicador->fecha_actualizacion }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Indicadores a Tiempo -->
                    <div class="col-md-4">
                        <div class="card border-success mb-3">
                            <div class="card-header bg-success text-white">Indicadores a Tiempo
                                <span class="badge text-bg-light mx-3">
                                    {{ count($indicadoresATiempo) }}
                                </span>
                            </div>
                            <div class="card-body">
                                @if ($indicadoresATiempo->isEmpty())
                                    <p class="text-muted">Hoy no se tiene que actualizar ningún indicador.</p>
                                @else
                                    <input type="text" class="form-control mb-2 search-input" placeholder="Buscar indicador..."
                                        data-target="indicadoresATiempo">
                                    <ul class="list-group indicadoresATiempo" style="max-height: 350px; overflow-y: scroll;">
                                        @foreach ($indicadoresATiempo as $indicador)
                                            <li class="list-group-item">
                                                <a href="{{ route('panel-indicadores.show', $indicador->id) }}">
                                                    <strong>{{ $indicador->nombre }}</strong>
                                                </a>
                                                <span class="badge bg-success">Hoy:
                                                    {{ $indicador->fecha_actualizacion }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row g-4">
                    <!-- Gráfico de Indicadores por Año -->
                    <div class="col-md-6">
                        <div id="chart-indicadores-anio"></div>
                    </div>

                    <!-- Gráfico de Periodicidad -->
                    <div class="col-md-6">
                        <div id="chart-periodicidad"></div>
                    </div>
                    <div class="col-md-12">
                        <div id="semaforizacionChart"></div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="encabezado-lista">
                        <h2>Enlaces</h2>
                    </div>
                    @if ($datosGraficas)
                        <div class="row g-4">
                            @foreach ($datosGraficas as $data)
                                <div class="col-md-4">
                                    <h5 class="text-center">{{ $data['nombre'] }}</h5>
                                    <div id="chart-{{ Str::slug($data['nombre']) }}" class="chart-container"
                                        data-url="{{ route('usuarios.indicadores', ['id' => $data['id_usuario']]) }}">
                                    </div>
                                    <script>
                                        document.addEventListener("DOMContentLoaded", function () {
                                            var chartOptions = {
                                                series: [
                                                    {{$data['validados']}},
                                                    {{$data['no_validados']}}
                                                ],
                                            chart: {
                                            type: 'pie',
                                            height: 300,
                                            events: {
                                                dataPointSelection: function (event, chartContext, config) {
                                                    let chartDiv = document.querySelector(
                                                        "#chart-{{ Str::slug($data['nombre']) }}");
                                                    let baseUrl = chartDiv.getAttribute('data-url');
                                                    let filtro = config.dataPointIndex === 0 ? 'validados' : 'no-validados';
                                                    window.open(baseUrl + '?filtro=' + filtro, '_blank');
                                                }
                                            },
                                            toolbar: {
                                                show: true,
                                                tools: {
                                                    download: true,
                                                    selection: false,
                                                    zoom: false,
                                                    zoomin: false,
                                                    zoomout: false,
                                                    pan: false,
                                                    reset: false
                                                }
                                            },
                                        },
                                            labels: ['Validados', 'No Validados'],
                                            colors: ['#28a745', '#dc3545'],
                                            responsive: [{
                                                breakpoint: 480,
                                                options: {
                                                    chart: {
                                                        width: 200
                                                    }
                                                }
                                            }]
                                            };

                                        new ApexCharts(document.querySelector("#chart-{{ Str::slug($data['nombre']) }}"), chartOptions)
                                            .render();
                                        });
                                    </script>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted my-3">No hay datos de enlaces</span>
                    @endif
                </div>
            </div>
        @else
            <div class="container contenedor-tarjetas">
                <div class="tarjeta-sped">
                    <div class="tarjeta-sped-details">
                        <img src="{{ asset('assets-administrador/img/modulo_indicadores.png') }}" alt="">
                    </div>
                    <a href="{{ route('panel-indicadores.index') }}" target="_self" title="Indicadores"
                        class="tarjeta-sped-button">Ver</a>
                </div>
            </div>
        @endif
    @endauth

    <script>
        var options = {
            series: [{
                        {
                $semaforizacionCounts['Excedido']
                        }
                    },
            {
                        {
            $semaforizacionCounts['Aceptable']
        }
                    },
        {
            {
                $semaforizacionCounts['Moderado']
            }
        },
        {
            {
                $semaforizacionCounts['Insuficiente']
            }
        },
        {
            {
                $semaforizacionCounts['No clasificado']
            }
        }
                ],
        chart: {
            type: 'pie',
                height: 350,
                    events: {
                dataPointSelection: function(event, chartContext, config) {
                    var categorias = ["Excedido", "Aceptable", "Moderado", "Insuficiente", "No clasificado"];
                    var categoriaSeleccionada = categorias[config.dataPointIndex];

                    var url = "/panel-indicadores/semaforizacion/" + categoriaSeleccionada;
                    window.open(url, "_blank");
                    toolbar: {
                        show: true,
                            tools: {
                            download: true,
                                selection: false,
                                    zoom: false,
                                        zoomin: false,
                                            zoomout: false,
                                                pan: false,
                                                    reset: false
                        }
                    },
                },
                title: {
                    text: "Distribución de Indicadores por Semaforización",
                        align: 'center',
                            style: {
                        fontSize: '18px',
                            fontWeight: 'bold',
                                color: '#333'
                    }
                },
                colors: ['#3E8CEE', '#43B383', '#F5E35B', '#B94149', '#D3D3D3'],
                    labels: ["Excedido", "Aceptable", "Moderado", "Insuficiente", "No clasificado"],
                    };

            var chart = new ApexCharts(document.querySelector("#semaforizacionChart"), options);
            chart.render();
    </script>
    <script>
                    var options = {
                        series: [{
                            name: 'Indicadores con Datos',
                            data: @json($datosPorAnio)
                        }],
                        chart: {
                            type: 'bar',
                            height: 350,

                        },
                        title: {
                            text: "Distribución de Indicadores por Datos Anuales",
                            align: 'center',
                            style: {
                                fontSize: '18px',
                                fontWeight: 'bold',
                                color: '#333'
                            }
                        },
                        xaxis: {
                            categories: @json($years)
                        },
                        colors: ['#007bff'],
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '50%'
                            }
                        },
                        dataLabels: {
                            enabled: false
                        }
                    };

                    new ApexCharts(document.querySelector("#chart-indicadores-anio"), options).render();
    </script>
    <script>
            var options = {
                series: @json($values_periodicidades),
                chart: {
                    type: 'donut',
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    },
                    height: 350
                },
                title: {
                    text: "Distribución de Indicadores por Periodicidad",
                    align: 'center',
                    style: {
                        fontSize: '18px',
                        fontWeight: 'bold',
                        color: '#333'
                    }
                },
                labels: @json($etiquetas_periodicidades),
                colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0'],
                dataLabels: {
                    enabled: true
                },
                legend: {
                    position: 'bottom'
                }
            };

            new ApexCharts(document.querySelector("#chart-periodicidad"), options).render();
    </script>
</x-app-layout>
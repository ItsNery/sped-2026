@extends('layouts.plantilla')
@section('title', 'Plan Estatal de Desarrollo 2024 - 2030')
@section('meta-description',
'Sección de la Plan Estatal de Desarrollo 2024 - 2030 dentro del Sistema de
Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
'Plan Estatal de Desarrollo 2024 - 2030 - Sistema de Información para el Seguimiento a la
Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description',
'Sección de la Plan Estatal de Desarrollo 2024 - 2030 dentro del Sistema de
Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
'Plan Estatal de Desarrollo 2024 - 2030 - Sistema de Información para el Seguimiento a la
Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description',
'Sección de la Plan Estatal de Desarrollo 2024 - 2030 dentro del Sistema de
Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('css')
@endsection
@section('jss-inicial')
@endsection
@section('content')
@include('partials.nav-unificada', [
'tipoNav' => 'ped',
'itemActivo' => null,
'bannerImg' => 'img/Banners/Banner_PED/PED.jpg',
'colorTema' => '#9d2449'
])
<!-- INICIO SECCIÓN AVANCE -->
<section class="avance-ped py-5 bg-light mb-0">
    <div class="container">
        <h2 class="text-center fw-bold mb-5" style="color: #9d2449;">Avance general</h2>

        <div class="row mb-5 justify-content-center">
            <div class="col-md-6 text-center">
                <div class="bg-white p-4 rounded shadow-sm">
                    <div id="mainGauge" style="height: 300px;"></div>
                    <div style="font-size: 2.5rem; font-weight: 700; margin-top: -60px;">{{ number_format($avancePlan, 1) }}%</div>
                </div>
            </div>
        </div>
        <nav class="nav nav-pills nav-fill" id="pills-tab" role="tablist">
            <a class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" href="#pills-home" role="tab" aria-controls="pills-home" aria-selected="true">Ejes</a>
            <a class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" href="#pills-profile" role="tab" aria-controls="pills-profile" aria-selected="false">Programas Derivados</a>
        </nav>
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                <h3 class="fw-bold mb-4 text-center">Avance por Ejes</h3>
                <div class="row g-4 mb-5">
                    @foreach($ejesData as $eje)
                    <div class="col-md-4">

                        <div class="bg-white p-3 rounded shadow-sm text-center h-100 card-avance">
                            <div class="card-avance_indicador" style="background-color: {{ $eje['color'] }};">
                                {{ $eje['numero'] }}
                            </div>
                            <a href="{{ url('/ped/eje-' . $eje['numero']) }}">
                                <h6 class="fw-bold">{{ $eje['nombre'] }}</h6>
                            </a>
                            <div id="gauge-eje-{{ $eje['id'] }}" style="height: 180px;"></div>
                            <div class="h4 fw-bold mt-2" style="color: {{ $eje['semaforo_color'] }}">{{ number_format($eje['avance'], 1) }}%</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                <h3 class="fw-bold mb-4 text-center">Avance por Programas Derivados</h3>

                @php
                // 1. Agrupamos los datos
                $programasAgrupados = $programasData->groupBy('tipo');

                // 2. Definimos el orden de prioridad EXACTO.
                // OJO: Asegúrate de que los textos coincidan exactamente con lo que trae tu variable $tipo
                $ordenDeseado = [
                'Programas Sectoriales',
                'Programas Especiales',
                'Programas Regionales',
                'Programas Institucionales'
                ];

                // 3. Ordenamos la colección agrupada
                $programasOrdenados = $programasAgrupados->sortBy(function ($programas, $tipo) use ($ordenDeseado) {
                $posicion = array_search($tipo, $ordenDeseado);
                // Si encuentra el tipo en el arreglo, le asigna su posición.
                // Si por alguna razón hay un tipo nuevo que no está en la lista, lo manda al final (999).
                return $posicion !== false ? $posicion : 999;
                });
                @endphp

                @foreach($programasOrdenados as $tipo => $programas)
                <h4 class="fw-bold mb-4 mt-5 text-center" style="color: #9d2449; border-bottom: 2px solid #9d2449; padding-bottom: 10px;">
                    {{ $tipo }}
                </h4>

                <div class="row g-4 mb-5">
                    @foreach($programas as $programa)
                    <div class="col-md-4">
                        <div class="bg-white p-3 rounded shadow-sm text-center h-100 card-avance">
                            <div class="card-avance_indicador" style="background-color: {{ $programa['color'] }};">
                                {{ $programa['id'] }}
                            </div>
                            <a href="{{ url('/ped-programas/' . $programa['tipo_slug'] . '/' . Illuminate\Support\Str::slug($programa['nombre'])) }}">
                                <h6 class="fw-bold">{{ $programa['nombre'] }}</h6>
                            </a>
                            <div id="gauge-prog-{{ $programa['tipo_slug'] }}-{{ $programa['id'] }}" style="height: 180px;"></div>
                            <div class="h4 fw-bold mt-2" style="color: {{ $programa['semaforo_color'] }}">
                                {{ number_format($programa['avance'], 1) }}%
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
<!-- FIN SECCIÓN AVANCE -->
@section('jss-final')
<script src="{{ asset('js/apexcharts.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const getGaugeOptions = (value, color) => {
            return {
                series: [value > 100 ? 100 : value],
                chart: {
                    type: 'radialBar',
                    offsetY: -10,
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
                            strokeWidth: '97%'
                        },
                        dataLabels: {
                            name: {
                                show: false
                            },
                            value: {
                                show: false
                            }
                        }
                    }
                },
                fill: {
                    colors: [color]
                },
                labels: ['Avance'],
            };
        };

        // Main Gauge
        var avancePlan = Number("{{ $avancePlan }}");
        var colorPlan = "{{ $colorPlan }}";
        new ApexCharts(document.querySelector("#mainGauge"), getGaugeOptions(avancePlan, colorPlan)).render();

        // Axis Gauges
        var ejesDataString = '{!! json_encode($ejesData) !!}';
        var ejesData = JSON.parse(ejesDataString);
        ejesData.forEach(function(eje) {
            new ApexCharts(document.querySelector("#gauge-eje-" + eje.id), getGaugeOptions(eje.avance, eje.semaforo_color)).render();
        });
        var programasDataString = '{!! json_encode($programasData) !!}';
        var programasData = JSON.parse(programasDataString);
        programasData.forEach(function(programa, index) {
            var selector = "#gauge-prog-" + programa.tipo_slug + "-" + programa.id;
            var element = document.querySelector(selector);

            if (element) {
                element.innerHTML = '';

                var chart = new ApexCharts(element, getGaugeOptions(programa.avance, programa.semaforo_color));
                chart.render();
            }
        });
    });
</script>
@endsection
@endsection
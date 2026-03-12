@extends('layouts.plantilla')
@section('title', 'Ficha técnica ' . $indicador->nombre)
@section('meta-description', 'Ficha ténica del indicador municipal ' . $indicador->nombre)
@section('canonical-url', url()->current())
@section('og-title',
    $indicador->nombre .
    ' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description', $indicador->descripcion)
@section('og:url', url()->current())
@section('twitter-title',
    $indicador->nombre .
    ' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description', $indicador->descripcion)
@section('css')
@endsection
@section('jss-inicial')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endsection
@section('content')
    <div class="row" style="margin-left: auto; margin-right: auto;">
        <img src="{{ asset($municipio->banner) }}" class="w-100 px-0">
    </div>
    &nbsp;
    <div class="container" id="imprimir">
        <div class="row" id="encabezado" style="display:none;">
            <img class="img-fluid" src="{{ asset('img/pleca_ficha.jpg') }}" width="100%" />
        </div>
        <div class="row ficha">
            <div class="row">
                <h2 style="color:#C1B999;">Indicador</h2>
                &nbsp;
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 mb-2">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid #C1B999;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_indicador" style="color:#C1B999;">

                                        {{ $indicador->indicador }}
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_datos align-self-center"
                                        style="text-align: center; margin-top:10px;">
                                        @foreach ($indicador->ods->unique('id') as $ods)
                                            <img src="{{ asset('/img/Icons_ODS/' . $ods->id . '.png') }}"
                                                alt="Imagen de ODS {{ $ods->id }}" class="hvr-wobble-top"
                                                style="width:60px; border-radius: 5px 5px 5px 5px;">
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-6">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid #C1B999;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 ficha_titulo" style="color:#C1B999;">
                                        Eje
                                    </div>
                                    <div class="col-md-9 ficha_datos">
                                        {{ $indicador->eje_indicador }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-6">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid #C1B999;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_titulo" style="color:#C1B999;">
                                        Temática
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_datos">
                                        {{ $indicador->tematica }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <h2 style="color:#C1B999;" class="my-3">Identificador del Indicador</h2>
                <div class="col-xs-12 col-sm-6 col-md-12 mb-3">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid #C1B999;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-2 ficha_titulo" style="color:#C1B999;">
                                        Descripción
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-10 ficha_datos">
                                        {{ $indicador->descripcion }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-12 mb-3">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid #C1B999;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-2 ficha_titulo" style="color:#C1B999;">
                                        Fuente
                                    </div>
                                    @if ($indicador->liga == null || $indicador->liga == '0')
                                        <div class="col-xs-12 col-sm-12 col-md-10 ficha_datos">
                                            {{ $indicador->fuente }}
                                        </div>
                                    @else
                                        <div class="col-xs-12 col-sm-12 col-md-9 ficha_datos">
                                            {{ $indicador->fuente }}
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-1 ficha_datos">
                                            <a href="{{ $indicador->liga }}" target="_blank">
                                                <i class="fa fa-globe fa-2x" aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3 col-md-4 mb-3">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid #C1B999;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-7 ficha_titulo" style="color:#C1B999;">
                                        Periodicidad
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-5 ficha_datos">
                                        {{ $indicador->periodicidad->nombre }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3 col-md-4 mb-3">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid #C1B999;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-8 ficha_titulo" style="color:#C1B999;">
                                        Próxima Actualización
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4 ficha_datos">
                                        {{ $indicador->proxima_actualizacion }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3 col-md-4 mb-3">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid #C1B999;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_titulo" style="color:#C1B999;">
                                        Tendencia
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_datos">
                                        {{ $indicador->tendencia }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid #C1B999;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_titulo" style="color:#C1B999;">
                                        Cobertura Geográfica
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_datos">
                                        {{ $indicador->cobertura }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid #C1B999;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_titulo" style="color:#C1B999;">
                                        Unidad de Medida
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_datos">
                                        {{ $indicador->unidad_medida }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3 col-md-4 mb-3">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid #C1B999;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_titulo" style="color:#C1B999;">
                                        Tipo
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_datos">
                                        {{ $indicador->tipo->nombre }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3 col-md-4 mb-3">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid #C1B999;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_titulo" style="color:#C1B999;">
                                        Nivel
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_datos">
                                        {{ $indicador->nivel->nombre }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3 col-md-4 mb-3">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid #C1B999;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_titulo" style="color:#C1B999;">
                                        Dimension
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6 ficha_datos">
                                        {{ $indicador->dimension->nombre }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <h2 style="color:#C1B999;">Principales Resultados</h2>
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="card">
                        <div class="card-content card_ficha" style="border-top: 12px solid #C1B999;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 ficha_datos">
                                        {{ $indicador->resultado_mas_reciente }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div width="100%" class="row ocultar_datos">
                    <h2 style="color:#C1B999;" class="mt-3 ocultar_grafica">Resultados Históricos</h2>
                    <div class="col-xs-12 col-sm-12 col-md-12 ocultar_tabla mb-1">
                        <div class="panel-body table-responsive">
                            <table class="table_resultados" style="table-layout: fixed; width: 100%;">
                                <thead style="background-color:#C1B999; color:#FFFFFF;">
                                    <tr>
                                        <th style="border-radius: 20px 0px 0px 0px;">2019</th>
                                        <th>2020</th>
                                        <th>2021</th>
                                        <th>2022</th>
                                        <th>2023</th>
                                        <th>2024</th>
                                        <th>2025</th>
                                        <th>2026</th>
                                        <th style="border-radius: 0px 20px 0px 0px;">2027</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            {{ $indicador->dato_2019 ?? 'Sin datos' }}
                                        </td>
                                        <td>
                                            {{ $indicador->dato_2020 ?? 'Sin datos' }}
                                        </td>
                                        <td>
                                            {{ $indicador->dato_2021 ?? 'Sin datos' }}
                                        </td>
                                        <td>
                                            {{ $indicador->dato_2022 ?? 'Sin datos' }}
                                        </td>
                                        <td>
                                            {{ $indicador->dato_2023 ?? 'Sin datos' }}
                                        </td>
                                        <td>
                                            {{ $indicador->dato_2024 ?? 'Sin datos' }}
                                        </td>
                                        <td>
                                            {{ $indicador->dato_2025 ?? 'Sin datos' }}
                                        </td>
                                        <td>
                                            {{ $indicador->dato_2026 ?? 'Sin datos' }}
                                        </td>
                                        <td>
                                            {{ $indicador->dato_2027 ?? 'Sin datos' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    &nbsp;
                    <h2 style="color:#C1B999;" class="pt-0 ocultar_grafica">Gráfico</h2>
                    <div class="col-xs-12 col-sm-12 col-md-12 ocultar_grafica" style="border-top: 12px solid #C1B999;">
                        <div id="grafica"></div>
                        <p style="text-align: center; font-size: 12px; color: #777;">
                            Fuente: {{ $indicador->fuente ?? 'Sin fuente disponible' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    &nbsp;
    <div class="container ocultar_grafica">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-5" style="text-align:right; color:#C1B999;">
            </div>
            <div class="col-xs-12 col-sm-12 col-md-2 hvr-grow my-2" onclick="printDiv('imprimir')">
                <img src="{{ asset('/img/Ficha_Tecnica.jpg') }}"
                    style=" float:left; border-radius: 5px 5px 5px 5px; width: 100%; cursor:pointer;">
            </div>
        </div>
    </div>
@section('jss-final')
    <script>
        var options = {
            series: [{
                    name: '{{ $indicador->unidad_medida }}',
                    data: [{
                            x: '2019',
                            y: {{ $indicador->dato_2019 ?? 'null' }}
                        },
                        {
                            x: '2020',
                            y: {{ $indicador->dato_2020 ?? 'null' }}
                        },
                        {
                            x: '2021',
                            y: {{ $indicador->dato_2021 ?? 'null' }}
                        },
                        {
                            x: '2022',
                            y: {{ $indicador->dato_2022 ?? 'null' }}
                        },
                        {
                            x: '2023',
                            y: {{ $indicador->dato_2023 ?? 'null' }}
                        },
                        {
                            x: '2024',
                            y: {{ $indicador->dato_2024 ?? 'null' }}
                        },
                        {
                            x: '2025',
                            y: {{ $indicador->dato_2025 ?? 'null' }}
                        },
                        {
                            x: '2026',
                            y: {{ $indicador->dato_2026 ?? 'null' }}
                        },
                        {
                            x: '2027',
                            y: {{ $indicador->dato_2027 ?? 'null' }}
                        }
                    ]
                },
                {
                    name: 'Meta 2027',
                    data: [{
                            x: '2019',
                            y: null
                        },
                        {
                            x: '2020',
                            y: null
                        },
                        {
                            x: '2021',
                            y: null
                        },
                        {
                            x: '2022',
                            y: null
                        },
                        {
                            x: '2023',
                            y: null
                        },
                        {
                            x: '2024',
                            y: null
                        },
                        {
                            x: '2025',
                            y: null
                        },
                        {
                            x: '2026',
                            y: null
                        },
                        {
                            x: '2027',
                            y: {{ $indicador->meta_2024 ?? 'null' }}
                        }
                    ],
                    type: 'scatter', // Usamos scatter para marcar solo un punto
                    marker: {
                        size: 8,
                        fillColor: '#ff0000', // Cambia el color del marcador de la meta
                        shape: 'diamond'
                    },

                    tooltip: {
                        custom: function({
                            seriesIndex,
                            dataPointIndex,
                            w
                        }) {
                            return '<div class="apexcharts-tooltip-text"><span>Meta 2027: </span>' +
                                w.config.series[seriesIndex].data[dataPointIndex].y + '</div>';
                        }
                    }
                }
            ],
            chart: {
                type: 'area', // Cambiado a gráfico de área
                height: 350,
                animations: {
                    enabled: false
                },
                zoom: {
                    enabled: false
                },
            },
            colors: ['#C1B999',
                '#ff0000'
            ],
            dataLabels: {
                enabled: true
            },
            stroke: {
                curve: 'smooth', // Usamos spline para las líneas
            },
            fill: {
                opacity: 0.2, // Transparente para la área
            },
            markers: {
                size: 5,
                hover: {
                    size: 9
                }
            },
            title: {
                text: '{{ $indicador->indicador }}',
            },
            tooltip: {
                intersect: true,
                shared: false
            },
            theme: {
                palette: 'palette1'
            },
            xaxis: {
                categories: ['2010', '2011', '2012', '2013', '2014', '2015', '2016', '2017', '2018', '2019', '2020',
                    '2021', '2022', '2023', '2024'
                ],
            },
            yaxis: {
                title: {
                    text: 'Rango de valores de medición'
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#grafica"), options);
        chart.render();
    </script>
    <script>
        function printDiv(imprimir) {
            var contenido = document.getElementById(imprimir).innerHTML;
            var contenidoOriginal = document.body.innerHTML;
            document.body.innerHTML = contenido;
            encabezado.style.display = 'block';
            window.print();
            document.body.innerHTML = contenidoOriginal;
            location.reload();
        }
    </script>
@endsection
@endsection

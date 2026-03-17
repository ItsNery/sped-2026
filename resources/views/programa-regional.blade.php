@extends('layouts.plantilla')
@section('title', 'Programa Derivado Regional ' . $programa->nombre)
@section('meta-description', $programaData->descripcion)
@section('canonical-url', url()->current())
@section('og-title',
'Programa Derivado Regional ' .
$programa->nombre .
' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description', $programaData->descripcion)
@section('og:url', url()->current())
@section('twitter-title',
$programa->nombre .
' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description', $programaData->descripcion)
@section('css')
@endsection
@section('jss-inicial')
@endsection

@section('content')
    @include('partials.programa-derivado-contenido', [
        'itemActivoNav' => 'App\Models\CatProgramaDerivadoRegional',
        'tituloBadge' => 'Programa Regional',
        // Variables pre-existentes disponibles en el controlador para la vista
    ])
@endsection

@section('jss-final')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var chartValGeneral = Number("{{ ($avancePrograma ?? 0) > 100 ? 100 : ($avancePrograma ?? 0) }}");
        new ApexCharts(document.querySelector("#gauge-general"), {
            series: [chartValGeneral],
            chart: {
                type: 'radialBar',
                height: 220,
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
                colors: ['{{ $programa->color }}']
            },
            stroke: {
                lineCap: 'round'
            }
        }).render();

        var popoverList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]')).map(function(el) {
            return new bootstrap.Popover(el, {
                sanitize: false
            });
        });
        document.addEventListener('click', function(e) {});
    });
</script>
@endsection
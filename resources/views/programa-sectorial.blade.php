@extends('layouts.plantilla')
@section('title', 'Programa Derivado Sectorial ' . $programa->nombre)
@section('meta-description', $descripcion)
@section('canonical-url', url()->current())
@section('og-title',
' Programa Derivado Sectorial ' .
$programa->nombre .
' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description', $descripcion)
@section('og:url', url()->current())
@section('twitter-title',
' Programa Derivado Sectorial ' .
$programa->nombre .
' - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description', $descripcion)
@section('content')
@include('partials.programa-derivado-contenido', [
'itemActivoNav' => 'App\Models\CatProgramaDerivadoSectorial',
'tituloBadge' => 'Programa Sectorial',
])
@endsection
@section('jss-final')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var chartValGeneral = Number("{{ ($avancePrograma ?? 0) > 100 ? 100 : ($avancePrograma ?? 0) }}");
        var gaugeGeneral = echarts.init(document.getElementById('gauge-general'));
        gaugeGeneral.setOption({
            series: [{
                type: 'gauge',
                startAngle: 180, endAngle: 0,
                min: 0, max: 100,
                progress: { show: true, width: 15, roundCap: true, itemStyle: { color: '{{ $programa->color }}' } },
                axisLine: { lineStyle: { width: 15, color: [[1, '#e7e7e7']] } },
                axisTick: { show: false }, splitLine: { show: false },
                axisLabel: { show: false }, pointer: { show: false },
                detail: { show: false },
                data: [{ value: chartValGeneral }]
            }]
        });
        gaugeGeneral.resize();

        var popoverList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]')).map(function(el) {
            return new bootstrap.Popover(el, {
                sanitize: false
            });
        });
        document.addEventListener('click', function(e) {});
    });
</script>
@endsection
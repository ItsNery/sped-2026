<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #21312f; font: 10px Arial, sans-serif; }
        h1, h2, h3, p { margin: 0; }
        .header { display: flex; justify-content: space-between; align-items: end; padding-bottom: 12px; border-bottom: 3px solid #0c312d; }
        .eyebrow { color: #9d2449; font-size: 8px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; }
        h1 { margin-top: 4px; color: #0c312d; font-size: 22px; }
        .meta { color: #687773; text-align: right; }
        .kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 14px 0; }
        .kpi, .panel { padding: 10px; border: 1px solid #dfe8e4; border-radius: 6px; }
        .kpi strong { display: block; margin: 5px 0; color: #0c312d; font-size: 21px; }
        .kpi span { color: #687773; font-size: 8px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .panel { margin-bottom: 10px; }
        h2 { margin-bottom: 8px; color: #0c312d; font-size: 13px; }
        h3 { margin: 10px 0 5px; color: #0c312d; font-size: 10px; }
        .bar { display: flex; height: 14px; margin: 10px 0; overflow: hidden; border-radius: 8px; }
        .legend { display: grid; grid-template-columns: repeat(2, 1fr); gap: 4px; }
        .legend span { color: #687773; }
        .legend b { float: right; color: #21312f; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 5px 4px; background: #0c312d; color: #fff; font-size: 8px; text-align: left; }
        td { padding: 5px 4px; border-bottom: 1px solid #e9efec; font-size: 8px; }
        .axis-row { display: grid; grid-template-columns: 1fr 1fr 50px; gap: 8px; align-items: center; padding: 5px 0; border-bottom: 1px solid #e9efec; }
        .axis-bar { height: 7px; overflow: hidden; border-radius: 4px; background: #edf1ef; }
        .axis-bar i { display: block; height: 100%; background: #0c312d; }
        .note { margin-top: 10px; color: #687773; font-size: 8px; }
    </style>
</head>
<body>
    <header class="header">
        <div><span class="eyebrow">Centro de mando del PED</span><h1>{{ $plan->nombre }}</h1></div>
        <div class="meta">{{ $soloValidados ? 'Datos validados' : 'Todos los registrados' }}<br> Corte: {{ $fechaCorte?->format('d/m/Y') ?? 'Sin fecha' }}</div>
    </header>

    <section class="kpis">
        <div class="kpi"><span>Avance promedio</span><strong>{{ number_format($avanceGlobalPromedio, 1) }}%</strong><span>{{ $metricasGlobal['total_evaluables'] }} de {{ $totalIndicadores }} evaluables</span></div>
        <div class="kpi"><span>Cobertura de evaluación</span><strong>{{ number_format($metricasGlobal['cobertura_evaluacion'], 1) }}%</strong><span>{{ $metricasGlobal['total_evaluables'] }} con dato útil</span></div>
        <div class="kpi"><span>Validación</span><strong>{{ number_format($porcentajeValidado, 1) }}%</strong><span>{{ $totalIndicadoresValidados }} indicadores validados</span></div>
        <div class="kpi"><span>Señales críticas</span><strong>{{ $indicadoresCriticos }}</strong><span>Requieren atención</span></div>
    </section>

    <div class="grid">
        <section class="panel">
            <h2>Estado del universo</h2>
            @php $colors = ['Excedido' => '#3E8CEE', 'Aceptable' => '#43B383', 'Moderado' => '#F5E35B', 'Insuficiente' => '#B94149', 'No clasificado' => '#A7AFB2']; $total = max(array_sum($semaforizacionCounts), 1); @endphp
            <div class="bar">@foreach ($semaforizacionCounts as $estado => $cantidad)<i style="width: {{ $cantidad / $total * 100 }}%; background: {{ $colors[$estado] }}"></i>@endforeach</div>
            <div class="legend">@foreach ($semaforizacionCounts as $estado => $cantidad)<span>{{ $estado }} <b>{{ $cantidad }}</b></span>@endforeach</div>
        </section>
        <section class="panel">
            <h2>Calidad de información</h2>
            <div class="legend">
                <span>Sin dato anual <b>{{ $quality['sin_datos'] }}</b></span>
                <span>Pendientes de validación <b>{{ $quality['pendientes_validacion'] }}</b></span>
                <span>Sin meta válida <b>{{ $quality['sin_meta'] }}</b></span>
                <span>Sin tendencia <b>{{ $quality['sin_tendencia'] }}</b></span>
            </div>
        </section>
    </div>

    <section class="panel">
        <h2>Avance por eje</h2>
        @foreach ($ejesData as $eje)
            <div class="axis-row"><span>{{ $eje['numero'] }}. {{ $eje['nombre'] }}</span><div class="axis-bar"><i style="width: {{ min(100, max(0, $eje['avance'] ?? 0)) }}%"></i></div><b>{{ number_format($eje['avance'] ?? 0, 1) }}%</b></div>
        @endforeach
    </section>

    <section class="panel">
        <h2>Indicadores prioritarios</h2>
        <table><thead><tr><th>Indicador</th><th>Institución</th><th>Motivo</th><th>Avance</th><th>Último dato</th></tr></thead><tbody>
            @foreach ($actionQueue->take(12) as $item)
                <tr><td>{{ Str::limit($item['nombre'], 80) }}</td><td>{{ Str::limit($item['institucion'], 35) }}</td><td>{{ $item['motivo'] }}</td><td>{{ $item['avance'] !== null ? number_format($item['avance'], 1) . '%' : 'N/D' }}</td><td>{{ $item['fecha_dato'] }}</td></tr>
            @endforeach
        </tbody></table>
    </section>

    <div class="grid">
        <section class="panel">
            <h2>Evolución del desempeño</h2>
            <table><thead><tr><th>Año</th><th>Avance promedio</th><th>Evaluables</th><th>Indicadores</th></tr></thead><tbody>
                @foreach ($trend['series'] as $item)
                    <tr><td>{{ $item['anio'] }}</td><td>{{ $item['avance'] !== null ? number_format($item['avance'], 1) . '%' : 'N/D' }}</td><td>{{ $item['evaluables'] }}</td><td>{{ $item['indicadores'] }}</td></tr>
                @endforeach
            </tbody></table>
        </section>
        <section class="panel">
            <h2>Comparación entre periodos</h2>
            <div class="legend">
                <span>Mejoran <b>{{ $trend['comparaciones']['mejoran'] }}</b></span>
                <span>Retroceden <b>{{ $trend['comparaciones']['retroceden'] }}</b></span>
                <span>Estables <b>{{ $trend['comparaciones']['estables'] }}</b></span>
                <span>Sin comparación <b>{{ $trend['comparaciones']['sin_comparacion'] }}</b></span>
            </div>
        </section>
    </div>

    <p class="note">Documento generado por el SPED. La evolución histórica representa comportamiento observado y no constituye una proyección.</p>
</body>
</html>

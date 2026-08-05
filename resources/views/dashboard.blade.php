<x-app-layout>
    @section('title', 'Centro de mando del PED')
    <x-slot name="header">
        <div class="exec-header">
            <div>
                <span class="exec-eyebrow">Centro de mando</span>
                <h2 class="exec-header__title">Seguimiento del Plan Estatal de Desarrollo</h2>
            </div>
            <span class="exec-header__plan">{{ $plan->nombre }}</span>
        </div>
    </x-slot>

    @php
        $semaforoColores = [
            'Excedido' => '#3E8CEE',
            'Aceptable' => '#43B383',
            'Moderado' => '#F5E35B',
            'Insuficiente' => '#B94149',
            'No clasificado' => '#A7AFB2',
        ];
        $totalSemaforo = max(array_sum($semaforizacionCounts), 1);
        $programasPrioritarios = $programasData
            ->flatMap(fn ($grupo) => $grupo['programas'])
            ->sortBy(fn ($programa) => $programa['avance'] ?? -1)
            ->take(8);
    @endphp

    <div class="exec-dashboard">
        <section class="exec-intro" aria-labelledby="exec-title">
            <div class="exec-intro__copy">
                <span class="exec-eyebrow">Lectura ejecutiva</span>
                <h1 id="exec-title">¿Qué requiere atención hoy?</h1>
                <p>
                    Resumen operativo del {{ $plan->nombre }} con datos
                    {{ $soloValidados ? 'validados' : 'registrados' }}.
                </p>
            </div>
            <div class="exec-options" aria-label="Opciones de consulta">
                <div class="exec-options__header">
                    <div>
                        <span class="exec-eyebrow">Controles del tablero</span>
                        <strong>Opciones de consulta</strong>
                    </div>
                    <span class="exec-options__hint">Actualiza el universo visible</span>
                </div>
                <div class="exec-options__body">
                    <form class="exec-filters" method="GET" action="{{ route('dashboard') }}">
                        <div class="exec-filter-field exec-filter-field--plan">
                            <span>Plan estatal activo</span>
                            <strong>{{ $plan->nombre }}</strong>
                        </div>
                        <label class="exec-filter-field">
                            <span>Datos</span>
                            <select name="solo_validados" aria-label="Seleccionar estado de datos">
                                <option value="1" @selected($soloValidados)>Solo validados</option>
                                <option value="0" @selected(!$soloValidados)>Todos los registrados</option>
                            </select>
                        </label>
                        <label class="exec-filter-field exec-filter-field--year">
                            <span>Desde</span>
                            <input type="number" name="anio_desde" min="2000" max="2100" value="{{ request('anio_desde') }}" placeholder="Año">
                        </label>
                        <label class="exec-filter-field exec-filter-field--year">
                            <span>Hasta</span>
                            <input type="number" name="anio_hasta" min="2000" max="2100" value="{{ request('anio_hasta') }}" placeholder="Año">
                        </label>
                        @foreach (['eje_id', 'programa_id', 'institucion_id', 'semaforo', 'calidad'] as $hiddenFilter)
                            @foreach ($filters[$hiddenFilter] as $hiddenValue)
                                <input type="hidden" name="{{ $hiddenFilter }}[]" value="{{ $hiddenValue }}">
                            @endforeach
                        @endforeach
                        @if ($filters['programa_tipo'])<input type="hidden" name="programa_tipo" value="{{ $filters['programa_tipo'] }}">@endif
                        @if ($filters['buscar'])<input type="hidden" name="buscar" value="{{ $filters['buscar'] }}">@endif
                        <button type="submit" class="exec-filter-button">Actualizar</button>
                    </form>
                    <div class="exec-options__tools">
                        <div class="exec-options__tool-group">
                            <a class="exec-clear-filters" href="{{ route('dashboard') }}">
                                <span aria-hidden="true">×</span> Limpiar
                            </a>
                            <button type="button" class="exec-more-filters" data-bs-toggle="offcanvas" data-bs-target="#dashboardFilters" aria-controls="dashboardFilters">
                                <span class="exec-more-filters__icon" aria-hidden="true">+</span>
                                <span class="exec-more-filters__label">Más filtros</span>
                                <b>{{ count($filters['eje_id']) + count($filters['programa_id']) + count($filters['institucion_id']) + count($filters['semaforo']) + count($filters['calidad']) + ($filters['programa_tipo'] ? 1 : 0) + ($filters['buscar'] ? 1 : 0) }}</b>
                            </button>
                        </div>
                        <div class="exec-export-actions" aria-label="Exportar dashboard">
                            <span class="exec-tools-label">Exportar</span>
                            <a href="{{ route('dashboard.export.pdf', request()->query()) }}" title="Descargar PDF"><strong>PDF</strong><small>Descargar</small></a>
                            <a href="{{ route('dashboard.export.xlsx', request()->query()) }}" title="Descargar Excel"><strong>XLSX</strong><small>Descargar</small></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="exec-filter-chips" aria-label="Filtros activos">
            @if ($filters['eje_id']) <a href="{{ route('dashboard', request()->except('eje_id')) }}">Eje: {{ count($filters['eje_id']) }} seleccionado(s) ×</a> @endif
            @if ($filters['institucion_id']) <a href="{{ route('dashboard', request()->except('institucion_id')) }}">Institución: {{ count($filters['institucion_id']) }} seleccionada(s) ×</a> @endif
            @if ($filters['programa_id']) <a href="{{ route('dashboard', request()->except(['programa_id', 'programa_tipo'])) }}">Programa: {{ count($filters['programa_id']) }} seleccionado(s) ×</a> @endif
            @if ($filters['semaforo']) <a href="{{ route('dashboard', request()->except('semaforo')) }}">Estado: {{ implode(', ', $filters['semaforo']) }} ×</a> @endif
            @if ($filters['calidad']) <a href="{{ route('dashboard', request()->except('calidad')) }}">Calidad: {{ count($filters['calidad']) }} criterio(s) ×</a> @endif
            @if ($filters['buscar']) <a href="{{ route('dashboard', request()->except('buscar')) }}">Búsqueda: {{ $filters['buscar'] }} ×</a> @endif
        </div>

        <div class="offcanvas offcanvas-end exec-filter-drawer" tabindex="-1" id="dashboardFilters" aria-labelledby="dashboardFiltersTitle">
            <div class="offcanvas-header">
                <div><span class="exec-eyebrow">Consulta avanzada</span><h2 id="dashboardFiltersTitle">Más filtros</h2></div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar filtros"></button>
            </div>
            <div class="offcanvas-body">
                <form method="GET" action="{{ route('dashboard') }}" class="exec-drawer-form">
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <input type="hidden" name="solo_validados" value="{{ $soloValidados ? 1 : 0 }}">
                    @if ($filters['anio_desde'] !== null)<input type="hidden" name="anio_desde" value="{{ $filters['anio_desde'] }}">@endif
                    @if ($filters['anio_hasta'] !== null)<input type="hidden" name="anio_hasta" value="{{ $filters['anio_hasta'] }}">@endif

                    <fieldset><legend>Alcance</legend>
                        <label class="exec-drawer-field">Buscar indicador<input type="search" name="buscar" value="{{ $filters['buscar'] }}" placeholder="Nombre, tema o descripción"></label>
                        <label class="exec-drawer-field">Ejes<select name="eje_id[]" multiple size="5">@foreach ($filterOptions['ejes'] as $eje)<option value="{{ $eje->id }}" @selected(in_array($eje->id, $filters['eje_id']))>{{ $eje->numero }}. {{ $eje->nombre }}</option>@endforeach</select></label>
                        <label class="exec-drawer-field">Tipo de programa<select name="programa_tipo"><option value="">Todos los tipos</option>@foreach ($filterOptions['programas'] as $tipo => $programas)<option value="{{ $tipo }}" @selected($filters['programa_tipo'] === $tipo)>{{ ucfirst($tipo) }}</option>@endforeach</select></label>
                        <label class="exec-drawer-field">Programas<select name="programa_id[]" multiple size="6">@foreach ($filterOptions['programas'] as $tipo => $programas)<optgroup label="{{ ucfirst($tipo) }}">@foreach ($programas as $programa)<option value="{{ $programa->id }}" @selected(in_array($programa->id, $filters['programa_id']))>{{ $programa->nombre }}</option>@endforeach</optgroup>@endforeach</select></label>
                        <label class="exec-drawer-field">Instituciones<select name="institucion_id[]" multiple size="6">@foreach ($filterOptions['instituciones'] as $institucion)<option value="{{ $institucion->id }}" @selected(in_array($institucion->id, $filters['institucion_id']))>{{ $institucion->nombre }}</option>@endforeach</select></label>
                    </fieldset>

                    <fieldset><legend>Diagnóstico</legend>
                        <div class="exec-check-grid">@foreach (['Excedido', 'Aceptable', 'Moderado', 'Insuficiente', 'No clasificado'] as $estado)<label><input type="checkbox" name="semaforo[]" value="{{ $estado }}" @checked(in_array($estado, $filters['semaforo']))>{{ $estado }}</label>@endforeach</div>
                        <div class="exec-check-grid">@foreach (['sin_datos' => 'Sin datos', 'sin_meta' => 'Sin meta', 'sin_tendencia' => 'Sin tendencia', 'pendiente_validacion' => 'Pendiente de validación'] as $valor => $label)<label><input type="checkbox" name="calidad[]" value="{{ $valor }}" @checked(in_array($valor, $filters['calidad']))>{{ $label }}</label>@endforeach</div>
                    </fieldset>

                    <div class="exec-drawer-actions"><a href="{{ route('dashboard', ['plan_id' => $plan->id]) }}">Limpiar</a><button type="submit" class="exec-filter-button">Aplicar filtros</button></div>
                </form>
            </div>
        </div>

        <section class="exec-kpis" aria-label="Indicadores principales">
            <a class="exec-kpi exec-kpi--primary" href="#prioridades">
                <span class="exec-kpi__label">Avance promedio</span>
                <strong>{{ number_format($avanceGlobalPromedio, 1) }}<small>%</small></strong>
                <span class="exec-kpi__detail">{{ $metricasGlobal['total_evaluables'] }} de {{ $totalIndicadores }} evaluables</span>
                <span class="exec-kpi__bar"><i style="width: {{ min(100, max(0, $avanceGlobalPromedio)) }}%; background: {{ $colorAvanceGlobal }}"></i></span>
            </a>
            <div class="exec-kpi">
                <span class="exec-kpi__label">Cobertura de evaluación</span>
                <strong>{{ number_format($metricasGlobal['cobertura_evaluacion'], 1) }}<small>%</small></strong>
                <span class="exec-kpi__detail">{{ $metricasGlobal['total_evaluables'] }} indicadores con dato útil</span>
                <span class="exec-kpi__signal exec-kpi__signal--green">Calidad de seguimiento</span>
            </div>
            <div class="exec-kpi">
                <span class="exec-kpi__label">Validación del universo</span>
                <strong>{{ number_format($porcentajeValidado, 1) }}<small>%</small></strong>
                <span class="exec-kpi__detail">{{ $totalIndicadoresValidados }} de {{ $totalIndicadores }} indicadores</span>
                <span class="exec-kpi__signal exec-kpi__signal--sand">{{ $quality['pendientes_validacion'] }} pendientes</span>
            </div>
            <a class="exec-kpi exec-kpi--alert" href="{{ route('dashboard.drill-down', array_merge(request()->query(), ['criticas' => 1])) }}">
                <span class="exec-kpi__label">Alertas críticas</span>
                <strong>{{ $indicadoresCriticos }}</strong>
                <span class="exec-kpi__detail">Avance insuficiente, actualización vencida o validación pendiente</span>
                <span class="exec-kpi__signal exec-kpi__signal--red">{{ $totalCriticos }} sin dato o insuficientes</span>
            </a>
        </section>

        <section class="exec-section" id="prioridades" aria-labelledby="prioridades-title">
            <div class="exec-section__heading">
                <div>
                    <span class="exec-eyebrow">Bandeja de atención</span>
                    <h2 id="prioridades-title">Prioridades para decisión</h2>
                </div>
                <a class="exec-table__action" href="{{ route('dashboard.drill-down', array_merge(request()->query(), ['alertas' => 1])) }}">{{ $actionQueue->count() }} alertas · Ver todas <span aria-hidden="true">→</span></a>
            </div>
            @if ($actionQueue->isEmpty())
                <div class="exec-empty exec-empty--success">
                    <i class="fas fa-circle-check" aria-hidden="true"></i>
                    <div><strong>Sin pendientes críticos</strong><span>El universo seleccionado no tiene alertas que requieran intervención inmediata.</span></div>
                </div>
            @else
                <div class="exec-priority-table-wrap">
                    <table class="exec-table">
                        <caption class="visually-hidden">Indicadores que requieren atención prioritaria</caption>
                        <thead>
                            <tr>
                                <th scope="col">Indicador</th>
                                <th scope="col">Institución</th>
                                <th scope="col">Motivo</th>
                                <th scope="col">Avance</th>
                                <th scope="col">Último dato</th>
                                <th scope="col"><span class="visually-hidden">Acción</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($actionQueue->take(12) as $item)
                                <tr>
                                    <td data-label="Indicador">
                                        <a href="{{ route('panel-indicadores.show', $item['id']) }}" class="exec-table__indicator">
                                            {{ Str::limit($item['nombre'], 72) }}
                                        </a>
                                    </td>
                                    <td data-label="Institución">{{ Str::limit($item['institucion'], 34) }}</td>
                                    <td data-label="Motivo"><span class="exec-status exec-status--{{ $item['prioridad'] <= 2 ? 'red' : 'sand' }}">{{ $item['motivo'] }}</span></td>
                                    <td data-label="Avance" class="exec-table__number">
                                        {{ $item['avance'] !== null ? number_format($item['avance'], 1) . '%' : 'N/D' }}
                                    </td>
                                    <td data-label="Último dato">{{ $item['fecha_dato'] }}{{ $item['anio'] ? ' · ' . $item['anio'] : '' }}</td>
                                    <td data-label="Acción" class="text-end"><a href="{{ route('panel-indicadores.show', $item['id']) }}" class="exec-table__action">Revisar <span aria-hidden="true">→</span></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <div class="exec-grid exec-grid--main">
            <section class="exec-section" aria-labelledby="semaforo-title">
                <div class="exec-section__heading">
                    <div>
                        <span class="exec-eyebrow">Desempeño</span>
                        <h2 id="semaforo-title">Estado del universo</h2>
                    </div>
                    <span class="exec-section__meta">{{ $totalIndicadores }} indicadores</span>
                </div>
                <div class="exec-semaphore" role="img" aria-label="Distribución de indicadores por estado de avance">
                    @foreach ($semaforizacionCounts as $estado => $cantidad)
                        <span style="width: {{ ($cantidad / $totalSemaforo) * 100 }}%; background: {{ $semaforoColores[$estado] }}"></span>
                    @endforeach
                </div>
                <div class="exec-legend">
                    @foreach ($semaforizacionCounts as $estado => $cantidad)
                        <div><i style="background: {{ $semaforoColores[$estado] }}"></i><a class="exec-legend__link" href="{{ route('dashboard.drill-down', ['plan_id' => $plan->id, 'solo_validados' => $soloValidados ? 1 : 0, 'semaforo' => [$estado]]) }}">{{ $estado }}</a><strong>{{ $cantidad }}</strong></div>
                    @endforeach
                </div>
                <div class="exec-method-note">
                    <i class="fas fa-circle-info" aria-hidden="true"></i>
                    <span>El avance se calcula contra la meta y la tendencia registrada de cada indicador.</span>
                </div>
            </section>

            <section class="exec-section" aria-labelledby="quality-title">
                <div class="exec-section__heading">
                    <div>
                        <span class="exec-eyebrow">Calidad de información</span>
                        <h2 id="quality-title">Qué limita la lectura</h2>
                    </div>
                </div>
                <div class="exec-quality-list">
                    <div><span class="exec-quality__marker exec-quality__marker--red"></span><span>Sin dato anual</span><strong>{{ $quality['sin_datos'] }}</strong></div>
                    <div><span class="exec-quality__marker exec-quality__marker--sand"></span><span>Pendientes de validación</span><strong>{{ $quality['pendientes_validacion'] }}</strong></div>
                    <div><span class="exec-quality__marker exec-quality__marker--gray"></span><span>Sin meta válida</span><strong>{{ $quality['sin_meta'] }}</strong></div>
                    <div><span class="exec-quality__marker exec-quality__marker--green"></span><span>Sin tendencia definida</span><strong>{{ $quality['sin_tendencia'] }}</strong></div>
                </div>
                <div class="exec-quality-footer">Último corte de datos: <strong>{{ $fechaCorte ? $fechaCorte->format('d/m/Y') : 'Sin fecha registrada' }}</strong></div>
            </section>
        </div>

        <section class="exec-section" aria-labelledby="trend-title">
            <div class="exec-section__heading">
                <div>
                    <span class="exec-eyebrow">Comportamiento observado</span>
                    <h2 id="trend-title">Evolución del desempeño</h2>
                </div>
                <span class="exec-section__meta">{{ $trend['anio_desde'] ?? 'Sin año' }} - {{ $trend['anio_hasta'] ?? 'Sin año' }}</span>
            </div>
            <div class="exec-trend-summary">
                <div><span class="exec-quality__marker exec-quality__marker--green"></span><span>Mejoran</span><strong>{{ $trend['comparaciones']['mejoran'] }}</strong></div>
                <div><span class="exec-quality__marker exec-quality__marker--red"></span><span>Retroceden</span><strong>{{ $trend['comparaciones']['retroceden'] }}</strong></div>
                <div><span class="exec-quality__marker exec-quality__marker--sand"></span><span>Estables</span><strong>{{ $trend['comparaciones']['estables'] }}</strong></div>
                <div><span class="exec-quality__marker exec-quality__marker--gray"></span><span>Sin comparación</span><strong>{{ $trend['comparaciones']['sin_comparacion'] }}</strong></div>
            </div>
            <div id="exec-trend-chart" class="exec-trend-chart" role="img" aria-label="Evolución histórica del avance promedio"></div>
            <div class="exec-trend-tables">
                <div>
                    <h3>Mayores mejoras</h3>
                    @forelse ($trend['mejoras'] as $item)
                        <div class="exec-trend-item"><span>{{ Str::limit($item['nombre'], 48) }}</span><strong class="exec-trend-item--up">+{{ number_format($item['variacion'], 1) }} pp</strong></div>
                    @empty
                        <p class="exec-trend-empty">No hay mejoras comparables.</p>
                    @endforelse
                </div>
                <div>
                    <h3>Mayores retrocesos</h3>
                    @forelse ($trend['retrocesos'] as $item)
                        <div class="exec-trend-item"><span>{{ Str::limit($item['nombre'], 48) }}</span><strong class="exec-trend-item--down">{{ number_format($item['variacion'], 1) }} pp</strong></div>
                    @empty
                        <p class="exec-trend-empty">No hay retrocesos comparables.</p>
                    @endforelse
                </div>
            </div>
            <p class="exec-method-note"><i class="fas fa-circle-info" aria-hidden="true"></i><span>La variación compara el avance calculado entre los dos últimos años disponibles por indicador. No representa una proyección.</span></p>
        </section>

        <section class="exec-section" aria-labelledby="ejes-title">
            <div class="exec-section__heading">
                <div>
                    <span class="exec-eyebrow">Seguimiento estratégico</span>
                    <h2 id="ejes-title">Avance por eje</h2>
                </div>
                <span class="exec-section__meta">Comparación con la misma regla de cálculo</span>
            </div>
            <div class="exec-axis-list">
                @foreach ($ejesData as $eje)
                    <a class="exec-axis exec-axis__link" href="{{ route('dashboard.drill-down', ['plan_id' => $plan->id, 'solo_validados' => $soloValidados ? 1 : 0, 'eje_id' => [$eje['id']]]) }}">
                        <div class="exec-axis__identity"><span style="background: {{ $eje['color'] }}">{{ $eje['numero'] }}</span><strong>{{ $eje['nombre'] }}</strong></div>
                        <div class="exec-axis__bar"><i style="width: {{ min(100, max(0, $eje['avance'] ?? 0)) }}%; background: {{ $eje['semaforo_color'] }}"></i></div>
                        <strong class="exec-axis__value" style="color: {{ $eje['semaforo_color'] }}">{{ number_format($eje['avance'] ?? 0, 1) }}%</strong>
                        <span class="exec-axis__coverage">{{ number_format($eje['cobertura'], 0) }}% cobertura</span>
                    </a>
                @endforeach
            </div>
        </section>

        <div class="exec-grid exec-grid--lower">
            <section class="exec-section" aria-labelledby="institutions-title">
                <div class="exec-section__heading">
                    <div>
                        <span class="exec-eyebrow">Responsabilidad</span>
                        <h2 id="institutions-title">Instituciones bajo presión</h2>
                    </div>
                </div>
                <div class="exec-institution-list">
                    @forelse ($institucionesData as $institucion)
                        <a class="exec-institution exec-institution__link" href="{{ route('dashboard.drill-down', ['plan_id' => $plan->id, 'solo_validados' => $soloValidados ? 1 : 0, 'institucion_id' => [$institucion['id']]]) }}">
                            <div class="exec-institution__name"><strong>{{ Str::limit($institucion['nombre'], 38) }}</strong><span>{{ $institucion['criticos'] }} señales</span></div>
                            <div class="exec-institution__bar"><i style="width: {{ min(100, max(0, $institucion['cobertura'])) }}%"></i></div>
                            <div class="exec-institution__meta"><span>{{ number_format($institucion['avance'] ?? 0, 1) }}% avance</span><span>{{ number_format($institucion['cobertura'], 0) }}% cobertura</span></div>
                        </a>
                    @empty
                        <div class="exec-empty"><span>No hay instituciones con indicadores en el universo seleccionado.</span></div>
                    @endforelse
                </div>
            </section>

            <section class="exec-section" aria-labelledby="programs-title">
                <div class="exec-section__heading">
                    <div>
                        <span class="exec-eyebrow">Implementación</span>
                        <h2 id="programs-title">Programas con mayor rezago</h2>
                    </div>
                </div>
                <div class="exec-program-list">
                    @forelse ($programasPrioritarios as $programa)
                        <a class="exec-program exec-program__link" href="{{ route('dashboard.drill-down', ['plan_id' => $plan->id, 'solo_validados' => $soloValidados ? 1 : 0, 'programa_tipo' => $programa['tipo_slug'], 'programa_id' => [$programa['id']]]) }}">
                            <div><strong>{{ Str::limit($programa['nombre'], 42) }}</strong><span>{{ $programa['tipo'] }}</span></div>
                            <strong class="exec-program__value" style="color: {{ $programa['semaforo_color'] }}">{{ number_format($programa['avance'] ?? 0, 1) }}%</strong>
                        </a>
                    @empty
                        <div class="exec-empty"><span>No hay programas asociados al plan seleccionado.</span></div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/echarts.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var element = document.getElementById('exec-trend-chart');
                if (!element || typeof echarts === 'undefined') return;

                var chart = echarts.init(element);
                var series = @json($trend['series']);
                chart.setOption({
                    animation: false,
                    tooltip: { trigger: 'axis', valueFormatter: function (value) { return value + '%'; } },
                    grid: { left: 12, right: 20, top: 20, bottom: 28, containLabel: true },
                    xAxis: { type: 'category', data: series.map(function (item) { return item.anio; }) },
                    yAxis: { type: 'value', name: '%', min: 0 },
                    series: [{
                        name: 'Avance promedio',
                        type: 'line',
                        smooth: true,
                        symbolSize: 7,
                        data: series.map(function (item) { return item.avance; }),
                        lineStyle: { width: 3, color: '#0c312d' },
                        itemStyle: { color: '#9d2449' },
                        areaStyle: { color: 'rgba(12, 49, 45, 0.08)' }
                    }]
                });
                window.addEventListener('resize', function () { chart.resize(); });
            });
        </script>
    @endpush
</x-app-layout>

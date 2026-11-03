<x-app-layout>
    @section('title', 'Detalle de indicadores')
    <x-slot name="header">
        <div class="exec-header">
            <div>
                <span class="exec-eyebrow">Drill-down del centro de mando</span>
                <h2 class="exec-header__title">{{ $seguimiento ? 'Seguimiento de actualizaciones' : 'Indicadores filtrados' }}</h2>
            </div>
            <div class="exec-header__context" aria-label="Filtros de la consulta">
                <span class="exec-header__plan">{{ $plan->nombre }}</span>
                <span>Datos: {{ $filters['solo_validados'] ? 'Solo validados' : 'Todos los registrados' }}</span>
                <span>Desde: {{ $filters['anio_desde'] ?? 'Sin límite' }}</span>
                <span>Hasta: {{ $filters['anio_hasta'] ?? 'Sin límite' }}</span>
            </div>
        </div>
    </x-slot>

    <div class="exec-dashboard">
        <div class="exec-detail-toolbar">
            <div>
                <span class="exec-eyebrow">Resultado de consulta</span>
                <h1>{{ $total }} indicadores encontrados</h1>
                <div class="exec-filter-chips">
                    <span>Plan: {{ $plan->nombre }}</span>
                    <span>Datos: {{ $filters['solo_validados'] ? 'Validados' : 'Registrados' }}</span>
                    @if ($filters['buscar']) <span>Búsqueda: {{ $filters['buscar'] }}</span> @endif
                    @if ($filters['eje_id']) <span>{{ count($filters['eje_id']) }} eje(s)</span> @endif
                    @if ($filters['institucion_id']) <span>{{ count($filters['institucion_id']) }} institución(es)</span> @endif
                    @if ($filters['semaforo']) <span>Semáforo filtrado</span> @endif
                    @if ($filters['calidad']) <span>Calidad filtrada</span> @endif
                    @if (request('criticas')) <span>Alertas críticas</span> @endif
                    @if (request('alertas') && !request('criticas')) <span>Bandeja de atención</span> @endif
                </div>
            </div>
            <a class="exec-filter-button" href="{{ route('dashboard', request()->except(['seguimiento', 'estado_plazo', 'estado_actualizacion', 'periodicidad', 'responsable', 'page', 'sort', 'direction'])) }}">Volver al dashboard</a>
        </div>

        <section class="exec-section">
            <div class="exec-section__heading {{ $seguimiento ? 'tracking-heading' : '' }}">
                <div>
                    <span class="exec-eyebrow">Detalle operativo</span>
                    <h2>{{ $seguimiento ? 'Fechas previstas y último dato' : 'Indicadores y responsables' }}</h2>
                    @if ($seguimiento)
                        <p>La fecha anual disponible tiene prioridad sobre la fecha del indicador. Sin fecha, se compara el último año disponible con la periodicidad. Los periodos menores a un año no pueden verificarse con el dato anual.</p>
                        <a class="tracking-attention" href="{{ request()->fullUrlWithQuery(['estado_actualizacion' => 'Desactualizado', 'estado_plazo' => null, 'page' => null]) }}">
                            <strong>{{ $estadosActualizacion->get('Desactualizado', 0) }}</strong>
                            <span>indicadores desactualizados <small>Ver indicadores que requieren atención →</small></span>
                        </a>
                        <div class="exec-filter-chips">
                            @foreach (['Vencido', 'Vence hoy', 'Próximo', 'Programado', 'Sin fecha confiable'] as $estado)
                                <span>{{ $estado }}: {{ $estadosSeguimiento->get($estado, 0) }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
                <form method="GET" class="exec-drill-controls {{ $seguimiento ? 'tracking-filters' : '' }}">
                    @foreach (request()->except(['buscar', 'sort', 'direction', 'page', 'estado_plazo', 'estado_actualizacion', 'periodicidad', 'responsable']) as $key => $value)
                        @if (is_array($value))
                            @foreach ($value as $item)<input type="hidden" name="{{ $key }}[]" value="{{ $item }}">@endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <div class="exec-drill-controls__search">
                        <label for="drill-down-search">Buscar en el universo</label>
                        <div class="exec-drill-controls__search-row">
                            <input id="drill-down-search" type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Indicador, temática o descripción">
                            @unless ($seguimiento)<button type="submit" class="exec-filter-button">Buscar</button>@endunless
                            @if ($filters['buscar'])
                                <a class="exec-drill-controls__clear" href="{{ request()->fullUrlWithQuery(['buscar' => null, 'page' => null]) }}">Limpiar</a>
                            @endif
                        </div>
                    </div>
                    <div class="exec-drill-controls__sort">
                        <label for="drill-down-sort">Ordenar por</label>
                        <select id="drill-down-sort" name="sort" onchange="this.form.submit()">
                            <option value="prioridad" @selected(request('sort', 'prioridad') === 'prioridad')>Prioridad</option>
                            <option value="nombre" @selected(request('sort') === 'nombre')>Indicador</option>
                            <option value="institucion" @selected(request('sort') === 'institucion')>Institución</option>
                            <option value="avance" @selected(request('sort') === 'avance')>Avance</option>
                            @if ($seguimiento)<option value="fecha" @selected(request('sort') === 'fecha')>Fecha prevista</option>@endif
                        </select>
                    </div>
                    @if ($seguimiento)
                        <div class="tracking-filter"><label for="estado-plazo">Estado de plazo</label><select id="estado-plazo" name="estado_plazo"><option value="">Todos</option>@foreach (['Vencido', 'Vence hoy', 'Próximo', 'Programado', 'Sin fecha confiable'] as $estado)<option value="{{ $estado }}" @selected(request('estado_plazo') === $estado)>{{ $estado }}</option>@endforeach</select></div>
                        <div class="tracking-filter"><label for="estado-actualizacion">Estado de actualización</label><select id="estado-actualizacion" name="estado_actualizacion"><option value="">Todos</option>@foreach (['Desactualizado', 'Vence hoy', 'Dentro del plazo', 'Actualizado', 'Dato del año disponible; periodo sin verificar', 'Información insuficiente'] as $estado)<option value="{{ $estado }}" @selected(request('estado_actualizacion') === $estado)>{{ $estado }}</option>@endforeach</select></div>
                        <div class="tracking-filter"><label for="periodicidad-seguimiento">Periodicidad</label><select id="periodicidad-seguimiento" name="periodicidad"><option value="">Todas</option>@foreach ($periodicidades as $periodicidad)<option value="{{ $periodicidad }}" @selected(request('periodicidad') === $periodicidad)>{{ $periodicidad }}</option>@endforeach</select></div>
                        <div class="tracking-filter"><label for="responsable-seguimiento">Responsable</label><select id="responsable-seguimiento" name="responsable"><option value="">Todos</option>@foreach ($responsables as $responsable)<option value="{{ $responsable }}" @selected(request('responsable') === $responsable)>{{ $responsable }}</option>@endforeach</select></div>
                        <button type="submit" class="exec-filter-button">Aplicar filtros</button>
                    @endif
                </form>
            </div>
            @if ($seguimiento)
            <div class="tracking-results" aria-label="Seguimiento de indicadores">
                @forelse ($rows as $row)
                    @php
                        $estadoClase = match ($row['estado_actualizacion']) {
                            'Desactualizado' => 'urgent',
                            'Vence hoy' => 'warning',
                            'Actualizado', 'Dentro del plazo' => 'good',
                            default => 'unknown',
                        };
                    @endphp
                    <article class="tracking-result tracking-result--{{ $estadoClase }}">
                        <div class="tracking-result__main">
                            <span class="tracking-badge tracking-badge--{{ $estadoClase }}">{{ $row['estado_actualizacion'] }}</span>
                            <h3><a href="{{ route('panel-indicadores.show', ['indicador' => $row['id'], 'plan_id' => $plan->id]) }}">{{ $row['nombre'] }}</a></h3>
                            <p>{{ $row['institucion'] }} <span aria-hidden="true">·</span> {{ $row['usuario'] }}</p>
                        </div>
                        <dl class="tracking-result__details">
                            <div><dt>Último dato · {{ $row['periodicidad'] ?: 'Sin periodicidad' }}</dt><dd>@if ($row['ultimo_dato'] !== null){{ $row['ultimo_dato'] }} {{ $row['unidad'] }} · {{ $row['ultimo_anio'] }}<small>{{ $row['ultimo_validado'] ? 'Validado' : 'Sin validar' }}</small>@else Sin dato @endif</dd></div>
                            <div><dt>Próxima actualización</dt><dd>{{ $row['fecha_prevista'] ?? 'Sin fecha' }}<small>{{ $row['fecha_fuente'] ? ($row['fecha_fuente'] === 'anio' ? 'Fecha del dato anual' : 'Fecha del indicador') : 'Sin fecha confiable' }}</small></dd></div>
                        </dl>
                        <a class="tracking-result__action" href="{{ route('panel-indicadores.show', ['indicador' => $row['id'], 'plan_id' => $plan->id]) }}" aria-label="Revisar {{ $row['nombre'] }}">Revisar <span aria-hidden="true">→</span></a>
                    </article>
                @empty
                    <div class="exec-empty">No hay indicadores que coincidan con estos filtros.</div>
                @endforelse
            </div>
            @else
            <div class="exec-priority-table-wrap">
                <table class="exec-table exec-detail-table">
                    <caption class="visually-hidden">Detalle de indicadores filtrados</caption>
                    <thead><tr><th scope="col">Indicador</th><th scope="col">Institución</th><th scope="col">Responsable</th>@if ($seguimiento)<th scope="col">Último dato</th><th scope="col">Periodicidad</th><th scope="col">Fecha prevista</th><th scope="col">Actualización</th>@else<th scope="col">Eje / programa</th><th scope="col">Estado</th><th scope="col">Avance</th>@endif<th scope="col"><span class="visually-hidden">Acción</span></th></tr></thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td data-label="Indicador"><a class="exec-table__indicator" href="{{ route('panel-indicadores.show', ['indicador' => $row['id'], 'plan_id' => $plan->id]) }}">{{ Str::limit($row['nombre'], 72) }}</a></td>
                                <td data-label="Institución">{{ Str::limit($row['institucion'], 30) }}</td>
                                <td data-label="Responsable">{{ Str::limit($row['usuario'], 26) }}</td>
                                @if ($seguimiento)
                                    <td data-label="Último dato">@if ($row['ultimo_dato'] !== null){{ $row['ultimo_dato'] }} {{ $row['unidad'] }} · {{ $row['ultimo_anio'] }}<small class="d-block">{{ $row['ultimo_validado'] ? 'Validado' : 'Sin validar' }}</small>@else Sin dato @endif</td>
                                    <td data-label="Periodicidad">{{ $row['periodicidad'] ?: 'No especificada' }}</td>
                                    <td data-label="Fecha prevista">{{ $row['fecha_prevista'] ?? 'Sin fecha' }} @if ($row['fecha_fuente'])<small class="d-block">{{ $row['fecha_fuente'] === 'anio' ? 'Dato anual' : 'Indicador' }}</small>@endif</td>
                                    <td data-label="Actualización">{{ $row['estado_actualizacion'] }}<small class="d-block">{{ $row['estado_plazo'] }}</small></td>
                                @else
                                <td data-label="Eje / programa"><small>{{ Str::limit($row['eje'], 24) }}</small><small class="d-block text-muted">{{ Str::limit($row['programa'], 30) }}</small></td>
                                <td data-label="Estado"><span class="exec-status exec-status--{{ $row['prioridad'] === 1 ? 'red' : ($row['prioridad'] === 2 ? 'sand' : 'green') }}">{{ $row['motivo'] }}</span></td>
                                <td data-label="Avance" class="exec-table__number">{{ $row['avance'] !== null ? number_format($row['avance'], 1) . '%' : 'N/D' }}</td>
                                @endif
                                <td data-label="Acción" class="text-end"><a class="exec-table__action" href="{{ route('panel-indicadores.show', ['indicador' => $row['id'], 'plan_id' => $plan->id]) }}">Abrir <span aria-hidden="true">→</span></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $seguimiento ? 8 : 7 }}"><div class="exec-empty">No hay indicadores que coincidan con estos filtros.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif
            @if ($rows->hasPages())
                <div class="exec-pagination">{{ $rows->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>

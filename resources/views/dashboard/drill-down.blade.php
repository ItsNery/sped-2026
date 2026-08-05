<x-app-layout>
    @section('title', 'Detalle de indicadores')
    <x-slot name="header">
        <div class="exec-header">
            <div>
                <span class="exec-eyebrow">Drill-down del centro de mando</span>
                <h2 class="exec-header__title">Indicadores filtrados</h2>
            </div>
            <span class="exec-header__plan">{{ $plan->nombre }}</span>
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
            <a class="exec-filter-button" href="{{ route('dashboard') }}">Volver al dashboard</a>
        </div>

        <section class="exec-section">
            <div class="exec-section__heading">
                <div>
                    <span class="exec-eyebrow">Detalle operativo</span>
                    <h2>Indicadores y responsables</h2>
                </div>
                <form method="GET" class="exec-sort-form">
                    @foreach (request()->except(['sort', 'direction', 'page']) as $key => $value)
                        @if (is_array($value))
                            @foreach ($value as $item)<input type="hidden" name="{{ $key }}[]" value="{{ $item }}">@endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label>Ordenar
                        <select name="sort" onchange="this.form.submit()">
                            <option value="prioridad" @selected(request('sort', 'prioridad') === 'prioridad')>Prioridad</option>
                            <option value="nombre" @selected(request('sort') === 'nombre')>Indicador</option>
                            <option value="institucion" @selected(request('sort') === 'institucion')>Institución</option>
                            <option value="avance" @selected(request('sort') === 'avance')>Avance</option>
                        </select>
                    </label>
                </form>
            </div>
            <div class="exec-priority-table-wrap">
                <table class="exec-table exec-detail-table">
                    <caption class="visually-hidden">Detalle de indicadores filtrados</caption>
                    <thead><tr><th scope="col">Indicador</th><th scope="col">Institución</th><th scope="col">Responsable</th><th scope="col">Eje / programa</th><th scope="col">Estado</th><th scope="col">Avance</th><th scope="col"><span class="visually-hidden">Acción</span></th></tr></thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td data-label="Indicador"><a class="exec-table__indicator" href="{{ route('panel-indicadores.show', $row['id']) }}">{{ Str::limit($row['nombre'], 72) }}</a></td>
                                <td data-label="Institución">{{ Str::limit($row['institucion'], 30) }}</td>
                                <td data-label="Responsable">{{ Str::limit($row['usuario'], 26) }}</td>
                                <td data-label="Eje / programa"><small>{{ Str::limit($row['eje'], 24) }}</small><small class="d-block text-muted">{{ Str::limit($row['programa'], 30) }}</small></td>
                                <td data-label="Estado"><span class="exec-status exec-status--{{ $row['prioridad'] === 1 ? 'red' : ($row['prioridad'] === 2 ? 'sand' : 'green') }}">{{ $row['motivo'] }}</span></td>
                                <td data-label="Avance" class="exec-table__number">{{ $row['avance'] !== null ? number_format($row['avance'], 1) . '%' : 'N/D' }}</td>
                                <td data-label="Acción" class="text-end"><a class="exec-table__action" href="{{ route('panel-indicadores.show', $row['id']) }}">Abrir <span aria-hidden="true">→</span></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="exec-empty">No hay indicadores que coincidan con estos filtros.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($rows->hasPages())
                <div class="exec-pagination">{{ $rows->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>

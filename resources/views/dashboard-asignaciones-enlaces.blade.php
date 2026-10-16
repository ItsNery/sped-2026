<x-app-layout>
    @section('title', 'Asignación de enlaces')
    <x-slot name="header">
        <div class="exec-header">
            <div>
                <span class="exec-eyebrow">Responsabilidad operativa</span>
                <h2 class="exec-header__title">Asignación de enlaces</h2>
            </div>
            <span class="exec-header__plan">{{ $plan->nombre }}</span>
        </div>
    </x-slot>

    <div class="exec-dashboard">
        <section class="exec-section" aria-labelledby="liaison-assignments-title">
            <div class="exec-section__heading">
                <div>
                    <span class="exec-eyebrow">Consulta detallada</span>
                    <h1 id="liaison-assignments-title">Indicadores asignados a enlaces</h1>
                </div>
                <a class="exec-table__action" href="{{ route('dashboard', ['plan_id' => $plan->id]) }}">
                    Volver al dashboard <span aria-hidden="true">←</span>
                </a>
            </div>
            <div class="row g-3 mb-4" aria-label="Filtros de indicadores asignados">
                <div class="col-md-6">
                    <label class="form-label" for="enlace-filter">Enlace</label>
                    <select id="enlace-filter" class="form-select">
                        <option value="">Todos los enlaces</option>
                        @foreach ($asignacionesEnlaces as $enlace)
                            <option value="{{ $enlace->id }}">{{ $enlace->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="dependencia-filter">Dependencia</label>
                    <select id="dependencia-filter" class="form-select" disabled>
                        <option value="">Selecciona primero un enlace</option>
                    </select>
                </div>
            </div>
            <div class="exec-priority-table-wrap">
                <table id="liaison-assignments-table" class="exec-table" style="width:100%">
                    <caption class="visually-hidden">Indicadores asignados a enlaces y dependencias</caption>
                    <thead>
                        <tr>
                            <th scope="col">Indicador</th>
                            <th scope="col">Dependencia</th>
                            <th scope="col">Enlace(s)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($indicadoresAsignados as $indicador)
                            <tr data-enlace-ids="{{ $indicador['enlace_ids']->implode(',') }}"
                                data-dependencia-id="{{ $indicador['institucion_id'] }}">
                                <td data-label="Indicador">
                                    <a class="exec-table__indicator"
                                        href="{{ route('panel-indicadores.show', $indicador['slug']) }}">
                                        {{ $indicador['nombre'] }}
                                    </a>
                                </td>
                                <td data-label="Dependencia">{{ $indicador['institucion'] }}</td>
                                <td data-label="Enlace(s)">
                                    @foreach ($indicador['enlaces'] as $enlace)
                                        <span class="badge bg-secondary me-1 mb-1">{{ $enlace }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No hay indicadores asignados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof $ === 'undefined' || !$.fn.DataTable) return;

                const enlaceFilter = document.getElementById('enlace-filter');
                const dependenciaFilter = document.getElementById('dependencia-filter');
                const table = $('#liaison-assignments-table').DataTable({
                    pagingType: 'simple_numbers',
                    pageLength: 25,
                    order: [],
                    language: {
                        search: 'Buscar indicador:',
                        lengthMenu: 'Mostrar _MENU_ indicadores',
                        info: 'Mostrando _START_ a _END_ de _TOTAL_ indicadores',
                        paginate: {
                            previous: 'Anterior',
                            next: 'Siguiente'
                        }
                    }
                });

                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'liaison-assignments-table') return true;

                    const row = settings.aoData[dataIndex].nTr;
                    const enlaceIds = (row.dataset.enlaceIds || '').split(',');

                    return (!enlaceFilter.value || enlaceIds.includes(enlaceFilter.value)) &&
                        (!dependenciaFilter.value || row.dataset.dependenciaId === dependenciaFilter.value);
                });

                enlaceFilter.addEventListener('change', function() {
                    dependenciaFilter.replaceChildren(new Option(
                        this.value ? 'Cargando dependencias...' : 'Selecciona primero un enlace', ''
                    ));
                    dependenciaFilter.disabled = true;

                    if (!this.value) {
                        table.draw();
                        return;
                    }

                    fetch(@json(route('dashboard.asignaciones-enlaces.dependencias')) + '?enlace_id=' + encodeURIComponent(this.value), {
                            headers: {
                                Accept: 'application/json'
                            }
                        })
                        .then((response) => {
                            if (!response.ok) throw new Error('No fue posible cargar las dependencias.');
                            return response.json();
                        })
                        .then((data) => {
                            dependenciaFilter.replaceChildren(
                                new Option('Todas las dependencias', ''),
                                ...data.dependencias.map((dependencia) => new Option(dependencia.nombre,
                                    dependencia.id))
                            );
                            dependenciaFilter.disabled = false;
                        })
                        .catch(() => {
                            dependenciaFilter.replaceChildren(new Option('No fue posible cargar dependencias', ''));
                        })
                        .finally(() => table.draw());
                });

                dependenciaFilter.addEventListener('change', () => table.draw());
            });
        </script>
    @endpush
</x-app-layout>

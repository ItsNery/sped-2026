<x-app-layout>
    @section('title', 'Reportes por Programa Derivado')

    <x-slot name="header">
        <div class="exec-header admin-index-header">
            <div>
                <span class="exec-eyebrow">Consulta consolidada</span>
                <h2 class="exec-header__title">Reportes por programa derivado</h2>
            </div>
            <span class="exec-header__plan">PED activo</span>
        </div>
    </x-slot>

    <div class="admin-index">
        <div class="contenedor-principal admin-index__surface mx-auto">
            <div class="admin-index__heading">
                <div>
                    <span class="exec-eyebrow">Paso 1</span>
                    <h1>Seleccione el tipo de programa</h1>
                </div>
            </div>

            <div class="row g-3 mb-4" role="group" aria-label="Tipos de programa derivado">
                @foreach (['sectoriales' => 'Sectoriales', 'especiales' => 'Especiales', 'regionales' => 'Regionales', 'institucionales' => 'Institucionales'] as $tipo => $nombre)
                    <div class="col-12 col-md-6 col-xl-3">
                        <button type="button" class="w-100 h-100 text-start border rounded p-3 bg-white reporte-tipo"
                            data-tipo="{{ $tipo }}" aria-controls="programas-{{ $tipo }}">
                            <span class="d-block fw-bold text-dark">{{ $nombre }}</span>
                            <span class="text-muted small">{{ $programasPorTipo[$tipo]->count() }} programas disponibles</span>
                        </button>
                    </div>
                @endforeach
            </div>

            <section id="programas-contenedor" class="d-none" aria-live="polite">
                <div class="admin-index__heading mb-3">
                    <div>
                        <span class="exec-eyebrow">Paso 2</span>
                        <h2 id="programas-titulo" class="h4 mb-0">Programas</h2>
                    </div>
                    <label class="mb-0" for="buscar-programa">
                        <span class="visually-hidden">Buscar programa</span>
                        <input id="buscar-programa" type="search" class="form-control" placeholder="Buscar programa">
                    </label>
                </div>

                @foreach ($programasPorTipo as $tipo => $programas)
                    <div id="programas-{{ $tipo }}" class="reporte-programas-lista d-none">
                        <div class="table-responsive admin-index-table-wrap">
                            <table class="table table-striped table-bordered admin-index-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Programa</th>
                                        <th>Indicadores</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($programas as $programa)
                                        <tr data-programa-nombre="{{ strtolower($programa->nombre) }}">
                                            <td>{{ $programa->nombre }}</td>
                                            <td>{{ $programa->indicadores_count }}</td>
                                            <td class="text-center">
                                                <a class="admin-index-table-action admin-index-table-action--review"
                                                    href="{{ route('panel-reportes-programas.show', [$tipo, $programa]) }}"
                                                    target="_blank" rel="noopener">Generar</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-muted">No hay programas para el PED activo.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </section>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('programas-contenedor');
                const title = document.getElementById('programas-titulo');
                const search = document.getElementById('buscar-programa');
                const lists = document.querySelectorAll('.reporte-programas-lista');
                const buttons = document.querySelectorAll('.reporte-tipo');

                buttons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        const type = button.dataset.tipo;
                        lists.forEach(function(list) {
                            list.classList.toggle('d-none', list.id !== `programas-${type}`);
                        });
                        buttons.forEach(function(item) {
                            item.classList.toggle('border-dark', item === button);
                            item.setAttribute('aria-pressed', item === button ? 'true' : 'false');
                        });
                        title.textContent = `Programas ${button.querySelector('.fw-bold').textContent}`;
                        search.value = '';
                        document.querySelectorAll(`#programas-${type} tbody tr`).forEach(function(row) {
                            row.classList.remove('d-none');
                        });
                        container.classList.remove('d-none');
                    });
                });

                search.addEventListener('input', function() {
                    const visibleList = document.querySelector('.reporte-programas-lista:not(.d-none)');
                    if (!visibleList) {
                        return;
                    }

                    const term = search.value.toLocaleLowerCase();
                    visibleList.querySelectorAll('tbody tr[data-programa-nombre]').forEach(function(row) {
                        row.classList.toggle('d-none', !row.dataset.programaNombre.includes(term));
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>

<x-app-layout>
    @section('title', 'Indicadores municipales')

    <x-slot name="header">
        <div class="exec-header admin-index-header">
            <div>
                <span class="exec-eyebrow">Seguimiento municipal</span>
                <h2 class="exec-header__title">{{ $municipioConvenio->municipio->nombre }}</h2>
            </div>
            <span class="exec-header__plan">Indicadores públicos</span>
        </div>
    </x-slot>

    <div class="admin-index municipality-indicators-index">
        <div class="contenedor-principal admin-index__surface mx-auto">
            <div class="admin-index__heading">
                <div>
                    <span class="exec-eyebrow">Indicadores vinculados</span>
                    <h1>Indicadores de {{ $municipioConvenio->municipio->nombre }}</h1>
                </div>
                <span class="admin-index__count">
                    {{ $indicadores->count() }} {{ $indicadores->count() === 1 ? 'registro' : 'registros' }}
                </span>
            </div>

            <section class="municipality-indicators-index__summary" aria-label="Resumen del municipio">
                <img src="{{ $municipioConvenio->icono }}"
                    alt="Ícono del municipio de {{ $municipioConvenio->municipio->nombre }}"
                    class="municipality-indicators-index__icon">
                <div class="municipality-indicators-index__summary-content">
                    <span class="exec-eyebrow">Objetivo del convenio</span>
                    <p>{{ $municipioConvenio->objetivo }}</p>
                </div>
                <div class="municipality-indicators-index__summary-actions">
                    <a href="{{ route('panel-municipios-convenio.index') }}"
                        class="admin-index-table-action admin-index-table-action--document">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                        Volver a municipios
                    </a>
                    <a href="{{ $municipioConvenio->convenio }}" target="_blank" rel="noopener noreferrer"
                        class="admin-index-table-action admin-index-table-action--document">
                        <i class="fa-regular fa-file-pdf" aria-hidden="true"></i>
                        Ver convenio
                    </a>
                </div>
            </section>

            <div class="table-responsive admin-index-table-wrap">
                <table id="tabla-indicadores-municipio" class="table table-striped table-bordered admin-index-table">
                    <thead>
                        <tr>
                            <th scope="col">Indicador</th>
                            <th scope="col">Temática</th>
                            <th scope="col">Periodicidad</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($indicadores as $indicador)
                            <tr>
                                <td>
                                    <a href="{{ route('indicadores.show_municipal', $indicador->id) }}" rel="noopener" target="_self">
                                        {{ $indicador->indicador }}
                                    </a>
                                </td>
                                <td>{{ $indicador->tematica ?: 'Sin temática' }}</td>
                                <td>{{ $indicador->periodicidad?->nombre ?? 'Sin periodicidad' }}</td>
                                <td>
                                    @if ($indicador->validado == 1)
                                        <span
                                            class="admin-index-table-action admin-index-table-action--validated">Validado</span>
                                    @elseif (is_null($indicador->validado))
                                        <span class="admin-index-table-action admin-index-table-action--updated">Sin
                                            actualizar</span>
                                    @else
                                        <span class="admin-index-table-action admin-index-table-action--pending">Pendiente de
                                            validación</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                $('#tabla-indicadores-municipio').DataTable({
                    pagingType: 'simple_numbers',
                    order: [],
                    dom: 'frtip',
                    language: {
                        search: 'Buscar:',
                        info: 'Mostrando _START_ a _END_ de _TOTAL_ entradas',
                        infoEmpty: 'No hay indicadores para mostrar',
                        zeroRecords: 'No se encontraron indicadores',
                        paginate: { previous: 'Anterior', next: 'Siguiente' }
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
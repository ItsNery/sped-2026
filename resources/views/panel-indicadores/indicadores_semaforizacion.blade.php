<x-app-layout>
    @section('title', 'Indicadores con semaforización ' . $categoria)
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <h2>Indicadores con Semaforización: {{ $categoria }}</h2>
        </h2>
    </x-slot>
    <div class="container contenedor-principal py-3 table-responsive">
        @if ($indicadores->isEmpty())
            <p class="text-muted">No hay indicadores {{ $categoria }}s.</p>
        @else
            <table id="tabla-indicadores-semaforo" class="table table-striped">
                <thead>
                    <tr>
                        <th>Indicador</th>
                        <th>Año Línea Base</th>
                        <th>Valor Línea Base</th>
                        <th>Año del Dato</th>
                        <th>Valor Actual</th>
                        <th>Meta 2024</th>
                        <th>Avance (%)</th>
                        <th>Progreso</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($indicadores as $indicador)
                        <tr>
                            <td><a
                                    href="{{ route('panel-indicadores.show', $indicador->id) }}">{{ $indicador->nombre }}</a>
                            </td>
                            <td>{{ $indicador->linea_base ?? 'N/A' }}</td>
                            <td>{{ $indicador->dato_linea_base !== null ? number_format($indicador->dato_linea_base, 2, '.', ',') : 'N/A' }}
                            </td>
                            <td>{{ $indicador->anio_ultimo_dato ?? 'N/A' }}</td>
                            <td>{{ $indicador->ultimo_dato !== null ? number_format($indicador->ultimo_dato, 2, '.', ',') : 'N/A' }}
                            </td>
                            <td>{{ $indicador->meta_2024 }}</td>
                            <td>{{ $indicador->avance ? number_format($indicador->avance, 2) . '%' : 'N/A' }}</td>
                            <td>
                                @if ($indicador->avance !== null)
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: {{ $indicador->avance }}%; background-color:var(--bg-badge-{{ $indicador->semaforizacion }});"
                                            aria-valuenow="{{ $indicador->avance }}" aria-valuemin="0"
                                            aria-valuemax="100">
                                            {{ number_format($indicador->avance, 2) }}%
                                        </div>
                                    </div>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('panel-indicadores.show', $indicador->id) }}" target="_blank"
                                    title="Ficha del indicador {{ $indicador->nombre }}"
                                    class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> <!-- Ícono de FontAwesome -->
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Institución</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($indicadores as $indicador)
                        <tr>
                            <td>{{ $indicador->nombre }}</td>
                            <td>{{ $indicador->institucion->nombre }}</td>
                            <td>
                                <span class="badge {{ $indicador->indicador_validado ? 'bg-success' : 'bg-danger' }}">
                                    {{ $indicador->indicador_validado ? 'Validado' : 'No Validado' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table> --}}
        @endif

        <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Volver</a>
    </div>
    <script>
        $(document).ready(function() {
            $('#tabla-indicadores-semaforo').DataTable({
                "pagingType": "simple_numbers",
                "order": [],
                responsive: true,
                lengthMenu: [5, 10, 25, 50],
                pageLength: 10,
                language: {
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    }
                },
                dom: 'Bflrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-primary',
                        title: "Indicadores con semaforización {{ $categoria }} | Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo",
                        bom: true,
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger',
                        orientation: 'landscape',
                        title: "Indicadores con semaforización {{ $categoria }} | Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo",
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'copy', // Copiar al portapapeles
                        className: 'btn btn-info',
                        text: '<i class="fas fa-copy"></i> Copiar',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    }
                ]
            });
        });
    </script>
</x-app-layout>

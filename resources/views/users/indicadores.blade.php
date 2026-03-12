<x-app-layout>
    @section('title', 'Indicadores ' . ($filtro === 'validados' ? 'Validados' : 'No Validados') . ' de ' .
        $usuario->name)
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Indicadores {{ $filtro === 'validados' ? 'Validados' : 'No Validados' }} de
                {{ $usuario->name }}
            </h2>
        </x-slot>
        <div class="container contenedor-principal py-3 table-responsive">
            @if ($indicadores->isEmpty())
                <p class="text-muted">No hay indicadores {{ $filtro === 'validados' ? 'validados' : 'no validados' }}.</p>
            @else
                <table id="tabla-validados" class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Institución</th>
                            <th scope="col">Periodicidad</th>
                            <th scope="col">Semáforo</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($indicadores as $indicador)
                            <tr>
                                <td>
                                    <a href="{{ route('panel-indicadores.show', $indicador->id) }}">
                                        {{ $indicador->nombre }}
                                    </a>
                                </td>
                                <td>{{ $indicador->institucion->nombre }}</td>
                                <td>{{ $indicador->periodicidad }}</td>
                                <td>
                                    <p class="d-flex justify-content-center">
                                        @if ($indicador->semaforizacion === 'Excedido')
                                            <span class="badge bg-excedido">
                                                {{ $indicador->semaforizacion }}
                                            </span>
                                        @elseif ($indicador->semaforizacion === 'Aceptable')
                                            <span class="badge bg-aceptable">
                                                {{ $indicador->semaforizacion }}
                                            </span>
                                        @elseif ($indicador->semaforizacion === 'Moderado')
                                            <span class="badge bg-moderado">
                                                {{ $indicador->semaforizacion }}
                                            </span>
                                        @elseif ($indicador->semaforizacion === 'Insuficiente')
                                            <span class="badge bg-insuficiente">
                                                {{ $indicador->semaforizacion }}
                                            </span>
                                        @else
                                            <span class="badge text-bg-secondary">
                                                {{ $indicador->semaforizacion }}
                                            </span>
                                        @endif
                                    </p>
                                </td>
                                <td>
                                    <span class="badge {{ $indicador->indicador_validado ? 'bg-success' : 'bg-danger' }}">
                                        {{ $indicador->indicador_validado ? 'Validado' : 'No Validado' }}
                                    </span>
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
                $('#tabla-validados').DataTable({
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
                            title: "Indicadores {{ $filtro === 'validados' ? 'validados' : 'no validados' }} de {{ $usuario->name }} | Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo",
                            bom: true,

                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fas fa-file-pdf"></i> PDF',
                            className: 'btn btn-danger',
                            orientation: 'portrait',
                            title: "Indicadores {{ $filtro === 'validados' ? 'validados' : 'no validados' }} de {{ $usuario->name }} | Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo",
                            pageSize: 'A4',
                        },
                        {
                            extend: 'copy', // Copiar al portapapeles
                            className: 'btn btn-info',
                            text: '<i class="fas fa-copy"></i> Copiar',
                        }
                    ]
                });
            });
        </script>
    </x-app-layout>

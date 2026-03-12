<x-indicador-municipal-layout>
    @section('title', 'Administración Indicadores Municipales: Inicio')
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('Reporte') }}
        </h2>
    </x-slot>
    <div class="container contenedor-tarjetas">
        <div class="contenedor-principal mx-auto">
            <div class="encabezado-lista">
                <h2>Listado de Indicadores de {{ $municipio_nombre }}</h2>
            </div>
            <div class="container table-responsive" id="contenedor-tabla-indicadores">
                <table id="tabla-indicadores" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Indicador</th>
                            <th>Instrumento</th>
                            <th>Eje</th>
                            <th>Dependencia</th>
                            <th>2016</th>
                            <th>2017</th>
                            <th>2018</th>
                            <th>2019</th>
                            <th>2020</th>
                            <th>2021</th>
                            <th>2022</th>
                            <th>2023</th>
                            <th>2024</th>
                            <th>Meta 2027</th>
                            <th>Periodicidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($indicadores as $indicador)
                            <tr>
                                <td>
                                    {{ $indicador->indicador }}
                                </td>
                                <td>
                                    {{ $indicador->instrumento }}
                                </td>
                                <td>
                                    {{ $indicador->eje_indicador }}
                                </td>
                                <td>
                                    {{ $indicador->dependencia }}
                                </td>
                                <td>
                                    {{ $indicador->valoresPorAño['dato_2016'] ?? 'N/D' }}
                                </td>
                                <td>
                                    {{ $indicador->valoresPorAño['dato_2017'] ?? 'N/D' }}
                                </td>
                                <td>
                                    {{ $indicador->valoresPorAño['dato_2018'] ?? 'N/D' }}
                                </td>
                                <td>
                                    {{ $indicador->valoresPorAño['dato_2019'] ?? 'N/D' }}
                                </td>
                                <td>
                                    {{ $indicador->valoresPorAño['dato_2020'] ?? 'N/D' }}
                                </td>
                                <td>
                                    {{ $indicador->valoresPorAño['dato_2021'] ?? 'N/D' }}
                                </td>
                                <td>
                                    {{ $indicador->valoresPorAño['dato_2022'] ?? 'N/D' }}
                                </td>
                                <td>
                                    {{ $indicador->valoresPorAño['dato_2023'] ?? 'N/D' }}
                                </td>
                                <td>
                                    {{ $indicador->valoresPorAño['dato_2024'] ?? 'N/D' }}
                                </td>
                                <td>
                                    {{ $indicador->meta_2024 }}
                                </td>
                                <td>
                                    {{ $indicador->periodicidad->nombre }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Indicador</th>
                            <th>Instrumento</th>
                            <th>Eje</th>
                            <th>Dependencia</th>
                            <th>2016</th>
                            <th>2017</th>
                            <th>2018</th>
                            <th>2019</th>
                            <th>2020</th>
                            <th>2021</th>
                            <th>2022</th>
                            <th>2023</th>
                            <th>2024</th>
                            <th>Meta 2030</th>
                            <th>Periodicidad</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#tabla-indicadores').DataTable({
                "pagingType": "simple_numbers",
                "order": [],
                dom: 'Bfrtip', // Define la disposición para que aparezcan los botones
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Excel',
                        className: 'btn btn-success'
                    },
                    {
                        extend: 'csvHtml5',
                        text: 'CSV',
                        className: 'btn btn-primary'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        title: 'Reporte de los Indicadores de {{ $municipio_nombre }} ',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        className: 'btn btn-danger',
                        customize: function(doc) {
                            doc.footer = function(currentPage, pageCount) {
                                return {
                                    text: `Página ${currentPage} de ${pageCount}`,
                                    alignment: 'center',
                                    margin: [0, 10, 0, 0]
                                };
                            };
                        }
                    },
                    {
                        extend: 'copy',
                        className: 'btn btn-info',
                        text: 'Copiar',
                    }
                ],
                "language": {
                    "search": "Buscar:",
                    "lengthMenu": "Mostrar _MENU_ entradas",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                    "paginate": {
                        "previous": "Anterior",
                        "next": "Siguiente"
                    }
                }
            });
        });
    </script>
</x-indicador-municipal-layout>

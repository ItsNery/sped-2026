<x-app-layout>
    @section('title', 'Registros: Inicio')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registros') }}
        </h2>
    </x-slot>

    <link rel="stylesheet" href="{{ asset('css/choices.min.css') }}">
    <script src="{{ asset('js/choices.min.js') }}"></script>

    <div class="container mx-auto">
        <div class="contenedor-principal mx-auto">
            <div class="encabezado-lista my-2">
                <h2>Logs del SPED</h2>
            </div>
            <div class="table-responsive">
                <table id="tabla-registros" class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Fecha y hora</th>
                            <th>Usuario</th>
                            <th>Tabla</th>
                            <th>Campo</th>
                            <th>Acción</th>

                        </tr>
                    </thead>
                    <tbody>


                        {{-- Los datos se cargarán mediante DataTables Server-Side --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Inicialización de DataTables Server-Side
            $('#tabla-registros').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('panel-logs.index') }}",
                columns: [{
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'usuario',
                        name: 'usuario'
                    },
                    {
                        data: 'tabla',
                        name: 'tabla'
                    },
                    {
                        data: 'columna_display',
                        name: 'columna',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'accion',
                        name: 'accion'
                    }
                ],
                pagingType: "simple_numbers",
                order: [
                    [0, 'desc']
                ], // Ordenar por fecha por defecto
                language: {
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ entradas",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                    paginate: {
                        previous: "Anterior",
                        next: "Siguiente",
                    },
                },
            });
        });
    </script>
</x-app-layout>
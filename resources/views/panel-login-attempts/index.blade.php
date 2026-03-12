<x-app-layout>
    @section('title', 'Accesos: Historial')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Historial de Accesos') }}
        </h2>
    </x-slot>

    <div class="container mx-auto">
        <div class="contenedor-principal mx-auto">
            <div class="encabezado-lista my-2">
                <h2>Historial de Intentos de Acceso</h2>
            </div>
            <div class="table-responsive">
                <table id="tabla-accesos" class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Fecha y hora</th>
                            <th>Usuario / Email</th>
                            <th>Dirección IP</th>
                            <th>Dispositivo (User Agent)</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables Server-Side --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            $('#tabla-accesos').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('panel-accesos.index') }}",
                columns: [{
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'usuario',
                        name: 'usuario'
                    },
                    {
                        data: 'ip_address',
                        name: 'ip_address'
                    },
                    {
                        data: 'user_agent',
                        name: 'user_agent'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    }
                ],
                pagingType: "simple_numbers",
                order: [
                    [0, 'desc']
                ],
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
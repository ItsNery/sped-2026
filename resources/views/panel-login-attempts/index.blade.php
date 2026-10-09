<x-app-layout>
    @section('title', 'Accesos: Historial')
    <x-slot name="header">
        <div class="exec-header admin-index-header">
            <div>
                <span class="exec-eyebrow">Bitácora de seguridad</span>
                <h2 class="exec-header__title">Historial de accesos</h2>
            </div>
            <span class="exec-header__plan">Intentos de inicio de sesión</span>
        </div>
    </x-slot>

    <div class="admin-index">
        <div class="contenedor-principal admin-index__surface mx-auto">
            <div class="admin-index__heading">
                <div>
                    <span class="exec-eyebrow">Registros de autenticación</span>
                    <h1>Intentos de acceso</h1>
                </div>
            </div>

            <div class="table-responsive admin-index-table-wrap">
                <table id="tabla-accesos" class="table table-striped table-bordered admin-index-table" style="width:100%">
                    <thead>
                        <tr>
                            <th scope="col">Fecha y hora</th>
                            <th scope="col">Usuario / Email</th>
                            <th scope="col">Dirección IP</th>
                            <th scope="col">Dispositivo (User Agent)</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables Server-Side --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const language = {
                    search: 'Buscar:',
                    lengthMenu: 'Mostrar _MENU_ entradas',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ entradas',
                    paginate: { previous: 'Anterior', next: 'Siguiente' }
                };
                const buttons = [
                    { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'admin-index-export-button admin-index-export-button--excel' },
                    { extend: 'csvHtml5', text: '<i class="fas fa-file-csv"></i> CSV', className: 'admin-index-export-button admin-index-export-button--csv' },
                    { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'admin-index-export-button admin-index-export-button--pdf' },
                    { extend: 'copy', text: '<i class="fas fa-copy"></i> Copiar', className: 'admin-index-export-button admin-index-export-button--copy' }
                ];

                $('#tabla-accesos').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('panel-accesos.index') }}",
                    columns: [
                        { data: 'created_at', name: 'created_at' },
                        { data: 'usuario', name: 'usuario' },
                        { data: 'ip_address', name: 'ip_address' },
                        { data: 'user_agent', name: 'user_agent' },
                        { data: 'status', name: 'status' }
                    ],
                    pagingType: 'simple_numbers',
                    order: [[0, 'desc']],
                    dom: 'Bfrtip',
                    buttons: buttons,
                    language: language
                });
            });
        </script>
    @endpush
</x-app-layout>

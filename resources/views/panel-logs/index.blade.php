<x-app-layout>
    @section('title', 'Registros: Inicio')
    @section('jss-inicial')
        <link rel="stylesheet" href="{{ asset('css/choices.min.css') }}">
        <script src="{{ asset('js/choices.min.js') }}"></script>
    @endsection
    <x-slot name="header">
        <div class="exec-header admin-index-header">
            <div>
                <span class="exec-eyebrow">Bitácora del sistema</span>
                <h2 class="exec-header__title">Registros de cambios</h2>
            </div>
            <span class="exec-header__plan">Auditoría y trazabilidad</span>
        </div>
    </x-slot>

    <div class="admin-index">
        <div class="contenedor-principal admin-index__surface mx-auto">
            <div class="admin-index__heading">
                <div>
                    <span class="exec-eyebrow">Historial de modificaciones</span>
                    <h1>Logs del SPED</h1>
                </div>
            </div>

            <div class="table-responsive admin-index-table-wrap">
                <table id="tabla-registros" class="table table-striped table-bordered admin-index-table" style="width:100%">
                    <thead>
                        <tr>
                            <th scope="col">Fecha y hora</th>
                            <th scope="col">Usuario</th>
                            <th scope="col">Tabla</th>
                            <th scope="col">Campo</th>
                            <th scope="col">Acción</th>
                            <th scope="col">Valor anterior</th>
                            <th scope="col">Valor nuevo</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Los datos se cargarán mediante DataTables Server-Side --}}
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

                $('#tabla-registros').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('panel-logs.index') }}",
                    columns: [
                        { data: 'created_at', name: 'created_at' },
                        { data: 'usuario', name: 'usuario' },
                        { data: 'tabla', name: 'tabla' },
                        { data: 'columna_display', name: 'columna', orderable: true, searchable: true },
                        { data: 'accion', name: 'accion' },
                        { data: 'valor_anterior', name: 'valor_anterior', defaultContent: '' },
                        { data: 'valor_nuevo', name: 'valor_nuevo', defaultContent: '' }
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

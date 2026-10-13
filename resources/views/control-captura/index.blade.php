<x-app-layout>
    @section('title', 'Control de Captura')
    <x-slot name="header">
        <div class="exec-header admin-index-header">
            <div>
                <span class="exec-eyebrow">Administración del sistema</span>
                <h2 class="exec-header__title">Control de Periodos y Cierre de Captura</h2>
            </div>
            <span class="exec-header__plan">PED: {{ $plan->nombre ?? 'Plan Estatal de Desarrollo' }}</span>
        </div>
    </x-slot>

    @if ($message = Session::get('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: '¡Operación exitosa!',
                    text: '{{ $message }}',
                    confirmButtonColor: '#3085d6'
                });
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Verifique los datos',
                    html: '{!! implode("<br>", $errors->all()) !!}',
                    confirmButtonColor: '#d33'
                });
            });
        </script>
    @endif

    <div class="admin-index pb-5">
        <div class="contenedor-principal admin-index__surface mx-auto">

            {{-- 1. Métricas Principales --}}
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-light">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase fw-bold">Dependencias</span>
                                <h3 class="fw-bold mb-0 mt-1">{{ $stats['total'] }}</h3>
                            </div>
                            <div class="rounded-circle bg-white p-3 text-primary shadow-sm">
                                <i class="fa-solid fa-building-columns fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100 p-3" style="background-color: #f0fdf4;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-success small text-uppercase fw-bold">Finalizadas (100%)</span>
                                <h3 class="fw-bold mb-0 mt-1 text-success">{{ $stats['finalizadas'] }}</h3>
                            </div>
                            <div class="rounded-circle bg-white p-3 text-success shadow-sm">
                                <i class="fa-solid fa-circle-check fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100 p-3" style="background-color: #fefce8;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-warning small text-uppercase fw-bold">En Captura</span>
                                <h3 class="fw-bold mb-0 mt-1 text-warning">{{ $stats['en_proceso'] }}</h3>
                            </div>
                            <div class="rounded-circle bg-white p-3 text-warning shadow-sm">
                                <i class="fa-solid fa-hourglass-half fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100 p-3" style="background-color: #eff6ff;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-info small text-uppercase fw-bold">Con Prórroga</span>
                                <h3 class="fw-bold mb-0 mt-1 text-info">{{ $stats['con_prorroga'] }}</h3>
                            </div>
                            <div class="rounded-circle bg-white p-3 text-info shadow-sm">
                                <i class="fa-solid fa-clock fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Configuración de Ventana de Captura General --}}
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-calendar-days text-primary fs-5"></i>
                        <h4 class="mb-0 fw-bold fs-5">Ventana General de Captura</h4>
                    </div>
                    <div>
                        @if ($periodo && $periodo->isVigente())
                            <span class="badge bg-success px-3 py-2 fs-6">
                                <i class="fa-solid fa-circle-dot me-1"></i> Periodo Abierto
                            </span>
                        @elseif ($periodo && $periodo->haVencido())
                            <span class="badge bg-danger px-3 py-2 fs-6">
                                <i class="fa-solid fa-lock me-1"></i> Plazo Vencido
                            </span>
                        @elseif ($periodo && !$periodo->activo)
                            <span class="badge bg-secondary px-3 py-2 fs-6">
                                <i class="fa-solid fa-pause me-1"></i> Captura Pausada
                            </span>
                        @else
                            <span class="badge bg-secondary px-3 py-2 fs-6">
                                Sin Periodo Restringido
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('panel-control-captura.guardarPeriodo') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="nombre" class="form-label fw-bold small text-muted">Nombre del Periodo:</label>
                                <input type="text" name="nombre" id="nombre" class="form-control"
                                    value="{{ old('nombre', $periodo->nombre ?? 'Periodo Ordinario ' . date('Y')) }}" required>
                            </div>

                            <div class="col-12 col-sm-6 col-md-3">
                                <label for="fecha_inicio" class="form-label fw-bold small text-muted">Fecha y Hora de Inicio:</label>
                                <input type="datetime-local" name="fecha_inicio" id="fecha_inicio" class="form-control"
                                    value="{{ old('fecha_inicio', $periodo ? $periodo->fecha_inicio->format('Y-m-d\TH:i') : date('Y-m-01\T00:00')) }}" required>
                            </div>

                            <div class="col-12 col-sm-6 col-md-3">
                                <label for="fecha_fin" class="form-label fw-bold small text-muted">Fecha y Hora de Cierre:</label>
                                <input type="datetime-local" name="fecha_fin" id="fecha_fin" class="form-control"
                                    value="{{ old('fecha_fin', $periodo ? $periodo->fecha_fin->format('Y-m-d\TH:i') : date('Y-12-31\T23:59')) }}" required>
                            </div>

                            <div class="col-12 col-md-2 d-flex flex-column justify-content-center">
                                <label class="form-label fw-bold small text-muted">Estado del Sistema:</label>
                                <div class="form-check form-switch pt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1"
                                        {{ old('activo', $periodo->activo ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small" for="activo">Captura Abierta</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-9">
                                <label for="descripcion" class="form-label fw-bold small text-muted">Instrucciones o Convocatoria (Opcional):</label>
                                <input type="text" name="descripcion" id="descripcion" class="form-control"
                                    placeholder="Ej. Cierre de captura del ejercicio fiscal correspondiente..."
                                    value="{{ old('descripcion', $periodo->descripcion ?? '') }}">
                            </div>

                            <div class="col-12 col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Periodo
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. Barra de Acciones Masivas --}}
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary fs-6 rounded-pill px-3 py-2" id="selectedCountBadge">0 seleccionadas</span>
                            <span class="text-muted small">Selecciona una o más dependencias para aplicar acciones en lote:</span>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button type="button" class="btn btn-success btn-sm px-3 fw-bold shadow-sm" id="btnMasivoFinalizar" disabled>
                                <i class="fa-solid fa-check-double me-1"></i> Finalizar Captura
                            </button>

                            <button type="button" class="btn btn-outline-danger btn-sm px-3 fw-bold shadow-sm" id="btnMasivoReabrir" disabled>
                                <i class="fa-solid fa-lock-open me-1"></i> Reabrir Captura
                            </button>

                            <button type="button" class="btn btn-info text-white btn-sm px-3 fw-bold shadow-sm" id="btnMasivoProrroga" disabled>
                                <i class="fa-solid fa-clock me-1"></i> Asignar Prórroga
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Formulario Oculto para Acciones Masivas --}}
            <form id="formAccionMasiva" action="{{ route('panel-control-captura.cambiarEstatusMasivo') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="accion" id="masivoAccionInput">
                <div id="masivoIdsContainer"></div>
            </form>

            {{-- 5. Tabla de Dependencias y Semáforo --}}
            <div class="admin-index__heading mb-3">
                <div>
                    <span class="exec-eyebrow">Seguimiento Institucional</span>
                    <h3 class="fs-4 fw-bold">Estatus de Enlaces de Dependencias</h3>
                </div>
                <span class="admin-index__count">{{ count($instituciones) }} dependencias</span>
            </div>

            <div class="table-responsive admin-index-table-wrap shadow-sm rounded-3 bg-white p-3">
                <table class="table table-hover align-middle admin-index-table w-100" id="table-control-captura">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">
                                <input type="checkbox" class="form-check-input" id="checkSelectAll">
                            </th>
                            <th>Dependencia</th>
                            <th>Enlace Asignado</th>
                            <th style="width: 180px;">Avance de Validación</th>
                            <th class="text-center" style="width: 150px;">Estatus</th>
                            <th class="text-center" style="width: 170px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($instituciones as $inst)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input inst-checkbox" value="{{ $inst->id }}">
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $inst->nombre }}</div>
                                    @if ($inst->siglas)
                                        <span class="badge bg-light text-secondary border">{{ $inst->siglas }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($inst->enlacePrincipal)
                                        <div class="small fw-bold text-dark">{{ $inst->enlacePrincipal->name }}</div>
                                        <div class="small text-muted">{{ $inst->enlacePrincipal->email }}</div>
                                    @else
                                        <span class="text-muted small fst-italic">Sin enlace asignado</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-between small mb-1">
                                        <span class="fw-bold text-muted">{{ $inst->indicadores_validados }} / {{ $inst->total_indicadores }} validados</span>
                                        <span class="fw-bold text-primary">{{ $inst->avancePorcentaje }}%</span>
                                    </div>
                                    <div class="progress" style="height: 7px;">
                                        <div class="progress-bar {{ $inst->avancePorcentaje === 100 ? 'bg-success' : 'bg-primary' }}"
                                            role="progressbar" style="width: {{ $inst->avancePorcentaje }}%;"
                                            aria-valuenow="{{ $inst->avancePorcentaje }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($inst->estadoVisual === 'finalizado')
                                        <span class="badge bg-success px-3 py-2 rounded-pill d-inline-flex align-items-center gap-1 shadow-sm">
                                            <i class="fa-solid fa-check"></i> Finalizado
                                        </span>
                                        @if ($inst->reportStatus?->finalizado_at)
                                            <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                                {{ $inst->reportStatus->finalizado_at->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                    @elseif ($inst->estadoVisual === 'prorroga')
                                        <span class="badge bg-info text-white px-3 py-2 rounded-pill d-inline-flex align-items-center gap-1 shadow-sm">
                                            <i class="fa-solid fa-clock"></i> Con Prórroga
                                        </span>
                                        @if ($inst->reportStatus?->prorroga_hasta)
                                            <div class="small text-primary mt-1 fw-bold" style="font-size: 0.75rem;">
                                                Hasta {{ $inst->reportStatus->prorroga_hasta->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                    @elseif ($inst->estadoVisual === 'vencido')
                                        <span class="badge bg-danger px-3 py-2 rounded-pill d-inline-flex align-items-center gap-1 shadow-sm">
                                            <i class="fa-solid fa-clock-rotate-left"></i> Plazo Vencido
                                        </span>
                                        <div class="small text-muted mt-1" style="font-size: 0.75rem;">Incompleto</div>
                                    @else
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill d-inline-flex align-items-center gap-1 shadow-sm">
                                            <i class="fa-solid fa-hourglass-half"></i> En Proceso
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        @if ($inst->esFinalizado)
                                            <form action="{{ route('panel-control-captura.cambiarEstatusIndividual', $inst->id) }}" method="POST" class="d-inline confirm-individual-form">
                                                @csrf
                                                <input type="hidden" name="accion" value="reabrir">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Reabrir captura" data-action-text="reabrir la captura para esta dependencia">
                                                    <i class="fa-solid fa-lock-open"></i> Reabrir
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('panel-control-captura.cambiarEstatusIndividual', $inst->id) }}" method="POST" class="d-inline confirm-individual-form">
                                                @csrf
                                                <input type="hidden" name="accion" value="finalizar">
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Finalizar captura administrativamente" data-action-text="finalizar la captura para esta dependencia">
                                                    <i class="fa-solid fa-check"></i> Finalizar
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-outline-info text-info btn-open-prorroga"
                                            data-id="{{ $inst->id }}"
                                            data-nombre="{{ $inst->nombre }}"
                                            data-fecha="{{ $inst->reportStatus?->prorroga_hasta ? $inst->reportStatus->prorroga_hasta->format('Y-m-d\TH:i') : '' }}"
                                            data-motivo="{{ $inst->reportStatus?->motivo_prorroga ?? '' }}"
                                            title="Asignar o editar prórroga">
                                            <i class="fa-solid fa-clock"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- Modal para Asignar Prórroga --}}
    <div class="modal fade" id="modalProrroga" tabindex="-1" aria-labelledby="modalProrrogaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('panel-control-captura.asignarProrroga') }}" method="POST" id="formProrrogaModal">
                    @csrf
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title fw-bold" id="modalProrrogaLabel">
                            <i class="fa-solid fa-clock me-1"></i> Asignar Prórroga de Captura
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-light border small text-muted mb-3" id="prorrogaTargetText">
                            Se otorgará ampliación de tiempo para captura.
                        </div>

                        <div id="prorrogaHiddenInputs"></div>

                        <div class="mb-3">
                            <label for="modal_prorroga_hasta" class="form-label fw-bold small text-muted">Fecha y Hora Límite de Prórroga:</label>
                            <input type="datetime-local" class="form-control" id="modal_prorroga_hasta" name="prorroga_hasta" required>
                        </div>

                        <div class="mb-3">
                            <label for="modal_motivo_prorroga" class="form-label fw-bold small text-muted">Motivo / Número de Oficio (Opcional):</label>
                            <input type="text" class="form-control" id="modal_motivo_prorroga" name="motivo_prorroga" placeholder="Ej. Solicitud por oficio SEC-045/2026">
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="reabrir_si_finalizado" name="reabrir_si_finalizado" value="1" checked>
                            <label class="form-check-label small fw-bold" for="reabrir_si_finalizado">Reabrir edición si la dependencia ya estaba finalizada</label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info text-white btn-sm fw-bold">Guardar Prórroga</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                const table = $('#table-control-captura').DataTable({
                    pagingType: 'simple_numbers',
                    pageLength: 25,
                    order: [[1, 'asc']],
                    language: {
                        search: 'Buscar dependencia:',
                        lengthMenu: 'Mostrar _MENU_ registros',
                        info: 'Mostrando _START_ a _END_ de _TOTAL_ dependencias',
                        paginate: { previous: 'Anterior', next: 'Siguiente' }
                    }
                });

                // Control de selección masiva
                const checkAll = document.getElementById('checkSelectAll');
                const btnFinalizar = document.getElementById('btnMasivoFinalizar');
                const btnReabrir = document.getElementById('btnMasivoReabrir');
                const btnProrroga = document.getElementById('btnMasivoProrroga');
                const badgeCount = document.getElementById('selectedCountBadge');

                function updateSelectedCounter() {
                    const checkedBoxes = document.querySelectorAll('.inst-checkbox:checked');
                    const count = checkedBoxes.length;
                    badgeCount.textContent = `${count} seleccionadas`;

                    const enabled = count > 0;
                    btnFinalizar.disabled = !enabled;
                    btnReabrir.disabled = !enabled;
                    btnProrroga.disabled = !enabled;
                }

                if (checkAll) {
                    checkAll.addEventListener('change', function() {
                        const rows = table.rows({ search: 'applied' }).nodes();
                        $('input.inst-checkbox', rows).prop('checked', this.checked);
                        updateSelectedCounter();
                    });
                }

                $('#table-control-captura').on('change', '.inst-checkbox', function() {
                    updateSelectedCounter();
                });

                // Acción masiva Finalizar
                btnFinalizar.addEventListener('click', function() {
                    ejecutarAccionMasiva('finalizar', '¿Finalizar captura masiva?', 'Se marcará la captura como finalizada para las dependencias seleccionadas, bloqueando la edición.');
                });

                // Acción masiva Reabrir
                btnReabrir.addEventListener('click', function() {
                    ejecutarAccionMasiva('reabrir', '¿Reabrir captura masiva?', 'Se reactivará la captura para las dependencias seleccionadas para que puedan volver a editar.');
                });

                function ejecutarAccionMasiva(accion, title, text) {
                    const checkedBoxes = document.querySelectorAll('.inst-checkbox:checked');
                    if (checkedBoxes.length === 0) return;

                    Swal.fire({
                        title: title,
                        text: text,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: accion === 'finalizar' ? '#198754' : '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, continuar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('formAccionMasiva');
                            const container = document.getElementById('masivoIdsContainer');
                            const accionInput = document.getElementById('masivoAccionInput');

                            container.innerHTML = '';
                            accionInput.value = accion;

                            checkedBoxes.forEach(cb => {
                                const hidden = document.createElement('input');
                                hidden.type = 'hidden';
                                hidden.name = 'institucion_ids[]';
                                hidden.value = cb.value;
                                container.appendChild(hidden);
                            });

                            form.submit();
                        }
                    });
                }

                // Modal Prórroga
                const prorrogaModalEl = document.getElementById('modalProrroga');
                const prorrogaModal = new bootstrap.Modal(prorrogaModalEl);
                const targetText = document.getElementById('prorrogaTargetText');
                const hiddenContainer = document.getElementById('prorrogaHiddenInputs');
                const inputFecha = document.getElementById('modal_prorroga_hasta');
                const inputMotivo = document.getElementById('modal_motivo_prorroga');

                // Prórroga masiva
                btnProrroga.addEventListener('click', function() {
                    const checkedBoxes = document.querySelectorAll('.inst-checkbox:checked');
                    if (checkedBoxes.length === 0) return;

                    hiddenContainer.innerHTML = '';
                    checkedBoxes.forEach(cb => {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'institucion_ids[]';
                        hidden.value = cb.value;
                        hiddenContainer.appendChild(hidden);
                    });

                    targetText.innerHTML = `<strong>Acción en lote:</strong> Se asignará prórroga a <strong>${checkedBoxes.length} dependencias</strong> seleccionadas.`;
                    inputFecha.value = '';
                    inputMotivo.value = '';
                    prorrogaModal.show();
                });

                // Prórroga individual
                $('#table-control-captura').on('click', '.btn-open-prorroga', function() {
                    const id = this.getAttribute('data-id');
                    const nombre = this.getAttribute('data-nombre');
                    const fecha = this.getAttribute('data-fecha');
                    const motivo = this.getAttribute('data-motivo');

                    hiddenContainer.innerHTML = `<input type="hidden" name="institucion_ids[]" value="${id}">`;
                    targetText.innerHTML = `Asignando prórroga para: <strong>${nombre}</strong>`;
                    inputFecha.value = fecha;
                    inputMotivo.value = motivo;

                    prorrogaModal.show();
                });

                // Confirmación individual
                $('#table-control-captura').on('submit', '.confirm-individual-form', function(e) {
                    e.preventDefault();
                    const form = this;
                    const btn = form.querySelector('button');
                    const actionText = btn ? btn.getAttribute('data-action-text') : 'realizar esta acción';

                    Swal.fire({
                        title: '¿Confirmar acción?',
                        text: `¿Está seguro de ${actionText}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, confirmar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>

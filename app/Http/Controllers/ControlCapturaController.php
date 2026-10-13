<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Models\InstitutionReportStatus;
use App\Models\PeriodoCaptura;
use App\Models\User;
use App\Services\ActivePlanResolver;
use App\Services\PeriodoCapturaService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ControlCapturaController extends Controller
{
    public function __construct(
        private ActivePlanResolver $activePlan,
        private PeriodoCapturaService $periodoCapturaService
    ) {
    }

    /**
     * Muestra el panel de control de captura y estatus de enlaces.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        if (!$user->isAdministrator() && !$user->can('editar-usuario')) {
            abort(403, 'No autorizado para gestionar el control de captura.');
        }

        $plan = $this->activePlan->get();
        $periodo = $this->periodoCapturaService->getPeriodoActivo($plan->id);

        $instituciones = Institucion::query()
            ->where('id', '!=', 1)
            ->with([
                'usuario' => fn($q) => $q->role(['Enlace dependencia', 'Enlace']),
                'usuarios' => fn($q) => $q->role(['Enlace dependencia', 'Enlace']),
            ])
            ->withCount([
                'indicadores as total_indicadores' => fn($q) => $q->forPlan($plan->id),
                'indicadores as indicadores_validados' => fn($q) => $q->forPlan($plan->id)->where('indicador_validado', 1),
            ])
            ->orderBy('nombre')
            ->get();

        $reportStatuses = InstitutionReportStatus::query()
            ->where('plan_id', $plan->id)
            ->get()
            ->keyBy('institucion_id');

        $periodoVencido = $periodo ? ($periodo->haVencido() || !$periodo->activo) : false;

        $instituciones->transform(function (Institucion $inst) use ($reportStatuses, $periodoVencido) {
            /** @var InstitutionReportStatus|null $status */
            $status = $reportStatuses->get($inst->id);

            $esFinalizado = $status?->finalizado_at !== null;
            $tieneProrroga = $status?->tieneProrrogaActiva() ?? false;
            $total = (int) $inst->total_indicadores;
            $validados = (int) $inst->indicadores_validados;
            $avance = $total > 0 ? (int) round(($validados / $total) * 100) : 0;

            if ($esFinalizado) {
                $estadoVisual = 'finalizado';
            } elseif ($tieneProrroga) {
                $estadoVisual = 'prorroga';
            } elseif ($periodoVencido) {
                $estadoVisual = 'vencido';
            } else {
                $estadoVisual = 'en_proceso';
            }

            $inst->reportStatus = $status;
            $inst->esFinalizado = $esFinalizado;
            $inst->tieneProrroga = $tieneProrroga;
            $inst->avancePorcentaje = $avance;
            $inst->estadoVisual = $estadoVisual;
            $inst->enlacePrincipal = $inst->usuario->first() ?? $inst->usuarios->first();

            return $inst;
        });

        $stats = [
            'total' => $instituciones->count(),
            'finalizadas' => $instituciones->where('esFinalizado', true)->count(),
            'en_proceso' => $instituciones->where('estadoVisual', 'en_proceso')->count(),
            'con_prorroga' => $instituciones->where('tieneProrroga', true)->where('esFinalizado', false)->count(),
            'vencidas' => $instituciones->where('estadoVisual', 'vencido')->count(),
        ];

        return view('control-captura.index', compact('plan', 'periodo', 'instituciones', 'stats'));
    }

    /**
     * Guarda o actualiza la configuración del periodo general de captura.
     */
    public function guardarPeriodo(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->isAdministrator() && !$user->can('editar-usuario')) {
            abort(403);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'activo' => 'nullable|boolean',
            'descripcion' => 'nullable|string|max:1000',
        ], [
            'nombre.required' => 'El nombre del periodo es obligatorio.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required' => 'La fecha de cierre es obligatoria.',
            'fecha_fin.after' => 'La fecha de cierre debe ser posterior a la fecha de inicio.',
        ]);

        $plan = $this->activePlan->get();

        PeriodoCaptura::updateOrCreate(
            ['plan_id' => $plan->id],
            [
                'nombre' => $validated['nombre'],
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'activo' => $request->boolean('activo', true),
                'descripcion' => $validated['descripcion'] ?? null,
            ]
        );

        return redirect()->route('panel-control-captura.index')
            ->with('success', 'El periodo de captura se ha actualizado exitosamente.');
    }

    /**
     * Aplica cambio de estatus masivo (finalizar o reabrir) a las dependencias seleccionadas.
     */
    public function cambiarEstatusMasivo(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->isAdministrator() && !$user->can('editar-usuario')) {
            abort(403);
        }

        $validated = $request->validate([
            'institucion_ids' => 'required|array|min:1',
            'institucion_ids.*' => 'integer|exists:instituciones,id',
            'accion' => 'required|in:finalizar,reabrir',
        ], [
            'institucion_ids.required' => 'Debe seleccionar al menos una dependencia.',
            'accion.required' => 'Debe indicar una acción a realizar.',
        ]);

        $planId = $this->activePlan->id();
        $accion = $validated['accion'];
        $ids = $validated['institucion_ids'];

        foreach ($ids as $institucionId) {
            $this->aplicarCambioEstatus($institucionId, $planId, $accion, $user->id);
        }

        $mensaje = $accion === 'finalizar'
            ? 'Se ha marcado la captura como FINALIZADA para las dependencias seleccionadas.'
            : 'Se ha REABIERTO la captura para las dependencias seleccionadas.';

        return redirect()->route('panel-control-captura.index')->with('success', $mensaje);
    }

    /**
     * Aplica cambio de estatus individual a una dependencia.
     */
    public function cambiarEstatusIndividual(Request $request, int $institucionId): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->isAdministrator() && !$user->can('editar-usuario')) {
            abort(403);
        }

        $validated = $request->validate([
            'accion' => 'required|in:finalizar,reabrir',
        ]);

        $planId = $this->activePlan->id();
        $accion = $validated['accion'];

        $this->aplicarCambioEstatus($institucionId, $planId, $accion, $user->id);

        $mensaje = $accion === 'finalizar'
            ? 'Captura finalizada exitosamente para la dependencia.'
            : 'Captura reabierta exitosamente para la dependencia.';

        return redirect()->route('panel-control-captura.index')->with('success', $mensaje);
    }

    /**
     * Asigna una prórroga de captura a una o más dependencias.
     */
    public function asignarProrroga(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->isAdministrator() && !$user->can('editar-usuario')) {
            abort(403);
        }

        $validated = $request->validate([
            'institucion_ids' => 'required|array|min:1',
            'institucion_ids.*' => 'integer|exists:instituciones,id',
            'prorroga_hasta' => 'required|date',
            'motivo_prorroga' => 'nullable|string|max:255',
            'reabrir_si_finalizado' => 'nullable|boolean',
        ], [
            'institucion_ids.required' => 'Debe seleccionar al menos una dependencia para asignar prórroga.',
            'prorroga_hasta.required' => 'La fecha límite de la prórroga es obligatoria.',
        ]);

        $planId = $this->activePlan->id();
        $prorrogaHasta = Carbon::parse($validated['prorroga_hasta']);
        $motivo = $validated['motivo_prorroga'] ?? null;
        $reabrir = $request->boolean('reabrir_si_finalizado', true);

        foreach ($validated['institucion_ids'] as $institucionId) {
            $status = InstitutionReportStatus::firstOrNew([
                'institucion_id' => $institucionId,
                'plan_id' => $planId,
            ]);

            $status->prorroga_hasta = $prorrogaHasta;
            $status->motivo_prorroga = $motivo;

            if ($reabrir) {
                $status->finalizado_at = null;
                $status->finalizado_por_user_id = null;
                $status->reporte_generado_at = null;
                $status->reporte_generado_por_user_id = null;

                User::where('id_institucion', $institucionId)
                    ->orWhereHas('instituciones', fn($q) => $q->where('instituciones.id', $institucionId))
                    ->update([
                        'finalizado' => false,
                        'reporte_generado' => false,
                        'reporte_generado_at' => null,
                    ]);
            }

            $status->save();
        }

        return redirect()->route('panel-control-captura.index')
            ->with('success', 'Prórroga asignada correctamente a las dependencias seleccionadas.');
    }

    /**
     * Aplica la lógica de finalizar o reabrir para una institución.
     */
    private function aplicarCambioEstatus(int $institucionId, int $planId, string $accion, int $userId): void
    {
        $status = InstitutionReportStatus::firstOrNew([
            'institucion_id' => $institucionId,
            'plan_id' => $planId,
        ]);

        if ($accion === 'finalizar') {
            $status->finalizado_at = now();
            $status->finalizado_por_user_id = $userId;
            $status->save();

            User::where('id_institucion', $institucionId)
                ->orWhereHas('instituciones', fn($q) => $q->where('instituciones.id', $institucionId))
                ->update([
                    'finalizado' => true,
                ]);
        } else {
            $status->finalizado_at = null;
            $status->finalizado_por_user_id = null;
            $status->reporte_generado_at = null;
            $status->reporte_generado_por_user_id = null;
            $status->save();

            User::where('id_institucion', $institucionId)
                ->orWhereHas('instituciones', fn($q) => $q->where('instituciones.id', $institucionId))
                ->update([
                    'finalizado' => false,
                    'reporte_generado' => false,
                    'reporte_generado_at' => null,
                ]);
        }
    }
}

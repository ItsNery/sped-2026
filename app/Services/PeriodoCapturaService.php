<?php

namespace App\Services;

use App\Models\InstitutionReportStatus;
use App\Models\PeriodoCaptura;
use App\Models\User;
use Carbon\Carbon;

class PeriodoCapturaService
{
    public function __construct(
        private ActivePlanResolver $activePlan
    ) {
    }

    /**
     * Obtiene el periodo de captura activo para el plan indicado o el plan activo actual.
     */
    public function getPeriodoActivo(?int $planId = null): ?PeriodoCaptura
    {
        $resolvedPlanId = $planId ?? $this->activePlan->id();

        return PeriodoCaptura::query()
            ->where('plan_id', $resolvedPlanId)
            ->where('activo', true)
            ->latest('id')
            ->first();
    }

    /**
     * Verifica si un usuario/institución puede capturar/editar información de indicadores.
     * Retorna un arreglo con el estado detallado.
     *
     * @return array{
     *     puede_capturar: bool,
     *     estado: string,
     *     mensaje: string,
     *     fecha_limite: ?Carbon,
     *     tiene_prorroga: bool,
     *     dias_restantes: ?int
     * }
     */
    public function verificarEstadoCaptura(?User $user = null, ?int $institucionId = null, ?int $planId = null): array
    {
        $user = $user ?? auth()->user();

        // Los administradores siempre tienen permiso para capturar/editar
        if ($user?->isAdministrator()) {
            return [
                'puede_capturar' => true,
                'estado' => 'admin_override',
                'mensaje' => 'Acceso administrativo total sin restricción de periodo.',
                'fecha_limite' => null,
                'tiene_prorroga' => false,
                'dias_restantes' => null,
            ];
        }

        $resolvedPlanId = $planId ?? $this->activePlan->id();
        $targetInstitucionId = $institucionId ?? $user?->id_institucion;

        // Verificar si la institución cuenta con una prórroga vigente
        if ($targetInstitucionId) {
            /** @var InstitutionReportStatus|null $reportStatus */
            $reportStatus = InstitutionReportStatus::query()
                ->where('institucion_id', $targetInstitucionId)
                ->where('plan_id', $resolvedPlanId)
                ->first();

            if ($reportStatus && $reportStatus->tieneProrrogaActiva()) {
                $horas = Carbon::now()->diffInHours($reportStatus->prorroga_hasta, false);
                return [
                    'puede_capturar' => true,
                    'estado' => 'prorroga',
                    'mensaje' => "Prórroga autorizada hasta el {$reportStatus->prorroga_hasta->format('d/m/Y H:i')} hrs.",
                    'fecha_limite' => $reportStatus->prorroga_hasta,
                    'tiene_prorroga' => true,
                    'dias_restantes' => max(0, (int) ceil($horas / 24)),
                ];
            }
        }

        $periodo = $this->getPeriodoActivo($resolvedPlanId);

        // Si no hay un periodo configurado en el sistema, por defecto permitimos captura (retrocompatibilidad)
        if (!$periodo) {
            return [
                'puede_capturar' => true,
                'estado' => 'sin_periodo',
                'mensaje' => 'No hay periodo de captura restringido activo.',
                'fecha_limite' => null,
                'tiene_prorroga' => false,
                'dias_restantes' => null,
            ];
        }

        $ahora = Carbon::now();

        if (!$periodo->activo) {
            return [
                'puede_capturar' => false,
                'estado' => 'inactivo',
                'mensaje' => 'El periodo de captura ha sido pausado o deshabilitado temporalmente.',
                'fecha_limite' => $periodo->fecha_fin,
                'tiene_prorroga' => false,
                'dias_restantes' => 0,
            ];
        }

        if ($ahora->lt($periodo->fecha_inicio)) {
            return [
                'puede_capturar' => false,
                'estado' => 'no_iniciado',
                'mensaje' => "El periodo de captura iniciará el {$periodo->fecha_inicio->format('d/m/Y H:i')} hrs.",
                'fecha_limite' => $periodo->fecha_inicio,
                'tiene_prorroga' => false,
                'dias_restantes' => 0,
            ];
        }

        if ($ahora->gt($periodo->fecha_fin)) {
            return [
                'puede_capturar' => false,
                'estado' => 'vencido',
                'mensaje' => "El plazo de captura concluyó el {$periodo->fecha_fin->format('d/m/Y H:i')} hrs.",
                'fecha_limite' => $periodo->fecha_fin,
                'tiene_prorroga' => false,
                'dias_restantes' => 0,
            ];
        }

        // Periodo vigente regular
        $horasRestantes = $ahora->diffInHours($periodo->fecha_fin, false);
        return [
            'puede_capturar' => true,
            'estado' => 'abierto',
            'mensaje' => "Periodo de captura vigente hasta el {$periodo->fecha_fin->format('d/m/Y H:i')} hrs.",
            'fecha_limite' => $periodo->fecha_fin,
            'tiene_prorroga' => false,
            'dias_restantes' => max(0, (int) ceil($horasRestantes / 24)),
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CatPlanEstatalDesarrollo;
use App\Models\CatEje;
use App\Models\CatProgramaDerivadoEspecial;
use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\CatProgramaDerivadoRegional;
use App\Models\CatProgramaDerivadoSectorial;
use App\Models\Indicador;
use Illuminate\Http\Request;

class DashboardGeneralController extends Controller
{
    /**
     * Muestra el tablero de avance general público.
     */
    public function publicIndex(Request $request)
    {
        return $this->generarDashboard($request, 'ped');
    }

    /**
     * Muestra el tablero de avance general para administradores.
     */
    public function adminIndex(Request $request)
    {
        return $this->generarDashboard($request, 'admin.dashboard-general');
    }

    private function generarDashboard(Request $request, $vista)
    {
        // El Plan Estatal por defecto es el ID 3 (2024-2030)
        $planId = 3;
        $plan = CatPlanEstatalDesarrollo::find($planId);

        if (!$plan) {
            $plan = CatPlanEstatalDesarrollo::where('nombre', 'like', '%2024-2030%')->first();
            if ($plan) $planId = $plan->id;
        }

        // Determinar si solo se usan datos validados
        // En la vista pública siempre es true. En admin depende del request.
        $soloValidados = $request->boolean('solo_validados', true);

        // Si el usuario no es admin o enlace (es decir, es público), forzar solo validados
        if (!auth()->check()) {
            $soloValidados = true;
        }

        // 1. Avance General del Plan
        $indicadoresPlan = Indicador::where(function ($query) use ($planId) {
            $query->whereHasMorph('indicadorable', [CatEje::class], function ($q) use ($planId) {
                $q->where('plan_id', $planId);
            })->orWhereHasMorph('indicadorable', [
                CatProgramaDerivadoSectorial::class,
                CatProgramaDerivadoEspecial::class,
                CatProgramaDerivadoRegional::class,
                CatProgramaDerivadoInstitucional::class
            ], function ($q) use ($planId) {
                $q->where('plan_estatal', $planId);
            });
        })->get();

        $avancePlan = $this->calcularPromedioAvance($indicadoresPlan, $soloValidados);

        // 2. Avance por Eje
        $ejes = CatEje::with('indicadores')->where('plan_id', $planId)->orderBy('numero')->get();
        $ejesData = $ejes->map(function ($eje) use ($soloValidados) {
            $indicadores = $eje->indicadores;
            $avance = $this->calcularPromedioAvance($indicadores, $soloValidados);
            return [
                'id' => $eje->id,
                'nombre' => $eje->nombre ?? 'No se encontró',
                'numero' => $eje->numero ?? 'ND',
                'color' => $eje->color ?? '#CCCCCC',
                'semaforo_color' => $this->getSemaforoColor($avance),
                'avance' => $avance,
                'total_indicadores' => $indicadores->count()
            ];
        });

        // 3. Avance por Programas Derivados
        $programasData = $this->getProgramasAvance($planId, $soloValidados);
        $programasDerivadosAgrupados = $programasData->groupBy('tipo');

        $colorPlan = $this->getSemaforoColor($avancePlan);

        return view($vista, compact(
            'plan',
            'avancePlan',
            'colorPlan',
            'ejesData',
            'programasData',
            'programasDerivadosAgrupados',
            'soloValidados'
        ));
    }

    /**
     * Calcula el promedio de avance de una colección de indicadores.
     */
    private function calcularPromedioAvance($indicadores, $soloValidados)
    {
        if ($indicadores->isEmpty()) return 0;

        $sumAvance = 0;
        $count = 0;

        foreach ($indicadores as $indicador) {
            $res = $indicador->calcularSemaforizacion($soloValidados);
            if ($res['avance'] !== null) {
                $sumAvance += $res['avance'];
                $count++;
            }
        }

        return $count > 0 ? round($sumAvance / $count, 2) : 0;
    }

    /**
     * Obtiene el avance de todos los programas derivados del plan.
     */
    private function getProgramasAvance($planId, $soloValidados)
    {
        $tipos = [
            ['class' => CatProgramaDerivadoSectorial::class, 'nombre' => 'Sectoriales', 'slug' => 'sectoriales', 'order' => 1],
            ['class' => CatProgramaDerivadoEspecial::class, 'nombre' => 'Especiales', 'slug' => 'especiales', 'order' => 2],
            ['class' => CatProgramaDerivadoRegional::class, 'nombre' => 'Regionales', 'slug' => 'regionales', 'order' => 3],
            ['class' => CatProgramaDerivadoInstitucional::class, 'nombre' => 'Institucionales', 'slug' => 'institucionales', 'order' => 4],
        ];

        $resultados = [];

        foreach ($tipos as $tipo) {
            $programas = $tipo['class']::where('plan_estatal', $planId)->get();
            foreach ($programas as $prog) {
                $indicadores = $prog->indicadores;
                $avance = $this->calcularPromedioAvance($indicadores, $soloValidados);

                $resultados[] = [
                    'id' => $prog->id,
                    'nombre' => $prog->nombre,
                    'tipo' => $tipo['nombre'],
                    'tipo_slug' => $tipo['slug'],
                    'tipo_order' => $tipo['order'],
                    'avance' => $avance,
                    'color' => $prog->color,
                    'semaforo_color' => $this->getSemaforoColor($avance),
                    'total_indicadores' => $indicadores->count()
                ];
            }
        }

        return collect($resultados)->sortBy('tipo_order')->values();
    }

    /**
     * Determina el color del semáforo basado en el avance.
     */
    private function getSemaforoColor($avance)
    {
        if ($avance === null || $avance == 0) return '#adb5bd'; // Solo línea base / Sin datos
        if ($avance >= 110) return '#0d6efd'; // Excedido (Azul)
        if ($avance >= 91) return '#198754';  // Aceptable (Verde)
        if ($avance >= 71) return '#ffc107';  // Moderado (Amarillo)
        return '#dc3545'; // Insuficiente (Rojo)
    }
}

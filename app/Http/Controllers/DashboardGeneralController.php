<?php

namespace App\Http\Controllers;

use App\Models\CatPlanEstatalDesarrollo;
use App\Models\CatEje;
use App\Models\CatProgramaDerivadoEspecial;
use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\CatProgramaDerivadoRegional;
use App\Models\CatProgramaDerivadoSectorial;
use App\Models\Indicador;
use App\Services\PedMetricsService;
use Illuminate\Http\Request;

class DashboardGeneralController extends Controller
{
    public function __construct(private PedMetricsService $pedMetrics)
    {
    }

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
        return redirect()->route('dashboard', $request->query());
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
                CatProgramaDerivadoRegional::class
            ], function ($q) use ($planId) {
                $q->where('plan_estatal', $planId);
            })->orWhereHas('programasInstitucionales', function ($q) use ($planId) {
                $q->where('plan_estatal', $planId);
            });
        })->with(['datosAnuales' => function ($query) use ($soloValidados) {
            if ($soloValidados) {
                $query->where('validado', true);
            }
            $query->orderByDesc('anio');
        }])->get();

        $metricasPlan = $this->pedMetrics->summarizeCached($indicadoresPlan, $soloValidados);
        $composicionPlan = $this->pedMetrics->summarizeCompositionCached($indicadoresPlan);
        $avancePlan = $metricasPlan['avance_promedio'];
        $totalIndicadores = $metricasPlan['total_registrados'];

        // 2. Avance por Eje
        $ejes = CatEje::with('indicadores.datosAnuales')->where('plan_id', $planId)->orderBy('numero')->get();
        $ejesData = $ejes->map(function ($eje) use ($soloValidados) {
            $indicadores = $eje->indicadores;
            $metricas = $this->pedMetrics->summarizeCached($indicadores, $soloValidados);
            return [
                'id' => $eje->id,
                'nombre' => $eje->nombre ?? 'No se encontró',
                'numero' => $eje->numero ?? 'ND',
                'color' => $eje->color ?? '#CCCCCC',
                'semaforo_color' => $this->getSemaforoColor($metricas['avance_promedio']),
                'avance' => $metricas['avance_promedio'],
                'total_indicadores' => $metricas['total_registrados'],
                'indicadores_evaluables' => $metricas['total_evaluables'],
                'cobertura_evaluacion' => $metricas['cobertura_evaluacion'],
            ];
        });

        // 3. Avance por Programas Derivados
        $programasData = $this->getProgramasAvance($planId, $soloValidados);
        $programasDerivadosAgrupados = $programasData->groupBy('tipo');
        $gruposInstitucionales = CatProgramaDerivadoInstitucional::where('plan_estatal', $planId)
            ->whereNotNull('grupo')
            ->where('grupo', '!=', '')
            ->distinct()
            ->orderBy('grupo')
            ->pluck('grupo');

        $colorPlan = $this->getSemaforoColor($avancePlan);

        return view($vista, compact(
            'plan',
            'avancePlan',
            'colorPlan',
            'totalIndicadores',
            'metricasPlan',
            'composicionPlan',
            'ejesData',
            'programasData',
            'programasDerivadosAgrupados',
            'gruposInstitucionales',
            'soloValidados'
        ));
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
            $programas = $tipo['class']::with('indicadores.datosAnuales')->where('plan_estatal', $planId)->get();
            foreach ($programas as $prog) {
                $indicadores = $prog->indicadores;
                $metricas = $this->pedMetrics->summarizeCached($indicadores, $soloValidados);

                $resultados[] = [
                    'id' => $prog->id,
                    'nombre' => $prog->nombre,
                    'tipo' => $tipo['nombre'],
                    'tipo_slug' => $tipo['slug'],
                    'tipo_order' => $tipo['order'],
                    'avance' => $metricas['avance_promedio'],
                    'color' => $prog->color,
                    'semaforo_color' => $this->getSemaforoColor($metricas['avance_promedio']),
                    'total_indicadores' => $metricas['total_registrados'],
                    'indicadores_evaluables' => $metricas['total_evaluables'],
                    'cobertura_evaluacion' => $metricas['cobertura_evaluacion'],
                    'grupo' => $prog->grupo ?? null,
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
        if ($avance === null) return '#adb5bd'; // Solo línea base / Sin datos
        if ($avance >= 110) return '#3E8CEE'; // Excedido
        if ($avance >= 91) return '#43B383';  // Aceptable
        if ($avance >= 71) return '#F5E35B';  // Moderado
        return '#B94149'; // Insuficiente
    }
}

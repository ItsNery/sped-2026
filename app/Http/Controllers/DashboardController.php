<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Indicador;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\CatPlanEstatalDesarrollo;
use App\Models\CatEje;
use App\Models\CatProgramaDerivadoEspecial;
use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\CatProgramaDerivadoRegional;
use App\Models\CatProgramaDerivadoSectorial;
use App\Services\PedMetricsService;
use App\Services\PedTrendService;
use App\Services\DashboardFilterService;

/**
 * Gestiona las vistas y métricas principales del panel de control.
 */
class DashboardController extends Controller
{
    /**
     * Crea una nueva instancia del controlador.
     *
     * @param  PedMetricsService  $pedMetrics Servicio para calcular métricas de indicadores.
     */
    public function __construct(
        private PedMetricsService $pedMetrics,
        private PedTrendService $pedTrend,
        private DashboardFilterService $dashboardFilters
    ) {}

    /**
     * Prepara y muestra el panel de control.
     *
     * Para usuarios asociados a un municipio muestra el panel municipal. Para
     * el resto de los usuarios calcula las métricas generales, la información
     * de programas derivados y los datos necesarios para los gráficos.
     *
     * @return \Illuminate\Contracts\View\View Vista del panel correspondiente.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ((int) $user->id_municipio !== 0) {
            return view('panel-indicadores-municipales.dashboard');
        }

        if (!$user->isAdministrator()
            && !$user->can('ver-panel-avance-general')
            && $user->can('ver-indicador')) {
            return redirect()->route('panel-indicadores.index');
        }

        abort_unless(
            $user->isAdministrator() || $user->can('ver-panel-avance-general'),
            403
        );

        $filters = $this->dashboardFilters->normalize($request);
        $planes = CatPlanEstatalDesarrollo::query()
            ->orderByDesc('id')
            ->get(['id', 'nombre']);
        $plan = $planes->firstWhere('id', $filters['plan_id']) ?? $planes->first();

        if (!$plan) {
            abort(404, 'No hay un plan estatal disponible.');
        }

        $filters['plan_id'] = $plan->id;
        $soloValidados = $filters['solo_validados'];
        $indicadoresPlan = $this->dashboardFilters
            ->queryForPlan($plan->id, $filters, $soloValidados)
            ->get();
        $indicadoresPlan = $this->dashboardFilters->filterComputed($indicadoresPlan, $filters, $soloValidados);
        $metricasGlobal = $this->pedMetrics->summarizeCached($indicadoresPlan, $soloValidados);
        $avanceGlobalPromedio = $metricasGlobal['avance_promedio'];
        $colorAvanceGlobal = $this->getSemaforoColor($avanceGlobalPromedio);
        $totalIndicadores = $indicadoresPlan->count();
        $totalIndicadoresValidados = $indicadoresPlan->where('indicador_validado', true)->count();
        $porcentajeValidado = $totalIndicadores > 0
            ? round(($totalIndicadoresValidados / $totalIndicadores) * 100, 1)
            : 0;

        $quality = [
            'sin_datos' => 0,
            'sin_meta' => 0,
            'sin_tendencia' => 0,
            'pendientes_validacion' => $totalIndicadores - $totalIndicadoresValidados,
        ];
        $semaforizacionCounts = [
            'Excedido' => 0,
            'Aceptable' => 0,
            'Moderado' => 0,
            'Insuficiente' => 0,
            'No clasificado' => 0,
        ];
        $tendenciaCounts = [
            'Mayor es mejor' => 0,
            'Menor es mejor' => 0,
            'Constante' => 0,
            'No definida' => 0,
        ];

        foreach ($indicadoresPlan as $indicador) {
            $resultado = $indicador->calcularSemaforizacion($soloValidados);
            $estado = array_key_exists($resultado['semaforizacion'], $semaforizacionCounts)
                ? $resultado['semaforizacion']
                : 'No clasificado';
            $semaforizacionCounts[$estado]++;

            $tendencia = trim((string) $indicador->tendencia);
            $tendenciaCounts[$tendencia] = isset($tendenciaCounts[$tendencia])
                ? $tendenciaCounts[$tendencia] + 1
                : $tendenciaCounts['No definida'] + 1;

            $datos = $indicador->datosAnuales
                ->filter(fn ($dato) => $dato->valor_dato !== null && trim((string) $dato->valor_dato) !== '');
            $datosDisponibles = $soloValidados
                ? $datos->where('validado', true)
                : $datos;

            if ($datosDisponibles->isEmpty()) {
                $quality['sin_datos']++;
            }
            if (!is_numeric(str_replace(',', '', (string) $indicador->meta_2024)) || (float) $indicador->meta_2024 === 0.0) {
                $quality['sin_meta']++;
            }
            if (!in_array(strtolower($tendencia), ['mayor es mejor', 'menor es mejor', 'constante'], true)) {
                $quality['sin_tendencia']++;
            }
        }

        $totalCriticos = $indicadoresPlan->filter(function ($indicador) use ($soloValidados) {
            $resultado = $indicador->calcularSemaforizacion($soloValidados);
            $datos = $indicador->datosAnuales
                ->filter(fn ($dato) => $dato->valor_dato !== null && trim((string) $dato->valor_dato) !== '');
            $datosDisponibles = $soloValidados ? $datos->where('validado', true) : $datos;

            return ($resultado['avance'] !== null && $resultado['avance'] < 71)
                || $datosDisponibles->isEmpty();
        })->count();

        $actionQueue = $this->buildActionQueue($indicadoresPlan, $soloValidados);
        $indicadoresCriticos = $actionQueue->whereIn('prioridad', [1, 2, 3])->count();

        $ejesData = CatEje::query()
            ->where('plan_id', $plan->id)
            ->orderBy('numero')
            ->get()
            ->map(function ($eje) use ($soloValidados, $indicadoresPlan) {
                $ejeIndicadores = $indicadoresPlan->filter(fn ($indicador) =>
                    $indicador->indicadorable_type === CatEje::class
                    && (int) $indicador->indicadorable_id === (int) $eje->id
                );
                $metricas = $this->pedMetrics->summarizeCached($ejeIndicadores, $soloValidados);

                return [
                    'id' => $eje->id,
                    'numero' => $eje->numero,
                    'nombre' => $eje->nombre,
                    'color' => $eje->color ?: '#0c312d',
                    'avance' => $metricas['avance_promedio'],
                    'cobertura' => $metricas['cobertura_evaluacion'],
                    'evaluables' => $metricas['total_evaluables'],
                    'total' => $metricas['total_registrados'],
                    'semaforo_color' => $this->getSemaforoColor($metricas['avance_promedio']),
                ];
            });

        $programasData = collect($this->getProgramasAvance($plan->id, $soloValidados, $indicadoresPlan))
            ->groupBy('tipo_slug')
            ->map(fn ($programas) => [
                'tipo' => $programas->first()['tipo'],
                'programas' => $programas,
            ]);

        $institucionesData = $indicadoresPlan
            ->filter(fn ($indicador) => $indicador->id_institucion && $indicador->institucion)
            ->groupBy('id_institucion')
            ->map(function ($indicadores) use ($actionQueue, $soloValidados) {
                $metricas = $this->pedMetrics->summarizeCached($indicadores, $soloValidados);
                $institucionId = $indicadores->first()->id_institucion;
                $criticos = $actionQueue->where('id_institucion', $institucionId)->count();

                return [
                    'id' => $institucionId,
                    'nombre' => $indicadores->first()->institucion->nombre,
                    'total' => $metricas['total_registrados'],
                    'avance' => $metricas['avance_promedio'],
                    'cobertura' => $metricas['cobertura_evaluacion'],
                    'validados' => $indicadores->where('indicador_validado', true)->count(),
                    'criticos' => $criticos,
                ];
            })
            ->sortByDesc(fn ($institucion) => [$institucion['criticos'], -($institucion['avance'] ?? 0)])
            ->take(8)
            ->values();

        $fechaCorte = $indicadoresPlan
            ->flatMap(fn ($indicador) => $indicador->datosAnuales)
            ->pluck('fecha_actualizacion')
            ->filter()
            ->map(function ($fecha) {
                try {
                    return Carbon::parse($fecha);
                } catch (\Throwable) {
                    return null;
                }
            })
            ->filter()
            ->sortDesc()
            ->first();

        $trend = $this->pedTrend->summarize(
            $indicadoresPlan,
            $soloValidados,
            $filters['anio_desde'],
            $filters['anio_hasta']
        );
        $filterOptions = $this->dashboardFilters->options($plan->id);

        return view('dashboard', compact(
            'plan',
            'planes',
            'soloValidados',
            'metricasGlobal',
            'avanceGlobalPromedio',
            'colorAvanceGlobal',
            'totalIndicadores',
            'totalIndicadoresValidados',
            'porcentajeValidado',
            'quality',
            'semaforizacionCounts',
            'tendenciaCounts',
            'actionQueue',
            'indicadoresCriticos',
            'totalCriticos',
            'ejesData',
            'programasData',
            'institucionesData',
            'fechaCorte',
            'trend',
            'filters',
            'filterOptions'
        ));
    }

    private function buildActionQueue(Collection $indicadores, bool $soloValidados): Collection
    {
        return $indicadores->map(function ($indicador) use ($soloValidados) {
            $resultado = $indicador->calcularSemaforizacion($soloValidados);
            $datos = $indicador->datosAnuales
                ->filter(fn ($dato) => $dato->valor_dato !== null && trim((string) $dato->valor_dato) !== '');
            $datosDisponibles = $soloValidados ? $datos->where('validado', true) : $datos;
            $ultimoDato = $datosDisponibles->sortByDesc('anio')->first();
            $proximaActualizacion = null;

            if ($indicador->proxima_actualizacion) {
                try {
                    $proximaActualizacion = Carbon::parse($indicador->proxima_actualizacion);
                } catch (\Throwable) {
                    $proximaActualizacion = null;
                }
            }

            $motivo = null;
            $prioridad = null;
            if ($resultado['avance'] !== null && $resultado['avance'] < 71) {
                $motivo = 'Avance insuficiente';
                $prioridad = 1;
            } elseif ($proximaActualizacion?->isPast()) {
                $motivo = 'Actualización vencida';
                $prioridad = 2;
            } elseif (!$indicador->indicador_validado) {
                $motivo = 'Pendiente de validación';
                $prioridad = 3;
            } elseif (!$ultimoDato) {
                $motivo = 'Sin dato anual';
                $prioridad = 4;
            } elseif (!is_numeric(str_replace(',', '', (string) $indicador->meta_2024)) || (float) $indicador->meta_2024 === 0.0) {
                $motivo = 'Sin meta válida';
                $prioridad = 5;
            } elseif (!in_array(strtolower(trim((string) $indicador->tendencia)), ['mayor es mejor', 'menor es mejor', 'constante'], true)) {
                $motivo = 'Sin tendencia definida';
                $prioridad = 6;
            }

            if (!$motivo) {
                return null;
            }

            $fechaDato = $ultimoDato?->fecha_actualizacion;
            try {
                $fechaDato = $fechaDato ? Carbon::parse($fechaDato)->format('d/m/Y') : 'Sin fecha';
            } catch (\Throwable) {
                $fechaDato = 'Sin fecha';
            }

            return [
                'id' => $indicador->id,
                'nombre' => $indicador->nombre,
                'institucion' => $indicador->institucion?->nombre ?? 'Sin institución',
                'id_institucion' => $indicador->id_institucion,
                'motivo' => $motivo,
                'prioridad' => $prioridad,
                'avance' => $resultado['avance'],
                'anio' => $resultado['anio_ultimo_dato'],
                'fecha_dato' => $fechaDato,
                'proxima_actualizacion' => $proximaActualizacion?->format('d/m/Y'),
            ];
        })->filter()->sortBy(function ($item) {
            return sprintf('%02d-%08.2f', $item['prioridad'], $item['avance'] ?? 999999);
        })->values();
    }

    /**
     * Muestra una lista filtrada de indicadores según su estado de semaforización.
     * Este método atiende los clics en el gráfico de semaforización.
     *
     * @param  string  $categoria Categoría solicitada del semáforo.
     * @return \Illuminate\Contracts\View\View Vista con los indicadores filtrados.
     */
    public function semaforizacion($categoria)
    {
        $categoriasValidas = ["Excedido", "Aceptable", "Moderado", "Insuficiente", "No clasificado"];

        if (!in_array($categoria, $categoriasValidas)) {
            abort(404, "Categoría no válida");
        }

        $indicadores = Indicador::with('datosAnuales')
            ->get()
            ->filter(function ($indicador) use ($categoria, $categoriasValidas) {
                $semaforo = $indicador->semaforizacion;

                // Si la semaforización no es válida (e.g. "Solo línea base"), se agrupa en "No clasificado"
                if (!in_array($semaforo, $categoriasValidas)) {
                    $semaforo = "No clasificado";
                }

                return $semaforo === $categoria;
            });

        return view('panel-indicadores.indicadores_semaforizacion', compact('indicadores', 'categoria'));
    }

    /**
     * Muestra los indicadores de un usuario "Enlace", filtrados por estado de validación.
     * Este método atiende los clics en los gráficos de avance por Enlace.
     *
     * @param  Request  $request Solicitud con el filtro de validación.
     * @param  int  $id Identificador del usuario Enlace.
     * @return \Illuminate\Contracts\View\View Vista con los indicadores filtrados.
     */
    public function mostrarIndicadores(Request $request, $id)
    {
        $usuario = User::with('instituciones.indicadores')->findOrFail($id);

        $filtro = $request->query('filtro');
        $indicadores = collect();

        foreach ($usuario->instituciones as $institucion) {
            $indicadores = $indicadores->merge(
                $institucion->indicadores->filter(function ($indicador) use ($filtro) {
                    return $filtro === 'validados' ? $indicador->indicador_validado : !$indicador->indicador_validado; //
                })
            );
        }

        return view('users.indicadores', compact('usuario', 'indicadores', 'filtro'));
    }

    /**
     * Calcula el promedio de avance de una colección de indicadores.
     *
     * @param  Collection  $indicadores Indicadores cuyo avance se calculará.
     * @param  bool  $soloValidados Indica si deben considerarse solo indicadores validados.
     * @return float Promedio de avance redondeado a dos decimales.
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
     * Obtiene el avance de los programas derivados de un plan estatal.
     *
     * @param  int  $planId Identificador del plan estatal.
     * @param  bool  $soloValidados Indica si deben considerarse solo indicadores validados.
     * @return array<int, array<string, mixed>> Datos resumidos de cada programa.
     */
    private function getProgramasAvance($planId, $soloValidados, Collection $selectedIndicators)
    {
        $tipos = [
            ['class' => CatProgramaDerivadoEspecial::class, 'nombre' => 'Programas Especiales', 'slug' => 'especiales'],
            ['class' => CatProgramaDerivadoInstitucional::class, 'nombre' => 'Programas Institucionales', 'slug' => 'institucionales'],
            ['class' => CatProgramaDerivadoRegional::class, 'nombre' => 'Programas Regionales', 'slug' => 'regionales'],
            ['class' => CatProgramaDerivadoSectorial::class, 'nombre' => 'Programas Sectoriales', 'slug' => 'sectoriales'],
        ];

        $resultados = [];
        $selectedById = $selectedIndicators->keyBy('id');

        foreach ($tipos as $tipo) {
            $programas = $tipo['class']::with('indicadores.datosAnuales')
                ->where('plan_estatal', $planId)
                ->get();
            foreach ($programas as $prog) {
                $indicadores = $prog->indicadores
                    ->map(fn ($indicador) => $selectedById->get($indicador->id))
                    ->filter()
                    ->values();
                $metricas = $this->pedMetrics->summarizeCached($indicadores, $soloValidados);

                $resultados[] = [
                    'id' => $prog->id,
                    'nombre' => $prog->nombre,
                    'tipo' => $tipo['nombre'],
                    'tipo_slug' => $tipo['slug'],
                    'avance' => $metricas['avance_promedio'],
                    'color' => $prog->color,
                    'semaforo_color' => $this->getSemaforoColor($metricas['avance_promedio']),
                    'total_indicadores' => $metricas['total_registrados'],
                    'evaluables' => $metricas['total_evaluables'],
                    'cobertura' => $metricas['cobertura_evaluacion'],
                ];
            }
        }

        return collect($resultados)->sortBy('id')->values()->toArray();
    }

    /**
     * Determina el color del semáforo según el porcentaje de avance.
     *
     * @param  float|null  $avance Porcentaje de avance del indicador o programa.
     * @return string Código hexadecimal del color correspondiente.
     */
    private function getSemaforoColor($avance)
    {
        if ($avance === null) return '#adb5bd';
        if ($avance >= 110) return '#3E8CEE';
        if ($avance >= 91) return '#43B383';
        if ($avance >= 71) return '#F5E35B';
        return '#B94149';
    }
}

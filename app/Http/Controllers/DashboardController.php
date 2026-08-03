<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Institucion;
use App\Models\Indicador;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
// use App\Models\DatoAnualIndicador;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\CatPlanEstatalDesarrollo;
use App\Models\CatEje;
use App\Models\CatProgramaDerivadoEspecial;
use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\CatProgramaDerivadoRegional;
use App\Models\CatProgramaDerivadoSectorial;
use App\Models\DatoAnual;
use App\Services\PedMetricsService;

class DashboardController extends Controller
{
    public function __construct(private PedMetricsService $pedMetrics)
    {
    }

    /**
     * Prepara y muestra el panel de control (dashboard).
     *
     * Este método es el corazón del dashboard. Agrega y procesa datos de
     * múltiples fuentes (Indicadores, Instituciones, Usuarios, Datos Anuales)
     * para calcular una variedad de métricas y KPIs que se envían a la vista.
     * La vista que se muestra depende del rol del usuario.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->id_municipio !== null  && $user->id_municipio !== 0) {
            return view('panel-indicadores-municipales.dashboard');
        } else {
            $institucionesExcluidas = [1];

            $institucionesTop = Institucion::whereNotIn('id', $institucionesExcluidas)
                ->whereHas('indicadores')
                ->withCount(['indicadores as indicadores_validados_count' => function ($query) {
                    $query->where('indicador_validado', true);
                }])
                ->orderByDesc('indicadores_validados_count')
                ->take(5)
                ->get();

            $instituciones = $institucionesTop;

            //  Avance Global Promedio
            $planId = 3;
            $plan = CatPlanEstatalDesarrollo::find($planId);

            if (!$plan) {
                $plan = CatPlanEstatalDesarrollo::where('nombre', 'like', '%2024-2030%')->first();
                if ($plan) $planId = $plan->id;
            }

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
            })->get();

        $metricasGlobal = $this->pedMetrics->summarizeCached($indicadoresPlan, false);
            $avanceGlobalPromedio = $metricasGlobal['avance_promedio'];
            $colorAvanceGlobal = $this->getSemaforoColor($avanceGlobalPromedio);

            // Programas Derivados
            $programasData = collect($this->getProgramasAvance($planId, false))
                ->groupBy('tipo_slug')
                ->map(function ($programas, $tipo) {
                    return [
                        'tipo' => $programas->first()['tipo'],
                        'programas' => $programas
                    ];
                });

            $ordenDeseado = ['sectoriales', 'especiales', 'regionales', 'institucionales'];
            $programasAgrupadosOrdenados = [];
            foreach ($ordenDeseado as $tipoSlug) {
                if ($programasData->has($tipoSlug)) {
                    $programasAgrupadosOrdenados[$tipoSlug] = $programasData->get($tipoSlug);
                }
            }
            $programasData = $programasAgrupadosOrdenados;

            // Se obtiene el porcentaje de indicadores que son validados
            $totalIndicadoresValidados = Indicador::where('indicador_validado', true)->count();

            $totalIndicadores = Indicador::count();
            $porcentajeValidado = $totalIndicadores > 0 ? ($totalIndicadoresValidados / $totalIndicadores) * 100 : 0;

            // Se obtiene el porcentaje de indicadores que no tienen datos anuales o no han sido validados
            // La lógica de 'orWhereDoesntHave('datosAnuales')' sigue siendo válida si significa
            // "no tiene ningún registro de dato anual asociado".
            $totalIndicadoresIncompletos = Indicador::where(function ($query) {
                $query->where('indicador_validado', false)
                    ->orWhereDoesntHave('datosAnuales')
                ;
            })->count();
            $porcentajeIncompletos = $totalIndicadores > 0 ? ($totalIndicadoresIncompletos / $totalIndicadores) * 100 : 0;
            /**------------------------------------------------------------------------------------------- */
            $indicadoresRecientes = Indicador::orderBy('updated_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($indicador) {
                    $esNuevo = false;

                    if ($indicador->created_at && $indicador->updated_at) {
                        $esNuevo = $indicador->created_at->eq($indicador->updated_at);
                    }

                    return [
                        'id' => $indicador->id,
                        'nombre' => $indicador->nombre,
                        'updated_at' => $indicador->updated_at ? $indicador->updated_at->diffForHumans() : 'Sin fecha',
                        'tipo' => $esNuevo ? 'Nuevo' : 'Modificado'
                    ];
                });
            /**------------------------------------------------------------------------------------------- */
            // Se muestran las instituciones que no tienen indicadores "completos" o "válidos"
            // Un indicador se considera "bueno" si está validado O si tiene datos para todos los años 2015-2030.
            // Una institución es "sin indicadores (buenos)" si NO TIENE indicadores que cumplan esa condición.
            // Obtener instituciones que TIENEN indicadores, y de esos, AL MENOS UNO NO ESTÁ VALIDADO.
            $institucionesSinIndicadores = Institucion::where('id', '!=', 1)
                ->whereHas('indicadores', function ($queryIndicador) {
                    $queryIndicador->where('indicador_validado', false); // O $queryIndicador->where('indicador_validado', 0);
                })
                ->get();

            // Se obtendrán los indicadores que están próximos a caducar, a tiempo y los que ya caducaron
            $hoy = Carbon::now()->format('Y-m-d');

            $getFechaMasReciente = function ($indicador) {
                $fechasValidasEnDatosAnuales = new Collection();

                foreach ($indicador->datosAnuales as $datoAnual) {
                    $yearDelDato = $datoAnual->anio;
                    if ($yearDelDato >= 2020 && $yearDelDato <= Carbon::now()->year) {
                        if (!empty($datoAnual->fecha_actualizacion)) {
                            try {
                                $fecha = Carbon::parse($datoAnual->fecha_actualizacion);
                                if ($fecha->isValid()) {
                                    $fechasValidasEnDatosAnuales->push($fecha);
                                }
                            } catch (\Exception $e) {
                                continue;
                            }
                        }
                    }
                }

                if ($fechasValidasEnDatosAnuales->isNotEmpty()) { //
                    return $fechasValidasEnDatosAnuales->max()->format('Y-m-d'); //
                }

                // Fallback a la fecha_actualizacion del indicador principal si no hay fechas en datosAnuales
                if (!empty($indicador->fecha_actualizacion)) {
                    try {
                        return Carbon::parse($indicador->fecha_actualizacion)->format('Y-m-d'); //
                    } catch (\Exception $e) {
                        return null;
                    }
                }
                return null;
            };

            $indicadoresProximos = new Collection();
            $indicadoresATiempo = new Collection();
            $indicadoresCaducados = new Collection();

            Indicador::with('datosAnuales')->get()->each(function ($indicador) use ($hoy, $getFechaMasReciente, &$indicadoresProximos, &$indicadoresATiempo, &$indicadoresCaducados) {
                $fechaMasReciente = $getFechaMasReciente($indicador);
                $indicador->setAttribute('fecha_actualizacion_calculada', $fechaMasReciente); //

                if ($fechaMasReciente) {
                    if ($fechaMasReciente > $hoy) {
                        $indicadoresProximos->push($indicador);
                    } elseif ($fechaMasReciente == $hoy) {
                        $indicadoresATiempo->push($indicador);
                    } else {
                        $indicadoresCaducados->push($indicador);
                    }
                }
            });
            /**------------------------------------------------------------------------------------------- */
            // Cantidad de indicadores validados por enlace
            $usuariosEnlace = User::whereHas('roles', function ($query) {
                $query->where('name', 'Enlace');
            })->with('instituciones.indicadores')->get();

            $datosGraficas = [];

            foreach ($usuariosEnlace as $usuario) {
                $totalIndicadores = 0;
                $indicadoresValidados = 0;

                foreach ($usuario->instituciones as $institucion) {
                    $totalIndicadores += $institucion->indicadores->count();
                    $indicadoresValidados += $institucion->indicadores->where('indicador_validado', 1)->count();
                }

                if ($totalIndicadores > 0) {
                    $datosGraficas[] = [
                        'id_usuario' => $usuario->id,
                        'nombre' => $usuario->name,
                        'total' => $totalIndicadores,
                        'validados' => $indicadoresValidados,
                        'no_validados' => $totalIndicadores - $indicadoresValidados
                    ];
                }
            }

            $years = range(2015, Carbon::now()->year);
            $datosPorAnio = [];

            foreach ($years as $year) {
                $count = DatoAnual::where('anio', $year)
                    ->whereNotNull('valor_dato')
                    ->count();
                $datosPorAnio[] = $count;
            }
            /**------------------------------------------------------------------------------------------- */
            // Indicadores por periodicidad
            $periodicidades = Indicador::select('periodicidad', DB::raw('COUNT(*) as total'))
                ->groupBy('periodicidad')
                ->get();

            $etiquetas_periodicidades = $periodicidades->pluck('periodicidad');
            $values_periodicidades = $periodicidades->pluck('total');

            /**------------------------------------------------------------------------------------------- */
            $indicadoresSemaforizacion = Indicador::with('datosAnuales') //
                ->get();

            $semaforizacionCounts = [
                "Excedido" => 0,
                "Aceptable" => 0,
                "Moderado" => 0,
                "Insuficiente" => 0,
                "No clasificado" => 0
            ];

            $indicadoresPorSemaforo = [
                "Excedido" => [],
                "Aceptable" => [],
                "Moderado" => [],
                "Insuficiente" => [],
                "No clasificado" => []
            ];

            foreach ($indicadoresSemaforizacion as $indicador) {
                $resultado = $indicador->calcularSemaforizacion();

                $indicador->anio_ultimo_dato = $resultado['anio_ultimo_dato'];
                $indicador->ultimo_dato = $resultado['ultimo_dato'];
                $indicador->avance = $resultado['avance'];
                $indicador->semaforizacion = $resultado['semaforizacion'];

                if (isset($semaforizacionCounts[$resultado['semaforizacion']])) {
                    $semaforizacionCounts[$resultado['semaforizacion']]++;
                    $indicadoresPorSemaforo[$resultado['semaforizacion']][] = $indicador;
                } else {
                    if (isset($semaforizacionCounts["No clasificado"])) {
                        $semaforizacionCounts["No clasificado"]++;
                        $indicadoresPorSemaforo["No clasificado"][] = $indicador;
                    }
                }
            }

            // 1. Distribución por Tendencia
            $tendenciaCounts = [
                "Mayor es mejor" => 0,
                "Menor es mejor" => 0,
                "Constante" => 0,
                "No definida" => 0
            ];

            foreach ($indicadoresSemaforizacion as $indicador) {
                $tend = trim((string)$indicador->tendencia);
                if (empty($tend)) {
                    $tendenciaCounts["No definida"]++;
                } elseif (isset($tendenciaCounts[$tend])) {
                    $tendenciaCounts[$tend]++;
                } else {
                    $tendenciaCounts["No definida"]++;
                }
            }

            // 2. Top 5: Indicadores con Avance más Bajo (Focos Rojos)
            $focosRojos = collect($indicadoresSemaforizacion)
                ->filter(function ($ind) {
                    return $ind->semaforizacion === "Insuficiente" && $ind->avance !== null;
                })
                ->sortBy('avance')
                ->take(5);

            // 3. Instituciones Críticas (Más indicadores Insuficientes + Caducados)
            $institucionesCriticas = Institucion::where('id', '!=', 1)
                ->get()
                ->map(function ($inst) use ($indicadoresPorSemaforo, $indicadoresCaducados) {
                    $insuficientes = collect($indicadoresPorSemaforo['Insuficiente'] ?? [])
                        ->where('id_institucion', $inst->id)
                        ->count();

                    $caducados = $indicadoresCaducados->where('id_institucion', $inst->id)->count();

                    $inst->total_criticos = $insuficientes + $caducados;
                    $inst->conteo_insuficientes = $insuficientes;
                    $inst->conteo_caducados = $caducados;

                    return $inst;
                })
                ->filter(function ($inst) {
                    return $inst->total_criticos > 0;
                })
                ->sortByDesc('total_criticos')
                ->take(5);

            /**------------------------------------------------------------------------------------------- */
            return view('dashboard', compact(
                'instituciones',
                'porcentajeValidado',
                'totalIndicadoresValidados',
                'totalIndicadores',
                'porcentajeIncompletos',
                'totalIndicadoresIncompletos',
                'indicadoresRecientes',
                'institucionesSinIndicadores',
                'indicadoresProximos',
                'indicadoresATiempo',
                'indicadoresCaducados',
                'datosGraficas',
                'years',
                'datosPorAnio',
                'etiquetas_periodicidades',
                'values_periodicidades',
                'semaforizacionCounts',
                'indicadoresSemaforizacion',
                'indicadoresPorSemaforo',
                'avanceGlobalPromedio',
                'metricasGlobal',
                'colorAvanceGlobal',
                'programasData',
                'tendenciaCounts',
                'focosRojos',
                'institucionesCriticas'
            ));
        }
    }

    /**
     * Muestra una lista filtrada de indicadores según su estado de semaforización.
     * Es el endpoint al que apuntan los clics en el gráfico de semaforización.
     *
     * @param  string $categoria La categoría de semaforización (Ej. "Aceptable").
     * @return \Illuminate\View\View
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
     * Es el endpoint al que apuntan los clics en los gráficos de avance por Enlace.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id ID del usuario Enlace.
     * @return \Illuminate\View\View
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

        return view('users.indicadores', compact('usuario', 'indicadores', 'filtro')); //
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
            ['class' => CatProgramaDerivadoEspecial::class, 'nombre' => 'Programas Especiales', 'slug' => 'especiales'],
            ['class' => CatProgramaDerivadoInstitucional::class, 'nombre' => 'Programas Institucionales', 'slug' => 'institucionales'],
            ['class' => CatProgramaDerivadoRegional::class, 'nombre' => 'Programas Regionales', 'slug' => 'regionales'],
            ['class' => CatProgramaDerivadoSectorial::class, 'nombre' => 'Programas Sectoriales', 'slug' => 'sectoriales'],
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
                    'avance' => $avance,
                    'color' => $prog->color,
                    'semaforo_color' => $this->getSemaforoColor($avance),
                    'total_indicadores' => $indicadores->count()
                ];
            }
        }

        return collect($resultados)->sortBy('id')->values()->toArray();
    }

    /**
     * Determina el color del semáforo basado en el avance.
     */
    private function getSemaforoColor($avance)
    {
        if ($avance === null || $avance == 0) return '#adb5bd';
        if ($avance >= 110) return '#0d6efd';
        if ($avance >= 91) return '#198754';
        if ($avance >= 71) return '#ffc107';
        return '#dc3545';
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Indicador;
use App\Models\SliderInicio;
use App\Models\CatEje;
use App\Models\CatPlanEstatalDesarrollo;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\CarruselIndicador;
use App\Models\CatRegion;
use App\Models\Institucion;
use App\Models\Odses;
use Illuminate\Support\Facades\Log;
use App\Models\CatProgramaDerivadoSectorial;
use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\CatProgramaDerivadoEspecial;
use App\Models\CatProgramaDerivadoRegional;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class HomeController
 * * Controlador principal para manejar las vistas públicas y la presentación
 * de los indicadores, programas (PED, Sectoriales, Especiales, etc.) y la agenda ODS.
 * * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    /**
     * Muestra la ficha técnica detallada de un indicador específico.
     *
     * @param  \App\Models\Indicador  $indicador Modelo inyectado (por Route Model Binding usando el slug).
     * @return \Illuminate\View\View
     */
    public function show(Indicador $indicador)
    {
        // 1. Cargamos el indicador con sus relaciones.
        $indicador->load(['datosAnuales' => function ($q) {
            $q->where('validado', true);
        }, 'ods', 'indicadorable', 'programasInstitucionales']);

        // --- LÓGICA DE COLORES ---
        $colorFinal = null;
        $colorPorDefectoGeneral = '#691A32';

        // A. INTENTO 1: Obtener color directamente de la relación polimórfica (La forma elegante)
        if ($indicador->indicadorable && isset($indicador->indicadorable->color)) {
            $colorFinal = $indicador->indicadorable->color;
        }

        // B. INTENTO 2: Si la relación polimórfica falló, buscar por TEMÁTICA
        if (!$colorFinal && $indicador->tematica) {
            $colorFinal = DB::table('cat_colores')
                ->where('tipo', 'programa')
                ->where('clave', $indicador->tematica)
                ->value('color');
        }

        // C. INTENTO 3: Si todo falla, buscar por PROGRAMA GENERAL
        if (!$colorFinal && $indicador->programa) {
            $colorFinal = DB::table('cat_colores')
                ->where('tipo', 'programa')
                ->where('clave', $indicador->programa)
                ->value('color');
        }

        // 4. Asignar el color final o el default
        $indicador->color = $colorFinal ?? $colorPorDefectoGeneral;

        return view('ficha-tecnica', compact('indicador'));
    }

    /**
     * Muestra los indicadores agrupados por eje del Plan Estatal de Desarrollo (PED).
     *
     * @param  int|string $num Número del eje del PED.
     * @return \Illuminate\View\View
     */
    public function ped($num)
    {
        // 1. Realiza la consulta específica para obtener los indicadores
        $indicadoresCollection = $this->consultarIndicadoresPed($num);

        if ($indicadoresCollection->isEmpty()) {
            Log::warning("HomeController@ped: No se encontraron indicadores para el eje/num: {$num} desde consultarIndicadoresPed.");
        }

        // 2. Procesar cada indicador para agregar el dato más reciente y avance validado
        $avanceEje = $this->prepararIndicadoresParaVista($indicadoresCollection);

        // 3. Agrupar la colección de indicadores por el campo 'tematica'
        $indicadoresAgrupados = $indicadoresCollection->groupBy('tematica');

        // 4. Devuelve la vista con los datos agrupados.
        return view('eje' . $num . '-ped', [
            'indicadoresAgrupados' => $indicadoresAgrupados,
            'avanceEje' => $avanceEje
        ]);
    }

    /**
     * Consulta específica para obtener los indicadores del PED según su eje.
     *
     * @param  int|string $num Número del eje.
     * @return Collection<\App\Models\Indicador> Colección de indicadores.
     */
    private function consultarIndicadoresPed($num)
    {
        $programas = [
            1 => 'Humanismo con Bienestar',
            2 => 'Prosperidad y Estabilidad Económica',
            3 => 'Estado de Derecho, Seguridad y Justicia',
            4 => 'Desarrollo Urbano y Crecimiento Sostenible',
            5 => 'Gobierno Transformador y de Resultados',
            6 => 'Por Amor a Puebla',
        ];

        if (!array_key_exists($num, $programas)) {
            return collect();
        }

        $nombreProgramaFiltrar = $programas[$num];
        $nombreProgramaFijo = 'Plan Estatal de Desarrollo';

        $query = Indicador::with([
            'datosAnuales' => function ($q_datos) {
                $q_datos->where('validado', true)
                    ->select('id', 'id_indicador', 'anio', 'valor_dato', 'validado' /*, 'resultados', 'observaciones', 'evidencia', 'fecha_actualizacion' */);
            },
            'ods'
        ])
            ->where('programa', $nombreProgramaFiltrar)
            ->where('programa_derivado', $nombreProgramaFijo)
            ->orderBy('id', 'asc');

        $indicadores = $query->get();

        if ($indicadores->isEmpty()) {
            Log::info("HomeController@consultarIndicadoresPed: No se encontraron indicadores para programa_derivado='{$nombreProgramaFiltrar}' y programa='{$nombreProgramaFijo}'.");
        } else {
            Log::info("HomeController@consultarIndicadoresPed: Se encontraron {$indicadores->count()} indicadores para programa_derivado='{$nombreProgramaFiltrar}' y programa='{$nombreProgramaFijo}'.");
        }

        return $indicadores;
    }

    /**
     * Obtiene el dato anual numérico validado más reciente de una colección.
     *
     * @param  Collection<\App\Models\DatoAnual>|null $datosAnualesCollection
     * @return array{anio: int|null, valor: float|string|null} Arreglo con el año y el valor.
     */
    private function obtenerDatoReciente($datosAnualesCollection)
    {
        if (!$datosAnualesCollection || !($datosAnualesCollection instanceof \Illuminate\Database\Eloquent\Collection) || $datosAnualesCollection->isEmpty()) {
            return [
                'anio' => null,
                'valor' => null,
            ];
        }

        $datoRecienteEncontrado = $datosAnualesCollection
            ->filter(function ($datoAnual) {
                return isset($datoAnual->valor_dato) &&
                    !is_null($datoAnual->valor_dato) &&
                    trim((string) $datoAnual->valor_dato) !== '';
            })
            ->sortByDesc('anio')
            ->first();

        if ($datoRecienteEncontrado) {
            $anio = $datoRecienteEncontrado->anio;
            $valorOriginal = $datoRecienteEncontrado->valor_dato;
            try {
                $valorNumerico = filter_var($valorOriginal, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);

                if (is_numeric($valorNumerico)) {
                    $valorFloat = (float) str_replace(',', '', $valorNumerico);

                    $valorFormateado = number_format($valorFloat, 2, '.', '');
                    return [
                        'anio' => $anio,
                        'valor' => $valorFormateado,
                    ];
                } else {
                    return [
                        'anio' => $anio,
                        'valor' => $valorOriginal,
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'anio' => $anio,
                    'valor' => $valorOriginal,
                ];
            }
        }

        return [
            'anio' => null,
            'valor' => null,
        ];
    }

    /**
     * Calcula los atributos dinámicos (semaforización, avance) para una colección de indicadores
     * y retorna el promedio de avance global del grupo.
     *
     * @param  Collection<\App\Models\Indicador> $indicadoresCollection
     * @return float Porcentaje promedio de avance del grupo de indicadores.
     */
    private function prepararIndicadoresParaVista($indicadoresCollection)
    {
        $sumAvance = 0;
        $count = 0;

        $indicadoresCollection->each(function ($indicador) use (&$sumAvance, &$count) {
            $datoRecienteInfo = $this->obtenerDatoReciente($indicador->datos_anuales_validados);

            $anioParaVista = $datoRecienteInfo['anio'];
            $valorParaVista = $datoRecienteInfo['valor'];

            if (is_null($valorParaVista)) {
                $anioParaVista = $indicador->linea_base;
                $valorOriginalLB = $indicador->dato_linea_base;
                if ($valorOriginalLB !== null && trim((string)$valorOriginalLB) !== '') {
                    $valorNumericoLB = filter_var($valorOriginalLB, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
                    if (is_numeric($valorNumericoLB)) {
                        $valorParaVista = number_format((float)str_replace(',', '', $valorNumericoLB), 2, '.', '');
                    } else {
                        $valorParaVista = $valorOriginalLB;
                    }
                } else {
                    $valorParaVista = 'Sin datos';
                }
            }
            $indicador->setAttribute('dato_reciente', $valorParaVista);
            $indicador->setAttribute('anio_reciente', $anioParaVista);

            $resultado = $indicador->calcularSemaforizacion(true);
            $indicador->setAttribute('avance_validado', $resultado['avance']);
            $indicador->setAttribute('semaforizacion_validada', $resultado['semaforizacion']);
            $indicador->setAttribute('dato_reciente_validado', $resultado['ultimo_dato']);
            $indicador->setAttribute('anio_reciente_validado', $resultado['anio_ultimo_dato']);

            if ($resultado['avance'] !== null) {
                $sumAvance += $resultado['avance'];
                $count++;
            }
        });

        return $count > 0 ? round($sumAvance / $count, 2) : 0;
    }

    /**
     * Muestra el listado general de programas sectoriales.
     *
     * @return \Illuminate\View\View
     */
    public function mostrarListadoSectoriales()
    {
        $sectoriales = CatProgramaDerivadoSectorial::has('indicadores')->get();
        return view('ped-programas-sectoriales', compact('sectoriales'));
    }

    /**
     * Muestra la vista detallada de un programa sectorial y sus indicadores.
     *
     * @param  string $slug Slug identificador del programa.
     * @return \Illuminate\View\View
     */
    public function mostrarSectorial($slug)
    {
        $programa = CatProgramaDerivadoSectorial::all()->first(function ($item) use ($slug) {
            return Str::slug($item->nombre) === $slug;
        });

        if (!$programa) abort(404, 'Programa no encontrado');

        $color = $programa->color ?? '#691A32';
        $descripcion = $programa->descripcion ?? 'Sin descripción';
        $imagen = $programa->imagen ?? 'img/pleca-pajaro-2.png';
        $programaData = $programa;

        $indicadores = $programa->indicadores()->with(['datosAnuales' => function ($q) {
            $q->where('validado', true);
        }, 'ods'])->orderBy('id', 'asc')->get();

        $avancePrograma = $this->prepararIndicadoresParaVista($indicadores);

        return view('programa-sectorial', compact('indicadores', 'programa', 'color', 'descripcion', 'imagen', 'programaData', 'avancePrograma'));
    }

    /**
     * Muestra la vista detallada de un programa especial y sus indicadores.
     *
     * @param  string $slug Slug identificador del programa.
     * @return \Illuminate\View\View
     */
    public function mostrarEspecial($slug)
    {
        $programa = CatProgramaDerivadoEspecial::all()->first(function ($item) use ($slug) {
            return Str::slug($item->nombre) === $slug;
        });

        if (!$programa) abort(404, 'Programa no encontrado');

        // 2. Datos estéticos
        $color = $programa->color ?? '#691A32';
        $descripcion = $programa->descripcion ?? 'Sin descripción';
        $imagen = $programa->imagen ?? 'img/pleca-pajaro-2.png';
        $programaData = $programa;

        $indicadores = $programa->indicadores()->with(['datosAnuales' => function ($q) {
            $q->where('validado', true);
        }, 'ods'])->orderBy('id', 'asc')->get();

        $avancePrograma = $this->prepararIndicadoresParaVista($indicadores);

        return view('programa-especial', compact('indicadores', 'programa', 'color', 'descripcion', 'imagen', 'programaData', 'avancePrograma'));
    }

    /**
     * Muestra el listado general de programas especiales.
     *
     * @return \Illuminate\View\View
     */
    public function mostrarListadoEspeciales()
    {
        $especiales = CatProgramaDerivadoEspecial::has('indicadores')->get();
        return view('ped-programas-especiales', compact('especiales'));
    }

    /**
     * Muestra el listado general de programas institucionales.
     *
     * @return \Illuminate\View\View
     */
    public function mostrarListadoInstitucionales()
    {
        $programas = CatProgramaDerivadoInstitucional::all();
        $grupos = CatProgramaDerivadoInstitucional::select('grupo')
            ->whereNotNull('grupo')
            ->where('grupo', '!=', '')
            ->distinct()
            ->pluck('grupo');

        return view('ped-programas-institucionales', compact('programas', 'grupos'));
    }

    /**
     * Muestra la vista detallada de un programa institucional y sus indicadores.
     *
     * @param  string $slug Slug identificador del programa.
     * @return \Illuminate\View\View
     */
    public function mostrarInstitucional($slug)
    {
        $programa = CatProgramaDerivadoInstitucional::all()->first(function ($item) use ($slug) {
            return Str::slug($item->nombre) === $slug;
        });

        if (!$programa) abort(404, 'Programa no encontrado');

        $color = $programa->color ?? '#691A32';
        $descripcion = $programa->descripcion ?? 'Sin descripción';
        $imagen = $programa->imagen ?? 'img/pleca-pajaro-2.png';
        $programaData = $programa;

        $indicadores = $programa->indicadores()->with(['datosAnuales' => function ($q) {
            $q->where('validado', true);
        }, 'ods'])->orderBy('id', 'asc')->get();

        $avancePrograma = $this->prepararIndicadoresParaVista($indicadores);

        return view('programa-institucional', compact('indicadores', 'programa', 'color', 'descripcion', 'imagen', 'programaData', 'avancePrograma'));
    }

    /**
     * Muestra la página principal (Home) con el dashboard del PED.
     *
     * @return \Illuminate\View\View
     */
    public function mostrarCarrusel()
    {
        // Plan Estatal por defecto: ID 3 (2024-2030)
        $planId = 3;
        $plan = CatPlanEstatalDesarrollo::find($planId);
        if (!$plan) {
            $plan = CatPlanEstatalDesarrollo::where('nombre', 'like', '%2024-2030%')->first();
            if ($plan) $planId = $plan->id;
        }

        $soloValidados = true; // Vista pública siempre usa validados

        // 1. Todos los indicadores del PED para calcular avance general
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

        $totalIndicadores = $indicadoresPlan->count();
        $avancePlan = $this->calcularPromedioAvanceInicio($indicadoresPlan, $soloValidados);
        $colorPlan = $this->getSemaforoColorInicio($avancePlan);

        // 2. Distribución por rangos de semáforo
        $distribucionGeneral = $this->calcularDistribucionRangos($indicadoresPlan, $soloValidados);

        // 3. Avance por Ejes
        $ejes = CatEje::with('indicadores')->where('plan_id', $planId)->orderBy('numero')->get();
        $ejesData = $ejes->map(function ($eje) use ($soloValidados) {
            $indicadores = $eje->indicadores;
            $avance = $this->calcularPromedioAvanceInicio($indicadores, $soloValidados);
            return [
                'id' => $eje->id,
                'nombre' => $eje->nombre ?? 'No se encontró',
                'numero' => $eje->numero ?? 'ND',
                'color' => $eje->color ?? '#CCCCCC',
                'semaforo_color' => $this->getSemaforoColorInicio($avance),
                'avance' => $avance,
                'total_indicadores' => $indicadores->count(),
                'distribucion' => $this->calcularDistribucionRangos($indicadores, $soloValidados),
            ];
        });

        // 4. Avance por Programas Derivados
        $programasData = $this->getProgramasAvanceInicio($planId, $soloValidados);

        // 5. Grupos de Programas Institucionales para filtrado en la vista
        $gruposInstitucionales = CatProgramaDerivadoInstitucional::select('grupo')
            ->whereNotNull('grupo')
            ->where('grupo', '!=', '')
            ->distinct()
            ->pluck('grupo');

        return view('inicio', compact(
            'plan',
            'avancePlan',
            'colorPlan',
            'totalIndicadores',
            'distribucionGeneral',
            'ejesData',
            'programasData',
            'gruposInstitucionales'
        ));
    }

    /**
     * Calcula la distribución de indicadores por rangos de semáforo.
     */
    private function calcularDistribucionRangos($indicadores, $soloValidados)
    {
        $rangos = ['rojo' => 0, 'amarillo' => 0, 'verde' => 0, 'azul' => 0, 'sin_datos' => 0];

        foreach ($indicadores as $indicador) {
            $res = $indicador->calcularSemaforizacion($soloValidados);
            $avance = $res['avance'];

            if ($avance === null || $avance == 0) {
                $rangos['sin_datos']++;
            } elseif ($avance >= 110) {
                $rangos['azul']++;
            } elseif ($avance >= 91) {
                $rangos['verde']++;
            } elseif ($avance >= 71) {
                $rangos['amarillo']++;
            } else {
                $rangos['rojo']++;
            }
        }

        return $rangos;
    }

    /**
     * Calcula el promedio de avance (replica lógica de DashboardGeneralController).
     */
    private function calcularPromedioAvanceInicio($indicadores, $soloValidados)
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
     * Obtiene el avance de programas derivados (replica lógica de DashboardGeneralController).
     */
    private function getProgramasAvanceInicio($planId, $soloValidados)
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
                $avance = $this->calcularPromedioAvanceInicio($indicadores, $soloValidados);
                $resultados[] = [
                    'id' => $prog->id,
                    'nombre' => $prog->nombre,
                    'tipo' => $tipo['nombre'],
                    'tipo_slug' => $tipo['slug'],
                    'tipo_order' => $tipo['order'],
                    'avance' => $avance,
                    'color' => $prog->color,
                    'semaforo_color' => $this->getSemaforoColorInicio($avance),
                    'total_indicadores' => $indicadores->count(),
                    'grupo' => $tipo['nombre'] === 'Institucionales' ? ($prog->grupo ?? null) : null,
                ];
            }
        }

        return collect($resultados)->sortBy('tipo_order')->values();
    }

    /**
     * Semáforo de color según avance (rangos SPED).
     */
    private function getSemaforoColorInicio($avance)
    {
        if ($avance === null || $avance == 0) return '#adb5bd';
        if ($avance >= 110) return '#0d6efd';
        if ($avance >= 91) return '#198754';
        if ($avance >= 71) return '#ffc107';
        return '#dc3545';
    }

    /**
     * Helper específico para obtener el dato reciente del carrusel.
     *
     * @param  \App\Models\Indicador $indicador
     * @return array{anio: int|string|null, valor: float|string|null}
     */
    private function obtenerDatoRecienteCarrusel(Indicador $indicador)
    {
        $datosAnualesCollection = $indicador->datos_anuales_validados;

        if ($datosAnualesCollection && $datosAnualesCollection->isNotEmpty()) {
            $datoRecienteEncontrado = $datosAnualesCollection
                ->filter(function ($datoAnual) {
                    return isset($datoAnual->valor_dato) &&
                        !is_null($datoAnual->valor_dato) &&
                        trim((string) $datoAnual->valor_dato) !== '';
                })
                ->sortByDesc('anio')
                ->first();

            if ($datoRecienteEncontrado) {
                $anio = $datoRecienteEncontrado->anio;
                $valorOriginal = $datoRecienteEncontrado->valor_dato;
                try {
                    $valorNumerico = filter_var($valorOriginal, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
                    if (is_numeric($valorNumerico)) {
                        $valorFloat = (float) str_replace(',', '', $valorNumerico);
                        return [
                            'anio' => $anio,
                            'valor' => number_format($valorFloat, 2, '.', ''),
                        ];
                    } else {
                        return ['anio' => $anio, 'valor' => $valorOriginal];
                    }
                } catch (\Exception $e) {
                    return ['anio' => $anio, 'valor' => $valorOriginal];
                }
            }
        }

        $valorLineaBase = $indicador->dato_linea_base;

        $anioLineaBase = $indicador->linea_base ?? 'Línea base';

        if ($valorLineaBase !== null && trim((string)$valorLineaBase) !== '') {
            try {
                $valorNumericoLB = filter_var($valorLineaBase, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
                if (is_numeric($valorNumericoLB)) {
                    $valorFloatLB = (float) str_replace(',', '', $valorNumericoLB);
                    return [
                        'anio' => $anioLineaBase,
                        'valor' => number_format($valorFloatLB, 2, '.', ''),
                    ];
                } else {
                    return ['anio' => $anioLineaBase, 'valor' => $valorLineaBase];
                }
            } catch (\Exception $e) {
                return ['anio' => $anioLineaBase, 'valor' => $valorLineaBase];
            }
        }

        return [
            'anio' => $anioLineaBase,
            'valor' => 'Sin datos',
        ];
    }

    /**
     * Muestra la vista de Agenda ODS (versión 2024 = 0).
     *
     * @return \Illuminate\View\View
     */
    public function indicadoresAgenda1()
    {
        $odsResultados = [];

        for ($ods = 1; $ods <= 17; $ods++) {
            $resultados = DB::table('indicadors as i')
                ->join('indicador_ods as io', 'i.id', '=', 'io.id_indicador')
                ->select(DB::raw('COUNT(DISTINCT i.id) AS numero_indicadores, i.programa_derivado'))
                ->where('io.id_ods', $ods)
                ->where('i.version_2024', 0)
                ->groupBy('i.programa_derivado')
                ->get();

            $odsResultados[$ods] = $resultados;
        }
        $totalIndicadores = Indicador::where('version_2024', '0')->count();
        return view('agenda', compact('odsResultados', 'totalIndicadores'));
    }

    /**
     * Muestra la vista de Agenda ODS (versión 2024 = 1).
     *
     * @return \Illuminate\View\View
     */
    public function indicadoresAgenda2()
    {
        $odsResultados = [];

        for ($ods = 1; $ods <= 17; $ods++) {
            $resultados = DB::table('indicadors as i')
                ->join('indicador_ods as io', 'i.id', '=', 'io.id_indicador')
                ->select(DB::raw('COUNT(DISTINCT i.id) AS numero_indicadores, i.programa_derivado'))
                ->where('io.id_ods', $ods)
                ->where('i.version_2024', 1)
                ->groupBy('i.programa_derivado')
                ->get();

            $odsResultados[$ods] = $resultados;
        }
        $totalIndicadores = Indicador::where('version_2024', '1')->count();

        return view('agenda2', compact('odsResultados', 'totalIndicadores'));
    }

    /**
     * Muestra el listado general de programas regionales.
     *
     * @return \Illuminate\View\View
     */
    public function mostrarListadoRegionales()
    {
        $regionales = CatProgramaDerivadoRegional::has('indicadores')->get();
        return view('ped-programas-regionales', compact('regionales'));
    }

    /**
     * Muestra la vista detallada de un programa regional y sus indicadores.
     *
     * @param  string $slug Slug identificador del programa regional.
     * @return \Illuminate\View\View
     */
    public function mostrarRegional($slug)
    {
        $programa = CatProgramaDerivadoRegional::all()->first(function ($item) use ($slug) {
            return Str::slug($item->nombre) === $slug;
        });

        if (!$programa) abort(404, 'Programa no encontrado');

        $color = $programa->color ?? '#691A32';
        $descripcion = $programa->descripcion ?? 'Sin descripción';
        $imagen = $programa->imagen ?? 'img/pleca-pajaro-2.png';
        $programaData = $programa;

        $indicadores = $programa->indicadores()->with(['datosAnuales' => function ($q) {
            $q->where('validado', true);
        }, 'ods'])->orderBy('id', 'asc')->get();

        $avancePrograma = $this->prepararIndicadoresParaVista($indicadores);

        return view('programa-regional', compact('indicadores', 'programa', 'color', 'descripcion', 'imagen', 'programaData', 'avancePrograma'));
    }

    /**
     * Genera una vista de ficha técnica estática (para impresión o PDF) 
     * buscando por ID explícito en vez de slug.
     *
     * @param  int|string $id ID del indicador.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function generarFicha($id)
    {
        $indicador = Indicador::with(['datosAnuales' => function ($q) {
            $q->where('validado', true);
        }, 'ods'])
            ->where('id', $id)
            ->first();

        if (!$indicador) {
            return redirect()->back()->with('error', 'Indicador no encontrado.');
        }

        $coloresBase = DB::table('cat_colores')
            ->whereIn('tipo', ['programa'])
            ->get()
            ->groupBy('tipo');

        $obtenerColorBase = function ($tipo, $clave, $default = null) use ($coloresBase) {
            if (!isset($coloresBase[$tipo])) return $default;
            $colorEncontrado = $coloresBase[$tipo]->firstWhere('clave', $clave);
            return $colorEncontrado ? $colorEncontrado->color : $default;
        };

        $colorFinal = null;
        $colorPorDefectoGeneral = '#691A32';

        switch ($indicador->programa_derivado) {
            case 'Programa Especial':
                $colorFinal = DB::table('cat_programas_derivados_especiales')
                    ->where('nombre', $indicador->programa)
                    ->value('color');
                break;
            case 'Programa Institucional':
                $colorFinal = DB::table('cat_programas_derivados_institucionales')
                    ->where('nombre', $indicador->programa)
                    ->value('color');
                break;
            case 'Programa Sectorial':
                $colorFinal = DB::table('cat_programas_derivados_sectoriales')
                    ->where('nombre', $indicador->programa)
                    ->value('color');
                break;
        }

        if (!$colorFinal) {
            $colorFinal = $obtenerColorBase('tematica_v1', $indicador->tematica);
        }
        if (!$colorFinal) {
            $colorFinal = $obtenerColorBase('programa', $indicador->programa);
        }
        $indicador->color = $colorFinal ?? $colorPorDefectoGeneral;
        // --- FIN DE LA LÓGICA DEL COLOR ---
        // Obtener el dato más reciente (solo validados)
        $datoReciente = $this->obtenerDatoReciente($indicador->datos_anuales_validados);
        $indicador->setAttribute('dato_reciente', $datoReciente['valor']);
        $indicador->setAttribute('anio_reciente', $datoReciente['anio']);

        // Pasar los datos a la vista
        return view('generar-ficha', compact('indicador'));
    }

    /**
     * Muestra la vista estática de Capacitación 2025.
     *
     * @return \Illuminate\View\View
     */
    public function capacitacion2025()
    {
        return view('capacitacion-2025');
    }

    /**
     * Muestra la vista interactiva de documentación y consulta de la API de indicadores.
     */
    public function apiDocs()
    {
        $excluidas = ['Administración del SPED', 'Dependencia'];
        $instituciones = Institucion::select('id', 'nombre')
            ->whereNotIn('nombre', $excluidas)
            ->orderBy('nombre', 'asc')
            ->get();
        $ods = Odses::select('id', 'nombre')->orderBy('id', 'asc')->get();
        $programasDerivados = Indicador::distinct()
            ->whereNotNull('programa_derivado')
            ->where('programa_derivado', '!=', '')
            ->orderBy('programa_derivado', 'asc')
            ->pluck('programa_derivado');

        return view('publico.api_docs', compact('instituciones', 'ods', 'programasDerivados'));
    }
}

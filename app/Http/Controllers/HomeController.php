<?php

namespace App\Http\Controllers;

use App\Models\Indicador;
use App\Models\SliderInicio;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\CarruselIndicador;
use App\Models\CatRegion;
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
        }, 'ods', 'indicadorable']);

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
        $imagen = $programa->imagen ?? 'img/secretarias/Sectorial1.jpg';
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
        $imagen = $programa->imagen ?? 'img/secretarias/Sectorial1.jpg';
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
        $programas = CatProgramaDerivadoInstitucional::has('indicadores')->get();
        return view('ped-programas-institucionales', compact('programas'));
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
        $imagen = $programa->imagen ?? 'img/secretarias/Sectorial1.jpg';
        $programaData = $programa;

        $indicadores = $programa->indicadores()->with(['datosAnuales' => function ($q) {
            $q->where('validado', true);
        }, 'ods'])->orderBy('id', 'asc')->get();

        $avancePrograma = $this->prepararIndicadoresParaVista($indicadores);

        return view('programa-institucional', compact('indicadores', 'programa', 'color', 'descripcion', 'imagen', 'programaData', 'avancePrograma'));
    }

    /**
     * Muestra la página principal (Home) con el carrusel de indicadores y sliders.
     *
     * @return \Illuminate\View\View
     */
    public function mostrarCarrusel()
    {
        $carruselItems = CarruselIndicador::with(['indicador.datosAnuales' => function ($q) {
            $q->where('validado', true);
        }])->get();
        $todosLosIndicadores = Indicador::all();


        $imagenesPath = public_path('img/iconos_indicadores');
        $imagenes = [];
        if (is_dir($imagenesPath)) {
            $imagenes = array_diff(scandir($imagenesPath), ['.', '..']);
        }

        $carruselItems->each(function ($item) {
            if ($item->indicador) {
                $datoReciente = $this->obtenerDatoRecienteCarrusel($item->indicador);

                $item->ultimo_dato = $datoReciente['valor'];
                $item->anio_mas_reciente = $datoReciente['anio'];
            } else {
                $item->ultimo_dato = 'Sin datos';
                $item->anio_mas_reciente = null;
            }
        });

        $indicadoresRecientes = Indicador::with('datosAnuales')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        $sliders = SliderInicio::where('activo', '1')->get();

        return view('inicio', compact(
            'carruselItems',
            'todosLosIndicadores',
            'imagenes',
            'indicadoresRecientes',
            'sliders'
        ));
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
        $imagen = $programa->imagen ?? 'img/secretarias/Sectorial1.jpg';
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
}

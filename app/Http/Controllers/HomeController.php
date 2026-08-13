<?php

namespace App\Http\Controllers;

use App\Models\Indicador;
use App\Models\CatEje;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\CatRegion;
use App\Models\Institucion;
use App\Models\Odses;
use Illuminate\Support\Facades\Log;
use App\Models\CatProgramaDerivadoSectorial;
use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\CatProgramaDerivadoEspecial;
use App\Models\CatProgramaDerivadoRegional;
use App\Services\PedMetricsService;
use App\Services\ActivePlanResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;

/**
 * Class HomeController
 * * Controlador principal para manejar las vistas públicas y la presentación
 * de los indicadores, programas (PED, Sectoriales, Especiales, etc.) y la agenda ODS.
 * * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    public function __construct(
        private PedMetricsService $pedMetrics,
        private ActivePlanResolver $activePlan
    )
    {
    }

    /**
     * Muestra la ficha técnica detallada de un indicador específico.
     *
     * @param  Indicador  $indicador
     * @return \Illuminate\View\View
     */
    public function show(Indicador $indicador)
    {
        return view('ficha-tecnica', $this->fichaData($indicador));
    }

    /**
     * Muestra la misma plantilla que se utiliza para generar el PDF.
     */
    public function fichaPreview(Indicador $indicador)
    {
        return view('ficha-tecnica-pdf', $this->fichaPdfData($indicador));
    }

    /**
     * Genera la ficha mediante Chromium para conservar el diseño CSS real.
     */
    public function downloadFicha(Indicador $indicador)
    {
        $nombre = Str::slug($indicador->nombre ?: 'indicador');
        $html = view('ficha-tecnica-pdf', $this->fichaPdfData($indicador))->render();
        $footer = '<div style="width: 100vw; margin: 0; padding: 0; color: #706b72; font: 9px Arial, sans-serif; text-align: center;">'
            . 'Hoja <span class="pageNumber"></span> de <span class="totalPages"></span></div>';
        $pdf = Browsershot::html($html)
            ->setNodeBinary(config('browsershot.node_binary', 'node'))
            ->setNodeModulePath(base_path('node_modules'))
            ->setEnvironmentOptions([
                'PUPPETEER_CACHE_DIR' => storage_path('app/puppeteer'),
            ])
            ->format('a4')
            ->margins(5, 5, 16, 5)
            ->showBrowserHeaderAndFooter()
            ->footerHtml($footer)
            ->timeout(120)
            ->protocolTimeout(120)
            ->showBackground()
            ->setOption('viewport', [
                'width' => 794,
                'height' => 1123,
                'deviceScaleFactor' => 2,
            ])
            ->setOption('args', [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--font-render-hinting=none',
            ])
            ->waitForFunction('window.pdfReady === true', null, 110000)
            ->pdf();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"ficha-tecnica-{$nombre}.pdf\"",
        ]);
    }

    private function fichaData(Indicador $indicador): array
    {
        // 1. Cargamos el indicador con sus relaciones.
        $indicador->load(['datosAnuales' => function ($q) {
            $q->where('validado', true);
        }, 'ods', 'indicadorable', 'programasInstitucionales']);

        // --- LÓGICA DE COLORES ---
        $colorFinal = null;
        $colorPorDefectoGeneral = '#0c312d';

        // A. INTENTO 1: Obtener color directamente de la relación polimórfica
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

        return compact('indicador');
    }

    private function fichaPdfData(Indicador $indicador): array
    {
        $data = $this->fichaData($indicador);
        $chart = $this->fichaChartData($indicador);

        return [
            'indicador' => $data['indicador'],
            'chartConfig' => $chart['config'],
            'semaforizacion' => $chart['semaforizacion'],
            'colorSemaforo' => $chart['colorSemaforo'],
            'esDatoLineaBase' => $chart['esDatoLineaBase'],
            'pdfAsset' => fn(string $path): string => $this->inlinePublicAsset($path),
            'pdfCss' => $this->inlineFichaPdfCss(),
            'pdfEcharts' => file_get_contents(public_path('js/echarts.min.js')),
            'pdfFichaJs' => file_get_contents(public_path('js/ficha-tecnica.js')),
        ];
    }

    private function fichaChartData(Indicador $indicador): array
    {
        $semaforizacion = $indicador->semaforizacion_validada ?: 'No Clasificado';
        $colors = [
            'excedido' => '#0d6efd',
            'aceptable' => '#198754',
            'moderado' => '#ffc107',
            'insuficiente' => '#dc3545',
            'solo línea base' => '#adb5bd',
        ];
        $colorSemaforo = $colors[mb_strtolower($semaforizacion)] ?? '#6c757d';
        $ultimoDatoValidado = $indicador->datos_anuales_validados
            ->filter(fn($dato) => trim((string) $dato->valor_dato) !== '')
            ->sortByDesc('anio')
            ->first();
        $esDatoLineaBase = !$ultimoDatoValidado
            || ($indicador->linea_base && (int) $ultimoDatoValidado->anio <= (int) $indicador->linea_base);
        $anioInicio = min(
            $indicador->linea_base ? (int) $indicador->linea_base : 2015,
            $indicador->datos_anuales_validados->min('anio') ?: 2015
        );
        $categorias = [];
        $datos = [];
        $lineaBase = [];
        $meta = [];
        $valorLB = $indicador->dato_linea_base !== null
            ? (float) preg_replace('/[^0-9.-]/', '', (string) $indicador->dato_linea_base)
            : null;
        $valorMeta = $indicador->meta !== null
            ? (float) preg_replace('/[^0-9.-]/', '', (string) $indicador->meta)
            : null;

        $anioMeta = (int) ($indicador->meta_anio ?? 2030);
        $anioFin = max(2030, $anioMeta);

        for ($year = $anioInicio; $year <= $anioFin; $year++) {
            $categorias[] = (string) $year;
            $dato = $indicador->datos_anuales_validados->firstWhere('anio', $year);
            $valor = $dato && trim((string) $dato->valor_dato) !== ''
                ? preg_replace('/[^0-9.-]/', '', (string) $dato->valor_dato)
                : null;
            $datos[] = is_numeric($valor) ? (float) $valor : null;
            $lineaBase[] = $year == (int) $indicador->linea_base ? $valorLB : null;
            $meta[] = $year === $anioMeta ? $valorMeta : null;
        }

        $avance = (float) ($indicador->avance_validado ?? $indicador->avance ?? 0);

        return [
            'semaforizacion' => $semaforizacion,
            'colorSemaforo' => $colorSemaforo,
            'esDatoLineaBase' => $esDatoLineaBase,
            'config' => [
                'chartVal' => min(100, $avance),
                'colorSemaforo' => $colorSemaforo,
                'nombreSerieLineaBase' => 'Línea Base ' . ($indicador->linea_base ?? ''),
                'datosLineaBasePunto' => $lineaBase,
                'unidadMedida' => $indicador->unidad_medida ?? 'Valor',
                'datosParaGraficaPrincipal' => $datos,
                'datosMetaPunto' => $meta,
                'colorIndicador' => $indicador->color ?? '#008FFB',
                'categoriasEjeX' => $categorias,
                'esDatoLineaBase' => $esDatoLineaBase,
                'idIndicador' => $indicador->id,
                'pdfMode' => true,
                'ultimoDato' => $ultimoDatoValidado?->valor_dato,
                'anioUltimoDato' => $ultimoDatoValidado?->anio,
            ],
        ];
    }

    private function inlineFichaPdfCss(): string
    {
        $files = [
            public_path('fontAwesome/css/fontawesome.css'),
            public_path('fontAwesome/css/brands.css'),
            public_path('fontAwesome/css/solid.css'),
            public_path('css/media_queries.css'),
            public_path('css/efectos.css'),
            public_path('css/app.css'),
            public_path('css/estilos.css'),
        ];

        $seen = [];
        $css = '';
        foreach ($files as $file) {
            $css .= $this->inlineCssFile($file, $seen) . "\n";
        }

        return $css;
    }

    private function inlineCssFile(string $path, array &$seen): string
    {
        $realPath = realpath($path);
        if (!$realPath || isset($seen[$realPath])) {
            return '';
        }

        $seen[$realPath] = true;
        $css = file_get_contents($realPath);

        $css = preg_replace_callback(
            '/@import\s+(?:url\(\s*)?["\']?([^\)"\';]+)["\']?\s*\)?\s*;/i',
            function (array $match) use ($realPath, &$seen): string {
                $importPath = realpath(dirname($realPath) . DIRECTORY_SEPARATOR . trim($match[1]));
                return $importPath ? $this->inlineCssFile($importPath, $seen) : '';
            },
            $css
        );

        return preg_replace_callback(
            '/url\(\s*(["\']?)([^)"\']+)\1\s*\)/i',
            function (array $match) use ($realPath): string {
                $url = trim($match[2]);
                if ($url === '' || str_starts_with($url, 'data:') || str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '#')) {
                    return $match[0];
                }

                $assetPath = realpath(dirname($realPath) . DIRECTORY_SEPARATOR . $url);
                if (!$assetPath || !is_file($assetPath)) {
                    return $match[0];
                }

                $mime = match (strtolower(pathinfo($assetPath, PATHINFO_EXTENSION))) {
                    'css' => 'text/css',
                    'eot' => 'application/vnd.ms-fontobject',
                    'otf' => 'font/otf',
                    'svg' => 'image/svg+xml',
                    'ttf' => 'font/ttf',
                    'woff' => 'font/woff',
                    'woff2' => 'font/woff2',
                    default => mime_content_type($assetPath) ?: 'application/octet-stream',
                };

                return 'url("data:' . $mime . ';base64,' . base64_encode(file_get_contents($assetPath)) . '")';
            },
            $css
        );
    }

    private function inlinePublicAsset(string $path): string
    {
        $path = ltrim($path, '/');
        $assetPath = public_path($path);
        if (!is_file($assetPath)) {
            return asset($path);
        }

        $mime = mime_content_type($assetPath) ?: 'application/octet-stream';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($assetPath));
    }

    /**
     * Muestra los indicadores agrupados por eje del Plan Estatal de Desarrollo (PED).
     *
     * @param  int|string $num Número del eje del PED.
     * @return \Illuminate\View\View
     */
    public function ped($num)
    {
        $indicadoresCollection = $this->consultarIndicadoresPed($num);

        if ($indicadoresCollection->isEmpty()) {
            Log::warning("HomeController@ped: No se encontraron indicadores para el eje/num: {$num} desde consultarIndicadoresPed.");
        }

        $avanceEje = $this->prepararIndicadoresParaVista($indicadoresCollection);

        $indicadoresAgrupados = $indicadoresCollection->groupBy('tematica');

        return view('eje' . $num . '-ped', [
            'indicadoresAgrupados' => $indicadoresAgrupados,
            'avanceEje' => $avanceEje
        ]);
    }

    /**
     * Busca indicadores públicos del PED activo para el buscador del navbar.
     */
    public function buscarIndicadores(Request $request)
    {
        $termino = trim((string) $request->query('q', ''));

        if (mb_strlen($termino) < 2) {
            return response()->json(['data' => []]);
        }

        $like = '%' . addcslashes($termino, '%_\\') . '%';
        $planId = $this->activePlan->id();

        $indicadores = Indicador::query()
            ->forPlan($planId)
            ->with(['institucion', 'indicadorable'])
            ->where(function ($query) use ($like) {
                $query->where('nombre', 'like', $like)
                    ->orWhere('tematica', 'like', $like)
                    ->orWhere('programa', 'like', $like)
                    ->orWhere('programa_derivado', 'like', $like)
                    ->orWhereHas('institucion', fn ($institucion) => $institucion->where('nombre', 'like', $like))
                    ->orWhereHasMorph(
                        'indicadorable',
                        [CatEje::class, CatProgramaDerivadoSectorial::class, CatProgramaDerivadoEspecial::class, CatProgramaDerivadoInstitucional::class],
                        fn ($programa) => $programa->where('nombre', 'like', $like)
                    );
            })
            ->orderBy('nombre')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $indicadores->map(function ($indicador) {
                $contexto = $indicador->indicadorable?->nombre
                    ?? $indicador->programa
                    ?? $indicador->programa_derivado
                    ?? $indicador->tematica;

                return [
                    'nombre' => $indicador->nombre,
                    'contexto' => $contexto,
                    'institucion' => $indicador->institucion?->nombre,
                    'url' => route('ficha-tecnica.show', $indicador),
                ];
            }),
        ]);
    }

    /**
     * Consulta específica para obtener los indicadores del PED según su eje.
     *
     * @param  int|string $num Número del eje.
     * @return Collection<Indicador> Colección de indicadores.
     */
    private function consultarIndicadoresPed($num)
    {
        $eje = CatEje::where('plan_id', $this->activePlan->id())
            ->where('numero', $num)
            ->first();

        if (!$eje) {
            return collect();
        }

        $indicadores = $eje->indicadores()
            ->with([
            'datosAnuales' => function ($q_datos) {
                $q_datos->where('validado', true)
                    ->select('id', 'id_indicador', 'anio', 'valor_dato', 'validado');
            },
            'ods',
        ])
            ->orderBy('id', 'asc');

        $indicadores = $indicadores->get();

        if ($indicadores->isEmpty()) {
            Log::info("HomeController@consultarIndicadoresPed: El eje {$num} del plan {$eje->plan_id} no tiene indicadores relacionados.");
        } else {
            Log::info("HomeController@consultarIndicadoresPed: Se encontraron {$indicadores->count()} indicadores relacionados con el eje {$num} del plan {$eje->plan_id}.");
        }

        return $indicadores;
    }

    /**
     * Obtiene el dato anual numérico validado más reciente de una colección.
     *
     * @param  Collection<\App\Models\DatoAnual>|null $datosAnualesCollection
     * @return array{anio: int|null, valor: float|string|null}
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
     * @param  Collection<Indicador> $indicadoresCollection
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
        $sectoriales = CatProgramaDerivadoSectorial::where('plan_estatal', $this->activePlan->id())
            ->has('indicadores')
            ->get();
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
        $programa = CatProgramaDerivadoSectorial::where('plan_estatal', $this->activePlan->id())
            ->get()
            ->first(function ($item) use ($slug) {
            return Str::slug($item->nombre) === $slug;
        });

        if (!$programa) abort(404, 'Programa no encontrado');

            $color = $programa->color ?? '#0c312d';
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
        $programa = CatProgramaDerivadoEspecial::where('plan_estatal', $this->activePlan->id())
            ->get()
            ->first(function ($item) use ($slug) {
            return Str::slug($item->nombre) === $slug;
        });

        if (!$programa) abort(404, 'Programa no encontrado');

            $color = $programa->color ?? '#0c312d';
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
        $especiales = CatProgramaDerivadoEspecial::where('plan_estatal', $this->activePlan->id())
            ->has('indicadores')
            ->get();
        return view('ped-programas-especiales', compact('especiales'));
    }

    /**
     * Muestra el listado general de programas institucionales.
     *
     * @return \Illuminate\View\View
     */
    public function mostrarListadoInstitucionales()
    {
        $planId = $this->activePlan->id();

        $programas = CatProgramaDerivadoInstitucional::where('plan_estatal', $planId)
            ->where(function ($query) {
                $query->whereNull('grupo')
                    ->orWhere('grupo', '!=', 'Historicos');
            })
            ->get();
        $grupos = CatProgramaDerivadoInstitucional::select('grupo')
            ->where('plan_estatal', $planId)
            ->whereNotNull('grupo')
            ->where('grupo', '!=', '')
            ->where('grupo', '!=', 'Historicos')
            ->distinct()
            ->orderBy('grupo')
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
        $programa = CatProgramaDerivadoInstitucional::where('plan_estatal', $this->activePlan->id())
            ->get()
            ->first(function ($item) use ($slug) {
            return Str::slug($item->nombre) === $slug;
        });

        if (!$programa) abort(404, 'Programa no encontrado');

            $color = $programa->color ?? '#0c312d';
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
    public function inicio()
    {
        $plan = $this->activePlan->get();
        $planId = $plan->id;

        $soloValidados = true; // Vista pública siempre usa validados

        $indicadoresPlan = Indicador::forPlan($planId)
            ->with(['datosAnuales' => function ($query) use ($soloValidados) {
            if ($soloValidados) {
                $query->where('validado', true);
            }
            $query->orderByDesc('anio');
        }])->get();

        $totalIndicadores = $indicadoresPlan->count();
        $metricasPlan = $this->pedMetrics->summarizeCached($indicadoresPlan, $soloValidados);
        $composicionPlan = $this->pedMetrics->summarizeCompositionCached($indicadoresPlan);
        $avancePlan = $metricasPlan['avance_promedio'];
        $colorPlan = $this->getSemaforoColorInicio($avancePlan);

        $distribucionGeneral = $metricasPlan['distribucion'];

        $ejes = CatEje::with('indicadores.datosAnuales')->where('plan_id', $planId)->orderBy('numero')->get();
        $ejesData = $ejes->map(function ($eje) use ($soloValidados) {
            $indicadores = $eje->indicadores;
            $metricas = $this->pedMetrics->summarizeCached($indicadores, $soloValidados);
            return [
                'id' => $eje->id,
                'nombre' => $eje->nombre ?? 'No se encontró',
                'numero' => $eje->numero ?? 'ND',
                'color' => $eje->color ?? '#CCCCCC',
                'semaforo_color' => $this->getSemaforoColorInicio($metricas['avance_promedio']),
                'avance' => $metricas['avance_promedio'],
                'total_indicadores' => $metricas['total_registrados'],
                'indicadores_evaluables' => $metricas['total_evaluables'],
                'cobertura_evaluacion' => $metricas['cobertura_evaluacion'],
                'distribucion' => $metricas['distribucion'],
            ];
        });

        $programasData = $this->getProgramasAvanceInicio($planId, $soloValidados);

        $gruposInstitucionales = $programasData
            ->where('tipo', 'Institucionales')
            ->pluck('grupo')
            ->filter()
            ->unique()
            ->values();

        $heroVideo = config('sped.hero_video');

        return view('inicio', compact(
            'plan',
            'avancePlan',
            'metricasPlan',
            'composicionPlan',
            'colorPlan',
            'totalIndicadores',
            'distribucionGeneral',
            'ejesData',
            'programasData',
            'gruposInstitucionales',
            'heroVideo'
        ));
    }

    /**
     * Obtiene el avance de programas derivados (replica lógica de DashboardGeneralController).
     */
    private function getProgramasAvanceInicio($planId, $soloValidados)
    {
        $tipos = [
            ['class' => CatProgramaDerivadoSectorial::class, 'nombre' => 'Sectoriales', 'slug' => 'sectoriales', 'order' => 1],
            ['class' => CatProgramaDerivadoEspecial::class, 'nombre' => 'Especiales', 'slug' => 'especiales', 'order' => 2],
            ['class' => CatProgramaDerivadoInstitucional::class, 'nombre' => 'Institucionales', 'slug' => 'institucionales', 'order' => 3],
        ];

        $resultados = [];
        foreach ($tipos as $tipo) {
            $programas = $tipo['class']::with('indicadores.datosAnuales')->where('plan_estatal', $planId)->get();
            foreach ($programas as $prog) {
                $indicadores = $prog->indicadores;
                $metricas = $this->pedMetrics->summarizeCached($indicadores, $soloValidados);

                if ($metricas['total_registrados'] === 0) {
                    continue;
                }

                $resultados[] = [
                    'id' => $prog->id,
                    'nombre' => $prog->nombre,
                    'tipo' => $tipo['nombre'],
                    'tipo_slug' => $tipo['slug'],
                    'tipo_order' => $tipo['order'],
                    'avance' => $metricas['avance_promedio'],
                    'color' => $prog->color,
                    'icono' => $prog->icono,
                    'semaforo_color' => $this->getSemaforoColorInicio($metricas['avance_promedio']),
                    'total_indicadores' => $metricas['total_registrados'],
                    'indicadores_evaluables' => $metricas['total_evaluables'],
                    'cobertura_evaluacion' => $metricas['cobertura_evaluacion'],
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
        // $regionales = CatProgramaDerivadoRegional::has('indicadores')->get();
        $regionales = CatProgramaDerivadoRegional::where('plan_estatal', $this->activePlan->id())
            ->get();
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
        $programa = CatProgramaDerivadoRegional::where('plan_estatal', $this->activePlan->id())
            ->get()
            ->first(function ($item) use ($slug) {
            return Str::slug($item->nombre) === $slug;
        });

        if (!$programa) abort(404, 'Programa no encontrado');

            $color = $programa->color ?? '#0c312d';
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
        $colorPorDefectoGeneral = '#0c312d';

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
        $datoReciente = $this->obtenerDatoReciente($indicador->datos_anuales_validados);
        
        $indicador->setAttribute('dato_reciente', $datoReciente['valor']);
        $indicador->setAttribute('anio_reciente', $datoReciente['anio']);

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

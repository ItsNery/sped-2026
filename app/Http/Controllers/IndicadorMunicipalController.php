<?php

namespace App\Http\Controllers;

use App\Models\CatMunicipio;
use App\Models\IndicadorMunicipal;
use App\Models\Odses;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\PeriodicidadIndicadorMunicipal;
use App\Models\CatTipo;
use App\Models\ResultadoIndicadorMunicipal;
use App\Models\MunicipioConvenio;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;


class IndicadorMunicipalController extends Controller
{
    /**
     * Aplica el middleware de permisos a las acciones del controlador.
     */
    public function __construct()
    {
        $this->middleware('permission:ver-indicador-municipal|crear-indicador-municipal|editar-indicador-municipal|borrar-indicador-municipal', ['only' => ['index']]);
        $this->middleware('permission:crear-indicador-municipal', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-indicador-municipal', ['only' => ['edit', 'update']]);
        $this->middleware('permission:borrar-indicador-municipal', ['only' => ['destroy']]);
        $this->middleware('permission:subir-resultados-indicador-municipal', ['only' => ['storeNuevosResultados']]);
        $this->middleware('permission:editar-resultados-indicador-municipal', ['only' => ['guardarResultados']]);
        $this->middleware('permission:validar-indicador-municipal', ['only' => ['toggleValidacion']]);
    }
    /**
     * Muestra una lista de los indicadores pertenecientes al municipio del usuario logueado.
     * @return View
     */
    public function index()
    {
        $municipio = auth()->user()->id_municipio;
        $municipio_nombre = CatMunicipio::find($municipio)->nombre;
        $indicadores = IndicadorMunicipal::with('resultados')
            ->where('id_municipio', $municipio)
            ->get()
            ->map(function ($indicador) {
                $valoresPorAño = [];
                for ($anio = 2016; $anio <= now()->year; $anio++) {
                    $resultado = ResultadoIndicadorMunicipal::where('id_indicador', $indicador->id)
                        ->where('año', $anio)
                        ->whereNotNull('dato')
                        ->orderByDesc('periodo')
                        ->first();

                    $valoresPorAño["dato_$anio"] = $resultado ? $resultado->dato : null;
                }

                $indicador->valoresPorAño = $valoresPorAño;

                return $indicador;
            });
        return view('panel-indicadores-municipales.index', compact('indicadores', 'municipio_nombre'));
    }

    /**
     * Muestra el formulario para crear un nuevo indicador municipal.
     * @return View
     */
    public function create()
    {
        $tipos = CatTipo::all();
        $periodicidades = PeriodicidadIndicadorMunicipal::all();
        $odes = Odses::all();
        return view('panel-indicadores-municipales.crear', compact('odes', 'periodicidades', 'tipos'));
    }

    /**
     * Almacena un nuevo indicador y genera sus registros de resultados iniciales
     * basados en la periodicidad seleccionada.
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate(
            [
                'indicador' => 'required|string',
                'eje_indicador' => 'required|string',
                'tematica' => 'required|string',
                'descripcion' => 'required|string',
                'unidad_medida' => 'required|string',
                'linea_base' => 'required|numeric|min:2000|max:' . date('Y'),
                'dato_linea' => 'required|numeric',
                'fuente' => 'required|string',
                'liga' => 'nullable|url',
                'meta_2024' => 'required|numeric',
                'proxima_actualizacion' => 'nullable|date',
                'periodicidad_id' => 'required|exists:periodicidad_indicadores_municipales,id',
                'cobertura' => 'required|string',
                'tendencia' => 'required|string',
                'id_tipo' => 'required|exists:cat_tipo,id',
                'id_nivel' => 'required|exists:cat_nivel,id',
                'id_dimension' => 'required|exists:cat_dimension,id',
                'formula' => 'required|string',
                'dependencia' => 'required|string',
                'publica' => 'required|boolean',
                'id_ods' => 'required|array',
                'id_ods.*' => 'exists:ods,id',
                'datos_periodos' => 'required|array',
                'datos_periodos.*.dato' => 'nullable|numeric',
                'datos_periodos.*.resultado' => 'nullable|string',
            ],
            [
                'indicador.required' => 'El campo indicador es obligatorio',
                'indicador.string' => 'El campo indicador debe ser una cadena',

                'eje_indicador.required' => 'El campo eje indicador es obligatorio',
                'eje_indicador.string' => 'El campo eje indicador debe ser una cadena',

                'tematica.required' => 'El campo tematica es obligatorio',
                'tematica.string' => 'El campo tematica debe ser una cadena',

                'descripcion.required' => 'El campo descripcion es obligatorio',
                'descripcion.string' => 'El campo descripcion debe ser una cadena',

                'unidad_medida.required' => 'El campo unidad medida es obligatorio',
                'unidad_medida.string' => 'El campo unidad medida debe ser una cadena',

                'linea_base.required' => 'El campo linea base es obligatorio',
                'linea_base.numeric' => 'El campo linea base debe ser un número',
                'linea_base.min' => 'El campo linea base debe ser mayor a 2000',
                'linea_base.max' => 'El campo linea base debe ser menor a ' . date('Y'),

                'dato_linea.required' => 'El campo dato linea es obligatorio',
                'dato_linea.numeric' => 'El campo dato linea debe ser un número',

                'fuente.required' => 'El campo fuente es obligatorio',
                'fuente.string' => 'El campo fuente debe ser una cadena',

                'liga.url' => 'El campo liga debe ser una URL',
                'liga.required' => 'El campo liga es obligatorio',
                'liga.string' => 'El campo liga debe ser una cadena',
                'meta_2024.required' => 'El campo meta 2024 es obligatorio',
                'meta_2024.numeric' => 'El campo meta 2024 debe ser un número',

                'periodicidad_id.required' => 'El campo periodicidad es obligatorio',
                'periodicidad_id.exists' => 'El campo periodicidad no existe',
                'periodicidad_id.integer' => 'El campo periodicidad debe ser un número',

                'id_tipo.required' => 'El campo tipo es obligatorio',
                'id_tipo.exists' => 'El campo tipo no existe',
                'id_tipo.integer' => 'El campo tipo debe ser un número',

                'id_ods.required' => 'El campo ods es obligatorio',
                'id_ods.array' => 'El campo ods debe ser un arreglo',
                'id_ods.*.exists' => 'El campo ods no existe',

                'año.required' => 'El campo año es obligatorio',
                'año.integer' => 'El campo año debe ser un número',
                'año.min' => 'El campo año debe ser mayor a 2000',
                'año.max' => 'El campo año debe ser menor a ' . date('Y'),
            ]
        );

        $validatedData['id_municipio'] = auth()->user()->id_municipio;
        $validatedData['instrumento'] = 'Plan de Desarrollo Municipal';
        $filteredData = collect($validatedData)->except(['id_ods', 'datos_periodos'])->toArray();
        $indicadorMunicipal = IndicadorMunicipal::create($filteredData);

        $indicadorMunicipal->ods()->sync($request->id_ods);
        $periodicidadId = $request->input('periodicidad_id');
        $datosPeriodos = $request->input('datos_periodos');
        $ano = $request->input('ano');

        $periodos = [
            1 => 1,
            2 => 6,
            3 => 3,
            4 => 12,
            5 => 2,
            6 => 4,
        ];
        $numPeriodos = $periodos[$periodicidadId] ?? 0;

        $registros = [];
        foreach (range(1, $numPeriodos) as $periodo) {
            $dato = $datosPeriodos[$periodo - 1]['dato'] ?? null;
            $resultado = $datosPeriodos[$periodo - 1]['resultado'] ?? null;

            if (isset($dato) || isset($resultado)) {
                $registros[] = [
                    'id_indicador' => $indicadorMunicipal->id,
                    'periodicidad_id' => $periodicidadId,
                    'año' => $ano,
                    'periodo' => $periodo,
                    'dato' => $dato,
                    'resultado' => $resultado,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($registros)) {
            ResultadoIndicadorMunicipal::insert($registros);
        }
        return redirect()->route('panel-indicadores-municipales.index')->with('success', 'Indicador creado con éxito.');
    }

    /**
     * Muestra la vista de detalle de un indicador, incluyendo sus resultados por año y periodo.
     * @param  int $id
     * @return View
     */
    public function show($id)
    {
        $indicador = IndicadorMunicipal::with(['resultados'])->findOrFail($id);
        $periodicidades = PeriodicidadIndicadorMunicipal::all();
        $añosDisponibles = $indicador->resultados->pluck('año')->unique()->sort()->toArray();
        $datosResultadosIndicador = ResultadoIndicadorMunicipal::where('id_indicador', $id)->get();
        if (auth()->user()->id_municipio !== $indicador->id_municipio) {
            abort(403, 'No tienes permiso para ver este indicador.');
        }
        return view('panel-indicadores-municipales.mostrar', compact('indicador', 'añosDisponibles', 'datosResultadosIndicador', 'periodicidades'));
    }

    /**
     * Muestra el formulario para editar un indicador.
     * Bloquea la edición si el indicador ya está validado y el usuario no es administrador.
     * @param  int $id
     * @return RedirectResponse
     */
    public function edit($id)
    {
        $tipos = CatTipo::all();
        $periodicidades = PeriodicidadIndicadorMunicipal::all();
        $odes = Odses::all();
        $indicador = IndicadorMunicipal::with(['resultados'])->findOrFail($id);
        if ($indicador->validado == 1
            && !auth()->user()->hasRole('Administrador Municipal')
            && !auth()->user()->isSuperAdministrator()) {
            return redirect()->route('panel-indicadores-municipales.index')->with('error', 'La información de este indicador no puede ser editada porque ha sido validado.');
        }
        $datosResultadosIndicador = ResultadoIndicadorMunicipal::where('id_indicador', $id)->get();
        return view('panel-indicadores-municipales.editar', compact('indicador', 'tipos', 'periodicidades', 'odes', 'datosResultadosIndicador'));
    }

    /**
     * Actualiza los datos principales de un indicador.
     * @param  Request  $request
     * @param  int  $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $indicador = IndicadorMunicipal::findOrFail($id);

        // Validación de los datos del formulario
        $validatedData = $request->validate([
            'indicador' => 'required|string',
            'eje_indicador' => 'required|string',
            'tematica' => 'required|string',
            'descripcion' => 'required|string',
            'unidad_medida' => 'required|string',
            'linea_base' => 'required|numeric|min:2000|max:' . date('Y'),
            'dato_linea' => 'required|numeric',
            'fuente' => 'required|string',
            'liga' => 'nullable|url',
            'meta_2024' => 'required|numeric',
            'proxima_actualizacion' => 'nullable|date',
            'cobertura' => 'required|string',
            'tendencia' => 'required|string',
            'id_tipo' => 'required|exists:cat_tipo,id',
            'id_nivel' => 'required|exists:cat_nivel,id',
            'id_dimension' => 'required|exists:cat_dimension,id',
            'formula' => 'required|string',
            'dependencia' => 'required|string',
            'publica' => 'required|boolean',
            'id_ods' => 'required|array',
            'id_ods.*' => 'exists:ods,id',
        ]);

        $indicador->update(collect($validatedData)->except(['id_ods', 'datos_periodos', 'nuevos_registros', 'eliminar_registros'])->toArray());

        $indicador->ods()->sync($request->input('id_ods'));

        return redirect()->route('panel-indicadores-municipales.index')->with('success', 'Indicador actualizado con éxito');
    }

    /**
     * Elimina un indicador y todos sus datos relacionados (resultados, relaciones con ODS).
     * @param  int  $id
     * @return RedirectResponse
     */
    public function destroy($id)
    {
        $indicador = IndicadorMunicipal::findOrFail($id);
        $indicador->resultados()->delete();
        $indicador->ods()->detach();
        $indicador->delete();
        return redirect()->route('panel-indicadores-municipales.index')->with('success', 'El indicador se ha eliminado correctamente');
    }

    /**
     * Actualiza los datos de un conjunto de resultados para un año específico.
     * @param  Request $request
     * @param  int $año
     * @return RedirectResponse
     */
    public function actualizarResultadosIndMun(Request $request, $año)
    {
        // dd($año);
        $validatedData = $request->validate([
            'resultados' => 'required|array',
            'resultados.*.dato' => 'nullable|numeric',
            'resultados.*.resultado' => 'nullable|string',
        ]);

        foreach ($validatedData['resultados'] as $id => $data) {
            $resultado = ResultadoIndicadorMunicipal::findOrFail($id);
            $resultado->update([
                'dato' => $data['dato'] ?? null,
                'resultado' => $data['resultado'] ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'Resultados del año ' . $año . ' actualizados con éxito.');
    }

    public function storeNuevosResultados(Request $request)
    {
        $validated = $request->validate([
            'ano' => 'required|integer',
            'periodicidad_id' => 'required|exists:periodicidades,id',
            'nuevos_registros' => 'required|array',
            'nuevos_registros.*.año' => 'required|integer',
            'nuevos_registros.*.periodo' => 'required|integer',
            'nuevos_registros.*.dato' => 'required|numeric',
            'nuevos_registros.*.resultado' => 'nullable|string',
        ]);

        foreach ($request->input('nuevos_registros') as $registro) {
            ResultadoIndicadorMunicipal::create([
                'año' => $registro['año'],
                'periodo' => $registro['periodo'],
                'dato' => $registro['dato'],
                'resultado' => $registro['resultado'],
            ]);
        }

        return response()->json(['message' => 'Nuevos resultados agregados exitosamente.'], 200);
    }

    /**
     * Almacena nuevos registros de resultados para un indicador.
     * @param  Request $request
     * @return JsonResponse
     */
    public function guardarResultados(Request $request)
    {
        $request->validate([
            'id_indicador' => 'required|exists:indicadores_municipales,id',
            'ano' => 'required|integer|digits:4',
            'periodicidad_id' => 'required|exists:periodicidad_indicadores_municipales,id',
            'nuevos_registros' => 'required|array',
            'nuevos_registros.*.año' => 'required|integer',
            'nuevos_registros.*.periodo' => 'required|integer',
            'nuevos_registros.*.dato' => 'nullable|numeric',
            'nuevos_registros.*.resultado' => 'nullable|string',
        ]);
        $idIndicador = $request->id_indicador;
        $nuevosRegistros = $request->input('nuevos_registros');

        foreach ($nuevosRegistros as $registro) {
            ResultadoIndicadorMunicipal::create([
                'id_indicador' => $idIndicador,
                'año' => $registro['año'],
                'periodicidad_id' => $request->periodicidad_id,
                'periodo' => $registro['periodo'],
                'dato' => $registro['dato'] ?? null,
                'resultado' => $registro['resultado'] ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'El resultado anual se ha agregado correctamente.');
    }

    /**
     * Cambia el estado de validación (Validado/No Validado) de un indicador.
     * @param  int $id
     * @return RedirectResponse
     */
    public function toggleValidacion($id)
    {
        $indicador = IndicadorMunicipal::findOrFail($id);

        $indicador->validado = !$indicador->validado;
        $indicador->save();

        return redirect()->back()->with('status', 'El estado de validación del indicador ha sido actualizado.');
    }

    /**
     * Genera la vista de reporte imprimible para los indicadores de un municipio.
     * @return View
     */
    public function reporteIndicadores()
    {
        $municipio = auth()->user()->id_municipio;
        $municipio_nombre = CatMunicipio::find($municipio)->nombre;
        $indicadores = IndicadorMunicipal::with('resultados')
            ->where('id_municipio', $municipio)
            ->get()
            ->map(function ($indicador) {
                $valoresPorAño = [];
                for ($anio = 2016; $anio <= now()->year; $anio++) {
                    $resultado = ResultadoIndicadorMunicipal::where('id_indicador', $indicador->id)
                        ->where('año', $anio)
                        ->whereNotNull('dato')
                        ->orderByDesc('periodo')
                        ->first();

                    $valoresPorAño["dato_$anio"] = $resultado ? $resultado->dato : null;
                }

                $indicador->valoresPorAño = $valoresPorAño;

                return $indicador;
            });

        return view('panel-indicadores-municipales.reporte', compact('indicadores', 'municipio_nombre'));
    }

    /**
     * Genera una ficha técnica pública para un indicador municipal.
     * @param  IndicadorMunicipal $indicador
     * @return \Illuminate\View\View
     */
    public function mostrarFicha(IndicadorMunicipal $indicador)
    {
        return view('ficha-tecnica-municipal', $this->datosFichaPublica($indicador));
    }

    /**
     * Descarga una ficha técnica municipal en PDF.
     */
    public function descargarFicha(IndicadorMunicipal $indicador)
    {
        $html = view('ficha-tecnica-municipal-pdf', [
            ...$this->datosFichaPublica($indicador),
            'pdfAsset' => fn (string $path): string => $this->inlinePublicAsset($path),
            'pdfEcharts' => file_get_contents(public_path('js/echarts.min.js')),
        ])->render();

        $footer = '<div style="position: relative; top: -6mm; z-index: 20; width: 100vw; margin: 0; padding: 0; background: #fff; color: #706b72; font: 9px Arial, sans-serif; text-align: center;">'
            . 'Hoja <span class="pageNumber"></span> de <span class="totalPages"></span></div>';

        $pdf = Browsershot::html($html)
            ->setNodeBinary(config('browsershot.node_binary', 'node'))
            ->setNodeModulePath(base_path('node_modules'))
            ->setEnvironmentOptions([
                'PUPPETEER_CACHE_DIR' => storage_path('app/puppeteer'),
            ])
            ->paperSize(210, 297, 'mm')
            ->margins(5, 5, 16, 5)
            ->scale(1)
            ->showBrowserHeaderAndFooter()
            ->footerHtml($footer)
            ->showBackground()
            ->setOption('viewport', [
                'width' => 1240,
                'height' => 1754,
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
            'Content-Disposition' => 'attachment; filename="ficha-tecnica-municipal-' . Str::slug($indicador->indicador ?: 'indicador') . '.pdf"',
        ]);
    }

    private function datosFichaPublica(IndicadorMunicipal $indicador): array
    {
        // Acceder a los resultados relacionados con el indicador
        $resultados = $indicador->resultados;

        $anioActual = now()->year;
        $aniosIniciales = array_filter([
            $indicador->linea_base ? (int) $indicador->linea_base : null,
            $resultados->min('año'),
        ]);
        $anioInicio = min($aniosIniciales ?: [2015]);
        $años = range($anioInicio, $anioActual);

        // Iterar sobre los años y agregar atributos dinámicos
        foreach ($años as $año) {
            // Buscar el resultado correspondiente a ese año
            $resultadoAño = $resultados->where('año', $año)->sortByDesc('periodo')->first();

            // Si existe un resultado para ese año, agregar el dato del periodo más grande
            if ($resultadoAño) {
                // Asignar el dato del periodo más grande como un atributo dinámico
                $atributo = "dato_$año";
                $indicador->$atributo = $resultadoAño->dato;
            } else {
                // Si no existe, asignar null
                $atributo = "dato_$año";
                $indicador->$atributo = null;
            }
        }

        // Buscar el año más reciente
        $anioMasReciente = $resultados->sortByDesc('año')->first();
        if ($anioMasReciente) {
            $resultadoMasReciente = $resultados->where('año', $anioMasReciente->año)
                ->sortByDesc('periodo')
                ->first();

            // Si el resultado del periodo más reciente está vacío, buscar el siguiente periodo más grande
            if (empty($resultadoMasReciente->resultado)) {
                $resultadoMasReciente = $resultados->where('año', $anioMasReciente->año)
                    ->sortByDesc('periodo')
                    ->skip(1)  // Obtener el siguiente periodo
                    ->first();
            }

            // Asignamos el resultado más reciente (si existe)
            $indicador->resultado_mas_reciente = $resultadoMasReciente ? $resultadoMasReciente->resultado : null;
        }

        $municipio = MunicipioConvenio::where('id_municipio', $indicador->id_municipio)->first();

        return compact('indicador', 'municipio');
    }

    private function inlinePublicAsset(string $path): string
    {
        $assetPath = public_path(ltrim($path, '/'));

        if (!is_file($assetPath)) {
            return asset($path);
        }

        $mime = mime_content_type($assetPath) ?: 'application/octet-stream';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($assetPath));
    }
}

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
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $municipio = auth()->user()->id_municipio;
        $municipio_nombre = CatMunicipio::find($municipio)->nombre;
        // $indicadores = IndicadorMunicipal::with('resultados')->where('id_municipio', $municipio)->paginate(2000);
        $indicadores = IndicadorMunicipal::with('resultados')
            ->where('id_municipio', $municipio)
            ->get()
            ->map(function ($indicador) {
                // Crear un arreglo con los valores más recientes por año
                $valoresPorAño = [];
                for ($anio = 2016; $anio <= now()->year; $anio++) {
                    // Buscar el período más alto con dato no nulo
                    $resultado = ResultadoIndicadorMunicipal::where('id_indicador', $indicador->id)
                        ->where('año', $anio)
                        ->whereNotNull('dato') // Solo resultados con datos
                        ->orderByDesc('periodo') // Ordenar por período de mayor a menor
                        ->first();

                    // Guardar el valor en el arreglo si existe
                    $valoresPorAño["dato_$anio"] = $resultado ? $resultado->dato : null;
                }

                // Agregar los valores al indicador
                $indicador->valoresPorAño = $valoresPorAño;

                return $indicador;
            });
        return view('panel-indicadores-municipales.index', compact('indicadores', 'municipio_nombre'));
    }

    /**
     * Muestra el formulario para crear un nuevo indicador municipal.
     * @return \Illuminate\View\View
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
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
                'datos_periodos' => 'required|array', // Validar que vengan datos para los periodos
                'datos_periodos.*.dato' => 'nullable|numeric', // Los datos pueden ser nulos
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
        // dd($validatedData);
        $filteredData = collect($validatedData)->except(['id_ods', 'datos_periodos'])->toArray();
        $indicadorMunicipal = IndicadorMunicipal::create($filteredData);

        // $indicadorMunicipal = IndicadorMunicipal::create($request->except(['id_ods', 'datos_periodos']));

        $indicadorMunicipal->ods()->sync($request->id_ods);
        // Lógica para agregar registros en resultados_indicadores_municipales
        $periodicidadId = $request->input('periodicidad_id');
        $datosPeriodos = $request->input('datos_periodos');
        $ano = $request->input('ano'); // Tomamos el año del campo línea base

        // Determinar el número de periodos según la periodicidad
        $periodos = [
            1 => 1,  // Anual
            2 => 6,  // Bimestral
            3 => 3,  // Cuatrimestral
            4 => 12, // Mensual
            5 => 2,  // Semestral
            6 => 4,  // Trimestral
        ];
        $numPeriodos = $periodos[$periodicidadId] ?? 0;

        $registros = [];
        foreach (range(1, $numPeriodos) as $periodo) {
            $dato = $datosPeriodos[$periodo - 1]['dato'] ?? null;
            $resultado = $datosPeriodos[$periodo - 1]['resultado'] ?? null;

            // Agregar registro solo si hay datos relevantes
            if (isset($dato) || isset($resultado)) {
                $registros[] = [
                    'id_indicador' => $indicadorMunicipal->id,
                    'periodicidad_id' => $periodicidadId,
                    'año' => $ano,
                    'periodo' => $periodo,
                    'dato' => $dato,
                    'resultado' => $resultado,
                    'created_at' => now(), // Usar la función de Laravel para timestamps
                    'updated_at' => now(),
                ];
            }
        }

        // Inserción masiva
        if (!empty($registros)) {
            ResultadoIndicadorMunicipal::insert($registros);
        }
        return redirect()->route('panel-indicadores-municipales.index')->with('success', 'Indicador creado con éxito.');
        // return redirect()->back()->with('success', 'Indicador municipal creado correctamente.');
    }

    /**
     * Muestra la vista de detalle de un indicador, incluyendo sus resultados por año y periodo.
     * @param  int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Obtener el indicador junto con sus relaciones
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
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        $tipos = CatTipo::all();
        $periodicidades = PeriodicidadIndicadorMunicipal::all();
        $odes = Odses::all();
        $indicador = IndicadorMunicipal::with(['resultados'])->findOrFail($id);
        // Verificar si el indicador está validado y si el usuario no es Administrador Municipal
        if ($indicador->validado == 1 && !auth()->user()->hasRole('Administrador Municipal')) {
            // Redirigir o devolver un mensaje de error si no se puede editar
            return redirect()->route('panel-indicadores-municipales.index')->with('error', 'La información de este indicador no puede ser editada porque ha sido validado.');
        }
        $datosResultadosIndicador = ResultadoIndicadorMunicipal::where('id_indicador', $id)->get();
        return view('panel-indicadores-municipales.editar', compact('indicador', 'tipos', 'periodicidades', 'odes', 'datosResultadosIndicador'));
    }

    /**
     * Actualiza los datos principales de un indicador.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
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

        // Actualizar el indicador principal
        $indicador->update(collect($validatedData)->except(['id_ods', 'datos_periodos', 'nuevos_registros', 'eliminar_registros'])->toArray());

        // Sincronizar ODS
        $indicador->ods()->sync($request->input('id_ods'));


        // Redirigir al usuario con un mensaje de éxito
        return redirect()->route('panel-indicadores-municipales.index')->with('success', 'Indicador actualizado con éxito');
    }

    /**
     * Elimina un indicador y todos sus datos relacionados (resultados, relaciones con ODS).
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Buscar el indicador
        $indicador = IndicadorMunicipal::findOrFail($id);

        // Eliminar los resultados relacionados con este indicador
        $indicador->resultados()->delete(); // Elimina los resultados asociados

        // Eliminar las relaciones en la tabla pivote si existen
        $indicador->ods()->detach(); // Eliminar la relación de ODS, si aplica

        // Eliminar el indicador
        $indicador->delete();

        // Redirigir a una página con un mensaje de éxito
        return redirect()->route('panel-indicadores-municipales.index')->with('success', 'El indicador se ha eliminado correctamente');
    }

    /**
     * Actualiza los datos de un conjunto de resultados para un año específico.
     * Probablemente llamado desde la vista de detalle.
     * @param  \Illuminate\Http\Request $request
     * @param  int $año
     * @return \Illuminate\Http\RedirectResponse
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
        // Validar los datos recibidos
        $validated = $request->validate([
            'ano' => 'required|integer',
            'periodicidad_id' => 'required|exists:periodicidades,id',
            'nuevos_registros' => 'required|array',
            'nuevos_registros.*.año' => 'required|integer',
            'nuevos_registros.*.periodo' => 'required|integer',
            'nuevos_registros.*.dato' => 'required|numeric',
            'nuevos_registros.*.resultado' => 'nullable|string',
        ]);

        // Procesar y guardar los nuevos resultados
        foreach ($request->input('nuevos_registros') as $registro) {
            // Crear el nuevo resultado (ajusta según tu modelo)
            ResultadoIndicadorMunicipal::create([
                'año' => $registro['año'],
                'periodo' => $registro['periodo'],
                'dato' => $registro['dato'],
                'resultado' => $registro['resultado'],
            ]);
        }

        // Puedes devolver una respuesta o redirigir según tu preferencia
        return response()->json(['message' => 'Nuevos resultados agregados exitosamente.'], 200);
    }

    /**
     * Almacena nuevos registros de resultados para un indicador.
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function guardarResultados(Request $request)
    {
        // Validar los datos
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
        // Recibir los nuevos registros
        $nuevosRegistros = $request->input('nuevos_registros');

        // Guardar cada nuevo registro en la base de datos
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

        // Redirigir con éxito
        return redirect()->back()->with('success', 'El resultado anual se ha agregado correctamente.');
    }

    /**
     * Cambia el estado de validación (Validado/No Validado) de un indicador.
     * @param  int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleValidacion($id)
    {
        $indicador = IndicadorMunicipal::findOrFail($id);

        // Alternar el estado de validación
        $indicador->validado = !$indicador->validado;
        $indicador->save();

        // Redirigir con un mensaje de éxito
        return redirect()->back()->with('status', 'El estado de validación del indicador ha sido actualizado.');
    }

    /**
     * Genera la vista de reporte imprimible para los indicadores de un municipio.
     * @return \Illuminate\View\View
     */
    public function reporteIndicadores()
    {
        $municipio = auth()->user()->id_municipio;
        $municipio_nombre = CatMunicipio::find($municipio)->nombre;
        $indicadores = IndicadorMunicipal::with('resultados')
            ->where('id_municipio', $municipio)
            ->get()
            ->map(function ($indicador) {
                // Crear un arreglo con los valores más recientes por año
                $valoresPorAño = [];
                for ($anio = 2016; $anio <= now()->year; $anio++) {
                    // Buscar el período más alto con dato no nulo
                    $resultado = ResultadoIndicadorMunicipal::where('id_indicador', $indicador->id)
                        ->where('año', $anio)
                        ->whereNotNull('dato') // Solo resultados con datos
                        ->orderByDesc('periodo') // Ordenar por período de mayor a menor
                        ->first();

                    // Guardar el valor en el arreglo si existe
                    $valoresPorAño["dato_$anio"] = $resultado ? $resultado->dato : null;
                }

                // Agregar los valores al indicador
                $indicador->valoresPorAño = $valoresPorAño;

                return $indicador;
            });

        return view('panel-indicadores-municipales.reporte', compact('indicadores', 'municipio_nombre'));
    }

    /**
     * Genera una ficha técnica pública para un indicador municipal.
     * @param  int $id
     * @return \Illuminate\View\View
     */
    public function mostrarFicha($id)
    {
        // Obtener el indicador
        $indicador = IndicadorMunicipal::findOrFail($id);

        // Acceder a los resultados relacionados con el indicador
        $resultados = $indicador->resultados;

        // Rango de años que queremos verificar (por ejemplo, de 2015 a 2024)
        $anioActual= now()->year;
        $años = range(2015, $anioActual);

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

        return view('ficha-tecnica-municipal', compact('indicador', 'municipio'));
    }
}

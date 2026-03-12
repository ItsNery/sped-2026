<?php

namespace App\Http\Controllers;

use App\Models\IndicadorMunicipal;
use App\Models\Odses;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\PeriodicidadIndicadorMunicipal;
use App\Models\CatTipo;
use App\Models\ResultadoIndicadorMunicipal;

class IndicadorMunicipalController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver-indicador-municipal|crear-indicador-municipal|editar-indicador-municipal|borrar-indicador-municipal', ['only' => ['index']]);
        $this->middleware('permission:crear-indicador-municipal', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-indicador-municipal', ['only' => ['edit', 'update']]);
        $this->middleware('permission:borrar-indicador-municipal', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $municipio = auth()->user()->id_municipio;
        $indicadores = IndicadorMunicipal::with('resultados')->where('id_municipio', $municipio)->paginate(2000);
        return view('panel-indicadores-municipales.index', compact('indicadores'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tipos = CatTipo::all();
        $periodicidades = PeriodicidadIndicadorMunicipal::all();
        $odes = Odses::all();
        return view('panel-indicadores-municipales.crear', compact('odes', 'periodicidades', 'tipos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
        return redirect()->route('panel-indicadores-municipales.index')->with('success', 'Indicador actualizado exitosamente.');
        // return redirect()->back()->with('success', 'Indicador municipal creado correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Obtener el indicador junto con sus relaciones
        $indicador = IndicadorMunicipal::with(['resultados'])->findOrFail($id);
        $añosDisponibles = $indicador->resultados->pluck('año')->unique()->sort()->toArray();

        if (auth()->user()->id_municipio !== $indicador->id_municipio) {
            abort(403, 'No tienes permiso para ver este indicador.');
        }
        return view('panel-indicadores-municipales.mostrar', compact('indicador', 'añosDisponibles'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tipos = CatTipo::all();
        $periodicidades = PeriodicidadIndicadorMunicipal::all();
        $odes = Odses::all();
        $indicador = IndicadorMunicipal::with(['resultados'])->findOrFail($id);
        $datosResultadosIndicador = ResultadoIndicadorMunicipal::where('id_indicador', $id)->get();
        return view('panel-indicadores-municipales.editar', compact('indicador', 'tipos', 'periodicidades', 'odes', 'datosResultadosIndicador'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
            'periodicidad_id' => 'nullable|exists:periodicidad_indicadores_municipales,id',
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
            'datos_periodos' => 'nullable|array', // Opcional porque podría no haber cambios
            'datos_periodos.*.id' => 'nullable|exists:resultado_indicador_municipal,id',
            'datos_periodos.*.dato' => 'nullable|numeric',
            'datos_periodos.*.resultado' => 'nullable|string',
        ]);

        // Actualizar el indicador principal
        $indicador->update(collect($validatedData)->except(['id_ods', 'datos_periodos'])->toArray());

        // Sincronizar ODS
        $indicador->ods()->sync($request->input('id_ods'));

        // Lógica para manejar los registros de resultados
        $datosPeriodos = $request->input('datos_periodos', []);
        $periodicidadId = $request->input('periodicidad_id');
        $ano = $request->input('linea_base');

        // 1. Actualizar o crear registros existentes
        foreach ($datosPeriodos as $registro) {
            if (isset($registro['id'])) {
                // Actualizar registros existentes
                $resultado = ResultadoIndicadorMunicipal::find($registro['id']);
                if ($resultado) {
                    $resultado->update([
                        'dato' => $registro['dato'] ?? $resultado->dato,
                        'resultado' => $registro['resultado'] ?? $resultado->resultado,
                    ]);
                }
            } else {
                // Crear nuevos registros
                ResultadoIndicadorMunicipal::create([
                    'id_indicador' => $indicador->id,
                    'periodicidad_id' => $periodicidadId,
                    'año' => $ano,
                    'periodo' => $registro['periodo'] ?? null,
                    'dato' => $registro['dato'] ?? null,
                    'resultado' => $registro['resultado'] ?? null,
                ]);
            }
        }

        // Redirigir al usuario con un mensaje de éxito
        return redirect()->route('panel-indicadores-municipales.index')->with('success', 'Indicador actualizado con éxito');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    public function actualizarResultados(array $datosPeriodos, $periodicidadId, $ano)
    {
        foreach ($datosPeriodos as $registro) {
            if (isset($registro['id'])) {
                // Actualizar registro existente
                $resultado = ResultadoIndicadorMunicipal::find($registro['id']);
                if ($resultado) {
                    $resultado->update([
                        'dato' => $registro['dato'] ?? $resultado->dato,
                        'resultado' => $registro['resultado'] ?? $resultado->resultado,
                    ]);
                }
            } else {
                // Crear nuevo registro
                ResultadoIndicadorMunicipal::create([
                    'id_indicador' => $this->id,
                    'periodicidad_id' => $periodicidadId,
                    'año' => $ano,
                    'periodo' => $registro['periodo'] ?? null,
                    'dato' => $registro['dato'] ?? null,
                    'resultado' => $registro['resultado'] ?? null,
                ]);
            }
        }
    }
    
}

<?php

namespace App\Http\Controllers;

use App\Models\Indicador;
use App\Models\User;
use App\Models\Odses;
use App\Models\Institucion;
use Illuminate\Http\Request;
// use App\Models\DatoAnualIndicador;
use App\Models\CatEje;
use App\Models\CatPlanEstatalDesarrollo;
use App\Models\CatProgramaDerivadoEspecial;
use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\CatProgramaDerivadoRegional;
use App\Models\CatProgramaDerivadoSectorial;
use App\Models\DatoAnual;
use App\Models\IndicadorOds;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\Log; // Para registrar errores (opcional pero recomendado)
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IndicadorController extends Controller
{
    /**
     * Aplica el middleware de permisos a las acciones del controlador.
     */
    public function __construct()
    {
        $this->middleware('permission:ver-indicador|crear-indicador|editar-indicador|borrar-indicador', ['only' => ['index']]);
        $this->middleware('permission:crear-indicador', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-indicador', ['only' => ['edit', 'update']]);
        $this->middleware('permission:borrar-indicador', ['only' => ['destroy']]);
        $this->middleware('permission:editar-indicador-anual', ['only' => ['updateAnualData']]);
        $this->middleware('permission:validar-indicador', ['only' => ['toggleValidacion']]);
        $this->middleware('permission:subida-masiva-indicador', ['only' => ['confirmImport']]);
    }

    /**
     * Muestra una lista de indicadores, adaptada al rol del usuario.
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();
        $tiposPrograma = Indicador::select('programa_derivado')
            ->whereNotNull('programa_derivado')
            ->where('programa_derivado', '!=', '')
            ->distinct()
            ->orderBy('programa_derivado')
            ->pluck('programa_derivado')
            ->toArray();

        if ($user->hasRole('Administrador')) {
            $indicadores = Indicador::with('datosAnuales')->get();
            $instituciones = Institucion::whereHas('indicadores')->where('id', '!=', 1)->get();
            return view('panel-indicadores.index', compact('indicadores', 'instituciones', 'tiposPrograma'));
        }

        if ($user->hasRole('Enlace')) {
            $institucionesAsignadas = $user->instituciones()->pluck('institucion_id');
            $indicadores = Indicador::whereIn('id_institucion', $institucionesAsignadas)
                ->orderBy('id')
                ->paginate(1000);
            $instituciones = $user->instituciones;

            return view('panel-indicadores.index', compact('indicadores', 'tiposPrograma', 'instituciones'));
        }

        if ($user->hasRole(['Enlace dependencia', 'Visualizador'])) {
            $indicadores = Indicador::where('id_institucion', $user->id_institucion)
                ->where('id', '!=', 608)
                ->orderBy('id')
                ->get();

            $todosValidados = $indicadores->isEmpty() ? false : ($indicadores->where('indicador_validado', 1)->count() === $indicadores->count());

            $mostrarBotonFinalizar = $todosValidados && $user->finalizado != 1;
            $mostrarBotonGenerarReporte = $todosValidados && $user->finalizado == 1 && $user->reporte_generado != 1;

            return view('panel-indicadores.index', compact('indicadores', 'mostrarBotonFinalizar', 'user', 'mostrarBotonGenerarReporte'));
        }

        $indicadores = Indicador::where('id_usuario', $user->id)
            ->where('id', '!=', 608)
            ->orderBy('id')
            ->get();

        $todosValidados = $indicadores->isEmpty() ? false : ($indicadores->where('indicador_validado', 1)->count() === $indicadores->count());

        $mostrarBotonFinalizar = $todosValidados && $user->finalizado != 1;
        $mostrarBotonGenerarReporte = $todosValidados && $user->finalizado == 1 && $user->reporte_generado != 1;

        return view('panel-indicadores.index', compact('indicadores', 'mostrarBotonFinalizar', 'user', 'mostrarBotonGenerarReporte'));
    }

    /**
     * Muestra el formulario para crear un nuevo indicador.
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $pds = [
            'Plan Estatal de Desarrollo',
            'Programa Especial',
            'Programa Regional',
            'Programa Sectorial',
        ];
        $odses = Odses::all();
        $periodicidades = [
            'Sexenal',
            'Quinquenal',
            'Trienal',
            'Bienal',
            'Ciclo escolar',
            'Cuatrimestral',
            'Trimestral',
            'Bimestral',
            'Anual',
            'Semestral',
            'Mensual'
        ];
        $coberturas = [
            'Estatal',
            'Regional',
            'Municipal',
        ];

        $tendencias = [
            'Mayor es Mejor',
            'Menor es Mejor',
            'Constante'
        ];

        $usuarios = User::where('id', '>=', 8)
            ->role('Enlace dependencia')
            ->get();
        $instituciones = Institucion::where('id', '!=', 1)->get();

        $planes = CatPlanEstatalDesarrollo::all();
        $programasInstitucionales = CatProgramaDerivadoInstitucional::all();

        return view('panel-indicadores.crear', compact('pds', 'instituciones', 'usuarios', 'odses', 'periodicidades', 'coberturas', 'tendencias', 'planes', 'programasInstitucionales'));
    }

    /**
     * Almacena un nuevo indicador y sus datos anuales asociados.
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        Log::debug('IndicadorController@store: Método iniciado.');

        Log::debug('IndicadorController@store: Request data (indicador):', $request->only([
            'nombre',
            'programa_derivado',
            'programa',
            'tematica',
            'linea_base',
            'dato_linea_base',
            'periodo',
            'meta_2024',
            'unidad_medida',
            'id_institucion',
            'id_usuario',
            'fuente',
            'liga',
            'descripcion',
            'periodicidad',
            'cobertura',
            'tendencia',
            'fecha_actualizacion',
            'formula'
        ]));

        Log::debug('IndicadorController@store: Request data (datos_anuales):', ['datos_anuales' => $request->input('datos_anuales')]);

        if ($request->filled('liga')) {
            $request->merge([
                'liga' => str_replace(' ', '%20', trim($request->input('liga')))
            ]);
        }
        $rules = [
            'nombre' => 'required|string|max:255',
            // 'programa_derivado' => 'required|string|max:255',
            // 'programa' => 'required|string|max:255',
            'plan_id' => 'required|exists:cat_planes_estatales_desarrollo,id',
            'eje_id' => 'nullable|required_unless:es_programa_derivado,1|exists:cat_ejes,id',
            'es_programa_derivado' => 'boolean',
            'tipo_programa' => 'nullable|required_if:es_programa_derivado,1|string',
            'programa_id' => 'nullable|required_if:es_programa_derivado,1|integer',
            'eje_app' => 'required|string|max:255',
            // 'cod_tematica' => 'required|string|max:255',
            'tematica' => 'required|string|max:255',
            'linea_base' => 'required|integer|digits:4',
            'dato_linea_base' => 'required|string|max:255',
            // 'periodo' => 'nullable|string|max:255',
            'meta_2024' => 'required|string|max:255',
            'unidad_medida' => 'required|string|max:255',
            'id_institucion' => 'nullable|integer|exists:instituciones,id',
            'id_usuario' => 'nullable|integer|exists:users,id',
            'fuente' => 'nullable|string',
            'liga' => 'nullable|url',
            'descripcion' => 'nullable|string',
            'periodicidad' => 'required|string|max:255',
            'cobertura' => 'required|string|max:255',
            'tendencia' => 'required|string|max:255',
            'fecha_actualizacion' => 'nullable|date',
            // 'resultados' => 'required|string',
            'formula' => 'required|string',
            // 'odses' => 'required|array',
            // 'odses.*' => 'exists:ods,id',
            'programas_institucionales' => 'nullable|array',
            'programas_institucionales.*' => 'exists:cat_programas_derivados_institucionales,id',
            'datos_anuales' => 'nullable|array',
            'datos_anuales.*.anio' => 'required_with:datos_anuales|integer|distinct|min:1900|max:' . (date('Y') + 10),
            'datos_anuales.*.valor_dato' => 'nullable|numeric',
            'datos_anuales.*.fecha_actualizacion' => 'nullable|date',
            'datos_anuales.*.resultados' => 'nullable|string',
            'datos_anuales.*.evidencia' => 'nullable|string|max:255',
            'datos_anuales.*.observaciones' => 'nullable|string',
        ];

        $messages = [
            'nombre.required' => 'El nombre del indicador es obligatorio.',
            'programa_derivado.required' => 'El programa derivado es obligatorio.',
            'tematica.required' => 'La temática es obligatoria.',
            'linea_base.required' => 'El año de la linea base es obligatorio.',
            'dato_linea_base.required' => 'El dato de la linea base es obligatorio.',
            'meta_2024.required' => 'La Meta 2030 es obligatoria.',
            'unidad_medida.required' => 'La unidad de medida es obligatoria.',
            'periodicidad.required' => 'El programa derivado es obligatorio.',
            'odses.required' => 'Debe seleccionar al menos un ODS.',
            'odses.*.exists' => 'El ODS seleccionado no es válido.',
            'datos_anuales.*.anio.required_with' => 'El año es obligatorio para cada entrada del histórico.',
            'datos_anuales.*.anio.integer' => 'El año debe ser un número entero (ej: 2023).',
            'datos_anuales.*.anio.distinct' => 'No puede haber años duplicados en el histórico.',
            'datos_anuales.*.valor_dato.numeric' => 'El valor del dato anual debe ser un número.',
            'datos_anuales.*.fecha_actualizacion.date' => 'La fecha de actualización del dato anual no es válida.',
        ];

        Log::debug('IndicadorController@store: Antes de la validación.');
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            Log::warning('IndicadorController@store: Falló la validación.', $validator->errors()->toArray()); // LOG 3.1 (Si falla)
            return back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();
        Log::info('IndicadorController@store: Validación exitosa.');
        Log::debug('IndicadorController@store: Antes de DB::beginTransaction().');
        DB::beginTransaction();

        try {
            Log::debug('IndicadorController@store: Dentro del bloque try, antes de crear Indicador.');

            $indicadorableId = null;
            $indicadorableType = null;
            $programaDerivadoString = '';

            if ($request->boolean('es_programa_derivado')) {
                $indicadorableId = $validatedData['programa_id'];
                $modelClass = $this->getProgramaModelClass($validatedData['tipo_programa']);
                $indicadorableType = $modelClass;
                $parentObj = $modelClass::find($indicadorableId);
                $programaDerivadoString = $parentObj ? $parentObj->nombre : $validatedData['tipo_programa'];
            } else {
                $indicadorableId = $request->input('eje_id');
                $indicadorableType = CatEje::class;
                $parentObj = CatEje::find($indicadorableId);
                $programaDerivadoString = ($parentObj && $parentObj->catPlanEstatalDesarrollo) ? $parentObj->catPlanEstatalDesarrollo->nombre : 'Plan Estatal de Desarrollo';
            }

            $indicador = Indicador::create([
                'nombre' => $validatedData['nombre'],
                'programa_derivado' => $programaDerivadoString,
                'programa' => $validatedData['eje_app'],
                'indicadorable_id' => $indicadorableId,
                'indicadorable_type' => $indicadorableType,
                // 'cod_tematica' => $validatedData['cod_tematica'],
                'tematica' => $validatedData['tematica'],
                'linea_base' => $validatedData['linea_base'],
                'dato_linea_base' => $validatedData['dato_linea_base'],
                // 'periodo' => $validatedData['periodo'],
                'meta_2024' => $validatedData['meta_2024'],
                'unidad_medida' => $validatedData['unidad_medida'],
                'id_institucion' => $validatedData['id_institucion'],
                'id_usuario' => $validatedData['id_usuario'],
                'fuente' => $validatedData['fuente'],
                'liga' => $validatedData['liga'],
                'descripcion' => $validatedData['descripcion'],
                'periodicidad' => $validatedData['periodicidad'],
                'cobertura' => $validatedData['cobertura'],
                'tendencia' => $validatedData['tendencia'],
                'fecha_actualizacion' => $validatedData['fecha_actualizacion'],
                // 'resultados' => $validatedData['resultados'],
                'formula' => $validatedData['formula'],
                'indicador_validado' => false,
            ]);
            Log::info('IndicadorController@store: Indicador creado con ID: ' . $indicador->id);

            if ($request->has('programas_institucionales')) {
                $indicador->programasInstitucionales()->sync($request->input('programas_institucionales', []));
            }

            if (isset($validatedData['linea_base']) && isset($validatedData['dato_linea_base']) && $validatedData['dato_linea_base'] !== '') {
                $indicador->datosAnuales()->create([
                    'anio' => $validatedData['linea_base'],
                    'valor_dato' => $validatedData['dato_linea_base'],
                    'modificado' => false,
                    'validado' => true
                ]);
                Log::info("IndicadorController@store: Línea base (Año {$validatedData['linea_base']}) guardada como DatoAnual.");
            }

            if (!empty($validatedData['datos_anuales'])) {
                Log::debug('IndicadorController@store: Procesando datos_anuales. Cantidad: ' . count($validatedData['datos_anuales']));
                foreach ($validatedData['datos_anuales'] as $index => $datoAnualData) {
                    Log::debug("IndicadorController@store: Procesando datoAnualData[{$index}]:", $datoAnualData);
                    if (isset($datoAnualData['anio'])) {
                        $hasSignificantData = !is_null($datoAnualData['valor_dato']) ||
                            !empty($datoAnualData['resultados']) ||
                            !empty($datoAnualData['evidencia']) ||
                            !empty($datoAnualData['observaciones']);

                        if ($hasSignificantData) {
                            Log::debug("IndicadorController@store: Creando DatoAnual para el año {$datoAnualData['anio']} del Indicador ID {$indicador->id}."); // LOG 9.1
                            $datoAnualCreado = $indicador->datosAnuales()->create([
                                'anio' => $datoAnualData['anio'],
                                'valor_dato' => $datoAnualData['valor_dato'] ?? null,
                                'fecha_actualizacion' => $datoAnualData['fecha_actualizacion'] ?? null,
                                'resultados' => $datoAnualData['resultados'] ?? null,
                                'evidencia' => $datoAnualData['evidencia'] ?? null,
                                'observaciones' => $datoAnualData['observaciones'] ?? null,
                                'modificado' => false,
                            ]);
                            Log::info("IndicadorController@store: DatoAnual creado con ID: {$datoAnualCreado->id} para el año {$datoAnualData['anio']}.");
                        } else {
                            Log::debug("IndicadorController@store: Omitiendo creación de DatoAnual para el año {$datoAnualData['anio']} (sin datos significativos).");
                        }
                    } else {
                        Log::warning("IndicadorController@store: Se omitió un dato anual porque no tenía 'anio'. Datos:", $datoAnualData);
                    }
                }
            } else {
                Log::debug('IndicadorController@store: No se proporcionaron datos_anuales.');
            }

            if (!empty($validatedData['odses'])) {
                Log::debug('IndicadorController@store: Antes de sincronizar ODSes.', ['odses_ids' => $validatedData['odses']]);
                $indicador->ods()->sync($validatedData['odses']);
                Log::info('IndicadorController@store: ODSes sincronizados.');
            } else {
                Log::debug('IndicadorController@store: No se proporcionaron ODSes para sincronizar.');
            }

            Log::debug('IndicadorController@store: Antes de DB::commit().');
            DB::commit();
            Log::info('IndicadorController@store: Transacción completada (commit).');

            return redirect()->route('panel-indicadores.index')
                ->with('success', 'Indicador creado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('IndicadorController@store: Error de Validación en el bloque try-catch.', [
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IndicadorController@store: Excepción general atrapada.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->withInput()
                ->with('error', 'Ocurrió un error al guardar el indicador. Por favor, inténtelo de nuevo. Revise los logs para más detalles.');
        }
    }

    /**
     * Muestra la vista de detalle de un indicador.
     * @param  int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        /** @var User */
        $user = auth()->user();

        $indicador = Indicador::with(['datosAnuales', 'ods', 'programasInstitucionales'])->findOrFail($id);

        if ($user->hasRole('Enlace')) {
            $institucionesAsignadas = $user->instituciones->pluck('id');
            if (!$institucionesAsignadas->contains($indicador->id_institucion)) {
                abort(403, 'No tienes permiso para acceder a este indicador.');
            }
        }

        if ($user->hasRole(['Enlace dependencia', 'Visualizador'])) {
            if ($user->id_institucion !== $indicador->id_institucion) {
                abort(403, 'No tienes permiso para acceder a este indicador.');
            }
        }

        if ($user->hasRole('Administrador')) {
            return view('panel-indicadores.mostrar', compact('indicador'));
        }

        return view('panel-indicadores.mostrar', compact('indicador'));
    }

    /**
     * Muestra el formulario para editar un indicador existente.
     * @param  int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        /** @var User */
        $user = auth()->user();

        $indicador = Indicador::with(['datosAnuales', 'programasInstitucionales'])->findOrFail($id);

        if ($user->hasRole('Enlace')) {
            $institucionesAsignadas = $user->instituciones->pluck('id');
            if (!$institucionesAsignadas->contains($indicador->id_institucion)) {
                abort(403, 'No tienes permiso para editar este indicador.');
            }
        }

        if ($user->hasRole(['Enlace dependencia', 'Visualizador'])) {
            if ($user->id_institucion !== $indicador->id_institucion) {
                abort(403, 'No tienes permiso para editar este indicador.');
            }
        }

        $instituciones = Institucion::where('id', '!=', 1)->get();
        $odeses = Odses::all();
        $planes = CatPlanEstatalDesarrollo::all();
        $programasInstitucionales = CatProgramaDerivadoInstitucional::all();
        $usuarios = User::where('id', '>=', 8)
            ->role('Enlace dependencia')
            ->get();
        $periodicidades = [
            'Sexenal',
            'Quinquenal',
            'Trienal',
            'Bienal',
            'Ciclo escolar',
            'Cuatrimestral',
            'Trimestral',
            'Bimestral',
            'Anual',
            'Semestral',
            'Mensual'
        ];
        $coberturas = [
            'Estatal',
            'Regional',
            'Municipal',
        ];

        $tendencias = [
            'Mayor es mejor',
            'Menor es mejor',
            'Constante'
        ];

        return view('panel-indicadores.editar', compact('indicador', 'instituciones', 'odeses', 'usuarios', 'periodicidades', 'coberturas', 'tendencias', 'planes', 'programasInstitucionales'));
    }

    /**
     * Actualiza un indicador y gestiona la creación, actualización y eliminación de sus datos anuales.
     * @param  Request  $request
     * @param  Indicador  $indicador
     * @return RedirectResponse
     */
    public function update(Request $request, Indicador $indicador)
    {
        $user = auth()->user();

        if ($user->hasRole('Enlace')) {
            $institucionesAsignadas = $user->instituciones->pluck('id');
            if (!$institucionesAsignadas->contains($indicador->id_institucion)) {
                abort(403, 'No tienes permiso para actualizar este indicador.');
            }
        }

        if ($user->hasRole(['Enlace dependencia', 'Visualizador'])) {
            if ($user->id_institucion !== $indicador->id_institucion) {
                abort(403, 'No tienes permiso para actualizar este indicador.');
            }
        }

        if (!$indicador || !$indicador->exists) {
            return redirect()->route('panel-indicadores.index')->with('error', 'Indicador no encontrado.');
        }

        if ($request->filled('liga')) {
            $request->merge([
                'liga' => str_replace(' ', '%20', trim($request->input('liga')))
            ]);
        }

        // --- VALIDACIÓN ---
        $rules = [
            'nombre' => 'required|string|max:255',
            'plan_id' => 'required|exists:cat_planes_estatales_desarrollo,id',
            'eje_id' => 'nullable|required_unless:es_programa_derivado,1|exists:cat_ejes,id',
            'es_programa_derivado' => 'boolean',
            'tipo_programa' => 'nullable|required_if:es_programa_derivado,1|string',
            'programa_id' => 'nullable|required_if:es_programa_derivado,1|integer',
            'eje_app' => 'required|string|max:255',
            'tematica' => 'required|string|max:255',
            'linea_base' => 'required|integer|digits:4',
            'dato_linea_base' => 'required|string|max:255',
            'meta_2024' => 'required|string|max:255',
            'unidad_medida' => 'required|string|max:255',
            'id_usuario' => 'nullable|integer|exists:users,id',
            'id_institucion' => 'nullable|integer|exists:instituciones,id',
            'fuente' => 'nullable|string',
            'liga' => 'nullable|url',
            'descripcion' => 'nullable|string',
            'periodicidad' => 'required|string|max:255',
            'cobertura' => 'required|string|max:255',
            'tendencia' => 'required|string|max:255',
            'fecha_actualizacion' => 'nullable|date',
            'formula' => 'required|string',
            'odses' => 'sometimes|array',
            'odses.*' => 'exists:ods,id',
            'programas_institucionales' => 'nullable|array',
            'programas_institucionales.*' => 'exists:cat_programas_derivados_institucionales,id',
            'indicador_validado' => 'sometimes|boolean',
            'datos_anuales' => 'nullable|array',
            'datos_anuales.*.id' => 'nullable|integer|exists:datos_anuales,id',
            'datos_anuales.*.anio' => 'required_with:datos_anuales|integer|min:1900|max:' . (date('Y') + 10) . '|distinct',
            'datos_anuales.*.valor_dato' => 'nullable|numeric',
            'datos_anuales.*.fecha_actualizacion' => 'nullable|date',
            'datos_anuales.*.resultados' => 'nullable|string',
            'datos_anuales.*.observaciones' => 'nullable|string',
            'datos_anuales.*.evidencia_file' => 'nullable|file|mimes:pdf|max:10240',
            'datos_anuales.*.evidencia_actual' => 'nullable|string',
            'datos_anuales.*.eliminar_evidencia' => 'nullable|boolean',
        ];

        $messages = [
            'nombre.required' => 'El nombre del indicador es obligatorio.',
            'nombre.string' => 'El nombre del indicador debe ser texto.',
            'nombre.max' => 'El nombre del indicador no debe exceder los 255 caracteres.',

            'programa_derivado.required' => 'El programa derivado es obligatorio.', // Asumiendo que este es el campo correcto
            'programa_derivado.string' => 'El programa derivado debe ser texto.',
            'programa_derivado.max' => 'El programa derivado no debe exceder los 255 caracteres.',

            'programa.required' => 'El programa es obligatorio.',
            'programa.string' => 'El programa debe ser texto.',
            'programa.max' => 'El programa no debe exceder los 255 caracteres.',

            // 'cod_tematica.required' => 'El código de temática es obligatorio.',
            // 'cod_tematica.string' => 'El código de temática debe ser texto.',
            // 'cod_tematica.max' => 'El código de temática no debe exceder los 255 caracteres.',

            'tematica.required' => 'La temática es obligatoria.',
            'tematica.string' => 'La temática debe ser texto.',
            'tematica.max' => 'La temática no debe exceder los 255 caracteres.',

            'linea_base.required' => 'El año de la línea base es obligatorio.',
            'linea_base.integer' => 'El año de la línea base debe ser un número entero.',
            'linea_base.digits' => 'El año de la línea base debe ser un número de 4 dígitos (ej: 2020).',

            'dato_linea_base.required' => 'El valor de la línea base es obligatorio.',
            'dato_linea_base.string' => 'El valor de la línea base debe ser texto o número.',
            'dato_linea_base.max' => 'El valor de la línea base no debe exceder los 255 caracteres.',

            'meta_2024.required' => 'La meta 2024 es obligatoria.',
            'meta_2024.string' => 'La meta 2024 debe ser texto o número.',
            'meta_2024.max' => 'La meta 2024 no debe exceder los 255 caracteres.',

            'unidad_medida.required' => 'La unidad de medida es obligatoria.',
            'unidad_medida.string' => 'La unidad de medida debe ser texto.',
            'unidad_medida.max' => 'La unidad de medida no debe exceder los 255 caracteres.',

            'id_usuario.integer' => 'El usuario asignado no es válido.',
            'id_usuario.exists' => 'El usuario asignado no existe.',

            'id_institucion.integer' => 'La institución asignada no es válida.',
            'id_institucion.exists' => 'La institución asignada no existe.',

            'fuente.string' => 'La fuente debe ser texto.',
            'liga.url' => 'El enlace (liga) debe ser una URL válida (ej: http://www.ejemplo.com).',
            'descripcion.string' => 'La descripción debe ser texto.',

            'periodicidad.required' => 'La periodicidad es obligatoria.',
            'periodicidad.string' => 'La periodicidad debe ser texto.',
            'periodicidad.max' => 'La periodicidad no debe exceder los 255 caracteres.',

            'cobertura.required' => 'La cobertura es obligatoria.',
            'cobertura.string' => 'La cobertura debe ser texto.',
            'cobertura.max' => 'La cobertura no debe exceder los 255 caracteres.',

            'tendencia.required' => 'La tendencia es obligatoria.',
            'tendencia.string' => 'La tendencia debe ser texto.',
            'tendencia.max' => 'La tendencia no debe exceder los 255 caracteres.',

            'fecha_actualizacion.date' => 'La fecha de actualización del indicador no es una fecha válida.',
            // 'resultados.required' => 'Los resultados principales del indicador son obligatorios.',
            // 'resultados.string' => 'Los resultados principales deben ser texto.',
            'formula.required' => 'La fórmula del indicador es obligatoria.',
            'formula.string' => 'La fórmula debe ser texto.',

            'odses.array' => 'La selección de ODS no es válida.',
            'odses.*.exists' => 'Uno o más ODS seleccionados no son válidos.',
            'indicador_validado.boolean' => 'El estado de validación del indicador no es correcto.',

            // Mensajes para los campos de 'datos_anuales' (array)
            'datos_anuales.array' => 'El formato de los datos anuales no es correcto.',

            'datos_anuales.*.id.integer' => 'El ID del dato anual no es válido.',
            'datos_anuales.*.id.exists' => 'Uno de los datos anuales que intenta modificar no existe.',

            'datos_anuales.*.anio.required_with' => 'El año es obligatorio para cada registro del histórico.',
            'datos_anuales.*.anio.integer' => 'El año en el histórico debe ser un número entero (ej: 2023).',
            'datos_anuales.*.anio.distinct' => 'No puede haber años duplicados en el histórico de este indicador.',
            'datos_anuales.*.anio.min' => 'El año en el histórico debe ser válido (mínimo 1900).',
            'datos_anuales.*.anio.max' => 'El año en el histórico no puede ser tan futuro.',

            'datos_anuales.*.valor_dato.numeric' => 'El valor del dato en el histórico debe ser un número.',

            'datos_anuales.*.fecha_actualizacion.date' => 'La fecha de actualización en el histórico no es una fecha válida.',

            'datos_anuales.*.resultados.string' => 'Los resultados anuales en el histórico deben ser texto.',
            'datos_anuales.*.observaciones.string' => 'Las observaciones anuales en el histórico deben ser texto.',

            'datos_anuales.*.evidencia_file.file' => 'El archivo de evidencia para el histórico debe ser un archivo válido.',
            'datos_anuales.*.evidencia_file.mimes' => 'El archivo de evidencia para el histórico debe ser de tipo PDF.',
            'datos_anuales.*.evidencia_file.max' => 'El archivo de evidencia PDF para el histórico no debe superar los 10MB.',

            'datos_anuales.*.evidencia_actual.string' => 'El nombre de la evidencia actual no es válido.',
            'datos_anuales.*.eliminar_evidencia.boolean' => 'La opción para eliminar evidencia no es válida.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();

        DB::beginTransaction();

        try {
            $indicadorableId = null;
            $indicadorableType = null;
            $programaDerivadoString = '';

            if ($request->boolean('es_programa_derivado')) {
                $indicadorableId = $validatedData['programa_id'];
                $modelClass = $this->getProgramaModelClass($validatedData['tipo_programa']);
                $indicadorableType = $modelClass;

                $parentObj = $modelClass::find($indicadorableId);
                $programaDerivadoString = $parentObj ? $parentObj->nombre : $validatedData['tipo_programa'];
            } else {
                $indicadorableId = $request->input('eje_id');
                $indicadorableType = CatEje::class;

                $parentObj = CatEje::find($indicadorableId);
                $programaDerivadoString = $parentObj && $parentObj->planEstatal ? $parentObj->planEstatal->nombre : 'Plan Estatal de Desarrollo';
            }

            $indicadorDataToUpdate = collect($validatedData)->except(['odses', 'programas_institucionales', 'datos_anuales', '_token', '_method', 'plan_id', 'es_programa_derivado', 'tipo_programa', 'programa_id', 'eje_app'])->toArray();

            $indicadorDataToUpdate['programa_derivado'] = $programaDerivadoString;
            $indicadorDataToUpdate['programa'] = $validatedData['eje_app'];
            $indicadorDataToUpdate['indicadorable_id'] = $indicadorableId;
            $indicadorDataToUpdate['indicadorable_type'] = $indicadorableType;

            $mainIndicadorFieldsChanged = false;
            foreach ($indicadorDataToUpdate as $key => $value) {
                if ($key !== 'indicador_validado' && $indicador->{$key} != $value) {
                    $mainIndicadorFieldsChanged = true;
                    break;
                }
            }
            if ($mainIndicadorFieldsChanged && !isset($indicadorDataToUpdate['indicador_validado'])) {
                $indicadorDataToUpdate['indicador_validado'] = false;
            }

            $anioLineaBaseAnterior = $indicador->linea_base;

            $indicador->update($indicadorDataToUpdate);

            if ($request->has('programas_institucionales')) {
                $indicador->programasInstitucionales()->sync($request->input('programas_institucionales', []));
            } else {
                $indicador->programasInstitucionales()->sync([]);
            }

            $idsDatosAnualesEnviadosYProcesados = [];

            if (isset($validatedData['linea_base']) && isset($validatedData['dato_linea_base']) && $validatedData['dato_linea_base'] !== '') {

                if ($anioLineaBaseAnterior && $anioLineaBaseAnterior != $validatedData['linea_base']) {
                    $indicador->datosAnuales()->where('anio', $anioLineaBaseAnterior)->delete();
                }

                $datoAnualLineaBase = $indicador->datosAnuales()->updateOrCreate(
                    ['anio' => $validatedData['linea_base']],
                    ['valor_dato' => $validatedData['dato_linea_base']]
                );

                $idsDatosAnualesEnviadosYProcesados[] = $datoAnualLineaBase->id;
            }

            if (isset($validatedData['datos_anuales'])) {
                $archivosEvidenciaEnRequest = $request->file('datos_anuales') ?? [];

                foreach ($validatedData['datos_anuales'] as $index => $datoAnualData) {
                    $idDatoAnual = $datoAnualData['id'] ?? null;
                    $anio = $datoAnualData['anio'] ?? null;

                    if (empty($anio)) continue;

                    if ($anioLineaBaseAnterior && $anio == $anioLineaBaseAnterior && $anio != $validatedData['linea_base']) {
                        continue;
                    }

                    $datoAnualRecord = null;
                    if ($idDatoAnual) {
                        $datoAnualRecord = DatoAnual::where('id', $idDatoAnual)->where('id_indicador', $indicador->id)->first();
                        if (!$datoAnualRecord) {
                            $datoAnualRecord = $indicador->datosAnuales()->firstOrNew(['anio' => $anio]);
                        }
                    } else {
                        $datoAnualRecord = $indicador->datosAnuales()->firstOrNew(['anio' => $anio]);
                    }

                    if (!$datoAnualRecord->exists) {
                        $datoAnualRecord->id_indicador = $indicador->id;
                    }

                    $datosParaLlenar = [
                        'anio' => $anio,
                        'valor_dato' => $datoAnualData['valor_dato'] ?? null,
                        'fecha_actualizacion' => $datoAnualData['fecha_actualizacion'] ?? null,
                        'resultados' => $datoAnualData['resultados'] ?? null,
                        'observaciones' => $datoAnualData['observaciones'] ?? null,
                    ];

                    $nombreArchivoEvidenciaActual = $datoAnualData['evidencia_actual'] ?? ($datoAnualRecord->evidencia ?? null);
                    $nombreArchivoEvidenciaParaGuardar = $nombreArchivoEvidenciaActual;

                    if (!empty($datoAnualData['eliminar_evidencia'])) {
                        if ($nombreArchivoEvidenciaActual && file_exists(public_path('assets-administrador/docs/' . $nombreArchivoEvidenciaActual))) {
                            unlink(public_path('assets-administrador/docs/' . $nombreArchivoEvidenciaActual));
                        }
                        $nombreArchivoEvidenciaParaGuardar = null;
                    }

                    $archivoEvidenciaSubido = $archivosEvidenciaEnRequest[$index]['evidencia_file'] ?? null;
                    if ($archivoEvidenciaSubido && $archivoEvidenciaSubido->isValid()) {
                        if ($nombreArchivoEvidenciaActual && ($nombreArchivoEvidenciaParaGuardar === null || $nombreArchivoEvidenciaActual !== $nombreArchivoEvidenciaParaGuardar)) {
                            if (file_exists(public_path('assets-administrador/docs/' . $nombreArchivoEvidenciaActual))) {
                                unlink(public_path('assets-administrador/docs/' . $nombreArchivoEvidenciaActual));
                            }
                        }
                        $extension = $archivoEvidenciaSubido->getClientOriginalExtension();
                        $nombreArchivoEvidenciaParaGuardar = "Evidencia_{$anio}_{$indicador->id}_" . time() . "_" . $index . "." . $extension;
                        $archivoEvidenciaSubido->move(public_path('assets-administrador/docs/'), $nombreArchivoEvidenciaParaGuardar);
                    }

                    $datosParaLlenar['evidencia'] = $nombreArchivoEvidenciaParaGuardar;
                    $datoAnualRecord->fill($datosParaLlenar);

                    if ($datoAnualRecord->id_indicador === null) {
                        throw new \Exception("Integridad de datos: id_indicador no puede ser nulo para DatoAnual del año {$anio}.");
                    }

                    if ($datoAnualRecord->isDirty() || !$datoAnualRecord->exists) {
                        $datoAnualRecord->save();
                    }

                    if (!in_array($datoAnualRecord->id, $idsDatosAnualesEnviadosYProcesados)) {
                        $idsDatosAnualesEnviadosYProcesados[] = $datoAnualRecord->id;
                    }
                }
            }

            if ($request->exists('datos_anuales')) {
                $datosAnualesAEliminar = $indicador->datosAnuales()->whereNotIn('id', $idsDatosAnualesEnviadosYProcesados)->get();
                foreach ($datosAnualesAEliminar as $dae) {
                    if ($dae->evidencia && file_exists(public_path('assets-administrador/docs/' . $dae->evidencia))) {
                        unlink(public_path('assets-administrador/docs/' . $dae->evidencia));
                    }
                    $dae->delete();
                }
            }

            if ($request->has('odses')) {
                $indicador->ods()->sync($validatedData['odses'] ?? []);
            }

            DB::commit();
            return redirect()->route('panel-indicadores.index')->with('success', 'Indicador actualizado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Ocurrió un error al actualizar el indicador: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un indicador y todos sus datos y archivos relacionados.
     * @param  Indicador  $indicador
     * @return RedirectResponse
     */
    public function destroy(Indicador $indicador)
    {
        /** @var User */
        $user = auth()->user();

        if ($user->hasRole('Enlace')) {
            $institucionesAsignadas = $user->instituciones->pluck('id');
            if (!$institucionesAsignadas->contains($indicador->id_institucion)) {
                abort(403, 'No tienes permiso para eliminar este indicador.');
            }
        }

        if ($user->hasRole(['Enlace dependencia', 'Visualizador'])) {
            if ($user->id_institucion !== $indicador->id_institucion) {
                abort(403, 'No tienes permiso para eliminar este indicador.');
            }
        }

        Log::debug("IndicadorController@destroy: Iniciando eliminación para Indicador ID: {$indicador->id}");

        DB::beginTransaction();
        try {
            foreach ($indicador->datosAnuales as $datoAnual) {
                if ($datoAnual->evidencia) {
                    $rutaArchivo = public_path('assets-administrador/docs/' . $datoAnual->evidencia);
                    if (file_exists($rutaArchivo)) {
                        unlink($rutaArchivo);
                        Log::info("IndicadorController@destroy: Archivo de evidencia '{$datoAnual->evidencia}' eliminado para DatoAnual ID {$datoAnual->id} (Indicador ID {$indicador->id}).");
                    }
                }
            }

            $indicador->datosAnuales()->delete();
            Log::info("IndicadorController@destroy: Registros DatoAnual eliminados para Indicador ID {$indicador->id}.");

            $indicador->ods()->detach();
            Log::info("IndicadorController@destroy: Relaciones ODS eliminadas para Indicador ID {$indicador->id}.");

            $indicador->delete();
            Log::info("IndicadorController@destroy: Indicador ID {$indicador->id} eliminado de la base de datos.");

            DB::commit();
            Log::info("IndicadorController@destroy: Transacción completada para Indicador ID {$indicador->id}.");

            return redirect()->route('panel-indicadores.index')->with('success', 'Indicador y todos sus datos relacionados han sido eliminados correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("IndicadorController@destroy: Error al eliminar Indicador ID {$indicador->id}.", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return redirect()->route('panel-indicadores.index')->with('error', 'Ocurrió un error al eliminar el indicador: ' . $e->getMessage());
        }
    }

    public function filtrarIndicadores($institucion, $programa = null)
    {
        /** @var User */
        $user = auth()->user();

        if ($user->hasRole('Enlace')) {
            $institucionesAsignadas = $user->instituciones->pluck('id');

            if ($institucion !== 'todos' && !$institucionesAsignadas->contains($institucion)) {
                return response()->json(['error' => 'No tienes acceso a esta institución.'], 403);
            }

            $indicadores = Indicador::query()
                ->when($institucion !== 'todos', function ($query) use ($institucion) {
                    $query->where('id_institucion', $institucion);
                })
                ->when($programa, function ($query) use ($programa) {
                    $query->where('programa_derivado', $programa);
                })
                ->whereIn('id_institucion', $institucionesAsignadas)
                ->get();
        } else {
            $indicadores = Indicador::query()
                ->when($institucion !== 'todos', function ($query) use ($institucion) {
                    $query->where('id_institucion', $institucion);
                })
                ->when($programa, function ($query) use ($programa) {
                    $query->where('programa_derivado', $programa);
                })
                ->get();
        }
        return View::make('panel-indicadores.tabla_indicadores', compact('indicadores', 'programa'));
    }

    /**
     * Cambia el estado de validación de un indicador.
     * @param  int $id
     * @return RedirectResponse
     */
    public function toggleValidacion($id)
    {
        $indicador = Indicador::findOrFail($id);

        $estadoValidacion = !$indicador->indicador_validado;
        $indicador->indicador_validado = $estadoValidacion;

        $indicador->save();
        $indicador->datosAnuales()->update(['validado' => $estadoValidacion]);

        return redirect()->back()->with('status', 'Estado de validación actualizado.');
    }

    /**
     * Almacena un nuevo año para un indicador.
     * @param  Request $request
     * @param  int $id ID del Indicador
     * @return RedirectResponse
     */
    public function storeAnualData(Request $request, $id)
    {
        Log::debug("IndicadorController@storeAnualData: Iniciado para Indicador ID: {$id}.");
        $year = $request->anio;

        if (empty($year)) {
            return redirect()->back()->with('error', 'El año es obligatorio.');
        }

        return $this->updateAnualData($request, $id, $year);
    }

    /**
     * Actualiza los datos de un único año para un indicador.
     * @param  \Illuminate\Http\Request $request
     * @param  int $id ID del Indicador
     * @param  int $year Año del DatoAnual
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAnualData(Request $request, $id, $year)
    {
        Log::debug("IndicadorController@updateAnualData: Iniciado para Indicador ID: {$id}, Año: {$year}.");
        Log::debug("IndicadorController@updateAnualData: Request All:", $request->all());
        Log::debug("IndicadorController@updateAnualData: Request Files:", $request->files->all());


        $indicador = Indicador::findOrFail($id);

        $rules = [
            'valor_dato' => 'nullable|numeric',
            'resultados_anual' => 'nullable|string',
            'observaciones_anual' => 'nullable|string|max:255',
            'evidencia_anual' => 'nullable|file|mimes:pdf|max:10240',
            'fecha_actualizacion_anual' => 'nullable|date',
            'eliminar_evidencia_anual' => 'nullable|boolean',
        ];

        $messages = [
            'valor_dato.numeric' => "El valor del dato para el año {$year} debe ser numérico.",
            'resultados_anual.string' => "Los resultados para el año {$year} deben ser texto.",
            'observaciones_anual.string' => "Las observaciones para el año {$year} deben ser texto.",
            'observaciones_anual.max' => "Las observaciones para el año {$year} no deben exceder los 255 caracteres.",
            'evidencia_anual.file' => "La evidencia para el año {$year} debe ser un archivo.",
            'evidencia_anual.mimes' => "La evidencia para el año {$year} debe ser un archivo PDF.",
            'evidencia_anual.max' => "La evidencia (PDF) para el año {$year} no debe pesar más de 10 MB.",
            'fecha_actualizacion_anual.date' => "La fecha de actualización para el año {$year} debe ser una fecha válida.",
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            Log::warning("IndicadorController@updateAnualData: Validación fallida para Indicador ID: {$id}, Año: {$year}.", $validator->errors()->toArray());
            return redirect()->back()
                ->withErrors($validator, "updateAnualValidation_{$year}")
                ->withInput()
                ->with("updateAnualValidationErrors_{$year}", true);
        }

        $validatedData = $validator->validated();
        Log::info("IndicadorController@updateAnualData: Validación exitosa para Indicador ID: {$id}, Año: {$year}.");

        DB::beginTransaction();
        try {
            $datoAnual = $indicador->datosAnuales()->firstOrNew(['anio' => $year]);
            Log::debug("IndicadorController@updateAnualData: DatoAnual " . ($datoAnual->exists ? "encontrado (ID: {$datoAnual->id})" : "nuevo") . " para Año: {$year}.");

            $dataToSave = [
                'valor_dato' => $validatedData['valor_dato'] ?? null,
                'resultados' => $validatedData['resultados_anual'] ?? null,
                'observaciones' => $validatedData['observaciones_anual'] ?? null,
                'fecha_actualizacion' => $validatedData['fecha_actualizacion_anual'] ?? null,
            ];

            $nombreArchivoEvidenciaActual = $datoAnual->evidencia ?? null;
            $nombreArchivoEvidenciaParaGuardar = $nombreArchivoEvidenciaActual;

            if (!empty($validatedData['eliminar_evidencia_anual'])) {
                if ($nombreArchivoEvidenciaActual) {
                    Log::debug("IndicadorController@updateAnualData: [Ind.{$id}, Año {$year}] Eliminando evidencia actual '{$nombreArchivoEvidenciaActual}' por checkbox.");
                    if (file_exists(public_path('assets-administrador/docs/' . $nombreArchivoEvidenciaActual))) {
                        unlink(public_path('assets-administrador/docs/' . $nombreArchivoEvidenciaActual));
                    }
                }
                $nombreArchivoEvidenciaParaGuardar = null;
            }

            if ($request->hasFile('evidencia_anual') && $request->file('evidencia_anual')->isValid()) {
                $archivoEvidenciaSubido = $request->file('evidencia_anual');
                Log::debug("IndicadorController@updateAnualData: [Ind.{$id}, Año {$year}] Nuevo archivo de evidencia subido: " . $archivoEvidenciaSubido->getClientOriginalName());

                if ($nombreArchivoEvidenciaActual && ($nombreArchivoEvidenciaParaGuardar === null || $nombreArchivoEvidenciaActual !== $nombreArchivoEvidenciaParaGuardar)) {
                    if (file_exists(public_path('assets-administrador/docs/' . $nombreArchivoEvidenciaActual))) {
                        unlink(public_path('assets-administrador/docs/' . $nombreArchivoEvidenciaActual));
                        Log::debug("IndicadorController@updateAnualData: [Ind.{$id}, Año {$year}] Evidencia antigua '{$nombreArchivoEvidenciaActual}' eliminada para reemplazo.");
                    }
                }
                $extension = $archivoEvidenciaSubido->getClientOriginalExtension();
                $nombreArchivoEvidenciaParaGuardar = "Evidencia_{$year}_{$indicador->id}_" . time() . "." . $extension;
                $archivoEvidenciaSubido->move(public_path('assets-administrador/docs/'), $nombreArchivoEvidenciaParaGuardar);
                Log::info("IndicadorController@updateAnualData: [Ind.{$id}, Año {$year}] Nueva evidencia '{$nombreArchivoEvidenciaParaGuardar}' guardada.");
            }
            $dataToSave['evidencia'] = $nombreArchivoEvidenciaParaGuardar;

            $datoAnual->fill($dataToSave);

            if ($datoAnual->isDirty() || !$datoAnual->exists) {
                if (!$datoAnual->exists && !$datoAnual->id_indicador) {
                    $datoAnual->id_indicador = $indicador->id;
                    Log::warning("IndicadorController@updateAnualData: [Ind.{$id}, Año {$year}] id_indicador fue explícitamente asignado para nuevo DatoAnual.");
                }

                if ($datoAnual->id_indicador === null) {
                    Log::critical("IndicadorController@updateAnualData: [Ind.{$id}, Año {$year}] ¡CRÍTICO! id_indicador es NULL ANTES de guardar DatoAnual. Abortando.");
                    throw new \Exception("No se pudo asociar el dato anual con el indicador.");
                }
                $datoAnual->save();
                Log::info("IndicadorController@updateAnualData: DatoAnual para Indicador ID: {$id}, Año: {$year} (ID DatoAnual: {$datoAnual->id}) guardado.");
            } else {
                Log::info("IndicadorController@updateAnualData: Sin cambios detectados para DatoAnual de Indicador ID: {$id}, Año: {$year}. No se guardó.");
            }

            DB::commit();
            Log::info("IndicadorController@updateAnualData: Transacción completada para Indicador ID: {$id}, Año: {$year}.");

            return redirect()->back()->with('success', "Datos para el año {$year} actualizados correctamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("IndicadorController@updateAnualData: Excepción para Indicador ID: {$id}, Año: {$year}.", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return redirect()->back()
                ->with('error', "Ocurrió un error al actualizar los datos para el año {$year}: " . $e->getMessage())
                ->with("updateAnualValidationErrors_{$year}", true);
        }
    }

    /**
     * Finaliza el periodo de captura para un usuario.
     * @param  Request $request
     * @return JsonResponse
     */
    public function finalizarCaptura(Request $request)
    {
        $user = User::find($request->userId);
        if ($user) {
            $user->finalizado = 1;
            $user->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 500);
    }

    /**
     * Genera la vista de reporte imprimible para un usuario.
     * @param int $id ID del Usuario
     * @return \Illuminate\View\View
     */
    public function generarReporte($id)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Usuario no autenticado.');
        }

        $user->load([
            'institucion',
            'indicadores.datosAnuales',
            'indicadores.institucion'
        ]);

        $user->update([
            'reporte_generado' => true,
            'reporte_generado_at' => now(),
        ]);

        return view('panel-indicadores.generar-documento', compact('user'));
    }

    /**
     * Genera y descarga un archivo Excel con indicadores filtrados.
     * @param  Request $request
     * @return JsonResponse
     */
    public function datosAbiertosPed(Request $request)
    {
        Log::debug('IndicadorController@datosAbiertosPed: Iniciado.', $request->all());
        $nombreArchivoBase = $request->nombre_archivo;
        $parametro = $request->parametro;
        $indicadoresQuery = Indicador::select(
            'id',
            'nombre',
            'programa_derivado',
            'programa',
            'tematica',
            'linea_base',
            'dato_linea_base',
            'unidad_medida',
            'meta_2024',
            'fuente',
            'liga',
            'descripcion',
            'periodicidad',
            'cobertura',
            'tendencia',
            'id_institucion',
            // 'resultados',
            'formula',
            'fecha_actualizacion'
        )->with([
            'datosAnuales' => function ($query) {
                $query->select('id_indicador', 'anio', 'valor_dato');
            },
            'ods',
            'institucion:id,nombre'
        ]);

        switch ($parametro) {
            case 'total-indicadores-ped':
                Log::debug("IndicadorController@datosAbiertosPed: Caso 'total-indicadores-ped', no se añaden filtros 'where' adicionales.");
                break;
            case 'indicadores-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo');
                Log::debug("IndicadorController@datosAbiertosPed: Aplicado filtro where programa_derivado = 'Plan Estatal de Desarrollo'");
                break;
            case 'indicadores-pd-ped':
                $indicadoresQuery->whereIn('programa_derivado', [
                    'Programa Sectorial',
                    'Programa Especial',
                    'Programa Institucional',
                    'Programa Regional'
                ]);
                Log::debug("IndicadorController@datosAbiertosPed: Aplicado filtro whereIn programa_derivado.");
                break;
            case 'indicadores-eje1-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Humanismo con Bienestar');
                Log::debug("IndicadorController@datosAbiertosPed: Aplicados filtros para Eje 1.");
                break;
            case 'indicadores-eje2-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Prosperidad y Estabilidad Económica');
                Log::debug("IndicadorController@datosAbiertosPed: Aplicados filtros para Eje 2.");
                break;
            case 'indicadores-eje3-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Estado de Derecho, Seguridad y Justicia');
                Log::debug("IndicadorController@datosAbiertosPed: Aplicados filtros para Eje 3.");
                break;
            case 'indicadores-eje4-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Desarrollo Urbano y Crecimiento Sostenible');
                Log::debug("IndicadorController@datosAbiertosPed: Aplicados filtros para Eje 4.");
                break;
            case 'indicadores-eje5-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Gobierno Transformador y de Resultados');
                Log::debug("IndicadorController@datosAbiertosPed: Aplicados filtros para Eje 5.");
                break;
            case 'indicadores-eje6-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Por Amor a Puebla');
                Log::debug("IndicadorController@datosAbiertosPed: Aplicados filtros para Eje 6.");
                break;
            default:
                Log::error('IndicadorController@datosAbiertosPed: Parámetro no válido recibido.', ['parametro' => $parametro]);
                return response()->json(['success' => false, 'message' => 'Parámetro de filtro no válido.'], 400);
        }

        $indicadoresCollection = $indicadoresQuery->get();
        Log::debug('IndicadorController@datosAbiertosPed: Indicadores obtenidos de BD: ' . $indicadoresCollection->count());

        $indicadoresParaExcel = $indicadoresCollection->map(function ($indicador) {
            $camposAnualesParaExcel = [];
            $rangoDeAniosParaExcel = range(2015, 2030);

            foreach ($rangoDeAniosParaExcel as $year) {
                $camposAnualesParaExcel["dato_$year"] = '';
            }

            if ($indicador->datosAnuales && $indicador->datosAnuales->isNotEmpty()) {
                foreach ($indicador->datosAnuales as $datoAnual) {
                    $keyParaValor = "dato_" . $datoAnual->anio;
                    if (array_key_exists($keyParaValor, $camposAnualesParaExcel)) {
                        $camposAnualesParaExcel[$keyParaValor] = $datoAnual->valor_dato;
                    }
                }
            }

            $ods = $indicador->ods->pluck('id')->unique()->implode(', ');

            $datosIndicadorBase = $indicador->only([
                'id',
                'nombre',
                'programa_derivado',
                'programa',
                'tematica',
                'linea_base',
                'dato_linea_base',
                'unidad_medida',
                'meta_2024',
                'fuente',
                'liga',
                'descripcion',
                'periodicidad',
                'cobertura',
                'tendencia',
                'id_institucion',
                // 'resultados',
                'formula',
                'fecha_actualizacion'
            ]);
            $datosIndicadorBase['nombre_institucion'] = $indicador->institucion ? $indicador->institucion->nombre : 'N/A';
            return array_merge($datosIndicadorBase, $camposAnualesParaExcel, ['ods' => $ods]);
        });

        Log::debug('IndicadorController@datosAbiertosPed: Indicadores mapeados para Excel: ' . $indicadoresParaExcel->count());

        $rutaPlantillaExcel = public_path('docs/plantillas-exportacion/plantilla.xlsx');

        try {
            $spreadsheet = IOFactory::load($rutaPlantillaExcel);
            $sheet = $spreadsheet->getActiveSheet();

            $fila = 2;
            foreach ($indicadoresParaExcel as $indicadorDataRow) {
                $sheet->setCellValue("A{$fila}", $fila - 1);
                $sheet->setCellValue("B{$fila}", $indicadorDataRow['nombre']);
                $sheet->setCellValue("C{$fila}", $indicadorDataRow['programa_derivado']);
                $sheet->setCellValue("D{$fila}", $indicadorDataRow['programa']);
                $sheet->setCellValue("E{$fila}", $indicadorDataRow['tematica']);
                $sheet->setCellValue("F{$fila}", $indicadorDataRow['linea_base']);
                $sheet->setCellValue("G{$fila}", $indicadorDataRow['dato_linea_base']);
                $sheet->setCellValue("H{$fila}", $indicadorDataRow['unidad_medida']);
                $sheet->setCellValue("I{$fila}", $indicadorDataRow['meta_2024']);
                $sheet->setCellValue("J{$fila}", $indicadorDataRow['fuente']);
                $sheet->setCellValue("K{$fila}", $indicadorDataRow['liga']);
                $sheet->setCellValue("L{$fila}", $indicadorDataRow['descripcion']);
                $sheet->setCellValue("M{$fila}", $indicadorDataRow['periodicidad']);
                $sheet->setCellValue("N{$fila}", $indicadorDataRow['cobertura']);
                $sheet->setCellValue("O{$fila}", $indicadorDataRow['tendencia']);
                // $sheet->setCellValue("P{$fila}", $indicadorDataRow['resultados']);
                $sheet->setCellValue("P{$fila}", $indicadorDataRow['formula']);
                $sheet->setCellValue("Q{$fila}", $indicadorDataRow['ods']);
                $sheet->setCellValue("R{$fila}", $indicadorDataRow['fecha_actualizacion']);

                // Columnas de datos anuales
                $sheet->setCellValue("S{$fila}", $indicadorDataRow['dato_2015']);
                $sheet->setCellValue("T{$fila}", $indicadorDataRow['dato_2016']);
                $sheet->setCellValue("U{$fila}", $indicadorDataRow['dato_2017']);
                $sheet->setCellValue("V{$fila}", $indicadorDataRow['dato_2018']);
                $sheet->setCellValue("W{$fila}", $indicadorDataRow['dato_2019']);
                $sheet->setCellValue("X{$fila}", $indicadorDataRow['dato_2020']);
                $sheet->setCellValue("Y{$fila}", $indicadorDataRow['dato_2021']);
                $sheet->setCellValue("Z{$fila}", $indicadorDataRow['dato_2022']);
                $sheet->setCellValue("AA{$fila}", $indicadorDataRow['dato_2023']);
                $sheet->setCellValue("AB{$fila}", $indicadorDataRow['dato_2024']);
                $sheet->setCellValue("AC{$fila}", $indicadorDataRow['dato_2025']);
                $sheet->setCellValue("AD{$fila}", $indicadorDataRow['dato_2026']);
                $sheet->setCellValue("AE{$fila}", $indicadorDataRow['dato_2027']);
                $sheet->setCellValue("AF{$fila}", $indicadorDataRow['dato_2028']);
                $sheet->setCellValue("AG{$fila}", $indicadorDataRow['dato_2029']);
                $sheet->setCellValue("AH{$fila}", $indicadorDataRow['dato_2030']);
                $sheet->setCellValue("AI{$fila}", $indicadorDataRow['nombre_institucion']);
                $fila++;
            }
            Log::debug('IndicadorController@datosAbiertosPed: Datos escritos en la hoja de Excel.');

            $nombreArchivoFinal = "indicadores_ped_{$nombreArchivoBase}.xlsx";
            $rutaSalida = storage_path("app/public/exports/{$nombreArchivoFinal}");

            if (!Storage::disk('public')->exists('exports')) {
                Storage::disk('public')->makeDirectory('exports');
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($rutaSalida);
            Log::info("IndicadorController@datosAbiertosPed: Archivo Excel '{$nombreArchivoFinal}' guardado en: {$rutaSalida}");

            return response()->download($rutaSalida, $nombreArchivoFinal)->deleteFileAfterSend(true);
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            Log::error('Error de PhpSpreadsheet en datosAbiertosPed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al generar el archivo Excel (Spreadsheet).'], 500);
        } catch (\Exception $e) {
            Log::error('Error general en datosAbiertosPed: ' . $e->getMessage() . ' Archivo: ' . $e->getFile() . ' Línea: ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Error inesperado al generar el reporte.'], 500);
        }
    }

    /**
     * Genera y descarga un archivo CSV con indicadores filtrados.
     * @param  Request $request
     * @return RedirectResponse
     */
    public function datosAbiertosPedCsv(Request $request)
    {
        Log::debug('IndicadorController@datosAbiertosPedCsv: Iniciado.', $request->all());
        $nombreArchivoBase = $request->input('nombre_archivo', 'exportacion_indicadores'); // Usar input()
        $parametro = $request->input('parametro');

        $indicadoresQuery = Indicador::select(
            'id',
            'nombre',
            'programa_derivado',
            'programa',
            'tematica',
            'linea_base',
            'dato_linea_base',
            'unidad_medida',
            'meta_2024',
            'fuente',
            'liga',
            'descripcion',
            'periodicidad',
            'cobertura',
            'tendencia',
            'id_institucion',
            // 'resultados',
            'formula',
            'fecha_actualizacion'
        )->with([
            'datosAnuales' => function ($query) {
                $query->select('id_indicador', 'anio', 'valor_dato');
            },
            'ods:id,nombre',
            'institucion:id,nombre'
        ]);

        switch ($parametro) {
            case 'total-indicadores':
                Log::debug("Caso 'total-indicadores-ped'");
                break;
            case 'indicadores-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo');
                Log::debug("IndicadorController@datosAbiertosPedCSV: Aplicado filtro where programa_derivado = 'Plan Estatal de Desarrollo'");
                break;
            case 'indicadores-pd-ped':
                $indicadoresQuery->whereIn('programa_derivado', [
                    'Programa Sectorial',
                    'Programa Especial',
                    'Programa Institucional',
                    'Programa Regional'
                ]);
                Log::debug("IndicadorController@datosAbiertosPedCSV: Aplicado filtro whereIn programa_derivado.");
                break;
            case 'indicadores-eje1-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Humanismo con Bienestar');
                Log::debug("IndicadorController@datosAbiertosPedCSV: Aplicados filtros para Eje 1.");
                break;
            case 'indicadores-eje2-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Prosperidad y Estabilidad Económica');
                Log::debug("IndicadorController@datosAbiertosPedCSV: Aplicados filtros para Eje 2.");
                break;
            case 'indicadores-eje3-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Estado de Derecho, Seguridad y Justicia');
                Log::debug("IndicadorController@datosAbiertosPedCSV: Aplicados filtros para Eje 3.");
                break;
            case 'indicadores-eje4-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Desarrollo Urbano y Crecimiento Sostenible');
                Log::debug("IndicadorController@datosAbiertosPedCSV: Aplicados filtros para Eje 4.");
                break;
            case 'indicadores-eje5-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Gobierno Transformador y de Resultados');
                Log::debug("IndicadorController@datosAbiertosPedCSV: Aplicados filtros para Eje 5.");
                break;
            case 'indicadores-eje6-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Por Amor a Puebla');
                Log::debug("IndicadorController@datosAbiertosPedCSV: Aplicados filtros para Eje 6.");
                break;
            default:
                Log::error('IndicadorController@datosAbiertosPedCSV: Parámetro no válido recibido.', ['parametro' => $parametro]);
                return response()->json(['success' => false, 'message' => 'Parámetro de filtro no válido.'], 400);
        }

        $indicadoresCollection = $indicadoresQuery->get();
        Log::debug('Indicadores obtenidos de BD para CSV: ' . $indicadoresCollection->count());

        $rangoDeAniosCsv = range(2010, 2030);

        $datosParaCsv = $indicadoresCollection->map(function ($indicador) use ($rangoDeAniosCsv) {
            $fila = [];
            $fila['ID Indicador'] = $indicador->id;
            $fila['Nombre Indicador'] = $indicador->nombre;
            $fila['Programa Derivado'] = $indicador->programa_derivado;
            $fila['Programa'] = $indicador->programa;
            $fila['Temática'] = $indicador->tematica;
            $fila['Linea Base (Año)'] = $indicador->linea_base;
            $fila['Linea Base (Dato)'] = $indicador->dato_linea_base;
            $fila['Unidad de Medida'] = $indicador->unidad_medida;
            $fila['Meta 2030'] = $indicador->meta_2024;
            $fila['Fuente'] = $indicador->fuente;
            $fila['Enlace'] = $indicador->liga;
            $fila['Descripción'] = $indicador->descripcion;
            $fila['Periodicidad'] = $indicador->periodicidad;
            $fila['Cobertura'] = $indicador->cobertura;
            $fila['Tendencia'] = $indicador->tendencia;
            $fila['Resultados Generales'] = $indicador->resultados;
            $fila['Fórmula'] = $indicador->formula;
            $fila['Fecha Actualización Indicador'] = $indicador->fecha_actualizacion;
            $fila['Institución'] = $indicador->institucion ? $indicador->institucion->nombre : 'N/A';
            $fila['ODS'] = $indicador->ods->pluck('nombre')->implode('; ');

            foreach ($rangoDeAniosCsv as $year) {
                $datoAnual = $indicador->datosAnuales->firstWhere('anio', $year);
                $fila["Dato {$year}"] = $datoAnual ? $datoAnual->valor_dato : '';
            }
            return $fila;
        });

        if ($datosParaCsv->isEmpty()) {
            Log::warning('No hay datos para generar el CSV después del mapeo.');
            return redirect()->back()->with('error', 'No hay datos disponibles para exportar en formato CSV con los filtros seleccionados.');
        }

        $nombreArchivoCsv = "indicadores_ped_{$nombreArchivoBase}.csv";
        $columnas = array_keys($datosParaCsv->first());

        $callback = function () use ($datosParaCsv, $columnas) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $columnas);

            foreach ($datosParaCsv as $fila) {
                fputcsv($file, $fila);
            }
            fclose($file);
        };

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$nombreArchivoCsv}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        Log::info("Enviando archivo CSV: {$nombreArchivoCsv}");
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Devuelve una respuesta JSON con indicadores filtrados.
     * @param  Request $request
     * @return JsonResponse
     */
    public function datosAbiertosPedJson(Request $request)
    {
        Log::debug('IndicadorController@datosAbiertosPedJson: Iniciado.', $request->all());
        $parametro = $request->input('parametro');

        $indicadoresQuery = Indicador::select(
            'id',
            'nombre',
            'programa_derivado',
            'programa',
            'tematica',
            'linea_base',
            'dato_linea_base',
            'unidad_medida',
            'meta_2024',
            'fuente',
            'liga',
            'descripcion',
            'periodicidad',
            'cobertura',
            'tendencia',
            'id_usuario',
            'id_institucion',
            'resultados',
            'formula',
            'fecha_actualizacion'
        )->with([
            'datosAnuales:id,id_indicador,anio,valor_dato,resultados,observaciones,evidencia,fecha_actualizacion',
            'ods:id,nombre',
            'institucion:id,nombre'
        ]);

        switch ($parametro) {
            case 'total-indicadores':
                Log::debug("Caso 'total-indicadores-ped'");
                break;
            case 'indicadores-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo');
                Log::debug("IndicadorController@datosAbiertosPedJSON: Aplicado filtro where programa_derivado = 'Plan Estatal de Desarrollo'");
                break;
            case 'indicadores-pd-ped':
                $indicadoresQuery->whereIn('programa_derivado', [
                    'Programa Sectorial',
                    'Programa Especial',
                    'Programa Institucional',
                    'Programa Regional'
                ]);
                Log::debug("IndicadorController@datosAbiertosPedJSON: Aplicado filtro whereIn programa_derivado.");
                break;
            case 'indicadores-eje1-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Humanismo con Bienestar');
                Log::debug("IndicadorController@datosAbiertosPedJSON: Aplicados filtros para Eje 1.");
                break;
            case 'indicadores-eje2-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Prosperidad y Estabilidad Económica');
                Log::debug("IndicadorController@datosAbiertosPedJSON: Aplicados filtros para Eje 2.");
                break;
            case 'indicadores-eje3-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Estado de Derecho, Seguridad y Justicia');
                Log::debug("IndicadorController@datosAbiertosPedJSON: Aplicados filtros para Eje 3.");
                break;
            case 'indicadores-eje4-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Desarrollo Urbano y Crecimiento Sostenible');
                Log::debug("IndicadorController@datosAbiertosPedJSON: Aplicados filtros para Eje 4.");
                break;
            case 'indicadores-eje5-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Gobierno Transformador y de Resultados');
                Log::debug("IndicadorController@datosAbiertosPedJSON: Aplicados filtros para Eje 5.");
                break;
            case 'indicadores-eje6-ped':
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Por Amor a Puebla');
                Log::debug("IndicadorController@datosAbiertosPedJSON: Aplicados filtros para Eje 6.");
                break;
            default:
                Log::error('IndicadorController@datosAbiertosPedJson: Parámetro no válido recibido.', ['parametro' => $parametro]);
                return response()->json(['success' => false, 'message' => 'Parámetro de filtro no válido.'], 400);
        }

        $indicadoresCollection = $indicadoresQuery->get();
        Log::debug('IndicadorController@datosAbiertosPedJson: Indicadores obtenidos de BD: ' . $indicadoresCollection->count());

        if ($indicadoresCollection->isEmpty()) {
            Log::warning('IndicadorController@datosAbiertosPedJson: No hay datos para generar el JSON con los filtros seleccionados.');
            return response()->json(['success' => true, 'message' => 'No hay datos disponibles para los filtros seleccionados.', 'data' => []], 200);
        }

        $rangoDeAniosJson = range(2015, 2030);

        $datosParaJson = $indicadoresCollection->map(function ($indicador) use ($rangoDeAniosJson) {
            $datosIndicador = $indicador->toArray();

            $datosAnualesFormatoAncho = [];
            foreach ($rangoDeAniosJson as $year) {
                $datoAnual = $indicador->datosAnuales->firstWhere('anio', $year);
                $datosAnualesFormatoAncho["dato_{$year}"] = $datoAnual ? $datoAnual->valor_dato : null;
            }
            $datosIndicador['datos_anuales_historico'] = $datosAnualesFormatoAncho;
            unset($datosIndicador['datos_anuales']);

            if (isset($datosIndicador['institucion']) && is_array($datosIndicador['institucion'])) {
                $datosIndicador['nombre_institucion'] = $datosIndicador['institucion']['nombre'] ?? 'N/A';
                unset($datosIndicador['institucion']);
            }
            if (isset($datosIndicador['ods']) && is_array($datosIndicador['ods'])) {
                $datosIndicador['nombres_ods'] = collect($datosIndicador['ods'])->pluck('nombre')->implode(', ');
                unset($datosIndicador['ods']);
            }

            return $datosIndicador;
        });

        Log::info("IndicadorController@datosAbiertosPedJson: Enviando respuesta JSON con {$datosParaJson->count()} registros.");
        return response()->json([
            'success' => true,
            'parametro_solicitado' => $parametro,
            'total_registros' => $datosParaJson->count(),
            'data' => $datosParaJson
        ], 200);
    }

    /**
     * Valida la estructura y contenido básico de un archivo Excel sin guardarlo en la BD.
     * @param  Request $request
     * @return JsonResponse
     */
    public function validateFile(Request $request)
    {
        Log::debug('IndicadorController@validateFile: Iniciado.');

        Log::debug('IndicadorController@validateFile: Antes de validar el request del archivo.');
        $validatedRequest = $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ], [
            'file.required' => 'Por favor, selecciona un archivo.',
            'file.mimes' => 'El archivo debe ser de tipo Excel (.xlsx, .xls, .csv).',
            'file.max' => 'El archivo no debe superar los 2MB.',
        ]);
        Log::info('IndicadorController@validateFile: Validación del request de archivo exitosa.');

        try {
            $file = $validatedRequest['file'];
            Log::debug('IndicadorController@validateFile: Archivo obtenido del request.', ['original_name' => $file->getClientOriginalName(), 'size' => $file->getSize()]);

            $spreadsheet = IOFactory::load($file);
            Log::debug('IndicadorController@validateFile: Spreadsheet cargado con IOFactory.');
            $sheet = $spreadsheet->getActiveSheet();
            Log::debug('IndicadorController@validateFile: Hoja activa obtenida.');

            $allRowsRaw = $sheet->toArray();
            Log::debug('IndicadorController@validateFile: Todas las filas leídas (raw): ' . count($allRowsRaw) . ' filas.');

            $rows = array_filter($allRowsRaw, function ($row) {
                return count(array_filter($row, function ($cell) {
                    return trim((string) $cell) !== '';
                })) > 0;
            });
            Log::info('IndicadorController@validateFile: Filas filtradas (no vacías): ' . count($rows) . ' filas.');

            if (empty($rows)) {
                Log::warning('IndicadorController@validateFile: El archivo está vacío después de filtrar filas no vacías.');
                return response()->json(['error' => 'El archivo está vacío o no contiene filas con datos.'], 422);
            }

            $headers = array_shift($rows);
            Log::debug('IndicadorController@validateFile: Encabezados extraídos.', ['headers' => $headers]);

            if (empty($rows)) {
                Log::warning('IndicadorController@validateFile: El archivo no contiene datos para procesar después de quitar encabezados.');
                return response()->json(['error' => 'El archivo no contiene datos para procesar (solo encabezados).'], 422);
            }
            Log::debug('IndicadorController@validateFile: Número de filas de datos (sin encabezados): ' . count($rows) . ' filas.');

            Log::debug('IndicadorController@validateFile: Iniciando validación de campos obligatorios por fila.');
            foreach ($rows as $index => $row) {
                $nombre = $row[1] ?? null;
                $plan = $row[2] ?? null;
                $tipoPrograma = $row[3] ?? null;
                $nombrePrograma = $row[4] ?? null;
                $eje = $row[5] ?? null;

                if (
                    empty(trim((string)$nombre)) ||
                    empty(trim((string)$plan)) ||
                    empty(trim((string)$tipoPrograma)) ||
                    empty(trim((string)$nombrePrograma)) ||
                    empty(trim((string)$eje))
                ) {
                    Log::warning('IndicadorController@validateFile: Error de validación en fila.', [
                        'numero_fila_excel' => $index + 2,
                        'contenido_fila' => $row,
                        'error' => 'Campos obligatorios vacíos (Nombre, Plan, Tipo, Programa o Eje).'
                    ]);
                    return response()->json([
                        'error' => "Error en la fila " . ($index + 2) . ": Faltan datos obligatorios (Nombre, Plan, Tipo de Programa, Nombre de Programa o Eje)."
                    ], 422);
                }
            }
            Log::info('IndicadorController@validateFile: Validación de campos obligatorios por fila completada exitosamente.');

            $path = $file->storeAs('temp_imports', 'import_' . uniqid() . '.' . $file->getClientOriginalExtension());
            session(['importFilePath' => $path]);
            Log::info('IndicadorController@validateFile: Archivo guardado temporalmente en: ' . $path);

            Log::info('IndicadorController@validateFile: Validación de archivo completada. Enviando respuesta exitosa.');
            return response()->json([
                'success' => true,
                'message' => 'El archivo ha sido validado correctamente bajo la nueva estructura (con Usuario e Institución). ' . count($rows) . ' filas listas para procesar. ¿Desea continuar?'
            ]);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            Log::error('IndicadorController@validateFile: Error de PhpSpreadsheet Reader.', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Error al leer el archivo Excel: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('IndicadorController@validateFile: Excepción general.', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Error inesperado: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Procesa las filas (previamente validadas y guardadas en sesión) para importarlas a la BD.
     * @param  Request $request
     * @return JsonResponse
     */
    public function confirmImport(Request $request)
    {
        Log::info('IndicadorController@confirmImport: Proceso de importación iniciado.');
        $filePath = session('importFilePath');

        if (!$filePath || !Storage::exists($filePath)) {
            Log::warning('IndicadorController@confirmImport: No se encontró el archivo temporal en sesión o disco.');
            return response()->json(['error' => 'No se encontró el archivo para importar. Valide el archivo nuevamente.'], 422);
        }

        $erroresEnImportacion = [];
        $indicadoresImportadosExitosamente = 0;
        $filasProcesadas = 0;

        try {
            $fullPath = storage_path('app/' . $filePath);
            $spreadsheet = IOFactory::load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();

            $allRowsRaw = $sheet->toArray();
            $rows = array_filter($allRowsRaw, function ($row) {
                return count(array_filter($row, function ($cell) {
                    return trim((string) $cell) !== '';
                })) > 0;
            });
            array_shift($rows);
        } catch (\Exception $e) {
            Log::error("IndicadorController@confirmImport: Error al releer el archivo: " . $e->getMessage());
            return response()->json(['error' => 'Error al procesar el archivo temporal.'], 500);
        }

        $columnaInicialDatosAnualesExcel = 22;
        $anioInicialDatosAnuales = 2015;
        $anioFinalDatosAnuales = 2030;
        $mapeoColumnasAnios = [];

        for ($i = 0; $i <= ($anioFinalDatosAnuales - $anioInicialDatosAnuales); $i++) {
            $indiceColumna = $columnaInicialDatosAnualesExcel + $i;
            $anioActual = $anioInicialDatosAnuales + $i;
            $mapeoColumnasAnios[$indiceColumna] = $anioActual;
        }

        foreach ($rows as $index => $row) {
            $filasProcesadas++;

            DB::beginTransaction();
            try {
                $nombreIndicador     = trim($row[1] ?? '');
                $nombrePlan          = trim($row[2] ?? '');
                $tipoProgramaRaw     = trim($row[3] ?? '');
                $nombreProgramaDeriv = trim($row[4] ?? '');
                $ejePrograma         = trim($row[5] ?? '');

                $idUsuario           = isset($row[6]) && trim((string)$row[6]) !== '' ? trim((string)$row[6]) : null;
                $idInstitucion       = isset($row[7]) && trim((string)$row[7]) !== '' ? trim((string)$row[7]) : null;

                $planObj = CatPlanEstatalDesarrollo::where('nombre', $nombrePlan)->first();
                if (!$planObj) {
                    throw new \Exception("El Plan Estatal '{$nombrePlan}' no existe en el catálogo.");
                }

                $indicadorableId = null;
                $indicadorableType = null;
                $programaDerivadoFinal = $nombreProgramaDeriv;

                $modelClass = null;
                if (stripos($tipoProgramaRaw, 'Sectorial') !== false) {
                    $modelClass = CatProgramaDerivadoSectorial::class;
                } elseif (stripos($tipoProgramaRaw, 'Especial') !== false) {
                    $modelClass = CatProgramaDerivadoEspecial::class;
                } elseif (stripos($tipoProgramaRaw, 'Institucional') !== false) {
                    $modelClass = CatProgramaDerivadoInstitucional::class;
                } elseif (stripos($tipoProgramaRaw, 'Regional') !== false) {
                    $modelClass = CatProgramaDerivadoRegional::class;
                } else {
                    throw new \Exception("Tipo de programa '{$tipoProgramaRaw}' no reconocido.");
                }

                if ($modelClass) {
                    $programaObj = $modelClass::where('nombre', $nombreProgramaDeriv)->first();
                    if (!$programaObj) {
                        throw new \Exception("El programa '{$nombreProgramaDeriv}' no encontrado.");
                    }
                    if ($modelClass !== CatProgramaDerivadoInstitucional::class) {
                        $indicadorableId = $programaObj->id;
                        $indicadorableType = $modelClass;
                    }
                    $programaDerivadoFinal = $programaObj->nombre;
                }

                if ($idUsuario && !User::find($idUsuario)) {
                    throw new \Exception("El Usuario con ID '{$idUsuario}' no existe.");
                }
                if ($idInstitucion && !Institucion::find($idInstitucion)) {
                    throw new \Exception("La Institución con ID '{$idInstitucion}' no existe.");
                }

                $datosIndicador = [
                    'nombre'             => $nombreIndicador,
                    'programa_derivado'  => $programaDerivadoFinal,
                    'programa'           => $ejePrograma,
                    'plan_id'            => $planObj->id,
                    'indicadorable_id'   => $indicadorableId,
                    'indicadorable_type' => $indicadorableType,
                    'id_usuario'         => $idUsuario,
                    'id_institucion'     => $idInstitucion,

                    'tematica'           => $row[8] ?? null,
                    'linea_base'         => $row[9] ?? null,
                    'dato_linea_base'    => $row[10] ?? null,
                    'unidad_medida'      => $row[11] ?? null,
                    'meta_2024'          => $row[12] ?? null,
                    'fuente'             => $row[13] ?? null,
                    'liga'               => $row[14] ?? null,
                    'descripcion'        => $row[15] ?? null,
                    'periodicidad'       => $row[16] ?? null,
                    'cobertura'          => $row[17] ?? null,
                    'tendencia'          => $row[18] ?? null,
                    'formula'            => $row[19] ?? null,
                    'fecha_actualizacion' => $row[21] ?? null,
                    'indicador_validado' => false,
                ];

                $validator = Validator::make($datosIndicador, [
                    'nombre' => 'required|string|max:255',
                    'programa_derivado' => 'required|string|max:255',
                    'programa' => 'required|string|max:255',
                    'tematica' => 'required|string|max:255',
                    'linea_base' => 'required|integer|digits:4',
                    'dato_linea_base' => 'required',
                    'unidad_medida' => 'required|string|max:255',
                    'meta_2024' => 'required',
                    'periodicidad' => 'required|string|max:255',
                    'cobertura' => 'required|string|max:255',
                    'tendencia' => 'required|string|max:255',
                    'formula' => 'required|string',
                ]);

                if ($validator->fails()) {
                    throw new \Exception("Validación fallida: " . $validator->errors()->first());
                }

                $idIndicadorExcel = $row[0] ?? null;
                $indicador = null;

                if (!empty($idIndicadorExcel)) {
                    $indicador = Indicador::find($idIndicadorExcel);
                    if ($indicador) {
                        $indicador->update($datosIndicador);
                    } else {
                        $indicador = Indicador::create($datosIndicador);
                    }
                } else {
                    $indicador = Indicador::updateOrCreate(
                        [
                            'nombre' => $datosIndicador['nombre'],
                            'programa_derivado' => $datosIndicador['programa_derivado']
                        ],
                        $datosIndicador
                    );
                }

                if ($modelClass === CatProgramaDerivadoInstitucional::class) {
                    $indicador->programasInstitucionales()->sync([$programaObj->id]);
                } else {
                    $indicador->programasInstitucionales()->sync([]);
                }

                foreach ($mapeoColumnasAnios as $indiceColumnaExcel => $anio) {
                    if (!array_key_exists($indiceColumnaExcel, $row)) continue;
                    $valorDatoAnual = $row[$indiceColumnaExcel];

                    if ($valorDatoAnual !== null && trim((string)$valorDatoAnual) !== '') {
                        $indicador->datosAnuales()->updateOrCreate(
                            ['anio' => $anio],
                            ['valor_dato' => $valorDatoAnual, 'modificado' => false]
                        );
                    }
                }

                $odsString = $row[20] ?? null;
                if (!empty($odsString)) {
                    $odsIds = array_filter(array_map('trim', explode(',', $odsString)));
                    $indicador->ods()->sync($odsIds);
                }

                DB::commit();
                $indicadoresImportadosExitosamente++;
            } catch (\Exception $e) {
                DB::rollBack();
                $mensajeError = "Fila: " . ($index + 2) . " Error: " . $e->getMessage();
                Log::error("IndicadorController@confirmImport: " . $mensajeError);
                $erroresEnImportacion[] = $mensajeError;
            }
        }

        try {
            Storage::delete($filePath);
        } catch (\Exception $e) {
            Log::warning("No se pudo eliminar archivo temporal: $filePath");
        }
        session()->forget('importFilePath');

        if (!empty($erroresEnImportacion)) {
            $htmlErrores = "<ul class='text-start'><li>" . implode("</li><li>", array_map('htmlspecialchars', $erroresEnImportacion)) . "</li></ul>";
            return response()->json([
                'success' => $indicadoresImportadosExitosamente > 0,
                'message' => "Proceso finalizado. Importados: {$indicadoresImportadosExitosamente}. Errores: {$htmlErrores}",
                'errors_list' => $erroresEnImportacion
            ], 207);
        }

        return response()->json([
            'success' => true,
            'message' => "Importación completada exitosamente. {$indicadoresImportadosExitosamente} indicadores procesados."
        ]);
    }

    /**
     * Descarga el catálogo de usuarios simplificado (ID, Nombre, Correo).
     */
    public function downloadUsuarios()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Nombre');
        $sheet->setCellValue('C1', 'Correo');

        $users = User::select('id', 'name', 'email')->get();
        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue('A' . $row, $user->id);
            $sheet->setCellValue('B' . $row, $user->name);
            $sheet->setCellValue('C' . $row, $user->email);
            $row++;
        }

        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="Catalogo_Usuarios.xlsx"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    /**
     * Descarga el catálogo de instituciones simplificado (ID, Nombre).
     */
    public function downloadInstituciones()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Nombre Institución');

        $instituciones = Institucion::select('id', 'nombre')->get();
        $row = 2;
        foreach ($instituciones as $inst) {
            $sheet->setCellValue('A' . $row, $inst->id);
            $sheet->setCellValue('B' . $row, $inst->nombre);
            $row++;
        }

        foreach (range('A', 'B') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="Catalogo_Instituciones.xlsx"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    /**
     * Genera la plantilla de Excel dinámicamente.
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'ID (Opcional)',
            'Nombre Indicador',
            'Plan Estatal (Exacto)',
            'Tipo Programa (Sectorial, Especial...)',
            'Nombre Programa Derivado (Exacto)',
            'Eje / Programa',
            'ID Usuario Responsable',
            'ID Institución Responsable',
            'Temática',
            'Línea Base (Año)',
            'Dato Línea Base',
            'Unidad de Medida',
            'Meta 2030',
            'Fuente',
            'Liga',
            'Descripción',
            'Periodicidad',
            'Cobertura',
            'Tendencia',
            'Fórmula',
            'ODS (Sep. comas)',
            'Fecha Actualización'
        ];

        for ($year = 2015; $year <= 2030; $year++) {
            $headers[] = $year;
        }

        $sheet->fromArray([$headers], NULL, 'A1');

        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE0E0E0'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $lastCol = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray($headerStyle);

        // AutoSize Columns
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add Example Row (Optional hint)
        $sheet->setCellValue('A2', '');
        $sheet->setCellValue('B2', 'Ejemplo de Indicador');
        $sheet->setCellValue('C2', 'Plan Estatal 2022-2027');
        $sheet->setCellValue('D2', 'Sectorial');
        $sheet->setCellValue('E2', 'Programa Sectorial de Salud');

        $writer = new Xlsx($spreadsheet);

        $response = response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="Plantilla_Carga_Indicadores.xlsx"',
                'Cache-Control' => 'max-age=0',
            ]
        );

        return $response;
    }

    /**
     * Obtiene los programas derivados filtrados por plan y tipo.
     */
    public function getProgramasDerivados(Request $request)
    {
        $planId = $request->query('plan_id');
        $tipo = $request->query('tipo');

        if (!$planId || !$tipo) {
            return response()->json([]);
        }

        $programas = [];

        switch ($tipo) {
            case 'Programa Especial':
                $programas = CatProgramaDerivadoEspecial::where('plan_estatal', $planId)->get(['id', 'nombre']);
                break;
            case 'Programa Institucional':
                $programas = CatProgramaDerivadoInstitucional::where('plan_estatal', $planId)->get(['id', 'nombre']);
                break;
            case 'Programa Regional':
                $programas = CatProgramaDerivadoRegional::where('plan_estatal', $planId)->get(['id', 'nombre']);
                break;
            case 'Programa Sectorial':
                $programas = CatProgramaDerivadoSectorial::where('plan_estatal', $planId)->get(['id', 'nombre']);
                break;
            case 'Eje':
                $programas = CatEje::where('plan_id', $planId)->get(['id', 'nombre']);
                break;
        }

        return response()->json($programas);
    }

    private function getProgramaModelClass($type)
    {
        switch ($type) {
            case 'Programa Especial':
                return CatProgramaDerivadoEspecial::class;
            case 'Programa Institucional':
                return CatProgramaDerivadoInstitucional::class;
            case 'Programa Regional':
                return CatProgramaDerivadoRegional::class;
            case 'Programa Sectorial':
                return CatProgramaDerivadoSectorial::class;
            default:
                return null;
        }
    }
}

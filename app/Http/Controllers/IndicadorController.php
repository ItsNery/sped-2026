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
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Facades\Log; // Para registrar errores (opcional pero recomendado)
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Services\AuditLogger;
use App\Services\ActivePlanResolver;

class IndicadorController extends Controller
{
    public function __construct(
        private AuditLogger $auditLogger,
        private ActivePlanResolver $activePlan
    )
    {
    /**
     * Aplica el middleware de permisos a las acciones del controlador.
     */
        $this->middleware('permission:ver-indicador|crear-indicador|editar-indicador|borrar-indicador', ['only' => ['index']]);
        $this->middleware('permission:crear-indicador', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-indicador', ['only' => ['edit', 'update']]);
        $this->middleware('permission:borrar-indicador', ['only' => ['destroy']]);
        $this->middleware('permission:editar-indicador-anual', ['only' => ['updateAnualData']]);
        $this->middleware('permission:validar-indicador', ['only' => ['toggleValidacion', 'toggleValidacionAnual']]);
        $this->middleware('permission:subida-masiva-indicador', ['only' => ['confirmImport']]);
    }

    /**
     * Muestra una lista de indicadores, adaptada al rol del usuario.
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();
        $activePlanId = $this->activePlan->id();
        $tiposPrograma = Indicador::select('programa_derivado')
            ->whereNotNull('programa_derivado')
            ->where('programa_derivado', '!=', '')
            ->distinct()
            ->orderBy('programa_derivado')
            ->pluck('programa_derivado')
            ->toArray();

        if ($user->isAdministrator()) {
            $indicadores = Indicador::forPlan($activePlanId)->with('datosAnuales')->get();
            $instituciones = Institucion::whereHas('indicadores', fn ($query) => $query->forPlan($activePlanId))
                ->where('id', '!=', 1)
                ->get();
            return view('panel-indicadores.index', compact('indicadores', 'instituciones', 'tiposPrograma'));
        }

        if ($user->hasRole('Enlace')) {
            $institucionesAsignadas = $user->instituciones()->pluck('institucion_id');
            $indicadores = Indicador::forPlan($activePlanId)
                ->whereIn('id_institucion', $institucionesAsignadas)
                ->orderBy('id')
                ->paginate(1000);
            $instituciones = $user->instituciones;

            return view('panel-indicadores.index', compact('indicadores', 'tiposPrograma', 'instituciones'));
        }

        if ($user->hasRole(['Enlace dependencia', 'Visualizador'])) {
            $indicadores = Indicador::forPlan($activePlanId)
                ->where('id_institucion', $user->id_institucion)
                ->where('id', '!=', 608)
                ->orderBy('id')
                ->get();

            $todosValidados = $indicadores->isEmpty() ? false : ($indicadores->where('indicador_validado', 1)->count() === $indicadores->count());

            $mostrarBotonFinalizar = $todosValidados && $user->finalizado != 1;
            $mostrarBotonGenerarReporte = $todosValidados && $user->finalizado == 1 && $user->reporte_generado != 1;

            return view('panel-indicadores.index', compact('indicadores', 'mostrarBotonFinalizar', 'user', 'mostrarBotonGenerarReporte'));
        }

        $indicadores = Indicador::forPlan($activePlanId)
            ->where('id_usuario', $user->id)
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

        $usuarios = User::role('Enlace dependencia')->orderBy('id')->get();
        $instituciones = Institucion::where('id', '!=', 1)->get();

        $planes = collect([$this->activePlan->get()]);
        $metaAnioSugerido = $this->activePlan->id() === 3 ? 2030 : 2024;
        $programasInstitucionales = CatProgramaDerivadoInstitucional::all();

        return view('panel-indicadores.crear', compact('pds', 'instituciones', 'usuarios', 'odses', 'periodicidades', 'coberturas', 'tendencias', 'planes', 'programasInstitucionales', 'metaAnioSugerido'));
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
            'meta_anio',
            'meta',
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
            'meta_anio' => 'required|integer|min:1900|max:2100',
            'meta' => 'required|string|max:255',
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
            'meta_anio.required' => 'El año de la meta es obligatorio.',
            'meta_anio.integer' => 'El año de la meta debe ser un número entero.',
            'meta.required' => 'La meta es obligatoria.',
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
                'meta_anio' => $validatedData['meta_anio'],
                'meta' => $validatedData['meta'],
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
    public function show(Request $request, $id)
    {
        /** @var User */
        $user = auth()->user();
        $planId = $this->activePlan->id();

        if (($user->isAdministrator() || $user->can('ver-panel-avance-general')) && $request->filled('plan_id')) {
            $planId = CatPlanEstatalDesarrollo::find($request->integer('plan_id'))?->id ?? $planId;
        }

        $query = Indicador::query()
            ->with(['datosAnuales', 'ods', 'programasInstitucionales']);

        // Los administradores pueden abrir indicadores históricos por ID cuando
        // el enlace no incluye un plan explícito; los demás usuarios permanecen
        // limitados al PED activo.
        if (!$user->isAdministrator() || $request->filled('plan_id')) {
            $query->forPlan($planId);
        }

        $indicador = $query->findOrFail($id);

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

        if ($user->isAdministrator()) {
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

        $indicador = Indicador::forPlan($this->activePlan->id())
            ->with(['datosAnuales', 'programasInstitucionales'])
            ->findOrFail($id);

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
        $planes = collect([$this->activePlan->get()]);
        $programasInstitucionales = CatProgramaDerivadoInstitucional::where('plan_estatal', $this->activePlan->id())->get();
        $usuarios = User::role('Enlace dependencia')->orderBy('id')->get();
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
            'meta_anio' => 'required|integer|min:1900|max:2100',
            'meta' => 'required|string|max:255',
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

            'meta_anio.required' => 'El año de la meta es obligatorio.',
            'meta_anio.integer' => 'El año de la meta debe ser un número entero.',
            'meta.required' => 'La meta es obligatoria.',
            'meta.string' => 'La meta debe ser texto o número.',
            'meta.max' => 'La meta no debe exceder los 255 caracteres.',

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
    public function toggleValidacion(Request $request, $id)
    {
        $indicador = Indicador::forPlan($this->activePlan->id())->findOrFail($id);

        $valorAnterior = (bool) $indicador->getRawOriginal('indicador_validado');
        $estadoValidacion = $request->has('estado')
            ? $request->boolean('estado')
            : !$valorAnterior;

        if ($valorAnterior === $estadoValidacion) {
            return redirect()->back()->with('status', 'La ficha ya tenía ese estado de validación.');
        }
        DB::transaction(function () use ($indicador, $estadoValidacion, $valorAnterior) {
            $actualizados = DB::table($indicador->getTable())
                ->where('id', $indicador->id)
                ->update([
                    'indicador_validado' => $estadoValidacion,
                    'updated_at' => now(),
                ]);

            abort_unless($actualizados === 1, 500, 'No se pudo guardar el estado de validación de la ficha.');
            $this->auditLogger->recordUpdate(
                $indicador,
                'indicador_validado',
                $valorAnterior,
                $estadoValidacion,
                $estadoValidacion ? 'validado' : 'invalidado',
                'Cambio de validación de ficha'
            );
        });

        return redirect()->back()->with('status', 'Estado de validación de la ficha actualizado. Los datos anuales conservan su validación independiente.');
    }

    /**
     * Cambia el estado de validación de un dato anual sin modificar los demás años.
     *
     * La carga histórica conserva su estado actual; esta acción aplica a las
     * validaciones independientes de nuevas cargas y actualizaciones futuras.
     */
    public function toggleValidacionAnual(Request $request, $id, $year)
    {
        $indicador = Indicador::forPlan($this->activePlan->id())->findOrFail($id);
        $datoAnual = $indicador->datosAnuales()->where('anio', $year)->firstOrFail();
        $valorAnterior = (bool) $datoAnual->getRawOriginal('validado');
        $estadoValidacion = $request->has('estado')
            ? $request->boolean('estado')
            : !$valorAnterior;

        if ($valorAnterior === $estadoValidacion) {
            return redirect()->back()->with('status', "El dato anual {$year} ya tenía ese estado de validación.");
        }
        DB::transaction(function () use ($datoAnual, $estadoValidacion, $valorAnterior, $year) {
            $actualizados = DB::table($datoAnual->getTable())
                ->where('id', $datoAnual->id)
                ->update([
                    'validado' => $estadoValidacion,
                    'modificado' => !$estadoValidacion,
                    'updated_at' => now(),
                ]);

            abort_unless($actualizados === 1, 500, "No se pudo guardar la validación del dato anual {$year}.");
            $this->auditLogger->recordUpdate(
                $datoAnual,
                'validado',
                $valorAnterior,
                $estadoValidacion,
                $estadoValidacion ? 'validado' : 'invalidado',
                "Cambio de validación del dato anual {$year}"
            );
        });

        return redirect()->back()->with(
            'status',
            $estadoValidacion
                ? "El dato anual {$year} fue validado."
                : "El dato anual {$year} quedó pendiente de validación."
        );
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


        $indicador = Indicador::forPlan($this->activePlan->id())->findOrFail($id);

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
        $indicadoresQuery = Indicador::forPlan($this->activePlan->id())->select(
            'id',
            'nombre',
            'programa_derivado',
            'programa',
            'tematica',
            'linea_base',
            'dato_linea_base',
            'unidad_medida',
            'meta_anio',
            'meta',
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
                $this->applyDerivedProgramFilter($indicadoresQuery);
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
                'meta_anio',
                'meta',
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
                $sheet->setCellValue("I{$fila}", $indicadorDataRow['meta']);
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

        $indicadoresQuery = Indicador::forPlan($this->activePlan->id())->select(
            'id',
            'nombre',
            'programa_derivado',
            'programa',
            'tematica',
            'linea_base',
            'dato_linea_base',
            'unidad_medida',
            'meta_anio',
            'meta',
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
                $this->applyDerivedProgramFilter($indicadoresQuery);
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
            $fila['Año Meta'] = $indicador->meta_anio;
            $fila['Meta'] = $indicador->meta;
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

        $indicadoresQuery = Indicador::forPlan($this->activePlan->id())->select(
            'id',
            'nombre',
            'programa_derivado',
            'programa',
            'tematica',
            'linea_base',
            'dato_linea_base',
            'unidad_medida',
            'meta_anio',
            'meta',
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
                $this->applyDerivedProgramFilter($indicadoresQuery);
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
    public function import(Request $request): JsonResponse
    {
        $validationResponse = $this->validateFile($request);

        if ($validationResponse->getStatusCode() !== 200) {
            return $validationResponse;
        }

        return $this->confirmImport($request);
    }

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

            $rows = array_values(array_filter($allRowsRaw, function ($row) {
                return count(array_filter($row, function ($cell) {
                    return trim((string) $cell) !== '';
                })) > 0;
            }));
            Log::info('IndicadorController@validateFile: Filas filtradas (no vacías): ' . count($rows) . ' filas.');

            if (empty($rows)) {
                Log::warning('IndicadorController@validateFile: El archivo está vacío después de filtrar filas no vacías.');
                return response()->json(['error' => 'El archivo está vacío o no contiene filas con datos.'], 422);
            }

            $headers = array_shift($rows);
            Log::debug('IndicadorController@validateFile: Encabezados extraídos.', ['headers' => $headers]);

            $headerError = $this->validateImportHeaders($headers);
            if ($headerError) {
                return response()->json(['error' => $headerError], 422);
            }

            if (count($rows) > 1000) {
                return response()->json(['error' => 'El archivo no puede contener más de 1000 indicadores por carga.'], 422);
            }

            if (empty($rows)) {
                Log::warning('IndicadorController@validateFile: El archivo no contiene datos para procesar después de quitar encabezados.');
                return response()->json(['error' => 'El archivo no contiene datos para procesar (solo encabezados).'], 422);
            }
            Log::debug('IndicadorController@validateFile: Número de filas de datos (sin encabezados): ' . count($rows) . ' filas.');

            Log::debug('IndicadorController@validateFile: Iniciando validación de campos obligatorios por fila.');
            foreach ($rows as $index => $row) {
                $rowError = $this->validateImportRowShape($row);
                if ($rowError) {
                    Log::warning('IndicadorController@validateFile: Error de validación en fila.', [
                        'numero_fila_excel' => $index + 2,
                        'contenido_fila' => $row,
                        'error' => $rowError,
                    ]);
                    return response()->json([
                        'error' => "Error en la fila " . ($index + 2) . ": {$rowError}"
                    ], 422);
                }
            }
            Log::info('IndicadorController@validateFile: Validación de campos obligatorios por fila completada exitosamente.');

            if ($oldPath = session('importFilePath')) {
                Storage::delete($oldPath);
            }

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
            $rows = array_values(array_filter($allRowsRaw, function ($row) {
                return count(array_filter($row, function ($cell) {
                    return trim((string) $cell) !== '';
                })) > 0;
            }));
            $headers = $rows[0] ?? [];
            $headerError = $this->validateImportHeaders($headers);
            if ($headerError) {
                throw new \RuntimeException($headerError);
            }
            array_shift($rows);
            if (!$rows) {
                throw new \RuntimeException('El archivo no contiene datos para procesar.');
            }
        } catch (\Throwable $e) {
            Log::error("IndicadorController@confirmImport: Error al releer el archivo: " . $e->getMessage());
            $this->forgetImportFile($filePath);
            return response()->json(['error' => $e->getMessage()], 422);
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
                $nombreIndicador     = $this->importCell($row, 1) ?? '';
                $nombrePlan          = $this->importCell($row, 2) ?? '';
                $tipoProgramaRaw     = $this->importCell($row, 3) ?? '';
                $nombreProgramaDeriv = $this->importCell($row, 4) ?? '';
                $ejePrograma         = $this->importCell($row, 5) ?? '';

                $idUsuario           = $this->importIntegerCell($row, 6, 'Usuario');
                $idInstitucion       = $this->importIntegerCell($row, 7, 'Institución');
                $idIndicadorExcel    = $this->importIntegerCell($row, 0, 'Indicador');

                $planObj = $this->findImportPlan($nombrePlan);
                if (!$planObj) {
                    throw new \Exception("El Plan Estatal '{$nombrePlan}' no existe en el catálogo.");
                }

                $alignment = $this->resolveImportAlignment($tipoProgramaRaw, $nombreProgramaDeriv, $ejePrograma, $planObj);
                $indicador = $this->findImportIndicator($idIndicadorExcel, $nombreIndicador, $planObj->id, $alignment);
                $institutionIdForAuthorization = $idInstitucion ?? $indicador?->id_institucion;
                $this->authorizeImportRow(auth()->user(), $planObj->id, $institutionIdForAuthorization, $indicador);

                if ($idUsuario !== null && !User::whereKey($idUsuario)->exists()) {
                    throw new \Exception("El Usuario con ID '{$idUsuario}' no existe.");
                }
                if ($idInstitucion !== null && !Institucion::whereKey($idInstitucion)->exists()) {
                    throw new \Exception("La Institución con ID '{$idInstitucion}' no existe.");
                }

                $datosIndicador = [
                    'nombre'             => $nombreIndicador,
                    'programa_derivado'  => $alignment['programaDerivado'],
                    'programa'           => $alignment['programa'],
                    'indicadorable_id'   => $alignment['id'],
                    'indicadorable_type' => $alignment['type'],
                    'id_usuario'         => $idUsuario ?? $indicador?->id_usuario,
                    'id_institucion'     => $idInstitucion ?? $indicador?->id_institucion,

                    'tematica'           => $this->importCell($row, 8),
                    'linea_base'         => $this->importCell($row, 9),
                    'dato_linea_base'    => $this->importCell($row, 10),
                    'unidad_medida'      => $this->importCell($row, 11),
                    'meta_anio'          => $this->importMetaYear($planObj),
                    'meta'               => $this->importCell($row, 12),
                    'fuente'             => $this->importCell($row, 13) ?? ($indicador?->fuente ?? ''),
                    'liga'               => $this->normalizeImportUrl($this->importCell($row, 14)),
                    'descripcion'        => $this->importCell($row, 15) ?? ($indicador?->descripcion ?? ''),
                    'periodicidad'       => $this->importCell($row, 16),
                    'cobertura'          => $this->importCell($row, 17),
                    'tendencia'          => $this->importCell($row, 18),
                    'formula'            => $this->importCell($row, 19),
                    'fecha_actualizacion' => $this->normalizeImportDate($this->importCell($row, 21))
                        ?? ($indicador?->fecha_actualizacion ?? date('Y-m-d')),
                    'indicador_validado' => false,
                ];

                $validator = Validator::make($datosIndicador, [
                    'nombre' => 'required|string|max:255',
                    'programa_derivado' => 'required|string|max:255',
                    'programa' => 'required|string|max:255',
                    'tematica' => 'required|string|max:255',
                    'linea_base' => 'required|integer|digits:4',
                    'dato_linea_base' => 'required|string|max:255',
                    'unidad_medida' => 'required|string|max:255',
                    'meta_anio' => 'required|integer|min:1900|max:2100',
                    'meta' => 'required|string|max:255',
                    'periodicidad' => 'required|string|max:255',
                    'cobertura' => 'required|string|max:255',
                    'tendencia' => 'required|string|max:255',
                    'formula' => 'required|string',
                    'liga' => 'nullable|url',
                    'fecha_actualizacion' => 'required|date',
                ]);

                if ($validator->fails()) {
                    throw new \Exception("Validación fallida: " . $validator->errors()->first());
                }

                if (!$indicador) {
                    $datosIndicador['cod_tematica'] = '';
                    $indicador = Indicador::create($datosIndicador);
                } else {
                    $indicador->update($datosIndicador);
                }

                if ($alignment['type'] === CatProgramaDerivadoInstitucional::class) {
                    $indicador->programasInstitucionales()->sync([$alignment['id']]);
                }

                $lineaBase = (int) $datosIndicador['linea_base'];
                $indicador->datosAnuales()->updateOrCreate(
                    ['anio' => $lineaBase],
                    [
                        'valor_dato' => $datosIndicador['dato_linea_base'],
                        'validado' => true,
                        'modificado' => false,
                    ]
                );

                foreach ($mapeoColumnasAnios as $indiceColumnaExcel => $anio) {
                    if (!array_key_exists($indiceColumnaExcel, $row)) continue;
                    if ($anio === $lineaBase) continue;
                    $valorDatoAnual = $this->importCell($row, $indiceColumnaExcel);

                    if ($valorDatoAnual !== null) {
                        if (!is_numeric($valorDatoAnual)) {
                            throw new \Exception("El valor anual de {$anio} debe ser numérico.");
                        }

                        $indicador->datosAnuales()->updateOrCreate(
                            ['anio' => $anio],
                            ['valor_dato' => $valorDatoAnual, 'validado' => false, 'modificado' => false]
                        );
                    }
                }

                $indicador->ods()->sync($this->parseImportOds($this->importCell($row, 20)));

                DB::commit();
                $indicadoresImportadosExitosamente++;
            } catch (\Throwable $e) {
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
                'success' => false,
                'partial' => $indicadoresImportadosExitosamente > 0,
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
            'Tipo Programa (Eje, Sectorial, Especial...)',
            'Nombre Programa Derivado (Exacto)',
            'Eje / Programa',
            'ID Usuario Responsable',
            'ID Institución Responsable',
            'Temática',
            'Línea Base (Año)',
            'Dato Línea Base',
            'Unidad de Medida',
            'Meta',
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

    private function importHeaders(): array
    {
        return [
            'ID (Opcional)',
            'Nombre Indicador',
            'Plan Estatal (Exacto)',
            'Tipo Programa (Eje, Sectorial, Especial...)',
            'Nombre Programa Derivado (Exacto)',
            'Eje / Programa',
            'ID Usuario Responsable',
            'ID Institución Responsable',
            'Temática',
            'Línea Base (Año)',
            'Dato Línea Base',
            'Unidad de Medida',
            'Meta',
            'Fuente',
            'Liga',
            'Descripción',
            'Periodicidad',
            'Cobertura',
            'Tendencia',
            'Fórmula',
            'ODS (Sep. comas)',
            'Fecha Actualización',
        ];
    }

    private function normalizeImportHeader($value): string
    {
        $value = str_replace("\xEF\xBB\xBF", '', trim((string) $value));

        return mb_strtolower((string) preg_replace('/\s+/u', ' ', $value));
    }

    private function validateImportHeaders(array $headers): ?string
    {
        $expectedHeaders = $this->importHeaders();

        if (count($headers) < count($expectedHeaders)) {
            return 'La plantilla no contiene todas las columnas obligatorias. Descarga la plantilla actualizada.';
        }

        foreach ($expectedHeaders as $index => $expectedHeader) {
            $actual = $this->normalizeImportHeader($headers[$index] ?? '');
            $allowed = [$this->normalizeImportHeader($expectedHeader)];

            if ($index === 12) {
                $allowed[] = 'meta 2024';
                $allowed[] = 'meta 2030';
            }

            if (!in_array($actual, $allowed, true)) {
                return "La columna " . chr(65 + $index) . " debe ser '{$expectedHeader}'.";
            }
        }

        for ($index = count($expectedHeaders); $index < count($headers); $index++) {
            $expectedYear = 2015 + ($index - count($expectedHeaders));
            if ($this->normalizeImportHeader($headers[$index] ?? '') !== (string) $expectedYear) {
                return "La columna de datos anuales en la posición " . ($index + 1) . " debe corresponder al año {$expectedYear}.";
            }
        }

        if (count($headers) > 38) {
            return 'La plantilla contiene columnas adicionales no soportadas.';
        }

        return null;
    }

    private function validateImportRowShape(array $row): ?string
    {
        foreach ([1 => 'Nombre del indicador', 2 => 'Plan Estatal', 3 => 'Tipo de programa'] as $index => $label) {
            if ($this->importCell($row, $index) === null) {
                return "Falta {$label}.";
            }
        }

        $tipo = $this->importCell($row, 3) ?? '';
        if ($this->isEjeImportType($tipo)) {
            return $this->importCell($row, 5) === null ? 'Falta el nombre del eje.' : null;
        }

        if ($this->importCell($row, 4) === null) {
            return 'Falta el nombre del programa derivado.';
        }

        if ($this->importCell($row, 5) === null) {
            return 'Falta el campo Eje / Programa.';
        }

        return null;
    }

    private function extractImportRows($spreadsheet): array
    {
        $allRows = $spreadsheet->getActiveSheet()->toArray();
        $rows = array_values(array_filter($allRows, function ($row) {
            return count(array_filter($row, fn ($cell) => trim((string) $cell) !== '')) > 0;
        }));

        if (!$rows) {
            throw new \RuntimeException('El archivo está vacío o no contiene filas con datos.');
        }

        $headers = array_shift($rows);
        $headerError = $this->validateImportHeaders($headers);
        if ($headerError) {
            throw new \RuntimeException($headerError);
        }

        if (!$rows) {
            throw new \RuntimeException('El archivo no contiene datos para procesar (solo encabezados).');
        }

        return [$headers, $rows];
    }

    private function importCell(array $row, int $index): ?string
    {
        if (!array_key_exists($index, $row) || $row[$index] === null) {
            return null;
        }

        $value = trim((string) $row[$index]);

        return $value === '' ? null : $value;
    }

    private function importIntegerCell(array $row, int $index, string $label): ?int
    {
        $value = $this->importCell($row, $index);
        if ($value === null) {
            return null;
        }

        if (!preg_match('/^\d+(?:\.0+)?$/', $value)) {
            throw new \RuntimeException("El {$label} debe ser un ID entero.");
        }

        return (int) $value;
    }

    private function findImportPlan(string $name): ?CatPlanEstatalDesarrollo
    {
        $normalized = mb_strtolower(trim($name));

        return CatPlanEstatalDesarrollo::all()->first(
            fn ($plan) => mb_strtolower(trim($plan->nombre)) === $normalized
        );
    }

    private function resolveImportAlignment(string $type, string $programName, string $ejeName, CatPlanEstatalDesarrollo $plan): array
    {
        $normalizedType = mb_strtolower(trim($type));
        $isEje = $this->isEjeImportType($normalizedType);

        if ($isEje) {
            $name = $ejeName ?: $programName;
            $eje = CatEje::where('plan_id', $plan->id)->get()->first(
                fn ($item) => mb_strtolower(trim($item->nombre)) === mb_strtolower(trim($name))
            );

            if (!$eje) {
                throw new \RuntimeException("El eje '{$name}' no existe en el plan '{$plan->nombre}'.");
            }

            return [
                'id' => $eje->id,
                'type' => CatEje::class,
                'programaDerivado' => $plan->nombre,
                'programa' => $eje->nombre,
            ];
        }

        $modelClass = null;
        foreach ([
            'sectorial' => CatProgramaDerivadoSectorial::class,
            'especial' => CatProgramaDerivadoEspecial::class,
            'regional' => CatProgramaDerivadoRegional::class,
            'institucional' => CatProgramaDerivadoInstitucional::class,
        ] as $keyword => $class) {
            if (str_contains($normalizedType, $keyword)) {
                $modelClass = $class;
                break;
            }
        }

        if (!$modelClass) {
            throw new \RuntimeException("Tipo de programa '{$type}' no reconocido.");
        }

        $program = $modelClass::where('plan_estatal', $plan->id)->get()->first(
            fn ($item) => mb_strtolower(trim($item->nombre)) === mb_strtolower(trim($programName))
        );

        if (!$program) {
            throw new \RuntimeException("El programa '{$programName}' no existe en el plan '{$plan->nombre}'.");
        }

        return [
            'id' => $program->id,
            'type' => $modelClass,
            'programaDerivado' => $program->nombre,
            'programa' => $ejeName ?: $program->nombre,
        ];
    }

    private function isEjeImportType(string $type): bool
    {
        $type = mb_strtolower(trim($type));

        return str_contains($type, 'eje') || str_contains($type, 'plan estatal');
    }

    private function findImportIndicator(?int $id, string $name, int $planId, array $alignment): ?Indicador
    {
        if ($id !== null) {
            $indicator = Indicador::forPlan($planId)->whereKey($id)->first();
            if (!$indicator) {
                throw new \RuntimeException("El indicador con ID {$id} no pertenece al plan indicado o no existe.");
            }

            return $indicator;
        }

        $query = Indicador::forPlan($planId)->where('nombre', $name);
        if ($alignment['type'] === CatProgramaDerivadoInstitucional::class) {
            $query->whereHas('programasInstitucionales', fn ($program) => $program->whereKey($alignment['id']));
        } else {
            $query->where('indicadorable_type', $alignment['type'])
                ->where('indicadorable_id', $alignment['id']);
        }

        return $query->first();
    }

    private function authorizeImportRow(User $user, int $planId, ?int $institutionId, ?Indicador $indicator): void
    {
        if ($user->isAdministrator()) {
            return;
        }

        if ($planId !== $this->activePlan->id()) {
            throw new \RuntimeException('Solo puedes importar indicadores del plan activo.');
        }

        if ($institutionId === null) {
            throw new \RuntimeException('La institución responsable es obligatoria para tu perfil.');
        }

        if ($indicator && $indicator->id_institucion && $indicator->id_institucion !== $institutionId) {
            throw new \RuntimeException('No puedes cambiar la institución responsable de un indicador existente.');
        }

        if ($user->hasRole('Enlace')) {
            $allowed = $user->instituciones()->whereKey($institutionId)->exists();
        } elseif ($user->hasRole(['Enlace dependencia', 'Visualizador'])) {
            $allowed = (int) $user->id_institucion === $institutionId;
        } else {
            $allowed = false;
        }

        if (!$allowed) {
            throw new \RuntimeException('No tienes permiso para importar indicadores de esa institución.');
        }
    }

    private function importMetaYear(CatPlanEstatalDesarrollo $plan): int
    {
        return (int) $plan->id === 3 ? 2030 : 2024;
    }

    private function normalizeImportUrl(?string $value): ?string
    {
        return $value === null ? null : str_replace(' ', '%20', trim($value));
    }

    private function normalizeImportDate(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            if (is_numeric($value) && (float) $value > 20000) {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            }

            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            throw new \RuntimeException("La fecha '{$value}' no es válida.");
        }
    }

    private function parseImportOds(?string $value): array
    {
        if ($value === null) {
            return [];
        }

        $ids = array_values(array_unique(array_filter(array_map('trim', preg_split('/[,;]+/', $value)))));
        if (array_filter($ids, fn ($id) => !ctype_digit($id))) {
            throw new \RuntimeException('La columna ODS solo puede contener IDs numéricos separados por comas.');
        }

        $ids = array_map('intval', $ids);
        if (count($ids) !== Odses::whereIn('id', $ids)->count()) {
            throw new \RuntimeException('Uno o más ODS no existen en el catálogo.');
        }

        return $ids;
    }

    private function forgetImportFile(string $filePath): void
    {
        try {
            Storage::delete($filePath);
        } catch (\Throwable $e) {
            Log::warning("No se pudo eliminar archivo temporal: {$filePath}");
        }

        session()->forget('importFilePath');
    }

    private function applyDerivedProgramFilter($query): void
    {
        $query->where(function ($query) {
            $query->whereHasMorph('indicadorable', [
                CatProgramaDerivadoSectorial::class,
                CatProgramaDerivadoEspecial::class,
                CatProgramaDerivadoRegional::class,
            ])->orWhereHas('programasInstitucionales');
        });
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

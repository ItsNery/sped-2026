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
            // Indicadores para el administrador
            $indicadores = Indicador::with('datosAnuales')->get();
            // dd($indicadores);
            // $instituciones = Institucion::where('id', '!=', 1)->get();
            $instituciones = Institucion::whereHas('indicadores')->where('id', '!=', 1)->get();


            return view('panel-indicadores.index', compact('indicadores', 'instituciones', 'tiposPrograma'));
        }

        if ($user->hasRole('Enlace')) {
            // Indicadores para el enlace (varias instituciones asignadas)
            $institucionesAsignadas = $user->instituciones()->pluck('institucion_id');
            $indicadores = Indicador::whereIn('id_institucion', $institucionesAsignadas)
                ->orderBy('id')
                ->paginate(1000);
            $instituciones = $user->instituciones;

            return view('panel-indicadores.index', compact('indicadores', 'tiposPrograma', 'instituciones'));
        }

        if ($user->hasRole(['Enlace dependencia', 'Visualizador'])) {
            // Indicadores para enlace de dependencia o visualizador (por id_institucion en tabla users)
            $indicadores = Indicador::where('id_institucion', $user->id_institucion)
                ->where('id', '!=', 608)
                ->orderBy('id')
                ->get();

            $todosValidados = $indicadores->isEmpty() ? false : ($indicadores->where('indicador_validado', 1)->count() === $indicadores->count());

            $mostrarBotonFinalizar = $todosValidados && $user->finalizado != 1;
            $mostrarBotonGenerarReporte = $todosValidados && $user->finalizado == 1 && $user->reporte_generado != 1;

            return view('panel-indicadores.index', compact('indicadores', 'mostrarBotonFinalizar', 'user', 'mostrarBotonGenerarReporte'));
        }

        // Caso por defecto (otros usuarios, ej. capturistas que solo ven lo suyo)
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
            'Programa Institucional',
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

        // $usuarios = User::where('id', '!=', 1)->get();
        $usuarios = User::where('id', '>=', 8) // IDs del 8 en adelante
            ->role('Enlace dependencia')       // Solo con este rol
            ->get();
        $instituciones = Institucion::where('id', '!=', 1)->get();

        // Fetch Plans for the new parent selection
        $planes = CatPlanEstatalDesarrollo::all();

        // dd($odses);
        return view('panel-indicadores.crear', compact('pds', 'instituciones', 'usuarios', 'odses', 'periodicidades', 'coberturas', 'tendencias', 'planes'));
    }

    /**
     * Almacena un nuevo indicador y sus datos anuales asociados.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Log::debug('IndicadorController@store: Método iniciado.');

        // Log::debug('IndicadorController@store: Datos completos del Request:', $request->all()); // LOG 2 (Todos los datos)
        // Opcional: Loguear solo los datos esperados para no llenar demasiado el log si hay muchos campos
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
        // Log::debug('IndicadorController@store: Request data (odses):', ['odses' => $request->input('odses')]);
        Log::debug('IndicadorController@store: Request data (datos_anuales):', ['datos_anuales' => $request->input('datos_anuales')]);


        // Pre-procesamiento de URL para codificar espacios
        if ($request->filled('liga')) {
            $request->merge([
                'liga' => str_replace(' ', '%20', trim($request->input('liga')))
            ]);
        }

        // --- VALIDACIÓN ---
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

        Log::debug('IndicadorController@store: Antes de la validación.'); // LOG 3
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            Log::warning('IndicadorController@store: Falló la validación.', $validator->errors()->toArray()); // LOG 3.1 (Si falla)
            return back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated(); // Obtener los datos validados
        Log::info('IndicadorController@store: Validación exitosa.'); // LOG 4 (Si pasa)
        // Log::debug('IndicadorController@store: Datos validados:', $validatedData); // Opcional, puede ser redundante si ya logueaste el request

        Log::debug('IndicadorController@store: Antes de DB::beginTransaction().'); // LOG 5
        DB::beginTransaction();

        try {
            Log::debug('IndicadorController@store: Dentro del bloque try, antes de crear Indicador.'); // LOG 6

            // Determine Parent and Strings
            $indicadorableId = null;
            $indicadorableType = null;
            $programaDerivadoString = '';

            if ($request->boolean('es_programa_derivado')) {
                // It's a Derived Program
                $indicadorableId = $validatedData['programa_id'];
                $modelClass = $this->getProgramaModelClass($validatedData['tipo_programa']);
                $indicadorableType = $modelClass;

                // Fetch the object to get its name
                $parentObj = $modelClass::find($indicadorableId);
                $programaDerivadoString = $parentObj ? $parentObj->nombre : $validatedData['tipo_programa'];
            } else {
                // It's a Plan (Eje)
                $indicadorableId = $request->input('eje_id');
                $indicadorableType = CatEje::class;

                // Fetch Eje Name
                $parentObj = CatEje::find($indicadorableId);
                $programaDerivadoString = ($parentObj && $parentObj->catPlanEstatalDesarrollo) ? $parentObj->catPlanEstatalDesarrollo->nombre : 'Plan Estatal de Desarrollo';
            }

            $indicador = Indicador::create([
                'nombre' => $validatedData['nombre'],
                'programa_derivado' => $programaDerivadoString, // Auto-filled from Parent Name
                'programa' => $validatedData['eje_app'], // Manual input for "Eje"
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
            Log::info('IndicadorController@store: Indicador creado con ID: ' . $indicador->id); // LOG 7

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
                Log::debug('IndicadorController@store: Procesando datos_anuales. Cantidad: ' . count($validatedData['datos_anuales'])); // LOG 8
                foreach ($validatedData['datos_anuales'] as $index => $datoAnualData) {
                    Log::debug("IndicadorController@store: Procesando datoAnualData[{$index}]:", $datoAnualData); // LOG 9
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
                            Log::info("IndicadorController@store: DatoAnual creado con ID: {$datoAnualCreado->id} para el año {$datoAnualData['anio']}."); // LOG 9.2
                        } else {
                            Log::debug("IndicadorController@store: Omitiendo creación de DatoAnual para el año {$datoAnualData['anio']} (sin datos significativos)."); // LOG 9.3
                        }
                    } else {
                        Log::warning("IndicadorController@store: Se omitió un dato anual porque no tenía 'anio'. Datos:", $datoAnualData); // LOG 9.4
                    }
                }
            } else {
                Log::debug('IndicadorController@store: No se proporcionaron datos_anuales.'); // LOG 8.1 (Si no hay datos anuales)
            }

            if (!empty($validatedData['odses'])) {
                Log::debug('IndicadorController@store: Antes de sincronizar ODSes.', ['odses_ids' => $validatedData['odses']]); // LOG 10
                $indicador->ods()->sync($validatedData['odses']);
                Log::info('IndicadorController@store: ODSes sincronizados.'); // LOG 11
            } else {
                Log::debug('IndicadorController@store: No se proporcionaron ODSes para sincronizar.'); // LOG 10.1
            }

            Log::debug('IndicadorController@store: Antes de DB::commit().'); // LOG 12
            DB::commit();
            Log::info('IndicadorController@store: Transacción completada (commit).'); // LOG 13

            return redirect()->route('panel-indicadores.index')
                ->with('success', 'Indicador creado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Este bloque es por si algo escapa a la validación inicial, aunque es raro con $validator->validated()
            DB::rollBack();
            Log::error('IndicadorController@store: Error de Validación en el bloque try-catch.', [
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'trace' => $e->getTraceAsString() // Loguear el stack trace puede ser muy largo, pero útil
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IndicadorController@store: Excepción general atrapada.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                // 'trace' => $e->getTraceAsString() // Descomentar si necesitas el stack trace completo
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
        /** @var \App\Models\User */
        $user = auth()->user();

        // Obtener el indicador junto con sus relaciones
        $indicador = Indicador::with(['datosAnuales', 'ods'])->findOrFail($id);

        // Verificar si el usuario tiene acceso al indicador

        if ($user->hasRole('Enlace')) {
            // Obtener las instituciones asignadas al usuario
            $institucionesAsignadas = $user->instituciones->pluck('id');

            // Validar que el indicador pertenece a una de las instituciones permitidas
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
            // Los administradores tienen acceso a todos los indicadores, no se hace restricción
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
        /** @var \App\Models\User */
        $user = auth()->user();

        // $id = $indicador->id;
        // $indicador = Indicador::findOrFail($id);
        $indicador = Indicador::with(['datosAnuales'])->findOrFail($id);

        // Verificar si el usuario tiene acceso al indicador
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
        $planes = CatPlanEstatalDesarrollo::all(); // Fetch Planes for Edit View
        // $usuarios = User::where('id', '!=', 1)->get();
        $usuarios = User::where('id', '>=', 8) // IDs del 8 en adelante
            ->role('Enlace dependencia')       // Solo con este rol
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

        return view('panel-indicadores.editar', compact('indicador', 'instituciones', 'odeses', 'usuarios', 'periodicidades', 'coberturas', 'tendencias', 'planes'));
    }

    /**
     * Actualiza un indicador y gestiona la creación, actualización y eliminación de sus datos anuales.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Indicador  $indicador
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Indicador $indicador)
    {
        $user = auth()->user();

        // Verificar si el usuario tiene acceso al indicador
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

        // Pre-procesamiento de URL
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

        // Mantenemos tus mensajes personalizados originales aquí
        $messages = [
            // Mensajes para los campos principales del Indicador
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
            'dato_linea_base.string' => 'El valor de la línea base debe ser texto o número.', // O 'numeric' si siempre es número
            'dato_linea_base.max' => 'El valor de la línea base no debe exceder los 255 caracteres.',

            'meta_2024.required' => 'La meta 2024 es obligatoria.',
            'meta_2024.string' => 'La meta 2024 debe ser texto o número.', // O 'numeric'
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
            // Resolver Programa Derivado (Polimórfico) o Plan
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

            $indicadorDataToUpdate = collect($validatedData)->except(['odses', 'datos_anuales', '_token', '_method', 'plan_id', 'es_programa_derivado', 'tipo_programa', 'programa_id', 'eje_app'])->toArray();

            // Sobrescribir datos calculados
            $indicadorDataToUpdate['programa_derivado'] = $programaDerivadoString;
            $indicadorDataToUpdate['programa'] = $validatedData['eje_app'];
            $indicadorDataToUpdate['indicadorable_id'] = $indicadorableId;
            $indicadorDataToUpdate['indicadorable_type'] = $indicadorableType;

            // Comprobar si hubo cambios para resetear la validación
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

            // === 1. CAPTURAMOS EL AÑO VIEJO ANTES DE ACTUALIZAR ===
            $anioLineaBaseAnterior = $indicador->linea_base;

            // === 2. ACTUALIZAMOS EL INDICADOR PRINCIPAL ===
            $indicador->update($indicadorDataToUpdate);

            $idsDatosAnualesEnviadosYProcesados = [];

            // === 3. SINCRONIZAR LÍNEA BASE EN DATOS ANUALES ===
            if (isset($validatedData['linea_base']) && isset($validatedData['dato_linea_base']) && $validatedData['dato_linea_base'] !== '') {

                // Si el año cambió, borramos el registro del año viejo
                if ($anioLineaBaseAnterior && $anioLineaBaseAnterior != $validatedData['linea_base']) {
                    $indicador->datosAnuales()->where('anio', $anioLineaBaseAnterior)->delete();
                }

                // Actualizamos o creamos el dato del año correcto
                $datoAnualLineaBase = $indicador->datosAnuales()->updateOrCreate(
                    ['anio' => $validatedData['linea_base']],
                    ['valor_dato' => $validatedData['dato_linea_base']]
                );

                // Protegemos este registro
                $idsDatosAnualesEnviadosYProcesados[] = $datoAnualLineaBase->id;
            }

            // === 4. PROCESAR HISTÓRICOS (DATOS ANUALES DE LA TABLA) ===
            if (isset($validatedData['datos_anuales'])) {
                $archivosEvidenciaEnRequest = $request->file('datos_anuales') ?? [];

                foreach ($validatedData['datos_anuales'] as $index => $datoAnualData) {
                    $idDatoAnual = $datoAnualData['id'] ?? null;
                    $anio = $datoAnualData['anio'] ?? null;

                    if (empty($anio)) continue;

                    // Si el año de este registro coincide con el año de la línea base anterior,
                    // y el año de la línea base ya cambió, evitamos re-crearlo.
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

                    // Manejo de Evidencia
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

                    // Solo agregarlo si no estaba ya protegido por la línea base
                    if (!in_array($datoAnualRecord->id, $idsDatosAnualesEnviadosYProcesados)) {
                        $idsDatosAnualesEnviadosYProcesados[] = $datoAnualRecord->id;
                    }
                }
            }

            // === 5. LIMPIEZA DE DATOS ELIMINADOS ===
            if ($request->exists('datos_anuales')) {
                $datosAnualesAEliminar = $indicador->datosAnuales()->whereNotIn('id', $idsDatosAnualesEnviadosYProcesados)->get();
                foreach ($datosAnualesAEliminar as $dae) {
                    if ($dae->evidencia && file_exists(public_path('assets-administrador/docs/' . $dae->evidencia))) {
                        unlink(public_path('assets-administrador/docs/' . $dae->evidencia));
                    }
                    $dae->delete();
                }
            }

            // === 6. SINCRONIZAR ODS ===
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
    // Old update method
    public function update2(Request $request, Indicador $indicador)
    {
        /** @var \App\Models\User */
        $user = auth()->user();

        // Verificar si el usuario tiene acceso al indicador
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

        $indicadorIdForLog = 'NO DISPONIBLE';
        if ($indicador && $indicador->exists) { // Verificamos si el modelo fue cargado y existe en BD
            $indicadorIdForLog = $indicador->id;
        }
        Log::debug("IndicadorController@update: Método iniciado. Indicador recibido (ID: {$indicadorIdForLog}). Tipo: " . get_class($indicador));
        if (!$indicador || !$indicador->exists) {
            Log::error("IndicadorController@update: La instancia del Indicador no es válida o no existe. ID recibido en ruta: " . $request->route('indicador')); // Asume que el parámetro de ruta se llama 'indicador'
            // Esto no debería pasar con Route Model Binding si la ruta está bien definida, Laravel daría 404.
            // Pero si llega aquí, es un problema grave.
            return redirect()->route('panel-indicadores.index')->with('error', 'Indicador no encontrado.');
        }
        Log::debug("IndicadorController@update: ID del Indicador (desde el objeto): {$indicador->id}.");


        // Pre-procesamiento de URL para codificar espacios
        if ($request->filled('liga')) {
            $request->merge([
                'liga' => str_replace(' ', '%20', trim($request->input('liga')))
            ]);
        }

        // --- VALIDACIÓN ---
        $rules = [
            'nombre' => 'required|string|max:255',
            // 'programa_derivado' => 'required|string|max:255', // Asegúrate que este sea el nombre correcto del campo
            // 'programa' => 'required|string|max:255',
            'plan_id' => 'required|exists:cat_planes_estatales_desarrollo,id',
            'eje_id' => 'nullable|required_unless:es_programa_derivado,1|exists:cat_ejes,id',
            'es_programa_derivado' => 'boolean',
            'tipo_programa' => 'nullable|required_if:es_programa_derivado,1|string',
            'programa_id' => 'nullable|required_if:es_programa_derivado,1|integer',
            'eje_app' => 'required|string|max:255',
            // 'cod_tematica' => 'required|string|max:255',
            // 'cod_tematica' => 'required|string|max:255',
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
            // 'resultados' => 'required|string',
            'formula' => 'required|string',
            'odses' => 'sometimes|array',
            'odses.*' => 'exists:ods,id',
            'indicador_validado' => 'sometimes|boolean',
            'datos_anuales' => 'nullable|array',
            'datos_anuales.*.id' => 'nullable|integer|exists:datos_anuales,id',
            'datos_anuales.*.anio' => [
                'required_with:datos_anuales', // o 'required' si cada bloque debe tener año
                'integer',
                'min:1900',
                'max:' . (date('Y') + 10),
                'distinct', // Único dentro del array enviado
                // Para unicidad contra la BD (id_indicador, anio) excluyendo el ID actual:
                // Esta validación se manejará mejor con updateOrCreate y la lógica de búsqueda.
                // Si necesitas un error de validación explícito aquí, sería una Regla Personalizada.
            ],
            'datos_anuales.*.valor_dato' => 'nullable|numeric',
            'datos_anuales.*.fecha_actualizacion' => 'nullable|date',
            'datos_anuales.*.resultados' => 'nullable|string',
            'datos_anuales.*.observaciones' => 'nullable|string',
            'datos_anuales.*.evidencia_file' => 'nullable|file|mimes:pdf|max:10240',
            'datos_anuales.*.evidencia_actual' => 'nullable|string',
            'datos_anuales.*.eliminar_evidencia' => 'nullable|boolean',
        ];

        $messages = [
            // Mensajes para los campos principales del Indicador
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
            'dato_linea_base.string' => 'El valor de la línea base debe ser texto o número.', // O 'numeric' si siempre es número
            'dato_linea_base.max' => 'El valor de la línea base no debe exceder los 255 caracteres.',

            'meta_2024.required' => 'La meta 2024 es obligatoria.',
            'meta_2024.string' => 'La meta 2024 debe ser texto o número.', // O 'numeric'
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

        Log::debug("IndicadorController@update: Antes de la validación para Indicador ID: {$indicador->id}.");
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            Log::warning("IndicadorController@update: Falló la validación para Indicador ID: {$indicador->id}.", $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();
        Log::info("IndicadorController@update: Validación exitosa para Indicador ID: {$indicador->id}.");

        DB::beginTransaction();
        Log::debug("IndicadorController@update: Transacción iniciada para Indicador ID: {$indicador->id}.");

        try {
            // Determine Parent and Strings (Same logic as store)
            $indicadorableId = null;
            $indicadorableType = null;
            $programaDerivadoString = '';

            if ($request->boolean('es_programa_derivado')) {
                // It's a Derived Program
                $indicadorableId = $validatedData['programa_id'];
                $modelClass = $this->getProgramaModelClass($validatedData['tipo_programa']);
                $indicadorableType = $modelClass;

                // Fetch the object to get its name
                $parentObj = $modelClass::find($indicadorableId);
                $programaDerivadoString = $parentObj ? $parentObj->nombre : $validatedData['tipo_programa'];
            } else {
                // It's a Plan (Eje)
                $indicadorableId = $request->input('eje_id');
                $indicadorableType = CatEje::class;

                // Fetch Eje Name
                $parentObj = CatEje::find($indicadorableId);
                $programaDerivadoString = $parentObj && $parentObj->planEstatal ? $parentObj->planEstatal->nombre : 'Plan Estatal de Desarrollo';
            }

            $indicadorDataToUpdate = collect($validatedData)->except(['odses', 'datos_anuales', '_token', '_method', 'plan_id', 'es_programa_derivado', 'tipo_programa', 'programa_id', 'eje_app'])->toArray();

            // Overwrite/Add the calculated fields
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
                Log::debug("IndicadorController@update: Indicador ID {$indicador->id} desvalidado por cambios.");
            }
            $indicador->update($indicadorDataToUpdate);
            Log::info("IndicadorController@update: Indicador principal ID {$indicador->id} actualizado.");

            if (!empty($validatedData['linea_base']) && !empty($validatedData['dato_linea_base'])) {
                // updateOrCreate busca un registro con ese 'anio'. Si existe, le actualiza el 'valor_dato'. Si no, lo crea.
                $indicador->datosAnuales()->updateOrCreate(
                    ['anio' => $validatedData['linea_base']], // Criterio de búsqueda
                    ['valor_dato' => $validatedData['dato_linea_base']] // Datos a actualizar/insertar
                );
                Log::info("IndicadorController@update: Línea base sincronizada en DatoAnual (Año {$validatedData['linea_base']}).");

                // OJO: Como este registro se genera/actualiza automáticamente para ser la línea base, 
                // debemos asegurarnos de que su ID no sea borrado por la rutina de limpieza que tienes más abajo.
                // Agregamos su ID al arreglo de registros "enviados y procesados" para protegerlo.
                $datoAnualLineaBase = $indicador->datosAnuales()->where('anio', $validatedData['linea_base'])->first();
                if ($datoAnualLineaBase) {
                    $idsDatosAnualesEnviadosYProcesados[] = $datoAnualLineaBase->id;
                }
            }
            $idsDatosAnualesEnviadosYProcesados = [];
            if (isset($validatedData['datos_anuales'])) {
                Log::debug("IndicadorController@update: Procesando datos_anuales para Indicador ID: {$indicador->id}. Cantidad: " . count($validatedData['datos_anuales']));
                $archivosEvidenciaEnRequest = $request->file('datos_anuales') ?? [];

                foreach ($validatedData['datos_anuales'] as $index => $datoAnualData) {
                    $idDatoAnual = $datoAnualData['id'] ?? null;
                    $anio = $datoAnualData['anio'] ?? null;

                    if (empty($anio)) {
                        Log::warning("IndicadorController@update: [Ind.{$indicador->id}] Se omitió un dato anual (índice validado {$index}) porque el año estaba vacío.", $datoAnualData);
                        continue;
                    }

                    $datoAnualRecord = null;
                    if ($idDatoAnual) {
                        $datoAnualRecord = DatoAnual::where('id', $idDatoAnual)->where('id_indicador', $indicador->id)->first();
                        if (!$datoAnualRecord) {
                            Log::warning("IndicadorController@update: [Ind.{$indicador->id}] ID de DatoAnual {$idDatoAnual} no encontrado o no pertenece. Buscando/creando por año {$anio}.");
                            $datoAnualRecord = $indicador->datosAnuales()->firstOrNew(['anio' => $anio]);
                        }
                    } else {
                        $datoAnualRecord = $indicador->datosAnuales()->firstOrNew(['anio' => $anio]);
                    }

                    // Asegurar que id_indicador esté establecido si es un nuevo registro
                    if (!$datoAnualRecord->exists) {
                        $datoAnualRecord->id_indicador = $indicador->id; // Crucial si firstOrNew en relación no lo hizo (debería)
                        Log::debug("IndicadorController@update: [Ind.{$indicador->id}] Nuevo DatoAnual para año {$anio}, asignando id_indicador: {$indicador->id}.");
                    }

                    $datosParaLlenar = [
                        'anio' => $anio, // Redundante si ya está en $datoAnualRecord, pero fill() lo manejará
                        'valor_dato' => $datoAnualData['valor_dato'] ?? null,
                        'fecha_actualizacion' => $datoAnualData['fecha_actualizacion'] ?? null,
                        'resultados' => $datoAnualData['resultados'] ?? null,
                        'observaciones' => $datoAnualData['observaciones'] ?? null,
                    ];

                    // Manejo de Evidencia
                    $nombreArchivoEvidenciaActual = $datoAnualData['evidencia_actual'] ?? ($datoAnualRecord->evidencia ?? null);
                    $nombreArchivoEvidenciaParaGuardar = $nombreArchivoEvidenciaActual;

                    if (!empty($datoAnualData['eliminar_evidencia'])) {
                        if ($nombreArchivoEvidenciaActual) {
                            Log::debug("IndicadorController@update: [Ind.{$indicador->id}, Año {$anio}] Eliminando evidencia '{$nombreArchivoEvidenciaActual}'.");
                            if (file_exists(public_path('assets-administrador/docs/' . $nombreArchivoEvidenciaActual))) {
                                unlink(public_path('assets-administrador/docs/' . $nombreArchivoEvidenciaActual));
                            }
                        }
                        $nombreArchivoEvidenciaParaGuardar = null;
                    }

                    $archivoEvidenciaSubido = $archivosEvidenciaEnRequest[$index]['evidencia_file'] ?? null;
                    if ($archivoEvidenciaSubido && $archivoEvidenciaSubido->isValid()) {
                        Log::debug("IndicadorController@update: [Ind.{$indicador->id}, Año {$anio}] Nuevo archivo de evidencia subido.");
                        if ($nombreArchivoEvidenciaActual && ($nombreArchivoEvidenciaParaGuardar === null || $nombreArchivoEvidenciaActual !== $nombreArchivoEvidenciaParaGuardar)) {
                            if (file_exists(public_path('assets-administrador/docs/' . $nombreArchivoEvidenciaActual))) {
                                unlink(public_path('assets-administrador/docs/' . $nombreArchivoEvidenciaActual));
                                Log::debug("IndicadorController@update: [Ind.{$indicador->id}, Año {$anio}] Evidencia antigua '{$nombreArchivoEvidenciaActual}' eliminada para reemplazo.");
                            }
                        }
                        $extension = $archivoEvidenciaSubido->getClientOriginalExtension();
                        $nombreArchivoEvidenciaParaGuardar = "Evidencia_{$anio}_{$indicador->id}_" . time() . "_" . $index . "." . $extension;
                        $archivoEvidenciaSubido->move(public_path('assets-administrador/docs/'), $nombreArchivoEvidenciaParaGuardar);
                        Log::info("IndicadorController@update: [Ind.{$indicador->id}, Año {$anio}] Nueva evidencia '{$nombreArchivoEvidenciaParaGuardar}' guardada.");
                    }
                    $datosParaLlenar['evidencia'] = $nombreArchivoEvidenciaParaGuardar;

                    $datoAnualRecord->fill($datosParaLlenar);

                    if ($datoAnualRecord->id_indicador === null) {
                        Log::critical("IndicadorController@update: [Ind.{$indicador->id}, Año {$anio}] ¡id_indicador es NULL ANTES de guardar DatoAnual! Record: ", $datoAnualRecord->toArray());
                        throw new \Exception("Integridad de datos: id_indicador no puede ser nulo para DatoAnual del año {$anio}.");
                    }

                    if ($datoAnualRecord->isDirty() || !$datoAnualRecord->exists) {
                        $datoAnualRecord->save();
                        Log::info("IndicadorController@update: [Ind.{$indicador->id}] DatoAnual ID {$datoAnualRecord->id} (Año: {$anio}) guardado.");
                    } else {
                        Log::debug("IndicadorController@update: [Ind.{$indicador->id}] Sin cambios para DatoAnual ID {$datoAnualRecord->id} (Año: {$anio}).");
                    }
                    $idsDatosAnualesEnviadosYProcesados[] = $datoAnualRecord->id;
                }
            } else {
                Log::debug("IndicadorController@update: [Ind.{$indicador->id}] No se proporcionaron datos_anuales en el request.");
            }

            if ($request->exists('datos_anuales')) {
                $datosAnualesAEliminar = $indicador->datosAnuales()->whereNotIn('id', $idsDatosAnualesEnviadosYProcesados)->get();
                foreach ($datosAnualesAEliminar as $dae) {
                    Log::debug("IndicadorController@update: [Ind.{$indicador->id}] Eliminando DatoAnual ID {$dae->id} (Año: {$dae->anio}).");
                    if ($dae->evidencia) {
                        if (file_exists(public_path('assets-administrador/docs/' . $dae->evidencia))) {
                            unlink(public_path('assets-administrador/docs/' . $dae->evidencia));
                        }
                    }
                    $dae->delete();
                }
            }

            if ($request->has('odses')) {
                Log::debug("IndicadorController@update: [Ind.{$indicador->id}] Sincronizando ODSes.", ['odses_ids' => $validatedData['odses'] ?? []]);
                $indicador->ods()->sync($validatedData['odses'] ?? []);
            }

            Log::debug("IndicadorController@update: [Ind.{$indicador->id}] Antes de DB::commit().");
            DB::commit();
            Log::info("IndicadorController@update: [Ind.{$indicador->id}] Transacción completada (commit).");

            return redirect()->route('panel-indicadores.index')->with('success', 'Indicador actualizado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error("IndicadorController@update: [Ind.{$indicador->id}] Error de Validación en try-catch.", ['message' => $e->getMessage(), 'errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("IndicadorController@update: [Ind.{$indicador->id}] Excepción general.", ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            return back()->withInput()->with('error', 'Ocurrió un error al actualizar el indicador: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un indicador y todos sus datos y archivos relacionados.
     * @param  \App\Models\Indicador  $indicador
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Indicador $indicador) // Asumiendo Route Model Binding
    {
        /** @var \App\Models\User */
        $user = auth()->user();

        // Verificar si el usuario tiene acceso al indicador
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
            // 1. (Opcional) Eliminar archivos de evidencia de los DatoAnual asociados
            //    Esto es necesario si el modelo DatoAnual no lo hace en su evento 'deleting'.
            foreach ($indicador->datosAnuales as $datoAnual) {
                if ($datoAnual->evidencia) {
                    $rutaArchivo = public_path('assets-administrador/docs/' . $datoAnual->evidencia);
                    if (file_exists($rutaArchivo)) {
                        unlink($rutaArchivo);
                        Log::info("IndicadorController@destroy: Archivo de evidencia '{$datoAnual->evidencia}' eliminado para DatoAnual ID {$datoAnual->id} (Indicador ID {$indicador->id}).");
                    }
                }
            }

            // 2. Elimina los registros DatoAnual relacionados
            $indicador->datosAnuales()->delete();
            Log::info("IndicadorController@destroy: Registros DatoAnual eliminados para Indicador ID {$indicador->id}.");

            // 3. Elimina las relaciones en la tabla pivot (Indicador_ods)
            $indicador->ods()->detach();
            Log::info("IndicadorController@destroy: Relaciones ODS eliminadas para Indicador ID {$indicador->id}.");

            // 4. Finalmente, elimina el indicador
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
        /** @var \App\Models\User */
        $user = auth()->user();

        // Verificar si el usuario tiene el rol "Enlace"
        if ($user->hasRole('Enlace')) {
            // Obtener las instituciones asignadas al usuario
            $institucionesAsignadas = $user->instituciones->pluck('id');

            // Validar si la institución seleccionada está permitida
            if ($institucion !== 'todos' && !$institucionesAsignadas->contains($institucion)) {
                return response()->json(['error' => 'No tienes acceso a esta institución.'], 403);
            }

            // Aplicar filtros según el valor de institución y programa
            $indicadores = Indicador::query()
                ->when($institucion !== 'todos', function ($query) use ($institucion) {
                    $query->where('id_institucion', $institucion);
                })
                ->when($programa, function ($query) use ($programa) {
                    $query->where('programa_derivado', $programa);
                })
                ->whereIn('id_institucion', $institucionesAsignadas) // Restringir a las instituciones asignadas
                ->get();
        } else {
            // Otros roles pueden ver todos los indicadores (o ajustar según necesidades)
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleValidacion($id)
    {
        // Buscar el indicador
        $indicador = Indicador::findOrFail($id);

        // Alternar el estado de validación
        $estadoValidacion = !$indicador->indicador_validado;
        $indicador->indicador_validado = $estadoValidacion;

        // Guardar los cambios y propagar al histórico anual
        $indicador->save();
        $indicador->datosAnuales()->update(['validado' => $estadoValidacion]);

        return redirect()->back()->with('status', 'Estado de validación actualizado.');
    }

    /**
     * Almacena un nuevo año para un indicador.
     * @param  \Illuminate\Http\Request $request
     * @param  int $id ID del Indicador
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeAnualData(Request $request, $id)
    {
        Log::debug("IndicadorController@storeAnualData: Iniciado para Indicador ID: {$id}.");
        $year = $request->anio;

        if (empty($year)) {
            return redirect()->back()->with('error', 'El año es obligatorio.');
        }

        // Simplemente reutilizamos la lógica de updateAnualData pasando el año del request
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

        // 1. Validar los datos de entrada (ahora con nombres genéricos)
        // Los nombres de los campos vienen del formulario del modal que ajustamos
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
                ->withErrors($validator, "updateAnualValidation_{$year}") // Enviar errores a una bolsa específica
                ->withInput()
                ->with("updateAnualValidationErrors_{$year}", true); // Flag para reabrir el modal
        }

        $validatedData = $validator->validated();
        Log::info("IndicadorController@updateAnualData: Validación exitosa para Indicador ID: {$id}, Año: {$year}.");

        DB::beginTransaction();
        try {
            // 2. Buscar o crear el registro DatoAnual para este indicador y año
            // firstOrNew preparará una nueva instancia con id_indicador y anio si no existe
            $datoAnual = $indicador->datosAnuales()->firstOrNew(['anio' => $year]);
            Log::debug("IndicadorController@updateAnualData: DatoAnual " . ($datoAnual->exists ? "encontrado (ID: {$datoAnual->id})" : "nuevo") . " para Año: {$year}.");


            // 3. Preparar los datos para actualizar/crear
            // Los nombres de las claves en $dataToSave deben coincidir con las columnas de la tabla `datos_anuales`
            $dataToSave = [
                'valor_dato' => $validatedData['valor_dato'] ?? null,
                'resultados' => $validatedData['resultados_anual'] ?? null,
                'observaciones' => $validatedData['observaciones_anual'] ?? null,
                'fecha_actualizacion' => $validatedData['fecha_actualizacion_anual'] ?? null,
                // 'modificado' será manejado por el observer del modelo DatoAnual si hay cambios
            ];

            // 4. Manejar la evidencia
            $nombreArchivoEvidenciaActual = $datoAnual->evidencia ?? null;
            $nombreArchivoEvidenciaParaGuardar = $nombreArchivoEvidenciaActual;

            // a. Si se marcó "eliminar_evidencia_anual"
            if (!empty($validatedData['eliminar_evidencia_anual'])) {
                if ($nombreArchivoEvidenciaActual) {
                    Log::debug("IndicadorController@updateAnualData: [Ind.{$id}, Año {$year}] Eliminando evidencia actual '{$nombreArchivoEvidenciaActual}' por checkbox.");
                    if (file_exists(public_path('assets-administrador/docs/' . $nombreArchivoEvidenciaActual))) {
                        unlink(public_path('assets-administrador/docs/' . $nombreArchivoEvidenciaActual));
                    }
                }
                $nombreArchivoEvidenciaParaGuardar = null;
            }

            // b. Si se subió un nuevo archivo "evidencia_anual"
            if ($request->hasFile('evidencia_anual') && $request->file('evidencia_anual')->isValid()) {
                $archivoEvidenciaSubido = $request->file('evidencia_anual');
                Log::debug("IndicadorController@updateAnualData: [Ind.{$id}, Año {$year}] Nuevo archivo de evidencia subido: " . $archivoEvidenciaSubido->getClientOriginalName());

                // Eliminar el archivo viejo si existe y se está reemplazando
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


            // 5. Llenar el modelo DatoAnual y guardar
            // El id_indicador y anio ya están establecidos por firstOrNew
            $datoAnual->fill($dataToSave);

            // El observer en DatoAnual se encargará de 'modificado = true' y de
            // 'indicador_validado = false' en el Indicador padre si hay cambios.
            if ($datoAnual->isDirty() || !$datoAnual->exists) {
                // Si es un nuevo registro y id_indicador no está (aunque firstOrNew en relación debería ponerlo)
                if (!$datoAnual->exists && !$datoAnual->id_indicador) {
                    $datoAnual->id_indicador = $indicador->id;
                    Log::warning("IndicadorController@updateAnualData: [Ind.{$id}, Año {$year}] id_indicador fue explícitamente asignado para nuevo DatoAnual.");
                }

                if ($datoAnual->id_indicador === null) { // Comprobación crítica
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
                // 'trace' => $e->getTraceAsString() // Descomentar para trace completo
            ]);
            return redirect()->back()
                ->with('error', "Ocurrió un error al actualizar los datos para el año {$year}: " . $e->getMessage())
                ->with("updateAnualValidationErrors_{$year}", true); // Para reabrir el modal
        }
    }

    /**
     * Finaliza el periodo de captura para un usuario.
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
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
    // old v1
    // public function generarReporte($id)
    // {

    //     $user = Auth::user()->load([
    //         'institucion', // Carga la institución relacionada con el usuario
    //         'indicadores.datosAnuales', // Carga los indicadores del usuario y los datos anuales de cada indicador
    //         'indicadores.institucion' // Carga la institución de cada indicador
    //     ]);
    //     $user->update([
    //         'reporte_generado' => true,
    //         'reporte_generado_at' => now(),
    //     ]);

    //     // Pasar los datos a la vista
    //     return view('panel-indicadores.generar-documento', compact('user'));
    // }

    /**
     * Genera la vista de reporte imprimible para un usuario.
     * @param int $id ID del Usuario
     * @return \Illuminate\View\View
     */
    public function generarReporte($id)
    {
        /** @var \App\Models\User|null $user */
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
     * @param  \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
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
            // Cada 'DatoAnual' tendrá 'anio' y 'valor_dato'.
            'datosAnuales' => function ($query) {
                // Seleccionar solo las columnas necesarias del modelo DatoAnual
                // para optimizar la consulta, si no necesitas todas.
                $query->select('id_indicador', 'anio', 'valor_dato' /*, 'resultados', 'observaciones', etc. si los necesitas */);
            },
            'ods',
            'institucion:id,nombre'
        ]);

        switch ($parametro) {
            case 'total-indicadores-ped':
                // No se necesitan condiciones 'where' adicionales para este caso.
                // La consulta base ya está definida en $indicadoresQuery.
                // Simplemente se usará $indicadoresQuery->get() después del switch.
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
            // Definir un array con los campos de datos anuales y valores vacíos por defecto
            // para el formato "ancho" del Excel.
            $camposAnualesParaExcel = [];
            $rangoDeAniosParaExcel = range(2015, 2030); // Ajusta este rango según necesites para las columnas del Excel

            foreach ($rangoDeAniosParaExcel as $year) {
                $camposAnualesParaExcel["dato_$year"] = ''; // Inicializar con vacío
                // Si necesitas otros campos anuales en el Excel, inicialízalos aquí también:
                // $camposAnualesParaExcel["resultados_$year"] = '';
                // $camposAnualesParaExcel["observaciones_$year"] = '';
            }

            // Si existen datos anuales (ahora es una colección de objetos DatoAnual),
            // iterar sobre ellos y llenar los campos correspondientes.
            if ($indicador->datosAnuales && $indicador->datosAnuales->isNotEmpty()) {
                foreach ($indicador->datosAnuales as $datoAnual) {
                    $keyParaValor = "dato_" . $datoAnual->anio;
                    if (array_key_exists($keyParaValor, $camposAnualesParaExcel)) {
                        $camposAnualesParaExcel[$keyParaValor] = $datoAnual->valor_dato;
                    }

                    // Si necesitas otros campos de DatoAnual en el Excel:
                    // $keyParaResultados = "resultados_" . $datoAnual->anio;
                    // if (array_key_exists($keyParaResultados, $camposAnualesParaExcel)) {
                    //    $camposAnualesParaExcel[$keyParaResultados] = $datoAnual->resultados;
                    // }
                }
            }

            // Concatenar ODS en un solo campo
            $ods = $indicador->ods->pluck('id')->unique()->implode(', ');

            // Retornar el nuevo formato del indicador para el Excel
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
                // 'resultados', // Estos son los resultados generales del indicador
                'formula',
                'fecha_actualizacion' // Esta es la fecha de actualización inicial
            ]);
            $datosIndicadorBase['nombre_institucion'] = $indicador->institucion ? $indicador->institucion->nombre : 'N/A';
            return array_merge($datosIndicadorBase, $camposAnualesParaExcel, ['ods' => $ods]);
        });

        Log::debug('IndicadorController@datosAbiertosPed: Indicadores mapeados para Excel: ' . $indicadoresParaExcel->count());

        $rutaPlantillaExcel = public_path('docs/plantillas-exportacion/plantilla.xlsx');

        try {
            $spreadsheet = IOFactory::load($rutaPlantillaExcel);
            $sheet = $spreadsheet->getActiveSheet();

            $fila = 2; // Comenzar desde la segunda fila
            foreach ($indicadoresParaExcel as $indicadorDataRow) { // Renombrada la variable para claridad
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
                // $sheet->setCellValue("P{$fila}", $indicadorDataRow['resultados']); // Resultados generales
                $sheet->setCellValue("P{$fila}", $indicadorDataRow['formula']);
                $sheet->setCellValue("Q{$fila}", $indicadorDataRow['ods']);
                $sheet->setCellValue("R{$fila}", $indicadorDataRow['fecha_actualizacion']); // Fecha actualización general

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

            $nombreArchivoFinal = "indicadores_ped_{$nombreArchivoBase}.xlsx"; // Nombre final del archivo
            $rutaSalida = storage_path("app/public/exports/{$nombreArchivoFinal}"); // Guardar en storage/app/public/exports

            // Asegurarse de que el directorio exista
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
     * @param  \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function datosAbiertosPedCsv(Request $request)
    {
        Log::debug('IndicadorController@datosAbiertosPedCsv: Iniciado.', $request->all());
        $nombreArchivoBase = $request->input('nombre_archivo', 'exportacion_indicadores'); // Usar input()
        $parametro = $request->input('parametro');

        // --- 1. OBTENER Y PREPARAR DATOS (REUTILIZAR LÓGICA) ---
        // Esta parte es muy similar a tu datosAbiertosPed para Excel.
        // Puedes refactorizar la lógica de consulta y mapeo a un método privado
        // para no duplicar código.

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
            'ods:id,nombre', // Asumiendo que quieres los nombres de los ODS
            'institucion:id,nombre'
        ]);

        // Aplicar filtros según $parametro (tu switch se mantiene igual)
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
                // OJO: Parece haber un espacio extra al final de 'Estado de Derecho, Seguridad y Justicia '
                // Asegúrate de que coincida exactamente con tu base de datos o quita el espacio.
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Estado de Derecho, Seguridad y Justicia'); // Corregido posible espacio extra
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

        // Mapeo de datos (igual que para Excel, pero asegúrate que los datos sean aptos para CSV)
        $rangoDeAniosCsv = range(2010, 2030); // O el rango que necesites

        $datosParaCsv = $indicadoresCollection->map(function ($indicador) use ($rangoDeAniosCsv) {
            $fila = [];
            // Datos del Indicador Principal
            $fila['ID Indicador'] = $indicador->id;
            $fila['Nombre Indicador'] = $indicador->nombre;
            $fila['Programa Derivado'] = $indicador->programa_derivado;
            $fila['Programa'] = $indicador->programa;
            $fila['Temática'] = $indicador->tematica;
            $fila['Linea Base (Año)'] = $indicador->linea_base;
            $fila['Linea Base (Dato)'] = $indicador->dato_linea_base;
            $fila['Unidad de Medida'] = $indicador->unidad_medida;
            $fila['Meta 2030'] = $indicador->meta_2024; // O Meta 2030 si el campo cambió
            $fila['Fuente'] = $indicador->fuente;
            $fila['Enlace'] = $indicador->liga;
            $fila['Descripción'] = $indicador->descripcion; // Cuidado con comas y saltos de línea aquí
            $fila['Periodicidad'] = $indicador->periodicidad;
            $fila['Cobertura'] = $indicador->cobertura;
            $fila['Tendencia'] = $indicador->tendencia;
            $fila['Resultados Generales'] = $indicador->resultados; // Cuidado con comas/saltos de línea
            $fila['Fórmula'] = $indicador->formula; // Cuidado con comas/saltos de línea
            $fila['Fecha Actualización Indicador'] = $indicador->fecha_actualizacion;
            $fila['Institución'] = $indicador->institucion ? $indicador->institucion->nombre : 'N/A';
            $fila['ODS'] = $indicador->ods->pluck('nombre')->implode('; '); // Usar punto y coma u otro delimitador si los nombres de ODS pueden tener comas

            // Datos Anuales
            foreach ($rangoDeAniosCsv as $year) {
                $datoAnual = $indicador->datosAnuales->firstWhere('anio', $year);
                $fila["Dato {$year}"] = $datoAnual ? $datoAnual->valor_dato : ''; // Vacío si no hay dato
            }
            return $fila;
        });

        if ($datosParaCsv->isEmpty()) {
            Log::warning('No hay datos para generar el CSV después del mapeo.');
            return redirect()->back()->with('error', 'No hay datos disponibles para exportar en formato CSV con los filtros seleccionados.');
        }

        // --- 2. GENERAR EL CONTENIDO CSV ---
        $nombreArchivoCsv = "indicadores_ped_{$nombreArchivoBase}.csv";
        $columnas = array_keys($datosParaCsv->first()); // Obtener los encabezados de la primera fila

        $callback = function () use ($datosParaCsv, $columnas) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // BOM para UTF-8
            fputcsv($file, $columnas); // Escribir encabezados

            foreach ($datosParaCsv as $fila) {
                fputcsv($file, $fila); // Escribir cada fila de datos
            }
            fclose($file);
        };

        // --- 3. RESPUESTA HTTP PARA CSV ---
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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function datosAbiertosPedJson(Request $request)
    {
        Log::debug('IndicadorController@datosAbiertosPedJson: Iniciado.', $request->all());
        $parametro = $request->input('parametro');
        // El 'nombre_archivo' no es tan relevante para una respuesta JSON directa,
        // pero podrías usarlo si el JSON se guarda en un archivo para descargar,
        // aunque lo común es devolver el JSON en la respuesta.
        // $nombreArchivoBase = $request->input('nombre_archivo', 'exportacion_indicadores');

        // --- 1. OBTENER Y PREPARAR DATOS (REUTILIZAR LÓGICA) ---
        // Asumimos que tienes un método privado como el que sugerí:
        // $indicadoresCollection = $this->obtenerIndicadoresFiltrados($parametro);
        // Si no, copia aquí la lógica de consulta y el switch de los otros métodos.

        // Copiando la lógica de consulta para este ejemplo:
        $indicadoresQuery = Indicador::select(
            'id',
            'nombre',
            'programa_derivado',
            'programa',
            'tematica',
            'linea_base',
            'dato_linea_base',
            'unidad_medida',
            'meta_2024', // O meta_2030
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
            // Para JSON, podrías querer una estructura anidada, que Eloquent maneja bien.
            'datosAnuales:id,id_indicador,anio,valor_dato,resultados,observaciones,evidencia,fecha_actualizacion', // Cargar más campos si son útiles
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
                // OJO: Parece haber un espacio extra al final de 'Estado de Derecho, Seguridad y Justicia '
                // Asegúrate de que coincida exactamente con tu base de datos o quita el espacio.
                $indicadoresQuery->where('programa_derivado', 'Plan Estatal de Desarrollo')
                    ->where('programa', 'Estado de Derecho, Seguridad y Justicia'); // Corregido posible espacio extra
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
            return response()->json(['success' => true, 'message' => 'No hay datos disponibles para los filtros seleccionados.', 'data' => []], 200); // Devolver un array vacío es común
        }

        // --- 2. FORMATEO PARA JSON (OPCIONAL, PERO RECOMENDADO PARA CONSISTENCIA) ---
        // Eloquent ya serializa bien a JSON, pero si quieres una estructura específica
        // o añadir/modificar campos, puedes usar ->map() como antes.
        // Si quieres la estructura tal cual la devuelve Eloquent con las relaciones cargadas,
        // puedes omitir el ->map() o hacerlo más simple.

        $rangoDeAniosJson = range(2015, 2030); // O el rango que necesites para el "formato ancho" si lo quieres así en JSON

        $datosParaJson = $indicadoresCollection->map(function ($indicador) use ($rangoDeAniosJson) {
            $datosIndicador = $indicador->toArray(); // Convierte el modelo y sus relaciones cargadas a array

            // Si quieres mantener el formato "ancho" para los datos anuales en el JSON:
            $datosAnualesFormatoAncho = [];
            foreach ($rangoDeAniosJson as $year) {
                $datoAnual = $indicador->datosAnuales->firstWhere('anio', $year);
                $datosAnualesFormatoAncho["dato_{$year}"] = $datoAnual ? $datoAnual->valor_dato : null;
                // Podrías añadir más campos anuales aquí si los cargaste:
                // $datosAnualesFormatoAncho["resultados_{$year}"] = $datoAnual ? $datoAnual->resultados : null;
            }
            // Reemplazar la colección 'datosAnuales' con el formato ancho (si así lo deseas)
            $datosIndicador['datos_anuales_historico'] = $datosAnualesFormatoAncho;
            unset($datosIndicador['datos_anuales']); // Eliminar la colección original si se reemplaza

            // Puedes limpiar o renombrar campos aquí si es necesario
            // Por ejemplo, si quieres que la institución aparezca como un string y no un objeto:
            if (isset($datosIndicador['institucion']) && is_array($datosIndicador['institucion'])) {
                $datosIndicador['nombre_institucion'] = $datosIndicador['institucion']['nombre'] ?? 'N/A';
                unset($datosIndicador['institucion']); // Opcional: eliminar el objeto institución
            }
            if (isset($datosIndicador['ods']) && is_array($datosIndicador['ods'])) {
                $datosIndicador['nombres_ods'] = collect($datosIndicador['ods'])->pluck('nombre')->implode(', ');
                unset($datosIndicador['ods']); // Opcional: eliminar el array de objetos ods
            }

            return $datosIndicador;
        });


        // --- 3. RESPUESTA HTTP PARA JSON ---
        // Laravel automáticamente convertirá la colección (o array) a una respuesta JSON
        // y establecerá la cabecera Content-Type a application/json.
        Log::info("IndicadorController@datosAbiertosPedJson: Enviando respuesta JSON con {$datosParaJson->count()} registros.");
        return response()->json([
            'success' => true,
            'parametro_solicitado' => $parametro,
            'total_registros' => $datosParaJson->count(),
            'data' => $datosParaJson // Aquí va tu colección de indicadores formateada
        ], 200);
    }

    /**
     * Valida la estructura y contenido básico de un archivo Excel sin guardarlo en la BD.
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateFile(Request $request)
    {
        Log::debug('IndicadorController@validateFile: Iniciado.');

        // 1. Validar el archivo subido
        Log::debug('IndicadorController@validateFile: Antes de validar el request del archivo.');
        $validatedRequest = $request->validate([ // Guardar el resultado de la validación
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ], [
            'file.required' => 'Por favor, selecciona un archivo.',
            'file.mimes' => 'El archivo debe ser de tipo Excel (.xlsx, .xls, .csv).',
            'file.max' => 'El archivo no debe superar los 2MB.',
        ]);
        Log::info('IndicadorController@validateFile: Validación del request de archivo exitosa.');

        try {
            // 2. Cargar el archivo
            $file = $validatedRequest['file']; // Usar el archivo validado
            Log::debug('IndicadorController@validateFile: Archivo obtenido del request.', ['original_name' => $file->getClientOriginalName(), 'size' => $file->getSize()]);

            $spreadsheet = IOFactory::load($file);
            Log::debug('IndicadorController@validateFile: Spreadsheet cargado con IOFactory.');
            $sheet = $spreadsheet->getActiveSheet();
            Log::debug('IndicadorController@validateFile: Hoja activa obtenida.');

            $allRowsRaw = $sheet->toArray();
            Log::debug('IndicadorController@validateFile: Todas las filas leídas (raw): ' . count($allRowsRaw) . ' filas.');

            $rows = array_filter($allRowsRaw, function ($row) {
                return count(array_filter($row, function ($cell) {
                    return trim((string) $cell) !== ''; // Convertir celda a string antes de trim
                })) > 0;
            });
            Log::info('IndicadorController@validateFile: Filas filtradas (no vacías): ' . count($rows) . ' filas.');

            if (empty($rows)) {
                Log::warning('IndicadorController@validateFile: El archivo está vacío después de filtrar filas no vacías.');
                return response()->json(['error' => 'El archivo está vacío o no contiene filas con datos.'], 422);
            }

            // 3. Quitar encabezados
            $headers = array_shift($rows);
            Log::debug('IndicadorController@validateFile: Encabezados extraídos.', ['headers' => $headers]);

            if (empty($rows)) {
                Log::warning('IndicadorController@validateFile: El archivo no contiene datos para procesar después de quitar encabezados.');
                return response()->json(['error' => 'El archivo no contiene datos para procesar (solo encabezados).'], 422);
            }
            Log::debug('IndicadorController@validateFile: Número de filas de datos (sin encabezados): ' . count($rows) . ' filas.');

            // 4. Validar campos obligatorios en las filas
            // NUEVA ESTRUCTURA (ACTUALIZADA con Usuario e Institución):
            // 0: ID
            // 1: Nombre
            // 2: Plan Estatal (REQUIRED)
            // 3: Tipo Programa (REQUIRED)
            // 4: Nombre Programa Derivado (REQUIRED)
            // 5: Eje / Programa (REQUIRED)
            // 6: ID Usuario (Opcional o Required - User pidió agregarlos "para que el admin sepa")
            // 7: ID Institución (Opcional o Required)
            // 8: Temática (REQUIRED)
            // ...

            Log::debug('IndicadorController@validateFile: Iniciando validación de campos obligatorios por fila.');
            foreach ($rows as $index => $row) {
                $nombre = $row[1] ?? null;
                $plan = $row[2] ?? null; // Plan
                $tipoPrograma = $row[3] ?? null; // Tipo
                $nombrePrograma = $row[4] ?? null; // Nombre Prog
                $eje = $row[5] ?? null; // Eje
                // $idUsuario = $row[6] ?? null; // IDs son opcionales en validación estricta del archivo? Mejor validarlos si están.
                // $idInstitucion = $row[7] ?? null;

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

            // 5. Almacenar el archivo temporalmente y guardar la RUTA en sesión (no las filas)
            // session(['importRows' => $rows]); // <-- ESTO CAUSABA EL ERROR DE SESSION PAYLOAD

            $path = $file->storeAs('temp_imports', 'import_' . uniqid() . '.' . $file->getClientOriginalExtension());
            session(['importFilePath' => $path]);
            Log::info('IndicadorController@validateFile: Archivo guardado temporalmente en: ' . $path);

            // 6. Respuesta exitosa
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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
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
            // Re-leer el archivo desde disco
            $fullPath = storage_path('app/' . $filePath);
            $spreadsheet = IOFactory::load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();

            // Lógica similar a validateFile para obtener filas
            $allRowsRaw = $sheet->toArray();
            $rows = array_filter($allRowsRaw, function ($row) {
                return count(array_filter($row, function ($cell) {
                    return trim((string) $cell) !== '';
                })) > 0;
            });
            // Quitar encabezados
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
            // $index en foreach sobre array filtrado puede saltar si hubo filas vacías intermedias, 
            // pero normalmente array_filter preserva keys. Resetear keys o usar contador es mejor para logs.
            // Usaremos contador manual si queremos exactitud con el excel original, 
            // pero para logs simples $filasProcesadas está bien.
            //$numeroFilaExcel = $index + 2; // +1 header +1 0-index. Ojo si keys se preservan.

            DB::beginTransaction();
            try {
                // 1. Extraer Datos Clave
                $nombreIndicador     = trim($row[1] ?? '');
                $nombrePlan          = trim($row[2] ?? '');
                $tipoProgramaRaw     = trim($row[3] ?? '');
                $nombreProgramaDeriv = trim($row[4] ?? '');
                $ejePrograma         = trim($row[5] ?? '');

                // Nuevos Campos
                $idUsuario           = isset($row[6]) && trim((string)$row[6]) !== '' ? trim((string)$row[6]) : null;
                $idInstitucion       = isset($row[7]) && trim((string)$row[7]) !== '' ? trim((string)$row[7]) : null;

                // 2. Resolver Plan Estatal
                $planObj = CatPlanEstatalDesarrollo::where('nombre', $nombrePlan)->first();
                if (!$planObj) {
                    throw new \Exception("El Plan Estatal '{$nombrePlan}' no existe en el catálogo.");
                }

                // 3. Resolver Programa Derivado (Polimórfico)
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
                    $indicadorableId = $programaObj->id;
                    $indicadorableType = $modelClass;
                    $programaDerivadoFinal = $programaObj->nombre;
                }

                // Validar existencia de Usuario e Institución si se proporcionaron
                if ($idUsuario && !User::find($idUsuario)) {
                    throw new \Exception("El Usuario con ID '{$idUsuario}' no existe.");
                }
                if ($idInstitucion && !Institucion::find($idInstitucion)) {
                    throw new \Exception("La Institución con ID '{$idInstitucion}' no existe.");
                }

                // 4. Preparar datos del Indicador (Indices desplazados +2)
                $datosIndicador = [
                    'nombre'             => $nombreIndicador,
                    'programa_derivado'  => $programaDerivadoFinal,
                    'programa'           => $ejePrograma,
                    'plan_id'            => $planObj->id,
                    'indicadorable_id'   => $indicadorableId,
                    'indicadorable_type' => $indicadorableType,
                    // Nuevos campos
                    'id_usuario'         => $idUsuario,
                    'id_institucion'     => $idInstitucion,

                    'tematica'           => $row[8] ?? null, // Antes 6 -> Ahora 8
                    'linea_base'         => $row[9] ?? null, // Antes 7 -> Ahora 9
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
                    'fecha_actualizacion' => $row[21] ?? null, // Antes 19 -> Ahora 21
                    'indicador_validado' => false,
                ];

                // Validar datos básicos
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

                // 5. Guardar/Actualizar
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

                // 6. Datos Anuales
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

                // 7. ODS (Ahora Col 20)
                $odsString = $row[20] ?? null; // Antes 18 -> Ahora 20
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

        // Limpieza: Borrar archivo temporal y olvidar sesión
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

        // Add Year Columns 2015-2030
        for ($year = 2015; $year <= 2030; $year++) {
            $headers[] = $year;
        }

        // Set Headers
        $sheet->fromArray([$headers], NULL, 'A1');

        // Style Headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE0E0E0'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
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

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

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

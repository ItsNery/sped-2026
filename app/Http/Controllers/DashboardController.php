<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Institucion;
use App\Models\Indicador;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
// use App\Models\DatoAnualIndicador;
use App\Models\DatoAnual;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{

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

            // // Obtener instituciones con mayor número de indicadores validados
            // $institucionesExcluidas = [1]; // IDs de instituciones a excluir //

            // $institucionesTop = Institucion::whereNotIn('id', $institucionesExcluidas)
            //     ->withCount(['indicadores' => function ($query) {
            //         $query->where('indicador_validado', true);
            //     }])
            //     ->orderByDesc('indicadores_count')
            //     ->take(5)
            //     ->get();
            // $instituciones = $institucionesTop;
            $institucionesExcluidas = [1]; // IDs de instituciones a excluir

            $institucionesTop = Institucion::whereNotIn('id', $institucionesExcluidas)
                // 1. Asegurar que la institución tenga al menos un indicador (cualquier estado)
                ->whereHas('indicadores')
                // 2. Contar solo los indicadores que están validados para el ordenamiento
                ->withCount(['indicadores as indicadores_validados_count' => function ($query) {
                    $query->where('indicador_validado', true);
                }])
                // 3. Ordenar por el número de indicadores validados en orden descendente
                ->orderByDesc('indicadores_validados_count')
                // 4. Tomar el top 5
                ->take(5)
                ->get();

            $instituciones = $institucionesTop; // Asignar a la variable que usas en la vista

            // dd($institucionesTop);
            // Log::info("DashboardController@index: Instituciones Top (con más indicadores validados) obtenidas: " . $institucionesTop->count());
            /**------------------------------------------------------------------------------------------- */
            // Se obtiene el porcentaje de indicadores que son validados
            $totalIndicadoresValidados = Indicador::where('indicador_validado', true)->count();

            $totalIndicadores = Indicador::count();
            $porcentajeValidado = $totalIndicadores > 0 ? ($totalIndicadoresValidados / $totalIndicadores) * 100 : 0;
            /**------------------------------------------------------------------------------------------- */
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
            // Se muestran los mas recientemente agregados o modificados (sin cambios aquí)
            // $indicadoresRecientes = Indicador::orderBy('updated_at', 'desc')
            //     ->take(10)
            //     ->get()
            //     ->map(function ($indicador) {
            //         return [
            //             'id' => $indicador->id,
            //             'nombre' => $indicador->nombre,
            //             'updated_at' => $indicador->updated_at->diffForHumans(),
            //             'tipo' => $indicador->created_at->eq($indicador->updated_at) ? 'Nuevo' : 'Modificado'
            //         ];
            //     });
            $indicadoresRecientes = Indicador::orderBy('updated_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($indicador) {
                    // 1. Verificamos que existan ambos objetos Carbon
                    // 2. Si created_at es null, asumimos que no es "Nuevo" (o el criterio que prefieras)
                    $esNuevo = false;

                    if ($indicador->created_at && $indicador->updated_at) {
                        $esNuevo = $indicador->created_at->eq($indicador->updated_at);
                    }

                    return [
                        'id' => $indicador->id,
                        'nombre' => $indicador->nombre,
                        // Agregamos validación también aquí por seguridad
                        'updated_at' => $indicador->updated_at ? $indicador->updated_at->diffForHumans() : 'Sin fecha',
                        'tipo' => $esNuevo ? 'Nuevo' : 'Modificado'
                    ];
                });
            /**------------------------------------------------------------------------------------------- */
            // Se muestran las instituciones que no tienen indicadores "completos" o "válidos"
            // Un indicador se considera "bueno" si está validado O si tiene datos para todos los años 2015-2030.
            // Una institución es "sin indicadores (buenos)" si NO TIENE indicadores que cumplan esa condición.
            // $aniosRequeridosParaCompletitud = range(2015, 2030); // Años que deben tener datos //

            // $institucionesSinIndicadores = Institucion::whereDoesntHave('indicadores', function ($queryIndicador) use ($aniosRequeridosParaCompletitud) { //
            //     $queryIndicador->where('indicador_validado', true) //
            //         ->orWhere(function ($qSubIndicador) use ($aniosRequeridosParaCompletitud) { //
            //             // Para que un indicador esté "completo" en datos, debe tener un registro DatoAnual
            //             // con valor_dato no nulo para CADA uno de los años requeridos.
            //             foreach ($aniosRequeridosParaCompletitud as $year) {
            //                 $qSubIndicador->whereHas('datosAnuales', function ($qDatoAnual) use ($year) {
            //                     $qDatoAnual->where('anio', $year)
            //                         ->whereNotNull('valor_dato');
            //                 });
            //             }
            //         });
            // })
            //     ->where('id', '!=', 1)
            //     ->get();
            // Obtener instituciones que TIENEN indicadores, y de esos, AL MENOS UNO NO ESTÁ VALIDADO.
            $institucionesSinIndicadores = Institucion::where('id', '!=', 1) // Excluir la institución con ID 1
                ->whereHas('indicadores', function ($queryIndicador) {
                    // Esta condición asegura que la institución tenga al menos un indicador.
                    // Ahora, dentro de esos indicadores, queremos que al menos uno NO esté validado.
                    $queryIndicador->where('indicador_validado', false); // O $queryIndicador->where('indicador_validado', 0);
                })
                ->get();
            // Log::info("DashboardController@index: Instituciones con indicadores no validados encontradas: " . $institucionesSinIndicadores->count());
            /**------------------------------------------------------------------------------------------- */
            // Se obtendrán los indicadores que están próximos a caducar, a tiempo y los que ya caducaron
            $hoy = Carbon::now()->format('Y-m-d'); //

            // MODIFICADO: $getFechaMasReciente para trabajar con la colección de DatoAnual
            $getFechaMasReciente = function ($indicador) { //
                $fechasValidasEnDatosAnuales = new Collection(); //

                // Iterar sobre la colección de DatoAnual del indicador
                foreach ($indicador->datosAnuales as $datoAnual) { //
                    $yearDelDato = $datoAnual->anio; //
                    // Considerar solo fechas de actualización de años relevantes si se desea
                    if ($yearDelDato >= 2020 && $yearDelDato <= Carbon::now()->year) { // Rango de años de interés para fechas de actualización //
                        if (!empty($datoAnual->fecha_actualizacion)) { //
                            try {
                                // Asegúrate de que fecha_actualizacion sea un objeto Carbon o una cadena parseable
                                $fecha = Carbon::parse($datoAnual->fecha_actualizacion); //
                                if ($fecha->isValid()) { //
                                    // Podrías añadir la validación de rango original si es necesaria
                                    // $minFecha = Carbon::createFromFormat('Y-m-d', "$yearDelDato-01-01")->subYear(1);
                                    // $maxFecha = Carbon::createFromFormat('Y-m-d', "$yearDelDato-12-31")->addYears(2);
                                    // if ($fecha->between($minFecha, $maxFecha)) {
                                    //     $fechasValidasEnDatosAnuales->push($fecha);
                                    // }
                                    $fechasValidasEnDatosAnuales->push($fecha); //
                                }
                            } catch (\Exception $e) { //
                                // Log::warning("Fecha inválida en DatoAnual ID {$datoAnual->id} para Indicador ID {$indicador->id}: {$datoAnual->fecha_actualizacion}"); //
                                continue; //
                            }
                        }
                    }
                }

                if ($fechasValidasEnDatosAnuales->isNotEmpty()) { //
                    return $fechasValidasEnDatosAnuales->max()->format('Y-m-d'); //
                }

                // Fallback a la fecha_actualizacion del indicador principal si no hay fechas en datosAnuales
                if (!empty($indicador->fecha_actualizacion)) { //
                    try {
                        return Carbon::parse($indicador->fecha_actualizacion)->format('Y-m-d'); //
                    } catch (\Exception $e) { //
                        // Log::warning("Fecha inválida en Indicador ID {$indicador->id}: {$indicador->fecha_actualizacion}"); //
                        return null; //
                    }
                }
                return null; //
            };

            $indicadoresProximos = new Collection(); //
            $indicadoresATiempo = new Collection(); //
            $indicadoresCaducados = new Collection(); //

            // Esta parte de la lógica de clasificación no cambia fundamentalmente, solo la función que obtiene la fecha
            Indicador::with('datosAnuales')->get()->each(function ($indicador) use ($hoy, $getFechaMasReciente, &$indicadoresProximos, &$indicadoresATiempo, &$indicadoresCaducados) { //
                $fechaMasReciente = $getFechaMasReciente($indicador); //
                $indicador->setAttribute('fecha_actualizacion_calculada', $fechaMasReciente); //

                if ($fechaMasReciente) { //
                    if ($fechaMasReciente > $hoy) { //
                        $indicadoresProximos->push($indicador); //
                    } elseif ($fechaMasReciente == $hoy) { //
                        $indicadoresATiempo->push($indicador); //
                    } else {
                        $indicadoresCaducados->push($indicador); //
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
            /**------------------------------------------------------------------------------------------- */
            // MODIFICADO: Indicadores que tienen datos en cada año
            $years = range(2015, Carbon::now()->year); // Usar hasta el año actual dinámicamente //
            $datosPorAnio = [];

            foreach ($years as $year) { //
                // Consultar el nuevo modelo DatoAnual
                $count = DatoAnual::where('anio', $year) //
                    ->whereNotNull('valor_dato') // Asegúrate que el campo se llame 'valor_dato'
                    ->count(); //
                $datosPorAnio[] = $count; //
            }
            /**------------------------------------------------------------------------------------------- */
            // Indicadores por periodicidad
            $periodicidades = Indicador::select('periodicidad', DB::raw('COUNT(*) as total'))
                ->groupBy('periodicidad')
                ->get();

            $etiquetas_periodicidades = $periodicidades->pluck('periodicidad');
            $values_periodicidades = $periodicidades->pluck('total');

            /**------------------------------------------------------------------------------------------- */
            // Semaforización V2
            // Esta sección depende de $indicador->calcularSemaforizacion().
            // Si esa función dentro del modelo Indicador ya fue actualizada para usar
            // la nueva estructura de DatoAnual, entonces esta parte debería funcionar bien.
            $indicadoresSemaforizacion = Indicador::with('datosAnuales') //
                ->get(); //

            $semaforizacionCounts = [ //
                "Excedido" => 0, //
                "Aceptable" => 0, //
                "Moderado" => 0, //
                "Insuficiente" => 0, //
                "No clasificado" => 0 //
            ]; //

            $indicadoresPorSemaforo = [ //
                "Excedido" => [], //
                "Aceptable" => [], //
                "Moderado" => [], //
                "Insuficiente" => [], //
                "No clasificado" => [] //
            ]; //

            foreach ($indicadoresSemaforizacion as $indicador) { //
                // Asumimos que $indicador->calcularSemaforizacion() ya está adaptado
                // en el modelo Indicador.php para la nueva estructura de datos anuales.
                $resultado = $indicador->calcularSemaforizacion(); //

                // Estos son atributos dinámicos o calculados en el modelo Indicador
                $indicador->anio_ultimo_dato = $resultado['anio_ultimo_dato']; //
                $indicador->ultimo_dato = $resultado['ultimo_dato']; //
                $indicador->avance = $resultado['avance']; //
                $indicador->semaforizacion = $resultado['semaforizacion']; //

                if (isset($semaforizacionCounts[$resultado['semaforizacion']])) { //
                    $semaforizacionCounts[$resultado['semaforizacion']]++; //
                    $indicadoresPorSemaforo[$resultado['semaforizacion']][] = $indicador; //
                } else {
                    // Manejar caso donde la semaforización devuelta no es una clave esperada
                    // Log::warning("Semaforización inesperada '{$resultado['semaforizacion']}' para indicador ID {$indicador->id}"); //
                    // Podrías asignarlo a "No clasificado" por defecto
                    if (isset($semaforizacionCounts["No clasificado"])) { //
                        $semaforizacionCounts["No clasificado"]++; //
                        $indicadoresPorSemaforo["No clasificado"][] = $indicador; //
                    }
                }
            }

            /**------------------------------------------------------------------------------------------- */
            return view('dashboard', compact('instituciones', 'porcentajeValidado', 'totalIndicadoresValidados', 'totalIndicadores', 'porcentajeIncompletos', 'totalIndicadoresIncompletos', 'indicadoresRecientes', 'indicadoresRecientes', 'institucionesSinIndicadores', 'indicadoresProximos', 'indicadoresATiempo', 'indicadoresCaducados', 'datosGraficas', 'years', 'datosPorAnio', 'etiquetas_periodicidades', 'values_periodicidades', 'semaforizacionCounts', 'indicadoresSemaforizacion', 'indicadoresPorSemaforo'));
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
        $categoriasValidas = ["Excedido", "Aceptable", "Moderado", "Insuficiente", "No clasificado"]; //

        if (!in_array($categoria, $categoriasValidas)) { //
            abort(404, "Categoría no válida"); //
        }

        // Asumimos que el accesor $indicador->semaforizacion en el modelo Indicador
        // ya usa el método calcularSemaforizacion() adaptado.
        $indicadores = Indicador::with('datosAnuales') //
            ->get() //
            ->filter(function ($indicador) use ($categoria) { //
                return $indicador->semaforizacion === $categoria; //
            });

        return view('panel-indicadores.indicadores_semaforizacion', compact('indicadores', 'categoria')); //
    }

    /**
     * Muestra los indicadores de un usuario "Enlace", filtrados por estado de validación.
     * Es el endpoint al que apuntan los clics en los gráficos de avance por Enlace.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id ID del usuario Enlace.
     * @return \Illuminate\View\View
     */
    public function mostrarIndicadores(Request $request, $id) //
    {
        $usuario = User::with('instituciones.indicadores')->findOrFail($id); //

        $filtro = $request->query('filtro'); // 'validados' o 'no-validados' //
        $indicadores = collect(); //

        foreach ($usuario->instituciones as $institucion) { //
            $indicadores = $indicadores->merge( //
                $institucion->indicadores->filter(function ($indicador) use ($filtro) { //
                    return $filtro === 'validados' ? $indicador->indicador_validado : !$indicador->indicador_validado; //
                }) //
            );
        }

        return view('users.indicadores', compact('usuario', 'indicadores', 'filtro')); //
    }
}

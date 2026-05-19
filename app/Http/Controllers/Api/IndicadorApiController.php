<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Indicador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IndicadorApiController extends Controller
{
    /**
     * Muestra un listado de los indicadores con sus relaciones (ODS, Institución y Datos Anuales validados).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        Log::info('IndicadorApiController@index: Consulta iniciada.', $request->only(['institucion_id', 'ods_id', 'buscar', 'programa_derivado']));

        try {
            // Consulta base cargando relaciones con restricciones en datosAnuales
            $query = Indicador::with([
                'institucion:id,nombre,titular',
                'ods:id,nombre',
                'datosAnuales' => function ($q) {
                    $q->where('validado', 1)->orderBy('anio', 'asc');
                }
            ]);

            // Filtro por institución responsable
            if ($request->has('institucion_id') && !empty($request->institucion_id)) {
                $query->where('id_institucion', $request->institucion_id);
            }

            // Filtro por ODS relacionado
            if ($request->has('ods_id') && !empty($request->ods_id)) {
                $query->whereHas('ods', function ($q) use ($request) {
                    $q->where('ods.id', $request->ods_id);
                });
            }

            // Filtro por programa derivado
            if ($request->has('programa_derivado') && !empty($request->programa_derivado)) {
                $query->where('programa_derivado', $request->programa_derivado);
            }

            // Filtro de búsqueda (en nombre o descripción)
            if ($request->has('buscar') && !empty($request->buscar)) {
                $buscar = $request->buscar;
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                      ->orWhere('descripcion', 'like', "%{$buscar}%")
                      ->orWhere('tematica', 'like', "%{$buscar}%");
                });
            }

            // Paginación de los resultados
            $perPage = (int) $request->input('per_page', 15);
            if ($perPage < 1 || $perPage > 100) {
                $perPage = 15;
            }

            $indicadores = $query->paginate($perPage);

            // Mapeo para formatear la respuesta JSON limpia
            $indicadoresFormateados = $indicadores->getCollection()->map(function ($indicador) {
                // Cálculo de semaforización y avance en tiempo real usando datos validados
                $semaforo = $indicador->calcularSemaforizacion(true);

                return [
                    'id' => $indicador->id,
                    'nombre' => $indicador->nombre,
                    'slug' => $indicador->slug,
                    'descripcion' => $indicador->descripcion,
                    'programa_derivado' => $indicador->programa_derivado,
                    'programa' => $indicador->programa,
                    'tematica' => $indicador->tematica,
                    'linea_base' => $indicador->linea_base,
                    'dato_linea_base' => $indicador->dato_linea_base,
                    'meta_2024' => $indicador->meta_2024,
                    'unidad_medida' => $indicador->unidad_medida,
                    'fuente' => $indicador->fuente,
                    'liga' => $indicador->liga,
                    'periodicidad' => $indicador->periodicidad,
                    'cobertura' => $indicador->cobertura,
                    'tendencia' => $indicador->tendencia,
                    'formula' => $indicador->formula,
                    'fecha_actualizacion' => $indicador->fecha_actualizacion,
                    'indicador_validado' => (bool) $indicador->indicador_validado,
                    'institucion' => $indicador->institucion ? [
                        'id' => $indicador->institucion->id,
                        'nombre' => $indicador->institucion->nombre,
                        'titular' => $indicador->institucion->titular,
                    ] : null,
                    'ods' => $indicador->ods->map(function ($ods) {
                        return [
                            'id' => $ods->id,
                            'nombre' => $ods->nombre,
                        ];
                    })->values()->toArray(),
                    'datos_anuales' => $indicador->datosAnuales->map(function ($da) {
                        return [
                            'id' => $da->id,
                            'anio' => $da->anio,
                            'valor_dato' => $da->valor_dato,
                            'resultados' => $da->resultados,
                            'observaciones' => $da->observaciones,
                            'fecha_actualizacion' => $da->fecha_actualizacion ? $da->fecha_actualizacion->format('Y-m-d') : null,
                        ];
                    })->values()->toArray(),
                    'avance_real_time' => $semaforo['avance'],
                    'semaforo_real_time' => $semaforo['semaforizacion'],
                    'anio_ultimo_dato_validado' => $semaforo['anio_ultimo_dato'],
                    'ultimo_dato_validado' => $semaforo['ultimo_dato'],
                ];
            });

            // Retornar paginado
            return response()->json([
                'success' => true,
                'total' => $indicadores->total(),
                'per_page' => $indicadores->perPage(),
                'current_page' => $indicadores->currentPage(),
                'last_page' => $indicadores->lastPage(),
                'data' => $indicadoresFormateados
            ], 200);

        } catch (\Exception $e) {
            Log::error('IndicadorApiController@index: Error al consultar indicadores.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error interno al consultar la información de los indicadores.'
            ], 500);
        }
    }

    /**
     * Muestra la información detallada de un indicador específico por su ID o Slug.
     *
     * @param  string  $id_or_slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id_or_slug)
    {
        Log::info('IndicadorApiController@show: Consulta iniciada.', ['id_or_slug' => $id_or_slug]);

        try {
            $indicador = Indicador::with([
                'institucion:id,nombre,titular',
                'ods:id,nombre',
                'datosAnuales' => function ($q) {
                    $q->where('validado', 1)->orderBy('anio', 'asc');
                }
            ])
            ->where(function ($query) use ($id_or_slug) {
                if (is_numeric($id_or_slug)) {
                    $query->where('id', $id_or_slug)->orWhere('slug', $id_or_slug);
                } else {
                    $query->where('slug', $id_or_slug);
                }
            })
            ->first();

            if (!$indicador) {
                return response()->json([
                    'success' => false,
                    'message' => 'El indicador solicitado no existe.'
                ], 404);
            }

            // Cálculo en tiempo real usando datos validados
            $semaforo = $indicador->calcularSemaforizacion(true);

            $detalle = [
                'id' => $indicador->id,
                'nombre' => $indicador->nombre,
                'slug' => $indicador->slug,
                'descripcion' => $indicador->descripcion,
                'programa_derivado' => $indicador->programa_derivado,
                'programa' => $indicador->programa,
                'tematica' => $indicador->tematica,
                'linea_base' => $indicador->linea_base,
                'dato_linea_base' => $indicador->dato_linea_base,
                'meta_2024' => $indicador->meta_2024,
                'unidad_medida' => $indicador->unidad_medida,
                'fuente' => $indicador->fuente,
                'liga' => $indicador->liga,
                'periodicidad' => $indicador->periodicidad,
                'cobertura' => $indicador->cobertura,
                'tendencia' => $indicador->tendencia,
                'formula' => $indicador->formula,
                'fecha_actualizacion' => $indicador->fecha_actualizacion,
                'indicador_validado' => (bool) $indicador->indicador_validado,
                'institucion' => $indicador->institucion ? [
                    'id' => $indicador->institucion->id,
                    'nombre' => $indicador->institucion->nombre,
                    'titular' => $indicador->institucion->titular,
                ] : null,
                'ods' => $indicador->ods->map(function ($ods) {
                    return [
                        'id' => $ods->id,
                        'nombre' => $ods->nombre,
                    ];
                })->values()->toArray(),
                'datos_anuales' => $indicador->datosAnuales->map(function ($da) {
                    return [
                        'id' => $da->id,
                        'anio' => $da->anio,
                        'valor_dato' => $da->valor_dato,
                        'resultados' => $da->resultados,
                        'observaciones' => $da->observaciones,
                        'fecha_actualizacion' => $da->fecha_actualizacion ? $da->fecha_actualizacion->format('Y-m-d') : null,
                    ];
                })->values()->toArray(),
                'avance_real_time' => $semaforo['avance'],
                'semaforo_real_time' => $semaforo['semaforizacion'],
                'anio_ultimo_dato_validado' => $semaforo['anio_ultimo_dato'],
                'ultimo_dato_validado' => $semaforo['ultimo_dato'],
            ];

            return response()->json([
                'success' => true,
                'data' => $detalle
            ], 200);

        } catch (\Exception $e) {
            Log::error('IndicadorApiController@show: Error al consultar indicador.', [
                'id_or_slug' => $id_or_slug,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error interno al consultar el indicador.'
            ], 500);
        }
    }
}

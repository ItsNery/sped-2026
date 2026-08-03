<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Indicador;
use App\Http\Requests\Api\IndicadorIndexRequest;
use App\Http\Resources\IndicadorResource;
use Illuminate\Support\Facades\Log;

class IndicadorApiController extends Controller
{
    /**
     * Muestra un listado de los indicadores con sus relaciones (ODS, Institución y Datos Anuales validados).
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(IndicadorIndexRequest $request)
    {
        Log::info('IndicadorApiController@index: Consulta iniciada.', $request->only(['institucion_id', 'ods_id', 'buscar', 'programa_derivado']));

        try {
            $include = array_values(array_intersect(
                ['institucion', 'ods', 'datos_anuales'],
                array_filter(array_map('trim', explode(',', (string) $request->input('include', 'institucion,ods,datos_anuales'))))
            ));
            $relations = [];
            if (in_array('institucion', $include, true)) {
                $relations[] = 'institucion:id,nombre,titular';
            }
            if (in_array('ods', $include, true)) {
                $relations[] = 'ods:id,nombre';
            }
            if (in_array('datos_anuales', $include, true)) {
                $relations['datosAnuales'] = fn ($q) => $q->where('validado', 1)->orderBy('anio');
            }
            $query = Indicador::with($relations);

            if ($request->filled('institucion_id')) {
                $query->where('id_institucion', $request->integer('institucion_id'));
            }

            if ($request->filled('ods_id')) {
                $query->whereHas('ods', function ($q) use ($request) {
                    $q->where('ods.id', $request->integer('ods_id'));
                });
            }

            if ($request->filled('programa_derivado')) {
                $query->where('programa_derivado', $request->string('programa_derivado')->toString());
            }

            if ($request->filled('buscar')) {
                $buscar = $request->string('buscar')->toString();
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                      ->orWhere('descripcion', 'like', "%{$buscar}%")
                      ->orWhere('tematica', 'like', "%{$buscar}%");
                });
            }

            $sort = $request->input('sort', 'id');
            $direction = $request->input('direction', 'asc');
            $indicadores = $query->orderBy($sort, $direction)->paginate((int) $request->input('per_page', 15));

            $indicadoresFormateados = $indicadores->getCollection()
                ->map(fn ($indicador) => (new IndicadorResource($indicador))->resolve($request))
                ->values();

            return response()->json([
                'success' => true,
                'total' => $indicadores->total(),
                'per_page' => $indicadores->perPage(),
                'current_page' => $indicadores->currentPage(),
                'last_page' => $indicadores->lastPage(),
                'data' => $indicadoresFormateados,
                'includes' => $include,
            ], 200, ['Cache-Control' => 'public, max-age=60']);

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

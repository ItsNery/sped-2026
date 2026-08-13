<?php

namespace App\Services;

use App\Models\CatEje;
use App\Models\CatPlanEstatalDesarrollo;
use App\Models\CatProgramaDerivadoEspecial;
use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\CatProgramaDerivadoRegional;
use App\Models\CatProgramaDerivadoSectorial;
use App\Models\Indicador;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardFilterService
{
    private const PROGRAM_TYPES = [
        'sectoriales' => CatProgramaDerivadoSectorial::class,
        'especiales' => CatProgramaDerivadoEspecial::class,
        'regionales' => CatProgramaDerivadoRegional::class,
        'institucionales' => CatProgramaDerivadoInstitucional::class,
    ];

    public function __construct(private ActivePlanResolver $activePlan)
    {
    }

    public function normalize(Request $request): array
    {
        $requestedPlanId = $request->filled('plan_id')
            ? (int) $request->input('plan_id')
            : null;
        $planId = $this->activePlan->id();

        if ($requestedPlanId && CatPlanEstatalDesarrollo::whereKey($requestedPlanId)->exists()) {
            $planId = $requestedPlanId;
        }

        return [
            'plan_id' => $planId,
            'solo_validados' => $request->boolean('solo_validados', true),
            'anio_desde' => $request->filled('anio_desde') ? (int) $request->input('anio_desde') : null,
            'anio_hasta' => $request->filled('anio_hasta') ? (int) $request->input('anio_hasta') : null,
            'eje_id' => $this->ids($request->input('eje_id', [])),
            'programa_id' => $this->ids($request->input('programa_id', [])),
            'programa_tipo' => array_key_exists($request->input('programa_tipo'), self::PROGRAM_TYPES)
                ? $request->input('programa_tipo')
                : null,
            'institucion_id' => $this->ids($request->input('institucion_id', [])),
            'semaforo' => array_values(array_intersect((array) $request->input('semaforo', []), [
                'Excedido', 'Aceptable', 'Moderado', 'Insuficiente', 'No clasificado',
            ])),
            'calidad' => array_values(array_intersect((array) $request->input('calidad', []), [
                'sin_datos', 'sin_meta', 'sin_tendencia', 'pendiente_validacion',
            ])),
            'buscar' => trim((string) $request->input('buscar', '')) ?: null,
        ];
    }

    public function queryForPlan(int $planId, array $filters, bool $soloValidados): \Illuminate\Database\Eloquent\Builder
    {
        $query = Indicador::forPlan($planId);

        if ($filters['eje_id']) {
            $query->whereHasMorph('indicadorable', CatEje::class, fn ($q) => $q->whereIn('id', $filters['eje_id']));
        }

        if ($filters['programa_tipo']) {
            $class = self::PROGRAM_TYPES[$filters['programa_tipo']];
            if ($filters['programa_id']) {
                if ($class === CatProgramaDerivadoInstitucional::class) {
                    $query->whereHas('programasInstitucionales', fn ($q) => $q->whereIn('id', $filters['programa_id']));
                } else {
                    $query->whereHasMorph('indicadorable', $class, fn ($q) => $q->whereIn('id', $filters['programa_id']));
                }
            } else {
                $query->whereHasMorph('indicadorable', $class);
            }
        }

        if ($filters['institucion_id']) {
            $query->whereIn('id_institucion', $filters['institucion_id']);
        }

        if ($filters['buscar']) {
            $term = '%' . $filters['buscar'] . '%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                    ->orWhere('descripcion', 'like', $term)
                    ->orWhere('tematica', 'like', $term);
            });
        }

        if ($filters['anio_desde'] !== null || $filters['anio_hasta'] !== null) {
            $query->whereHas('datosAnuales', function ($q) use ($filters) {
                if ($filters['anio_desde'] !== null) $q->where('anio', '>=', $filters['anio_desde']);
                if ($filters['anio_hasta'] !== null) $q->where('anio', '<=', $filters['anio_hasta']);
            });
        }

        return $query->with([
            'institucion',
            'usuario',
            'indicadorable',
            'programasInstitucionales',
            'datosAnuales' => function ($query) use ($filters, $soloValidados) {
                if ($soloValidados) $query->where('validado', true);
                if ($filters['anio_desde'] !== null) $query->where('anio', '>=', $filters['anio_desde']);
                if ($filters['anio_hasta'] !== null) $query->where('anio', '<=', $filters['anio_hasta']);
                $query->orderByDesc('anio');
            },
        ]);
    }

    public function filterComputed(Collection $indicadores, array $filters, bool $soloValidados): Collection
    {
        if (!$filters['semaforo'] && !$filters['calidad']) return $indicadores;

        return $indicadores->filter(function ($indicador) use ($filters, $soloValidados) {
            $resultado = $indicador->calcularSemaforizacion($soloValidados);
            $estado = in_array($resultado['semaforizacion'], ['Excedido', 'Aceptable', 'Moderado', 'Insuficiente'], true)
                ? $resultado['semaforizacion']
                : 'No clasificado';
            $datos = $indicador->datosAnuales->filter(fn ($dato) => $dato->valor_dato !== null && trim((string) $dato->valor_dato) !== '');
            $tieneDato = $datos->isNotEmpty();
            $sinMeta = !is_numeric(str_replace(',', '', (string) $indicador->meta)) || (float) $indicador->meta === 0.0;
            $sinTendencia = !in_array(strtolower(trim((string) $indicador->tendencia)), ['mayor es mejor', 'menor es mejor', 'constante'], true);
            $calidad = [];
            if (!$tieneDato) $calidad[] = 'sin_datos';
            if ($sinMeta) $calidad[] = 'sin_meta';
            if ($sinTendencia) $calidad[] = 'sin_tendencia';
            if (!$indicador->indicador_validado) $calidad[] = 'pendiente_validacion';

            return (!$filters['semaforo'] || in_array($estado, $filters['semaforo'], true))
                && (!$filters['calidad'] || array_intersect($filters['calidad'], $calidad));
        })->values();
    }

    public function options(int $planId): array
    {
        return [
            'ejes' => CatEje::where('plan_id', $planId)->orderBy('numero')->get(['id', 'numero', 'nombre']),
            'programas' => collect(self::PROGRAM_TYPES)->mapWithKeys(function ($class, $slug) use ($planId) {
                return [$slug => $class::where('plan_estatal', $planId)->orderBy('nombre')->get(['id', 'nombre'])];
            }),
            'instituciones' => \App\Models\Institucion::whereHas('indicadores', function ($query) use ($planId) {
                $query->where(function ($query) use ($planId) {
                    $query->whereHasMorph('indicadorable', [CatEje::class], fn ($q) => $q->where('plan_id', $planId))
                        ->orWhereHasMorph('indicadorable', [
                            CatProgramaDerivadoSectorial::class,
                            CatProgramaDerivadoEspecial::class,
                            CatProgramaDerivadoRegional::class,
                        ], fn ($q) => $q->where('plan_estatal', $planId))
                        ->orWhereHas('programasInstitucionales', fn ($q) => $q->where('plan_estatal', $planId));
                });
            })->orderBy('nombre')->get(['id', 'nombre']),
        ];
    }

    private function ids($value): array
    {
        return collect((array) $value)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
    }
}

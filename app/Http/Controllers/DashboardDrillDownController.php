<?php

namespace App\Http\Controllers;

use App\Models\CatEje;
use App\Models\CatPlanEstatalDesarrollo;
use App\Models\Indicador;
use App\Services\ActivePlanResolver;
use App\Services\DashboardFilterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardDrillDownController extends Controller
{
    public function __construct(
        private DashboardFilterService $dashboardFilters,
        private ActivePlanResolver $activePlan
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user
                && (int) $user->id_municipio === 0
                && ($user->isAdministrator() || $user->can('ver-panel-avance-general')),
            403
        );

        $filters = $this->dashboardFilters->normalize($request);
        $plan = CatPlanEstatalDesarrollo::find($filters['plan_id']) ?? $this->activePlan->get();

        $indicadores = $this->dashboardFilters
            ->queryForPlan($plan->id, $filters, $filters['solo_validados'])
            ->get();
        $indicadores = $this->dashboardFilters->filterComputed($indicadores, $filters, $filters['solo_validados']);
        $rows = $indicadores->map(fn ($indicador) => $this->row($indicador, $filters['solo_validados']));

        if ($request->boolean('criticas')) {
            $rows = $rows->filter(fn ($row) => $row['prioridad'] !== null && $row['prioridad'] <= 3);
        } elseif ($request->boolean('alertas')) {
            $rows = $rows->filter(fn ($row) => $row['prioridad'] !== null);
        }

        $rows = $rows->sortBy(fn ($row) => match ($request->input('sort')) {
                'nombre' => strtolower($row['nombre']),
                'avance' => $row['avance'] ?? PHP_FLOAT_MAX,
                'institucion' => strtolower($row['institucion']),
                default => $row['prioridad'] . '-' . str_pad((string) ($row['avance'] ?? 999999), 12, '0', STR_PAD_LEFT),
            });

        if ($request->input('direction') === 'desc') {
            $rows = $rows->reverse()->values();
        } else {
            $rows = $rows->values();
        }

        $perPage = min(max((int) $request->input('per_page', 25), 10), 100);
        $page = max((int) $request->input('page', 1), 1);
        $paginator = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('dashboard.drill-down', [
            'plan' => $plan,
            'filters' => $filters,
            'rows' => $paginator,
            'total' => $rows->count(),
        ]);
    }

    private function row(Indicador $indicador, bool $soloValidados): array
    {
        $resultado = $indicador->calcularSemaforizacion($soloValidados);
        $parent = $indicador->indicadorable;
        $programa = $parent?->nombre;
        $eje = $parent instanceof CatEje ? $parent->nombre : null;

        if (!$programa && $indicador->relationLoaded('programasInstitucionales')) {
            $programa = $indicador->programasInstitucionales->first()?->nombre;
        }

        $estado = in_array($resultado['semaforizacion'], ['Excedido', 'Aceptable', 'Moderado', 'Insuficiente'], true)
            ? $resultado['semaforizacion']
            : 'No clasificado';
        $datos = $indicador->datosAnuales->filter(fn ($dato) => $dato->valor_dato !== null && trim((string) $dato->valor_dato) !== '');
        $datosDisponibles = $soloValidados ? $datos->where('validado', true) : $datos;
        $ultimoDato = $datosDisponibles->sortByDesc('anio')->first();
        $proximaActualizacion = null;

        if ($indicador->proxima_actualizacion) {
            try {
                $proximaActualizacion = Carbon::parse($indicador->proxima_actualizacion);
            } catch (\Throwable) {
                $proximaActualizacion = null;
            }
        }

        $motivo = null;
        $prioridad = null;
        if ($resultado['avance'] !== null && $resultado['avance'] < 71) {
            $motivo = 'Avance insuficiente';
            $prioridad = 1;
        } elseif ($proximaActualizacion?->isPast()) {
            $motivo = 'Actualización vencida';
            $prioridad = 2;
        } elseif (!$indicador->indicador_validado) {
            $motivo = 'Pendiente de validación';
            $prioridad = 3;
        } elseif (!$ultimoDato) {
            $motivo = 'Sin dato anual';
            $prioridad = 4;
        } elseif (!is_numeric(str_replace(',', '', (string) $indicador->meta_2024)) || (float) $indicador->meta_2024 === 0.0) {
            $motivo = 'Sin meta válida';
            $prioridad = 5;
        } elseif (!in_array(strtolower(trim((string) $indicador->tendencia)), ['mayor es mejor', 'menor es mejor', 'constante'], true)) {
            $motivo = 'Sin tendencia definida';
            $prioridad = 6;
        }

        return [
            'id' => $indicador->id,
            'slug' => $indicador->slug,
            'nombre' => $indicador->nombre,
            'institucion' => $indicador->institucion?->nombre ?? 'Sin institución',
            'usuario' => $indicador->usuario?->name ?? 'Sin responsable',
            'eje' => $eje ?? 'Sin eje',
            'programa' => $programa ?? 'Sin programa',
            'validado' => (bool) $indicador->indicador_validado,
            'avance' => $resultado['avance'],
            'estado' => $estado,
            'motivo' => $motivo ?? 'Sin alerta',
            'anio' => $resultado['anio_ultimo_dato'],
            'prioridad' => $prioridad,
        ];
    }
}

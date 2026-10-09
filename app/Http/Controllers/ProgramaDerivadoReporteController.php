<?php

namespace App\Http\Controllers;

use App\Models\CatProgramaDerivadoEspecial;
use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\CatProgramaDerivadoRegional;
use App\Models\CatProgramaDerivadoSectorial;
use App\Services\ActivePlanResolver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\View\View;

class ProgramaDerivadoReporteController extends Controller
{
    /** @var array<string, class-string<\Illuminate\Database\Eloquent\Model>> */
    private const PROGRAM_TYPES = [
        'sectoriales' => CatProgramaDerivadoSectorial::class,
        'especiales' => CatProgramaDerivadoEspecial::class,
        'regionales' => CatProgramaDerivadoRegional::class,
        'institucionales' => CatProgramaDerivadoInstitucional::class,
    ];

    public function __construct(private ActivePlanResolver $activePlan)
    {
        $this->middleware(function ($request, $next) {
            abort_unless(auth()->user()?->isAdministrator(), 403);

            return $next($request);
        });
    }

    public function index(): View
    {
        $programasPorTipo = collect(self::PROGRAM_TYPES)
            ->map(fn (string $class) => $class::query()
                ->where('plan_estatal', $this->activePlan->id())
                ->withCount('indicadores')
                ->orderBy('nombre')
                ->get())
            ->all();

        return view('panel-reportes-programas.index', compact('programasPorTipo'));
    }

    public function show(string $tipo, int $programa): View
    {
        $programClass = self::PROGRAM_TYPES[$tipo] ?? abort(404);
        $programaDerivado = $programClass::query()
            ->where('plan_estatal', $this->activePlan->id())
            ->findOrFail($programa);

        /** @var Relation $indicadoresRelation */
        $indicadoresRelation = $programaDerivado->indicadores();
        $indicadores = $indicadoresRelation
            ->with([
                'institucion:id,nombre',
                'datosAnuales' => fn ($query) => $query->where('validado', true)->orderBy('anio'),
            ])
            ->orderBy('nombre')
            ->get();

        return view('panel-reportes-programas.reporte', compact('indicadores', 'programaDerivado', 'tipo'));
    }
}

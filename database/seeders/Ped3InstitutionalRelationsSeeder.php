<?php

namespace Database\Seeders;

use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\Indicador;
use Database\Seeders\Support\Ped3InstitutionalManifest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class Ped3InstitutionalRelationsSeeder extends Seeder
{
    private const PLAN_ID = 3;
    private const PIVOT_TABLE = 'programa_institucional_indicador';
    private const PENDING_INDICATORS = [
        'porcentajededocentesevaluadosconniveldedesempenosatisfactorio' => 'No incluido en el Excel de indicadores nuevos.',
    ];

    public function run(): void
    {
        if (!Schema::hasTable(self::PIVOT_TABLE)) {
            throw new RuntimeException('Falta la tabla programa_institucional_indicador. Ejecuta primero la migracion del pivote.');
        }

        $manifest = Ped3InstitutionalManifest::load();
        $programs = $this->indexPrograms();
        $indicators = $this->indexIndicators();
        $relations = [];
        $missing = [];
        $pending = [];
        $ambiguous = [];

        foreach ($manifest['relations'] as $relation) {
            $program = $programs[$relation['program_key']] ?? null;
            if (!$program) {
                $missing[] = "Programa: {$relation['program_name']}";
                continue;
            }

            $candidates = $indicators[$relation['indicator_key']] ?? [];
            if (!$candidates) {
                if (isset(self::PENDING_INDICATORS[$relation['indicator_key']])) {
                    $pending[] = [
                        'indicator' => $relation['indicator_name'],
                        'program' => $relation['program_name'],
                        'reason' => self::PENDING_INDICATORS[$relation['indicator_key']],
                    ];
                    continue;
                }

                $missing[] = "Indicador: {$relation['indicator_name']} | Programa: {$relation['program_name']}";
                continue;
            }

            if (count($candidates) > 1) {
                $ambiguous[] = [
                    'indicator' => $relation['indicator_name'],
                    'program' => $relation['program_name'],
                    'ids' => array_map(fn ($indicator) => $indicator->id, $candidates),
                ];
                continue;
            }

            $relations[] = [
                'indicador_id' => $candidates[0]->id,
                'programa_institucional_id' => $program->id,
            ];
        }

        $existing = DB::table(self::PIVOT_TABLE)
            ->get(['indicador_id', 'programa_institucional_id'])
            ->mapWithKeys(fn ($relation) => [$this->relationKey($relation->indicador_id, $relation->programa_institucional_id) => true]);

        $newRelations = array_values(array_filter(
            $relations,
            fn (array $relation) => !$existing->has($this->relationKey($relation['indicador_id'], $relation['programa_institucional_id']))
        ));

        $this->report($manifest, $relations, $newRelations, $missing, $pending, $ambiguous);

        if ($missing || $ambiguous) {
            throw new RuntimeException('No se insertaron relaciones: el manifiesto tiene indicadores o programas faltantes/ambiguos.');
        }

        DB::transaction(function () use ($newRelations): void {
            foreach (array_chunk($newRelations, 500) as $chunk) {
                $now = now();
                $rows = array_map(fn (array $relation) => [
                    ...$relation,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $chunk);

                DB::table(self::PIVOT_TABLE)->insert($rows);
            }
        });

        $this->command?->info('Relaciones institucionales PED 3 insertadas: ' . count($newRelations));
    }

    private function indexPrograms(): array
    {
        $indexed = [];
        $duplicates = [];

        foreach (CatProgramaDerivadoInstitucional::where('plan_estatal', self::PLAN_ID)->get() as $program) {
            $key = Ped3InstitutionalManifest::programKey($program->nombre);

            if (isset($indexed[$key])) {
                $duplicates[$key][] = $program->id;
                continue;
            }

            $indexed[$key] = $program;
        }

        if ($duplicates) {
            throw new RuntimeException('Hay programas institucionales duplicados por nombre normalizado: ' . json_encode($duplicates));
        }

        return $indexed;
    }

    private function indexIndicators(): array
    {
        $all = [];
        $ped3 = [];
        $ped3Ids = Indicador::forPlan(self::PLAN_ID)->pluck('id')->all();
        $ped3IdSet = array_fill_keys($ped3Ids, true);

        foreach (Indicador::query()->get(['id', 'nombre']) as $indicator) {
            $key = Ped3InstitutionalManifest::indicatorKey($indicator->nombre);
            $all[$key][] = $indicator;

            if (isset($ped3IdSet[$indicator->id])) {
                $ped3[$key][] = $indicator;
            }
        }

        foreach ($all as $key => $candidates) {
            if (!isset($ped3[$key])) {
                $ped3[$key] = $candidates;
            }
        }

        return $ped3;
    }

    private function relationKey(int $indicatorId, int $programId): string
    {
        return $indicatorId . ':' . $programId;
    }

    private function report(array $manifest, array $relations, array $newRelations, array $missing, array $pending, array $ambiguous): void
    {
        $this->command?->line('Fuente: ' . $manifest['file']);
        $this->command?->line('Programas en manifiesto: ' . count($manifest['programs']));
        $this->command?->line('Relaciones en manifiesto: ' . count($manifest['relations']));
        $this->command?->line('Relaciones resueltas: ' . count($relations));
        $this->command?->line('Relaciones nuevas: ' . count($newRelations));

        if ($pending) {
            $this->command?->warn('Indicadores pendientes: ' . json_encode($pending, JSON_UNESCAPED_UNICODE));
        }

        if ($missing) {
            $this->command?->error('Registros faltantes: ' . json_encode($missing, JSON_UNESCAPED_UNICODE));
        }

        if ($ambiguous) {
            $this->command?->error('Indicadores ambiguos: ' . json_encode($ambiguous, JSON_UNESCAPED_UNICODE));
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\CatProgramaDerivadoRegional;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class CleanupRegionalIndicators extends Command
{
    protected $signature = 'sped:cleanup-regional
        {--plan=3 : ID del plan estatal cuyos indicadores regionales se revisaran}
        {--execute : Ejecutar la eliminacion; sin esta opcion solo se genera el respaldo y dry-run}
        {--backup= : Ruta opcional para el snapshot JSON}';

    protected $description = 'Respalda y elimina indicadores regionales de un PED sin eliminar sus programas.';

    private const DEPENDENCIES = [
        'datos_anuales' => 'id_indicador',
        'indicador_ods' => 'id_indicador',
        'carrusel_indicadors' => 'id_indicador',
        'datos_anuales_indicadores' => 'id_indicador',
        'programa_institucional_indicador' => 'indicador_id',
    ];

    public function handle(): int
    {
        $planId = (int) $this->option('plan');
        $programs = CatProgramaDerivadoRegional::where('plan_estatal', $planId)->get();

        if ($programs->isEmpty()) {
            $this->error("No existen programas regionales para el PED {$planId}.");
            return self::FAILURE;
        }

        $programIds = $programs->modelKeys();
        $indicatorRows = DB::table('indicadors')
            ->where('indicadorable_type', CatProgramaDerivadoRegional::class)
            ->whereIn('indicadorable_id', $programIds)
            ->get();
        $indicatorIds = $indicatorRows->pluck('id')->all();
        $dependencyRows = $this->dependencyRows($indicatorIds);
        $backupPath = $this->writeSnapshot($planId, $programs, $indicatorRows, $dependencyRows);

        $this->info("PED {$planId} - programas regionales: {$programs->count()}");
        $this->line('Indicadores regionales afectados: ' . count($indicatorIds));
        $this->line('Datos anuales afectados: ' . count($dependencyRows['datos_anuales'] ?? []));
        $this->line('Relaciones ODS afectadas: ' . count($dependencyRows['indicador_ods'] ?? []));
        $this->line('Registros de carrusel afectados: ' . count($dependencyRows['carrusel_indicadors'] ?? []));
        $this->line('Relaciones institucionales inesperadas: ' . count($dependencyRows['programa_institucional_indicador'] ?? []));
        $this->line('Snapshot: ' . $backupPath);

        if (!$this->option('execute')) {
            $this->comment('DRY-RUN: no se realizaron cambios y las evidencias no se eliminaran.');
            return self::SUCCESS;
        }

        if (!$indicatorIds) {
            $this->comment('No hay indicadores regionales que eliminar. Los programas se conservaron.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($indicatorIds): void {
            foreach (self::DEPENDENCIES as $table => $column) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                DB::table($table)->whereIn($column, $indicatorIds)->delete();
            }

            DB::table('indicadors')->whereIn('id', $indicatorIds)->delete();
        });

        $this->info('Indicadores regionales eliminados: ' . count($indicatorIds));
        $this->info('Programas regionales conservados: ' . $programs->count());
        $this->comment('Las evidencias fisicas se conservaron.');

        return self::SUCCESS;
    }

    private function dependencyRows(array $indicatorIds): array
    {
        $rows = [];

        foreach (self::DEPENDENCIES as $table => $column) {
            if (!$indicatorIds || !Schema::hasTable($table)) {
                $rows[$table] = [];
                continue;
            }

            $rows[$table] = DB::table($table)
                ->whereIn($column, $indicatorIds)
                ->get()
                ->map(fn ($row) => (array) $row)
                ->all();
        }

        return $rows;
    }

    private function writeSnapshot(int $planId, $programs, $indicatorRows, array $dependencyRows): string
    {
        $tables = ['cat_programas_derivados_regionales', 'indicadors', ...array_keys(self::DEPENDENCIES)];
        $structure = [];

        foreach (array_unique($tables) as $table) {
            if (Schema::hasTable($table)) {
                $structure[$table] = Schema::getColumnListing($table);
            }
        }

        $snapshot = [
            'created_at' => now()->toIso8601String(),
            'scope' => [
                'plan_id' => $planId,
                'indicatorable_type' => CatProgramaDerivadoRegional::class,
            ],
            'structure' => $structure,
            'programs' => $programs->map(fn ($program) => $program->toArray())->all(),
            'indicators' => $indicatorRows->map(fn ($row) => (array) $row)->all(),
            'dependencies' => $dependencyRows,
            'evidence_policy' => 'Los archivos fisicos de evidencia se conservan; solo se respaldan sus referencias.',
        ];

        $path = $this->option('backup')
            ?: storage_path('app/backups/regionales-ped-' . $planId . '-' . now()->format('Ymd-His') . '.json');

        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException("No se pudo crear el directorio del respaldo: {$directory}");
        }

        file_put_contents(
            $path,
            json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        return $path;
    }
}

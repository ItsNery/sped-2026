<?php

namespace App\Console\Commands;

use App\Services\CandidateExcelReconciliationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

class ApplyCandidateExcelData extends Command
{
    protected $signature = 'sped:apply-candidate-excel
        {--indicators= : Ruta al Excel de indicadores}
        {--institutions= : Ruta al Excel de instituciones}
        {--execute : Aplicar altas y actualizaciones; sin esta opcion solo se genera el reporte}
        {--report= : Ruta opcional para el reporte JSON}';

    protected $description = 'Aplica Excel de instituciones e indicadores sin eliminar registros ausentes.';

    public function handle(CandidateExcelReconciliationService $reconciler): int
    {
        $indicatorPath = $this->option('indicators') ?: public_path('indicadors0210.xls');
        $institutionPath = $this->option('institutions') ?: public_path('instituciones0210.xls');
        $execute = (bool) $this->option('execute');

        try {
            $report = $reconciler->apply($indicatorPath, $institutionPath, $execute);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $reportPath = $this->option('report') ?: storage_path('app/reconciliation/apply-candidate-excel-' . now()->format('Ymd-His') . '.json');
        File::ensureDirectoryExists(dirname($reportPath));
        File::put($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $this->warn($execute ? 'Modo EJECUCION: se aplicaron altas y actualizaciones.' : 'Modo DRY-RUN: no se realizaron cambios.');
        foreach ($report['tables'] as $name => $table) {
            $this->line("{$name}: {$table['new_in_excel_count']} altas, {$table['changed_count']} actualizaciones, {$table['missing_in_excel_count']} ausentes conservados.");
        }
        $this->info('Reporte: ' . $reportPath);

        return self::SUCCESS;
    }
}

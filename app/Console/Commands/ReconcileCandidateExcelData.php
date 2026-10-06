<?php

namespace App\Console\Commands;

use App\Services\CandidateExcelReconciliationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

class ReconcileCandidateExcelData extends Command
{
    protected $signature = 'sped:reconcile-candidate-excel
        {--indicators= : Ruta al Excel de indicadores}
        {--institutions= : Ruta al Excel de instituciones}
        {--report= : Ruta opcional para el reporte JSON}';

    protected $description = 'Compara los Excel candidatos contra la base actual sin modificar datos.';

    public function handle(CandidateExcelReconciliationService $reconciler): int
    {
        $indicatorPath = $this->option('indicators') ?: public_path('indicadors0210.xls');
        $institutionPath = $this->option('institutions') ?: public_path('instituciones0210.xls');

        try {
            $report = $reconciler->reconcile($indicatorPath, $institutionPath);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $reportPath = $this->option('report') ?: storage_path('app/reconciliation/candidate-excel-' . now()->format('Ymd-His') . '.json');
        File::ensureDirectoryExists(dirname($reportPath));
        File::put($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        foreach ($report['tables'] as $name => $table) {
            $this->line("{$name}: {$table['source_rows']} Excel, {$table['database_rows']} BD, {$table['new_in_excel_count']} nuevos, {$table['missing_in_excel_count']} ausentes, {$table['changed_count']} con cambios.");
        }

        $this->info('Reporte: ' . $reportPath);

        return self::SUCCESS;
    }
}

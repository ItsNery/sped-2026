<?php

namespace App\Console\Commands;

use App\Models\CatPlanEstatalDesarrollo;
use App\Services\HistoricalPedImportService;
use Illuminate\Console\Command;

class ImportHistoricalPed extends Command
{
    protected $signature = 'sped:import-historical
        {--plan=1 : ID del plan estatal que se importara}
        {--file= : Ruta al archivo BD_Completa.xlsx}
        {--execute : Ejecutar cambios; sin esta opcion solo se simula}';

    protected $description = 'Importa indicadores historicos del PED y sus relaciones de catalogo.';

    public function handle(HistoricalPedImportService $importer): int
    {
        $planId = (int) $this->option('plan');
        $plan = CatPlanEstatalDesarrollo::find($planId);

        if (!$plan) {
            $this->error("No existe el plan estatal con ID {$planId}.");
            return self::FAILURE;
        }

        $filePath = $this->option('file')
            ?: public_path('docs/datos-abiertos/2019-2024/ped/datos-generales/BD_Completa.xlsx');
        $execute = (bool) $this->option('execute');

        $this->info("Plan: {$plan->id} - {$plan->nombre}");
        $this->info('Archivo: ' . $filePath);
        $this->warn($execute ? 'Modo EJECUCION' : 'Modo DRY-RUN: no se realizaran cambios');

        $report = $importer->run($planId, $filePath, $execute);

        $this->newLine();
        $this->line('Filas fuente: ' . $report['source_rows']);
        $this->line('Filas validas: ' . $report['valid_rows']);
        $this->line('Filas invalidas: ' . $report['invalid_rows']);
        $this->line('Valores anuales: ' . $report['annual_values']);
        $this->line('Ejes unicos: ' . $report['unique_catalogs']['ejes']);
        $this->line('Programas unicos: ' . $report['unique_catalogs']['programas']);
        $this->line('Tipos: ' . json_encode($report['rows_by_type'], JSON_UNESCAPED_UNICODE));

        if ($report['invalid_ods']) {
            $this->warn('ODS no resueltos: ' . json_encode($report['invalid_ods'], JSON_UNESCAPED_UNICODE));
        }

        if ($report['errors']) {
            foreach ($report['errors'] as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $reportDirectory = storage_path('app/imports');
        if (!is_dir($reportDirectory)) {
            mkdir($reportDirectory, 0775, true);
        }

        $reportPath = $reportDirectory . '/historical-ped-' . $planId . '-' . now()->format('Ymd-His') . '.json';
        file_put_contents(
            $reportPath,
            json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
        $this->line('Reporte: ' . $reportPath);

        if ($execute) {
            $this->info('Creado: ' . json_encode($report['created'], JSON_UNESCAPED_UNICODE));
            $this->info('Actualizado: ' . json_encode($report['updated'], JSON_UNESCAPED_UNICODE));
        }

        return self::SUCCESS;
    }
}

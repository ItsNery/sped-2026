<?php

namespace App\Console\Commands;

use App\Services\Ped3InstitutionalIndicatorImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportPed3InstitutionalIndicators extends Command
{
    protected $signature = 'sped:import-ped3-institutional
        {--file= : Ruta al archivo Excel de indicadores institucionales PED 3}
        {--execute : Aplicar los cambios; sin esta opción solamente se valida}
        {--report= : Ruta opcional para el reporte JSON}';

    protected $description = 'Importa indicadores institucionales PED 3 desde un Excel validado.';

    public function handle(Ped3InstitutionalIndicatorImportService $importer): int
    {
        $filePath = $this->option('file') ?: public_path('Indicadores nuevos para carga en el SPED.xlsx');
        $execute = (bool) $this->option('execute');

        $this->info('Archivo: ' . $filePath);
        $this->warn($execute ? 'Modo EJECUCIÓN' : 'Modo DRY-RUN: no se realizarán cambios');

        try {
            $result = $importer->run($filePath, $execute);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        foreach ($result['errors'] as $error) {
            $this->error($error);
        }

        $this->line('Filas: ' . $result['rows']);
        $this->line('Indicadores por crear: ' . $result['created']);
        $this->line('Indicadores por actualizar: ' . $result['updated']);

        $reportPath = $this->option('report') ?: storage_path('app/imports/ped3-institutional-' . now()->format('Ymd-His') . '.json');
        File::ensureDirectoryExists(dirname($reportPath));
        File::put($reportPath, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->line('Reporte: ' . $reportPath);

        return $result['errors'] ? self::FAILURE : self::SUCCESS;
    }
}

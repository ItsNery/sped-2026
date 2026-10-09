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

        if ($report['unresolved_institutions']) {
            $this->warn('Instituciones sin asignacion: ' . json_encode($report['unresolved_institutions'], JSON_UNESCAPED_UNICODE));
        }

        foreach ($report['field_issues'] ?? [] as $field => $issues) {
            if ($issues) {
                $this->warn("{$field} faltante en fuente: " . count($issues));
            }
        }

        foreach ($report['field_warnings'] ?? [] as $field => $warnings) {
            if ($warnings) {
                $this->warn("{$field} requiere revision: " . count($warnings));
            }
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

        if ($execute && $planId === 2) {
            $pendingPath = base_path('documentation/pendientes-instituciones-ped2.md');
            $this->writePendingInstitutionsMarkdown($pendingPath, $report);
            $this->line('Pendientes de instituciones: ' . $pendingPath);
        }

        if ($execute && in_array($planId, [1, 2], true)) {
            $auditPath = base_path("documentation/auditoria-campos-ped{$planId}.md");
            $this->writeFieldAuditMarkdown($auditPath, $planId, $report);
            $this->line('Auditoria de campos: ' . $auditPath);
        }

        if ($execute) {
            $this->info('Creado: ' . json_encode($report['created'], JSON_UNESCAPED_UNICODE));
            $this->info('Actualizado: ' . json_encode($report['updated'], JSON_UNESCAPED_UNICODE));
        }

        return self::SUCCESS;
    }

    private function writePendingInstitutionsMarkdown(string $path, array $report): void
    {
        $rows = $report['unresolved_institution_indicators'] ?? [];
        $lines = [
            '# Instituciones Pendientes: PED 2',
            '',
            'Estos indicadores fueron importados sin `id_institucion` porque la institución no fue especificada en el archivo o no existe en el catálogo actual.',
            '',
            '- No se creó ninguna institución nueva automáticamente.',
            '- Las relaciones deberán revisarse y asignarse posteriormente desde el catálogo central.',
            '- El importador dejó estos indicadores con `id_institucion = null`.',
            '',
            '## Resumen',
            '',
            '| Institución del archivo | Indicadores pendientes |',
            '| --- | ---: |',
        ];

        foreach ($report['unresolved_institutions'] ?? [] as $institution => $count) {
            $lines[] = '| ' . $this->markdownCell($institution) . ' | ' . $count . ' |';
        }

        $lines = array_merge($lines, [
            '',
            '## Indicadores',
            '',
            '| Fila Excel | Institución del archivo | Indicador | Tipo | Programa o región | Temática |',
            '| ---: | --- | --- | --- | --- | --- |',
        ]);

        foreach ($rows as $row) {
            $lines[] = '| ' . ($row['source_row'] ?? '')
                . ' | ' . $this->markdownCell($row['institution'] ?? '')
                . ' | ' . $this->markdownCell($row['indicator'] ?? '')
                . ' | ' . $this->markdownCell($row['type'] ?? '')
                . ' | ' . $this->markdownCell($row['program'] ?? '')
                . ' | ' . $this->markdownCell($row['tematica'] ?? '') . ' |';
        }

        file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL);
    }

    private function markdownCell(string $value): string
    {
        return str_replace(['|', "\r", "\n"], ['\\|', ' ', ' '], trim($value));
    }

    private function writeFieldAuditMarkdown(string $path, int $planId, array $report): void
    {
        $labels = [
            'unidad_medida' => 'Unidad de medida',
            'cobertura' => 'Cobertura',
            'tendencia' => 'Tendencia',
        ];
        $lines = [
            "# Auditoria de campos: PED {$planId}",
            '',
            'Este reporte identifica campos vacios, `NULL`, `N/D` u otro marcador equivalente en el archivo fuente.',
            'No implica que deban rellenarse automaticamente; los valores deben confirmarse contra la fuente oficial.',
            '',
            '## Normalizaciones aplicadas',
            '',
            '- La columna `Unidad de Medida` se importa desde su encabezado normalizado correcto.',
            '- Los valores fuente `NULL` se conservan como `N/D` y no como texto literal.',
            '- El valor fuente `Ascendente` se normaliza como `Mayor es Mejor`.',
            '',
            '## Resumen',
            '',
            '| Campo | Casos en fuente |',
            '| --- | ---: |',
        ];

        foreach ($labels as $field => $label) {
            $lines[] = '| ' . $label . ' | ' . count($report['field_issues'][$field] ?? []) . ' |';
        }

        if (!empty($report['field_warnings']['cobertura'])) {
            $lines = array_merge($lines, [
                '',
                '## Valores sospechosos para revisar',
                '',
                '| Fila Excel | Indicador | Tipo | Programa o region | Periodicidad | Cobertura |',
                '| ---: | --- | --- | --- | --- | --- |',
            ]);

            foreach ($report['field_warnings']['cobertura'] as $warning) {
                $lines[] = '| ' . ($warning['source_row'] ?? '')
                    . ' | ' . $this->markdownCell($warning['indicator'] ?? '')
                    . ' | ' . $this->markdownCell($warning['type'] ?? '')
                    . ' | ' . $this->markdownCell($warning['program'] ?? '')
                    . ' | ' . $this->markdownCell($warning['periodicity'] ?? '')
                    . ' | ' . $this->markdownCell($warning['value'] ?? '') . ' |';
            }
        }

        foreach ($labels as $field => $label) {
            $lines = array_merge($lines, [
                '',
                '## ' . $label,
                '',
                '| Fila Excel | Indicador | Tipo | Programa o region | Valor fuente |',
                '| ---: | --- | --- | --- | --- |',
            ]);

            foreach ($report['field_issues'][$field] ?? [] as $issue) {
                $lines[] = '| ' . ($issue['source_row'] ?? '')
                    . ' | ' . $this->markdownCell($issue['indicator'] ?? '')
                    . ' | ' . $this->markdownCell($issue['type'] ?? '')
                    . ' | ' . $this->markdownCell($issue['program'] ?? '')
                    . ' | ' . $this->markdownCell($issue['value'] ?? '') . ' |';
            }
        }

        file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL);
    }
}

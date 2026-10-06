<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class CandidateExcelReconciliationCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_reports_new_missing_and_changed_records_without_writing_to_the_database(): void
    {
        DB::table('instituciones')->insert([
            ['id' => 1, 'nombre' => 'Institucion actualizada', 'titular' => 'Titular anterior', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nombre' => 'Solo en base', 'titular' => 'Titular', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('indicadors')->insert($this->indicator(1, 'Indicador actualizado', 'Antes'));
        DB::table('indicadors')->insert($this->indicator(2, 'Solo en base', 'Sin cambios'));

        $indicatorPath = $this->spreadsheet('indicadores', [
            array_merge(['id' => 1], $this->indicator(1, 'Indicador actualizado', 'Despues')),
            array_merge(['id' => 3], $this->indicator(3, 'Nuevo en Excel', 'Nuevo')),
        ]);
        $institutionPath = $this->spreadsheet('instituciones', [
            ['id' => 1, 'nombre' => 'Institucion actualizada', 'titular' => 'Titular nuevo'],
            ['id' => 3, 'nombre' => 'Nueva en Excel', 'titular' => 'Titular'],
        ]);
        $reportPath = storage_path('framework/testing/reconciliation-' . uniqid() . '.json');

        $this->artisan('sped:reconcile-candidate-excel', [
            '--indicators' => $indicatorPath,
            '--institutions' => $institutionPath,
            '--report' => $reportPath,
        ])->assertSuccessful();

        $report = json_decode((string) file_get_contents($reportPath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $report['tables']['indicadors']['changed_count']);
        $this->assertSame('Despues', $report['tables']['indicadors']['changed'][0]['changes']['descripcion']['excel']);
        $this->assertSame('Antes', $report['tables']['indicadors']['changed'][0]['changes']['descripcion']['database']);
        $this->assertSame([['id' => '3', 'label' => 'Nuevo en Excel']], $report['tables']['indicadors']['new_in_excel']);
        $this->assertSame([['id' => '2', 'label' => 'Solo en base']], $report['tables']['indicadors']['missing_in_excel']);
        $this->assertSame(1, $report['tables']['instituciones']['changed_count']);
        $this->assertSame('Titular nuevo', $report['tables']['instituciones']['changed'][0]['changes']['titular']['excel']);
        $this->assertSame(2, DB::table('instituciones')->count());
        $this->assertSame(2, DB::table('indicadors')->count());
    }

    public function test_rejects_a_spreadsheet_missing_a_required_column(): void
    {
        $indicatorPath = $this->spreadsheet('indicadores-invalidos', [['id' => 1, 'nombre' => 'Indicador']]);
        $institutionPath = $this->spreadsheet('instituciones', [['id' => 1, 'nombre' => 'Institucion', 'titular' => 'Titular']]);

        $this->artisan('sped:reconcile-candidate-excel', [
            '--indicators' => $indicatorPath,
            '--institutions' => $institutionPath,
        ])->expectsOutputToContain('no contiene la columna requerida: slug')->assertFailed();
    }

    /**
     * @return array<string, mixed>
     */
    private function indicator(int $id, string $name, string $description): array
    {
        return [
            'id' => $id,
            'nombre' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'programa_derivado' => 'Programa derivado',
            'programa' => 'Programa',
            'cod_tematica' => 'Tema',
            'tematica' => 'Tematica',
            'id_institucion' => null,
            'linea_base' => '2024',
            'dato_linea_base' => '1',
            'meta_2024' => '2',
            'meta_anio' => 2030,
            'meta' => '3',
            'unidad_medida' => 'Porcentaje',
            'id_usuario' => null,
            'fuente' => 'Fuente',
            'liga' => null,
            'descripcion' => $description,
            'periodicidad' => 'Anual',
            'periodo' => null,
            'cobertura' => 'Estatal',
            'tendencia' => 'Mayor es mejor',
            'fecha_actualizacion' => '2026-01-01',
            'resultados' => null,
            'formula' => 'Formula',
            'indicador_validado' => true,
            'indicadorable_type' => 'App\\Models\\CatEje',
            'indicadorable_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function spreadsheet(string $name, array $rows): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([array_keys($rows[0]), ...array_map('array_values', $rows)]);

        $path = storage_path('framework/testing/' . $name . '-' . uniqid() . '.xlsx');
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }
}

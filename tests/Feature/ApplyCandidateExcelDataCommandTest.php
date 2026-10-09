<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ApplyCandidateExcelDataCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dry_run_reports_changes_without_writing_to_the_database(): void
    {
        [$indicatorPath, $institutionPath] = $this->sourceFiles();
        $reportPath = storage_path('framework/testing/apply-dry-run-' . uniqid() . '.json');

        $this->artisan('sped:apply-candidate-excel', [
            '--indicators' => $indicatorPath,
            '--institutions' => $institutionPath,
            '--report' => $reportPath,
        ])->assertSuccessful();

        $report = json_decode((string) file_get_contents($reportPath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('dry-run', $report['mode']);
        $this->assertSame(1, $report['tables']['instituciones']['new_in_excel_count']);
        $this->assertSame(1, $report['tables']['indicadors']['changed_count']);
        $this->assertSame('Antes', DB::table('indicadors')->where('id', 1)->value('descripcion'));
        $this->assertSame(1, DB::table('instituciones')->count());
    }

    public function test_execute_creates_and_updates_records_without_deleting_absent_rows(): void
    {
        [$indicatorPath, $institutionPath] = $this->sourceFiles();

        $this->artisan('sped:apply-candidate-excel', [
            '--indicators' => $indicatorPath,
            '--institutions' => $institutionPath,
            '--execute' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('instituciones', ['id' => 1, 'titular' => 'Titular actualizado']);
        $this->assertDatabaseHas('instituciones', ['id' => 2, 'nombre' => 'Nueva institucion']);
        $this->assertDatabaseHas('indicadors', ['id' => 1, 'descripcion' => 'Despues']);
        $this->assertDatabaseHas('indicadors', ['id' => 2, 'nombre' => 'Nuevo en Excel', 'id_institucion' => 2]);
        $this->assertDatabaseHas('indicadors', ['id' => 3, 'nombre' => 'Solo en base']);
    }

    public function test_execute_rejects_indicators_with_an_unknown_institution(): void
    {
        $indicatorPath = $this->spreadsheet('invalid-institution-indicators', [
            $this->indicator(1, 'Indicador invalido', 'Nuevo', 99),
        ]);
        $institutionPath = $this->spreadsheet('invalid-institution-institutions', [
            ['id' => 2, 'nombre' => 'Nueva institucion', 'titular' => 'Titular nuevo'],
        ]);

        $this->artisan('sped:apply-candidate-excel', [
            '--indicators' => $indicatorPath,
            '--institutions' => $institutionPath,
            '--execute' => true,
        ])->expectsOutputToContain('No existe la institucion con ID 99')->assertFailed();

        $this->assertDatabaseMissing('instituciones', ['id' => 2]);
        $this->assertDatabaseMissing('indicadors', ['id' => 1]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function sourceFiles(): array
    {
        DB::table('instituciones')->insert(['id' => 1, 'nombre' => 'Institucion existente', 'titular' => 'Titular anterior', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('indicadors')->insert($this->indicator(1, 'Indicador existente', 'Antes', null));
        DB::table('indicadors')->insert($this->indicator(3, 'Solo en base', 'Sin cambios', null));

        return [
            $this->spreadsheet('apply-indicators', [
                $this->indicator(1, 'Indicador existente', 'Despues', null),
                $this->indicator(2, 'Nuevo en Excel', 'Nuevo', 2),
            ]),
            $this->spreadsheet('apply-institutions', [
                ['id' => 1, 'nombre' => 'Institucion existente', 'titular' => 'Titular actualizado'],
                ['id' => 2, 'nombre' => 'Nueva institucion', 'titular' => 'Titular nuevo'],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function indicator(int $id, string $name, string $description, ?int $institutionId): array
    {
        return [
            'id' => $id,
            'nombre' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'programa_derivado' => 'Programa derivado',
            'programa' => 'Programa',
            'cod_tematica' => 'Tema',
            'tematica' => 'Tematica',
            'id_institucion' => $institutionId,
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

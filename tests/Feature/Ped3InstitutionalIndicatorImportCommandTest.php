<?php

namespace Tests\Feature;

use App\Models\CatPlanEstatalDesarrollo;
use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\Indicador;
use App\Models\Institucion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class Ped3InstitutionalIndicatorImportCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_validates_rows_without_writing_indicators(): void
    {
        [$program, $institution] = $this->catalogs();
        $filePath = $this->spreadsheet($program->nombre, $institution->nombre);

        $this->artisan('sped:import-ped3-institutional', ['--file' => $filePath])
            ->expectsOutput('Indicadores por crear: 1')
            ->assertSuccessful();

        $this->assertDatabaseMissing('indicadors', ['nombre' => 'Indicador institucional de prueba']);
    }

    public function test_execute_creates_indicator_and_repeating_import_updates_it_without_duplicates(): void
    {
        [$program, $institution] = $this->catalogs();
        $filePath = $this->spreadsheet($program->nombre, $institution->nombre);

        $this->artisan('sped:import-ped3-institutional', ['--file' => $filePath, '--execute' => true])
            ->expectsOutput('Indicadores por crear: 1')
            ->assertSuccessful();

        $indicator = Indicador::where('nombre', 'Indicador institucional de prueba')->firstOrFail();

        $this->assertDatabaseHas('programa_institucional_indicador', [
            'indicador_id' => $indicator->id,
            'programa_institucional_id' => $program->id,
        ]);
        $this->assertDatabaseHas('datos_anuales', [
            'id_indicador' => $indicator->id,
            'anio' => 2025,
            'valor_dato' => 25,
            'validado' => true,
        ]);

        $this->artisan('sped:import-ped3-institutional', ['--file' => $filePath, '--execute' => true])
            ->expectsOutput('Indicadores por actualizar: 1')
            ->assertSuccessful();

        $this->assertSame(1, Indicador::where('nombre', 'Indicador institucional de prueba')->count());
    }

    public function test_rejects_unknown_program_without_writing_indicators(): void
    {
        [, $institution] = $this->catalogs();
        $filePath = $this->spreadsheet('Programa institucional inexistente', $institution->nombre);

        $this->artisan('sped:import-ped3-institutional', ['--file' => $filePath, '--execute' => true])
            ->expectsOutputToContain("No existe el programa institucional 'Programa institucional inexistente' en PED 3.")
            ->assertFailed();

        $this->assertDatabaseMissing('indicadors', ['nombre' => 'Indicador institucional de prueba']);
    }

    /**
     * @return array{0: CatProgramaDerivadoInstitucional, 1: Institucion}
     */
    private function catalogs(): array
    {
        DB::table('cat_planes_estatales_desarrollo')->insert([
            'id' => 3,
            'nombre' => 'PED 2024-2030',
            'gobernador' => 'Gobernador de prueba',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $plan = CatPlanEstatalDesarrollo::findOrFail(3);
        $programAttributes = [
            'nombre' => 'Programa Institucional de Prueba',
            'tipo' => 'Institucional',
            'grupo' => 'Organismos Auxiliares',
            'imagen' => 'imagen.png',
            'descripcion' => 'Descripción de prueba',
            'color' => '#691A32',
            'plan_estatal' => $plan->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('cat_programas_derivados_institucionales')->insert($programAttributes);
        $program = CatProgramaDerivadoInstitucional::where('nombre', $programAttributes['nombre'])->firstOrFail();
        $institution = Institucion::create([
            'nombre' => 'Institución de Prueba',
            'titular' => 'Titular de prueba',
        ]);

        return [$program, $institution];
    }

    private function spreadsheet(string $program, string $institution): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            [
                'Nombre Indicador', 'Programa Derivado', 'Programa', 'Temática', 'Linea Base (Año)',
                'Linea Base (Dato)', 'Unidad de Medida', 'Meta 2030', 'Fuente', 'Enlace', 'Descripción',
                'Periodicidad', 'Cobertura', 'Tendencia', 'Resultados Generales', 'Fórmula',
                'Fecha Actualización Indicador', 'Institución', 'ODS', 'Dato 2025',
            ],
            [
                'Indicador institucional de prueba', $program, 'PI.1', 'Temática de prueba', '2025', '25',
                'Porcentaje', '50', 'Fuente de prueba', '', 'Descripción de prueba', 'Anual', 'Estatal',
                'Mayor es mejor', '', 'Dato / meta', '2026-01-01', $institution, '', '25',
            ],
        ]);

        $path = storage_path('framework/testing/ped3-institutional-' . uniqid() . '.xlsx');
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }
}

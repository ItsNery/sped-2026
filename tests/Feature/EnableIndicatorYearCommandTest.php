<?php

namespace Tests\Feature;

use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\DatoAnual;
use App\Models\Indicador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EnableIndicatorYearCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_reports_missing_indicators_without_creating_annual_records(): void
    {
        [, $missingIndicator] = $this->indicators();

        $this->artisan('sped:enable-indicator-year', ['year' => 2025])
            ->expectsOutput('Indicadores sin registro: 2')
            ->expectsOutputToContain('Modo DRY-RUN')
            ->assertSuccessful();

        $this->assertDatabaseMissing('datos_anuales', [
            'id_indicador' => $missingIndicator->id,
            'anio' => 2025,
        ]);
    }

    public function test_execute_creates_only_missing_records_and_preserves_existing_data(): void
    {
        [$existingIndicator, $missingIndicator] = $this->indicators();
        DatoAnual::create([
            'id_indicador' => $existingIndicator->id,
            'anio' => 2025,
            'valor_dato' => 42.5,
            'resultados' => 'Dato previamente capturado',
            'validado' => true,
            'modificado' => true,
        ]);

        $this->artisan('sped:enable-indicator-year', ['year' => 2025, '--execute' => true])
            ->expectsOutput('Indicadores sin registro: 1')
            ->expectsOutput('Registros anuales creados: 1')
            ->assertSuccessful();

        $this->assertDatabaseHas('datos_anuales', [
            'id_indicador' => $missingIndicator->id,
            'anio' => 2025,
            'valor_dato' => null,
            'validado' => false,
            'modificado' => false,
        ]);
        $this->assertDatabaseHas('datos_anuales', [
            'id_indicador' => $existingIndicator->id,
            'anio' => 2025,
            'valor_dato' => 42.5,
            'resultados' => 'Dato previamente capturado',
            'validado' => true,
            'modificado' => true,
        ]);

        $this->artisan('sped:enable-indicator-year', ['year' => 2025, '--execute' => true])
            ->expectsOutput('Indicadores sin registro: 0')
            ->expectsOutput('Registros anuales creados: 0')
            ->assertSuccessful();
    }

    /**
     * @return array{0: Indicador, 1: Indicador}
     */
    private function indicators(): array
    {
        DB::table('cat_planes_estatales_desarrollo')->insert([
            'id' => 3,
            'nombre' => 'PED 2024-2030',
            'gobernador' => 'Gobernador de prueba',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('cat_programas_derivados_institucionales')->insert([
            'nombre' => 'Programa Institucional de Prueba',
            'tipo' => 'Institucional',
            'imagen' => 'programa.png',
            'descripcion' => 'Programa de prueba',
            'color' => '#691A32',
            'grupo' => 'Organismos Auxiliares',
            'plan_estatal' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $program = CatProgramaDerivadoInstitucional::where('nombre', 'Programa Institucional de Prueba')->firstOrFail();
        $attributes = [
            'programa_derivado' => $program->nombre,
            'programa' => 'PI.1',
            'cod_tematica' => '1',
            'tematica' => 'Temática de prueba',
            'linea_base' => '2024',
            'dato_linea_base' => '0',
            'meta_2024' => '100',
            'unidad_medida' => 'Porcentaje',
            'fuente' => 'Fuente de prueba',
            'descripcion' => 'Descripción de prueba',
            'periodicidad' => 'Anual',
            'cobertura' => 'Estatal',
            'tendencia' => 'Mayor es mejor',
            'fecha_actualizacion' => '2025-01-01',
            'formula' => 'Dato / meta',
        ];
        $existingIndicator = Indicador::create([...$attributes, 'nombre' => 'Indicador con dato 2025']);
        $missingIndicator = Indicador::create([...$attributes, 'nombre' => 'Indicador sin dato 2025']);
        $program->indicadores()->attach([$existingIndicator->id, $missingIndicator->id]);

        return [$existingIndicator, $missingIndicator];
    }
}

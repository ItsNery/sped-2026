<?php

namespace Tests\Feature;

use App\Models\CatEje;
use App\Models\CatPlanEstatalDesarrollo;
use App\Models\DatoAnual;
use App\Models\Indicador;
use App\Models\Institucion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnnualDataDecimalInputTest extends TestCase
{
    use RefreshDatabase;

    public function test_annual_data_modals_use_a_dot_decimal_input(): void
    {
        $plan = CatPlanEstatalDesarrollo::create([
            'nombre' => 'PED de prueba',
            'gobernador' => 'Gobernador de prueba',
        ]);
        config()->set('sped.active_plan_id', $plan->id);
        $eje = CatEje::create([
            'nombre' => 'Eje de prueba',
            'numero' => 1,
            'plan_id' => $plan->id,
        ]);
        $institution = Institucion::create([
            'nombre' => 'Institución de prueba',
            'titular' => 'Titular de prueba',
        ]);
        $indicator = $eje->indicadores()->create($this->indicatorAttributes($institution));
        DatoAnual::create([
            'id_indicador' => $indicator->id,
            'anio' => 2025,
            'valor_dato' => 433.16,
        ]);
        $administrator = User::factory()->create();
        $administrator->assignRole(Role::findOrCreate('Administrador', 'web'));

        $content = $this->actingAs($administrator)
            ->get(route('panel-indicadores.show', $indicator->id))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="modal_valor_dato_2025"', $content);
        $this->assertSame(2, substr_count(
            $content,
            'type="text" inputmode="decimal" pattern="[0-9]*[.]?[0-9]*"'
        ));
        $this->assertStringContainsString('value="433.16"', $content);
    }

    /**
     * @return array<string, int|string>
     */
    private function indicatorAttributes(Institucion $institution): array
    {
        return [
            'nombre' => 'Indicador de prueba',
            'programa_derivado' => 'Plan de prueba',
            'programa' => 'Eje de prueba',
            'cod_tematica' => 'T1',
            'tematica' => 'Temática de prueba',
            'linea_base' => 2023,
            'dato_linea_base' => '10',
            'meta_anio' => 2030,
            'meta' => '20',
            'unidad_medida' => 'Porcentaje',
            'id_institucion' => $institution->id,
            'fuente' => 'Fuente de prueba',
            'descripcion' => 'Descripción de prueba',
            'periodicidad' => 'Anual',
            'cobertura' => 'Estatal',
            'tendencia' => 'Mayor es mejor',
            'fecha_actualizacion' => '2025-01-01',
            'formula' => 'Dato / meta',
        ];
    }
}

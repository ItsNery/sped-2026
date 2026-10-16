<?php

namespace Tests\Feature;

use App\Models\CatEje;
use App\Models\CatPlanEstatalDesarrollo;
use App\Models\DatoAnual;
use App\Models\Indicador;
use App\Models\Institucion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IndicatorIndexAnnualValidationStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_validated_when_the_technical_sheet_and_all_annual_data_are_validated(): void
    {
        $indicator = $this->createIndicator(true);
        DatoAnual::create([
            'id_indicador' => $indicator->id,
            'anio' => 2025,
            'valor_dato' => 10,
            'validado' => true,
            'modificado' => false,
        ]);

        $this->actingAs($this->administrator())
            ->get(route('panel-indicadores.index'))
            ->assertSeeText('Validado')
            ->assertDontSeeText('Revisar')
            ->assertSee('title="No hay modificaciones pendientes en los datos anuales."', false);
    }

    public function test_shows_technical_sheet_pending_when_all_annual_data_are_validated_but_the_technical_sheet_is_not(): void
    {
        $indicator = $this->createIndicator(false);
        DatoAnual::create([
            'id_indicador' => $indicator->id,
            'anio' => 2025,
            'valor_dato' => 10,
            'validado' => true,
            'modificado' => false,
        ]);

        $this->actingAs($this->administrator())
            ->get(route('panel-indicadores.index'))
            ->assertSeeText('Ficha por validar')
            ->assertDontSeeText('Validado')
            ->assertSee('title="Los datos anuales están validados; falta validar la ficha técnica."', false);
    }

    public function test_shows_review_when_an_annual_data_item_is_pending_validation(): void
    {
        $indicator = $this->createIndicator(true);
        DatoAnual::create([
            'id_indicador' => $indicator->id,
            'anio' => 2025,
            'valor_dato' => 10,
            'validado' => false,
            'modificado' => true,
        ]);

        $this->actingAs($this->administrator())
            ->get(route('panel-indicadores.index'))
            ->assertSeeText('Revisar')
            ->assertDontSeeText('Validado')
            ->assertSee('title="Uno o más datos anuales fueron modificados y requieren validación."', false);
    }

    public function test_dependency_liaison_sees_a_validated_badge_with_a_tooltip(): void
    {
        $indicator = $this->createIndicator(true);
        DatoAnual::create([
            'id_indicador' => $indicator->id,
            'anio' => 2025,
            'valor_dato' => 10,
            'validado' => true,
            'modificado' => false,
        ]);
        $liaison = User::factory()->create(['id_institucion' => $indicator->id_institucion]);
        $liaison->assignRole(Role::findOrCreate('Enlace dependencia', 'web'));
        $liaison->givePermissionTo(Permission::findOrCreate('ver-indicador', 'web'));

        $this->actingAs($liaison)
            ->get(route('panel-indicadores.index'))
            ->assertSee('badge rounded-pill bg-success px-3 py-2', false)
            ->assertSee('data-bs-toggle="tooltip"', false)
            ->assertSee('title="Todos los datos anuales del indicador están validados."', false);
    }

    private function administrator(): User
    {
        $administrator = User::factory()->create();
        $administrator->assignRole(Role::findOrCreate('Administrador', 'web'));
        $administrator->givePermissionTo(Permission::findOrCreate('ver-indicador', 'web'));

        return $administrator;
    }

    private function createIndicator(bool $indicatorValidated): Indicador
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

        return $eje->indicadores()->create([
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
            'indicador_validado' => $indicatorValidated,
        ]);
    }
}

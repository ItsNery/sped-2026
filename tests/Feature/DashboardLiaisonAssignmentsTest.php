<?php

namespace Tests\Feature;

use App\Models\CatEje;
use App\Models\CatPlanEstatalDesarrollo;
use App\Models\Indicador;
use App\Models\Institucion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardLiaisonAssignmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_see_enlace_assignments_in_the_detail_page(): void
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
            'nombre' => 'Dependencia asignada',
            'titular' => 'Titular de prueba',
        ]);
        $liaison = User::factory()->create(['name' => 'Enlace de prueba']);
        $liaison->assignRole(Role::findOrCreate('Enlace', 'web'));
        $liaison->instituciones()->attach($institution);
        $indicator = $eje->indicadores()->create($this->indicatorAttributes($institution));
        $administrator = User::factory()->create();
        $administrator->assignRole(Role::findOrCreate('Administrador', 'web'));

        $this->actingAs($administrator)
            ->get(route('dashboard'))
            ->assertSeeText('Asignación de enlaces')
            ->assertSeeText('Ver detalle');

        $this->actingAs($administrator)
            ->get(route('dashboard.asignaciones-enlaces', ['plan_id' => $plan->id]))
            ->assertSeeText('Indicadores asignados a enlaces')
            ->assertSeeText($liaison->name)
            ->assertSeeText($institution->nombre)
            ->assertSeeText($indicator->nombre)
            ->assertSee('id="enlace-filter"', false)
            ->assertSee('id="dependencia-filter"', false);

        $this->actingAs($administrator)
            ->getJson(route('dashboard.asignaciones-enlaces.dependencias', ['enlace_id' => $liaison->id]))
            ->assertOk()
            ->assertJsonPath('dependencias.0.id', $institution->id)
            ->assertJsonPath('dependencias.0.nombre', $institution->nombre);
    }

    public function test_detail_page_excludes_enlace_dependencia_and_forbids_non_administrators(): void
    {
        $plan = CatPlanEstatalDesarrollo::create([
            'nombre' => 'PED de prueba',
            'gobernador' => 'Gobernador de prueba',
        ]);
        config()->set('sped.active_plan_id', $plan->id);
        $dependencyLiaison = User::factory()->create(['name' => 'Enlace dependencia de prueba']);
        $dependencyLiaison->assignRole(Role::findOrCreate('Enlace dependencia', 'web'));
        $administrator = User::factory()->create();
        $administrator->assignRole(Role::findOrCreate('Administrador', 'web'));

        $this->actingAs($administrator)
            ->get(route('dashboard.asignaciones-enlaces', ['plan_id' => $plan->id]))
            ->assertDontSeeText($dependencyLiaison->name);

        $this->actingAs($dependencyLiaison)
            ->get(route('dashboard.asignaciones-enlaces', ['plan_id' => $plan->id]))
            ->assertForbidden();
    }

    /**
     * @return array<string, int|string>
     */
    private function indicatorAttributes(Institucion $institution): array
    {
        return [
            'nombre' => 'Indicador asignado',
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

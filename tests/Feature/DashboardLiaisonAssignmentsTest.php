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

    public function test_tracking_prioritizes_outdated_indicators_and_shows_active_additional_filters(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-06-01'));
        $plan = CatPlanEstatalDesarrollo::create(['nombre' => 'PED de prueba', 'gobernador' => 'Gobernador']);
        config()->set('sped.active_plan_id', $plan->id);
        $eje = CatEje::create(['nombre' => 'Eje de prueba', 'numero' => 1, 'plan_id' => $plan->id]);
        $institucion = Institucion::create(['nombre' => 'Dependencia', 'titular' => 'Titular']);
        $eje->indicadores()->create(array_merge($this->indicatorAttributes($institucion), ['nombre' => 'A vigente', 'fecha_actualizacion' => '2027-01-01']));
        $eje->indicadores()->create(array_merge($this->indicatorAttributes($institucion), ['nombre' => 'Z vencido', 'fecha_actualizacion' => '2025-01-01']));
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Administrador', 'web'));

        $this->actingAs($user)->get(route('dashboard.drill-down', ['plan_id' => $plan->id, 'seguimiento' => 1]))
            ->assertOk()->assertSeeInOrder(['Z vencido', 'A vigente'])
            ->assertSeeText('Atención de actualización')->assertSeeText('Más filtros');

        $this->get(route('dashboard.drill-down', ['plan_id' => $plan->id, 'seguimiento' => 1, 'periodicidad' => 'Anual']))
            ->assertOk()->assertSeeText('(1 activo)')->assertSeeText('Periodicidad: Anual')
            ->assertSeeText('Limpiar filtros adicionales');
    }

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
        $unassignedInstitution = Institucion::create([
            'nombre' => 'Dependencia sin enlace',
            'titular' => 'Titular sin enlace',
        ]);
        $unassignedIndicator = $eje->indicadores()->create(array_merge(
            $this->indicatorAttributes($unassignedInstitution),
            ['nombre' => 'Indicador sin enlace']
        ));
        $administrator = User::factory()->create();
        $administrator->assignRole(Role::findOrCreate('Administrador', 'web'));

        $this->actingAs($administrator)
            ->get(route('dashboard'))
            ->assertSeeText('Asignación de enlaces')
            ->assertSeeText('Ver detalle')
            ->assertSeeText('Seguimiento de actualizaciones');

        $this->actingAs($administrator)
            ->get(route('dashboard.drill-down', ['plan_id' => $plan->id, 'seguimiento' => 1]))
            ->assertOk()
            ->assertSeeText('Seguimiento de actualizaciones')
            ->assertSeeText($indicator->nombre)
            ->assertSeeText('01/01/2025')
            ->assertSeeText('Desactualizado')
            ->assertSeeText('indicadores desactualizados')
            ->assertSeeText('Ver indicadores que requieren atención');

        foreach (['dashboard', 'dashboard.drill-down'] as $route) {
            $this->actingAs($administrator)
                ->get(route($route, ['plan_id' => $plan->id, 'solo_validados' => 0, 'anio_desde' => 2024, 'anio_hasta' => 2026]))
                ->assertOk()
                ->assertSeeText('Datos: Todos los registrados')
                ->assertSeeText('Desde: 2024')
                ->assertSeeText('Hasta: 2026');
        }

        $this->actingAs($administrator)->get(route('dashboard.drill-down', ['plan_id' => $plan->id, 'limpiar_filtros' => 1]))
            ->assertSeeText('Datos: Solo validados')
            ->assertSeeText('Desde: Sin límite')
            ->assertSeeText('Hasta: Sin límite');

        $this->actingAs($administrator)
            ->get(route('dashboard.drill-down', ['plan_id' => $plan->id, 'seguimiento' => 1, 'estado_plazo' => 'Sin fecha confiable']))
            ->assertOk()
            ->assertDontSeeText($indicator->nombre);

        $this->actingAs($administrator)
            ->get(route('dashboard.asignaciones-enlaces', ['plan_id' => $plan->id]))
            ->assertSeeText('Indicadores asignados a enlaces')
            ->assertSeeText($liaison->name)
            ->assertSeeText($institution->nombre)
            ->assertSeeText($indicator->nombre)
            ->assertSeeText($unassignedInstitution->nombre)
            ->assertSeeText($unassignedIndicator->nombre)
            ->assertSee(route('panel-indicadores.show', ['indicador' => $indicator->id, 'plan_id' => $plan->id]), false)
            ->assertSeeText('Sin enlace asignado')
            ->assertSee('id="enlace-filter"', false)
            ->assertSee('id="dependencia-filter"', false);

        $this->actingAs($administrator)
            ->getJson(route('dashboard.asignaciones-enlaces.dependencias', ['enlace_id' => $liaison->id]))
            ->assertOk()
            ->assertJsonPath('dependencias.0.id', $institution->id)
            ->assertJsonPath('dependencias.0.nombre', $institution->nombre);
    }

    public function test_dashboard_filters_persist_between_dashboard_and_details_until_cleared(): void
    {
        $plan = CatPlanEstatalDesarrollo::create(['nombre' => 'PED persistente', 'gobernador' => 'Gobernador']);
        config()->set('sped.active_plan_id', $plan->id);
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Administrador', 'web'));
        $this->actingAs($user);

        $this->get(route('dashboard', ['plan_id' => $plan->id, 'solo_validados' => 0, 'anio_desde' => 2024, 'anio_hasta' => 2026]))->assertOk();
        foreach (['dashboard.drill-down', 'dashboard'] as $route) {
            $this->get(route($route))->assertOk()
                ->assertSeeText('Datos: Todos los registrados')
                ->assertSeeText('Desde: 2024')
                ->assertSeeText('Hasta: 2026');
        }

        $this->get(route('dashboard', ['solo_validados' => 1, 'anio_desde' => '', 'anio_hasta' => '']))
            ->assertSeeText('Datos: Solo validados')
            ->assertSeeText('Desde: Sin límite')
            ->assertSeeText('Hasta: Sin límite');

        $this->get(route('dashboard', ['solo_validados' => 0, 'anio_desde' => 2024]))->assertOk();
        $this->get(route('dashboard', ['limpiar_filtros' => 1]))
            ->assertSeeText('Datos: Solo validados')
            ->assertSeeText('Desde: Sin límite');
        $this->get(route('dashboard.drill-down'))
            ->assertSeeText('Datos: Solo validados')
            ->assertSeeText('Desde: Sin límite');
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

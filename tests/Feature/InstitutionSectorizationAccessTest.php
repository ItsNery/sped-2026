<?php

namespace Tests\Feature;

use App\Models\CatEje;
use App\Models\CatPlanEstatalDesarrollo;
use App\Models\Indicador;
use App\Models\Institucion;
use App\Models\User;
use App\Policies\IndicadorPolicy;
use App\Services\InstitutionAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InstitutionSectorizationAccessTest extends TestCase
{
    use RefreshDatabase;

    private InstitutionAccessService $institutionAccess;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (['ver-indicador', 'editar-indicador', 'borrar-indicador', 'agregar-dato-anual', 'editar-indicador-anual', 'validar-indicador'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $this->institutionAccess = app(InstitutionAccessService::class);
    }

    public function test_access_flows_from_a_mother_institution_to_its_children_only(): void
    {
        $mother = $this->institution('Dependencia madre');
        $child = $this->institution('Institución hija', $mother);
        $sibling = $this->institution('Institución hermana', $mother);
        $unrelated = $this->institution('Institución no sectorizada');

        $managerRole = Role::findOrCreate('Enlace', 'web');
        $manager = User::factory()->create();
        $manager->assignRole($managerRole);
        $manager->instituciones()->attach($mother);

        $childUser = User::factory()->create(['id_institucion' => $child->id]);

        $this->assertEqualsCanonicalizing(
            [$mother->id, $child->id, $sibling->id],
            $this->institutionAccess->visibleInstitutionIds($manager)->all()
        );
        $this->assertSame([$child->id], $this->institutionAccess->visibleInstitutionIds($childUser)->all());
        $this->assertFalse($this->institutionAccess->canViewInstitution($manager, $unrelated->id));
        $this->assertFalse($this->institutionAccess->canViewInstitution($childUser, $mother->id));
        $this->assertFalse($this->institutionAccess->canViewInstitution($childUser, $sibling->id));
    }

    public function test_mother_can_capture_and_validate_but_not_manage_child_definition(): void
    {
        $mother = $this->institution('Dependencia madre');
        $child = $this->institution('Institución hija', $mother);
        $managerRole = Role::findOrCreate('Enlace', 'web');
        $managerRole->givePermissionTo([
            'ver-indicador',
            'editar-indicador',
            'borrar-indicador',
            'agregar-dato-anual',
            'editar-indicador-anual',
            'validar-indicador',
        ]);
        $manager = User::factory()->create();
        $manager->assignRole($managerRole);
        $manager->instituciones()->attach($mother);

        $motherIndicator = new Indicador(['id_institucion' => $mother->id]);
        $childIndicator = new Indicador(['id_institucion' => $child->id]);
        $policy = app(IndicadorPolicy::class);

        $this->assertTrue($policy->view($manager, $childIndicator));
        $this->assertTrue($policy->addAnnualData($manager, $childIndicator));
        $this->assertTrue($policy->updateAnnualData($manager, $childIndicator));
        $this->assertTrue($policy->validate($manager, $childIndicator));
        $this->assertFalse($policy->update($manager, $childIndicator));
        $this->assertFalse($policy->delete($manager, $childIndicator));
        $this->assertTrue($policy->update($manager, $motherIndicator));
        $this->assertTrue($policy->delete($manager, $motherIndicator));
    }

    public function test_http_actions_allow_downward_access_and_block_reverse_access(): void
    {
        $mother = $this->institution('Dependencia madre');
        $child = $this->institution('Institución hija', $mother);
        $managerRole = Role::findOrCreate('Enlace', 'web');
        $managerRole->givePermissionTo([
            'ver-indicador',
            'agregar-dato-anual',
            'editar-indicador-anual',
            'validar-indicador',
        ]);
        $manager = User::factory()->create();
        $manager->assignRole($managerRole);
        $manager->instituciones()->attach($mother);

        $childRole = Role::findOrCreate('Enlace dependencia', 'web');
        $childRole->givePermissionTo([
            'ver-indicador',
            'agregar-dato-anual',
            'editar-indicador-anual',
            'validar-indicador',
        ]);
        $childUser = User::factory()->create(['id_institucion' => $child->id]);
        $childUser->assignRole($childRole);

        $motherIndicator = $this->activePlanIndicator($mother, 'Indicador de la madre');
        $childIndicator = $this->activePlanIndicator($child, 'Indicador de la hija');

        $this->actingAs($manager)
            ->get(route('panel-indicadores.show', $childIndicator->id))
            ->assertOk();
        $this->actingAs($manager)
            ->post(route('indicador.storeAnual', $childIndicator->id), [
                'anio' => 2026,
                'valor_dato' => 42,
            ])
            ->assertRedirect();
        $this->actingAs($manager)
            ->patch(route('indicadores.toggleValidacion', $childIndicator->id), ['estado' => 1])
            ->assertRedirect();

        $this->assertDatabaseHas('datos_anuales', [
            'id_indicador' => $childIndicator->id,
            'anio' => 2026,
            'valor_dato' => 42,
        ]);
        $this->assertTrue((bool) $childIndicator->fresh()->indicador_validado);

        $this->actingAs($childUser)
            ->get(route('panel-indicadores.show', $motherIndicator->id))
            ->assertForbidden();
        $this->actingAs($childUser)
            ->post(route('indicador.storeAnual', $motherIndicator->id), [
                'anio' => 2026,
                'valor_dato' => 99,
            ])
            ->assertForbidden();
        $this->actingAs($childUser)
            ->patch(route('indicadores.toggleValidacion', $motherIndicator->id), ['estado' => 1])
            ->assertForbidden();

        $this->assertDatabaseMissing('datos_anuales', [
            'id_indicador' => $motherIndicator->id,
            'anio' => 2026,
        ]);
        $this->assertFalse((bool) $motherIndicator->fresh()->indicador_validado);
    }

    public function test_catalog_rejects_more_than_one_sectorization_level(): void
    {
        $mother = $this->institution('Dependencia madre');
        $child = $this->institution('Institución hija', $mother);
        $administrator = User::factory()->create(['is_active' => true]);
        $administrator->assignRole(Role::findOrCreate('Administrador', 'web'));

        $response = $this->actingAs($administrator)->post(route('panel-cat-instituciones.store'), [
            'nombre' => 'Institución nieta',
            'titular' => 'Titular de prueba',
            'institucion_sectorizadora_id' => $child->id,
        ]);

        $response->assertSessionHasErrors('institucion_sectorizadora_id');
        $this->assertDatabaseMissing('instituciones', ['nombre' => 'Institución nieta']);
    }

    public function test_catalog_rejects_turning_a_mother_into_a_child(): void
    {
        $sectorizingInstitution = $this->institution('Otra dependencia');
        $mother = $this->institution('Dependencia madre');
        $this->institution('Institución hija', $mother);
        $administrator = User::factory()->create(['is_active' => true]);
        $administrator->assignRole(Role::findOrCreate('Administrador', 'web'));

        $response = $this->actingAs($administrator)->put(route('panel-cat-instituciones.update', $mother), [
            'nombre' => $mother->nombre,
            'titular' => $mother->titular,
            'institucion_sectorizadora_id' => $sectorizingInstitution->id,
        ]);

        $response->assertSessionHasErrors('institucion_sectorizadora_id');
        $this->assertNull($mother->fresh()->institucion_sectorizadora_id);
    }

    private function institution(string $name, ?Institucion $mother = null): Institucion
    {
        return Institucion::create([
            'nombre' => $name,
            'titular' => 'Titular de prueba',
            'institucion_sectorizadora_id' => $mother?->id,
        ]);
    }

    private function activePlanIndicator(Institucion $institution, string $name): Indicador
    {
        $plan = CatPlanEstatalDesarrollo::find(3);
        if (!$plan) {
            $plan = new CatPlanEstatalDesarrollo([
                'nombre' => 'Plan activo de prueba',
                'gobernador' => 'Gobernador de prueba',
            ]);
            $plan->id = 3;
            $plan->save();
        }

        $eje = CatEje::firstOrCreate(
            ['plan_id' => $plan->id, 'numero' => 1],
            ['nombre' => 'Eje de prueba']
        );

        return $eje->indicadores()->create([
            'nombre' => $name,
            'programa_derivado' => 'Plan Estatal de Desarrollo',
            'programa' => 'Programa de prueba',
            'cod_tematica' => 'T1',
            'tematica' => 'Temática de prueba',
            'id_institucion' => $institution->id,
            'linea_base' => '2024',
            'dato_linea_base' => '10',
            'meta_2024' => '100',
            'unidad_medida' => 'Porcentaje',
            'fuente' => 'Fuente de prueba',
            'descripcion' => 'Descripción de prueba',
            'periodicidad' => 'Anual',
            'cobertura' => 'Estatal',
            'tendencia' => 'Mayor es mejor',
            'fecha_actualizacion' => '2026-01-01',
            'formula' => 'Dato / meta',
        ]);
    }
}

<?php

namespace Tests\Feature\Security;

use App\Models\CatDimension;
use App\Models\CatMunicipio;
use App\Models\CatNivel;
use App\Models\CatPlanEstatalDesarrollo;
use App\Models\CatRegion;
use App\Models\CatTipo;
use App\Models\IndicadorMunicipal;
use App\Models\PeriodicidadIndicadorMunicipal;
use App\Models\ResultadoIndicadorMunicipal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HighRiskAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_ped_catalogs_without_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('panel-cat-planes.index'))
            ->assertForbidden();
    }

    public function test_authorized_user_cannot_upload_an_svg_to_a_ped_catalog(): void
    {
        $plan = CatPlanEstatalDesarrollo::create([
            'nombre' => 'Plan de prueba',
            'gobernador' => 'Gobernador de prueba',
        ]);
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('administrar-catalogos-ped', 'web'));

        $this->actingAs($user)
            ->post(route('panel-cat-prog-der-esp.store'), [
                'nombre' => 'Programa de prueba',
                'imagen' => UploadedFile::fake()->create('malicious.svg', 10, 'image/svg+xml'),
                'color' => '#123456',
                'icono' => 'fa-chart-line',
                'plan_estatal' => $plan->id,
                'documento' => 'https://example.test/programa.pdf',
            ])
            ->assertSessionHasErrors('imagen');
    }

    public function test_user_cannot_modify_or_delete_another_municipality_indicator(): void
    {
        [$userMunicipality, $otherMunicipality] = $this->municipalities();
        $indicator = $this->municipalIndicator($otherMunicipality->id);
        $user = User::factory()->create(['id_municipio' => $userMunicipality->id]);
        $user->givePermissionTo([
            Permission::findOrCreate('borrar-indicador-municipal', 'web'),
            Permission::findOrCreate('validar-indicador-municipal', 'web'),
        ]);

        $this->actingAs($user)
            ->delete(route('panel-indicadores-municipales.destroy', $indicator->id))
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('indicadores-municipales.toggleValidacion', $indicator->id))
            ->assertForbidden();

        $this->assertDatabaseHas('indicadores_municipales', ['id' => $indicator->id]);
    }

    public function test_user_cannot_update_another_municipality_results(): void
    {
        [$userMunicipality, $otherMunicipality] = $this->municipalities();
        $indicator = $this->municipalIndicator($otherMunicipality->id);
        $periodicity = PeriodicidadIndicadorMunicipal::create(['nombre' => 'Anual']);
        $result = ResultadoIndicadorMunicipal::create([
            'id_indicador' => $indicator->id,
            'periodicidad_id' => $periodicity->id,
            'año' => 2024,
            'periodo' => 1,
            'dato' => 10,
        ]);
        $user = User::factory()->create(['id_municipio' => $userMunicipality->id]);
        $user->givePermissionTo(Permission::findOrCreate('editar-resultados-indicador-municipal', 'web'));

        $this->actingAs($user)
            ->put(route('actualizarResultadosIndMun', ['anio' => 2024]), [
                'resultados' => [$result->id => ['dato' => 99]],
            ])
            ->assertForbidden();

        $this->assertSame(10.0, (float) $result->fresh()->dato);
    }

    /**
     * @return array{CatMunicipio, CatMunicipio}
     */
    private function municipalities(): array
    {
        $region = CatRegion::create(['nombre_region' => 'Region de prueba']);

        return [
            CatMunicipio::create(['nombre' => 'Municipio uno', 'region_id' => $region->id]),
            CatMunicipio::create(['nombre' => 'Municipio dos', 'region_id' => $region->id]),
        ];
    }

    private function municipalIndicator(int $municipalityId): IndicadorMunicipal
    {
        $type = CatTipo::create(['nombre' => 'Tipo de prueba']);
        $level = CatNivel::create(['nombre' => 'Nivel de prueba', 'tipo_id' => $type->id]);
        $dimension = CatDimension::create(['nombre' => 'Dimension de prueba', 'nivel_id' => $level->id]);

        return IndicadorMunicipal::create([
            'indicador' => 'Indicador municipal de prueba',
            'instrumento' => 'Plan de Desarrollo Municipal',
            'eje_indicador' => 'Eje de prueba',
            'tematica' => 'Tematica de prueba',
            'unidad_medida' => 'Porcentaje',
            'id_tipo' => $type->id,
            'id_nivel' => $level->id,
            'id_dimension' => $dimension->id,
            'id_municipio' => $municipalityId,
        ]);
    }
}

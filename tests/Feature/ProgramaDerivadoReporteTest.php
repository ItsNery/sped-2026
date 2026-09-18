<?php

namespace Tests\Feature;

use App\Models\CatPlanEstatalDesarrollo;
use App\Models\CatProgramaDerivadoSectorial;
use App\Models\CatEje;
use App\Models\DatoAnual;
use App\Models\Indicador;
use App\Models\Institucion;
use App\Models\InstitutionReportStatus;
use App\Models\User;
use App\Services\InstitutionAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProgramaDerivadoReporteTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_generate_a_report_with_validated_program_indicators(): void
    {
        $plan = CatPlanEstatalDesarrollo::create([
            'nombre' => 'PED de prueba',
            'gobernador' => 'Gobernador de prueba',
        ]);
        config()->set('sped.active_plan_id', $plan->id);
        $programa = CatProgramaDerivadoSectorial::create([
            'nombre' => 'Programa sectorial de prueba',
            'color' => '#000000',
            'icono' => 'fa-chart-line',
            'plan_estatal' => $plan->id,
            'documento' => 'https://example.test/programa.pdf',
        ]);
        $institucion = Institucion::create([
            'nombre' => 'Institución de prueba',
            'titular' => 'Titular de prueba',
        ]);
        $indicador = $programa->indicadores()->create($this->indicatorAttributes($institucion));
        DatoAnual::create([
            'id_indicador' => $indicador->id,
            'anio' => 2024,
            'valor_dato' => 12,
            'validado' => true,
        ]);
        DatoAnual::create([
            'id_indicador' => $indicador->id,
            'anio' => 2025,
            'valor_dato' => 99,
            'validado' => false,
        ]);
        $administrador = User::factory()->create();
        $administrador->assignRole(Role::findOrCreate('Administrador', 'web'));

        $this->actingAs($administrador)
            ->get(route('panel-reportes-programas.show', ['tipo' => 'sectoriales', 'programa' => $programa]))
            ->assertOk()
            ->assertSee('Programa sectorial de prueba')
            ->assertSee('12.00')
            ->assertSee('indicador-bloque')
            ->assertSee('Semáforo')
            ->assertSee('Último dato')
            ->assertDontSee('99.00');
    }

    public function test_non_administrators_cannot_access_program_reports(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('panel-reportes-programas.index'))
            ->assertForbidden();
    }

    public function test_institution_access_only_includes_direct_assignments(): void
    {
        $firstInstitution = Institucion::create([
            'nombre' => 'Primera institución',
            'titular' => 'Titular uno',
        ]);
        $secondInstitution = Institucion::create([
            'nombre' => 'Segunda institución',
            'titular' => 'Titular dos',
        ]);
        $user = User::factory()->create(['id_institucion' => $firstInstitution->id]);

        $this->assertSame(
            [$firstInstitution->id],
            app(InstitutionAccessService::class)->visibleInstitutionIds($user)->all()
        );
        $this->assertFalse(app(InstitutionAccessService::class)->canViewInstitution($user, $secondInstitution->id));
    }

    public function test_institution_user_report_includes_indicators_owned_by_another_user(): void
    {
        $plan = CatPlanEstatalDesarrollo::create([
            'nombre' => 'PED de reporte institucional',
            'gobernador' => 'Gobernador de prueba',
        ]);
        config()->set('sped.active_plan_id', $plan->id);
        $institucion = Institucion::create([
            'nombre' => 'Institución con nuevo usuario',
            'titular' => 'Titular de prueba',
        ]);
        $propietarioAnterior = User::factory()->create(['id_institucion' => $institucion->id]);
        $dummy = User::factory()->create([
            'id_institucion' => $institucion->id,
        ]);
        $dummy->assignRole(Role::findOrCreate('Enlace dependencia', 'web'));
        $eje = CatEje::create([
            'nombre' => 'Eje de reporte institucional',
            'numero' => 1,
            'color' => '#000000',
            'plan_id' => $plan->id,
        ]);
        $eje->indicadores()->create(array_merge($this->indicatorAttributes($institucion), [
            'nombre' => 'Indicador asignado a otra persona',
            'id_usuario' => $propietarioAnterior->id,
            'programa_derivado' => 'Plan Estatal de Desarrollo',
            'indicador_validado' => true,
        ]));

        $this->actingAs($dummy)
            ->postJson(route('finalizar.captura'))
            ->assertOk();

        $this->assertDatabaseHas('institution_report_statuses', [
            'institucion_id' => $institucion->id,
            'plan_id' => $plan->id,
            'finalizado_por_user_id' => $dummy->id,
        ]);

        $this->actingAs($dummy)
            ->get(route('generarReporte', $dummy->id))
            ->assertOk()
            ->assertSee('Indicador asignado a otra persona');

        $this->assertSame($dummy->id, InstitutionReportStatus::firstOrFail()->reporte_generado_por_user_id);
    }

    /**
     * @return array<string, int|string>
     */
    private function indicatorAttributes(Institucion $institucion): array
    {
        return [
            'nombre' => 'Indicador de prueba',
            'programa_derivado' => 'Programa Sectorial',
            'programa' => 'Programa sectorial de prueba',
            'cod_tematica' => 'T1',
            'tematica' => 'Temática de prueba',
            'id_institucion' => $institucion->id,
            'linea_base' => '2023',
            'dato_linea_base' => '10',
            'meta_anio' => 2030,
            'meta' => '20',
            'meta_2024' => '20',
            'unidad_medida' => 'Porcentaje',
            'fuente' => 'Fuente de prueba',
            'descripcion' => 'Descripción de prueba',
            'periodicidad' => 'Anual',
            'cobertura' => 'Estatal',
            'tendencia' => 'Mayor es mejor',
            'fecha_actualizacion' => '2026-01-01',
            'formula' => 'Dato / meta',
        ];
    }
}

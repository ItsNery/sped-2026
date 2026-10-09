<?php

namespace Tests\Feature\Security;

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

class CriticalSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_editor_cannot_assign_super_administrator_role(): void
    {
        $institution = Institucion::create([
            'nombre' => 'Institucion de prueba',
            'titular' => 'Titular de prueba',
        ]);
        $editor = User::factory()->create(['id_institucion' => $institution->id]);
        $editor->givePermissionTo(Permission::findOrCreate('editar-usuario', 'web'));
        $target = User::factory()->create(['id_institucion' => $institution->id]);
        Role::findOrCreate('SuperAdministrador', 'web');

        $this->actingAs($editor)
            ->put(route('panel-usuarios.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'roles' => 'SuperAdministrador',
                'tipo_usuario' => 'institucion',
                'id_institucion' => $institution->id,
            ])
            ->assertForbidden();

        $this->assertFalse($target->fresh()->hasRole('SuperAdministrador'));
    }

    public function test_indicator_evidence_deletion_cannot_escape_its_directory(): void
    {
        $plan = CatPlanEstatalDesarrollo::create([
            'nombre' => 'Plan de prueba',
            'gobernador' => 'Gobernador de prueba',
        ]);
        $eje = CatEje::create([
            'nombre' => 'Eje de prueba',
            'numero' => 1,
            'plan_id' => $plan->id,
        ]);
        $institution = Institucion::create([
            'nombre' => 'Institucion de prueba',
            'titular' => 'Titular de prueba',
        ]);
        $administrator = User::factory()->create();
        $administrator->assignRole(Role::findOrCreate('Administrador', 'web'));
        $administrator->givePermissionTo(Permission::findOrCreate('editar-indicador', 'web'));
        $indicator = $eje->indicadores()->create($this->indicatorAttributes($institution));
        $annualData = DatoAnual::create([
            'id_indicador' => $indicator->id,
            'anio' => 2024,
            'valor_dato' => 10,
        ]);
        $sentinelPath = public_path('critical-security-sentinel.txt');

        file_put_contents($sentinelPath, 'must remain');

        try {
            $this->actingAs($administrator)
                ->put(route('panel-indicadores.update', $indicator), array_merge(
                    $this->indicatorAttributes($institution),
                    [
                        'plan_id' => $plan->id,
                        'eje_id' => $eje->id,
                        'eje_app' => $eje->nombre,
                        'datos_anuales' => [[
                            'id' => $annualData->id,
                            'anio' => 2024,
                            'valor_dato' => 10,
                            'evidencia_actual' => '../../../critical-security-sentinel.txt',
                            'eliminar_evidencia' => true,
                        ]],
                    ]
                ))
                ->assertRedirect(route('panel-indicadores.index'));

            $this->assertFileExists($sentinelPath);
        } finally {
            if (file_exists($sentinelPath)) {
                unlink($sentinelPath);
            }
        }
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
            'tematica' => 'Tematica de prueba',
            'linea_base' => 2023,
            'dato_linea_base' => '10',
            'meta_anio' => 2030,
            'meta' => '20',
            'unidad_medida' => 'Porcentaje',
            'id_institucion' => $institution->id,
            'fuente' => 'Fuente de prueba',
            'descripcion' => 'Descripcion de prueba',
            'periodicidad' => 'Anual',
            'cobertura' => 'Estatal',
            'tendencia' => 'Mayor es mejor',
            'fecha_actualizacion' => '2026-01-01',
            'formula' => 'Dato / meta',
        ];
    }
}

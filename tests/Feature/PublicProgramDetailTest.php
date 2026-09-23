<?php

namespace Tests\Feature;

use App\Models\CatEje;
use App\Models\CatPlanEstatalDesarrollo;
use App\Models\CatProgramaDerivadoSectorial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicProgramDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_derived_program_detail_hides_general_progress(): void
    {
        $plan = CatPlanEstatalDesarrollo::create([
            'nombre' => 'PED de prueba',
            'gobernador' => 'Gobernador de prueba',
        ]);
        config()->set('sped.active_plan_id', $plan->id);
        $programa = CatProgramaDerivadoSectorial::create([
            'nombre' => 'Programa sectorial de prueba',
            'imagen' => 'img/pleca-pajaro-2.png',
            'descripcion' => 'Descripción de prueba',
            'color' => '#000000',
            'icono' => 'fa-chart-line',
            'plan_estatal' => $plan->id,
            'documento' => 'https://example.test/programa.pdf',
        ]);

        $this->get('/ped-programas/sectoriales/' . Str::slug($programa->nombre))
            ->assertOk()
            ->assertSee('Indicadores en total')
            ->assertDontSee('id="gauge-general"', false)
            ->assertDontSee('chartValGeneral', false);
    }

    public function test_eje_detail_keeps_general_progress(): void
    {
        $plan = CatPlanEstatalDesarrollo::create([
            'nombre' => 'PED de prueba',
            'gobernador' => 'Gobernador de prueba',
        ]);
        config()->set('sped.active_plan_id', $plan->id);
        CatEje::create([
            'nombre' => 'Eje de prueba',
            'numero' => 1,
            'color' => '#000000',
            'plan_id' => $plan->id,
        ]);

        $this->get('/ped/eje-1')
            ->assertOk()
            ->assertSee('id="gauge-general"', false)
            ->assertSee('chartValGeneral', false);
    }
}

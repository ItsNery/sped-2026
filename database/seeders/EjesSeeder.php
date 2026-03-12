<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EjesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plan = \App\Models\CatPlanEstatalDesarrollo::find(3);
        if (!$plan) {
            $plan = \App\Models\CatPlanEstatalDesarrollo::where('nombre', 'like', '%2024-2030%')->first();
        }
        if (!$plan) {
            return;
        }

        $ejes = [
            ['numero' => 1, 'nombre' => 'Humanismo con Bienestar', 'color' => '#9d1738'],
            ['numero' => 2, 'nombre' => 'Prosperidad y Estabilidad Económica', 'color' => '#ceac53'],
            ['numero' => 3, 'nombre' => 'Estado de Derecho, Seguridad y Justicia', 'color' => '#c7692f'],
            ['numero' => 4, 'nombre' => 'Desarrollo Urbano y Crecimiento Sostenible', 'color' => '#205342'],
            ['numero' => 5, 'nombre' => 'Gobierno Transformador y de Resultados', 'color' => '#112e3e'],
            ['numero' => 6, 'nombre' => 'Por Amor a Puebla', 'color' => '#74777a'],
        ];

        foreach ($ejes as $data) {
            $eje = \App\Models\CatEje::updateOrCreate(
                ['plan_id' => $plan->id, 'numero' => $data['numero']],
                ['nombre' => $data['nombre'], 'color' => $data['color']]
            );

            // Reasignar indicadores que estaban directo al plan y coinciden con este programa
            \App\Models\Indicador::where('indicadorable_type', \App\Models\CatPlanEstatalDesarrollo::class)
                ->where('indicadorable_id', $plan->id)
                ->where('programa', $data['nombre'])
                ->update([
                    'indicadorable_type' => \App\Models\CatEje::class,
                    'indicadorable_id' => $eje->id
                ]);
        }
    }
}

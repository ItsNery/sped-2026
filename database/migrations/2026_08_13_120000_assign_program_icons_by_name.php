<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $iconosPorTabla = [
            'cat_programas_derivados_sectoriales' => [
                ['terminos' => ['agua'], 'icono' => 'fa-water'],
                ['terminos' => ['socioambiental', 'sostenibilidad', 'ambiental'], 'icono' => 'fa-leaf'],
                ['terminos' => ['bienestar social'], 'icono' => 'fa-hand-holding-heart'],
                ['terminos' => ['buen gobierno', 'gobernabilidad', 'legalidad', 'transparencia', 'corrupcion'], 'icono' => 'fa-scale-balanced'],
                ['terminos' => ['calidad y armonia', 'salud'], 'icono' => 'fa-heart-pulse'],
                ['terminos' => ['competencias educativas', 'educacion'], 'icono' => 'fa-school'],
                ['terminos' => ['desarrollo economico', 'prosperidad', 'trabajo'], 'icono' => 'fa-chart-line'],
                ['terminos' => ['desarrollo rural', 'campo'], 'icono' => 'fa-tractor'],
                ['terminos' => ['estado seguro', 'seguridad publica'], 'icono' => 'fa-shield-halved'],
                ['terminos' => ['turismo'], 'icono' => 'fa-umbrella-beach'],
                ['terminos' => ['movilidad', 'transporte'], 'icono' => 'fa-bus'],
                ['terminos' => ['justicia social', 'estado de derecho'], 'icono' => 'fa-scale-balanced'],
                ['terminos' => ['diversidad cultural'], 'icono' => 'fa-people-group'],
            ],
            'cat_programas_derivados_especiales' => [
                ['terminos' => ['anticorrupcion'], 'icono' => 'fa-scale-balanced'],
                ['terminos' => ['juventudes'], 'icono' => 'fa-person-running'],
                ['terminos' => ['mujeres', 'igualdad sustantiva'], 'icono' => 'fa-person-dress'],
                ['terminos' => ['ninas, ninos y adolescentes', 'ninas ninos y adolescentes'], 'icono' => 'fa-children'],
                ['terminos' => ['tecnologia', 'innovacion'], 'icono' => 'fa-microchip'],
                ['terminos' => ['pueblos indigenas', 'afromexicanos'], 'icono' => 'fa-people-group'],
                ['terminos' => ['infraestructura'], 'icono' => 'fa-road'],
                ['terminos' => ['cuidado ambiental', 'cambio climatico'], 'icono' => 'fa-leaf'],
                ['terminos' => ['desarrollo energetico'], 'icono' => 'fa-bolt'],
                ['terminos' => ['gobierno democratico', 'transparente'], 'icono' => 'fa-landmark'],
                ['terminos' => ['derechos humanos'], 'icono' => 'fa-hand-holding-heart'],
            ],
            'cat_programas_derivados_institucionales' => [
                ['terminos' => ['universidad', 'colegio', 'bachilleres', 'educacion profesional', 'educacion digital', 'magisterio'], 'icono' => 'fa-school'],
                ['terminos' => ['instituto tecnologico', 'instituto de educacion', 'instituto de capacitacion', 'instituto de profesionalizacion'], 'icono' => 'fa-graduation-cap'],
                ['terminos' => ['salud', 'issstep'], 'icono' => 'fa-heart-pulse'],
                ['terminos' => ['agua y saneamiento'], 'icono' => 'fa-water'],
                ['terminos' => ['agricultura', 'desarrollo rural'], 'icono' => 'fa-tractor'],
                ['terminos' => ['medio ambiente', 'sustentable', 'sostenibilidad'], 'icono' => 'fa-leaf'],
                ['terminos' => ['movilidad', 'transporte'], 'icono' => 'fa-bus'],
                ['terminos' => ['carreteras', 'infraestructura', 'vivienda'], 'icono' => 'fa-road'],
                ['terminos' => ['turismo'], 'icono' => 'fa-umbrella-beach'],
                ['terminos' => ['derechos humanos'], 'icono' => 'fa-scale-balanced'],
                ['terminos' => ['seguridad', 'policia'], 'icono' => 'fa-shield-halved'],
                ['terminos' => ['anticorrupcion', 'transparencia', 'consejeria juridica'], 'icono' => 'fa-scale-balanced'],
                ['terminos' => ['deporte', 'juventud'], 'icono' => 'fa-person-running'],
                ['terminos' => ['mujeres'], 'icono' => 'fa-person-dress'],
                ['terminos' => ['ciencia', 'tecnologia', 'innovacion'], 'icono' => 'fa-microchip'],
                ['terminos' => ['arte', 'cultura', 'museos'], 'icono' => 'fa-palette'],
                ['terminos' => ['migrante'], 'icono' => 'fa-plane-departure'],
                ['terminos' => ['pueblos indigenas', 'intercultural'], 'icono' => 'fa-people-group'],
                ['terminos' => ['discapacidad'], 'icono' => 'fa-person-cane'],
                ['terminos' => ['desarrollo integral de la familia'], 'icono' => 'fa-people-roof'],
                ['terminos' => ['comunicacion', 'telecomunicaciones'], 'icono' => 'fa-bullhorn'],
                ['terminos' => ['energia'], 'icono' => 'fa-bolt'],
                ['terminos' => ['conciliacion laboral', 'trabajo'], 'icono' => 'fa-briefcase'],
            ],
        ];

        foreach ($iconosPorTabla as $tabla => $reglas) {
            foreach (DB::table($tabla)->get(['id', 'nombre']) as $programa) {
                $nombre = Str::lower(Str::ascii($programa->nombre));

                foreach ($reglas as $regla) {
                    $coincide = collect($regla['terminos'])->contains(
                        fn (string $termino) => Str::contains($nombre, $termino)
                    );

                    if ($coincide) {
                        DB::table($tabla)
                            ->where('id', $programa->id)
                            ->update(['icono' => $regla['icono']]);
                        break;
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // No se revierten iconos existentes porque algunos pudieron ser editados manualmente.
    }
};

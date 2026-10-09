<?php

namespace Database\Seeders;

use App\Models\CatPlanEstatalDesarrollo;
use App\Models\CatProgramaDerivadoInstitucional;
use Database\Seeders\Support\Ped3InstitutionalManifest;
use Illuminate\Database\Seeder;
use RuntimeException;

class Ped3InstitutionalCatalogSeeder extends Seeder
{
    private const PLAN_ID = 3;

    public function run(): void
    {
        $plan = CatPlanEstatalDesarrollo::find(self::PLAN_ID);

        if (!$plan) {
            throw new RuntimeException('No existe el PED 3 en el catalogo de planes estatales.');
        }

        $manifest = Ped3InstitutionalManifest::load();
        $existing = [];
        $duplicates = [];

        foreach (CatProgramaDerivadoInstitucional::where('plan_estatal', self::PLAN_ID)->get() as $program) {
            $key = Ped3InstitutionalManifest::programKey($program->nombre);

            if (isset($existing[$key])) {
                $duplicates[$key][] = $program->id;
                continue;
            }

            $existing[$key] = $program;
        }

        if ($duplicates) {
            throw new RuntimeException('Hay programas institucionales duplicados por nombre normalizado: ' . json_encode($duplicates));
        }

        $created = 0;
        $reused = 0;

        foreach ($manifest['programs'] as $programData) {
            $key = Ped3InstitutionalManifest::programKey($programData['name']);

            if (isset($existing[$key])) {
                $reused++;
                continue;
            }

            $program = CatProgramaDerivadoInstitucional::create([
                'nombre' => $programData['name'],
                'grupo' => $programData['group'],
                'siglas' => null,
                'imagen' => 'img/pleca-pajaro-2.png',
                'descripcion' => 'Programa Institucional del Plan Estatal de Desarrollo 2024-2030.',
                'color' => '#691A32',
                'icono' => 'fa-building',
                'plan_estatal' => self::PLAN_ID,
                'documento' => 'https://ped2024-2030.puebla.gob.mx/',
            ]);

            $program->siglas = $program->siglas;
            $program->save();
            $existing[$key] = $program;
            $created++;
        }

        $this->command?->info("PED 3 institucional: {$created} programas creados, {$reused} existentes.");
        $this->command?->line('Fuente: ' . $manifest['file']);
    }
}

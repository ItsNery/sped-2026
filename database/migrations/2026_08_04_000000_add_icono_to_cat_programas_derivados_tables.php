<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'cat_programas_derivados_sectoriales',
            'cat_programas_derivados_especiales',
            'cat_programas_derivados_regionales',
            'cat_programas_derivados_institucionales',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('icono', 50)->nullable()->after('color');
            });
        }

        $iconosIniciales = [
            'cat_programas_derivados_sectoriales' => ['fa-industry', 'fa-chart-line', 'fa-briefcase'],
            'cat_programas_derivados_especiales' => ['fa-star', 'fa-lightbulb', 'fa-heart'],
            'cat_programas_derivados_regionales' => ['fa-map-location-dot', 'fa-earth-americas', 'fa-route'],
            'cat_programas_derivados_institucionales' => ['fa-building', 'fa-landmark', 'fa-users'],
        ];

        foreach ($iconosIniciales as $tableName => $iconos) {
            foreach (DB::table($tableName)->orderBy('id')->pluck('id') as $index => $id) {
                DB::table($tableName)->where('id', $id)->update([
                    'icono' => $iconos[$index % count($iconos)],
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach ([
            'cat_programas_derivados_sectoriales',
            'cat_programas_derivados_especiales',
            'cat_programas_derivados_regionales',
            'cat_programas_derivados_institucionales',
        ] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('icono');
            });
        }
    }
};

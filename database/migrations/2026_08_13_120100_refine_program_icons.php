<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('cat_programas_derivados_sectoriales')
            ->whereIn('nombre', ['Bienestar', 'Programa Sectorial de Bienestar Social'])
            ->update(['icono' => 'fa-hand-holding-heart']);

        DB::table('cat_programas_derivados_sectoriales')
            ->where('nombre', 'Cultura')
            ->update(['icono' => 'fa-palette']);

        DB::table('cat_programas_derivados_sectoriales')
            ->where('nombre', 'Trabajo')
            ->update(['icono' => 'fa-briefcase']);

        DB::table('cat_programas_derivados_institucionales')
            ->where('nombre', 'Programa Institucional de la Secretaría de Educación Pública')
            ->update(['icono' => 'fa-school']);

        DB::table('cat_programas_derivados_institucionales')
            ->where('nombre', 'Programa Institucional de la Secretaría de Bienestar')
            ->update(['icono' => 'fa-hand-holding-heart']);
    }

    public function down(): void
    {
        // No se revierten iconos existentes porque algunos pudieron ser editados manualmente.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddSlugToMunicipiosConvenioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // PASO A: Crear la columna nullable
        // Primero verificamos si no existe para evitar errores si la migración corrió a medias
        if (!Schema::hasColumn('municipios_convenio', 'slug')) {
            Schema::table('municipios_convenio', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('id_municipio');
            });
        }

        // PASO B: Llenar los datos (JOIN con cat_municipios)
        $registros = DB::table('municipios_convenio')
            ->join('cat_municipios', 'municipios_convenio.id_municipio', '=', 'cat_municipios.id')
            ->select('municipios_convenio.id', 'cat_municipios.nombre')
            ->get();

        foreach ($registros as $row) {
            $slug = Str::slug($row->nombre);

            DB::table('municipios_convenio')
                ->where('id', $row->id)
                ->update(['slug' => $slug]);
        }

        // PASO C: Agregar índice UNIQUE (Modificado para no usar ->change())
        // En lugar de cambiar la columna, solo le agregamos la restricción unique.
        // La columna se quedará como "nullable" en la BD, pero no afecta el funcionamiento.
        Schema::table('municipios_convenio', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('municipios_convenio', function (Blueprint $table) {
            // Primero botamos el índice unique
            $table->dropUnique(['slug']);
            // Luego la columna
            $table->dropColumn('slug');
        });
    }
}

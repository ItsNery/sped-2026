<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateResultadosIndicadoresMunicipalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resultados_indicadores_municipales', function (Blueprint $table) {
            // Eliminar el campo periodicidad
            $table->dropColumn('periodicidad');

            // Agregar periodicidad_id como clave foránea
            $table->unsignedBigInteger('periodicidad_id')->after('id_indicador');
            $table->foreign('periodicidad_id')->references('id')->on('periodicidad_indicadores_municipales');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resultados_indicadores_municipales', function (Blueprint $table) {
            // Restaurar el campo periodicidad
            $table->enum('periodicidad', ['anual', 'bimestral', 'cuatrimestral', 'mensual', 'semestral', 'trimestral'])->after('id_indicador');

            // Eliminar la clave foránea y el campo periodicidad_id
            $table->dropForeign(['periodicidad_id']);
            $table->dropColumn('periodicidad_id');
        });
    }
}

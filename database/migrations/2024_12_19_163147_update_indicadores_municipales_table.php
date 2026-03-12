<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIndicadoresMunicipalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('indicadores_municipales', function (Blueprint $table) {
            // Eliminar columnas existentes
            $table->dropColumn(['periodicidad', 'fecha_alta', 'fecha_actualizacion']);

            // Agregar nueva columna de clave foránea
            $table->unsignedBigInteger('periodicidad_id')->nullable()->after('id');
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
        Schema::table('indicadores_municipales', function (Blueprint $table) {
            // Restaurar columnas eliminadas
            $table->string('periodicidad')->nullable();
            $table->date('fecha_alta')->nullable();
            $table->date('fecha_actualizacion')->nullable();

            // Eliminar clave foránea y columna relacionada
            $table->dropForeign(['periodicidad_id']);
            $table->dropColumn('periodicidad_id');
        });
    }
}

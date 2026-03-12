<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResultadosIndicadoresMunicipalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resultados_indicadores_municipales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_indicador')->constrained('indicadores_municipales')->onDelete('cascade');
            $table->enum('periodicidad', ['anual', 'bimestral', 'cuatrimestral', 'mensual', 'semestral', 'trimestral']);
            $table->year('año');
            $table->unsignedInteger('periodo'); // Por ejemplo, 1, 2, 3 según periodicidad
            $table->float('dato')->nullable();
            $table->float('resultado')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resultados_indicadores_municipales');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndicadoresMunicipalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indicadores_municipales', function (Blueprint $table) {
            $table->id();
            $table->string('indicador');
            $table->string('instrumento');
            $table->string('eje_indicador');
            $table->string('tematica');
            $table->text('descripcion')->nullable();
            $table->string('unidad_medida');
            $table->float('linea_base')->nullable();
            $table->float('dato_linea')->nullable();
            $table->float('meta_2024')->nullable();
            $table->string('fuente')->nullable();
            $table->text('liga')->nullable();
            $table->enum('periodicidad', ['anual', 'bimestral', 'cuatrimestral', 'mensual', 'semestral', 'trimestral']);
            $table->string('cobertura')->nullable();
            $table->string('tendencia')->nullable();
            $table->foreignId('id_tipo')->constrained('cat_tipo');
            $table->foreignId('id_nivel')->constrained('cat_nivel');
            $table->foreignId('id_dimension')->constrained('cat_dimension');
            $table->text('formula')->nullable();
            $table->string('dependencia')->nullable();
            $table->boolean('publica')->default(false);
            $table->timestamp('fecha_alta')->useCurrent();
            $table->timestamp('fecha_actualizacion')->useCurrent()->nullable();
            $table->boolean('validado')->default(false);
            $table->foreignId('id_municipio')->constrained('cat_municipios')->onDelete('cascade');
            $table->timestamp('proxima_actualizacion')->nullable();
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
        Schema::dropIfExists('indicadores_municipales');
    }
}

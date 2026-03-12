<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatosIndicadoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('datos_anuales_indicadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_indicador')->constrained('indicadors')->onDelete('cascade');
            $table->string('dato_2010')->nullable();
            $table->string('dato_2011')->nullable();
            $table->string('dato_2012')->nullable();
            $table->string('dato_2013')->nullable();
            $table->string('dato_2014')->nullable();
            $table->string('dato_2015')->nullable();
            $table->string('dato_2016')->nullable();
            $table->string('dato_2017')->nullable();
            $table->string('dato_2018')->nullable();
            $table->string('dato_2019')->nullable();
            $table->string('dato_2020')->nullable();
            $table->string('dato_2021')->nullable();
            $table->string('dato_2022')->nullable();
            $table->string('dato_2023')->nullable();
            $table->string('dato_2024')->nullable();  
            $table->string('dato_2025')->nullable();
            $table->date('fecha_actualizacion_2020')->nullable();
            $table->date('fecha_actualizacion_2021')->nullable();
            $table->date('fecha_actualizacion_2022')->nullable();
            $table->date('fecha_actualizacion_2023')->nullable();
            $table->date('fecha_actualizacion_2024')->nullable();
            $table->date('fecha_actualizacion_2025')->nullable();
            $table->text('resultados_2020')->nullable();
            $table->text('resultados_2021')->nullable();
            $table->text('resultados_2022')->nullable();
            $table->text('resultados_2023')->nullable();
            $table->text('resultados_2024')->nullable();
            $table->text('resultados_2025')->nullable();
            $table->string('evidencia_2020')->nullable();
            $table->string('evidencia_2021')->nullable();
            $table->string('evidencia_2022')->nullable();
            $table->string('evidencia_2023')->nullable();
            $table->string('evidencia_2024')->nullable();
            $table->string('evidencia_2025')->nullable();
            $table->text('observaciones_2020')->nullable();
            $table->text('observaciones_2021')->nullable();
            $table->text('observaciones_2022')->nullable();
            $table->text('observaciones_2023')->nullable();
            $table->text('observaciones_2024')->nullable();
            $table->text('observaciones_2025')->nullable();
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
        Schema::dropIfExists('datos_anuales_indicadores');
    }
}

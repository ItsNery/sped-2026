<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Create20192024IndicadorsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Crear nuevas tablas con el prefijo 2019_2024_
        Schema::create('2019_2024_indicadors', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('programa_derivado')->nullable();
            $table->string('programa')->nullable();
            $table->string('cod_tematica')->nullable();
            $table->string('tematica')->nullable();
            $table->unsignedBigInteger('id_institucion')->nullable();
            $table->string('linea_base')->nullable();
            $table->string('dato_linea_base')->nullable();
            $table->string('meta_2024')->nullable();
            $table->string('unidad_medida')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->text('fuente')->nullable();
            $table->string('liga')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('periodicidad')->nullable();
            $table->string('periodo')->nullable();
            $table->string('cobertura')->nullable();
            $table->string('tendencia')->nullable();
            $table->date('fecha_actualizacion')->nullable();
            $table->text('resultados')->nullable();
            $table->text('formula')->nullable();
            $table->boolean('version_2024')->default(false)->nullable();
            $table->boolean('indicador_validado')->default(false)->nullable();
            $table->unsignedBigInteger('id_semaforo')->nullable();
            $table->timestamps();
        });

        Schema::create('2019_2024_datos_anuales_indicadores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_indicador');
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

        Schema::create('2019_2024_indicadors_ods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_indicador');
            $table->unsignedBigInteger('id_ods');
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
        Schema::dropIfExists('2019_2024_indicadors');
        Schema::dropIfExists('2019_2024_datos_anuales_indicadores');
        Schema::dropIfExists('2019_2024_indicadors_ods');
    }
}

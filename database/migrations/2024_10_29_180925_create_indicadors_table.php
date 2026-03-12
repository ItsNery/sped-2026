<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndicadorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indicadors', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('programa_derivado');
            $table->string('programa');
            $table->string('cod_tematica');
            $table->string('tematica');
            $table->unsignedBigInteger('id_institucion')->nullable(); 
            $table->foreign('id_institucion')->references('id')->on('institucions')->onDelete('cascade');
            $table->string('linea_base');
            $table->string('dato_linea_base');
            $table->string('meta_2024');
            $table->string('unidad_medida');
            $table->unsignedBigInteger('id_usuario')->nullable(); 
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->text('fuente');
            $table->string('liga')->nullable();
            $table->text('descripcion');
            $table->string('periodicidad');
            $table->string('periodo')->nullable();
            $table->string('cobertura');
            $table->string('tendencia');
            $table->string('fecha_actualizacion');
            $table->text('resultados')->nullable();
            $table->text('formula');
            $table->boolean('version_2024')->default(false);
            $table->boolean('indicador_validado')->default(false);
            $table->integer('id_semaforo')->nullable();
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
        Schema::dropIfExists('indicadors');
    }
}

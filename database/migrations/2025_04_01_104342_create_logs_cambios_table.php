<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsCambiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs_cambios', function (Blueprint $table) {
            $table->id();
            $table->string('usuario')->nullable(); // Guarda el nombre o ID del usuario
            $table->string('tabla'); // Nombre de la tabla afectada
            $table->string('columna')->nullable();  // Columna afectada
            $table->string('accion'); // Tipo de acción: creado, actualizado, eliminado
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
        Schema::dropIfExists('logs_cambios');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatProgramasDerivadosInstitucionalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cat_programas_derivados_institucionales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo');
            $table->string('imagen');
            $table->text('descripcion');
            $table->string('color');
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
        Schema::dropIfExists('cat_programas_derivados_institucionales');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMunicipiosConvenioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('municipios_convenio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_municipio')->nullable();
            $table->foreign('id_municipio')->references('id')->on('cat_municipios')->onDelete('cascade');
            $table->string('icono');
            $table->text('objetivo');
            $table->string('convenio');
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
        Schema::dropIfExists('municipios_convenio');
    }
}

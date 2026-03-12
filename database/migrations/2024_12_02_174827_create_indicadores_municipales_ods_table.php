<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndicadoresMunicipalesOdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indicadores_municipales_ods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_indicador')->constrained('indicadores_municipales')->onDelete('cascade');
            $table->foreignId('id_ods')->constrained('ods')->onDelete('cascade'); // Suponiendo que `cat_ods` es el catálogo de ODS
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
        Schema::dropIfExists('indicadores_municipales_ods');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSemaforoToIndicadors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('indicadors', function (Blueprint $table) {
            $table->unsignedBigInteger('id_semaforo')->nullable(); // nullable si el campo puede estar vacío
            $table->foreign('id_semaforo')->references('id')->on('semfaforo_indicador')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('indicadors', function (Blueprint $table) {
            $table->dropForeign(['id_semaforo']);
            $table->dropColumn('id_semaforo');
        });
    }
}

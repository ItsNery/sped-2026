<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatosAnualesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('datos_anuales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_indicador')->constrained('indicadors')->onDelete('cascade');
            $table->year('anio');
            $table->decimal('valor_dato', 15, 5)->nullable(); // Ajusta precisión y escala
            $table->date('fecha_actualizacion')->nullable();
            $table->text('resultados')->nullable();
            $table->string('evidencia')->nullable(); // O text si es más largo
            $table->text('observaciones')->nullable();
            $table->boolean('modificado')->default(false);
            $table->timestamps();
            $table->unique(['id_indicador', 'anio']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('datos_anuales');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatPlanesEstatalesDesarrolloTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cat_planes_estatales_desarrollo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('gobernador', 200);
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
        Schema::dropIfExists('cat_planes_estatales_desarrollo');
    }
}

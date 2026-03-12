<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndicadorableToIndicadorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('indicadors', function (Blueprint $table) {
            $table->nullableMorphs('indicadorable');
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
            $table->dropMorphs('indicadorable');
        });
    }
}

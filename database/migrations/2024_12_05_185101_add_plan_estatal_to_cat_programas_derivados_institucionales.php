<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlanEstatalToCatProgramasDerivadosInstitucionales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cat_programas_derivados_institucionales', function (Blueprint $table) {
            $table->unsignedBigInteger('plan_estatal')->nullable();


            $table->foreign('plan_estatal')
                ->references('id')
                ->on('cat_planes_estatales_desarrollo')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cat_programas_derivados_institucionales', function (Blueprint $table) {
            //
        });
    }
}

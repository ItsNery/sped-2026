<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentoToCatProgramasDerivadosSectoriales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cat_programas_derivados_sectoriales', function (Blueprint $table) {
            $table->string('documento')->nullable()->after('plan_estatal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cat_programas_derivados_sectoriales', function (Blueprint $table) {
            //
        });
    }
}

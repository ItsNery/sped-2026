<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddValidadoToDatosAnualesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('datos_anuales', function (Blueprint $table) {
            $table->boolean('validado')->default(false)->after('observaciones');
        });
        DB::table('datos_anuales')->update(['validado' => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('datos_anuales', function (Blueprint $table) {
            $table->dropColumn('validado');
        });
    }
}

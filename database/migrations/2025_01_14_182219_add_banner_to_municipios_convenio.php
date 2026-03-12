<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBannerToMunicipiosConvenio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('municipios_convenio', function (Blueprint $table) {
            $table->string('banner')->after('convenio');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('municipios_convenio', function (Blueprint $table) {
            //
        });
    }
}

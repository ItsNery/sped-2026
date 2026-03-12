<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddSlugToIndicadorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('indicadors', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('nombre');
        });

        // Populate slugs
        $indicadors = DB::table('indicadors')->get();
        foreach ($indicadors as $indicador) {
            $slug = Str::slug($indicador->nombre);
            
            // Ensure uniqueness
            $originalSlug = $slug;
            $count = 1;
            while (DB::table('indicadors')->where('slug', $slug)->where('id', '!=', $indicador->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            DB::table('indicadors')->where('id', $indicador->id)->update(['slug' => $slug]);
        }

        // Add unique constraint
        Schema::table('indicadors', function (Blueprint $table) {
            $table->unique('slug');
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
            $table->dropColumn('slug');
        });
    }
}

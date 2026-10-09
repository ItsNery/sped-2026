<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indicadores_municipales', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('indicador');
        });

        foreach (DB::table('indicadores_municipales')->get() as $indicador) {
            $baseSlug = Str::slug($indicador->indicador) ?: 'indicador';
            $slug = $baseSlug;
            $count = 1;

            while (DB::table('indicadores_municipales')
                ->where('slug', $slug)
                ->where('id', '!=', $indicador->id)
                ->exists()) {
                $slug = $baseSlug . '-' . $count++;
            }

            DB::table('indicadores_municipales')
                ->where('id', $indicador->id)
                ->update(['slug' => $slug]);
        }

        Schema::table('indicadores_municipales', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('indicadores_municipales', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};

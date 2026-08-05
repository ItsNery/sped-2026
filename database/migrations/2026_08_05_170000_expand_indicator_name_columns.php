<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indicadors', function (Blueprint $table) {
            $table->string('nombre', 255)->change();
            $table->string('slug', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('indicadors')->whereRaw('CHAR_LENGTH(nombre) > 191')->exists()
            || DB::table('indicadors')->whereRaw('CHAR_LENGTH(slug) > 191')->exists()) {
            throw new RuntimeException('No se puede reducir nombre o slug mientras existan valores mayores a 191 caracteres.');
        }

        Schema::table('indicadors', function (Blueprint $table) {
            $table->string('nombre', 191)->change();
            $table->string('slug', 191)->nullable()->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'cat_programas_derivados_sectoriales',
            'cat_programas_derivados_especiales',
            'cat_programas_derivados_regionales',
            'cat_programas_derivados_institucionales',
        ] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('imagen')->nullable()->change();
                $table->text('descripcion')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'cat_programas_derivados_sectoriales',
            'cat_programas_derivados_especiales',
            'cat_programas_derivados_regionales',
            'cat_programas_derivados_institucionales',
        ] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('imagen')->nullable(false)->change();
                $table->text('descripcion')->nullable(false)->change();
            });
        }
    }
};

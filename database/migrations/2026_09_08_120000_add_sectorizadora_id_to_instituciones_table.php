<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->foreignId('institucion_sectorizadora_id')
                ->nullable()
                ->after('titular')
                ->constrained('instituciones')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('institucion_sectorizadora_id');
        });
    }
};

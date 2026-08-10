<?php

use App\Models\Indicador;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indicadors', function (Blueprint $table) {
            $table->unsignedSmallInteger('meta_anio')->nullable()->after('meta_2024');
            $table->string('meta')->nullable()->after('meta_anio');
            $table->index('meta_anio');
        });

        DB::table('indicadors')->update([
            'meta_anio' => 2024,
            'meta' => DB::raw('meta_2024'),
        ]);

        $ped3Ids = Indicador::forPlan(3)->pluck('id');
        if ($ped3Ids->isNotEmpty()) {
            DB::table('indicadors')
                ->whereIn('id', $ped3Ids)
                ->update(['meta_anio' => 2030]);
        }
    }

    public function down(): void
    {
        Schema::table('indicadors', function (Blueprint $table) {
            $table->dropIndex(['meta_anio']);
            $table->dropColumn(['meta_anio', 'meta']);
        });
    }
};

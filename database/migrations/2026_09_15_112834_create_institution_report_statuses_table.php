<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('institution_report_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('cat_planes_estatales_desarrollo')->cascadeOnDelete();
            $table->timestamp('finalizado_at')->nullable();
            $table->foreignId('finalizado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reporte_generado_at')->nullable();
            $table->foreignId('reporte_generado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['institucion_id', 'plan_id']);
        });

        if (!Schema::hasColumn('users', 'finalizado') || !Schema::hasColumn('users', 'reporte_generado')) {
            return;
        }

        $planId = (int) config('sped.active_plan_id', 3);
        $users = DB::table('users')
            ->whereNotNull('id_institucion')
            ->where(function ($query) {
                $query->where('finalizado', true)->orWhere('reporte_generado', true);
            })
            ->orderByDesc('reporte_generado_at')
            ->orderByDesc('updated_at')
            ->get()
            ->unique('id_institucion');

        foreach ($users as $user) {
            DB::table('institution_report_statuses')->insert([
                'institucion_id' => $user->id_institucion,
                'plan_id' => $planId,
                'finalizado_at' => $user->finalizado ? ($user->reporte_generado_at ?? $user->updated_at) : null,
                'finalizado_por_user_id' => $user->finalizado ? $user->id : null,
                'reporte_generado_at' => $user->reporte_generado ? $user->reporte_generado_at : null,
                'reporte_generado_por_user_id' => $user->reporte_generado ? $user->id : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_report_statuses');
    }
};

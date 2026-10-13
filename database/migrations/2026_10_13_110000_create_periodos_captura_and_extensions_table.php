<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('periodos_captura')) {
            Schema::create('periodos_captura', function (Blueprint $table) {
                $table->id();
                $table->foreignId('plan_id')->constrained('cat_planes_estatales_desarrollo')->cascadeOnDelete();
                $table->string('nombre');
                $table->dateTime('fecha_inicio');
                $table->dateTime('fecha_fin');
                $table->boolean('activo')->default(true);
                $table->text('descripcion')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('institution_report_statuses')) {
            Schema::table('institution_report_statuses', function (Blueprint $table) {
                if (!Schema::hasColumn('institution_report_statuses', 'prorroga_hasta')) {
                    $table->dateTime('prorroga_hasta')->nullable()->after('finalizado_por_user_id');
                }
                if (!Schema::hasColumn('institution_report_statuses', 'motivo_prorroga')) {
                    $table->string('motivo_prorroga')->nullable()->after('prorroga_hasta');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('institution_report_statuses')) {
            Schema::table('institution_report_statuses', function (Blueprint $table) {
                if (Schema::hasColumn('institution_report_statuses', 'motivo_prorroga')) {
                    $table->dropColumn('motivo_prorroga');
                }
                if (Schema::hasColumn('institution_report_statuses', 'prorroga_hasta')) {
                    $table->dropColumn('prorroga_hasta');
                }
            });
        }

        Schema::dropIfExists('periodos_captura');
    }
};

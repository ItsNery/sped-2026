<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasIndex('datos_anuales', ['id_indicador', 'validado', 'anio'])) {
            Schema::table('datos_anuales', function (Blueprint $table) {
                $table->index(['id_indicador', 'validado', 'anio']);
            });
        }

        if (!Schema::hasIndex('indicadores_municipales', ['id_municipio', 'publica'])) {
            Schema::table('indicadores_municipales', function (Blueprint $table) {
                $table->index(['id_municipio', 'publica']);
            });
        }

        if (!Schema::hasIndex('resultados_indicadores_municipales', ['id_indicador', 'año', 'periodo'])) {
            Schema::table('resultados_indicadores_municipales', function (Blueprint $table) {
                $table->index(['id_indicador', 'año', 'periodo']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('datos_anuales', ['id_indicador', 'validado', 'anio'])) {
            Schema::table('datos_anuales', function (Blueprint $table) {
                $table->dropIndex(['id_indicador', 'validado', 'anio']);
            });
        }
        if (Schema::hasIndex('indicadores_municipales', ['id_municipio', 'publica'])) {
            Schema::table('indicadores_municipales', function (Blueprint $table) {
                $table->dropIndex(['id_municipio', 'publica']);
            });
        }
        if (Schema::hasIndex('resultados_indicadores_municipales', ['id_indicador', 'año', 'periodo'])) {
            Schema::table('resultados_indicadores_municipales', function (Blueprint $table) {
                $table->dropIndex(['id_indicador', 'año', 'periodo']);
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logs_cambios', function (Blueprint $table) {
            $table->unsignedBigInteger('usuario_id')->nullable()->after('id');
            $table->unsignedBigInteger('registro_id')->nullable()->after('tabla');
            $table->text('valor_anterior')->nullable()->after('columna');
            $table->text('valor_nuevo')->nullable()->after('valor_anterior');
            $table->string('ip', 45)->nullable()->after('valor_nuevo');
            $table->text('user_agent')->nullable()->after('ip');
            $table->string('request_id', 100)->nullable()->after('user_agent');
            $table->string('motivo')->nullable()->after('request_id');

            $table->index(['tabla', 'registro_id']);
            $table->index(['usuario_id', 'created_at']);
            $table->index('accion');
        });
    }

    public function down(): void
    {
        Schema::table('logs_cambios', function (Blueprint $table) {
            $table->dropIndex(['tabla', 'registro_id']);
            $table->dropIndex(['usuario_id', 'created_at']);
            $table->dropIndex(['accion']);
            $table->dropColumn([
                'usuario_id', 'registro_id', 'valor_anterior', 'valor_nuevo',
                'ip', 'user_agent', 'request_id', 'motivo',
            ]);
        });
    }
};

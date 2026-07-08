<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProgramaInstitucionalIndicadorTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar columnas de grupo y siglas a la tabla de cat_programas_derivados_institucionales
        // y hacer que imagen sea nullable
        Schema::table('cat_programas_derivados_institucionales', function (Blueprint $table) {
            $table->string('grupo')->nullable()->after('nombre');
            $table->string('siglas')->nullable()->after('grupo');
            $table->string('imagen')->nullable()->change();
        });

        // 2. Crear tabla pivote programa_institucional_indicador
        Schema::create('programa_institucional_indicador', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('indicador_id');
            $table->unsignedBigInteger('programa_institucional_id');
            $table->timestamps();

            $table->foreign('indicador_id', 'fk_prog_inst_ind_indicador')
                  ->references('id')->on('indicadors')->onDelete('cascade');
            $table->foreign('programa_institucional_id', 'fk_prog_inst_ind_institucional')
                  ->references('id')->on('cat_programas_derivados_institucionales')->onDelete('cascade');
        });

        // 3. Asignar grupos e iniciales por defecto a los 3 registros existentes
        DB::table('cat_programas_derivados_institucionales')
            ->where('nombre', 'like', '%Instituto%')
            ->update(['grupo' => 'Organismos Auxiliares', 'siglas' => 'ISSSTEP']);

        DB::table('cat_programas_derivados_institucionales')
            ->where('nombre', 'like', '%Movilidad%')
            ->update(['grupo' => 'Secretarías', 'siglas' => 'SMT']);

        DB::table('cat_programas_derivados_institucionales')
            ->where('nombre', 'like', '%Infraestructura%')
            ->update(['grupo' => 'Secretarías', 'siglas' => 'SI']);

        // 4. Transferir los indicadores polimórficos de origen a la nueva tabla pivote
        $indicadores = DB::table('indicadors')
            ->where('indicadorable_type', 'App\Models\CatProgramaDerivadoInstitucional')
            ->get();

        foreach ($indicadores as $ind) {
            DB::table('programa_institucional_indicador')->insert([
                'indicador_id' => $ind->id,
                'programa_institucional_id' => $ind->indicadorable_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Limpiar los campos polimórficos de origen para los registros transferidos
        DB::table('indicadors')
            ->where('indicadorable_type', 'App\Models\CatProgramaDerivadoInstitucional')
            ->update([
                'indicadorable_type' => null,
                'indicadorable_id' => null,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Restaurar las relaciones polimórficas de la tabla pivote a la tabla indicadors (por seguridad)
        $relaciones = DB::table('programa_institucional_indicador')->get();
        foreach ($relaciones as $rel) {
            DB::table('indicadors')
                ->where('id', $rel->indicador_id)
                ->update([
                    'indicadorable_type' => 'App\Models\CatProgramaDerivadoInstitucional',
                    'indicadorable_id' => $rel->programa_institucional_id,
                ]);
        }

        // 2. Dropear la tabla pivote
        Schema::dropIfExists('programa_institucional_indicador');

        // 3. Revertir cambios en cat_programas_derivados_institucionales
        Schema::table('cat_programas_derivados_institucionales', function (Blueprint $table) {
            $table->dropColumn(['grupo', 'siglas']);
            $table->string('imagen')->nullable(false)->change();
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("DROP VIEW IF EXISTS vista_consulta_indicadores");
        DB::statement("
            CREATE VIEW vista_consulta_indicadores AS
            SELECT 
                i.id AS indicador_id,
                i.nombre AS indicador_nombre,
                i.slug AS indicador_slug,
                i.descripcion AS indicador_descripcion,
                i.programa_derivado,
                i.programa,
                i.tematica,
                i.unidad_medida,
                i.periodicidad,
                i.cobertura,
                i.tendencia,
                i.formula,
                inst.nombre AS institucion_nombre,
                inst.titular AS institucion_titular,
                (SELECT GROUP_CONCAT(o.nombre SEPARATOR ', ') 
                 FROM ods o 
                 INNER JOIN indicador_ods io ON io.id_ods = o.id 
                 WHERE io.id_indicador = i.id) AS ods_relacionados,
                da.anio AS ultimo_anio_validado,
                da.valor_dato AS ultimo_valor_validado,
                da.resultados AS ultimo_resultado_validado
            FROM 
                indicadors i
            LEFT JOIN 
                instituciones inst ON i.id_institucion = inst.id
            LEFT JOIN 
                datos_anuales da ON da.id_indicador = i.id 
                AND da.validado = 1
                AND da.anio = (
                    SELECT MAX(da2.anio) 
                    FROM datos_anuales da2 
                    WHERE da2.id_indicador = i.id 
                    AND da2.validado = 1
                )
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS vista_consulta_indicadores");
    }
};

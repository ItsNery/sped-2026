<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatoAnualIndicador extends Model
{
    use HasFactory;
    protected $table = 'datos_anuales_indicadores';
    protected $fillable = [
        'id_indicador',
        'dato_2015',
        'dato_2016',
        'dato_2017',
        'dato_2018',
        'dato_2019',
        'dato_2020',
        'dato_2021',
        'dato_2022',
        'dato_2023',
        'dato_2024',
        'dato_2025',
        'fecha_actualizacion_2015',
        'fecha_actualizacion_2016',
        'fecha_actualizacion_2017',
        'fecha_actualizacion_2018',
        'fecha_actualizacion_2019',
        'fecha_actualizacion_2020',
        'fecha_actualizacion_2021',
        'fecha_actualizacion_2022',
        'fecha_actualizacion_2023',
        'fecha_actualizacion_2024',
        'fecha_actualizacion_2025',
        'resultados_2015',
        'resultados_2016',
        'resultados_2017',
        'resultados_2018',
        'resultados_2019',
        'resultados_2020',
        'resultados_2021',
        'resultados_2021',
        'resultados_2022',
        'resultados_2023',
        'resultados_2024',
        'evidencia_2015',
        'evidencia_2016',
        'evidencia_2017',
        'evidencia_2018',
        'evidencia_2019',
        'evidencia_2020',
        'evidencia_2021',
        'evidencia_2022',
        'evidencia_2023',
        'evidencia_2024',
        'evidencia_2025',
        'observaciones_2015',
        'observaciones_2016',
        'observaciones_2017',
        'observaciones_2018',
        'observaciones_2019',
        'observaciones_2020',
        'observaciones_2021',
        'observaciones_2022',
        'observaciones_2023',
        'observaciones_2024',
        'observaciones_2025',
        'modificado'
    ];

    // Relación inversa con Indicador
    public function indicador()
    {
        return $this->belongsTo(Indicador::class, 'id_indicador');
    }
    // En el modelo DatosAnualesIndicador
    protected static function booted()
    {
        static::updating(function ($datosAnualesIndicador) {
            // Si cualquier campo es modificado, establece `modificado` en 1
            if ($datosAnualesIndicador->isDirty()) {
                $datosAnualesIndicador->modificado = 1;

                // Opcional: Resetear `indicador_validado` en el indicador principal
                $datosAnualesIndicador->indicador->indicador_validado = 0;
                $datosAnualesIndicador->indicador->save();
            }
        });
    }
}

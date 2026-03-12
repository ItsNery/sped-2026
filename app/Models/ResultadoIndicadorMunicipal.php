<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo ResultadoIndicadorMunicipal.
 * Almacena un dato periódico para un IndicadorMunicipal.
 * Contiene la lógica para invalidar a su indicador padre cuando se modifica.
 */
class ResultadoIndicadorMunicipal extends Model
{
    use HasFactory;
    protected $table = 'resultados_indicadores_municipales';
    protected $fillable = [
        'id_indicador',
        'periodicidad_id',
        'año',
        'periodo',
        'dato',
        'resultado',
    ];

    /**
     * Define la relación inversa con IndicadorMunicipal.
     */
    public function indicador()
    {
        return $this->belongsTo(IndicadorMunicipal::class, 'id_indicador');
    }
    public function periodicidad()
    {
        return $this->belongsTo(PeriodicidadIndicadorMunicipal::class, 'periodicidad_id');
    }

    /**
     * Eventos 'saved' y 'deleted' de Eloquent.
     * Se disparan después de guardar o eliminar un resultado.
     * Ambos eventos buscan el IndicadorMunicipal padre y establecen
     * su estado 'validado' a 0 (No Validado), forzando una revisión.
     */
    protected static function booted()
    {
        static::saved(function ($resultado) {
            $indicadorMunicipal = $resultado->indicador;
            if ($indicadorMunicipal) {
                $indicadorMunicipal->validado = 0;
                $indicadorMunicipal->save();
            }
        });

        static::deleted(function ($resultado) {
            $indicadorMunicipal = $resultado->indicador;
            if ($indicadorMunicipal) {
                $indicadorMunicipal->validado = 0;
                $indicadorMunicipal->save();
            }
        });
    }
}

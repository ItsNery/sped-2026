<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo IndicadorMunicipal.
 * Representa un indicador a nivel municipal. Contiene la lógica para
 * desvalidarse automáticamente cuando se detectan cambios.
 */
class IndicadorMunicipal extends Model
{
    use HasFactory;
    protected $table = 'indicadores_municipales';
    protected $fillable = [
        'indicador',
        'instrumento',
        'eje_indicador',
        'tematica',
        'descripcion',
        'unidad_medida',
        'linea_base',
        'dato_linea',
        'meta_2024',
        'fuente',
        'liga',
        'periodicidad_id',
        'cobertura',
        'tendencia',
        'id_tipo',
        'id_nivel',
        'id_dimension',
        'formula',
        'dependencia',
        'publica',
        'validado',
        'id_municipio',
        'proxima_actualizacion',
    ];

    public function resultados()
    {
        return $this->hasMany(ResultadoIndicadorMunicipal::class, 'id_indicador');
    }
    public function periodicidad()
    {
        return $this->belongsTo(PeriodicidadIndicadorMunicipal::class, 'periodicidad_id');
    }

    public function ods()
    {
        return $this->belongsToMany(
            Odses::class, // Modelo Ods
            'indicadores_municipales_ods', // Tabla pivote
            'id_indicador', // Llave foránea de esta tabla en la pivote
            'id_ods' // Llave foránea del modelo relacionado en la pivote
        );
    }
    public function odsRelations()
    {
        // Argumentos de hasMany:
        // 1. Modelo relacionado (IndicadorMunicipalODS)
        // 2. Clave foránea en la tabla IndicadorMunicipalODS (ej: 'id_indicador')
        // 3. Clave local (PK) en la tabla IndicadorMunicipal (ej: 'id')
        return $this->hasMany(IndicadorMunicipalODS::class, 'id_indicador', 'id')
            ->orderBy('id_ods', 'asc'); // Orden consistente es clave aquí
    }

    public function municipio()
    {
        return $this->belongsTo(CatMunicipio::class, 'id_municipio');
    }
    public function tipo()
    {
        return $this->belongsTo(CatTipo::class, 'id_tipo');
    }
    public function dimension()
    {
        return $this->belongsTo(CatDimension::class, 'id_dimension');
    }
    public function nivel()
    {
        return $this->belongsTo(CatNivel::class, 'id_nivel');
    }

    /**
     * Evento 'saving' de Eloquent.
     * Se dispara justo antes de guardar (crear o actualizar) el modelo.
     * Si se está modificando cualquier campo que no sea 'validado',
     * fuerza el campo 'validado' a 0 (No Validado).
     */
    protected static function booted()
    {
        static::saving(function ($indicadorMunicipal) {
            // Solo cambiar a 0 si el campo validado no fue modificado manualmente
            if (!$indicadorMunicipal->isDirty('validado')) {
                $indicadorMunicipal->validado = 0;
            }
        });
    }
}

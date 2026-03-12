<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Odses extends Model
{
    use HasFactory;
    protected $table = 'ods';
    protected $fillable = [
        'nombre',
    ];
    // Relación muchos a muchos con Indicador
    public function indicadores()
    {
        return $this->belongsToMany(Indicador::class, 'indicador_ods', 'id_ods', 'id_indicador');
    }
    public function indicadoresMunicipales()
    {
        return $this->belongsToMany(IndicadorMunicipal::class, 'indicadores_municipales_ods', 'id_ods', 'id_indicador');
    }
}

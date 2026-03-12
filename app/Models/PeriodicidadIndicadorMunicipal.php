<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodicidadIndicadorMunicipal extends Model
{
    use HasFactory;
    protected $table = 'periodicidad_indicadores_municipales';
    protected $fillable = [
        'nombre',
    ];
    public function indicadores()
    {
        return $this->hasMany(IndicadorMunicipal::class, 'periodicidad_id');
    }
}

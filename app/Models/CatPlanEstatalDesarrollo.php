<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatPlanEstatalDesarrollo extends Model
{
    use HasFactory;

    protected $table = 'cat_planes_estatales_desarrollo';

    protected $fillable = [
        'nombre',
        'gobernador',
    ];

    /**
     * Relación polimórfica: Un plan puede tener muchos indicadores.
     */
    public function indicadores()
    {
        return $this->morphMany(Indicador::class, 'indicadorable');
    }

    /**
     * Relación con los ejes del plan.
     */
    public function catEjes()
    {
        return $this->hasMany(CatEje::class, 'plan_id');
    }
}

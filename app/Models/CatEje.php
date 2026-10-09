<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatEje extends Model
{
    use HasFactory;

    protected $table = 'cat_ejes';

    protected $fillable = [
        'nombre',
        'numero',
        'color',
        'plan_id',
    ];

    /**
     * Relación con el Plan Estatal de Desarrollo.
     */
    public function catPlanEstatalDesarrollo()
    {
        return $this->belongsTo(CatPlanEstatalDesarrollo::class, 'plan_id');
    }

    /**
     * Relación polimórfica: Un eje puede tener muchos indicadores.
     */
    public function indicadores()
    {
        return $this->morphMany(Indicador::class, 'indicadorable');
    }
}

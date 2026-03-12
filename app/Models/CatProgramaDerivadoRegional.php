<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatProgramaDerivadoRegional extends Model
{
    use HasFactory;

    protected $table = 'cat_programas_derivados_regionales';

    protected $fillable = [
        'nombre',
        'imagen',
        'descripcion',
        'color',
        'plan_estatal',
        'documento',
    ];

    /**
     * Relación con el Plan Estatal de Desarrollo.
     * Un programa derivado regional pertenece a un plan estatal.
     */
    public function catPlanEstatalDesarrollo()
    {
        return $this->belongsTo(CatPlanEstatalDesarrollo::class, 'plan_estatal');
    }

    /**
     * Relación polimórfica: Un programa derivado puede tener muchos indicadores.
     */
    public function indicadores()
    {
        return $this->morphMany(Indicador::class, 'indicadorable');
    }
}

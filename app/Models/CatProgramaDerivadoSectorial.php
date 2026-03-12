<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatProgramaDerivadoSectorial extends Model
{
    use HasFactory;

     protected $table = 'cat_programas_derivados_sectoriales';

    protected $fillable = [
        'nombre',
        'imagen',
        'descripcion',
        'color',
        'plan_estatal', // FK corrected
        'documento',
    ];

    /**
     * Relación con el Plan Estatal de Desarrollo.
     * Un programa derivado especial pertenece a un plan estatal.
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

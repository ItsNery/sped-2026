<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo CarruselIndicador.
 *
 * Representa un ítem dentro del carrusel de indicadores del sitio.
 * Cada ítem del carrusel está asociado a un Indicador.
 */
class CarruselIndicador extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     * @var string
     */
    protected $table = 'carrusel_indicadors';

    /**
     * Los atributos que son asignables en masa.
     * @var array<int, string>
     */
    protected $fillable = ['id', 'imagen', 'id_indicador'];

    /**
     * Define la relación inversa de uno-a-uno con Indicador.
     * Un ítem del carrusel pertenece a un único Indicador.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function indicador()
    {
        return $this->belongsTo(Indicador::class, 'id_indicador');
    }
}

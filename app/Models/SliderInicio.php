<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo SliderInicio.
 *
 * Representa una diapositiva (slide) en el carrusel principal de la página de inicio.
 */
class SliderInicio extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     * @var string
     */
    protected $table = 'slider_inicios';

    /**
     * Los atributos que son asignables en masa.
     * @var array<int, string>
     */
    protected $fillable = [
        'titulo',
        'descripcion',
        'imagen',
        'orden',
        'enlace',
        'activo'
    ];
}

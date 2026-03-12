<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo LogCambio.
 *
 * Representa una entrada en el registro de auditoría (log) de la aplicación.
 * Cada instancia de este modelo es un registro de una acción (crear, actualizar, etc.)
 * realizada sobre otro registro en la base de datos.
 */
class LogCambio extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     * @var string
     */
    protected $table = 'logs_cambios';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'usuario',
        'tabla',
        'columna',
        'accion'
    ];
}

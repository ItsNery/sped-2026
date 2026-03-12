<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatoAnual extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'datos_anuales'; // Nombre sugerido para la nueva tabla
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_indicador',
        'anio', // Ej: 2023, 2024
        'valor_dato',
        'fecha_actualizacion', // Fecha de actualización para este dato anual específico
        'resultados',
        'evidencia', // Campo para la evidencia de este año
        'observaciones',
        'validado', // Para indicar si este dato anual ha sido validado por un enlace o el administrador
        'modificado', // Para rastrear si este registro anual específico fue modificado
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_actualizacion' => 'date',
        'anio' => 'integer',
        'valor_dato' => 'decimal:6', // Ajusta según la precisión que necesites
        'validado' => 'boolean',
        'modificado' => 'boolean',
    ];
    /**
     * Get the indicador that owns the anio.
     */
    public function indicador()
    {
        return $this->belongsTo(Indicador::class, 'id_indicador');
    }

    /**
     * The "booted" method of the model.
     *
     * Se ejecuta cuando se actualiza un registro de DatoAnual.
     * Marca este registro como modificado y el indicador principal como no validado.
     */
    protected static function booted()
    {
        static::updating(function ($datoAnual) {
            // Campos que al ser modificados requieren una nueva validación
            $camposSensibles = ['valor_dato', 'resultados', 'evidencia', 'observaciones', 'anio', 'fecha_actualizacion'];

            if ($datoAnual->isDirty($camposSensibles)) {
                // Siempre que cambie un dato, se marca como no validado y modificado
                $datoAnual->validado = false;
                $datoAnual->modificado = true;

                // También desvalidamos el indicador principal para que no se muestre información parcial o no oficial
                $indicador = $datoAnual->indicador;
                if ($indicador && $indicador->indicador_validado) {
                    $indicador->indicador_validado = false;
                    $indicador->save();
                }
            }
        });
    }
}

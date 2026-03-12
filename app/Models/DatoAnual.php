<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DatoAnual
 * * Representa un registro de valor anual asociado a un Indicador.
 * Contiene la lógica para invalidar automáticamente la información si se editan datos sensibles.
 * * @package App\Models
 * * Propiedades de la base de datos:
 * @property int $id
 * @property int $id_indicador
 * @property int $anio
 * @property float|string|null $valor_dato
 * @property \Illuminate\Support\Carbon|null $fecha_actualizacion
 * @property string|null $resultados
 * @property string|null $evidencia
 * @property string|null $observaciones
 * @property bool $validado
 * @property bool $modificado
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * * Relaciones:
 * @property-read \App\Models\Indicador $indicador
 */
class DatoAnual extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'datos_anuales'; // Nombre sugerido para la nueva tabla

    /**
     * Los atributos que son asignables en masa.
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
     * Los atributos que deben ser convertidos a tipos nativos (Casting).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_actualizacion' => 'date',
        'anio' => 'integer',
        'valor_dato' => 'decimal:6',
        'validado' => 'boolean',
        'modificado' => 'boolean',
    ];

    /**
     * Obtiene el indicador al que pertenece este registro anual.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function indicador()
    {
        return $this->belongsTo(Indicador::class, 'id_indicador');
    }

    /**
     * Inicializa eventos del modelo (Booting).
     *
     * Se ejecuta cuando se actualiza un registro de DatoAnual.
     * Si se modifican campos críticos, marca este registro como modificado (y no validado),
     * y revoca la validación del Indicador padre por seguridad.
     * * @return void
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

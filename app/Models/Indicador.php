<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;

/**
 * Modelo Indicador.
 * Contiene toda la lógica de negocio para el cálculo de avances y semaforización.
 */
class Indicador extends Model
{
    use HasFactory;
    protected $table = 'indicadors';
    protected $fillable = [
        'nombre',
        'slug',
        'programa_derivado',
        'programa',
        // 'cod_tematica', // 
        'tematica',
        'id_institucion',
        'linea_base',       // Año de la línea base, ej: 2015
        'dato_linea_base',  // Valor de la línea base
        'meta_2024',        // O meta_2030 
        'unidad_medida',
        'id_usuario',
        'fuente',
        'liga',
        'descripcion',
        'periodicidad',
        // 'periodo',
        'cobertura',
        'tendencia',
        'fecha_actualizacion', // Fecha de actualización inicial del indicador
        // 'resultados', 
        'formula',
        'indicador_validado',
        'indicadorable_id',
        'indicadorable_type',
        'slug',
    ];
    public function getRouteKeyName()
    {
        return 'slug';
    }
    protected static function boot()
    {
        parent::boot();

        // Antes de crear (creating)
        static::creating(function ($indicador) {
            $indicador->slug = Str::slug($indicador->nombre);
        });

        // Antes de actualizar (updating) - opcional, si quieres que cambie la URL si cambia el nombre
        static::updating(function ($indicador) {
            $indicador->slug = Str::slug($indicador->nombre);
        });
    }
    // Relación muchos a muchos con Ods
    public function ods()
    {
        return $this->belongsToMany(Odses::class, 'indicador_ods', 'id_indicador', 'id_ods');
    }

    /**
     * Relación polimórfica para obtener el programa o plan al que pertenece el indicador.
     */
    public function indicadorable()
    {
        return $this->morphTo();
    }

    /**
     * Relación con DatoAnual. Un indicador tiene MUCHOS registros de datos anuales.
     */
    public function datosAnuales()
    {
        return $this->hasMany(DatoAnual::class, 'id_indicador');
    }

    /**
     * Accesor que devuelve solo los datos anuales validados.
     * Uso: $indicador->datos_anuales_validados
     */
    public function getDatosAnualesValidadosAttribute()
    {
        if ($this->relationLoaded('datosAnuales') && $this->relations['datosAnuales'] instanceof EloquentCollection) {
            return $this->relations['datosAnuales']
                ->filter(function ($da) {
                    return isset($da->validado) && $da->validado;
                })
                ->values();
        }

        return $this->datosAnuales()->where('validado', true)->get();
    }

    // Relación inversa con User
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    // Relación inversa con Institucion
    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
    }

    /**
     * Escucha el evento 'updated' del modelo.
     * Si el indicador se marca como validado, resetea el estado 'modificado'
     * de todos sus datos anuales asociados.
     */
    protected static function booted()
    {
        static::updated(function ($indicador) {
            if ($indicador->isDirty('indicador_validado') && $indicador->indicador_validado) {
                $indicador->datosAnuales()->update(['modificado' => false]);
            }
        });
    }

    /**
     * Orquesta el cálculo completo de la semaforización.
     * @return array
     */
    public function calcularSemaforizacion($soloValidados = false)
    {
        $ultimoDato = $this->calcularUltimoDato($soloValidados);
        $avance = $this->calcularAvance($ultimoDato);
        $semaforizacion = $this->determinarSemaforizacion($avance);

        return [
            'anio_ultimo_dato' => $ultimoDato['anio'],
            'ultimo_dato' => $ultimoDato['valor'],
            'avance' => $avance,
            'semaforizacion' => $semaforizacion,
        ];
    }

    /**
     * Busca el último dato anual disponible o la línea base.
     * @return array
     */
    private function calcularUltimoDato($soloValidados = false)
    {
        // --- PARTE 1: Buscar en Datos Anuales ---
        $ultimoDatoAnual = null;

        // Decidir qué colección o query usar
        $fuenteDatos = $soloValidados ? $this->getDatosAnualesValidadosAttribute() : $this->datosAnuales;

        if ($fuenteDatos instanceof EloquentCollection) {
            $ultimoDatoAnual = $fuenteDatos
                ->filter(function ($da) {
                    return isset($da->valor_dato) && !is_null($da->valor_dato) && trim((string)$da->valor_dato) !== '';
                })
                ->sortByDesc('anio')
                ->first();
        } else {
            // Fallback si es un query (esto pasa si no se cargó la relación y se llamó al accessor)
            $query = $this->datosAnuales();
            if ($soloValidados) {
                $query->where('validado', true);
            }
            $ultimoDatoAnual = $query->whereNotNull('valor_dato')
                ->orderBy('anio', 'desc')
                ->first();
        }

        if ($ultimoDatoAnual) {
            return [
                'valor' => $ultimoDatoAnual->valor_dato,
                'anio' => $ultimoDatoAnual->anio,
            ];
        }

        // --- PARTE 2: EL SALVAVIDAS (Línea Base) ---
        // Si llegamos aquí es porque NO hubo dato anual.
        // Verificamos si existe dato en la línea base.
        if (!is_null($this->dato_linea_base) && trim((string)$this->dato_linea_base) !== '') {
            return [
                'valor' => $this->dato_linea_base, // Devuelve "21.6"
                'anio' => $this->linea_base,       // Devuelve "2022"
            ];
        }

        return ['valor' => null, 'anio' => null];
    }

    /**
     * Calcula el porcentaje de avance hacia la meta según la tendencia.
     * @param array $ultimoDato
     * @return float|null
     */
    private function calcularAvance($ultimoDato)
    {
        // 1. Validaciones iniciales
        if ($ultimoDato['valor'] === null) return null;

        // 2. Limpieza de Meta
        $metaLimpia = $this->meta_2024 !== null ? str_replace(',', '', (string)$this->meta_2024) : null;
        if (!is_numeric($metaLimpia) || $metaLimpia == 0) return null; // Evitar división por cero
        $meta = (float)$metaLimpia;

        // 3. Limpieza de Valor (Dato Anual o Línea Base)
        $valorLimpio = str_replace(',', '', (string)$ultimoDato['valor']);
        if (!is_numeric($valorLimpio)) return null;
        $valor = (float)$valorLimpio;

        // 4. Lógica de Tendencias
        $tendencia = strtolower(trim((string)$this->tendencia)); // "constante"

        // --- AQUÍ ESTÁ EL ARREGLO ---
        if ($tendencia === "mayor es mejor") {
            return ($valor / $meta) * 100;
        } elseif ($tendencia === "menor es mejor") {
            return max(0, 100 - ((($valor - $meta) / $meta) * 100));
        } elseif ($tendencia === "constante") {
            // Lógica para constante: Si la meta es mantener 21.6 y tienes 21.6, es el 100%
            // Usamos la misma lógica que "mayor es mejor" para calcular el % de cumplimiento
            return ($valor / $meta) * 100;
        }

        // Si no coincide con ninguna tendencia conocida
        return null;
    }

    /**
     * Determina el color del semáforo basado en el porcentaje de avance.
     * @param float|null $avance
     * @return string
     */
    private function determinarSemaforizacion($avance)
    {
        if ($avance === null) return "No clasificado";
        if ($avance >= 110) return "Excedido";
        if ($avance >= 91) return "Aceptable"; // De 91 a 109.9
        if ($avance >= 71) return "Moderado";  // De 71 a 90.9
        return "Insuficiente"; // Menos de 71
    }
    /**
     * Accesor Mágico: Cuando la vista pida $indicador->dato_reciente
     * Laravel ejecutará esto automáticamente.
     */
    public function getDatoRecienteAttribute()
    {
        // 1. Reutilizamos la lógica que ya funciona
        $info = $this->calcularUltimoDato();

        // 2. Si hay valor, lo devolvemos como float para que las vistas lo formateen
        if (!is_null($info['valor'])) {
            $valorLimpio = str_replace(',', '', (string)$info['valor']);
            return is_numeric($valorLimpio) ? (float)$valorLimpio : $info['valor'];
        }

        return null;
    }

    /**
     * Accesor Mágico: Cuando la vista pida $indicador->anio_reciente
     */
    public function getAnioRecienteAttribute()
    {
        // 1. Reutilizamos la misma lógica
        $info = $this->calcularUltimoDato();

        // 2. Devolvemos el año
        return $info['anio'] ?? null;
    }

    /**
     * Accesor Mágico: Cuando la vista pida $indicador->dato_reciente_validado
     */
    public function getDatoRecienteValidadoAttribute()
    {
        $info = $this->calcularUltimoDato(true);
        if (!is_null($info['valor'])) {
            $valorLimpio = str_replace(',', '', (string)$info['valor']);
            return is_numeric($valorLimpio) ? (float)$valorLimpio : $info['valor'];
        }
        return null;
    }

    /**
     * Accesor Mágico: Cuando la vista pida $indicador->anio_reciente_validado
     */
    public function getAnioRecienteValidadoAttribute()
    {
        $info = $this->calcularUltimoDato(true);
        return $info['anio'] ?? null;
    }

    /**
     * Accesor para obtener el último dato registrado.
     */
    public function getUltimoDatoAttribute()
    {
        return $this->calcularSemaforizacion()['ultimo_dato'];
    }

    /**
     * Accesor para obtener el año del último dato registrado.
     */
    public function getAnioUltimoDatoAttribute()
    {
        return $this->calcularSemaforizacion()['anio_ultimo_dato'];
    }

    /**
     * Accesor para obtener el porcentaje de avance.
     */
    public function getAvanceAttribute()
    {
        return $this->calcularSemaforizacion()['avance'];
    }

    /**
     * Accesor para obtener el estado de la semaforización.
     */
    /**
     * Accesor para obtener el estado de la semaforización.
     */
    public function getSemaforizacionAttribute()
    {
        return $this->calcularSemaforizacion()['semaforizacion'];
    }

    /**
     * Accesor para obtener el estado de la semaforización considerando SOLO datos validados.
     * Útil para el sitio público.
     */
    public function getSemaforizacionValidadaAttribute()
    {
        return $this->calcularSemaforizacion(true)['semaforizacion'];
    }

    /**
     * Obtiene y formatea un valor anual específico para las vistas.
     * @param int $year
     * @param string $default
     * @return string
     */
    public function getValorDatoAnual($year, $default = 'N/D', $soloValidados = false)
    {
        $coleccion = $soloValidados ? $this->getDatosAnualesValidadosAttribute() : $this->datosAnuales;

        if (!($coleccion instanceof EloquentCollection) || $coleccion->isEmpty()) {
            return $default;
        }
        $datoAnual = $coleccion->firstWhere('anio', $year);
        if ($datoAnual && isset($datoAnual->valor_dato) && trim((string)$datoAnual->valor_dato) !== '') {
            $valor = $datoAnual->valor_dato;
            $valorNumerico = filter_var($valor, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
            if (is_numeric($valorNumerico)) {
                // return number_format((float)str_replace(',', '', $valorNumerico), 2, '.', ',');
                 return number_format((float)str_replace(',', '', $valorNumerico), $this->id == 100 ? 6 : 2, '.', ',');
            }
            return htmlspecialchars($valor);
        }
        return $default;
    }

    public function getProximaFechaActualizacionParaVista($default = 'N/D', $soloValidados = false)
    {
        $coleccion = $soloValidados ? $this->getDatosAnualesValidadosAttribute() : $this->datosAnuales;
        $fechaMasRecienteAnual = null;
        $fechaFormateada = $default;

        if ($coleccion instanceof EloquentCollection && $coleccion->isNotEmpty()) {
            $datoConFecha = $coleccion
                ->filter(function ($da) {
                    return isset($da->fecha_actualizacion) && !is_null($da->fecha_actualizacion) && trim((string) $da->fecha_actualizacion) !== '';
                })
                ->sortByDesc('anio')->first();
            if ($datoConFecha) {
                try {
                    $fechaMasRecienteAnual = Carbon::parse($datoConFecha->fecha_actualizacion)->format('d-m-Y');
                } catch (\Exception $e) { /* Log::warning(...) */
                }
            }
        }
        if ($fechaMasRecienteAnual) {
            $fechaFormateada = $fechaMasRecienteAnual;
        } elseif (!empty($this->fecha_actualizacion)) {
            try {
                $fechaFormateada = Carbon::parse($this->fecha_actualizacion)->format('d-m-Y');
            } catch (\Exception $e) {
                $fechaFormateada = 'Fecha Inválida';
            }
        }
        return $fechaFormateada;
    }

    public function getResultadosParaVista($default = 'Sin resultados registrados.', $soloValidados = false)
    {
        $coleccion = $soloValidados ? $this->getDatosAnualesValidadosAttribute() : $this->datosAnuales;
        $resultadosMostrados = null;
        if ($coleccion instanceof EloquentCollection && $coleccion->isNotEmpty()) {
            $datoConResultados = $coleccion
                ->filter(function ($da) {
                    return isset($da->resultados) && !is_null($da->resultados) && trim((string) $da->resultados) !== '';
                })
                ->sortByDesc('anio')->first();
            if ($datoConResultados) {
                $resultadosMostrados = $datoConResultados->resultados;
            }
        }
        if (is_null($resultadosMostrados)) {
            $resultadosMostrados = $this->resultados;
        }
        return $resultadosMostrados ?? $default;
    }
}

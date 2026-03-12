<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;

/**
 * Class Indicador
 * * Modelo que contiene toda la lógica de negocio para el cálculo de avances y semaforización.
 * * @package App\Models
 * * @property int $id
 * @property string $nombre
 * @property string $slug
 * @property string|null $programa_derivado
 * @property string|null $programa
 * @property string|null $tematica
 * @property int|null $id_institucion
 * @property int|null $linea_base
 * @property float|string|null $dato_linea_base
 * @property float|string|null $meta_2024
 * @property string|null $unidad_medida
 * @property int|null $id_usuario
 * @property string|null $fuente
 * @property string|null $liga
 * @property string|null $descripcion
 * @property string|null $periodicidad
 * @property string|null $cobertura
 * @property string|null $tendencia
 * @property string|null $fecha_actualizacion
 * @property string|null $formula
 * @property bool $indicador_validado
 * @property int|null $indicadorable_id
 * @property string|null $indicadorable_type
 * * Accessors (Propiedades mágicas)
 * @property-read EloquentCollection $datos_anuales_validados
 * @property-read float|string|null $dato_reciente
 * @property-read int|null $anio_reciente
 * @property-read float|string|null $dato_reciente_validado
 * @property-read int|null $anio_reciente_validado
 * @property-read float|string|null $ultimo_dato
 * @property-read int|null $anio_ultimo_dato
 * @property-read float|null $avance
 * @property-read string $semaforizacion
 * @property-read string $semaforizacion_validada
 */
class Indicador extends Model
{
    use HasFactory;

    /**
     * Tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'indicadors';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
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
        'meta_2024',        // El dato de la meta, se quedó en meta_2024, pero puede ser 2030, 2036,etc. 
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

    /**
     * Obtiene la clave de ruta para el modelo.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Inicializa el modelo y sus eventos (Booting).
     *
     * @return void
     */
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

    /**
     * Escucha eventos del modelo después de que ha sido inicializado.
     * Si el indicador se marca como validado, resetea el estado 'modificado'
     * de todos sus datos anuales asociados.
     *
     * @return void
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
     * Relación muchos a muchos con Odses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ods()
    {
        return $this->belongsToMany(Odses::class, 'indicador_ods', 'id_indicador', 'id_ods');
    }

    /**
     * Relación polimórfica para obtener el programa o plan al que pertenece el indicador.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function indicadorable()
    {
        return $this->morphTo();
    }

    /**
     * Relación con DatoAnual. Un indicador tiene MUCHOS registros de datos anuales.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function datosAnuales()
    {
        return $this->hasMany(DatoAnual::class, 'id_indicador');
    }

    /**
     * Relación inversa con User (Responsable del indicador).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Relación inversa con Institucion.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
    }

    /**
     * Accesor que devuelve solo los datos anuales validados.
     *
     * @return EloquentCollection
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

    /**
     * Orquesta el cálculo completo de la semaforización.
     *
     * @param bool $soloValidados Determina si se deben usar solo datos validados.
     * @return array{anio_ultimo_dato: int|null, ultimo_dato: float|string|null, avance: float|null, semaforizacion: string}
     */
    public function calcularSemaforizacion(bool $soloValidados = false): array
    {
        $ultimoDato = $this->calcularUltimoDato($soloValidados);

        // Verificamos si es solo la línea base
        $esLineaBase = $ultimoDato['es_linea_base'] ?? false;

        if ($esLineaBase) {
            // No calculamos avance ni semáforo tradicional
            $avance = null;
            $semaforizacion = "Solo línea base"; // o "Solo línea base"
        } else {
            // Lógica normal
            $avance = $this->calcularAvance($ultimoDato);
            $semaforizacion = $this->determinarSemaforizacion($avance);
        }

        return [
            'anio_ultimo_dato' => $ultimoDato['anio'],
            'ultimo_dato'      => $ultimoDato['valor'],
            'avance'           => $avance,
            'semaforizacion'   => $semaforizacion,
        ];
    }

    /**
     * Busca el último dato anual disponible o la línea base.
     *
     * @param bool $soloValidados
     * @return array{valor: float|string|null, anio: int|null}
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
            // Validamos si el "último" año que encontramos es en realidad la línea base (o incluso anterior).
            // Si el año del dato es menor o igual a la línea base, entonces es línea base.
            $esIgualALineaBase = (!is_null($this->linea_base) && $ultimoDatoAnual->anio <= $this->linea_base);

            return [
                'valor' => $ultimoDatoAnual->valor_dato,
                'anio' => $ultimoDatoAnual->anio,
                'es_linea_base' => $esIgualALineaBase,
            ];
        }

        // --- PARTE 2: EL SALVAVIDAS (Línea Base) ---
        // Si llegamos aquí es porque NO hubo dato anual.
        // Verificamos si existe dato en la línea base.
        if (!is_null($this->dato_linea_base) && trim((string)$this->dato_linea_base) !== '') {
            return [
                'valor' => $this->dato_linea_base, // Devuelve "21.6"
                'anio' => $this->linea_base,       // Devuelve "2022"
                'es_linea_base' => true,
            ];
        }

        return ['valor' => null, 'anio' => null, 'es_linea_base' => false];
    }

    /**
     * Calcula el porcentaje de avance hacia la meta según la tendencia.
     *
     * @param array{valor: float|string|null, anio: int|null} $ultimoDato
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
     * Determina el color o estado del semáforo basado en el porcentaje de avance.
     *
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
     * Accesor para obtener el dato más reciente disponible.
     *
     * @return float|string|null
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
     * Accesor para obtener el año del dato más reciente disponible.
     *
     * @return int|null
     */
    public function getAnioRecienteAttribute()
    {
        // 1. Reutilizamos la misma lógica
        $info = $this->calcularUltimoDato();

        // 2. Devolvemos el año
        return $info['anio'] ?? null;
    }

    /**
     * Accesor para obtener el dato más reciente, considerando solo los validados.
     *
     * @return float|string|null
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
     * Accesor para obtener el año del dato validado más reciente.
     *
     * @return int|null
     */
    public function getAnioRecienteValidadoAttribute()
    {
        $info = $this->calcularUltimoDato(true);
        return $info['anio'] ?? null;
    }

    /**
     * Accesor para obtener el último dato registrado mediante la lógica de semaforización.
     *
     * @return float|string|null
     */
    public function getUltimoDatoAttribute()
    {
        return $this->calcularSemaforizacion()['ultimo_dato'];
    }

    /**
     * Accesor para obtener el año del último dato registrado mediante la lógica de semaforización.
     *
     * @return int|null
     */
    public function getAnioUltimoDatoAttribute()
    {
        return $this->calcularSemaforizacion()['anio_ultimo_dato'];
    }

    /**
     * Accesor para obtener el porcentaje de avance general.
     *
     * @return float|null
     */
    public function getAvanceAttribute()
    {
        return $this->calcularSemaforizacion()['avance'];
    }

    /**
     * Accesor para obtener el estado de la semaforización general.
     *
     * @return string
     */
    public function getSemaforizacionAttribute()
    {
        return $this->calcularSemaforizacion()['semaforizacion'];
    }

    /**
     * Accesor para obtener el estado de la semaforización considerando SOLO datos validados.
     *
     * @return string
     */
    public function getSemaforizacionValidadaAttribute()
    {
        return $this->calcularSemaforizacion(true)['semaforizacion'];
    }

    /**
     * Obtiene y formatea un valor anual específico para las vistas.
     *
     * @param int $year El año a consultar.
     * @param string $default Valor por defecto si no se encuentra.
     * @param bool $soloValidados Filtrar solo por datos validados.
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
                return number_format((float)str_replace(',', '', $valorNumerico), $this->id == 100 ? 6 : 2, '.', ',');
            }
            return htmlspecialchars($valor);
        }
        return $default;
    }

    /**
     * Calcula la fecha de actualización más reciente para ser mostrada en la vista.
     *
     * @param string $default Valor por defecto.
     * @param bool $soloValidados Evaluar solo datos validados.
     * @return string Fecha formateada (d-m-Y) o valor por defecto.
     */
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

    /**
     * Obtiene los resultados descriptivos más recientes para mostrarlos en la vista.
     *
     * @param string $default Mensaje por defecto si no hay resultados.
     * @param bool $soloValidados Evaluar solo datos validados.
     * @return string
     */
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

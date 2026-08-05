<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;

/**
 * Modelo de indicadores y sus cálculos de avance y semaforización.
 *
 * @property int $id
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
 * Accessors (propiedades mágicas):
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
        'resultados',
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
     * Registra los eventos de creación y actualización del modelo.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($indicador) {
            $indicador->slug = static::uniqueSlug($indicador->nombre);
        });

        static::updating(function ($indicador) {
            if ($indicador->isDirty('nombre')) {
                $indicador->slug = static::uniqueSlug($indicador->nombre, $indicador->id);
            }
        });
    }

    /**
     * Limita los indicadores a un plan mediante sus relaciones actuales.
     */
    public function scopeForPlan(Builder $query, int $planId): Builder
    {
        return $query->where(function (Builder $query) use ($planId) {
            $query->whereHasMorph(
                'indicadorable',
                [CatEje::class],
                fn (Builder $parent) => $parent->where('plan_id', $planId)
            )->orWhereHasMorph(
                'indicadorable',
                [
                    CatProgramaDerivadoSectorial::class,
                    CatProgramaDerivadoEspecial::class,
                    CatProgramaDerivadoRegional::class,
                ],
                fn (Builder $parent) => $parent->where('plan_estatal', $planId)
            )->orWhereHas(
                'programasInstitucionales',
                fn (Builder $program) => $program->where('plan_estatal', $planId)
            );
        });
    }

    private static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'indicador';
        $slug = $base;
        $suffix = 1;

        while (static::query()
            ->where('slug', $slug)
            ->when(
                $ignoreId !== null,
                fn (Builder $query) => $query->where($query->getModel()->getKeyName(), '!=', $ignoreId)
            )
            ->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    /**
     * Obtiene los Objetivos de Desarrollo Sostenible asociados.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ods()
    {
        return $this->belongsToMany(Odses::class, 'indicador_ods', 'id_indicador', 'id_ods');
    }

    /**
     * Obtiene el programa o plan al que pertenece el indicador.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function indicadorable()
    {
        return $this->morphTo();
    }

    /**
     * Obtiene los programas institucionales asociados.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function programasInstitucionales()
    {
        return $this->belongsToMany(
            CatProgramaDerivadoInstitucional::class,
            'programa_institucional_indicador',
            'indicador_id',
            'programa_institucional_id'
        )->withTimestamps();
    }

    /**
     * Obtiene los datos anuales del indicador.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function datosAnuales()
    {
        return $this->hasMany(DatoAnual::class, 'id_indicador');
    }

    /**
     * Obtiene el usuario responsable del indicador.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Obtiene la institución responsable del indicador.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
    }

    /**
     * Obtiene solo los datos anuales validados.
     *
     * @return EloquentCollection Datos anuales validados.
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
     * Calcula el avance y estado de semaforización del indicador.
     *
     * @param  bool  $soloValidados Indica si deben usarse solo datos validados.
     * @return array{anio_ultimo_dato: int|null, ultimo_dato: float|string|null, avance: float|null, semaforizacion: string} Resultado del cálculo.
     */
    public function calcularSemaforizacion(bool $soloValidados = false): array
    {
        $ultimoDato = $this->calcularUltimoDato($soloValidados);

        $esLineaBase = $ultimoDato['es_linea_base'] ?? false;

        if ($esLineaBase) {
            $avance = null;
            $semaforizacion = "Solo línea base";
        } else {
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
     * Busca el dato anual más reciente o utiliza el valor de línea base.
     *
     * @param  bool  $soloValidados Indica si deben usarse solo datos validados.
     * @return array{valor: float|string|null, anio: int|null, es_linea_base: bool} Dato seleccionado y su origen.
     */
    private function calcularUltimoDato($soloValidados = false)
    {
        $ultimoDatoAnual = null;

        $fuenteDatos = $soloValidados ? $this->getDatosAnualesValidadosAttribute() : $this->datosAnuales;

        if ($fuenteDatos instanceof EloquentCollection) {
            $ultimoDatoAnual = $fuenteDatos
                ->filter(function ($da) {
                    return isset($da->valor_dato) && !is_null($da->valor_dato) && trim((string)$da->valor_dato) !== '';
                })
                ->sortByDesc('anio')
                ->first();
        } else {
            // Consulta directamente la relación si la colección no fue cargada.
            $query = $this->datosAnuales();
            if ($soloValidados) {
                $query->where('validado', true);
            }
            $ultimoDatoAnual = $query->whereNotNull('valor_dato')
                ->orderBy('anio', 'desc')
                ->first();
        }

        if ($ultimoDatoAnual) {
            $esIgualALineaBase = (!is_null($this->linea_base) && $ultimoDatoAnual->anio <= $this->linea_base);

            return [
                'valor' => $ultimoDatoAnual->valor_dato,
                'anio' => $ultimoDatoAnual->anio,
                'es_linea_base' => $esIgualALineaBase,
            ];
        }

        // Usa la línea base cuando no existe un dato anual disponible.
        if (!is_null($this->dato_linea_base) && trim((string)$this->dato_linea_base) !== '') {
            return [
                'valor' => $this->dato_linea_base,
                'anio' => $this->linea_base,
                'es_linea_base' => true,
            ];
        }

        return ['valor' => null, 'anio' => null, 'es_linea_base' => false];
    }

    /**
     * Calcula el porcentaje de avance hacia la meta según la tendencia.
     *
     * @param  array{valor: float|string|null, anio: int|null, es_linea_base?: bool}  $ultimoDato Dato usado para el cálculo.
     * @return float|null Porcentaje de avance o null si no puede calcularse.
     */
    private function calcularAvance($ultimoDato)
    {
        if ($ultimoDato['valor'] === null) return null;

        $metaLimpia = $this->meta_2024 !== null ? str_replace(',', '', (string)$this->meta_2024) : null;
        if (!is_numeric($metaLimpia) || $metaLimpia == 0) return null;
        $meta = (float)$metaLimpia;

        $valorLimpio = str_replace(',', '', (string)$ultimoDato['valor']);
        if (!is_numeric($valorLimpio)) return null;
        $valor = (float)$valorLimpio;

        $tendencia = strtolower(trim((string)$this->tendencia));

        if ($tendencia === "mayor es mejor") {
            return ($valor / $meta) * 100;
        } elseif ($tendencia === "menor es mejor") {
            if ($valor == 0.0) return null;
            return  (($meta / $valor) * 100);
        } elseif ($tendencia === "constante") {
            return ($valor / $meta) * 100;
        }

        return null;
    }

    /**
     * Determina el estado del semáforo según el porcentaje de avance.
     *
     * @param  float|null  $avance Porcentaje de avance.
     * @return string Estado de semaforización.
     */
    private function determinarSemaforizacion($avance)
    {
        if ($avance === null) return "No clasificado";
        if ($avance >= 110) return "Excedido";
        if ($avance >= 91) return "Aceptable";
        if ($avance >= 71) return "Moderado";
        return "Insuficiente";
    }

    /**
     * Obtiene el valor del dato más reciente disponible.
     *
     * @return float|string|null Valor más reciente o null si no existe.
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
     * Obtiene el año del dato más reciente disponible.
     *
     * @return int|null Año del dato más reciente.
     */
    public function getAnioRecienteAttribute()
    {
        // 1. Reutilizamos la misma lógica
        $info = $this->calcularUltimoDato();

        // 2. Devolvemos el año
        return $info['anio'] ?? null;
    }

    /**
     * Obtiene el valor del dato más reciente validado.
     *
     * @return float|string|null Valor validado más reciente o null si no existe.
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
     * Obtiene el año del dato validado más reciente.
     *
     * @return int|null Año del dato validado más reciente.
     */
    public function getAnioRecienteValidadoAttribute()
    {
        $info = $this->calcularUltimoDato(true);
        return $info['anio'] ?? null;
    }

    /**
     * Obtiene el último valor usado por la semaforización.
     *
     * @return float|string|null Último valor disponible.
     */
    public function getUltimoDatoAttribute()
    {
        return $this->calcularSemaforizacion()['ultimo_dato'];
    }

    /**
     * Obtiene el año del último dato usado por la semaforización.
     *
     * @return int|null Año del último dato disponible.
     */
    public function getAnioUltimoDatoAttribute()
    {
        return $this->calcularSemaforizacion()['anio_ultimo_dato'];
    }

    /**
     * Obtiene el porcentaje de avance general del indicador.
     *
     * @return float|null Porcentaje de avance o null si no puede calcularse.
     */
    public function getAvanceAttribute()
    {
        return $this->calcularSemaforizacion()['avance'];
    }

    /**
     * Obtiene el estado general de semaforización del indicador.
     *
     * @return string Estado de semaforización.
     */
    public function getSemaforizacionAttribute()
    {
        return $this->calcularSemaforizacion()['semaforizacion'];
    }

    /**
     * Obtiene el estado de semaforización usando solo datos validados.
     *
     * @return string Estado de semaforización validada.
     */
    public function getSemaforizacionValidadaAttribute()
    {
        return $this->calcularSemaforizacion(true)['semaforizacion'];
    }

    /**
     * Obtiene y formatea el valor de un año específico para las vistas.
     *
     * @param  int  $year Año que se consultará.
     * @param  string  $default Valor devuelto si no existe un dato.
     * @param  bool  $soloValidados Indica si deben usarse solo datos validados.
     * @return string Valor formateado o el valor por defecto.
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
     * Obtiene la fecha de actualización más reciente para la vista.
     *
     * @param  string  $default Valor devuelto si no existe una fecha válida.
     * @param  bool  $soloValidados Indica si deben usarse solo datos validados.
     * @return string Fecha en formato d-m-Y o valor por defecto.
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
     * @param  string  $default Mensaje devuelto si no hay resultados.
     * @param  bool  $soloValidados Indica si deben usarse solo datos validados.
     * @return string Resultados más recientes o el mensaje por defecto.
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Modelo MunicipioConvenio.
 *
 * Representa la información de un municipio que ha firmado un convenio.
 * Almacena el objetivo, el convenio en PDF, un ícono y un banner.
 */
class MunicipioConvenio extends Model
{
    use HasFactory;


    /**
     * La tabla asociada con el modelo.
     * @var string
     */
    protected $table = 'municipios_convenio';

    /**
     * Los atributos que son asignables en masa.
     * @var array<int, string>
     */
    protected $fillable = [
        'id_municipio',
        'icono',
        'objetivo',
        'convenio',
        'banner',
        'slug',
    ];
    // Para que las rutas busquen por slug en lugar de ID
    public function getRouteKeyName()
    {
        return 'slug';
    }
    /**
     * Define la relación inversa con el catálogo de municipios.
     * Un registro de convenio pertenece a un único Municipio.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function municipio()
    {
        return $this->belongsTo(CatMunicipio::class, 'id_municipio');
    }

    // Automatización
    protected static function boot()
    {
        parent::boot();

        // Al crear o actualizar un convenio...
        static::saving(function ($municipioConvenio) {
            // Si el slug está vacío...
            if (empty($municipioConvenio->slug)) {
                // Buscamos el nombre a través de la relación. 
                // OJO: Esto asume que 'id_municipio' ya está seteado.
                if ($municipioConvenio->municipio) {
                    $municipioConvenio->slug = Str::slug($municipioConvenio->municipio->nombre);
                }
                // Caso especial: Si estamos creando y apenas pasamos el ID pero la relación no está cargada
                elseif ($municipioConvenio->id_municipio) {
                    $nombre = \App\Models\CatMunicipio::find($municipioConvenio->id_municipio)->nombre ?? 'sin-nombre';
                    $municipioConvenio->slug = Str::slug($nombre);
                }
            }
        });
    }
}

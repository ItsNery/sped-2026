<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatMunicipio extends Model
{
    use HasFactory;
    protected $table = 'cat_municipios';
    protected $fillable = ['nombre', 'region_id'];
    /**
     * Define la relación con CatRegion.
     * Un municipio pertenece a una región.
     */
    public function region()
    {
        return $this->belongsTo(CatRegion::class, 'region_id');
    }
    public function municipiosConvenios()
    {
        return $this->hasMany(MunicipioConvenio::class, 'id_municipio');
    }
    public function indicadores() // Puedes llamar a esta relación como prefieras
    {
        // Asume que la clave foránea en IndicadorMunicipal es 'id_municipio'
        // y la clave local en CatMunicipios es 'id_municipio'
        return $this->hasMany(IndicadorMunicipal::class, 'id_municipio', 'id');
    }
}

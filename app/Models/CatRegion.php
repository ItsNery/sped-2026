<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatRegion extends Model
{
    use HasFactory;
    protected $table = 'cat_regiones';
    
    protected $fillable = ['nombre_region', 'descripcion'];

    public function municipios()
{
    return $this->hasMany(CatMunicipio::class, 'region_id');
}
}

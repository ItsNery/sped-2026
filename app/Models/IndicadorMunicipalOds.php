<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndicadorMunicipalOds extends Model
{
    use HasFactory;
    protected $table = 'indicadores_municipales_ods';
    protected $fillable = [
        'id_indicador',
        'id_ods',
    ];
}

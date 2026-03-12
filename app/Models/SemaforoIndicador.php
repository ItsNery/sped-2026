<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemaforoIndicador extends Model
{
    use HasFactory;
    protected $table = 'semfaforo_indicador';
    protected $fillable = [
        'color'
    ];
    // Relación uno a muchos con Indicador
    public function indicadores()
    {
        return $this->hasMany(Indicador::class, 'id_semaforo');
    }
}

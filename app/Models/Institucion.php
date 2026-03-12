<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    use HasFactory;
    protected $table = 'instituciones';
    protected $fillable = [
        'nombre',
        'titular',
    ];
    // Relación uno a muchos con Indicador
    public function indicadores()
    {
        return $this->hasMany(Indicador::class, 'id_institucion');
    }

    // Relación uno a muchos con User
    public function usuario()
    {
        return $this->hasMany(User::class, 'id_institucion');
    }
    
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'institucion_user')->withTimestamps();
    }
}

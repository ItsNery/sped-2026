<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatNivel extends Model
{
    use HasFactory;
    protected $table = 'cat_nivel';
    protected $fillable = ['nombre', 'tipo_id'];
    public function tipo()
    {
        return $this->belongsTo(CatTipo::class);
    }

    // Relación uno a muchos con CatDimension
    public function dimensiones()
    {
        return $this->hasMany(CatDimension::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatTipo extends Model
{
    use HasFactory;
    protected $table = 'cat_tipo';
    protected $fillable = ['nombre'];
    public function niveles()
    {
        return $this->hasMany(CatNivel::class);
    }
}

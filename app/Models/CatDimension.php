<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatDimension extends Model
{
    use HasFactory;
    protected $table = 'cat_dimension';
    protected $fillable = ['nombre', 'nivel_id'];
    public function nivel()
    {
        return $this->belongsTo(CatNivel::class);
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodoCaptura extends Model
{
    use HasFactory;

    protected $table = 'periodos_captura';

    protected $fillable = [
        'plan_id',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'descripcion',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'activo' => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(CatPlanEstatalDesarrollo::class, 'plan_id');
    }

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function isVigente(): bool
    {
        if (!$this->activo) {
            return false;
        }

        return Carbon::now()->between($this->fecha_inicio, $this->fecha_fin);
    }

    public function haVencido(): bool
    {
        return Carbon::now()->gt($this->fecha_fin);
    }

    public function haIniciado(): bool
    {
        return Carbon::now()->gte($this->fecha_inicio);
    }
}

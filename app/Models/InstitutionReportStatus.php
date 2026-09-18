<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionReportStatus extends Model
{
    /** @use HasFactory<\Database\Factories\InstitutionReportStatusFactory> */
    use HasFactory;

    protected $fillable = [
        'institucion_id',
        'plan_id',
        'finalizado_at',
        'finalizado_por_user_id',
        'reporte_generado_at',
        'reporte_generado_por_user_id',
    ];

    protected $casts = [
        'finalizado_at' => 'datetime',
        'reporte_generado_at' => 'datetime',
    ];

    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'institucion_id');
    }

    public function plan()
    {
        return $this->belongsTo(CatPlanEstatalDesarrollo::class, 'plan_id');
    }

    public function finalizadoPor()
    {
        return $this->belongsTo(User::class, 'finalizado_por_user_id');
    }

    public function reporteGeneradoPor()
    {
        return $this->belongsTo(User::class, 'reporte_generado_por_user_id');
    }
}

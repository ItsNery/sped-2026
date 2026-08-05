<?php

namespace App\Services;

use App\Models\CatPlanEstatalDesarrollo;
use RuntimeException;

class ActivePlanResolver
{
    public function id(): int
    {
        $planId = (int) config('sped.active_plan_id', 3);

        if ($planId < 1) {
            throw new RuntimeException('SPED_ACTIVE_PLAN_ID debe ser un entero positivo.');
        }

        return $planId;
    }

    public function get(): CatPlanEstatalDesarrollo
    {
        $plan = CatPlanEstatalDesarrollo::find($this->id());

        if (!$plan) {
            throw new RuntimeException('El PED activo configurado no existe en la base de datos.');
        }

        return $plan;
    }
}

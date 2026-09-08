<?php

namespace App\Services;

use App\Models\Institucion;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class InstitutionAccessService
{
    public function directInstitutionIds(User $user): Collection
    {
        if ($user->hasRole('Enlace')) {
            return $user->instituciones()->pluck('instituciones.id')->map(fn ($id) => (int) $id)->unique()->values();
        }

        return $user->id_institucion
            ? collect([(int) $user->id_institucion])
            : collect();
    }

    public function visibleInstitutionIds(User $user): Collection
    {
        if ($user->isAdministrator()) {
            return Institucion::query()->pluck('id')->map(fn ($id) => (int) $id);
        }

        $directIds = $this->directInstitutionIds($user);
        if ($directIds->isEmpty()) {
            return $directIds;
        }

        $sectorizedIds = Institucion::query()
            ->whereIn('institucion_sectorizadora_id', $directIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        return $directIds->merge($sectorizedIds)->unique()->values();
    }

    public function canViewInstitution(User $user, ?int $institutionId): bool
    {
        return $institutionId !== null
            && $this->visibleInstitutionIds($user)->contains($institutionId);
    }

    public function canManageInstitutionDirectly(User $user, ?int $institutionId): bool
    {
        return $user->isAdministrator()
            || ($institutionId !== null && $this->directInstitutionIds($user)->contains($institutionId));
    }

    public function scopeIndicators(Builder $query, User $user): Builder
    {
        if ($user->isAdministrator()) {
            return $query;
        }

        $institutionIds = $this->visibleInstitutionIds($user);

        return $institutionIds->isEmpty()
            ? $query->whereRaw('1 = 0')
            : $query->whereIn('id_institucion', $institutionIds);
    }
}

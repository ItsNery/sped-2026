<?php

namespace App\Policies;

use App\Models\Indicador;
use App\Models\User;
use App\Services\InstitutionAccessService;
use App\Services\PeriodoCapturaService;

class IndicadorPolicy
{
    public function __construct(
        private InstitutionAccessService $institutionAccess,
        private PeriodoCapturaService $periodoCapturaService
    ) {
    }

    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdministrator() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('ver-indicador');
    }

    public function view(User $user, Indicador $indicador): bool
    {
        return $user->can('ver-indicador')
            && $this->institutionAccess->canViewInstitution($user, $indicador->id_institucion);
    }

    public function update(User $user, Indicador $indicador): bool
    {
        return $user->can('editar-indicador')
            && $this->institutionAccess->canManageInstitutionDirectly($user, $indicador->id_institucion);
    }

    public function delete(User $user, Indicador $indicador): bool
    {
        return $user->can('borrar-indicador')
            && $this->institutionAccess->canManageInstitutionDirectly($user, $indicador->id_institucion);
    }

    public function addAnnualData(User $user, Indicador $indicador): bool
    {
        return $user->can('agregar-dato-anual')
            && $this->institutionAccess->canViewInstitution($user, $indicador->id_institucion)
            && $this->periodoCapturaService->verificarEstadoCaptura($user, $indicador->id_institucion)['puede_capturar'];
    }

    public function updateAnnualData(User $user, Indicador $indicador): bool
    {
        return $user->can('editar-indicador-anual')
            && $this->institutionAccess->canViewInstitution($user, $indicador->id_institucion)
            && $this->periodoCapturaService->verificarEstadoCaptura($user, $indicador->id_institucion)['puede_capturar'];
    }

    public function deleteAnnualData(User $user, Indicador $indicador): bool
    {
        return $user->hasRole('Enlace')
            && $this->institutionAccess->canViewInstitution($user, $indicador->id_institucion);
    }

    public function validate(User $user, Indicador $indicador): bool
    {
        return $user->can('validar-indicador')
            && $this->institutionAccess->canViewInstitution($user, $indicador->id_institucion);
    }
}

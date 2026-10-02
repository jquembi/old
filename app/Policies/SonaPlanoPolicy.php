<?php

namespace App\Policies;

use App\Models\SonaPlano;
use App\Models\User;

class SonaPlanoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->activo && $user->temPermissao('sona.ver');
    }

    public function view(User $user, SonaPlano $plano): bool
    {
        return $user->activo
            && $user->temPermissao('sona.ver')
            && $this->pertenceAoAmbito($user, $plano);
    }

    public function create(User $user): bool
    {
        return $user->activo && $user->temPermissao('sona.criar');
    }

    public function update(User $user, SonaPlano $plano): bool
    {
        return $user->activo
            && $user->temPermissao('sona.editar')
            && $this->pertenceAoAmbito($user, $plano);
    }

    public function delete(User $user, SonaPlano $plano): bool
    {
        return $user->activo
            && $user->temPermissao('sona.eliminar')
            && $this->pertenceAoAmbito($user, $plano);
    }

    private function pertenceAoAmbito(User $user, SonaPlano $plano): bool
    {
        if ($user->isNacional() || $user->isInotu()) {
            return true;
        }

        if ($user->isProvincial()) {
            return $plano->municipio?->provincia_id === $user->provincia_id;
        }

        return $user->municipio_id === $plano->municipio_id;
    }
}

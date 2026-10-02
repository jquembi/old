<?php

namespace App\Policies;

use App\Models\TerrenoOcioso;
use App\Models\User;

class TerrenoOciosoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->activo && $user->temPermissao('operacoes.ver');
    }

    public function view(User $user, TerrenoOcioso $terreno): bool
    {
        return $user->activo
            && $user->temPermissao('operacoes.ver')
            && $this->pertenceAoAmbito($user, $terreno);
    }

    public function create(User $user): bool
    {
        return $user->activo && $user->temPermissao('operacoes.criar');
    }

    public function update(User $user, TerrenoOcioso $terreno): bool
    {
        return $user->activo
            && $user->temPermissao('operacoes.editar')
            && $this->pertenceAoAmbito($user, $terreno);
    }

    public function delete(User $user, TerrenoOcioso $terreno): bool
    {
        return $user->activo
            && $user->temPermissao('operacoes.eliminar')
            && $this->pertenceAoAmbito($user, $terreno);
    }

    private function pertenceAoAmbito(User $user, TerrenoOcioso $terreno): bool
    {
        if ($user->isNacional() || $user->isInotu()) {
            return true;
        }

        if ($user->isProvincial()) {
            return $terreno->municipio?->provincia_id === $user->provincia_id;
        }

        return $user->municipio_id === $terreno->municipio_id;
    }
}

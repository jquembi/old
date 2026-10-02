<?php

namespace App\Policies;

use App\Models\Croqui;
use App\Models\User;

class CroquiPolicy
{
    /**
     * Utilizador pode ver croquis do seu município (ou todos se INOTU/Provincial).
     */
    public function viewAny(User $user): bool
    {
        return $user->activo && $user->temPermissao('operacoes.ver');
    }

    public function view(User $user, Croqui $croqui): bool
    {
        return $user->activo
            && $user->temPermissao('operacoes.ver')
            && $this->pertenceAoAmbito($user, $croqui);
    }

    public function create(User $user): bool
    {
        return $user->activo && $user->temPermissao('operacoes.criar');
    }

    public function update(User $user, Croqui $croqui): bool
    {
        return $user->activo
            && $user->temPermissao('operacoes.editar')
            && $this->pertenceAoAmbito($user, $croqui);
    }

    public function delete(User $user, Croqui $croqui): bool
    {
        return $user->activo
            && $user->temPermissao('operacoes.eliminar')
            && $this->pertenceAoAmbito($user, $croqui);
    }

    private function pertenceAoAmbito(User $user, Croqui $croqui): bool
    {
        if ($user->isNacional() || $user->isInotu()) {
            return true;
        }

        $municipioId = $croqui->operacao?->municipio_id;

        if ($user->isProvincial()) {
            return $croqui->operacao?->municipio?->provincia_id === $user->provincia_id;
        }

        return $user->municipio_id === $municipioId;
    }
}

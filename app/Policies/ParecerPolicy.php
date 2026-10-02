<?php

namespace App\Policies;

use App\Models\Parecer;
use App\Models\User;

class ParecerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->activo && $user->temPermissao('pareceres.ver');
    }

    public function view(User $user, Parecer $parecer): bool
    {
        return $user->activo
            && $user->temPermissao('pareceres.ver')
            && $this->pertenceAoAmbito($user, $parecer);
    }

    public function create(User $user): bool
    {
        return $user->activo && $user->temPermissao('pareceres.criar');
    }

    public function update(User $user, Parecer $parecer): bool
    {
        return $user->activo
            && $user->temPermissao('pareceres.editar')
            && $this->pertenceAoAmbito($user, $parecer);
    }

    public function delete(User $user, Parecer $parecer): bool
    {
        return $user->activo
            && $user->temPermissao('pareceres.eliminar')
            && $this->pertenceAoAmbito($user, $parecer);
    }

    private function pertenceAoAmbito(User $user, Parecer $parecer): bool
    {
        if ($user->isNacional() || $user->isInotu()) {
            return true;
        }

        if ($user->isProvincial()) {
            return $parecer->municipio?->provincia_id === $user->provincia_id;
        }

        return $user->municipio_id === $parecer->municipio_id;
    }
}

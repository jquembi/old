<?php

namespace App\Policies;

use App\Models\InstrumentoOrdenamento;
use App\Models\User;

class InstrumentoOrdenamentoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->activo && $user->temPermissao('instrumentos.ver');
    }

    public function view(User $user, InstrumentoOrdenamento $instrumento): bool
    {
        return $user->activo
            && $user->temPermissao('instrumentos.ver')
            && $this->pertenceAoAmbito($user, $instrumento);
    }

    public function create(User $user): bool
    {
        return $user->activo && $user->temPermissao('instrumentos.criar');
    }

    public function update(User $user, InstrumentoOrdenamento $instrumento): bool
    {
        return $user->activo
            && $user->temPermissao('instrumentos.editar')
            && $this->pertenceAoAmbito($user, $instrumento);
    }

    public function delete(User $user, InstrumentoOrdenamento $instrumento): bool
    {
        return $user->activo
            && $user->temPermissao('instrumentos.eliminar')
            && $this->pertenceAoAmbito($user, $instrumento);
    }

    public function approve(User $user): bool
    {
        return $user->activo && $user->temPermissao('instrumentos.aprovar');
    }

    private function pertenceAoAmbito(User $user, InstrumentoOrdenamento $instrumento): bool
    {
        if ($user->isNacional() || $user->isInotu()) {
            return true;
        }

        if ($user->isProvincial()) {
            return $instrumento->municipio?->provincia_id === $user->provincia_id;
        }

        return $user->municipio_id === $instrumento->municipio_id;
    }
}

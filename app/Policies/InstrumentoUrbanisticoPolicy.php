<?php

namespace App\Policies;

use App\Models\InstrumentoUrbanistico;
use App\Models\User;

class InstrumentoUrbanisticoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->activo && $user->temPermissao('instrumentos.ver');
    }

    public function view(User $user, InstrumentoUrbanistico $instrumento): bool
    {
        return $user->activo
            && $user->temPermissao('instrumentos.ver')
            && $this->pertenceAoAmbito($user, $instrumento);
    }

    public function create(User $user): bool
    {
        return $user->activo && $user->temPermissao('instrumentos.criar');
    }

    public function update(User $user, InstrumentoUrbanistico $instrumento): bool
    {
        return $user->activo
            && $user->temPermissao('instrumentos.editar')
            && $this->pertenceAoAmbito($user, $instrumento);
    }

    public function delete(User $user, InstrumentoUrbanistico $instrumento): bool
    {
        return $user->activo
            && $user->temPermissao('instrumentos.eliminar')
            && $this->pertenceAoAmbito($user, $instrumento);
    }

    private function pertenceAoAmbito(User $user, InstrumentoUrbanistico $instrumento): bool
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

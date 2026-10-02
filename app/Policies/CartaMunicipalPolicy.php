<?php

namespace App\Policies;

use App\Models\CartaMunicipal;
use App\Models\User;

class CartaMunicipalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->activo && $user->temPermissao('cartas_municipais.ver');
    }

    public function view(User $user, CartaMunicipal $carta): bool
    {
        return $user->activo
            && $user->temPermissao('cartas_municipais.ver')
            && $this->pertenceAoAmbito($user, $carta);
    }

    public function create(User $user): bool
    {
        return $user->activo && $user->temPermissao('cartas_municipais.criar');
    }

    public function update(User $user, CartaMunicipal $carta): bool
    {
        return $user->activo
            && $user->temPermissao('cartas_municipais.editar')
            && $this->pertenceAoAmbito($user, $carta);
    }

    public function delete(User $user, CartaMunicipal $carta): bool
    {
        return $user->activo
            && $user->temPermissao('cartas_municipais.eliminar')
            && $this->pertenceAoAmbito($user, $carta);
    }

    private function pertenceAoAmbito(User $user, CartaMunicipal $carta): bool
    {
        if ($user->isNacional() || $user->isInotu()) {
            return true;
        }

        if ($user->isProvincial()) {
            return $carta->municipio?->provincia_id === $user->provincia_id;
        }

        return $user->municipio_id === $carta->municipio_id;
    }
}

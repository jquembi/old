<?php

namespace App\Policies;

use App\Models\Municipio;
use App\Models\User;

class MunicipioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->activo;
    }

    public function view(User $user, Municipio $municipio): bool
    {
        return $user->activo && $user->podeVerMunicipio($municipio->id);
    }

    public function create(User $user): bool
    {
        return $user->activo && $user->isInotu();
    }

    public function update(User $user, Municipio $municipio): bool
    {
        return $user->activo && $user->isInotu();
    }

    public function delete(User $user, Municipio $municipio): bool
    {
        return $user->activo && $user->isInotu();
    }
}

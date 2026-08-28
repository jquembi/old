<?php

namespace App\Policies;

use App\Models\ProjectoSona;
use App\Models\User;

class ProjectoSonaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->activo && $user->temPermissao('sona.ver');
    }

    public function view(User $user, ProjectoSona $projecto): bool
    {
        return $user->activo
            && $user->temPermissao('sona.ver')
            && $user->podeVerMunicipio($projecto->municipio_id);
    }

    public function create(User $user): bool
    {
        return $user->activo && $user->temPermissao('sona.criar');
    }

    public function update(User $user, ProjectoSona $projecto): bool
    {
        return $user->activo
            && $user->temPermissao('sona.editar')
            && $user->podeEditarMunicipio($projecto->municipio_id);
    }

    public function delete(User $user, ProjectoSona $projecto): bool
    {
        return $user->activo
            && $user->temPermissao('sona.eliminar')
            && $user->podeEditarMunicipio($projecto->municipio_id);
    }
}

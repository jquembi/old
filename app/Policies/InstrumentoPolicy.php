<?php

namespace App\Policies;

use App\Models\Instrumento;
use App\Models\User;

class InstrumentoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->activo && $user->temPermissao('instrumentos.ver');
    }

    public function view(User $user, Instrumento $instrumento): bool
    {
        return $user->activo
            && $user->temPermissao('instrumentos.ver')
            && $user->podeVerMunicipio($instrumento->municipio_id);
    }

    public function create(User $user): bool
    {
        return $user->activo && $user->temPermissao('instrumentos.criar');
    }

    public function update(User $user, Instrumento $instrumento): bool
    {
        return $user->activo
            && $user->temPermissao('instrumentos.editar')
            && $user->podeEditarMunicipio($instrumento->municipio_id);
    }

    public function delete(User $user, Instrumento $instrumento): bool
    {
        return $user->activo
            && $user->temPermissao('instrumentos.eliminar')
            && $user->podeEditarMunicipio($instrumento->municipio_id);
    }
}

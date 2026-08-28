<?php

namespace App\Policies;

use App\Models\Loteamento;
use App\Models\User;

class LoteamentoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->activo && $user->temPermissao('loteamentos.ver');
    }

    public function view(User $user, Loteamento $loteamento): bool
    {
        return $user->activo
            && $user->temPermissao('loteamentos.ver')
            && $user->podeVerMunicipio($loteamento->municipio_id);
    }

    public function create(User $user): bool
    {
        return $user->activo && $user->temPermissao('loteamentos.criar');
    }

    public function update(User $user, Loteamento $loteamento): bool
    {
        return $user->activo
            && $user->temPermissao('loteamentos.editar')
            && $user->podeEditarMunicipio($loteamento->municipio_id);
    }

    public function delete(User $user, Loteamento $loteamento): bool
    {
        return $user->activo
            && $user->temPermissao('loteamentos.eliminar')
            && $user->podeEditarMunicipio($loteamento->municipio_id);
    }
}

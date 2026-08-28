<?php

namespace App\Policies;

use App\Models\OperacaoUrbanistica;
use App\Models\User;

class OperacaoUrbanisticaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->activo && $user->temPermissao('operacoes.ver');
    }

    public function view(User $user, OperacaoUrbanistica $operacao): bool
    {
        return $user->activo
            && $user->temPermissao('operacoes.ver')
            && $user->podeVerMunicipio($operacao->municipio_id);
    }

    public function create(User $user): bool
    {
        return $user->activo && $user->temPermissao('operacoes.criar');
    }

    public function update(User $user, OperacaoUrbanistica $operacao): bool
    {
        return $user->activo
            && $user->temPermissao('operacoes.editar')
            && $user->podeEditarMunicipio($operacao->municipio_id);
    }

    public function delete(User $user, OperacaoUrbanistica $operacao): bool
    {
        return $user->activo
            && $user->temPermissao('operacoes.eliminar')
            && $user->podeEditarMunicipio($operacao->municipio_id);
    }
}

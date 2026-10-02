<?php

namespace App\Policies;

use App\Models\Operacao;
use App\Models\User;

class OperacaoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->activo && $user->temPermissao('operacoes.ver');
    }

    public function view(User $user, Operacao $operacao): bool
    {
        return $user->activo
            && $user->temPermissao('operacoes.ver')
            && $this->pertenceAoAmbito($user, $operacao);
    }

    public function create(User $user): bool
    {
        return $user->activo && $user->temPermissao('operacoes.criar');
    }

    public function update(User $user, Operacao $operacao): bool
    {
        return $user->activo
            && $user->temPermissao('operacoes.editar')
            && $this->pertenceAoAmbito($user, $operacao);
    }

    public function delete(User $user, Operacao $operacao): bool
    {
        return $user->activo
            && $user->temPermissao('operacoes.eliminar')
            && $this->pertenceAoAmbito($user, $operacao);
    }

    private function pertenceAoAmbito(User $user, Operacao $operacao): bool
    {
        // Nacional e INOTU acedem a tudo
        if ($user->isNacional() || $user->isInotu()) {
            return true;
        }

        // Provincial: apenas operações da sua província
        if ($user->isProvincial()) {
            return $operacao->municipio?->provincia_id === $user->provincia_id;
        }

        // Municipal/Técnico: apenas o seu município
        return $user->municipio_id === $operacao->municipio_id;
    }
}


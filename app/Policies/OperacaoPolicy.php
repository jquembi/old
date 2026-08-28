<?php

namespace App\Policies;

use App\Models\Operacao;
use App\Models\User;

class OperacaoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('operacao_view');
    }

    public function view(User $user, Operacao $operacao): bool
    {
        if ($user->canAccessAllMunicipios()) {
            return true;
        }
        return $user->municipio_id === $operacao->municipio_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('operacao_create');
    }

    public function update(User $user, Operacao $operacao): bool
    {
        if (!$user->hasPermission('operacao_edit')) {
            return false;
        }
        if ($user->canAccessAllMunicipios()) {
            return true;
        }
        return $user->municipio_id === $operacao->municipio_id;
    }

    public function delete(User $user, Operacao $operacao): bool
    {
        if (!$user->hasPermission('operacao_delete')) {
            return false;
        }
        if ($user->isINOTU()) {
            return true;
        }
        return $user->municipio_id === $operacao->municipio_id;
    }
}


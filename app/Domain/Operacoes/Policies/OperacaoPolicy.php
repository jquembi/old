<?php

namespace App\Domain\Operacoes\Policies;

use App\Models\User;
use App\Models\Operacao;
use App\Shared\Enums\RoleEnum;

/**
 * Policy para autorização de operações em Operacoes.
 */
class OperacaoPolicy
{
    /**
     * Determina se o usuário pode visualizar operações.
     */
    public function view(User $user, Operacao $operacao): bool
    {
        // INOTU vê tudo
        if ($user->hasRole(RoleEnum::INOTU->value)) {
            return true;
        }

        // Governo Provincial vê tudo
        if ($user->hasRole(RoleEnum::GOVERNO_PROVINCIAL->value)) {
            return true;
        }

        // Técnico municipal vê operações do seu município
        if ($user->hasRole(RoleEnum::TECNICO_MUNICIPAL->value)) {
            return $user->municipio_id === $operacao->municipio_id;
        }

        return false;
    }

    /**
     * Determina se o usuário pode listar operações.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            RoleEnum::INOTU->value,
            RoleEnum::GOVERNO_PROVINCIAL->value,
            RoleEnum::TECNICO_MUNICIPAL->value,
        ]);
    }

    /**
     * Determina se o usuário pode criar uma nova operação.
     */
    public function create(User $user): bool
    {
        // INOTU e Governo Provincial podem criar
        return $user->hasAnyRole([
            RoleEnum::INOTU->value,
            RoleEnum::GOVERNO_PROVINCIAL->value,
        ]);
    }

    /**
     * Determina se o usuário pode atualizar a operação.
     */
    public function update(User $user, Operacao $operacao): bool
    {
        // INOTU pode atualizar tudo
        if ($user->hasRole(RoleEnum::INOTU->value)) {
            return true;
        }

        // Governo Provincial pode atualizar
        if ($user->hasRole(RoleEnum::GOVERNO_PROVINCIAL->value)) {
            return true;
        }

        return false;
    }

    /**
     * Determina se o usuário pode deletar a operação.
     */
    public function delete(User $user, Operacao $operacao): bool
    {
        // Apenas INOTU pode deletar
        return $user->hasRole(RoleEnum::INOTU->value);
    }
}

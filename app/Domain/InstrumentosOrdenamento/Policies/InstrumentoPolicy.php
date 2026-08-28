<?php

namespace App\Domain\InstrumentosOrdenamento\Policies;

use App\Models\User;
use App\Models\InstrumentoOrdenamento;
use App\Shared\Enums\RoleEnum;

/**
 * Policy para autorização de operações em Instrumentos de Ordenamento.
 */
class InstrumentoPolicy
{
    /**
     * Determina se o usuário pode visualizar instrumentos.
     */
    public function view(User $user, InstrumentoOrdenamento $instrumento): bool
    {
        // INOTU vê tudo
        if ($user->hasRole(RoleEnum::INOTU->value)) {
            return true;
        }

        // Governo Provincial vê tudo
        if ($user->hasRole(RoleEnum::GOVERNO_PROVINCIAL->value)) {
            return true;
        }

        // Técnico municipal vê instrumentos do seu município
        if ($user->hasRole(RoleEnum::TECNICO_MUNICIPAL->value)) {
            return $user->municipio_id === $instrumento->municipio_id;
        }

        return false;
    }

    /**
     * Determina se o usuário pode listar instrumentos.
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
     * Determina se o usuário pode criar um novo instrumento.
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
     * Determina se o usuário pode atualizar o instrumento.
     */
    public function update(User $user, InstrumentoOrdenamento $instrumento): bool
    {
        // INOTU pode atualizar tudo
        if ($user->hasRole(RoleEnum::INOTU->value)) {
            return true;
        }

        // Governo Provincial pode atualizar
        if ($user->hasRole(RoleEnum::GOVERNO_PROVINCIAL->value)) {
            return true;
        }

        // Técnico municipal pode atualizar seu próprio instrumento se estiver em rascunho
        if ($user->hasRole(RoleEnum::TECNICO_MUNICIPAL->value)) {
            return $user->municipio_id === $instrumento->municipio_id 
                && $instrumento->status === 'draft';
        }

        return false;
    }

    /**
     * Determina se o usuário pode deletar o instrumento.
     */
    public function delete(User $user, InstrumentoOrdenamento $instrumento): bool
    {
        // Apenas INOTU pode deletar
        return $user->hasRole(RoleEnum::INOTU->value);
    }

    /**
     * Determina se o usuário pode aprovar o instrumento.
     */
    public function approve(User $user, InstrumentoOrdenamento $instrumento): bool
    {
        // Apenas INOTU e Governo Provincial podem aprovar
        return $user->hasAnyRole([
            RoleEnum::INOTU->value,
            RoleEnum::GOVERNO_PROVINCIAL->value,
        ]);
    }
}

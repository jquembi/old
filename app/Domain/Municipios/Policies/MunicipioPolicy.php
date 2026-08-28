<?php

namespace App\Domain\Municipios\Policies;

use App\Models\User;
use App\Models\Municipio;
use App\Shared\Enums\RoleEnum;

/**
 * Policy para autorização de operações em Municipios.
 * 
 * Define quem pode visualizar, criar, atualizar e deletar municípios.
 */
class MunicipioPolicy
{
    /**
     * Determina se o usuário pode visualizar municípios.
     */
    public function view(User $user, Municipio $municipio): bool
    {
        // INOTU vê tudo
        if ($user->hasRole(RoleEnum::INOTU->value)) {
            return true;
        }

        // Governo Provincial vê tudo
        if ($user->hasRole(RoleEnum::GOVERNO_PROVINCIAL->value)) {
            return true;
        }

        // Técnico municipal vê só seu município
        if ($user->hasRole(RoleEnum::TECNICO_MUNICIPAL->value)) {
            return $user->municipio_id === $municipio->id;
        }

        return false;
    }

    /**
     * Determina se o usuário pode listar municípios.
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
     * Determina se o usuário pode criar um novo município.
     */
    public function create(User $user): bool
    {
        // Apenas INOTU pode criar municípios
        return $user->hasRole(RoleEnum::INOTU->value);
    }

    /**
     * Determina se o usuário pode atualizar o município.
     */
    public function update(User $user, Municipio $municipio): bool
    {
        // INOTU pode atualizar tudo
        if ($user->hasRole(RoleEnum::INOTU->value)) {
            return true;
        }

        // Técnico municipal pode atualizar seu próprio município (dados básicos apenas)
        if ($user->hasRole(RoleEnum::TECNICO_MUNICIPAL->value)) {
            return $user->municipio_id === $municipio->id;
        }

        return false;
    }

    /**
     * Determina se o usuário pode deletar o município.
     */
    public function delete(User $user, Municipio $municipio): bool
    {
        // Apenas INOTU pode deletar
        return $user->hasRole(RoleEnum::INOTU->value);
    }

    /**
     * Determina se o usuário pode restaurar o município.
     */
    public function restore(User $user, Municipio $municipio): bool
    {
        return $user->hasRole(RoleEnum::INOTU->value);
    }

    /**
     * Determina se o usuário pode deletar permanentemente o município.
     */
    public function forceDelete(User $user, Municipio $municipio): bool
    {
        return $user->hasRole(RoleEnum::INOTU->value);
    }
}

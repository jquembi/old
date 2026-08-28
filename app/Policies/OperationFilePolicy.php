<?php

namespace App\Policies;

use App\Models\OperationFile;
use App\Models\User;

class OperationFilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->activo && $user->temPermissao('operacoes.ver');
    }

    public function view(User $user, OperationFile $file): bool
    {
        return $user->activo
            && $user->temPermissao('operacoes.ver')
            && $this->pertenceAoAmbito($user, $file);
    }

    public function create(User $user): bool
    {
        return $user->activo && $user->temPermissao('operacoes.criar');
    }

    public function delete(User $user, OperationFile $file): bool
    {
        if (! $user->activo) {
            return false;
        }

        // INOTU pode apagar tudo
        if ($user->isInotu()) {
            return true;
        }

        // O próprio autor pode apagar
        if ($file->enviado_por === $user->id) {
            return true;
        }

        // Provincial pode apagar ficheiros da sua província
        if ($user->isProvincial()) {
            return $file->operacao?->municipio?->provincia_id === $user->provincia_id;
        }

        // Admin municipal pode apagar ficheiros do seu município
        if ($user->isAdminMunicipal()) {
            return $user->municipio_id === $file->operacao?->municipio_id;
        }

        return false;
    }

    private function pertenceAoAmbito(User $user, OperationFile $file): bool
    {
        if ($user->isInotu()) {
            return true;
        }

        $municipioId = $file->operacao?->municipio_id;

        if ($user->isProvincial()) {
            return $file->operacao?->municipio?->provincia_id === $user->provincia_id;
        }

        return $user->municipio_id === $municipioId;
    }
}

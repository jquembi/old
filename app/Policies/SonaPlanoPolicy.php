<?php

namespace App\Policies;

use App\Models\SonaPlano;
use App\Models\User;

class SonaPlanoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('sona_view');
    }

    public function view(User $user, SonaPlano $plano): bool
    {
        if ($user->canAccessAllMunicipios()) {
            return true;
        }
        return $user->municipio_id === $plano->municipio_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('sona_create');
    }

    public function update(User $user, SonaPlano $plano): bool
    {
        if (!$user->hasPermission('sona_edit')) {
            return false;
        }
        if ($user->canAccessAllMunicipios()) {
            return true;
        }
        return $user->municipio_id === $plano->municipio_id;
    }

    public function delete(User $user, SonaPlano $plano): bool
    {
        if (!$user->hasPermission('sona_delete')) {
            return false;
        }
        return $user->isINOTU();
    }
}



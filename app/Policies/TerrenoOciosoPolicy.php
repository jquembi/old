<?php

namespace App\Policies;

use App\Models\TerrenoOcioso;
use App\Models\User;

class TerrenoOciosoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('terreno_view');
    }

    public function view(User $user, TerrenoOcioso $terreno): bool
    {
        if ($user->canAccessAllMunicipios()) {
            return true;
        }
        return $user->municipio_id === $terreno->municipio_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('terreno_create');
    }

    public function update(User $user, TerrenoOcioso $terreno): bool
    {
        if (!$user->hasPermission('terreno_edit')) {
            return false;
        }
        if ($user->canAccessAllMunicipios()) {
            return true;
        }
        return $user->municipio_id === $terreno->municipio_id;
    }

    public function delete(User $user, TerrenoOcioso $terreno): bool
    {
        if (!$user->hasPermission('terreno_delete')) {
            return false;
        }
        if ($user->isINOTU()) {
            return true;
        }
        return $user->municipio_id === $terreno->municipio_id;
    }
}



<?php

namespace App\Policies;

use App\Models\CartaMunicipal;
use App\Models\User;

class CartaMunicipalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('carta_view');
    }

    public function view(User $user, CartaMunicipal $carta): bool
    {
        if ($user->canAccessAllMunicipios()) {
            return true;
        }
        return $user->municipio_id === $carta->municipio_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('carta_create');
    }

    public function update(User $user, CartaMunicipal $carta): bool
    {
        if (!$user->hasPermission('carta_edit')) {
            return false;
        }
        if ($user->canAccessAllMunicipios()) {
            return true;
        }
        return $user->municipio_id === $carta->municipio_id;
    }

    public function delete(User $user, CartaMunicipal $carta): bool
    {
        if (!$user->hasPermission('carta_delete')) {
            return false;
        }
        return $user->isINOTU();
    }
}



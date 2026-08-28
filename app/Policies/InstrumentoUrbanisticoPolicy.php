<?php

namespace App\Policies;

use App\Models\InstrumentoUrbanistico;
use App\Models\User;

class InstrumentoUrbanisticoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('instrumento_view');
    }

    public function view(User $user, InstrumentoUrbanistico $instrumento): bool
    {
        if ($user->canAccessAllMunicipios()) {
            return true;
        }
        return $user->municipio_id === $instrumento->municipio_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('instrumento_create');
    }

    public function update(User $user, InstrumentoUrbanistico $instrumento): bool
    {
        if (!$user->hasPermission('instrumento_edit')) {
            return false;
        }
        if ($user->canAccessAllMunicipios()) {
            return true;
        }
        return $user->municipio_id === $instrumento->municipio_id;
    }

    public function delete(User $user, InstrumentoUrbanistico $instrumento): bool
    {
        if (!$user->hasPermission('instrumento_delete')) {
            return false;
        }
        return $user->isINOTU();
    }
}


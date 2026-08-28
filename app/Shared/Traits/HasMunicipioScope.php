<?php

namespace App\Shared\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Trait para adicionar filtro de município a um modelo.
 *
 * Limita automaticamente as queries ao município do usuário autenticado
 * se o usuário for um Técnico Municipal.
 */
trait HasMunicipioScope
{
    public static function bootHasMunicipioScope(): void
    {
        static::addGlobalScope('municipio', function ($query) {
            $user = User::find(Auth::id());

            // Se for técnico municipal, filtra pelo seu município
            if ($user && $user->hasRole('tecnico_municipal') && method_exists($user, 'municipio')) {
                $query->where('municipio_id', $user->municipio_id);
            }
        });
    }

    /**
     * Obtém todos os registros desabilitando o escopo de município.
     */
    public function scopeWithoutMunicipioScope($query)
    {
        return $query->withoutGlobalScope('municipio');
    }

    /**
     * Filtra por um município específico.
     */
    public function scopeForMunicipio($query, int $municipioId)
    {
        return $query->where('municipio_id', $municipioId);
    }
}

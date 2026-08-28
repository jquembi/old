<?php

namespace App\Shared\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Shared\Exceptions\UnauthorizedException;

/**
 * Middleware para garantir que o usuário pode editar um município.
 *
 * Verifica se:
 * - Usuário é INOTU (pode editar tudo)
 * - Usuário é técnico municipal do próprio município
 */
class EnsureCanEditMunicipio
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();
        $municipio = $request->route('municipio');

        if (!$user) {
            return response()->json(['message' => 'Não autenticado'], 401);
        }

        // INOTU pode editar tudo
        if ($user->hasRole('inotu')) {
            return $next($request);
        }

        // Técnico municipal só pode editar seu próprio município
        if ($user->hasRole('tecnico_municipal')) {
            if ($user->municipio_id === $municipio?->id) {
                return $next($request);
            }
        }

        throw new UnauthorizedException(
            'Você não tem permissão para editar este município.'
        );
    }
}

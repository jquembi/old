<?php

namespace App\Shared\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Shared\Exceptions\UnauthorizedException;

/**
 * Middleware para verificar se o usuário tem um role específico.
 *
 * Uso na rota:
 * Route::get('/admin', Controller::class)->middleware('ensure.role:inotu');
 * Route::get('/manager', Controller::class)->middleware('ensure.role:inotu,governo_provincial');
 */
class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Não autenticado'], 401);
        }

        if (!$user->hasAnyRole($roles)) {
            throw new UnauthorizedException(
                'O usuário não tem o role necessário para acessar este recurso.'
            );
        }

        return $next($request);
    }
}

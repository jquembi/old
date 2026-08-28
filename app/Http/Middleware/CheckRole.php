<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de verificação de role para a API.
 *
 * Suporta roles nomeadas (inotu, provincial, municipal) e níveis numéricos.
 * Uso: ->middleware('check.role:inotu,provincial')
 */
class CheckRole
{
    private const ROLE_MAP = [
        'inotu'      => 1,
        'provincial' => 2,
        'municipal'  => 3,
        'tecnico'    => 4,
    ];

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->activo || $user->roles->isEmpty()) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Acesso negado.',
                'errors'  => null,
            ], 403);
        }

        $nivelUser = $user->nivelMinimo();
        $niveisPermitidos = [];

        foreach ($roles as $role) {
            if (isset(self::ROLE_MAP[$role])) {
                $niveisPermitidos[] = self::ROLE_MAP[$role];
            } elseif (is_numeric($role)) {
                $niveisPermitidos[] = (int) $role;
            }
        }

        // INOTU (nível 1) tem acesso a tudo
        if ($nivelUser === 1) {
            return $next($request);
        }

        if (! in_array($nivelUser, $niveisPermitidos)) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'O seu nível de acesso não permite esta operação.',
                'errors'  => null,
            ], 403);
        }

        return $next($request);
    }
}

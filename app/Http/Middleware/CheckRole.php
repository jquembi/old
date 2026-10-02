<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de verificação de role para a API.
 *
 * Suporta roles nomeadas (nacional, inotu, provincial, municipal, tecnico) e níveis numéricos.
 * Uso: ->middleware('check.role:inotu,provincial')
 */
class CheckRole
{
    private const ROLE_MAP = [
        'nacional'   => Role::NIVEL_ADMIN_NACIONAL,
        'inotu'      => Role::NIVEL_INOTU,
        'provincial' => Role::NIVEL_PROVINCIAL,
        'municipal'  => Role::NIVEL_MUNICIPAL,
        'tecnico'    => Role::NIVEL_TECNICO,
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

        // Super Admin Nacional (nível 0) e Admin Nacional (nível 1) têm acesso a tudo
        if ($nivelUser !== null && $nivelUser <= Role::NIVEL_ADMIN_NACIONAL) {
            return $next($request);
        }

        $niveisPermitidos = [];

        foreach ($roles as $role) {
            if (isset(self::ROLE_MAP[$role])) {
                $niveisPermitidos[] = self::ROLE_MAP[$role];
            } elseif (is_numeric($role)) {
                $niveisPermitidos[] = (int) $role;
            }
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


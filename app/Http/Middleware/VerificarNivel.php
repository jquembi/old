<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarNivel
{
    public function handle(Request $request, Closure $next, string ...$niveis): Response
    {
        $user = $request->user();

        if (!$user || !$user->activo || $user->roles->isEmpty()) {
            abort(403, 'Acesso negado.');
        }

        $niveisPermitidos = array_map('intval', $niveis);

        // Verificar se alguma role do utilizador tem um nível permitido
        $niveisUser = $user->roles->pluck('nivel')->toArray();
        if (empty(array_intersect($niveisUser, $niveisPermitidos))) {
            abort(403, 'O seu nível de acesso não permite aceder a este recurso.');
        }

        return $next($request);
    }
}

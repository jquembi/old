<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarPermissao
{
    public function handle(Request $request, Closure $next, string $permissao): Response
    {
        $user = $request->user();

        if (!$user || !$user->activo) {
            abort(403, 'Acesso negado.');
        }

        if (!$user->temPermissao($permissao)) {
            abort(403, 'Não tem permissão para aceder a este recurso.');
        }

        return $next($request);
    }
}

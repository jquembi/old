<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Não autenticado.',
                'errors' => null,
            ], 401);
        }

        if (!$user->hasAnyRole($roles)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Sem permissão. Role necessária: ' . implode(' ou ', $roles),
                'errors' => null,
            ], 403);
        }

        return $next($request);
    }
}

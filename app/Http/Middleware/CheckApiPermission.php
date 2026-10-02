<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
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

        if (!$user->activo || !$user->temPermissao($permission)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => "Sem permissão: {$permission}",
                'errors' => null,
            ], 403);
        }

        return $next($request);
    }
}

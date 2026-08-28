<?php

namespace App\Http\Middleware;

use App\Models\LogAcesso;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistarAcesso
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ignorar endpoints de polling de alta frequência (editor GIS sync)
        if ($request->user() && !$request->is('api/maps/*/sync')) {
            LogAcesso::create([
                'user_id' => $request->user()->id,
                'accao' => $this->determinarAccao($request),
                'recurso' => $request->path(),
                'detalhes' => $request->method() . ' ' . $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }

    private function determinarAccao(Request $request): string
    {
        return match ($request->method()) {
            'GET' => 'ver',
            'POST' => 'criar',
            'PUT', 'PATCH' => 'editar',
            'DELETE' => 'eliminar',
            default => 'outro',
        };
    }
}

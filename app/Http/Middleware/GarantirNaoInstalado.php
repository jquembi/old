<?php

namespace App\Http\Middleware;

use App\Services\InstaladorService;
use Closure;
use Illuminate\Http\Request;

class GarantirNaoInstalado
{
    public function handle(Request $request, Closure $next)
    {
        if (InstaladorService::instalado()) {
            return $request->expectsJson() ? abort(403, 'O sistema já está instalado.') : redirect('/login');
        }

        return $next($request);
    }
}

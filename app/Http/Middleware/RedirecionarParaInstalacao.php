<?php

namespace App\Http\Middleware;

use App\Services\InstaladorService;
use Closure;
use Illuminate\Http\Request;

class RedirecionarParaInstalacao
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->runningUnitTests()
            || $request->is('instalar', 'instalar/*', 'up')
            || InstaladorService::instalado()) {
            return $next($request);
        }

        return redirect('/instalar');
    }
}

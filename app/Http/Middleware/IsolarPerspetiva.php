<?php

namespace App\Http\Middleware;

use App\Services\PerspetivaService;
use Closure;
use Illuminate\Http\Request;

/**
 * Isola as áreas da aplicação conforme a perspetiva ativa do Superadmin.
 */
class IsolarPerspetiva
{
    public function __construct(private readonly PerspetivaService $perspetiva)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $request->isMethod('GET') || ! $this->perspetiva->isSuperAdmin($user)) {
            return $next($request);
        }

        $nome = $request->route()?->getName();

        if ($this->perspetiva->rotaPermitida($nome)) {
            return $next($request);
        }

        return redirect()
            ->route($this->perspetiva->rotaInicial())
            ->with('info', 'Esta área não pertence à perspetiva ativa: '.$this->perspetiva->label().'.');
    }
}

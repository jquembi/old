<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class TenantType
{
    public function handle(Request $request, Closure $next, string $type)
    {
        $tenant = $request->attributes->get('tenant') ?? app('current.tenant');

        if (!$tenant) {
            abort(404, 'Tenant não encontrado');
        }

        // Verificar se o tenant é do tipo especificado
        if ($type === 'central' && !$tenant->isCentral()) {
            abort(403, 'Acesso restrito ao Núcleo Central Nacional');
        }

        if ($type === 'provincial' && !$tenant->isProvincial()) {
            abort(403, 'Acesso restrito aos Nodes Provinciais');
        }

        return $next($request);
    }
}

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

        if (! $tenant) {
            abort(404, 'Tenant não encontrado');
        }

        if ($type === 'central') {
            $user = $request->user();
            abort_unless($user && $user->activo && $user->isNacional(), 403, 'Acesso restrito aos perfis nacionais');
        }

        // Verificar se o tenant é do tipo especificado
        if ($type === 'central' && ! $tenant->isCentral()) {
            abort(403, 'Acesso restrito ao Núcleo Central Nacional');
        }

        // O Superadmin acede às rotas provinciais em qualquer perspetiva; o isolamento
        // por perspetiva é feito pelo middleware `perspetiva`.
        $superadmin = $type === 'provincial' && $request->user()?->isSuperAdminNacional();

        if ($type === 'provincial' && ! $superadmin && ! $tenant->isProvincial()) {
            abort(403, 'Acesso restrito aos Nodes Provinciais');
        }

        return $next($request);
    }
}

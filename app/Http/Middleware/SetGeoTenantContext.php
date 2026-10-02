<?php

namespace App\Http\Middleware;

use App\Geo\GeoTenantContext;
use Closure;
use Illuminate\Http\Request;

class SetGeoTenantContext
{
    public function handle(Request $request, Closure $next)
    {
        if (config('angola-geoguard.enabled', false)) {
            $request->attributes->set('geo_tenant_id', app(GeoTenantContext::class)->tenantId());
        }

        return $next($request);
    }
}

<?php

namespace App\Geo;

use JoseQuembi\AngolaGeoGuard\Contracts\TenantContextInterface;

class GeoTenantContext implements TenantContextInterface
{
    public function tenantId(): string
    {
        $tenant = app()->bound('current.tenant') ? app('current.tenant') : null;

        return (string) ($tenant?->id ?: $tenant?->code ?: config('tenancy.node.code'));
    }

    public function tenantSlug(): ?string
    {
        $tenant = app()->bound('current.tenant') ? app('current.tenant') : null;

        return $tenant?->code ? strtolower((string) $tenant->code) : null;
    }

    public function geoConfig(): array
    {
        return [
            'mode' => config('angola-geoguard.default_mode'),
            'allowed_provinces' => config('angola-geoguard.allowed_provinces', []),
            'blocked_provinces' => config('angola-geoguard.blocked_provinces', []),
        ];
    }
}

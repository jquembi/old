<?php

namespace App\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use JoseQuembi\AngolaGeoGuard\DTOs\GeoAccessDecision;
use JoseQuembi\AngolaGeoGuard\Events\GeoAccessAllowed;
use JoseQuembi\AngolaGeoGuard\Events\GeoAccessDenied;
use JoseQuembi\AngolaGeoGuard\Events\GeoAccessRequiresVerification;
use Throwable;

class RecordGeoAccessDecision
{
    public function handle(GeoAccessAllowed|GeoAccessDenied|GeoAccessRequiresVerification $event): void
    {
        if (! config('angola-geoguard.audit.enabled', true) || ! Schema::hasTable('geo_access_decisions')) {
            return;
        }

        try {
            $this->store($event->decision);
        } catch (Throwable $exception) {
            // A auditoria complementar não pode indisponibilizar um node.
            report($exception);
        }
    }

    private function store(GeoAccessDecision $decision): void
    {
        $request = request();
        $location = $decision->location;
        $coordinates = $location?->coordinates;

        DB::table('geo_access_decisions')->insert([
            'uuid' => (string) Str::uuid(),
            'allowed' => $decision->allowed,
            'reason_code' => $decision->reasonCode->value,
            'reason' => $decision->reason,
            'policy_identifier' => $decision->policyIdentifier,
            'tenant_id' => $request->attributes->get('geo_tenant_id'),
            'user_id' => auth()->id() ? (string) auth()->id() : null,
            'country_code' => $location?->countryCode,
            'province_slug' => $location?->provinceSlug,
            'latitude' => config('angola-geoguard.audit.store_coordinates') ? $coordinates?->latitude : null,
            'longitude' => config('angola-geoguard.audit.store_coordinates') ? $coordinates?->longitude : null,
            'confidence' => $decision->confidence->value,
            'risk' => $decision->risk->value,
            'is_vpn' => $location?->isVpn ?? false,
            'is_proxy' => $location?->isProxy ?? false,
            'is_tor' => $location?->isTor ?? false,
            'is_datacenter' => $location?->isDatacenter ?? false,
            'provider' => $location?->provider,
            'ip_address' => config('angola-geoguard.audit.store_ip') ? $request->ip() : null,
            'evidence' => json_encode($decision->evidence, JSON_THROW_ON_ERROR),
            'warnings' => json_encode($decision->warnings, JSON_THROW_ON_ERROR),
            'applied_exceptions' => json_encode($decision->appliedExceptions, JSON_THROW_ON_ERROR),
            'processing_time_ms' => $decision->processingTimeMs ? (int) round($decision->processingTimeMs) : null,
            'created_at' => $decision->decidedAt,
        ]);
    }
}

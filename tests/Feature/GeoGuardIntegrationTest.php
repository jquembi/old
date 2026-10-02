<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use JoseQuembi\AngolaGeoGuard\DTOs\GeoAccessDecision;
use JoseQuembi\AngolaGeoGuard\Enums\ConfidenceLevel;
use JoseQuembi\AngolaGeoGuard\Enums\DecisionReasonCode;
use JoseQuembi\AngolaGeoGuard\Enums\RiskLevel;
use JoseQuembi\AngolaGeoGuard\Events\GeoAccessAllowed;

test('regista decisões geográficas sem reter o endereço IP por defeito', function () {
    config()->set('angola-geoguard.audit.enabled', true);
    config()->set('angola-geoguard.audit.store_ip', false);

    expect(Schema::hasTable('geo_access_decisions'))->toBeTrue();

    request()->attributes->set('geo_tenant_id', 'HUI');

    event(new GeoAccessAllowed(new GeoAccessDecision(
        allowed: true,
        reasonCode: DecisionReasonCode::ACCESS_ALLOWED_EXCEPTION,
        reason: 'Decisão de teste em observação.',
        location: null,
        policyIdentifier: 'geo.angola',
        confidence: ConfidenceLevel::VERY_LOW,
        risk: RiskLevel::MEDIUM,
        evidence: ['test'],
        warnings: ['observation'],
        appliedExceptions: [],
        decidedAt: new DateTimeImmutable(),
    )));

    $record = DB::table('geo_access_decisions')->latest('id')->first();

    expect($record)
        ->tenant_id->toBe('HUI')
        ->policy_identifier->toBe('geo.angola')
        ->ip_address->toBeNull();
});

<?php

use App\Models\Tenant;
use Database\Seeders\NodesSeeder;
use Database\Seeders\ProvinciasMunicipiosSeeder;

test('semeia nodes provinciais Cunene, Huíla e Namibe de forma idempotente', function () {
    $this->seed(ProvinciasMunicipiosSeeder::class);
    $this->seed(NodesSeeder::class);
    $this->seed(NodesSeeder::class);

    $nodes = Tenant::provincial()
        ->with('provincia')
        ->orderBy('code')
        ->get();

    expect($nodes->pluck('code')->all())->toBe(['CUN', 'HUI', 'NAM'])
        ->and($nodes->firstWhere('code', 'CUN')->provincia->nome)->toBe('Cunene')
        ->and($nodes->firstWhere('code', 'HUI')->provincia->nome)->toBe('Huíla')
        ->and($nodes->firstWhere('code', 'NAM')->provincia->nome)->toBe('Namibe')
        ->and(Tenant::provincial()->count())->toBe(3);
});

<?php

use App\Models\Municipio;
use App\Models\Provincia;
use App\Models\User;

it('numa instalação provincial só mostra a província escolhida', function () {
    config([
        'tenancy.deployment_mode' => 'node',
        'tenancy.node.code' => 'HUI',
    ]);

    $huila = Provincia::create(['codigo' => 'HUI', 'nome' => 'Huíla', 'activo' => true]);
    $luanda = Provincia::create(['codigo' => 'LUA', 'nome' => 'Luanda', 'activo' => true]);
    Municipio::create(['nome' => 'Lubango', 'slug' => 'lubango', 'codigo' => 'HUI-LUB', 'provincia_id' => $huila->id]);
    Municipio::create(['nome' => 'Belas', 'slug' => 'belas', 'codigo' => 'LUA-BEL', 'provincia_id' => $luanda->id]);
    User::factory()->create(['provincia_id' => $huila->id, 'email' => 'huila@inotu.ao']);
    User::factory()->create(['provincia_id' => $luanda->id, 'email' => 'luanda@inotu.ao']);

    expect(Provincia::pluck('codigo')->all())->toBe(['HUI'])
        ->and(Municipio::pluck('codigo')->all())->toBe(['HUI-LUB'])
        ->and(User::pluck('email')->all())->toContain('huila@inotu.ao')
        ->and(User::pluck('email')->all())->not->toContain('luanda@inotu.ao');
});

it('o núcleo continua a ver todas as províncias', function () {
    config(['tenancy.deployment_mode' => 'central']);

    Provincia::create(['codigo' => 'HUI', 'nome' => 'Huíla', 'activo' => true]);
    Provincia::create(['codigo' => 'LUA', 'nome' => 'Luanda', 'activo' => true]);

    expect(Provincia::orderBy('codigo')->pluck('codigo')->all())->toBe(['HUI', 'LUA']);
});

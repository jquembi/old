<?php

use App\Models\User;

it('instala os dados base e cria o super administrador', function () {
    $this->artisan('gterra:install', [
        '--admin-name' => 'Admin Teste',
        '--admin-email' => 'instalador@inotu.ao',
        '--admin-password' => 'segredo1234',
        '--skip-build' => true,
    ])->assertSuccessful();

    $user = User::where('email', 'instalador@inotu.ao')->first();

    expect($user)->not->toBeNull()
        ->and($user->isNacional())->toBeTrue()
        ->and(User::where('email', 'superadmin@inotu.ao')->exists())->toBeFalse();
});

it('rejeita palavra-passe curta', function () {
    $this->artisan('gterra:install', [
        '--admin-email' => 'curta@inotu.ao',
        '--admin-name' => 'X',
        '--admin-password' => '123',
        '--skip-build' => true,
    ])->assertFailed();
});

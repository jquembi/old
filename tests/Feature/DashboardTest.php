<?php

use App\Models\Permissao;
use App\Models\Role;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('users without um tenant provincial não podem visitar o dashboard provincial', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertForbidden();
});

test('super administrador nacional pode visitar o dashboard de um node provincial', function () {
    $role = Role::firstOrCreate(
        ['nome' => Role::SUPER_ADMIN_NACIONAL],
        [
            'descricao' => 'Super administrador nacional',
            'grupo' => 'Nacional',
            'nivel' => Role::NIVEL_NACIONAL,
        ],
    );

    $user = User::factory()->create([
        'activo' => true,
        'email_verified_at' => now(),
    ]);
    $user->roles()->attach($role);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
});

test('administrador INOTU pode visitar o dashboard de um node provincial', function () {
    $role = Role::firstOrCreate(
        ['nome' => Role::CHEFE_SECCAO_TECNICA],
        [
            'descricao' => 'Administrador INOTU',
            'grupo' => Role::GRUPO_INOTU,
            'nivel' => Role::NIVEL_INOTU,
        ],
    );

    $user = User::factory()->create([
        'activo' => true,
        'email_verified_at' => now(),
    ]);
    $user->roles()->attach($role);
    $role->darPermissao(Permissao::firstOrCreate(
        ['nome' => 'nodes.acessar'],
        [
            'descricao' => 'Acessar nodes provinciais',
            'grupo' => 'Tenancy',
        ],
    ));

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
});

test('administrador INOTU sem permissao de node continua bloqueado', function () {
    $role = Role::firstOrCreate(
        ['nome' => Role::CHEFE_SECCAO_TECNICA],
        [
            'descricao' => 'Administrador INOTU',
            'grupo' => Role::GRUPO_INOTU,
            'nivel' => Role::NIVEL_INOTU,
        ],
    );

    $user = User::factory()->create([
        'activo' => true,
        'email_verified_at' => now(),
    ]);
    $user->roles()->attach($role);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertForbidden();
});

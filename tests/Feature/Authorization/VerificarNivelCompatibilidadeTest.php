<?php

use App\Http\Middleware\VerificarNivel;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

function userWithAccessLevel(string $roleName, int $level): User
{
    $role = Role::firstOrCreate(
        ['nome' => $roleName],
        [
            'descricao' => $roleName,
            'grupo' => 'Teste',
            'nivel' => $level,
        ],
    );

    $user = User::factory()->create(['activo' => true]);
    $user->roles()->attach($role);

    return $user->fresh('roles');
}

function requestForAccessLevel(User $user): Request
{
    $request = Request::create('/teste');
    $request->setUserResolver(fn (): User => $user);

    return $request;
}

it('aceita um administrador INOTU numa rota administrativa legada', function () {
    $user = userWithAccessLevel(Role::DIRECTOR_INOTU, Role::NIVEL_INOTU);

    $response = app(VerificarNivel::class)->handle(
        requestForAccessLevel($user),
        fn () => response('ok'),
        '1',
    );

    expect($response->getStatusCode())->toBe(200);
});

it('continua a negar uma rota administrativa INOTU a um perfil provincial', function () {
    $user = userWithAccessLevel(Role::GOVERNADOR, Role::NIVEL_PROVINCIAL);

    expect(fn () => app(VerificarNivel::class)->handle(
        requestForAccessLevel($user),
        fn () => response('ok'),
        '1',
    ))->toThrow(HttpException::class);
});

<?php

use App\Http\Middleware\VerificarAcessoMunicipio;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

it('permite ao super administrador nacional aceder a qualquer municipio', function () {
    $role = Role::firstOrCreate(
        ['nome' => Role::SUPER_ADMIN_NACIONAL],
        [
            'descricao' => 'Super administrador nacional',
            'grupo' => 'Nacional',
            'nivel' => Role::NIVEL_NACIONAL,
        ],
    );

    $user = User::factory()->create(['activo' => true]);
    $user->roles()->attach($role);
    $user = $user->fresh('roles');

    $request = Request::create('/municipio/999');
    $request->setUserResolver(fn (): User => $user);

    $route = new Route('GET', 'municipio/{municipio}', []);
    $route->bind($request);
    $request->setRouteResolver(fn (): Route => $route);

    $response = app(VerificarAcessoMunicipio::class)->handle(
        $request,
        fn () => response('ok'),
    );

    expect($response->getStatusCode())->toBe(200);
});

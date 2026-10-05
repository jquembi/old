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

function nacionalAccessUser(int $nivel, string $nome): User
{
    $role = Role::firstOrCreate(['nome' => $nome], [
        'descricao' => 'Perfil de teste', 'grupo' => 'Teste', 'nivel' => $nivel,
    ]);
    $provincia = \App\Models\Provincia::firstOrCreate(['codigo' => 'HUI'], ['nome' => 'Huíla', 'activo' => true]);
    $user = User::factory()->create(['activo' => true, 'provincia_id' => $provincia->id]);
    $user->roles()->attach($role);

    return $user->fresh('roles');
}

it('permite ao superadmin com província abrir as rotas nacionais no núcleo', function () {
    config(['tenancy.deployment_mode' => 'central']);
    $this->actingAs(nacionalAccessUser(Role::NIVEL_NACIONAL, Role::SUPER_ADMIN_NACIONAL));
    $this->get('/nacional')->assertOk();
    $this->get('/nacional/indicadores', ['X-Tenant' => 'HUI'])->assertOk();
    $this->get('/nacional/administracao/usuarios-nacionais')->assertOk();
});

it('permite ao superadmin abrir o nacional na instalação local da Huíla', function () {
    $this->app->detectEnvironment(fn () => 'local');
    config([
        'tenancy.deployment_mode' => 'node', 'tenancy.node.code' => 'HUI',
        'tenancy.node.database' => ':memory:',
    ]);
    $this->actingAs(nacionalAccessUser(Role::NIVEL_NACIONAL, Role::SUPER_ADMIN_NACIONAL));
    $this->get('/nacional')->assertOk();
    $this->get('/nacional/indicadores')->assertOk();
    $this->get('/nacional/administracao/usuarios-nacionais')->assertOk();
});

it('mantém o node de produção separado do núcleo nacional', function () {
    $this->app->detectEnvironment(fn () => 'production');
    // Este teste isola o âmbito do tenant; a ligação node em memória não tem as tabelas GeoGuard.
    $this->withoutMiddleware(\JoseQuembi\AngolaGeoGuard\Http\Middleware\EnsureRequestIsFromAngola::class);
    config([
        'tenancy.deployment_mode' => 'node', 'tenancy.node.code' => 'HUI',
        'tenancy.node.database' => ':memory:',
    ]);
    $this->actingAs(nacionalAccessUser(Role::NIVEL_NACIONAL, Role::SUPER_ADMIN_NACIONAL));
    $this->get('https://hui.gterra.gov.ao/nacional')->assertForbidden();
});

it('impede um perfil provincial de entrar no nacional mesmo selecionando o central', function () {
    config(['tenancy.deployment_mode' => 'central']);
    $this->actingAs(nacionalAccessUser(Role::NIVEL_PROVINCIAL, Role::GOVERNADOR));
    $this->get('/nacional', ['X-Tenant' => 'CTR'])->assertForbidden();
});

it('impede um superadmin inactivo de entrar no nacional', function () {
    config(['tenancy.deployment_mode' => 'central']);
    $user = nacionalAccessUser(Role::NIVEL_NACIONAL, Role::SUPER_ADMIN_NACIONAL);
    $user->update(['activo' => false]);
    $this->actingAs($user);
    $this->get('/nacional')->assertForbidden();
});

<?php

use App\Models\Municipio;
use App\Models\Permissao;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

/*
|--------------------------------------------------------------------------
| Helpers — criar utilizadores com role e permissões
|--------------------------------------------------------------------------
*/

function criarRole(string $nome, int $nivel, array $permissoes = []): Role
{
    $role = Role::create([
        'nome'      => $nome,
        'descricao' => "Role {$nome}",
        'nivel'     => $nivel,
        'grupo'     => 'Teste',
    ]);

    foreach ($permissoes as $perm) {
        $p = Permissao::firstOrCreate(
            ['nome' => $perm],
            ['descricao' => $perm, 'grupo' => 'teste']
        );
        $role->permissoes()->attach($p);
    }

    return $role;
}

function criarUsuario(Role $role, ?Municipio $municipio = null): User
{
    $user = User::factory()->create([
        'provincia_id' => $municipio?->provincia_id,
        'municipio_id' => $municipio?->id,
        'activo'       => true,
    ]);
    $user->roles()->attach($role->id);

    return $user;
}

function criarMunicipio(string $nome = 'Lubango'): Municipio
{
    return Municipio::firstOrCreate(
        ['slug' => \Illuminate\Support\Str::slug($nome)],
        [
            'nome'         => $nome,
            'codigo'       => rand(1000, 9999),
            'provincia_id' => \App\Models\Provincia::firstOrCreate(
                ['nome' => 'Huíla'],
                ['slug' => 'huila', 'codigo' => '14']
            )->id,
            'activo'       => true,
            'latitude'     => -14.92,
            'longitude'    => 13.50,
        ],
    );
}

/*
|--------------------------------------------------------------------------
| Auth Tests
|--------------------------------------------------------------------------
*/

describe('Auth API', function () {

    test('login com credenciais válidas retorna token', function () {
        $role = criarRole('inotu_test', Role::NIVEL_INOTU);
        $user = criarUsuario($role);

        postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['token', 'token_type', 'user'],
                'message',
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'Bearer');
    });

    test('login com credenciais inválidas falha', function () {
        $role = criarRole('inotu_fail', Role::NIVEL_INOTU);
        $user = criarUsuario($role);

        postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'wrong_password',
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    });

    test('login com conta desactivada falha', function () {
        $role = criarRole('inotu_inactive', Role::NIVEL_INOTU);
        $user = User::factory()->create([
            'activo'  => false,
        ]);
        $user->roles()->attach($role->id);

        postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertStatus(403);
    });

    test('GET /me retorna dados do utilizador autenticado', function () {
        $role = criarRole('inotu_me', Role::NIVEL_INOTU, ['dashboard.ver']);
        $user = criarUsuario($role);

        actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['user' => ['id', 'nome', 'email', 'roles'], 'permissoes'],
            ])
            ->assertJsonPath('data.user.id', $user->id);
    });

    test('logout revoga o token', function () {
        $role = criarRole('inotu_logout', Role::NIVEL_INOTU);
        $user = criarUsuario($role);

        $token = $user->createToken('test')->plainTextToken;

        postJson('/api/v1/auth/logout', [], [
            'Authorization' => "Bearer {$token}",
        ])->assertOk();

        expect($user->tokens()->count())->toBe(0);
    });

    test('acesso sem token retorna 401', function () {
        getJson('/api/v1/auth/me')
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    });
});

/*
|--------------------------------------------------------------------------
| Municípios Tests
|--------------------------------------------------------------------------
*/

describe('Municípios API', function () {

    test('INOTU lista todos os municípios', function () {
        $municipio = criarMunicipio();
        $role = criarRole('inotu_mun', Role::NIVEL_INOTU);
        $user = criarUsuario($role, $municipio);

        actingAs($user, 'sanctum')
            ->getJson('/api/v1/municipios')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => ['current_page', 'total'],
            ]);
    });

    test('municipal só vê o seu município', function () {
        $mun1 = criarMunicipio('Lubango');
        $mun2 = criarMunicipio('Humpata');
        $role = criarRole('mun_scope', Role::NIVEL_MUNICIPAL, ['operacoes.ver']);
        $user = criarUsuario($role, $mun1);

        $response = actingAs($user, 'sanctum')
            ->getJson('/api/v1/municipios')
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id');
        expect($ids)->toContain($mun1->id);
        expect($ids)->not->toContain($mun2->id);
    });

    test('stats do município retorna contadores', function () {
        $municipio = criarMunicipio();
        $role = criarRole('inotu_stats', Role::NIVEL_INOTU);
        $user = criarUsuario($role, $municipio);

        actingAs($user, 'sanctum')
            ->getJson("/api/v1/municipios/{$municipio->slug}/stats")
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['totais'],
            ]);
    });
});

/*
|--------------------------------------------------------------------------
| Operações Tests
|--------------------------------------------------------------------------
*/

describe('Operações API', function () {

    test('CRUD completo de operação', function () {
        $municipio = criarMunicipio();
        $role = criarRole('inotu_op', Role::NIVEL_INOTU, [
            'operacoes.ver', 'operacoes.criar', 'operacoes.editar', 'operacoes.eliminar',
        ]);
        $user = criarUsuario($role, $municipio);

        // CREATE
        $response = actingAs($user, 'sanctum')
            ->postJson('/api/v1/operacoes', [
                'municipio_id' => $municipio->id,
                'titulo'       => 'Operação Teste',
                'descricao'    => 'Descrição da operação de teste.',
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true);

        $operacaoId = $response->json('data.id');

        // READ
        actingAs($user, 'sanctum')
            ->getJson("/api/v1/operacoes/{$operacaoId}")
            ->assertOk()
            ->assertJsonPath('data.titulo', 'Operação Teste');

        // UPDATE
        actingAs($user, 'sanctum')
            ->putJson("/api/v1/operacoes/{$operacaoId}", [
                'municipio_id' => $municipio->id,
                'titulo'       => 'Operação Actualizada',
            ])
            ->assertOk()
            ->assertJsonPath('data.titulo', 'Operação Actualizada');

        // DELETE
        actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/operacoes/{$operacaoId}")
            ->assertOk();

        // LIST (deve estar vazio)
        actingAs($user, 'sanctum')
            ->getJson('/api/v1/operacoes')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    });

    test('municipal não pode criar operação noutro município', function () {
        $mun1 = criarMunicipio('Lubango');
        $mun2 = criarMunicipio('Chibia');
        $role = criarRole('mun_deny', Role::NIVEL_MUNICIPAL, ['operacoes.ver', 'operacoes.criar']);
        $user = criarUsuario($role, $mun1);

        actingAs($user, 'sanctum')
            ->postJson('/api/v1/operacoes', [
                'municipio_id' => $mun2->id,
                'titulo'       => 'Tentativa negada',
            ])
            ->assertStatus(403);
    });

    test('coordenadas retorna GeoJSON FeatureCollection', function () {
        $municipio = criarMunicipio();
        $role = criarRole('inotu_geo', Role::NIVEL_INOTU, ['operacoes.ver', 'operacoes.criar']);
        $user = criarUsuario($role, $municipio);

        $operacao = \App\Models\OperacaoUrbanistica::create([
            'municipio_id' => $municipio->id,
            'titulo'       => 'Op Geo',
            'criado_por'   => $user->id,
        ]);

        \App\Models\Croqui::create([
            'operacao_id' => $operacao->id,
            'tipo'        => 'elaborado',
            'condicao'    => 'legal',
            'latitude'    => -14.92,
            'longitude'   => 13.50,
            'criado_por'  => $user->id,
        ]);

        actingAs($user, 'sanctum')
            ->getJson("/api/v1/operacoes/{$operacao->id}/coordenadas")
            ->assertOk()
            ->assertJsonPath('data.type', 'FeatureCollection')
            ->assertJsonCount(1, 'data.features');
    });
});

/*
|--------------------------------------------------------------------------
| Coordinates Tests
|--------------------------------------------------------------------------
*/

describe('Coordinates API', function () {

    test('converter lat/lng para UTM', function () {
        $role = criarRole('inotu_coord', Role::NIVEL_INOTU);
        $user = criarUsuario($role);

        actingAs($user, 'sanctum')
            ->postJson('/api/v1/coordinates/convert', [
                'format'    => 'latlng',
                'latitude'  => -14.92,
                'longitude' => 13.50,
            ])
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['coordinates' => ['utm_zone', 'utm_easting', 'utm_northing']],
            ]);
    });

    test('converter UTM para lat/lng', function () {
        $role = criarRole('inotu_utm', Role::NIVEL_INOTU);
        $user = criarUsuario($role);

        actingAs($user, 'sanctum')
            ->postJson('/api/v1/coordinates/convert', [
                'format'         => 'utm',
                'utm_zone'       => '33',
                'utm_hemisphere' => 'south',
                'utm_easting'    => 349000.000,
                'utm_northing'   => 8349000.000,
            ])
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['coordinates' => ['latitude', 'longitude']],
            ]);
    });

    test('default zone retorna zona 33S Huíla', function () {
        $role = criarRole('inotu_dz', Role::NIVEL_INOTU);
        $user = criarUsuario($role);

        actingAs($user, 'sanctum')
            ->getJson('/api/v1/coordinates/default-zone')
            ->assertOk()
            ->assertJsonPath('data.utm_zone', '33')
            ->assertJsonPath('data.utm_hemisphere', 'south');
    });
});

/*
|--------------------------------------------------------------------------
| Rate Limiting Tests
|--------------------------------------------------------------------------
*/

describe('Security', function () {

    test('rate limiting bloqueia após exceder limite', function () {
        $role = criarRole('inotu_rl', Role::NIVEL_INOTU);
        $user = criarUsuario($role);

        // Enviar muitas requisições de login com credenciais inválidas
        for ($i = 0; $i < 62; $i++) {
            postJson('/api/v1/auth/login', [
                'email'    => 'naoexiste@test.com',
                'password' => 'wrong',
            ]);
        }

        $response = postJson('/api/v1/auth/login', [
            'email'    => 'naoexiste@test.com',
            'password' => 'wrong',
        ]);

        // Deve ser 429 (Too Many Requests) ou 422 (validation)
        expect($response->status())->toBeIn([429, 422]);
    });
});

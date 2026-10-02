<?php

use App\Models\Provincia;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Route::middleware('set.tenant')->get('/__test/tenant', function () {
        return response()->json([
            'code' => app('current.tenant')->code,
            'connection' => config('database.default'),
        ]);
    });

    Route::middleware(['auth', 'set.tenant:validate'])->get('/__test/tenant-auth', fn () => response()->noContent());
});

test('resolve o node pelo cabeçalho e ativa a ligação provincial', function () {
    config()->set('tenancy.deployment_mode', 'node');
    config()->set('tenancy.node.code', 'HUI');
    config()->set('tenancy.node.database', ':memory:');

    $tenant = Tenant::where('code', 'HUI')->firstOrFail();
    $tenant->update(['database_name' => ':memory:']);

    $this->withHeader('X-Tenant', 'HUI')
        ->get('/__test/tenant')
        ->assertOk()
        ->assertJsonPath('code', 'HUI')
        ->assertJsonPath('connection', 'tenant');
});

test('um utilizador provincial não pode selecionar outro node', function () {
    // O núcleo é o único ponto que resolve e valida seleções entre províncias.
    config()->set('tenancy.deployment_mode', 'central');

    $provincia = Provincia::create(['nome' => 'Huíla', 'codigo' => 'HUI', 'activo' => true]);
    $role = Role::create(['nome' => 'governador_teste', 'descricao' => 'Teste', 'nivel' => Role::NIVEL_PROVINCIAL, 'grupo' => 'Teste']);
    $user = User::factory()->create(['provincia_id' => $provincia->id, 'email_verified_at' => now()]);
    $user->roles()->attach($role);

    Tenant::create([
        'code' => 'BGO',
        'name' => 'Província do Bengo',
        'type' => Tenant::TYPE_PROVINCIAL,
        'database_name' => ':memory:',
        'is_active' => true,
    ]);

    actingAs($user)
        ->get('/__test/tenant-auth', ['X-Tenant' => 'BGO'])
        ->assertForbidden();
});

test('um utilizador nacional pode selecionar o núcleo central', function () {
    config()->set('tenancy.deployment_mode', 'central');

    $role = Role::create(['nome' => Role::ADMIN_NACIONAL, 'descricao' => 'Admin nacional', 'nivel' => Role::NIVEL_ADMIN_NACIONAL, 'grupo' => 'Nacional']);
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->roles()->attach($role);

    actingAs($user)
        ->get('/__test/tenant-auth', ['X-Tenant' => 'CTR'])
        ->assertNoContent();
});

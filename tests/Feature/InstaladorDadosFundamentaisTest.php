<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\SystemCoreSeeder;
use Illuminate\Support\Facades\DB;

it('carrega apenas os dados fundamentais e não cria contas de demonstração', function () {
    $this->seed(SystemCoreSeeder::class);

    expect(User::query()->count())->toBe(0)
        ->and(Role::query()->where('nome', Role::SUPER_ADMIN_NACIONAL)->exists())->toBeTrue()
        ->and(Role::query()->where('nome', Role::ADMIN_SISTEMA_PROVINCIAL)->exists())->toBeTrue()
        ->and(DB::table('tenants')->count())->toBe(0)
        ->and(DB::table('workflows')->where('tipo_processo', 'processo_fundiario')->exists())->toBeTrue()
        ->and(DB::table('permissoes')->where('nome', 'documentos.ver')->exists())->toBeTrue()
        ->and(DB::table('permissoes')->where('nome', 'pagamentos.ver')->exists())->toBeTrue();
});

it('permite repetir os seeders fundamentais sem duplicar os catálogos', function () {
    $this->seed(SystemCoreSeeder::class);

    $firstProvinceCount = DB::table('provincias')->count();
    $firstRoleCount = DB::table('roles')->count();
    $firstPermissionCount = DB::table('permissoes')->count();
    $firstCategoryCount = DB::table('categoria_instrumentos')->count();
    $firstTypesCount = DB::table('tipo_instrumentos')->count();
    $firstWorkflowCount = DB::table('workflows')->count();

    $this->seed(SystemCoreSeeder::class);

    expect(DB::table('provincias')->count())->toBe($firstProvinceCount)
        ->and(DB::table('roles')->count())->toBe($firstRoleCount)
        ->and(DB::table('permissoes')->count())->toBe($firstPermissionCount)
        ->and(DB::table('categoria_instrumentos')->count())->toBe($firstCategoryCount)
        ->and(DB::table('tipo_instrumentos')->count())->toBe($firstTypesCount)
        ->and(DB::table('workflows')->count())->toBe($firstWorkflowCount);
});

it('rejeita SQLite em memória no assistente web multi-etapas', function () {
    $this->postJson('/instalar/testar-base', [
        'db_connection' => 'sqlite',
        'db_database' => ':memory:',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['db_database']);
});

<?php

use App\Livewire\Dashboard;
use App\Livewire\SeletorPerspetiva;
use App\Models\Instrumento;
use App\Models\Municipio;
use App\Models\Provincia;
use App\Models\Role;
use App\Models\User;
use App\Services\PerspetivaService;
use Livewire\Livewire;

function criarSuperAdmin(): User
{
    $role = Role::firstOrCreate(
        ['nome' => Role::SUPER_ADMIN_NACIONAL],
        [
            'descricao' => 'Super Administrador Nacional',
            'grupo' => 'INOTU Nacional',
            'nivel' => Role::NIVEL_NACIONAL,
        ],
    );

    $user = User::factory()->create([
        'name' => 'Super Admin Test',
        'email' => 'superadmin.test@gterra.ao',
        'activo' => true,
    ]);

    $user->roles()->attach($role);

    return $user->fresh('roles');
}

function criarUtilizadorComum(): User
{
    $role = Role::firstOrCreate(
        ['nome' => Role::TECNICO_ORDENAMENTO],
        [
            'descricao' => 'Técnico',
            'grupo' => 'Administração Municipal',
            'nivel' => Role::NIVEL_TECNICO,
        ],
    );

    $provincia = Provincia::firstOrCreate(
        ['codigo' => 'HUI'],
        ['nome' => 'Huíla', 'activo' => true],
    );

    $municipio = Municipio::firstOrCreate(
        ['slug' => 'lubango-unit'],
        ['nome' => 'Lubango', 'codigo' => 'LUB-UNIT', 'provincia_id' => $provincia->id, 'activo' => true],
    );

    $user = User::factory()->create([
        'name' => 'Técnico Comum',
        'email' => 'tecnico.test@gterra.ao',
        'activo' => true,
        'provincia_id' => $provincia->id,
        'municipio_id' => $municipio->id,
    ]);

    $user->roles()->attach($role);

    return $user->fresh(['roles', 'provincia', 'municipio']);
}

it('inicia por padrão em visão global para o superadmin', function () {
    $superadmin = criarSuperAdmin();
    $this->actingAs($superadmin);

    $service = app(PerspetivaService::class);

    expect($service->isSuperAdmin())->toBeTrue()
        ->and($service->getModo())->toBe(PerspetivaService::MODO_GLOBAL)
        ->and($service->isGlobal())->toBeTrue()
        ->and($service->isNacional())->toBeFalse()
        ->and($service->isProvincial())->toBeFalse()
        ->and($service->label())->toBe('Visão Global');
});

it('permite ao superadmin alternar para visão nacional', function () {
    $superadmin = criarSuperAdmin();
    $this->actingAs($superadmin);

    $service = app(PerspetivaService::class);
    $service->definirModo(PerspetivaService::MODO_NACIONAL);

    expect($service->getModo())->toBe(PerspetivaService::MODO_NACIONAL)
        ->and($service->isNacional())->toBeTrue()
        ->and($service->isGlobal())->toBeFalse()
        ->and($service->isProvincial())->toBeFalse()
        ->and($service->label())->toBe('Visão Nacional')
        ->and($superadmin->perspetivaModo())->toBe(PerspetivaService::MODO_NACIONAL);
});

it('permite ao superadmin alternar para visão por província', function () {
    $superadmin = criarSuperAdmin();
    $this->actingAs($superadmin);

    $provHuila = Provincia::firstOrCreate(
        ['codigo' => 'HUI'],
        ['nome' => 'Huíla', 'activo' => true],
    );
    $provLuanda = Provincia::firstOrCreate(
        ['codigo' => 'LUA'],
        ['nome' => 'Luanda', 'activo' => true],
    );

    $service = app(PerspetivaService::class);
    $service->definirModo(PerspetivaService::MODO_PROVINCIAL, $provLuanda->id);

    expect($service->getModo())->toBe(PerspetivaService::MODO_PROVINCIAL)
        ->and($service->isProvincial())->toBeTrue()
        ->and($service->getProvinciaId())->toBe($provLuanda->id)
        ->and($service->getProvincia()?->nome)->toBe('Luanda')
        ->and($service->label())->toBe('Província: Luanda')
        ->and($superadmin->perspetivaProvinciaId())->toBe($provLuanda->id);
});

it('impede utilizador não superadmin de alterar modo de perspetiva', function () {
    $user = criarUtilizadorComum();
    $this->actingAs($user);

    $service = app(PerspetivaService::class);
    $service->definirModo(PerspetivaService::MODO_NACIONAL);

    expect($service->isSuperAdmin())->toBeFalse()
        ->and($service->getModo())->toBe(PerspetivaService::MODO_GLOBAL);
});

it('renderiza o componente SeletorPerspetiva e responde a alterações', function () {
    $superadmin = criarSuperAdmin();
    $this->actingAs($superadmin);

    $provHuila = Provincia::firstOrCreate(
        ['codigo' => 'HUI'],
        ['nome' => 'Huíla', 'activo' => true],
    );

    Livewire::test(SeletorPerspetiva::class)
        ->assertSee('Visão Global')
        ->assertSee('Visão Nacional')
        ->assertSee('Visão por Província')
        ->call('selecionarModo', PerspetivaService::MODO_NACIONAL)
        ->assertDispatched('perspetiva-alterada', modo: PerspetivaService::MODO_NACIONAL);

    $service = app(PerspetivaService::class);
    expect($service->getModo())->toBe(PerspetivaService::MODO_NACIONAL);

    Livewire::test(SeletorPerspetiva::class)
        ->call('selecionarProvincia', $provHuila->id)
        ->assertDispatched('perspetiva-alterada', modo: PerspetivaService::MODO_PROVINCIAL);

    expect($service->getModo())->toBe(PerspetivaService::MODO_PROVINCIAL)
        ->and($service->getProvinciaId())->toBe($provHuila->id);
});

it('filtra o Dashboard exclusivamente para a província na visão por província', function () {
    $superadmin = criarSuperAdmin();
    $this->actingAs($superadmin);

    $provHuila = Provincia::firstOrCreate(['codigo' => 'HUI'], ['nome' => 'Huíla', 'activo' => true]);
    $provLuanda = Provincia::firstOrCreate(['codigo' => 'LUA'], ['nome' => 'Luanda', 'activo' => true]);

    $munHuila = Municipio::firstOrCreate(
        ['slug' => 'lubango-test'],
        ['nome' => 'Lubango Test', 'codigo' => 'LUB-T', 'provincia_id' => $provHuila->id, 'activo' => true],
    );
    $munLuanda = Municipio::firstOrCreate(
        ['slug' => 'belas-test'],
        ['nome' => 'Belas Test', 'codigo' => 'BEL-T', 'provincia_id' => $provLuanda->id, 'activo' => true],
    );

    // Definir modo provincial para Huíla
    $service = app(PerspetivaService::class);
    $service->definirModo(PerspetivaService::MODO_PROVINCIAL, $provHuila->id);

    Livewire::test(Dashboard::class)
        ->assertSee('Modo de Perspetiva Ativo')
        ->assertSee('Visão por Província')
        ->assertSee('Huíla')
        ->assertSee('Lubango Test')
        ->assertDontSee('Belas Test');
});

it('exibe o banner nacional no Dashboard na visão nacional', function () {
    $superadmin = criarSuperAdmin();
    $this->actingAs($superadmin);

    $service = app(PerspetivaService::class);
    $service->definirModo(PerspetivaService::MODO_NACIONAL);

    Livewire::test(Dashboard::class)
        ->assertSee('Modo de Perspetiva Ativo')
        ->assertSee('Visão Nacional')
        ->assertSee('Núcleo Central')
        ->assertSee('Dashboard Nacional');
});

it('permite alternar perspetiva diretamente a partir do Dashboard', function () {
    $superadmin = criarSuperAdmin();
    $this->actingAs($superadmin);

    $service = app(PerspetivaService::class);
    $service->definirModo(PerspetivaService::MODO_GLOBAL);

    $provHuila = Provincia::firstOrCreate(['codigo' => 'HUI'], ['nome' => 'Huíla', 'activo' => true]);

    Livewire::test(Dashboard::class)
        ->call('definirPerspetiva', PerspetivaService::MODO_PROVINCIAL, $provHuila->id)
        ->assertDispatched('perspetiva-alterada', modo: PerspetivaService::MODO_PROVINCIAL, provinciaId: $provHuila->id);

    expect($service->getModo())->toBe(PerspetivaService::MODO_PROVINCIAL)
        ->and($service->getProvinciaId())->toBe($provHuila->id);
});

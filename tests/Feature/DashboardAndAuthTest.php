<?php

use App\Livewire\Dashboard;
use App\Models\Municipio;
use App\Models\Provincia;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    $this->role = Role::firstOrCreate(
        ['nome' => Role::SUPER_ADMIN_NACIONAL],
        [
            'descricao' => 'Super administrador nacional',
            'grupo' => 'Nacional',
            'nivel' => Role::NIVEL_NACIONAL,
        ]
    );

    $this->provincia = Provincia::firstOrCreate(
        ['codigo' => 'HUI'],
        ['nome' => 'Huíla', 'activo' => true]
    );

    $this->municipio = Municipio::firstOrCreate(
        ['slug' => 'lubango'],
        ['nome' => 'Lubango', 'codigo' => 'LUB', 'provincia_id' => $this->provincia->id, 'activo' => true]
    );

    $this->user = User::create([
        'name' => 'Dr. António Silva',
        'email' => 'antonio.silva@gterras.gov.ao',
        'password' => Hash::make('Password123!'),
        'activo' => true,
        'email_verified_at' => now(),
        'provincia_id' => $this->provincia->id,
        'municipio_id' => $this->municipio->id,
    ]);
    $this->user->roles()->attach($this->role);
});

test('dashboard carrega metricas, graficos reais e ultimos registos', function () {
    Livewire::actingAs($this->user)
        ->test(Dashboard::class)
        ->assertOk()
        ->assertSee('Olá, Dr.')
        ->assertSee('Distribuição Territorial por Município')
        ->assertSee('Estado dos Instrumentos')
        ->assertSee('Evolução Mensal de Registos')
        ->assertSee('Tipologia de Projectos')
        ->assertSee('Módulos do Sistema');
});

test('ecra de autenticacao carrega com campos e layout seguro', function () {
    $response = $this->get(route('login'));

    $response->assertOk()
        ->assertSee('Iniciar Sessão')
        ->assertSee('Endereço de Email')
        ->assertSee('Palavra-passe')
        ->assertSee('Entrar no Sistema');
});

test('tentativa de login com credenciais invalidas retorna erro para toastr', function () {
    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => 'antonio.silva@gterras.gov.ao',
        'password' => 'SenhaErrada123',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
});

test('tentativa de login com conta desactivada exibe mensagem explicita de bloqueio', function () {
    $userInactivo = User::create([
        'name' => 'Utilizador Inactivo',
        'email' => 'utilizador.inactivo@gterras.gov.ao',
        'password' => Hash::make('Password123!'),
        'activo' => false,
        'email_verified_at' => now(),
    ]);

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => 'utilizador.inactivo@gterras.gov.ao',
        'password' => 'Password123!',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
});

test('autenticacao com sucesso inicia sessao e redireciona', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));

    $response->assertOk();
    $this->assertAuthenticatedAs($this->user);
});

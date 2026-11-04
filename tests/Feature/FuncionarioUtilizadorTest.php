<?php

use App\Http\Livewire\Erp\RecursosHumanos\Funcionarios;
use App\Models\Funcionario;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

function rhAdmin(): User
{
    $role = Role::firstOrCreate(
        ['nome' => Role::SUPER_ADMIN_NACIONAL],
        ['descricao' => 'Super Administrador Nacional', 'grupo' => 'INOTU Nacional', 'nivel' => Role::NIVEL_NACIONAL],
    );
    $user = User::factory()->create(['activo' => true]);
    $user->roles()->attach($role);

    return $user->fresh(['roles']);
}

function dadosFuncionario(array $extra = []): array
{
    return array_merge([
        'numero_funcionario' => 'F-'.random_int(1000, 9999),
        'nome_completo' => 'Maria Teste',
        'nome_curto' => 'Maria',
        'data_nascimento' => '1990-01-01',
        'genero' => 'feminino',
        'telefone' => '900000000',
        'data_admissao' => '2024-01-01',
        'tipo_contrato' => 'efetivo',
    ], $extra);
}

it('exige um utilizador ao guardar funcionário', function () {
    $this->actingAs(rhAdmin());

    $c = Livewire::test(Funcionarios::class)->call('create');
    foreach (dadosFuncionario() as $k => $v) {
        $c->set($k, $v);
    }
    $c->call('save')->assertHasErrors(['user_id_form' => 'required']);
});

it('vincula funcionário a utilizador existente', function () {
    $this->actingAs(rhAdmin());
    $alvo = User::factory()->create();

    $c = Livewire::test(Funcionarios::class)->call('create')->set('user_id_form', $alvo->id);
    foreach (dadosFuncionario() as $k => $v) {
        $c->set($k, $v);
    }
    $c->call('save')->assertHasNoErrors();

    expect($alvo->fresh()->funcionario)->not->toBeNull();
});

it('cria utilizador novo junto com o funcionário', function () {
    $this->actingAs(rhAdmin());
    $role = Role::firstOrCreate(
        ['nome' => 'tecnico_teste'],
        ['descricao' => 'Técnico', 'grupo' => 'Teste', 'nivel' => 9],
    );

    $c = Livewire::test(Funcionarios::class)->call('create')
        ->set('modo_utilizador', 'novo')
        ->set('email', 'novo.func@inotu.ao')
        ->set('user_password', 'segredo1234')
        ->set('user_role_id', $role->id);
    foreach (dadosFuncionario() as $k => $v) {
        $c->set($k, $v);
    }
    $c->call('save')->assertHasNoErrors();

    $user = User::where('email', 'novo.func@inotu.ao')->first();
    expect($user)->not->toBeNull()
        ->and($user->funcionario?->nome_completo)->toBe('Maria Teste')
        ->and($user->roles)->toHaveCount(1);
});

it('não permite o mesmo utilizador em dois funcionários', function () {
    $this->actingAs(rhAdmin());
    $alvo = User::factory()->create();
    Funcionario::create(dadosFuncionario(['user_id' => $alvo->id]));

    $c = Livewire::test(Funcionarios::class)->call('create')->set('user_id_form', $alvo->id);
    foreach (dadosFuncionario() as $k => $v) {
        $c->set($k, $v);
    }
    $c->call('save')->assertHasErrors(['user_id_form' => 'unique']);
});

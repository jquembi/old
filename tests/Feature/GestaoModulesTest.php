<?php

use App\Livewire\ErrosSistema;
use App\Livewire\GestaoPermissoes;
use App\Livewire\LixeiraSistema;
use App\Models\Role;
use App\Models\SystemError;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $role = Role::firstOrCreate(['nome' => Role::DIRECTOR_INOTU], [
        'descricao' => 'Director', 'grupo' => Role::GRUPO_INOTU, 'nivel' => Role::NIVEL_INOTU,
    ]);
    $user = User::factory()->create(['activo' => true]);
    $user->roles()->attach($role);
    $this->actingAs($user->fresh('roles'));
});

it('renderiza permissões com o tema partilhado', function () {
    Livewire::test(GestaoPermissoes::class)->assertStatus(200)->assertSee('Gestão de Permissões');
});

it('renderiza e filtra a lixeira vazia', function () {
    Livewire::test(LixeiraSistema::class)->assertStatus(200)->assertSee('A lixeira está vazia.')
        ->set('tipo', 'instrumentos')->assertStatus(200);
});

it('filtra, resolve e reabre erros do sistema', function () {
    $erro = SystemError::create([
        'fingerprint' => hash('sha256', 'erp-regression'), 'exception_class' => 'QueryException',
        'message' => 'Tabela inexistente', 'status' => 'pendente', 'occurrences' => 2,
        'first_seen_at' => now(), 'last_seen_at' => now(),
    ]);
    Livewire::test(ErrosSistema::class)->assertStatus(200)->assertSee('Tabela inexistente')
        ->call('marcarResolvido', $erro->id)->assertStatus(200);
    expect($erro->fresh()->status)->toBe('resolvido');
    Livewire::test(ErrosSistema::class)->set('status', 'pendente')->assertDontSee('Tabela inexistente')
        ->set('status', '')->call('reabrir', $erro->id)->assertSee('Tabela inexistente');
    expect($erro->fresh()->status)->toBe('pendente');
});

it('impede acesso de utilizadores sem autorização às telas administrativas', function () {
    $this->actingAs(User::factory()->create());
    Livewire::test(ErrosSistema::class)->assertForbidden();
    Livewire::test(LixeiraSistema::class)->assertForbidden();
});

it('lista e restaura um registo eliminado através da lixeira', function () {
    $provincia = \App\Models\Provincia::create(['nome' => 'Huíla', 'codigo' => 'HUI', 'activo' => true]);
    $municipio = \App\Models\Municipio::create([
        'nome' => 'Lubango', 'codigo' => 'HUI-LUB', 'provincia_id' => $provincia->id, 'activo' => true,
    ]);
    $loteamento = \App\Models\Loteamento::create([
        'municipio_id' => $municipio->id, 'nome' => 'Loteamento de teste',
        'estado' => 'rascunho', 'criado_por' => auth()->id(), 'deleted_by' => auth()->id(),
    ]);
    $loteamento->delete();
    Livewire::test(LixeiraSistema::class)->assertSee('Loteamento de teste')
        ->set('tipo', 'loteamentos')->assertSee('Loteamento de teste')
        ->call('restaurar', 'loteamentos', $loteamento->id)->assertDontSee('Loteamento de teste');
    expect($loteamento->fresh()->deleted_at)->toBeNull();
});

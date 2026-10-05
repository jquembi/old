<?php

use App\Livewire\LiveSearch;
use App\Models\Municipio;
use App\Models\Provincia;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

function criarUsuarioBusca(): User
{
    $role = Role::firstOrCreate(
        ['nome' => Role::SUPER_ADMIN_NACIONAL],
        [
            'descricao' => 'Super Administrador Nacional',
            'grupo' => 'INOTU Nacional',
            'nivel' => Role::NIVEL_NACIONAL,
        ],
    );

    $provincia = Provincia::firstOrCreate(
        ['codigo' => 'HUI'],
        ['nome' => 'Huíla', 'activo' => true],
    );

    $municipio = Municipio::firstOrCreate(
        ['slug' => 'lubango-search-test'],
        ['nome' => 'Lubango Central', 'codigo' => 'LUB-ST', 'provincia_id' => $provincia->id, 'activo' => true],
    );

    $user = User::factory()->create([
        'name' => 'Search Tester',
        'email' => 'search.tester@gterra.ao',
        'activo' => true,
        'provincia_id' => $provincia->id,
        'municipio_id' => $municipio->id,
    ]);

    $user->roles()->attach($role);

    return $user->fresh(['roles', 'provincia', 'municipio']);
}

it('renderiza o LiveSearch com o trigger de Command Bar e modal de atalhos rápidos', function () {
    $user = criarUsuarioBusca();
    $this->actingAs($user);

    Livewire::test(LiveSearch::class)
        ->assertSee('Pesquisa inteligente')
        ->assertSee('Acessos Rápidos do Sistema')
        ->assertSee('Painel Principal (Dashboard)')
        ->assertSee('Território & Municípios')
        ->assertSee('Gestão de Utilizadores')
        ->assertSee('Gestão de Pagamentos RUPE')
        ->assertSee('Núcleo Central Nacional')
        ->assertSee('Sugestões de Pesquisa')
        ->assertSee('Lubango')
        ->assertSee('Esc')
        ->assertSee('⌘');
});

it('pesquisa módulos e atalhos do sistema pelo termo', function () {
    $user = criarUsuarioBusca();
    $this->actingAs($user);

    Livewire::test(LiveSearch::class)
        ->set('query', 'pagamento')
        ->assertSee('Módulos & Atalhos')
        ->assertSee('Gestão de Pagamentos RUPE')
        ->assertSee('Arrecadação de emolumentos')
        ->assertSee('Abrir');
});

it('pesquisa municípios e apresenta resultados com metadados e badges', function () {
    $user = criarUsuarioBusca();
    $this->actingAs($user);

    Livewire::test(LiveSearch::class)
        ->set('query', 'Lubango Central')
        ->assertSee('Municípios')
        ->assertSee('Lubango Central')
        ->assertSee('LUB-ST')
        ->assertSee('Huíla');
});

it('permite filtrar por escopo de categoria', function () {
    $user = criarUsuarioBusca();
    $this->actingAs($user);

    Livewire::test(LiveSearch::class)
        ->set('query', 'Lubango Central')
        ->set('scope', 'municipios')
        ->assertSee('Municípios')
        ->assertSee('Lubango Central');
});

it('permite preencher o termo via setQuery e limpar via clear', function () {
    $user = criarUsuarioBusca();
    $this->actingAs($user);

    Livewire::test(LiveSearch::class)
        ->call('setQuery', 'Huíla')
        ->assertSet('query', 'Huíla')
        ->call('clear')
        ->assertSet('query', '')
        ->assertSet('scope', 'all');
});

it('exibe estado de nenhum resultado quando o termo não existe', function () {
    $user = criarUsuarioBusca();
    $this->actingAs($user);

    Livewire::test(LiveSearch::class)
        ->set('query', 'TermoTotalmenteInexistenteXYZ123')
        ->assertSee('Nenhum resultado para')
        ->assertSee('TermoTotalmenteInexistenteXYZ123')
        ->assertSee('Limpar Pesquisa');
});

<?php

use App\Http\Livewire\Erp\Financeiro\ContasGerais;
use App\Models\ContaGeral;
use Livewire\Livewire;

it('renderiza os módulos ERP e os formulários com as tabelas migradas', function (string $component) {
    Livewire::test($component)
        ->assertStatus(200)
        ->assertSee('Nenhum registo encontrado')
        ->call('create')
        ->assertSet('modalOpen', true)
        ->assertStatus(200);
})->with([
    'Contratos' => ['App\Http\Livewire\Erp\ComprasContratos\Contratos'],
    'Fornecedores' => ['App\Http\Livewire\Erp\ComprasContratos\Fornecedores'],
    'RequisicoesCompra' => ['App\Http\Livewire\Erp\ComprasContratos\RequisicoesCompra'],
    'ContasGerais' => ['App\Http\Livewire\Erp\Financeiro\ContasGerais'],
    'Lancamentos' => ['App\Http\Livewire\Erp\Financeiro\Lancamentos'],
    'Pagamentos' => ['App\Http\Livewire\Erp\Financeiro\Pagamentos'],
    'CategoriasPatrimonio' => ['App\Http\Livewire\Erp\Patrimonio\CategoriasPatrimonio'],
    'Localizacoes' => ['App\Http\Livewire\Erp\Patrimonio\Localizacoes'],
    'Patrimonios' => ['App\Http\Livewire\Erp\Patrimonio\Patrimonios'],
    'Cargos' => ['App\Http\Livewire\Erp\RecursosHumanos\Cargos'],
    'Departamentos' => ['App\Http\Livewire\Erp\RecursosHumanos\Departamentos'],
    'Funcionarios' => ['App\Http\Livewire\Erp\RecursosHumanos\Funcionarios'],
]);

it('guarda e consulta contas na tabela contas_gerais', function () {
    Livewire::test(ContasGerais::class)
        ->call('create')
        ->set('codigo', '11')
        ->set('nome', 'Caixa')
        ->set('tipo_conta', 'ativo')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('modalOpen', false)
        ->assertSee('Caixa');

    $pai = ContaGeral::where('codigo', '11')->firstOrFail();
    Livewire::test(ContasGerais::class)
        ->call('create')
        ->set('codigo', '11.1')
        ->set('nome', 'Caixa municipal')
        ->set('tipo_conta', 'ativo')
        ->set('conta_pai_id', $pai->id)
        ->call('save')
        ->assertHasNoErrors();

    expect($pai->contasFilhas()->firstOrFail()->nome)->toBe('Caixa municipal');
});

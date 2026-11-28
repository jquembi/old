<?php

use App\Livewire\CroquisElaborados;
use App\Models\Croqui;
use App\Models\Municipio;
use App\Models\OperacaoUrbanistica;
use App\Models\Provincia;
use App\Models\User;
use Livewire\Livewire;

it('lista croquis elaborados de todas as operações do município', function () {
    $provincia = Provincia::create([
        'nome' => 'Huíla',
        'codigo' => 'HUI',
        'activo' => true,
    ]);

    $municipio = Municipio::create([
        'nome' => 'Caconda',
        'codigo' => 'HUI-CCD',
        'provincia_id' => $provincia->id,
        'activo' => true,
    ]);

    $outroMunicipio = Municipio::create([
        'nome' => 'Chibia',
        'codigo' => 'HUI-CHB',
        'provincia_id' => $provincia->id,
        'activo' => true,
    ]);

    $user = User::factory()->create([
        'provincia_id' => $provincia->id,
        'municipio_id' => $municipio->id,
        'activo' => true,
    ]);

    $primeiraOperacao = OperacaoUrbanistica::create([
        'municipio_id' => $municipio->id,
        'titulo' => 'Croquis Elaborados',
        'criado_por' => $user->id,
    ]);

    $segundaOperacao = OperacaoUrbanistica::create([
        'municipio_id' => $municipio->id,
        'titulo' => 'Operação Complementar',
        'criado_por' => $user->id,
    ]);

    $operacaoOutroMunicipio = OperacaoUrbanistica::create([
        'municipio_id' => $outroMunicipio->id,
        'titulo' => 'Operação de outro município',
        'criado_por' => $user->id,
    ]);

    Croqui::create([
        'operacao_id' => $primeiraOperacao->id,
        'tipo' => 'elaborado',
        'titulo' => 'Croqui da primeira operação',
        'criado_por' => $user->id,
    ]);

    Croqui::create([
        'operacao_id' => $segundaOperacao->id,
        'tipo' => 'elaborado',
        'titulo' => 'Croqui da segunda operação',
        'criado_por' => $user->id,
    ]);

    Croqui::create([
        'operacao_id' => $segundaOperacao->id,
        'tipo' => 'terreno_ocioso',
        'titulo' => 'Terreno ocioso que não deve aparecer',
        'criado_por' => $user->id,
    ]);

    Croqui::create([
        'operacao_id' => $operacaoOutroMunicipio->id,
        'tipo' => 'elaborado',
        'titulo' => 'Croqui de outro município',
        'criado_por' => $user->id,
    ]);

    Livewire::test(CroquisElaborados::class, ['municipio' => $municipio])
        ->assertSee('Croqui da primeira operação')
        ->assertSee('Croqui da segunda operação')
        ->assertDontSee('Terreno ocioso que não deve aparecer')
        ->assertDontSee('Croqui de outro município');
});

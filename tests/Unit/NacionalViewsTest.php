<?php

uses(Tests\TestCase::class);

test('as views usadas pelas rotas nacionais estão disponíveis', function () {
    expect(view()->exists('livewire.nacional.indicadores'))->toBeTrue()
        ->and(view()->exists('livewire.nacional.supervisao-nodes'))->toBeTrue()
        ->and(view()->exists('livewire.nacional.node-detalhe'))->toBeTrue()
        ->and(view()->exists('livewire.nacional.relatorios'))->toBeTrue()
        ->and(view()->exists('livewire.nacional.parametros'))->toBeTrue()
        ->and(view()->exists('livewire.nacional.auditoria-nacional'))->toBeTrue()
        ->and(view()->exists('livewire.nacional.dashboard-nacional'))->toBeTrue()
        ->and(view()->exists('livewire.nacional.administracao-nacional'))->toBeTrue();
});

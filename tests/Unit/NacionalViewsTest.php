<?php

uses(Tests\TestCase::class);

test('as views usadas pelas rotas nacionais estão disponíveis', function () {
    expect(view()->exists('nacional.modulo'))->toBeTrue()
        ->and(view()->exists('livewire.nacional.dashboard-nacional'))->toBeTrue()
        ->and(view()->exists('livewire.nacional.administracao-nacional'))->toBeTrue();
});

<?php

use App\Livewire\GestaoAuditoria;
use App\Models\LogAcesso;
use App\Models\User;
use Livewire\Livewire;

it('renderiza os logs de acesso na aba inicial', function () {
    $user = User::factory()->create();

    LogAcesso::create([
        'user_id' => $user->id,
        'accao' => 'ver',
        'recurso' => 'Dashboard',
        'ip_address' => '127.0.0.1',
    ]);

    Livewire::test(GestaoAuditoria::class)
        ->assertSee('Logs de Acesso')
        ->assertSee('Dashboard');
});


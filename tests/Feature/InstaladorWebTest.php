<?php

use App\Models\User;

it('mostra o assistente quando ainda não há utilizadores', function () {
    $this->get('/instalar')->assertOk()->assertSee('Assistente de instalação');
});

it('bloqueia o assistente depois de instalado', function () {
    User::factory()->create();

    $this->get('/instalar')->assertRedirect('/login');
});

it('valida os dados da base de dados ao testar a ligação', function () {
    $this->postJson('/instalar/testar-base', ['db_connection' => 'mysql'])
        ->assertStatus(422);
});

it('testa a ligação SQLite', function () {
    $ficheiro = sys_get_temp_dir().DIRECTORY_SEPARATOR.'instalador-'.uniqid().'.sqlite';

    $this->postJson('/instalar/testar-base', [
        'db_connection' => 'sqlite',
        'db_database' => $ficheiro,
    ])->assertOk()->assertJson(['ok' => true]);

    @unlink($ficheiro);
});

<?php

use App\Livewire\ConfiguracaoDocumentoQr;
use App\Models\TipoDocumentoQrConfig;
use App\Models\User;
use App\Models\Role;
use Livewire\Livewire;

function criarUsuarioNacional(): User
{
    $role = Role::firstOrCreate(
        ['nome' => Role::SUPER_ADMIN_NACIONAL],
        [
            'descricao' => 'Super Administrador Nacional',
            'grupo' => 'INOTU Nacional',
            'nivel' => Role::NIVEL_NACIONAL,
        ],
    );

    $user = User::factory()->create([
        'name' => 'Admin QR Test',
        'email' => 'admin.qr@gterra.ao',
        'activo' => true,
    ]);

    $user->roles()->attach($role);

    return $user->fresh(['roles']);
}

it('permite que administradores acedam à página de personalização de layouts de QR code', function () {
    $admin = criarUsuarioNacional();
    $this->actingAs($admin);

    $this->get(route('settings.qr-layouts'))
        ->assertOk()
        ->assertSee('Personalização do Código QR por Tipo de Documento')
        ->assertSee('Croqui de Localização')
        ->assertSee('Memória Descritiva');
});

it('carrega o componente Livewire ConfiguracaoDocumentoQr com valores corretos', function () {
    $admin = criarUsuarioNacional();
    $this->actingAs($admin);

    Livewire::test(ConfiguracaoDocumentoQr::class)
        ->assertSet('tipoSelecionado', 'croqui')
        ->assertSee('Croqui de Localização')
        ->assertSee('Posição')
        ->assertSee('Dimensão')
        ->assertSee('Estilização');
});

it('permite alterar de tipo de documento e atualizar a posição do QR', function () {
    $admin = criarUsuarioNacional();
    $this->actingAs($admin);

    Livewire::test(ConfiguracaoDocumentoQr::class)
        ->call('selecionarTipo', 'memoria_descritiva')
        ->assertSet('tipoSelecionado', 'memoria_descritiva')
        ->assertSet('nome', 'Memória Descritiva')
        ->call('atualizarPosicaoInterativa', 75.5, 12.0, 18.0)
        ->assertSet('posicao_x_pct', 75.5)
        ->assertSet('posicao_y_pct', 12.0)
        ->assertSet('largura_pct', 18.0)
        ->call('salvar')
        ->assertDispatched('toast');

    $config = TipoDocumentoQrConfig::where('tipo_documento', 'memoria_descritiva')->first();
    expect($config)->not->toBeNull()
        ->and((float) $config->posicao_x_pct)->toBe(75.5)
        ->and((float) $config->posicao_y_pct)->toBe(12.0)
        ->and((float) $config->largura_pct)->toBe(18.0);
});

it('permite aplicar posições pré-definidas de cantos e restaurar padrões', function () {
    $admin = criarUsuarioNacional();
    $this->actingAs($admin);

    Livewire::test(ConfiguracaoDocumentoQr::class)
        ->call('selecionarTipo', 'croqui')
        ->call('aplicarPosicaoPredefinida', 'superior_esquerdo')
        ->assertSet('posicao_x_pct', 4.0)
        ->assertSet('posicao_y_pct', 4.0)
        ->call('restaurarPadrao')
        ->assertDispatched('toast');

    $config = TipoDocumentoQrConfig::where('tipo_documento', 'croqui')->first();
    expect($config)->not->toBeNull()
        ->and((float) $config->posicao_x_pct)->toBe(88.0)
        ->and((float) $config->posicao_y_pct)->toBe(82.0)
        ->and((float) $config->largura_pct)->toBe(7.5);
});

it('permite redimensionar o QR e abrir e fechar a modal de pré-visualização', function () {
    $admin = criarUsuarioNacional();
    $this->actingAs($admin);

    Livewire::test(ConfiguracaoDocumentoQr::class)
        ->call('selecionarTipo', 'croqui')
        ->assertSee('Pré-visualizar')
        ->assertSee('Arrastar')
        ->assertSee('Redimensionar')
        ->call('atualizarPosicaoInterativa', 70.0, 75.0, 22.5)
        ->assertSet('posicao_x_pct', 70.0)
        ->assertSet('posicao_y_pct', 75.0)
        ->assertSet('largura_pct', 22.5)
        ->call('abrirModalPrevisualizacao')
        ->assertSet('modalPrevisualizacaoAberta', true)
        ->call('fecharModalPrevisualizacao')
        ->assertSet('modalPrevisualizacaoAberta', false)
        ->call('salvar')
        ->assertDispatched('toast');

    $config = TipoDocumentoQrConfig::where('tipo_documento', 'croqui')->first();
    expect($config)->not->toBeNull()
        ->and((float) $config->largura_pct)->toBe(22.5);
});

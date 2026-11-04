<?php

use App\Livewire\DocumentoStudio;
use App\Models\DocumentoLayout;
use App\Models\Role;
use App\Models\User;
use App\Services\PdfQrStampService;
use Livewire\Livewire;

function studioAdmin(): User
{
    $role = Role::firstOrCreate(
        ['nome' => Role::SUPER_ADMIN_NACIONAL],
        ['descricao' => 'Super Administrador Nacional', 'grupo' => 'INOTU Nacional', 'nivel' => Role::NIVEL_NACIONAL],
    );
    $user = User::factory()->create(['activo' => true]);
    $user->roles()->attach($role);

    return $user->fresh(['roles']);
}

it('mostra o layout predefinido apenas com QR quando não há personalização', function () {
    $layout = DocumentoLayout::efetivo('croqui');

    expect($layout['personalizado'])->toBeFalse()
        ->and($layout['blocos'])->toHaveCount(1)
        ->and($layout['blocos'][0]['tipo'])->toBe('qr');
});

it('guarda, sanea e repõe o layout de um tipo', function () {
    $admin = studioAdmin();
    $this->actingAs($admin);

    $blocos = [
        ['id' => 'a1', 'tipo' => 'texto', 'x' => 5, 'y' => 5, 'w' => 40, 'h' => 4, 'props' => ['texto' => 'Olá {{orgao}}', 'tamanho' => 12, 'script' => 'x']],
        ['id' => 'a2', 'tipo' => 'inexistente', 'x' => 0, 'y' => 0, 'w' => 1, 'h' => 1],
        ['id' => 'a3', 'tipo' => 'qr', 'x' => 120, 'y' => -4, 'w' => 9, 'h' => 6, 'props' => []],
    ];

    Livewire::test(DocumentoStudio::class)
        ->call('selecionarTipo', 'parecer')
        ->call('guardar', $blocos, 'paisagem', true)
        ->assertDispatched('studio-guardado');

    $guardado = DocumentoLayout::where('tipo_documento', 'parecer')->firstOrFail();
    expect($guardado->orientacao)->toBe('paisagem')
        ->and($guardado->blocos)->toHaveCount(2)
        ->and($guardado->blocos[0]['props'])->not->toHaveKey('script')
        ->and($guardado->blocos[1]['x'])->toEqual(100);

    Livewire::test(DocumentoStudio::class)
        ->call('selecionarTipo', 'parecer')
        ->call('repor');

    expect(DocumentoLayout::where('tipo_documento', 'parecer')->exists())->toBeFalse();
});

it('guarda os dados institucionais globais', function () {
    $this->actingAs(studioAdmin());

    Livewire::test(DocumentoStudio::class)
        ->set('institucional.orgao', 'INOTU Teste')
        ->call('guardarInstitucional')
        ->assertHasNoErrors();

    expect(DocumentoLayout::institucional()['orgao'])->toBe('INOTU Teste');
});

it('aplica cabeçalho, rodapé e QR a um PDF', function () {
    $layout = DocumentoLayout::padrao('croqui');
    $layout['blocos'] = array_merge(DocumentoLayout::modelos()['cabecalho'], DocumentoLayout::modelos()['rodape'], $layout['blocos']);

    $pdf = PdfQrStampService::gerarExemplo($layout, 'Croqui');

    expect(substr($pdf, 0, 5))->toBe('%PDF-')
        ->and(strlen($pdf))->toBeGreaterThan(5000);
});

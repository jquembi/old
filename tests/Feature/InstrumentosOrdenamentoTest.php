<?php

use App\Livewire\InstrumentoForm;
use App\Livewire\InstrumentosOrdenamento;
use App\Livewire\InstrumentosTipo;
use App\Models\CategoriaInstrumento;
use App\Models\Instrumento;
use App\Models\Municipio;
use App\Models\Permissao;
use App\Models\Provincia;
use App\Models\Role;
use App\Models\TipoInstrumento;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->role = Role::firstOrCreate(
        ['nome' => Role::SUPER_ADMIN_NACIONAL],
        [
            'descricao' => 'Super administrador nacional',
            'grupo' => 'Nacional',
            'nivel' => Role::NIVEL_NACIONAL,
        ]
    );

    // Garantir permissões necessárias
    $permissoes = ['instrumentos.ver', 'instrumentos.criar', 'instrumentos.editar_municipio', 'instrumentos.eliminar', 'nodes.acessar'];
    foreach ($permissoes as $p) {
        $perm = Permissao::firstOrCreate(['nome' => $p], ['descricao' => $p, 'grupo' => 'Instrumentos']);
        $this->role->darPermissao($perm);
    }

    $this->provincia = Provincia::firstOrCreate(
        ['codigo' => 'HUI'],
        ['nome' => 'Huíla', 'activo' => true]
    );

    $this->municipio = Municipio::firstOrCreate(
        ['slug' => 'lubango'],
        ['nome' => 'Lubango', 'codigo' => 'LUB', 'provincia_id' => $this->provincia->id, 'activo' => true]
    );

    $this->user = User::factory()->create([
        'activo' => true,
        'email_verified_at' => now(),
        'provincia_id' => $this->provincia->id,
        'municipio_id' => $this->municipio->id,
    ]);
    $this->user->roles()->attach($this->role);

    $this->categoria = CategoriaInstrumento::firstOrCreate(
        ['slug' => 'ordenamento-territorio'],
        ['nome' => 'Instrumentos de Ordenamento do Território', 'ordem' => 1]
    );

    $this->tipoPmot = TipoInstrumento::firstOrCreate(
        ['slug' => 'pmot', 'categoria_id' => $this->categoria->id],
        ['nome' => 'Plano Municipal de Ordenamento do Território', 'ordem' => 1]
    );
});

test('utilizador autenticado pode visualizar página de instrumentos de ordenamento', function () {
    Livewire::test(InstrumentosOrdenamento::class, ['municipio' => $this->municipio])
        ->assertSee('Plano Municipal de Ordenamento do Território')
        ->assertSee('PMOT');
});

test('utilizador autenticado pode visualizar lista de PMOTs', function () {
    Livewire::actingAs($this->user)
        ->test(InstrumentosTipo::class, ['municipio' => $this->municipio, 'tipoSlug' => 'pmot'])
        ->assertSee('Plano Municipal de Ordenamento do Território')
        ->assertSee('Novo PMOT');
});

test('Livewire InstrumentoForm inicializa com ano corrente preenchido automaticamente', function () {
    Livewire::actingAs($this->user)
        ->test(InstrumentoForm::class, [
            'municipio' => $this->municipio,
            'tipoSlug' => 'pmot',
        ])
        ->assertSet('ano', (string) date('Y'))
        ->assertSee('Ano Atual (' . date('Y') . ')')
        ->call('definirAno', '2025')
        ->assertSet('ano', '2025')
        ->call('preencherAnoAtual')
        ->assertSet('ano', (string) date('Y'));
});

test('Livewire InstrumentoForm grava novo PMOT com ano e dados corretos', function () {
    Livewire::actingAs($this->user)
        ->test(InstrumentoForm::class, [
            'municipio' => $this->municipio,
            'tipoSlug' => 'pmot',
        ])
        ->set('titulo', 'PMOT Lubango 2025-2030')
        ->set('ano', '2025')
        ->set('estado', 'aprovado')
        ->set('descricao', 'Plano Municipal de Ordenamento do Território aprovado em sessão municipal.')
        ->call('guardar')
        ->assertRedirect(route('municipio.instrumentos.tipo', [$this->municipio, 'pmot']));

    expect(Instrumento::query()->where('titulo', 'PMOT Lubango 2025-2030')->exists())->toBeTrue();

    $instrumento = Instrumento::query()->where('titulo', 'PMOT Lubango 2025-2030')->first();
    expect($instrumento->ano)->toBe('2025');
    expect($instrumento->estado)->toBe('aprovado');
    expect($instrumento->municipio_id)->toBe($this->municipio->id);
    expect($instrumento->tipo_instrumento_id)->toBe($this->tipoPmot->id);
});

test('página de detalhe do PMOT exibe informações técnicas e badge de ano', function () {
    $instrumento = Instrumento::create([
        'municipio_id' => $this->municipio->id,
        'tipo_instrumento_id' => $this->tipoPmot->id,
        'titulo' => 'PMOT Piloto Lubango',
        'ano' => '2026',
        'estado' => 'aprovado',
        'descricao' => 'Descrição técnica de teste para o PMOT.',
        'criado_por' => $this->user->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\InstrumentoDetalhe::class, ['instrumento' => $instrumento])
        ->assertSee('PMOT Piloto Lubango')
        ->assertSee('2026')
        ->assertSee('Aprovado');
});

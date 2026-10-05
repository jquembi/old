<?php

use App\Livewire\CartaMunicipalForm;
use App\Livewire\CartasMunicipais;
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
    $permissoes = ['cartas_municipais.ver', 'cartas_municipais.criar', 'cartas_municipais.editar', 'nodes.acessar'];
    foreach ($permissoes as $p) {
        $perm = Permissao::firstOrCreate(['nome' => $p], ['descricao' => $p, 'grupo' => 'Cartografia']);
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
        ['slug' => 'cartas-municipais'],
        ['nome' => 'Cartas Municipais', 'ordem' => 3]
    );

    $this->tipoCarta = TipoInstrumento::firstOrCreate(
        ['slug' => 'carta-municipal', 'categoria_id' => $this->categoria->id],
        ['nome' => 'Carta Municipal', 'ordem' => 1]
    );
});

test('utilizador autenticado pode visualizar página de cartas municipais', function () {
    Livewire::actingAs($this->user)
        ->test(CartasMunicipais::class, ['municipio' => $this->municipio])
        ->assertSee('Cartas Municipais')
        ->assertSee('Nova Carta (OCR)');
});

test('Livewire CartaMunicipalForm inicializa com ano corrente preenchido automaticamente', function () {
    Livewire::actingAs($this->user)
        ->test(CartaMunicipalForm::class, [
            'municipio' => $this->municipio,
        ])
        ->assertSet('ano', (string) date('Y'))
        ->assertSee('Ano Atual (' . date('Y') . ')')
        ->call('definirAno', '2025')
        ->assertSet('ano', '2025')
        ->call('preencherAnoAtual')
        ->assertSet('ano', (string) date('Y'));
});

test('Livewire CartaMunicipalForm grava nova carta com ano e dados corretos', function () {
    Livewire::actingAs($this->user)
        ->test(CartaMunicipalForm::class, [
            'municipio' => $this->municipio,
        ])
        ->set('titulo', 'Carta Topográfica Lubango 1:25000')
        ->set('ano', '2025')
        ->set('estado', 'aprovado')
        ->set('descricao', 'Carta topográfica com curvas de nível e rede viária municipal.')
        ->call('guardar')
        ->assertRedirect(route('municipio.cartas', $this->municipio));

    expect(Instrumento::query()->where('titulo', 'Carta Topográfica Lubango 1:25000')->exists())->toBeTrue();

    $carta = Instrumento::query()->where('titulo', 'Carta Topográfica Lubango 1:25000')->first();
    expect($carta->ano)->toBe('2025');
    expect($carta->estado)->toBe('aprovado');
    expect($carta->municipio_id)->toBe($this->municipio->id);
    expect($carta->tipo_instrumento_id)->toBe($this->tipoCarta->id);
});

test('alterna entre visualização de grade e tabela de cartas', function () {
    Livewire::actingAs($this->user)
        ->test(CartasMunicipais::class, ['municipio' => $this->municipio])
        ->assertSet('visualizacao', 'grade')
        ->call('setVisualizacao', 'tabela')
        ->assertSet('visualizacao', 'tabela');
});

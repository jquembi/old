<?php

use App\Livewire\GestaoOutrosProjectos;
use App\Livewire\GestaoProjectoSona;
use App\Livewire\OutroProjetoForm;
use App\Livewire\ProjetoSonaForm;
use App\Livewire\ProjectosRequalificacao;
use App\Models\Municipio;
use App\Models\OutroProjecto;
use App\Models\Permissao;
use App\Models\ProjectoSona;
use App\Models\Provincia;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    $permissoes = [
        'requalificacao.ver',
        'sona.ver',
        'sona.criar',
        'sona.editar',
        'sona.editar_municipio',
        'sona.eliminar',
        'outros_projectos.ver',
        'outros_projectos.criar',
        'outros_projectos.editar',
        'outros_projectos.eliminar',
        'nodes.acessar',
    ];

    foreach ($permissoes as $p) {
        $perm = Permissao::firstOrCreate(['nome' => $p], ['descricao' => $p, 'grupo' => 'Requalificação']);
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
});

test('hub requalificacao renderiza com estatisticas e badges', function () {
    ProjectoSona::create([
        'municipio_id' => $this->municipio->id,
        'titulo' => 'Projecto SONA Teste',
        'tipo' => 'requalificacao',
        'fase' => 'Fase 1',
        'estado' => 'em_curso',
        'criado_por' => $this->user->id,
    ]);

    OutroProjecto::create([
        'municipio_id' => $this->municipio->id,
        'titulo' => 'Pavimentação de Acesso',
        'tipo' => 'infraestrutura',
        'estado' => 'em_curso',
        'orcamento' => 15000000.00,
        'criado_por' => $this->user->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(ProjectosRequalificacao::class, ['municipio' => $this->municipio])
        ->assertOk()
        ->assertSee('Projectos de Requalificação')
        ->assertSee('Projecto SONA')
        ->assertSee('Outros Projectos')
        ->assertSee('Total de Projectos');
});

test('gestao projecto sona lista, filtra e elimina registos', function () {
    $sona = ProjectoSona::create([
        'municipio_id' => $this->municipio->id,
        'titulo' => 'Requalificação da Zona Histórica',
        'tipo' => 'requalificacao',
        'fase' => 'Fase 2',
        'estado' => 'em_curso',
        'criado_por' => $this->user->id,
    ]);

    $outroMunicipio = Municipio::firstOrCreate(
        ['slug' => 'humpata'],
        ['nome' => 'Humpata', 'codigo' => 'HUM', 'provincia_id' => $this->provincia->id, 'activo' => true]
    );

    $sonaOutro = ProjectoSona::create([
        'municipio_id' => $outroMunicipio->id,
        'titulo' => 'SONA Outro Município',
        'tipo' => 'avancos_obras',
        'fase' => 'Fase 1',
        'estado' => 'em_curso',
        'criado_por' => $this->user->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(GestaoProjectoSona::class, ['municipio' => $this->municipio])
        ->assertOk()
        ->assertSee('Requalificação da Zona Histórica')
        ->assertDontSee('SONA Outro Município')
        ->set('filtroTipo', 'avancos_obras')
        ->assertDontSee('Requalificação da Zona Histórica')
        ->set('filtroTipo', '')
        ->assertSee('Requalificação da Zona Histórica')
        ->call('eliminar', $sona->id);

    expect(ProjectoSona::find($sona->id))->toBeNull();
});

test('projeto sona form cria e guarda com anexos', function () {
    Storage::fake('public');

    $imagem = UploadedFile::fake()->image('foto_obra.jpg', 600, 400);

    Livewire::actingAs($this->user)
        ->test(ProjetoSonaForm::class, ['municipio' => $this->municipio])
        ->set('titulo', 'Requalificação da Avenida Principal')
        ->set('tipo', 'avancos_obras')
        ->set('fase', 'Fase 1')
        ->set('estado', 'em_curso')
        ->set('descricao', 'Obras de requalificação urbana')
        ->set('ficheiros', [$imagem])
        ->call('guardar')
        ->assertRedirect(route('municipio.requalificacao.sona', $this->municipio));

    $projecto = ProjectoSona::where('titulo', 'Requalificação da Avenida Principal')->first();
    expect($projecto)->not->toBeNull()
        ->and($projecto->municipio_id)->toBe($this->municipio->id)
        ->and($projecto->tipo)->toBe('avancos_obras')
        ->and($projecto->fase)->toBe('Fase 1');
});

test('gestao outros projectos lista e elimina', function () {
    $outro = OutroProjecto::create([
        'municipio_id' => $this->municipio->id,
        'titulo' => 'Construção do Centro Social',
        'tipo' => 'social',
        'estado' => 'planificado',
        'entidade_responsavel' => 'Gabinete Provincial',
        'orcamento' => 50000000.00,
        'criado_por' => $this->user->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(GestaoOutrosProjectos::class, ['municipio' => $this->municipio])
        ->assertOk()
        ->assertSee('Construção do Centro Social')
        ->assertSee('Social')
        ->assertSee('Planificado')
        ->call('eliminar', $outro->id);

    expect(OutroProjecto::find($outro->id))->toBeNull();
});

test('outro projeto form cria com orcamento e atalhos de data', function () {
    Livewire::actingAs($this->user)
        ->test(OutroProjetoForm::class, ['municipio' => $this->municipio])
        ->set('titulo', 'Drenagem de Águas Pluviais')
        ->set('tipo', 'infraestrutura')
        ->set('estado', 'em_curso')
        ->set('orcamento', '12000000.50')
        ->set('entidade_responsavel', 'Empreiteiro Local')
        ->call('definirAtalhoData', 'inicio_hoje')
        ->call('definirAtalhoData', 'fim_1_ano')
        ->call('guardar')
        ->assertRedirect(route('municipio.requalificacao.outros', $this->municipio));

    $projecto = OutroProjecto::where('titulo', 'Drenagem de Águas Pluviais')->first();
    expect($projecto)->not->toBeNull()
        ->and($projecto->municipio_id)->toBe($this->municipio->id)
        ->and($projecto->tipo)->toBe('infraestrutura')
        ->and((float) $projecto->orcamento)->toBe(12000000.50);
});

<?php

use App\Http\Livewire\Nacional\AuditoriaNacional;
use App\Http\Livewire\Nacional\Indicadores;
use App\Http\Livewire\Nacional\NodeDetalhe;
use App\Http\Livewire\Nacional\Parametros;
use App\Http\Livewire\Nacional\Relatorios;
use App\Http\Livewire\Nacional\SupervisaoNodes;
use App\Models\NationalAuditLog;
use App\Models\ParametroNacional;
use App\Models\Provincia;
use App\Models\SyncLog;
use App\Models\Tenant;
use Livewire\Livewire;

function superAdminNacional(): App\Models\User
{
    $role = App\Models\Role::firstOrCreate(
        ['nome' => App\Models\Role::SUPER_ADMIN_NACIONAL],
        ['descricao' => 'Super Administrador Nacional', 'grupo' => 'INOTU Nacional', 'nivel' => App\Models\Role::NIVEL_NACIONAL],
    );
    $user = App\Models\User::factory()->create(['activo' => true]);
    $user->roles()->attach($role);

    return $user->fresh('roles');
}

function tecnicoComum(): App\Models\User
{
    $role = App\Models\Role::firstOrCreate(
        ['nome' => App\Models\Role::TECNICO_ORDENAMENTO],
        ['descricao' => 'Técnico', 'grupo' => 'Administração Municipal', 'nivel' => App\Models\Role::NIVEL_TECNICO],
    );
    $user = App\Models\User::factory()->create(['activo' => true]);
    $user->roles()->attach($role);

    return $user->fresh('roles');
}

beforeEach(function () {
    config(['tenancy.deployment_mode' => 'central']);
    $this->admin = superAdminNacional();
    Tenant::firstOrCreate(['code' => 'CTR'], ['name' => 'Núcleo Central', 'type' => 'central', 'database_name' => 'central', 'is_active' => true, 'sync_status' => 'online']);
    $this->node = Tenant::firstOrCreate(['code' => 'HUI'], ['name' => 'Huíla', 'type' => 'provincial', 'database_name' => 'hui', 'is_active' => true, 'sync_status' => 'online']);
    Provincia::firstOrCreate(['codigo' => 'HUI'], ['nome' => 'Huíla', 'activo' => true]);
});

it('mostra as páginas nacionais sem o placeholder antigo', function () {
    foreach ([
        'nacional.indicadores', 'nacional.indicadores.processos', 'nacional.indicadores.terras',
        'nacional.indicadores.financeiro', 'nacional.nodes', 'nacional.relatorios', 'nacional.parametros',
        'nacional.regras-globais', 'nacional.auditoria', 'nacional.auditoria.logs',
    ] as $rota) {
        $resposta = $this->actingAs($this->admin)->get(route($rota));
        expect($resposta->status())->toBe(200, $rota.' -> '.substr(strip_tags((string) $resposta->getContent()), 0, 300));
        $resposta
            ->assertOk()
            ->assertDontSee('Módulo disponível');
    }

    $this->actingAs($this->admin)->get(route('nacional.node.detalhe', $this->node->id))->assertOk()->assertSee('Huíla');
});

it('calcula indicadores por província', function () {
    Livewire::actingAs($this->admin)->test(Indicadores::class, ['categoria' => 'financeiro'])
        ->assertSee('Arrecadação RUPE')
        ->assertSee('Huíla');
});

it('exporta o relatório em CSV e audita a exportação', function () {
    Livewire::actingAs($this->admin)->test(Relatorios::class)
        ->set('categoria', 'terras')
        ->call('exportar')
        ->assertFileDownloaded();

    expect(NationalAuditLog::where('action', 'export')->count())->toBe(1);
});

it('rejeita intervalo de datas inválido no relatório', function () {
    Livewire::actingAs($this->admin)->test(Relatorios::class)
        ->set('de', '2026-05-10')
        ->set('ate', '2026-05-01')
        ->assertHasErrors(['ate']);
});

it('ativa, desativa e pede sincronização de nodes', function () {
    Livewire::actingAs($this->admin)->test(SupervisaoNodes::class)
        ->call('alternarAtivo', $this->node->id);
    expect($this->node->fresh()->is_active)->toBeFalse();

    Livewire::actingAs($this->admin)->test(SupervisaoNodes::class)
        ->call('alternarAtivo', $this->node->id);
    expect($this->node->fresh()->is_active)->toBeTrue();

    Livewire::actingAs($this->admin)->test(NodeDetalhe::class, ['tenant' => $this->node->id])
        ->call('sincronizar')
        ->call('sincronizar');
    expect(SyncLog::pending()->where('source_tenant_id', $this->node->id)->count())->toBe(1);
});

it('não desativa o núcleo central', function () {
    $central = Tenant::central()->first();
    Livewire::actingAs($this->admin)->test(SupervisaoNodes::class)->call('alternarAtivo', $central->id);
    expect($central->fresh()->is_active)->toBeTrue();
});

it('guarda parâmetros com validação e auditoria', function () {
    $componente = Livewire::actingAs($this->admin)->test(Parametros::class);
    $parametro = ParametroNacional::where('chave', 'sync.intervalo_minutos')->first();

    $componente->set("valores.{$parametro->id}", 'abc')->call('guardar', $parametro->id)->assertHasErrors("valores.{$parametro->id}");

    $componente->set("valores.{$parametro->id}", '30')->call('guardar', $parametro->id);
    expect(ParametroNacional::obter('sync.intervalo_minutos'))->toBe(30);
    expect(NationalAuditLog::where('entity_type', 'parametro_nacional')->count())->toBe(1);

    $componente->call('repor', $parametro->id);
    expect(ParametroNacional::obter('sync.intervalo_minutos'))->toBe(15);
});

it('alterna regras globais', function () {
    $regra = null;
    $c = Livewire::actingAs($this->admin)->test(Parametros::class, ['modo' => 'regras']);
    $regra = ParametroNacional::where('chave', 'regras.aprovacao_dupla')->first();
    $c->call('alternar', $regra->id);
    expect(ParametroNacional::obter('regras.aprovacao_dupla'))->toBeFalse();
});

it('filtra eventos de auditoria por ação', function () {
    NationalAuditLog::create(['action' => 'login', 'description' => 'Entrada de teste']);
    NationalAuditLog::create(['action' => 'export', 'description' => 'Exportação de teste']);

    Livewire::actingAs($this->admin)->test(AuditoriaNacional::class)
        ->set('acao', 'login')
        ->assertSee('Entrada de teste')
        ->assertDontSee('Exportação de teste');
});

it('impede utilizadores comuns de aceder ao núcleo', function () {
    $this->actingAs(tecnicoComum())->get(route('nacional.indicadores'))->assertForbidden();
});

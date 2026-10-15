<?php

use App\Livewire\AmbienteTrabalho;
use App\Livewire\CartasMunicipais;
use App\Livewire\PainelSistema;
use App\Livewire\CroquisElaborados;
use App\Livewire\Dashboard;
use App\Livewire\AuditoriaDetalhe;
use App\Livewire\GestaoAuditoria;
use App\Livewire\GestaoPareceres;
use App\Livewire\GestaoOutrosProjectos;
use App\Livewire\GestaoProjectoSona;
use App\Livewire\GestaoUsuarios;
use App\Livewire\GestaoPermissoes;
use App\Livewire\ErroSistemaDetalhe;
use App\Livewire\ErrosSistema;
use App\Livewire\InstrumentoDetalhe;
use App\Livewire\IdTerrenoDetalhe;
use App\Livewire\IdsTerreno;
use App\Livewire\InstrumentosOrdenamento;
use App\Livewire\InstrumentosTipo;
use App\Livewire\InstrumentosUrbanisticos;
use App\Livewire\ListaMunicipios;
use App\Livewire\LogAcessoDetalhe;
use App\Livewire\LixeiraSistema;
use App\Livewire\LoteamentoDetalhe;
use App\Livewire\LoteamentosMunicipio;
use App\Livewire\MemoriaDescritivaDetalhe;
use App\Livewire\MunicipioShow;
use App\Livewire\Notificacoes;
use App\Livewire\OperacoesUrbanisticas;
use App\Livewire\ParecerDetalhe;
use App\Livewire\ProjectosRequalificacao;
use App\Http\Controllers\DocumentoAutenticidadeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ServirDocumentoController;
use App\Livewire\TerrenosOciosos;
use App\Livewire\AmbienteTrabalhoForm;
use App\Livewire\CartaMunicipalForm;
use App\Livewire\CroquiForm;
use App\Livewire\InstrumentoForm;
use App\Livewire\LoteamentoForm;
use App\Livewire\OutroProjetoForm;
use App\Livewire\ParecerForm;
use App\Livewire\ProjetoSonaForm;
use App\Livewire\TerrenoForm;
use App\Livewire\UsuarioForm;
use App\Livewire\RegraForm;
use App\Livewire\CroquiDetalhe;
use App\Livewire\GerirPagamentos;
use App\Livewire\PagamentoDetalhe;
use App\Http\Livewire\Erp\Financeiro\ContasGerais;
use App\Http\Livewire\Erp\Financeiro\Lancamentos;
use App\Http\Livewire\Erp\Financeiro\Pagamentos;
use App\Http\Livewire\Erp\RecursosHumanos\Funcionarios;
use App\Http\Livewire\Erp\RecursosHumanos\Departamentos;
use App\Http\Livewire\Erp\RecursosHumanos\Cargos;
use App\Http\Livewire\Erp\ComprasContratos\Fornecedores;
use App\Http\Livewire\Erp\ComprasContratos\RequisicoesCompra;
use App\Http\Livewire\Erp\ComprasContratos\Contratos;
use App\Http\Livewire\Erp\Patrimonio\Patrimonios;
use App\Http\Livewire\Erp\Patrimonio\CategoriasPatrimonio;
use App\Http\Livewire\Erp\Patrimonio\Localizacoes;
use App\Services\DocumentoAutenticidadeService;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('desktop', 'pages.download-desktop')->name('desktop.download');
Route::view('portal', 'portal.home')->name('portal.home');
Route::view('portal/consulta', 'consulta.index')->name('portal.consulta');
Route::view('portal/servicos-publicos', 'portal.servicos-publicos')->name('portal.servicos-publicos');
Route::redirect('consulta', 'portal/consulta')->name('consulta.publica');

// ─── Sessão desktop: converte token Sanctum em sessão web ───
Route::get('auth/desktop-session', function () {
    $user = request()->user('sanctum');

    if (! $user) {
        return redirect('/login');
    }

    \Illuminate\Support\Facades\Auth::login($user, remember: true);
    request()->session()->regenerate();

    return redirect('/dashboard');
})->middleware(['web', 'auth:sanctum'])->name('auth.desktop-session');

// ─── Sincronização desktop: gera token Sanctum para utilizador web autenticado ───
Route::post('auth/desktop-sync', function () {
    $user = \Illuminate\Support\Facades\Auth::user();

    if (! $user) {
        return response()->json(['success' => false, 'message' => 'Não autenticado.'], 401);
    }

    // Revogar tokens desktop antigos para evitar acumulação
    $user->tokens()->where('name', 'gterras-desktop')->delete();

    $token = $user->createToken('gterras-desktop')->plainTextToken;

    $user->load(['roles.permissoes', 'provincia', 'municipio']);
    $permissoes = $user->todasPermissoes();

    return response()->json([
        'success' => true,
        'data' => [
            'token' => $token,
            'user' => [
                'id'        => $user->id,
                'nome'      => $user->name,
                'email'     => $user->email,
                'activo'    => $user->activo,
                'roles'     => $user->roles->map(fn ($role) => [
                    'id'        => $role->id,
                    'nome'      => $role->nome,
                    'descricao' => $role->descricao,
                    'nivel'     => $role->nivel,
                    'grupo'     => $role->grupo,
                ])->values(),
                'provincia' => $user->provincia ? [
                    'id'   => $user->provincia->id,
                    'nome' => $user->provincia->nome,
                ] : null,
                'municipio' => $user->municipio ? [
                    'id'   => $user->municipio->id,
                    'nome' => $user->municipio->nome,
                    'slug' => $user->municipio->slug,
                ] : null,
            ],
            'permissoes' => $permissoes,
        ],
    ]);
})->middleware(['web', 'auth'])->name('auth.desktop-sync');

// ─── Servir documentos de forma segura (com permissões + auditoria) ──────
//
// A rota legada `storage.serve` mantém-se como alias para retrocompatibilidade,
// mas agora delega no ServirDocumentoController que aplica:
//   • Verificação de permissão (documentos.ver / documentos.download)
//   • Rate limiting (60 views / 20 downloads por minuto)
//   • Registo de auditoria no LogAcesso
//   • Headers HTTP de segurança (Cache-Control, X-Content-Type-Options…)
//   • QR stamp de autenticidade em PDFs/imagens
//   • Bloqueio de homologação para croquis

// Download forçado (attachment) — deve ficar ANTES da rota de visualização
// para evitar que `ficheiros/{path}.*` capture o sufixo /download
Route::get(
    'ficheiros/{path}/download',
    [ServirDocumentoController::class, 'download']
)->where('path', '.*')
 ->middleware(['auth', 'permissao:documentos.download'])
 ->name('storage.download');

// Visualizar inline (sem forçar download)
Route::get(
    'ficheiros/{path}',
    [ServirDocumentoController::class, 'visualizar']
)->where('path', '.*')
 ->middleware(['auth'])
 ->name('storage.serve');     // mantém nome legado — todos os `route('storage.serve', $path)` continuam a funcionar

// ─── Autenticidade de Documentos (verificação pública) ─────────────────────
Route::get('documentos/verificar/{token}', [DocumentoAutenticidadeController::class, 'verificar'])
    ->name('documento.verificar');
Route::get('documentos/api/verificar/{token}', [DocumentoAutenticidadeController::class, 'verificarApi'])
    ->name('documento.verificar.api');
Route::get('documentos/baixar/{token}', [DocumentoAutenticidadeController::class, 'baixar'])
    ->middleware(['auth:web,sanctum'])
    ->name('documento.baixar');

// ─── Painel do Sistema (acesso restrito INOTU) ───────────
Route::get('sistema', PainelSistema::class)->middleware(['auth', 'verified', 'nivel:1'])->name('painel.sistema');

Route::middleware(['auth', 'verified', 'set.tenant:validate', 'geo.context', 'geo.angola', 'tenant.type:provincial', 'perspetiva', 'log.acesso'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    Route::get('notificacoes', Notificacoes::class)
        ->name('notificacoes.index');

    // ─── Municípios ─────────────────────────────────────
    Route::get('municipios', ListaMunicipios::class)
        ->name('municipios.index');

    // ─── Município ──────────────────────────────────────
    Route::get('municipio/{municipio}', MunicipioShow::class)
        ->middleware('municipio.acesso')
        ->name('municipio.show');

    // Instrumentos de Ordenamento do Território
    Route::get('municipio/{municipio}/instrumentos-ordenamento', InstrumentosOrdenamento::class)
        ->middleware(['municipio.acesso', 'permissao:instrumentos.ver'])
        ->name('municipio.instrumentos-ordenamento');

    // Instrumentos Urbanísticos
    Route::get('municipio/{municipio}/instrumentos-urbanisticos', InstrumentosUrbanisticos::class)
        ->middleware(['municipio.acesso', 'permissao:instrumentos.ver'])
        ->name('municipio.instrumentos-urbanisticos');

    // Cartas Municipais
    Route::get('municipio/{municipio}/cartas', CartasMunicipais::class)
        ->middleware(['municipio.acesso', 'permissao:cartas_municipais.ver'])
        ->name('municipio.cartas');

    // CRUD genérico por tipo de instrumento
    Route::get('municipio/{municipio}/instrumentos/{tipoSlug}', InstrumentosTipo::class)
        ->middleware(['municipio.acesso', 'permissao:instrumentos.ver'])
        ->name('municipio.instrumentos.tipo');

    // Loteamentos por município
    Route::get('municipio/{municipio}/loteamentos', LoteamentosMunicipio::class)
        ->middleware(['municipio.acesso', 'permissao:loteamentos.ver'])
        ->name('municipio.loteamentos');

    // Operações Urbanísticas (página intermédia com 3 cards)
    Route::get('municipio/{municipio}/operacoes', OperacoesUrbanisticas::class)
        ->middleware(['municipio.acesso', 'permissao:operacoes.ver'])
        ->name('municipio.operacoes');

    Route::get('municipio/{municipio}/operacoes/croquis-elaborados', CroquisElaborados::class)
        ->middleware(['municipio.acesso', 'permissao:operacoes.ver'])
        ->name('municipio.operacoes.croquis-elaborados');

    Route::get('municipio/{municipio}/operacoes/terrenos-ociosos', TerrenosOciosos::class)
        ->middleware(['municipio.acesso', 'permissao:operacoes.ver'])
        ->name('municipio.operacoes.terrenos-ociosos');

    Route::get('municipio/{municipio}/operacoes/ids-terreno', IdsTerreno::class)
        ->middleware(['municipio.acesso', 'permissao:operacoes.ver'])
        ->name('municipio.operacoes.ids-terreno');

    Route::get('municipio/{municipio}/operacoes/ids-terreno/{croqui}', IdTerrenoDetalhe::class)
        ->middleware(['municipio.acesso', 'permissao:operacoes.ver'])
        ->name('municipio.operacoes.ids-terreno.detalhe');

    Route::get('municipio/{municipio}/operacoes/ids-terreno/{croqui}/memoria', MemoriaDescritivaDetalhe::class)
        ->middleware(['municipio.acesso', 'permissao:operacoes.ver', 'permissao:documentos.ver'])
        ->name('municipio.operacoes.ids-terreno.memoria');

    Route::get('municipio/{municipio}/operacoes/ambiente-trabalho', AmbienteTrabalho::class)
        ->middleware(['municipio.acesso', 'permissao:operacoes.ver'])
        ->name('municipio.operacoes.ambiente-trabalho');

    Route::get('municipio/{municipio}/operacoes/pareceres', GestaoPareceres::class)
        ->middleware(['municipio.acesso', 'permissao:pareceres.ver'])
        ->name('municipio.operacoes.pareceres');

    // Projectos de Requalificação
    Route::get('municipio/{municipio}/requalificacao', ProjectosRequalificacao::class)
        ->middleware(['municipio.acesso', 'permissao:requalificacao.ver'])
        ->name('municipio.requalificacao');

    Route::get('municipio/{municipio}/requalificacao/sona', GestaoProjectoSona::class)
        ->middleware(['municipio.acesso', 'permissao:sona.ver'])
        ->name('municipio.requalificacao.sona');

    Route::get('municipio/{municipio}/requalificacao/outros', GestaoOutrosProjectos::class)
        ->middleware(['municipio.acesso', 'permissao:outros_projectos.ver'])
        ->name('municipio.requalificacao.outros');

    // ─── Formulários de criação/edição (páginas dedicadas) ─────────

    // Croquis Elaborados
    Route::get('municipio/{municipio}/operacoes/croquis-elaborados/criar', CroquiForm::class)
        ->middleware(['municipio.acesso', 'permissao:operacoes.criar'])
        ->name('municipio.operacoes.croqui.criar');
    Route::get('municipio/{municipio}/operacoes/croquis-elaborados/{croqui}/editar', CroquiForm::class)
        ->middleware(['municipio.acesso', 'permissao:operacoes.editar'])
        ->name('municipio.operacoes.croqui.editar');

    // Terrenos Ociosos
    Route::get('municipio/{municipio}/operacoes/terrenos-ociosos/criar', TerrenoForm::class)
        ->middleware(['municipio.acesso', 'permissao:operacoes.criar'])
        ->name('municipio.operacoes.terreno.criar');
    Route::get('municipio/{municipio}/operacoes/terrenos-ociosos/{croqui}/editar', TerrenoForm::class)
        ->middleware(['municipio.acesso', 'permissao:operacoes.editar'])
        ->name('municipio.operacoes.terreno.editar');

    // Pareceres INOTU
    Route::get('municipio/{municipio}/operacoes/pareceres/criar', ParecerForm::class)
        ->middleware(['municipio.acesso', 'permissao:pareceres.criar'])
        ->name('municipio.operacoes.parecer.criar');
    Route::get('municipio/{municipio}/operacoes/pareceres/{parecer}/editar', ParecerForm::class)
        ->middleware(['municipio.acesso', 'permissao:pareceres.editar'])
        ->name('municipio.operacoes.parecer.editar');

    // Loteamentos
    Route::get('municipio/{municipio}/loteamentos/criar', LoteamentoForm::class)
        ->middleware(['municipio.acesso', 'permissao:loteamentos.criar'])
        ->name('municipio.loteamento.criar');
    Route::get('municipio/{municipio}/loteamentos/{loteamento}/editar', LoteamentoForm::class)
        ->middleware(['municipio.acesso', 'permissao:loteamentos.editar'])
        ->name('municipio.loteamento.editar');

    // Projectos SONA
    Route::get('municipio/{municipio}/requalificacao/sona/criar', ProjetoSonaForm::class)
        ->middleware(['municipio.acesso', 'permissao:sona.criar'])
        ->name('municipio.requalificacao.sona.criar');
    Route::get('municipio/{municipio}/requalificacao/sona/{projecto}/editar', ProjetoSonaForm::class)
        ->middleware(['municipio.acesso', 'permissao:sona.editar_municipio'])
        ->name('municipio.requalificacao.sona.editar');

    // Outros Projectos
    Route::get('municipio/{municipio}/requalificacao/outros/criar', OutroProjetoForm::class)
        ->middleware(['municipio.acesso', 'permissao:outros_projectos.criar'])
        ->name('municipio.requalificacao.outros.criar');
    Route::get('municipio/{municipio}/requalificacao/outros/{projecto}/editar', OutroProjetoForm::class)
        ->middleware(['municipio.acesso', 'permissao:outros_projectos.editar'])
        ->name('municipio.requalificacao.outros.editar');

    // Cartas Municipais
    Route::get('municipio/{municipio}/cartas/criar', CartaMunicipalForm::class)
        ->middleware(['municipio.acesso', 'permissao:cartas_municipais.criar'])
        ->name('municipio.cartas.criar');
    Route::get('municipio/{municipio}/cartas/{instrumento}/editar', CartaMunicipalForm::class)
        ->middleware(['municipio.acesso', 'permissao:cartas_municipais.editar'])
        ->name('municipio.cartas.editar');

    // Ambiente de Trabalho (apenas criação)
    Route::get('municipio/{municipio}/operacoes/ambiente-trabalho/enviar', AmbienteTrabalhoForm::class)
        ->middleware(['municipio.acesso', 'permissao:operacoes.criar'])
        ->name('municipio.operacoes.ambiente-trabalho.enviar');

    // Instrumentos (tipo genérico)
    Route::get('municipio/{municipio}/instrumentos/{tipoSlug}/criar', InstrumentoForm::class)
        ->middleware(['municipio.acesso', 'permissao:instrumentos.criar'])
        ->name('municipio.instrumentos.tipo.criar');
    Route::get('municipio/{municipio}/instrumentos/{tipoSlug}/{instrumento}/editar', InstrumentoForm::class)
        ->middleware(['municipio.acesso', 'permissao:instrumentos.editar_municipio'])
        ->name('municipio.instrumentos.tipo.editar');

    // ─── Gestão (restrito por nível hierárquico) ────────
    Route::get('usuarios', GestaoUsuarios::class)
        ->middleware(['nivel:1,2,3', 'permissao:usuarios.ver'])
        ->name('usuarios.index');

    Route::get('usuarios/criar', UsuarioForm::class)
        ->middleware(['nivel:1', 'permissao:usuarios.criar'])
        ->name('usuarios.criar');

    Route::get('usuarios/{usuario}/editar', UsuarioForm::class)
        ->middleware(['nivel:1', 'permissao:usuarios.editar'])
        ->name('usuarios.editar');

    Route::get('permissoes/regras/criar', RegraForm::class)
        ->middleware(['nivel:1', 'permissao:permissoes.gerir'])
        ->name('permissoes.regra.criar');

    Route::get('permissoes/regras/{role}/editar', RegraForm::class)
        ->middleware(['nivel:1', 'permissao:permissoes.gerir'])
        ->name('permissoes.regra.editar');

    Route::get('permissoes', GestaoPermissoes::class)
        ->middleware(['nivel:1', 'permissao:permissoes.gerir'])
        ->name('permissoes.index');

    Route::get('auditoria', GestaoAuditoria::class)
        ->middleware('nivel:1')
        ->name('auditoria.index');

    Route::get('lixeira', LixeiraSistema::class)
        ->middleware('nivel:1')
        ->name('lixeira.index');

    Route::get('auditoria/logs/{log}', LogAcessoDetalhe::class)
        ->middleware('nivel:1')
        ->whereNumber('log')
        ->name('auditoria.logs.show');

    Route::get('auditoria/{auditoria}', AuditoriaDetalhe::class)
        ->middleware('nivel:1')
        ->whereNumber('auditoria')
        ->name('auditoria.show');

    Route::get('erros-sistema', ErrosSistema::class)
        ->middleware('nivel:1')
        ->name('erros-sistema.index');

    Route::get('erros-sistema/{erro}', ErroSistemaDetalhe::class)
        ->middleware('nivel:1')
        ->name('erros-sistema.show');

    // ─── Detalhe de documentos (para QR code) ───────────
    Route::get('instrumento/{instrumento}', InstrumentoDetalhe::class)
        ->middleware('permissao:instrumentos.ver')
        ->name('instrumento.detalhe');

    Route::get('loteamento/{loteamento}', LoteamentoDetalhe::class)
        ->middleware('permissao:loteamentos.ver')
        ->name('loteamento.detalhe');

    Route::get('parecer/{parecer}', ParecerDetalhe::class)
        ->middleware('permissao:pareceres.ver')
        ->name('parecer.detalhe');

    Route::get('croqui/{croqui}', CroquiDetalhe::class)
        ->middleware('permissao:operacoes.ver')
        ->name('croqui.detalhe');

    // ─── Gestão de Pagamentos RUPE ──────────────────────────────

    Route::get('pagamentos', GerirPagamentos::class)
        ->middleware('permissao:pagamentos.ver')
        ->name('pagamentos.gerir');

    Route::get('pagamento/{pagamento}', PagamentoDetalhe::class)
        ->middleware('permissao:pagamentos.ver')
        ->name('pagamento.detalhe');

    Route::get('pagamento/{pagamento}/rupe', function (\App\Models\Pagamento $pagamento) {
        $user = auth()->user();
        abort_unless(
            $user && ($user->isInotu() || $user->temPermissao('pagamentos.ver') || $pagamento->municipio_id === $user->municipio_id),
            403,
        );
        abort_unless($pagamento->rupe_referencia, 404);
        return view('pagamentos.rupe-print', compact('pagamento'));
    })->name('pagamento.rupe');

    // Fatura do Projecto
    Route::get('fatura-projecto', [InvoiceController::class, 'mostrarFaturaProjeto'])
        ->name('invoice.project');

    Route::get('fatura-projecto/pdf', [InvoiceController::class, 'gerarFaturaProjeto'])
        ->name('invoice.project.pdf');

    // ─── Módulos ERP ───────────────────────────────────────
    // Módulo Financeiro
    Route::get('erp/financeiro/contas-gerais', ContasGerais::class)
        ->middleware(['nivel:1,2,3', 'permissao:financeiro.ver'])
        ->name('erp.financeiro.contas-gerais');

    Route::get('erp/financeiro/lancamentos', Lancamentos::class)
        ->middleware(['nivel:1,2,3', 'permissao:financeiro.ver'])
        ->name('erp.financeiro.lancamentos');

    Route::get('erp/financeiro/pagamentos', Pagamentos::class)
        ->middleware(['nivel:1,2,3', 'permissao:financeiro.ver'])
        ->name('erp.financeiro.pagamentos');

    // Módulo de Recursos Humanos
    Route::get('erp/rh/funcionarios', Funcionarios::class)
        ->middleware(['nivel:1,2,3', 'permissao:rh.ver'])
        ->name('erp.rh.funcionarios');

    Route::get('erp/rh/departamentos', Departamentos::class)
        ->middleware(['nivel:1,2,3', 'permissao:rh.ver'])
        ->name('erp.rh.departamentos');

    Route::get('erp/rh/cargos', Cargos::class)
        ->middleware(['nivel:1,2,3', 'permissao:rh.ver'])
        ->name('erp.rh.cargos');

    // Módulo de Compras e Contratos
    Route::get('erp/compras/fornecedores', Fornecedores::class)
        ->middleware(['nivel:1,2,3', 'permissao:compras.ver'])
        ->name('erp.compras.fornecedores');

    Route::get('erp/compras/requisicoes', RequisicoesCompra::class)
        ->middleware(['nivel:1,2,3', 'permissao:compras.ver'])
        ->name('erp.compras.requisicoes');

    Route::get('erp/compras/contratos', Contratos::class)
        ->middleware(['nivel:1,2,3', 'permissao:compras.ver'])
        ->name('erp.compras.contratos');

    // Módulo de Património
    Route::get('erp/patrimonio/bens', Patrimonios::class)
        ->middleware(['nivel:1,2,3', 'permissao:patrimonio.ver'])
        ->name('erp.patrimonio.bens');

    Route::get('erp/patrimonio/categorias', CategoriasPatrimonio::class)
        ->middleware(['nivel:1,2,3', 'permissao:patrimonio.ver'])
        ->name('erp.patrimonio.categorias');

    Route::get('erp/patrimonio/localizacoes', Localizacoes::class)
        ->middleware(['nivel:1,2,3', 'permissao:patrimonio.ver'])
        ->name('erp.patrimonio.localizacoes');
});

require __DIR__.'/settings.php';

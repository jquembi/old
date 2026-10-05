<?php

namespace App\Livewire;

use App\Models\Instrumento;
use App\Models\Loteamento;
use App\Models\Municipio;
use App\Models\OperacaoUrbanistica;
use App\Models\Parecer;
use App\Models\Provincia;
use App\Models\User;
use App\Services\PerspetivaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;

class LiveSearch extends Component
{
    private const SCOPES = [
        'all',
        'modulos',
        'municipios',
        'operacoes',
        'instrumentos',
        'loteamentos',
        'pareceres',
        'utilizadores',
    ];

    public string $query = '';
    public string $scope = 'all';

    public function updatedScope(string $scope): void
    {
        if (! in_array($scope, self::SCOPES, true)) {
            $this->scope = 'all';
        }
    }

    public function setQuery(string $valor): void
    {
        $this->query = $valor;
    }

    public function clear(): void
    {
        $this->reset('query', 'scope');
    }

    public function render()
    {
        $termo = $this->normalizarTermo($this->query);
        $resultados = $this->resultados();

        // Dados para o estado inicial (quando o modal é aberto antes de digitar)
        $atalhos = $this->atalhosSistema();
        $municipiosDestaque = $this->municipiosDestaque();
        $sugestoesPopulares = [
            'Lubango', 'Luanda', 'Huíla', 'Plano Diretor', 'SONA', 'Loteamento', 'RUPE', 'Alvará', 'Operação',
        ];

        return view('livewire.live-search', [
            'resultados' => $resultados,
            'totalResultados' => $resultados->flatten(1)->count(),
            'atalhos' => $atalhos,
            'municipiosDestaque' => $municipiosDestaque,
            'sugestoesPopulares' => $sugestoesPopulares,
            'scopesDisponiveis' => [
                'all' => 'Todos',
                'modulos' => 'Módulos',
                'municipios' => 'Municípios',
                'instrumentos' => 'Instrumentos',
                'operacoes' => 'Operações',
                'loteamentos' => 'Loteamentos',
                'pareceres' => 'Pareceres',
                'utilizadores' => 'Utilizadores',
            ],
        ]);
    }

    private function resultados(): Collection
    {
        $termo = $this->normalizarTermo($this->query);

        if (mb_strlen($termo) < 2) {
            return collect();
        }

        $resultados = collect();

        if ($this->inScope('modulos')) {
            $resultados = $resultados->merge($this->modulos($termo));
        }

        if ($this->inScope('municipios')) {
            $resultados = $resultados->merge($this->municipios($termo));
        }

        if ($this->inScope('operacoes')) {
            $resultados = $resultados->merge($this->operacoes($termo));
        }

        if ($this->inScope('instrumentos')) {
            $resultados = $resultados->merge($this->instrumentos($termo));
        }

        if ($this->inScope('loteamentos')) {
            $resultados = $resultados->merge($this->loteamentos($termo));
        }

        if ($this->inScope('pareceres')) {
            $resultados = $resultados->merge($this->pareceres($termo));
        }

        if ($this->inScope('utilizadores') && $this->podePesquisarUtilizadores()) {
            $resultados = $resultados->merge($this->utilizadores($termo));
        }

        return $resultados
            ->sortByDesc('relevancia')
            ->groupBy('categoria')
            ->map(fn (Collection $grupo) => $grupo->take(6)->values());
    }

    private function modulos(string $termo): Collection
    {
        $todos = collect($this->atalhosSistema());
        $termoLower = mb_strtolower($termo);

        return $todos->filter(function (array $item) use ($termoLower) {
            $titulo = mb_strtolower($item['titulo']);
            $descricao = mb_strtolower($item['descricao']);
            $tags = collect($item['tags'] ?? [])->map(fn ($t) => mb_strtolower($t));

            return str_contains($titulo, $termoLower)
                || str_contains($descricao, $termoLower)
                || $tags->contains(fn ($t) => str_contains($t, $termoLower));
        })->map(function (array $item) use ($termo) {
            return $this->resultado(
                categoria: 'Módulos & Atalhos',
                icone: $item['icone'],
                titulo: $item['titulo'],
                detalhe: $item['descricao'],
                url: $item['url'],
                termo: $termo,
                campoRelevancia: $item['titulo'],
                badge: 'Acesso Rápido',
                cor: $item['cor'] ?? 'red',
            );
        })->values();
    }

    private function municipios(string $termo): Collection
    {
        $query = Municipio::query()->with('provincia')->where('activo', true);
        $this->aplicarEscopoMunicipio($query, 'id');

        return $query
            ->where(fn (Builder $builder) => $builder
                ->where('nome', 'like', "%{$termo}%")
                ->orWhere('codigo', 'like', "%{$termo}%"))
            ->orderBy('nome')
            ->limit(6)
            ->get()
            ->map(fn (Municipio $municipio) => $this->resultado(
                categoria: 'Municípios',
                icone: 'map-pin',
                titulo: $municipio->nome,
                detalhe: ($municipio->provincia?->nome ?? 'Província') . ($municipio->codigo ? " • Código {$municipio->codigo}" : ''),
                url: route('municipio.show', $municipio, absolute: false),
                termo: $termo,
                campoRelevancia: $municipio->nome,
                badge: $municipio->codigo ?? 'MUNICÍPIO',
                cor: 'emerald',
            ));
    }

    private function operacoes(string $termo): Collection
    {
        $query = OperacaoUrbanistica::query()->with('municipio');
        $this->aplicarEscopoMunicipio($query);

        return $query
            ->where(fn (Builder $builder) => $builder
                ->where('titulo', 'like', "%{$termo}%")
                ->orWhere('descricao', 'like', "%{$termo}%"))
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (OperacaoUrbanistica $operacao) => $this->resultado(
                categoria: 'Operações Urbanísticas',
                icone: 'building-office-2',
                titulo: $operacao->titulo,
                detalhe: ($operacao->municipio?->nome ?? 'Território') . ($operacao->estado ? " • Estado: {$operacao->estado}" : ''),
                url: route('municipio.operacoes', $operacao->municipio, absolute: false),
                termo: $termo,
                campoRelevancia: $operacao->titulo,
                badge: 'Operação',
                cor: 'blue',
            ));
    }

    private function instrumentos(string $termo): Collection
    {
        $query = Instrumento::query()->with('municipio');
        $this->aplicarEscopoMunicipio($query);

        return $query
            ->where(fn (Builder $builder) => $builder
                ->where('titulo', 'like', "%{$termo}%")
                ->orWhere('descricao', 'like', "%{$termo}%"))
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Instrumento $instrumento) => $this->resultado(
                categoria: 'Instrumentos de Gestão',
                icone: 'document-text',
                titulo: $instrumento->titulo,
                detalhe: ($instrumento->municipio?->nome ?? 'Território') . ($instrumento->estado ? " • {$instrumento->estado}" : '') . ($instrumento->ano ? " ({$instrumento->ano})" : ''),
                url: route('instrumento.detalhe', $instrumento, absolute: false),
                termo: $termo,
                campoRelevancia: $instrumento->titulo,
                badge: 'Ordenamento',
                cor: 'amber',
            ));
    }

    private function loteamentos(string $termo): Collection
    {
        $query = Loteamento::query()->with('municipio');
        $this->aplicarEscopoMunicipio($query);

        return $query
            ->where(fn (Builder $builder) => $builder
                ->where('nome', 'like', "%{$termo}%")
                ->orWhere('localizacao', 'like', "%{$termo}%")
                ->orWhere('descricao', 'like', "%{$termo}%"))
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Loteamento $loteamento) => $this->resultado(
                categoria: 'Loteamentos',
                icone: 'squares-2x2',
                titulo: $loteamento->nome,
                detalhe: ($loteamento->municipio?->nome ?? 'Território') . ($loteamento->numero_lotes ? " • {$loteamento->numero_lotes} lotes" : '') . ($loteamento->localizacao ? " • {$loteamento->localizacao}" : ''),
                url: route('loteamento.detalhe', $loteamento, absolute: false),
                termo: $termo,
                campoRelevancia: $loteamento->nome,
                badge: 'Loteamento',
                cor: 'orange',
            ));
    }

    private function pareceres(string $termo): Collection
    {
        $query = Parecer::query()->with('municipio');
        $this->aplicarEscopoMunicipio($query);

        return $query
            ->where(fn (Builder $builder) => $builder
                ->where('titulo', 'like', "%{$termo}%")
                ->orWhere('projecto_nome', 'like', "%{$termo}%")
                ->orWhere('entidade_requerente', 'like', "%{$termo}%"))
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Parecer $parecer) => $this->resultado(
                categoria: 'Pareceres Técnicos',
                icone: 'clipboard-document-check',
                titulo: $parecer->titulo ?: ($parecer->projecto_nome ?? 'Parecer Técnico'),
                detalhe: ($parecer->municipio?->nome ?? 'Território') . ($parecer->entidade_requerente ? " • Requerente: {$parecer->entidade_requerente}" : ''),
                url: route('parecer.detalhe', $parecer, absolute: false),
                termo: $termo,
                campoRelevancia: $parecer->titulo ?: $parecer->projecto_nome,
                badge: 'Parecer',
                cor: 'purple',
            ));
    }

    private function utilizadores(string $termo): Collection
    {
        $query = User::query()->with(['municipio', 'provincia']);
        $utilizador = auth()->user();

        if ($utilizador?->isSuperAdminNacional()) {
            $perspetiva = app(PerspetivaService::class);
            if ($perspetiva->isProvincial() && $provId = $perspetiva->getProvinciaId()) {
                $query->where('provincia_id', $provId);
            }
        } elseif ($utilizador?->isMunicipal()) {
            $query->where('municipio_id', $utilizador->municipio_id);
        } elseif ($utilizador?->isProvincial()) {
            $query->where('provincia_id', $utilizador->provincia_id);
        }

        return $query
            ->where(fn (Builder $builder) => $builder
                ->where('name', 'like', "%{$termo}%")
                ->orWhere('email', 'like', "%{$termo}%"))
            ->orderBy('name')
            ->limit(6)
            ->get()
            ->map(fn (User $user) => $this->resultado(
                categoria: 'Utilizadores',
                icone: 'user-circle',
                titulo: $user->name,
                detalhe: $user->email . ($user->provincia ? " • {$user->provincia->nome}" : '') . ($user->municipio ? " ({$user->municipio->nome})" : ''),
                url: route('usuarios.index', absolute: false),
                termo: $termo,
                campoRelevancia: $user->name,
                badge: $user->roles->first()?->nome ?? 'Utilizador',
                cor: 'indigo',
            ));
    }

    private function aplicarEscopoMunicipio(Builder $query, string $coluna = 'municipio_id'): void
    {
        $utilizador = auth()->user();

        if (! $utilizador) {
            return;
        }

        if ($utilizador->isSuperAdminNacional()) {
            $perspetiva = app(PerspetivaService::class);
            if ($perspetiva->isProvincial() && $provId = $perspetiva->getProvinciaId()) {
                if ($coluna === 'id') {
                    $query->where('provincia_id', $provId);
                } else {
                    $query->whereHas('municipio', fn (Builder $municipios) => $municipios
                        ->where('provincia_id', $provId));
                }
            }
            return;
        }

        if ($utilizador->isNacional() || $utilizador->isInotu()) {
            return;
        }

        if ($utilizador->isMunicipal()) {
            $query->where($coluna, $utilizador->municipio_id);

            return;
        }

        if ($utilizador->isProvincial()) {
            if ($coluna === 'id') {
                $query->where('provincia_id', $utilizador->provincia_id);
            } else {
                $query->whereHas('municipio', fn (Builder $municipios) => $municipios
                    ->where('provincia_id', $utilizador->provincia_id));
            }
        }
    }

    private function resultado(
        string $categoria,
        string $icone,
        ?string $titulo,
        string $detalhe,
        string $url,
        string $termo,
        ?string $campoRelevancia,
        ?string $badge = null,
        string $cor = 'red',
    ): array {
        return [
            'categoria' => $categoria,
            'icone' => $icone,
            'titulo' => $titulo ?: 'Sem título',
            'detalhe' => $detalhe,
            'url' => $url,
            'relevancia' => $this->relevancia($campoRelevancia ?? '', $termo),
            'badge' => $badge,
            'cor' => $cor,
        ];
    }

    private function relevancia(string $valor, string $termo): int
    {
        $valor = mb_strtolower($valor);
        $termo = mb_strtolower($termo);

        return match (true) {
            $valor === $termo => 100,
            str_starts_with($valor, $termo) => 75,
            default => 50,
        };
    }

    private function normalizarTermo(string $termo): string
    {
        return trim(preg_replace('/\s+/', ' ', $termo) ?? '');
    }

    private function inScope(string $scope): bool
    {
        return $this->scope === 'all' || $this->scope === $scope;
    }

    private function podePesquisarUtilizadores(): bool
    {
        return auth()->user()?->temPermissao('usuarios.ver') ?? false;
    }

    public function atalhosSistema(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        $atalhos = [
            [
                'titulo' => 'Painel Principal (Dashboard)',
                'descricao' => 'Visão geral com indicadores territoriais e gráficos',
                'url' => route('dashboard'),
                'icone' => 'home',
                'cor' => 'red',
                'tags' => ['dashboard', 'inicio', 'painel', 'graficos', 'metricas', 'estatisticas'],
            ],
            [
                'titulo' => 'Território & Municípios',
                'descricao' => 'Gestão territorial, instrumentos e operações dos municípios',
                'url' => route('municipios.index'),
                'icone' => 'map',
                'cor' => 'emerald',
                'tags' => ['municipios', 'territorio', 'provincias', 'angola', 'cidades', 'huila', 'luanda'],
            ],
        ];

        if ($user->temPermissao('usuarios.ver') || $user->isSuperAdminNacional() || $user->isInotu()) {
            $atalhos[] = [
                'titulo' => 'Gestão de Utilizadores',
                'descricao' => 'Contas, atribuição de perfis e permissões de acesso',
                'url' => route('usuarios.index'),
                'icone' => 'users',
                'cor' => 'blue',
                'tags' => ['utilizadores', 'usuarios', 'contas', 'perfis', 'acessos', 'senhas', 'emails'],
            ];
        }

        if ($user->temPermissao('pagamentos.ver') || $user->isSuperAdminNacional() || $user->isInotu()) {
            $atalhos[] = [
                'titulo' => 'Gestão de Pagamentos RUPE',
                'descricao' => 'Arrecadação de emolumentos, referências RUPE e faturas',
                'url' => route('pagamentos.gerir'),
                'icone' => 'banknotes',
                'cor' => 'amber',
                'tags' => ['pagamentos', 'rupe', 'faturas', 'emolumentos', 'receitas', 'taxas', 'financas'],
            ];
        }

        if ($user->temPermissao('auditoria.ver') || $user->isSuperAdminNacional() || $user->isInotu()) {
            $atalhos[] = [
                'titulo' => 'Auditoria & Logs de Segurança',
                'descricao' => 'Rastreio de actividades, acessos e autenticidade QR',
                'url' => route('auditoria.index'),
                'icone' => 'shield-check',
                'cor' => 'purple',
                'tags' => ['auditoria', 'logs', 'seguranca', 'rastreio', 'qr', 'autenticidade', 'eventos'],
            ];
        }

        if ($user->isSuperAdminNacional() || $user->isNacional()) {
            $atalhos[] = [
                'titulo' => 'Núcleo Central Nacional',
                'descricao' => 'Supervisão de nós provinciais, sincronização e governação',
                'url' => route('nacional.dashboard'),
                'icone' => 'building-library',
                'cor' => 'red',
                'tags' => ['nacional', 'nucleo', 'governo', 'central', 'nodes', 'sincronizacao', 'supervisao'],
            ];
        }

        if ($user->temPermissao('permissoes.gerir') || $user->isSuperAdminNacional() || $user->isInotu()) {
            $atalhos[] = [
                'titulo' => 'Gestão de Permissões & Regras',
                'descricao' => 'Matriz de permissões institucionais e níveis de acesso',
                'url' => route('permissoes.index'),
                'icone' => 'lock-closed',
                'cor' => 'zinc',
                'tags' => ['permissoes', 'regras', 'perfis', 'roles', 'cargos', 'seguranca'],
            ];
        }

        if ($user->temPermissao('financeiro.ver') || $user->isSuperAdminNacional()) {
            $atalhos[] = [
                'titulo' => 'ERP Financeiro: Contas Gerais',
                'descricao' => 'Plano de contas e movimentos de tesouraria',
                'url' => route('erp.financeiro.contas-gerais'),
                'icone' => 'credit-card',
                'cor' => 'teal',
                'tags' => ['erp', 'financeiro', 'contas', 'orcamento', 'balancete', 'lancamentos'],
            ];
        }

        if ($user->temPermissao('rh.ver') || $user->isSuperAdminNacional()) {
            $atalhos[] = [
                'titulo' => 'ERP Recursos Humanos: Funcionários',
                'descricao' => 'Fichas de funcionários, departamentos e cargos',
                'url' => route('erp.rh.funcionarios'),
                'icone' => 'user-group',
                'cor' => 'indigo',
                'tags' => ['erp', 'rh', 'funcionarios', 'departamentos', 'cargos', 'recursos humanos'],
            ];
        }

        if ($user->temPermissao('patrimonio.ver') || $user->isSuperAdminNacional()) {
            $atalhos[] = [
                'titulo' => 'ERP Património: Bens & Inventário',
                'descricao' => 'Registo de bens patrimoniais e localizações físicas',
                'url' => route('erp.patrimonio.bens'),
                'icone' => 'archive-box',
                'cor' => 'orange',
                'tags' => ['erp', 'patrimonio', 'bens', 'inventario', 'equipamentos', 'imoveis'],
            ];
        }

        if ($user->temPermissao('compras.ver') || $user->isSuperAdminNacional()) {
            $atalhos[] = [
                'titulo' => 'ERP Compras: Fornecedores & Contratos',
                'descricao' => 'Gestão de fornecedores e contratos de aquisição',
                'url' => route('erp.compras.fornecedores'),
                'icone' => 'shopping-cart',
                'cor' => 'pink',
                'tags' => ['erp', 'compras', 'fornecedores', 'contratos', 'requisicoes', 'aquisicoes'],
            ];
        }

        $atalhos[] = [
            'titulo' => 'Portal Público de Serviços',
            'descricao' => 'Portal externo para consulta de autenticidade documental',
            'url' => route('portal.home'),
            'icone' => 'globe-alt',
            'cor' => 'sky',
            'tags' => ['portal', 'publico', 'cidadao', 'consulta', 'servicos', 'autenticidade'],
        ];

        return $atalhos;
    }

    private function municipiosDestaque(): Collection
    {
        $query = Municipio::query()->with('provincia')->where('activo', true);
        $this->aplicarEscopoMunicipio($query, 'id');

        return $query->orderBy('nome')->limit(6)->get();
    }
}

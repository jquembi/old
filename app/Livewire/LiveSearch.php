<?php

namespace App\Livewire;

use App\Models\Instrumento;
use App\Models\Loteamento;
use App\Models\Municipio;
use App\Models\OperacaoUrbanistica;
use App\Models\Parecer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;

class LiveSearch extends Component
{
    private const SCOPES = [
        'all',
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

    public function clear(): void
    {
        $this->reset('query', 'scope');
    }

    public function render()
    {
        $resultados = $this->resultados();

        return view('livewire.live-search', [
            'resultados' => $resultados,
            'totalResultados' => $resultados->flatten(1)->count(),
        ]);
    }

    private function resultados(): Collection
    {
        $termo = $this->normalizarTermo($this->query);

        if (mb_strlen($termo) < 2) {
            return collect();
        }

        $resultados = collect();

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
            ->map(fn (Collection $grupo) => $grupo->take(4)->values());
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
            ->limit(5)
            ->get()
            ->map(fn (Municipio $municipio) => $this->resultado(
                categoria: 'Municípios',
                icone: 'map-pin',
                titulo: $municipio->nome,
                detalhe: $municipio->provincia?->nome ?? 'Município',
                url: route('municipio.show', $municipio, absolute: false),
                termo: $termo,
                campoRelevancia: $municipio->nome,
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
            ->limit(5)
            ->get()
            ->map(fn (OperacaoUrbanistica $operacao) => $this->resultado(
                categoria: 'Operações',
                icone: 'building-office-2',
                titulo: $operacao->titulo,
                detalhe: $operacao->municipio?->nome ?? 'Operação urbanística',
                url: route('municipio.operacoes', $operacao->municipio, absolute: false),
                termo: $termo,
                campoRelevancia: $operacao->titulo,
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
            ->limit(5)
            ->get()
            ->map(fn (Instrumento $instrumento) => $this->resultado(
                categoria: 'Instrumentos',
                icone: 'document-text',
                titulo: $instrumento->titulo,
                detalhe: $instrumento->municipio?->nome ?? 'Instrumento territorial',
                url: route('instrumento.detalhe', $instrumento, absolute: false),
                termo: $termo,
                campoRelevancia: $instrumento->titulo,
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
            ->limit(5)
            ->get()
            ->map(fn (Loteamento $loteamento) => $this->resultado(
                categoria: 'Loteamentos',
                icone: 'squares-2x2',
                titulo: $loteamento->nome,
                detalhe: $loteamento->municipio?->nome ?? 'Loteamento',
                url: route('loteamento.detalhe', $loteamento, absolute: false),
                termo: $termo,
                campoRelevancia: $loteamento->nome,
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
            ->limit(5)
            ->get()
            ->map(fn (Parecer $parecer) => $this->resultado(
                categoria: 'Pareceres',
                icone: 'clipboard-document-check',
                titulo: $parecer->titulo ?: $parecer->projecto_nome,
                detalhe: $parecer->municipio?->nome ?? $parecer->servicoLabel(),
                url: route('parecer.detalhe', $parecer, absolute: false),
                termo: $termo,
                campoRelevancia: $parecer->titulo ?: $parecer->projecto_nome,
            ));
    }

    private function utilizadores(string $termo): Collection
    {
        $query = User::query()->with('municipio');
        $utilizador = auth()->user();

        if ($utilizador?->isMunicipal()) {
            $query->where('municipio_id', $utilizador->municipio_id);
        } elseif ($utilizador?->isProvincial()) {
            $query->where('provincia_id', $utilizador->provincia_id);
        }

        return $query
            ->where(fn (Builder $builder) => $builder
                ->where('name', 'like', "%{$termo}%")
                ->orWhere('email', 'like', "%{$termo}%"))
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (User $user) => $this->resultado(
                categoria: 'Utilizadores',
                icone: 'user-circle',
                titulo: $user->name,
                detalhe: $user->email,
                url: route('usuarios.index', absolute: false),
                termo: $termo,
                campoRelevancia: $user->name,
            ));
    }

    private function aplicarEscopoMunicipio(Builder $query, string $coluna = 'municipio_id'): void
    {
        $utilizador = auth()->user();

        if (! $utilizador || $utilizador->isNacional() || $utilizador->isInotu()) {
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
    ): array {
        return [
            'categoria' => $categoria,
            'icone' => $icone,
            'titulo' => $titulo ?: 'Sem título',
            'detalhe' => $detalhe,
            'url' => $url,
            'relevancia' => $this->relevancia($campoRelevancia ?? '', $termo),
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
}

<?php

namespace App\Services\Nacional;

use App\Models\Municipio;
use App\Models\Provincia;
use Carbon\CarbonInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega os dados operacionais por província para os indicadores e relatórios
 * do Núcleo Nacional. Os valores são calculados em tempo real.
 */
class IndicadoresNacionaisService
{
    public const CATEGORIAS = [
        'geral' => 'Visão geral',
        'processos' => 'Processos',
        'terras' => 'Terras',
        'financeiro' => 'Financeiro',
    ];

    /** @var array<string, bool> */
    private array $temSoftDelete = [];

    /**
     * Métricas por categoria: [chave, rótulo, fonte, agregação SQL, filtro, formato].
     */
    public function metricas(string $categoria): array
    {
        $estado = fn (string $col, array $valores) => fn (Builder $q) => $q->whereIn($col, $valores);

        return match ($categoria) {
            'processos' => [
                ['croquis', 'Croquis', 'croquis', 'count(*)', null, 'int'],
                ['croquis_pub', 'Croquis publicados', 'croquis', 'count(*)', fn (Builder $q) => $q->where('croquis.publicado', true), 'int'],
                ['operacoes', 'Operações urbanísticas', 'operacoes_urbanisticas', 'count(*)', null, 'int'],
                ['pareceres', 'Pareceres', 'pareceres', 'count(*)', null, 'int'],
                ['instrumentos', 'Instrumentos', 'instrumentos', 'count(*)', null, 'int'],
                ['instr_aprov', 'Instrumentos aprovados', 'instrumentos', 'count(*)', $estado('instrumentos.estado', ['aprovado']), 'int'],
            ],
            'terras' => [
                ['terrenos', 'Terrenos ociosos', 'terrenos_ociosos', 'count(*)', null, 'int'],
                ['terrenos_area', 'Área ociosa (ha)', 'terrenos_ociosos', 'coalesce(sum(terrenos_ociosos.area_m2),0)/10000.0', null, 'dec'],
                ['loteamentos', 'Loteamentos', 'loteamentos', 'count(*)', null, 'int'],
                ['projectos', 'Outros projectos', 'outros_projectos', 'count(*)', null, 'int'],
            ],
            'financeiro' => [
                ['receitas', 'Receitas confirmadas', 'lancamentos', 'coalesce(sum(lancamentos.valor),0)', fn (Builder $q) => $q->where('lancamentos.tipo', 'receita')->where('lancamentos.status', 'confirmado'), 'money'],
                ['despesas', 'Despesas confirmadas', 'lancamentos', 'coalesce(sum(lancamentos.valor),0)', fn (Builder $q) => $q->where('lancamentos.tipo', 'despesa')->where('lancamentos.status', 'confirmado'), 'money'],
                ['rupe', 'Pagamentos RUPE', 'pagamentos', 'count(*)', null, 'int'],
                ['rupe_pagos', 'RUPE pagos', 'pagamentos', 'count(*)', $estado('pagamentos.estado', ['pago', 'concluido']), 'int'],
                ['rupe_valor', 'Arrecadação RUPE', 'pagamentos', 'coalesce(sum(pagamentos.valor),0)', $estado('pagamentos.estado', ['pago', 'concluido']), 'money'],
            ],
            default => [
                ['croquis', 'Croquis', 'croquis', 'count(*)', null, 'int'],
                ['pareceres', 'Pareceres', 'pareceres', 'count(*)', null, 'int'],
                ['terrenos', 'Terrenos ociosos', 'terrenos_ociosos', 'count(*)', null, 'int'],
                ['loteamentos', 'Loteamentos', 'loteamentos', 'count(*)', null, 'int'],
                ['instrumentos', 'Instrumentos', 'instrumentos', 'count(*)', null, 'int'],
                ['rupe_valor', 'Arrecadação RUPE', 'pagamentos', 'coalesce(sum(pagamentos.valor),0)', $estado('pagamentos.estado', ['pago', 'concluido']), 'money'],
            ],
        };
    }

    /**
     * Tabela comparativa por província, com totais.
     *
     * @return array{colunas: array<int, array{chave:string,rotulo:string,formato:string}>, linhas: Collection, totais: array<string, float>}
     */
    public function porProvincia(string $categoria, ?int $provinciaId = null, ?CarbonInterface $de = null, ?CarbonInterface $ate = null): array
    {
        $metricas = $this->metricas($categoria);
        $provincias = Provincia::where('activo', true)
            ->when($provinciaId, fn ($q) => $q->whereKey($provinciaId))
            ->orderBy('nome')
            ->get(['id', 'nome', 'codigo']);

        $valores = [];
        foreach ($metricas as [$chave, , $fonte, $agg, $filtro]) {
            $valores[$chave] = $this->agrupar($fonte, $agg, $filtro, $de, $ate);
        }

        $totais = array_fill_keys(array_column($metricas, 0), 0.0);
        $linhas = $provincias->map(function (Provincia $provincia) use ($metricas, $valores, &$totais) {
            $linha = ['provincia' => $provincia->nome, 'codigo' => $provincia->codigo, 'provincia_id' => $provincia->id];
            foreach ($metricas as [$chave]) {
                $valor = (float) ($valores[$chave][$provincia->id] ?? 0);
                $linha[$chave] = $valor;
                $totais[$chave] += $valor;
            }

            return $linha;
        });

        return [
            'colunas' => array_map(fn ($m) => ['chave' => $m[0], 'rotulo' => $m[1], 'formato' => $m[5]], $metricas),
            'linhas' => $linhas,
            'totais' => $totais,
        ];
    }

    /**
     * Registos criados por mês nos últimos N meses.
     *
     * @return array<int, array{mes:string,valor:int}>
     */
    public function evolucaoMensal(string $categoria, ?int $provinciaId = null, int $meses = 6): array
    {
        $fonte = match ($categoria) {
            'terras' => 'terrenos_ociosos',
            'financeiro' => 'pagamentos',
            default => 'croquis',
        };

        $serie = [];
        for ($i = $meses - 1; $i >= 0; $i--) {
            $inicio = now()->startOfMonth()->subMonths($i);
            $total = $this->consulta($fonte, $provinciaId)
                ->whereBetween("{$fonte}.created_at", [$inicio, $inicio->copy()->endOfMonth()])
                ->count();
            $serie[] = ['mes' => $inicio->format('m/y'), 'valor' => $total];
        }

        return $serie;
    }

    /**
     * Distribuição por estado.
     *
     * @return array<string, int>
     */
    public function distribuicaoEstados(string $categoria, ?int $provinciaId = null): array
    {
        [$fonte, $coluna] = match ($categoria) {
            'terras' => ['terrenos_ociosos', 'status'],
            'financeiro' => ['pagamentos', 'estado'],
            'processos' => ['instrumentos', 'estado'],
            default => ['pareceres', 'estado'],
        };

        return $this->consulta($fonte, $provinciaId)
            ->selectRaw("{$fonte}.{$coluna} as estado, count(*) as total")
            ->groupBy("{$fonte}.{$coluna}")
            ->orderByDesc('total')
            ->pluck('total', 'estado')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    public function provincias(): Collection
    {
        return Provincia::where('activo', true)->orderBy('nome')->pluck('nome', 'id');
    }

    public function totaisGerais(): array
    {
        return [
            'provincias' => Provincia::where('activo', true)->count(),
            'municipios' => Municipio::where('activo', true)->count(),
        ];
    }

    public function formatar(float $valor, string $formato): string
    {
        return match ($formato) {
            'money' => number_format($valor, 2, ',', '.').' Kz',
            'dec' => number_format($valor, 2, ',', '.'),
            default => number_format($valor, 0, ',', '.'),
        };
    }

    /** @return array<int, mixed> */
    private function agrupar(string $fonte, string $agg, ?callable $filtro, ?CarbonInterface $de, ?CarbonInterface $ate): array
    {
        $query = $this->consulta($fonte, null);

        if ($filtro) {
            $filtro($query);
        }
        if ($de) {
            $query->where("{$fonte}.created_at", '>=', $de->copy()->startOfDay());
        }
        if ($ate) {
            $query->where("{$fonte}.created_at", '<=', $ate->copy()->endOfDay());
        }

        return $query->selectRaw("municipios.provincia_id as provincia_id, {$agg} as valor")
            ->groupBy('municipios.provincia_id')
            ->pluck('valor', 'provincia_id')
            ->all();
    }

    /** Consulta base ligada a municípios (croquis passam por operações). */
    private function consulta(string $fonte, ?int $provinciaId): Builder
    {
        $query = DB::table($fonte);

        if ($fonte === 'croquis') {
            $query->join('operacoes_urbanisticas', 'operacoes_urbanisticas.id', '=', 'croquis.operacao_id')
                ->join('municipios', 'municipios.id', '=', 'operacoes_urbanisticas.municipio_id')
                ->whereNull('operacoes_urbanisticas.deleted_at')
                ->whereNull('croquis.deleted_at');
        } else {
            $query->join('municipios', 'municipios.id', '=', "{$fonte}.municipio_id");

            if ($this->usaSoftDelete($fonte)) {
                $query->whereNull("{$fonte}.deleted_at");
            }
        }

        if ($provinciaId) {
            $query->where('municipios.provincia_id', $provinciaId);
        }

        return $query;
    }

    private function usaSoftDelete(string $tabela): bool
    {
        return $this->temSoftDelete[$tabela] ??= Schema::hasColumn($tabela, 'deleted_at');
    }
}

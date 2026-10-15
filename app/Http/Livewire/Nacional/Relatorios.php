<?php

namespace App\Http\Livewire\Nacional;

use App\Services\AuditoriaCentralizadaService;
use App\Services\Nacional\IndicadoresNacionaisService;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Relatorios extends Component
{
    #[Url]
    public string $categoria = 'geral';

    public ?int $provinciaId = null;

    public ?string $de = null;

    public ?string $ate = null;

    protected function rules(): array
    {
        return [
            'categoria' => 'required|in:'.implode(',', array_keys(IndicadoresNacionaisService::CATEGORIAS)),
            'provinciaId' => 'nullable|integer|exists:provincias,id',
            'de' => 'nullable|date',
            'ate' => 'nullable|date|after_or_equal:de',
        ];
    }

    public function mount(): void
    {
        $this->de = now()->startOfYear()->toDateString();
        $this->ate = now()->toDateString();
    }

    public function updated($property): void
    {
        if (in_array($property, ['categoria', 'provinciaId', 'de', 'ate'], true)) {
            $this->validateOnly($property);
        }
    }

    public function updatedProvinciaId($valor): void
    {
        $this->provinciaId = $valor ? (int) $valor : null;
    }

    private function dados(IndicadoresNacionaisService $servico): array
    {
        $valido = ! $this->getErrorBag()->hasAny(['categoria', 'de', 'ate']);

        return $servico->porProvincia(
            in_array($this->categoria, array_keys(IndicadoresNacionaisService::CATEGORIAS), true) ? $this->categoria : 'geral',
            $this->provinciaId,
            $valido && $this->de ? Carbon::parse($this->de) : null,
            $valido && $this->ate ? Carbon::parse($this->ate) : null,
        );
    }

    public function exportar(IndicadoresNacionaisService $servico): StreamedResponse
    {
        $this->validate();
        $dados = $this->dados($servico);
        $nome = 'relatorio-nacional-'.$this->categoria.'-'.now()->format('Ymd-His').'.csv';

        app(AuditoriaCentralizadaService::class)->logAction(
            'export',
            'relatorio_nacional',
            null,
            null,
            ['categoria' => $this->categoria, 'provincia_id' => $this->provinciaId, 'de' => $this->de, 'ate' => $this->ate],
            "Exportação do relatório nacional ({$this->categoria})",
        );

        return response()->streamDownload(function () use ($dados, $servico) {
            $saida = fopen('php://output', 'w');
            fwrite($saida, "\xEF\xBB\xBF");
            fputcsv($saida, array_merge(['Província'], array_column($dados['colunas'], 'rotulo')), ';');

            foreach ($dados['linhas'] as $linha) {
                fputcsv($saida, array_merge([$linha['provincia']], array_map(
                    fn ($c) => $servico->formatar($linha[$c['chave']], $c['formato']),
                    $dados['colunas'],
                )), ';');
            }

            fputcsv($saida, array_merge(['Total nacional'], array_map(
                fn ($c) => $servico->formatar($dados['totais'][$c['chave']], $c['formato']),
                $dados['colunas'],
            )), ';');
            fclose($saida);
        }, $nome, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function render(IndicadoresNacionaisService $servico)
    {
        return view('livewire.nacional.relatorios', [
            'servico' => $servico,
            'dados' => $this->dados($servico),
            'provincias' => $servico->provincias(),
            'categorias' => IndicadoresNacionaisService::CATEGORIAS,
            'categoria' => in_array($this->categoria, array_keys(IndicadoresNacionaisService::CATEGORIAS), true) ? $this->categoria : 'geral',
        ]);
    }
}

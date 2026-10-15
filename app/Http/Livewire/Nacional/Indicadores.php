<?php

namespace App\Http\Livewire\Nacional;

use App\Services\Nacional\IndicadoresNacionaisService;
use Livewire\Component;

class Indicadores extends Component
{
    public string $categoria = 'geral';

    public ?int $provinciaId = null;

    public int $meses = 6;

    public function mount(string $categoria = 'geral'): void
    {
        $this->categoria = array_key_exists($categoria, IndicadoresNacionaisService::CATEGORIAS) ? $categoria : 'geral';
    }

    public function updatedProvinciaId($valor): void
    {
        $this->provinciaId = $valor ? (int) $valor : null;
    }

    public function render(IndicadoresNacionaisService $servico)
    {
        $dados = $servico->porProvincia($this->categoria, $this->provinciaId);
        $meses = in_array($this->meses, [3, 6, 12], true) ? $this->meses : 6;

        return view('livewire.nacional.indicadores', [
            'servico' => $servico,
            'dados' => $dados,
            'provincias' => $servico->provincias(),
            'evolucao' => $servico->evolucaoMensal($this->categoria, $this->provinciaId, $meses),
            'estados' => $servico->distribuicaoEstados($this->categoria, $this->provinciaId),
            'geral' => $servico->totaisGerais(),
            'categorias' => IndicadoresNacionaisService::CATEGORIAS,
        ]);
    }
}

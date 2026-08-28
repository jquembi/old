<?php

namespace App\Livewire;

use App\Models\ArquivoBase;
use App\Models\Croqui;
use App\Models\Municipio;
use App\Models\OperacaoUrbanistica;
use App\Models\TerrenoOcioso;
use Livewire\Component;

class OperacoesUrbanisticas extends Component
{
    public Municipio $municipio;

    public function mount(Municipio $municipio)
    {
        $this->municipio = $municipio;
    }

    public function render()
    {
        $operacoes = OperacaoUrbanistica::where('municipio_id', $this->municipio->id)
            ->latest()
            ->paginate(25);

        return view('livewire.operacoes-urbanisticas', [
            'municipio' => $this->municipio,
            'operacoes' => $operacoes,
            'stats' => $this->stats(),
        ]);
    }

    private function stats(): array
    {
        $operacaoIds = OperacaoUrbanistica::query()
            ->where('municipio_id', $this->municipio->id)
            ->pluck('id');

        return [
            'croquis_elaborados' => Croqui::query()
                ->whereIn('operacao_id', $operacaoIds)
                ->count(),
            'terrenos_ociosos' => TerrenoOcioso::query()
                ->where('municipio_id', $this->municipio->id)
                ->count(),
            'pareceres' => $this->municipio->pareceres()->count(),
            'arquivos_trabalho' => ArquivoBase::query()
                ->where('municipio_id', $this->municipio->id)
                ->count(),
            'ids_territoriais' => Croqui::query()
                ->whereIn('operacao_id', $operacaoIds)
                ->whereNotNull('codigo_territorial')
                ->count(),
        ];
    }
}

<?php

namespace App\Livewire;

use App\Models\CategoriaInstrumento;
use App\Models\Instrumento;
use App\Models\Municipio;
use Livewire\Component;

class InstrumentosOrdenamento extends Component
{
    public Municipio $municipio;

    public function mount(Municipio $municipio): void
    {
        $this->municipio = $municipio;
    }

    public function render()
    {
        $categoria = CategoriaInstrumento::query()
            ->where('slug', 'ordenamento-territorio')
            ->with(['tipoInstrumentos' => fn ($query) => $query->orderBy('ordem')->orderBy('nome')])
            ->first();

        $tipos = $categoria?->tipoInstrumentos ?? collect();

        // Contagens e métricas detalhadas por tipo
        $estatisticas = [];
        $totalGeral = 0;
        $totalAprovados = 0;
        $totalSubmetidos = 0;

        foreach ($tipos as $tipo) {
            $queryTipo = Instrumento::query()
                ->where('municipio_id', $this->municipio->id)
                ->where('tipo_instrumento_id', $tipo->id);

            $total = (clone $queryTipo)->count();
            $aprovados = (clone $queryTipo)->where('estado', 'aprovado')->count();
            $submetidos = (clone $queryTipo)->where('estado', 'submetido')->count();
            $ultimoAno = (clone $queryTipo)->whereNotNull('ano')->orderBy('ano', 'desc')->value('ano');

            $estatisticas[$tipo->id] = [
                'total' => $total,
                'aprovados' => $aprovados,
                'submetidos' => $submetidos,
                'rascunhos' => $total - $aprovados - $submetidos,
                'ultimo_ano' => $ultimoAno,
            ];

            $totalGeral += $total;
            $totalAprovados += $aprovados;
            $totalSubmetidos += $submetidos;
        }

        return view('livewire.instrumentos-ordenamento', [
            'municipio' => $this->municipio,
            'categoria' => $categoria,
            'tipos' => $tipos,
            'estatisticas' => $estatisticas,
            'totalGeral' => $totalGeral,
            'totalAprovados' => $totalAprovados,
            'totalSubmetidos' => $totalSubmetidos,
        ]);
    }
}

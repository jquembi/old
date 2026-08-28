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
        $tipos = CategoriaInstrumento::query()
            ->where('slug', 'ordenamento-territorio')
            ->with(['tipoInstrumentos' => fn ($query) => $query->orderBy('ordem')->orderBy('nome')])
            ->first()
            ?->tipoInstrumentos
            ?? collect();

        $contagens = Instrumento::query()
            ->where('municipio_id', $this->municipio->id)
            ->whereIn('tipo_instrumento_id', $tipos->pluck('id'))
            ->selectRaw('tipo_instrumento_id, count(*) as total')
            ->groupBy('tipo_instrumento_id')
            ->pluck('total', 'tipo_instrumento_id')
            ->all();

        return view('livewire.instrumentos-ordenamento', [
            'municipio' => $this->municipio,
            'tipos' => $tipos,
            'contagens' => $contagens,
        ]);
    }
}

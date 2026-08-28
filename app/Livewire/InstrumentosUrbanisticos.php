<?php

namespace App\Livewire;

use App\Models\CategoriaInstrumento;
use App\Models\Instrumento;
use App\Models\Loteamento;
use App\Models\Municipio;
use Livewire\Component;

class InstrumentosUrbanisticos extends Component
{
    public Municipio $municipio;

    public function mount(Municipio $municipio): void
    {
        $this->municipio = $municipio;
    }

    public function render()
    {
        $tipos = CategoriaInstrumento::query()
            ->where('slug', 'instrumentos-urbanisticos')
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

        $loteamentos = [
            'total' => Loteamento::query()->where('municipio_id', $this->municipio->id)->count(),
            'aprovados' => Loteamento::query()->where('municipio_id', $this->municipio->id)->where('estado', 'aprovado')->count(),
            'em_curso' => Loteamento::query()->where('municipio_id', $this->municipio->id)->where('estado', 'em_curso')->count(),
            'concluidos' => Loteamento::query()->where('municipio_id', $this->municipio->id)->where('estado', 'concluido')->count(),
        ];

        return view('livewire.instrumentos-urbanisticos', [
            'municipio' => $this->municipio,
            'tipos' => $tipos,
            'contagens' => $contagens,
            'loteamentos' => $loteamentos,
        ]);
    }
}

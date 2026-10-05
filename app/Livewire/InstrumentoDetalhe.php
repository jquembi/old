<?php

namespace App\Livewire;

use App\Models\Instrumento;
use Livewire\Component;

class InstrumentoDetalhe extends Component
{
    public Instrumento $instrumento;
    public ?string $ficheiroAtivo = null;

    public function mount(Instrumento $instrumento): void
    {
        $user = auth()->user();
        if ($user && $user->isMunicipal() && $user->municipio_id && $instrumento->municipio_id !== $user->municipio_id) {
            abort(403, 'Não tem permissão para aceder a instrumentos deste município.');
        }

        $this->instrumento = $instrumento->load(['municipio', 'criadoPor', 'tipoInstrumento.categoria', 'ficheiros']);

        // Selecionar o primeiro PDF como ficheiro ativo para pré-visualização
        $primeiroPdf = $this->instrumento->ficheiros->first(fn ($f) => strtolower($f->tipo_ficheiro) === 'pdf');
        if ($primeiroPdf) {
            $this->ficheiroAtivo = $primeiroPdf->caminho;
        } elseif ($this->instrumento->ficheiros->isNotEmpty()) {
            $this->ficheiroAtivo = $this->instrumento->ficheiros->first()->caminho;
        }
    }

    public function selecionarFicheiro(string $caminho): void
    {
        $this->ficheiroAtivo = $caminho;
    }

    public function render()
    {
        return view('livewire.instrumento-detalhe');
    }
}

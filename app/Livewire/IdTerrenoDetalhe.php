<?php

namespace App\Livewire;

use App\Models\Croqui;
use App\Models\Municipio;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class IdTerrenoDetalhe extends Component
{
    public Municipio $municipio;
    public Croqui $croqui;

    public function mount(Municipio $municipio, Croqui $croqui): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        if ($user->isMunicipal() && $user->municipio_id && $municipio->id !== $user->municipio_id) {
            abort(403, 'Não tem permissão para aceder a terrenos deste município.');
        }

        $this->municipio = $municipio;
        $this->croqui = $croqui->load(['proprietarios', 'criadoPor', 'pareceres', 'operacao']);
    }

    public function render()
    {
        $terreno = $this->croqui;
        $pareceres = $this->croqui->pareceres ?? collect();

        // Extrair coordenadas
        $coordenadas = [];
        if ($this->croqui->coordenadas_texto) {
            $parsed = json_decode($this->croqui->coordenadas_texto, true);
            if (is_array($parsed)) {
                $coordenadas = $parsed;
            }
        }

        // Construir lista de documentos associados
        $documentos = [];
        if ($this->croqui->ficheiro_pdf_caminho) {
            $documentos[] = [
                'titulo' => $this->croqui->ficheiro_pdf_nome ?: 'Croqui de Localização (PDF)',
                'tipo' => 'Croqui Principal',
                'descricao' => 'Ficheiro PDF do croqui georreferenciado.',
                'icone' => 'document-text',
                'rota' => route('croqui.detalhe', $this->croqui),
            ];
        }

        if ($this->croqui->memoria_descritiva_caminho) {
            $documentos[] = [
                'titulo' => $this->croqui->memoria_descritiva_nome ?: 'Memória Descritiva',
                'tipo' => 'Memória Descritiva',
                'descricao' => 'Documento descritivo dos limites e confrontações.',
                'icone' => 'document',
                'rota' => route('municipio.operacoes.memoria-descritiva', [$this->municipio, $this->croqui]),
            ];
        }

        $totalDocumentos = count($documentos);

        return view('livewire.id-terreno-detalhe', [
            'terreno' => $terreno,
            'municipio' => $this->municipio,
            'pareceres' => $pareceres,
            'documentos' => $documentos,
            'totalDocumentos' => $totalDocumentos,
            'coordenadas' => $coordenadas,
        ]);
    }
}

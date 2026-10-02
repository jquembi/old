<?php

namespace App\Livewire;

use App\Enums\EstadoPagamento;
use App\Models\Pagamento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class PagamentoDetalhe extends Component
{
    use WithFileUploads;

    public Pagamento $pagamento;
    public bool $showUploadModal = false;
    public $comprovativo = null;

    public function mount(Pagamento $pagamento): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        // Se o utilizador for municipal/técnico, só pode aceder a pagamentos do seu município
        if ($user->isMunicipal() && $user->municipio_id && $pagamento->municipio_id !== $user->municipio_id) {
            abort(403, 'Não tem permissão para aceder a pagamentos deste município.');
        }

        $this->pagamento = $pagamento->load(['croqui', 'municipio', 'criadoPor', 'rupeGeradoPor', 'comprovativoPor', 'confirmadoPor']);
    }

    public function getUploadProgressProperty(): int
    {
        return $this->comprovativo ? 100 : 0;
    }

    public function abrirUpload(): void
    {
        $this->showUploadModal = true;
    }

    public function fecharUpload(): void
    {
        $this->showUploadModal = false;
        $this->comprovativo = null;
    }

    public function submeterComprovativo(): void
    {
        $user = Auth::user();
        if (!$this->pagamento->podeSubmeterComprovativo()) {
            $this->addError('comprovativo', 'Este pagamento não se encontra em estado que permita submeter comprovativo.');
            return;
        }

        $this->validate([
            'comprovativo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'comprovativo.required' => 'O ficheiro do comprovativo é obrigatório.',
            'comprovativo.mimes' => 'O comprovativo deve ser PDF, JPG ou PNG.',
            'comprovativo.max' => 'O tamanho máximo do comprovativo é de 5MB.',
        ]);

        $caminho = $this->comprovativo->store('comprovativos', 'public');

        $this->pagamento->update([
            'comprovativo_path' => $caminho,
            'comprovativo_nome' => $this->comprovativo->getClientOriginalName(),
            'comprovativo_mime' => $this->comprovativo->getMimeType(),
            'comprovativo_em' => now(),
            'comprovativo_por' => $user->id,
            'estado' => EstadoPagamento::ComproativoSubmetido,
            'motivo_rejeicao' => null,
        ]);

        $this->fecharUpload();
        session()->flash('status', 'Comprovativo submetido com sucesso. Aguarda confirmação pelo INOTU.');
    }

    public function render()
    {
        $user = Auth::user();
        $podeSubmeter = $this->pagamento->podeSubmeterComprovativo() &&
            ($user?->isMunicipal() || $user?->isInotu() || $user?->isNacional());

        return view('livewire.pagamento-detalhe', [
            'podeSubmeter' => $podeSubmeter,
        ]);
    }
}

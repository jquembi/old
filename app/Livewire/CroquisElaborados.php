<?php

namespace App\Livewire;

use App\Models\Croqui;
use App\Models\Municipio;
use App\Models\OperacaoUrbanistica;
use App\Models\Pagamento;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CroquisElaborados extends Component
{
    use WithPagination;

    public Municipio $municipio;
    public string $search = '';
    public bool $showRupModal = false;
    public ?string $rup_referencia = null;
    public ?int $rupCroquiId = null;

    public function mount(Municipio $municipio)
    {
        $this->municipio = $municipio;
    }

    public function getPodeEditarProperty()
    {
        return Auth::user()?->temPermissao('operacoes.editar') ?? false;
    }

    public function getPodePublicarProperty()
    {
        return Auth::user()?->temPermissao('croquis.publicar') ?? false;
    }

    public function getPodeHomologarProperty()
    {
        return Auth::user()?->temPermissao('croquis.aprovar') ?? false;
    }

    public function getPodeEliminarProperty()
    {
        return Auth::user()?->podeEnviarParaLixeira() ?? false;
    }

    public function getCroquisProperty()
    {
        $operacao = OperacaoUrbanistica::where('municipio_id', $this->municipio->id)
            ->where('tipo', 'croqui_elaborado')
            ->first();

        if (!$operacao) {
            return Croqui::where('id', 0)->paginate(25); // Empty pagination
        }

        $query = $operacao->croquis()
            ->where('tipo', 'elaborado')
            ->with(['proprietarios', 'criadoPor', 'pagamentoActivo']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('titulo', 'like', '%' . $this->search . '%')
                  ->orWhere('ficheiro_pdf_nome', 'like', '%' . $this->search . '%');
            });
        }

        return $query->latest()->paginate(25);
    }

    public function publicarCroqui(int $croquiId)
    {
        $croqui = Croqui::findOrFail($croquiId);
        
        if (!$croqui->ficheiro_pdf_caminho || !$croqui->memoria_descritiva_caminho) {
            $this->addError('publicar', 'O croqui precisa de PDF e memória descritiva para ser publicado.');
            return;
        }

        $croqui->update([
            'publicado' => true,
            'publicado_por' => Auth::id(),
            'publicado_em' => now(),
        ]);

        // Create pagamento record for RUP
        if (!$croqui->pagamentoActivo) {
            Pagamento::create([
                'croqui_id' => $croqui->id,
                'estado' => 'pendente',
                'valor' => 0, // Will be set by INOTU
            ]);
        }
    }

    public function despublicarCroqui(int $croquiId)
    {
        $croqui = Croqui::findOrFail($croquiId);
        
        $croqui->update([
            'publicado' => false,
            'publicado_por' => null,
            'publicado_em' => null,
            'rup_pago' => false,
            'rup_referencia' => null,
            'rup_pago_em' => null,
            'download_liberado' => false,
            'liberado_por' => null,
            'liberado_em' => null,
        ]);
    }

    public function homologarDownload(int $croquiId)
    {
        $croqui = Croqui::findOrFail($croquiId);
        
        if (!$croqui->rup_pago) {
            $this->addError('homologar', 'O croqui precisa ter o RUP pago para ser homologado.');
            return;
        }

        $croqui->update([
            'download_liberado' => true,
            'liberado_por' => Auth::id(),
            'liberado_em' => now(),
        ]);
    }

    public function revogarDownload(int $croquiId)
    {
        $croqui = Croqui::findOrFail($croquiId);
        
        $croqui->update([
            'download_liberado' => false,
            'liberado_por' => null,
            'liberado_em' => null,
        ]);
    }

    public function eliminar(int $croquiId)
    {
        $croqui = Croqui::findOrFail($croquiId);
        
        // Soft delete using trait
        $croqui->deleted_by = Auth::id();
        $croqui->deleted_at = now();
        $croqui->save();
    }

    public function abrirModalRup(int $croquiId)
    {
        $this->rupCroquiId = $croquiId;
        $this->rup_referencia = null;
        $this->showRupModal = true;
    }

    public function fecharModalRup()
    {
        $this->showRupModal = false;
        $this->rup_referencia = null;
        $this->rupCroquiId = null;
    }

    public function registarRup(int $croquiId)
    {
        $this->validate([
            'rup_referencia' => 'required|string|max:255',
        ]);

        $croqui = Croqui::findOrFail($croquiId);
        
        $croqui->update([
            'rup_pago' => true,
            'rup_referencia' => $this->rup_referencia,
            'rup_pago_em' => now(),
        ]);

        // Update pagamento record
        if ($croqui->pagamentoActivo) {
            $croqui->pagamentoActivo->update([
                'estado' => 'pago',
                'rup_referencia' => $this->rup_referencia,
                'pago_em' => now(),
            ]);
        }

        $this->fecharModalRup();
    }

    public function render()
    {
        return view('livewire.croquis-elaborados', [
            'croquis' => $this->croquis,
            'podeEditar' => $this->podeEditar,
            'podePublicar' => $this->podePublicar,
            'podeHomologar' => $this->podeHomologar,
            'podeEliminar' => $this->podeEliminar,
        ]);
    }
}

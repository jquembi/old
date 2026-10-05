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

    public function getPodeCriarProperty(): bool
    {
        $user = Auth::user();
        return (bool) ($user?->temPermissao('operacoes.criar') || $user?->temPermissao('croquis.criar') || $user?->isSuperAdminNacional() || $user?->isDiretorInotu());
    }

    public function getPodeEditarProperty(): bool
    {
        return (bool) (Auth::user()?->temPermissao('operacoes.editar') || Auth::user()?->isSuperAdminNacional());
    }

    public function getPodePublicarProperty(): bool
    {
        return (bool) (Auth::user()?->temPermissao('croquis.publicar') || Auth::user()?->isSuperAdminNacional());
    }

    public function getPodeHomologarProperty(): bool
    {
        return (bool) (Auth::user()?->temPermissao('croquis.aprovar') || Auth::user()?->isSuperAdminNacional());
    }

    public function getPodeEliminarProperty(): bool
    {
        return (bool) (Auth::user()?->podeEnviarParaLixeira() || Auth::user()?->isSuperAdminNacional());
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

    public function publicarCroqui(int $croquiId): void
    {
        if (!$this->podePublicar) {
            $this->addError('publicar', 'Não tem permissão para publicar croquis.');
            $this->dispatch('toast', type: 'error', message: 'Não tem permissão para submeter croquis.');
            return;
        }

        $croqui = Croqui::findOrFail($croquiId);

        if (!$croqui->ficheiro_pdf_caminho || !$croqui->memoria_descritiva_caminho) {
            $this->addError('publicar', 'O croqui precisa de PDF e memória descritiva para ser publicado.');
            $this->dispatch('toast', type: 'warning', message: 'O croqui necessita de PDF e memória descritiva anexados.');
            return;
        }

        $croqui->update([
            'publicado' => true,
            'publicado_por' => Auth::id(),
            'publicado_em' => now(),
        ]);

        // Criar registro de pagamento se necessário
        if (!$croqui->pagamentoActivo) {
            Pagamento::create([
                'croqui_id' => $croqui->id,
                'municipio_id' => $this->municipio->id,
                'estado' => 'aguardando_pagamento',
                'valor' => 0,
                'criado_por' => Auth::id(),
            ]);
        }

        $this->dispatch('toast', type: 'success', message: 'Croqui submetido ao INOTU com sucesso.');
    }

    public function despublicarCroqui(int $croquiId): void
    {
        if (!$this->podePublicar && !$this->podeHomologar) {
            $this->addError('despublicar', 'Não tem permissão para cancelar submissão.');
            $this->dispatch('toast', type: 'error', message: 'Não tem permissão para cancelar submissão.');
            return;
        }

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

        $this->dispatch('toast', type: 'info', message: 'Submissão cancelada com sucesso.');
    }

    public function homologarDownload(int $croquiId): void
    {
        if (!$this->podeHomologar) {
            $this->addError('homologar', 'Não tem permissão para homologar downloads.');
            $this->dispatch('toast', type: 'error', message: 'Não tem permissão para homologar downloads.');
            return;
        }

        $croqui = Croqui::findOrFail($croquiId);

        if (!$croqui->rup_pago) {
            $this->addError('homologar', 'O croqui precisa ter o RUP pago para ser homologado.');
            $this->dispatch('toast', type: 'warning', message: 'O croqui precisa ter o RUP liquidado para homologação.');
            return;
        }

        $croqui->update([
            'download_liberado' => true,
            'liberado_por' => Auth::id(),
            'liberado_em' => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: 'Download homologado com sucesso.');
    }

    public function revogarDownload(int $croquiId): void
    {
        if (!$this->podeHomologar) {
            $this->addError('revogar', 'Não tem permissão para revogar downloads.');
            $this->dispatch('toast', type: 'error', message: 'Não tem permissão para revogar downloads.');
            return;
        }

        $croqui = Croqui::findOrFail($croquiId);

        $croqui->update([
            'download_liberado' => false,
            'liberado_por' => null,
            'liberado_em' => null,
        ]);

        $this->dispatch('toast', type: 'warning', message: 'Homologação de download revogada.');
    }

    public function eliminar(int $croquiId): void
    {
        if (!$this->podeEliminar) {
            $this->addError('eliminar', 'Não tem permissão para eliminar croquis.');
            $this->dispatch('toast', type: 'error', message: 'Não tem permissão para eliminar croquis.');
            return;
        }

        $croqui = Croqui::findOrFail($croquiId);

        $croqui->deleted_by = Auth::id();
        $croqui->deleted_at = now();
        $croqui->save();

        $this->dispatch('toast', type: 'success', message: 'Croqui enviado para a lixeira.');
    }

    public function abrirModalRup(int $croquiId): void
    {
        $this->rupCroquiId = $croquiId;
        $this->rup_referencia = null;
        $this->showRupModal = true;
    }

    public function fecharModalRup(): void
    {
        $this->showRupModal = false;
        $this->rup_referencia = null;
        $this->rupCroquiId = null;
    }

    public function registarRup(int $croquiId): void
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

        if ($croqui->pagamentoActivo) {
            $croqui->pagamentoActivo->update([
                'estado' => 'pago',
                'rupe_referencia' => $this->rup_referencia,
                'confirmado_em' => now(),
            ]);
        }

        $this->fecharModalRup();
        $this->dispatch('toast', type: 'success', message: 'Pagamento RUPE registado com sucesso.');
    }

    public function render()
    {
        return view('livewire.croquis-elaborados', [
            'croquis' => $this->croquis,
            'podeCriar' => $this->podeCriar,
            'podeEditar' => $this->podeEditar,
            'podePublicar' => $this->podePublicar,
            'podeHomologar' => $this->podeHomologar,
            'podeEliminar' => $this->podeEliminar,
        ]);
    }
}

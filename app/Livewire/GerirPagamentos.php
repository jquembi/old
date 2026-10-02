<?php

namespace App\Livewire;

use App\Enums\EstadoPagamento;
use App\Models\Croqui;
use App\Models\Pagamento;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class GerirPagamentos extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filtroEstado = 'todos';

    // Modal Gerar RUPE
    public bool $showGerarRupeModal = false;
    public ?int $pagamentoId = null;
    public string $rupeReferencia = '';
    public string $rupeEntidade = '00001';
    public ?float $rupeValor = null;
    public ?string $rupeDataLimite = null;
    public ?string $rupeObservacoes = null;

    // Modal Ver Comprovativo
    public bool $showVerComprovativoModal = false;
    public ?Pagamento $pagComp = null;

    // Modal Confirmar
    public bool $showConfirmarModal = false;
    public ?string $observacoesConfirmacao = null;

    // Modal Rejeitar
    public bool $showRejeitarModal = false;
    public string $motivoRejeicao = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user || (!$user->temPermissao('pagamentos.gerir') && !$user->temPermissao('operacoes.ver') && !$user->isInotu() && !$user->isNacional())) {
            abort(403, 'Não tem permissão para aceder à gestão de pagamentos.');
        }

        $this->rupeDataLimite = now()->addDays(30)->format('Y-m-d');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    // ─── Modal Gerar RUPE ───────────────────────────────────────

    public function abrirGerarRupe(int $id): void
    {
        $user = Auth::user();
        if (!$user?->isInotu() && !$user?->isNacional() && !$user?->temPermissao('pagamentos.gerir')) {
            abort(403, 'Apenas o INOTU ou utilizadores com permissão podem gerar RUPE.');
        }

        $pagamento = Pagamento::findOrFail($id);
        $this->pagamentoId = $pagamento->id;
        $this->rupeReferencia = $pagamento->rupe_referencia ?? (string) mt_rand(100000000000000, 999999999999999);
        $this->rupeEntidade = $pagamento->rupe_entidade ?? '00001';
        $this->rupeValor = (float) ($pagamento->valor > 0 ? $pagamento->valor : 50000.00);
        $this->rupeDataLimite = $pagamento->rupe_data_limite ? $pagamento->rupe_data_limite->format('Y-m-d') : now()->addDays(30)->format('Y-m-d');
        $this->rupeObservacoes = $pagamento->observacoes;

        $this->showGerarRupeModal = true;
    }

    public function fecharGerarRupe(): void
    {
        $this->showGerarRupeModal = false;
        $this->resetValidation();
    }

    public function gerarRupe(): void
    {
        $user = Auth::user();
        if (!$user?->isInotu() && !$user?->isNacional() && !$user?->temPermissao('pagamentos.gerir')) {
            abort(403, 'Apenas o INOTU ou utilizadores com permissão podem gerar RUPE.');
        }

        $this->validate([
            'rupeReferencia' => ['required', 'string', 'max:50'],
            'rupeEntidade' => ['required', 'string', 'max:20'],
            'rupeValor' => ['required', 'numeric', 'min:0.01'],
            'rupeDataLimite' => ['required', 'date', 'after_or_equal:today'],
            'rupeObservacoes' => ['nullable', 'string', 'max:1000'],
        ], [
            'rupeReferencia.required' => 'A referência RUPE é obrigatória.',
            'rupeEntidade.required' => 'A entidade é obrigatória.',
            'rupeValor.required' => 'O valor é obrigatório.',
            'rupeDataLimite.required' => 'A data limite é obrigatória.',
            'rupeDataLimite.after_or_equal' => 'A data limite deve ser hoje ou posterior.',
        ]);

        $pagamento = Pagamento::findOrFail($this->pagamentoId);
        $pagamento->update([
            'rupe_referencia' => $this->rupeReferencia,
            'rupe_entidade' => $this->rupeEntidade,
            'valor' => $this->rupeValor,
            'rupe_data_limite' => $this->rupeDataLimite,
            'observacoes' => $this->rupeObservacoes,
            'estado' => EstadoPagamento::RupeGerado,
            'rupe_gerado_por' => $user->id,
            'rupe_gerado_em' => now(),
        ]);

        $this->fecharGerarRupe();
        session()->flash('status', "Referência RUPE para '{$pagamento->croqui?->titulo}' gerada com sucesso.");
    }

    // ─── Modal Ver Comprovativo ─────────────────────────────────

    public function abrirVerComprovativo(int $id): void
    {
        $this->pagComp = Pagamento::with('croqui')->findOrFail($id);
        $this->showVerComprovativoModal = true;
    }

    public function fecharVerComprovativo(): void
    {
        $this->showVerComprovativoModal = false;
        $this->pagComp = null;
    }

    // ─── Modal Confirmar ────────────────────────────────────────

    public function abrirConfirmar(int $id): void
    {
        $this->pagamentoId = $id;
        $this->observacoesConfirmacao = null;
        $this->showConfirmarModal = true;
    }

    public function fecharConfirmar(): void
    {
        $this->showConfirmarModal = false;
        $this->observacoesConfirmacao = null;
    }

    public function confirmarPagamento(): void
    {
        $user = Auth::user();
        if (!$user?->isInotu() && !$user?->isNacional() && !$user?->temPermissao('croquis.aprovar')) {
            abort(403, 'Apenas o INOTU ou utilizadores com permissão podem confirmar pagamentos.');
        }

        $pagamento = Pagamento::with('croqui')->findOrFail($this->pagamentoId);
        $pagamento->update([
            'estado' => EstadoPagamento::Pago,
            'confirmado_por' => $user->id,
            'confirmado_em' => now(),
            'observacoes' => $this->observacoesConfirmacao ?: $pagamento->observacoes,
        ]);

        // Liberta o download do croqui associado
        if ($pagamento->croqui) {
            $pagamento->croqui->update([
                'download_liberado' => true,
                'liberado_por' => $user->id,
                'liberado_em' => now(),
                'rup_pago' => true,
                'rup_pago_em' => now(),
            ]);
        }

        $this->fecharConfirmar();
        session()->flash('status', "Pagamento confirmado com sucesso. O download do croqui '{$pagamento->croqui?->titulo}' foi libertado.");
    }

    // ─── Modal Rejeitar ─────────────────────────────────────────

    public function abrirRejeitar(int $id): void
    {
        $this->pagamentoId = $id;
        $this->motivoRejeicao = '';
        $this->showRejeitarModal = true;
    }

    public function fecharRejeitar(): void
    {
        $this->showRejeitarModal = false;
        $this->motivoRejeicao = '';
        $this->resetValidation();
    }

    public function rejeitarPagamento(): void
    {
        $user = Auth::user();
        if (!$user?->isInotu() && !$user?->isNacional() && !$user?->temPermissao('croquis.aprovar')) {
            abort(403, 'Apenas o INOTU ou utilizadores com permissão podem rejeitar pagamentos.');
        }

        $this->validate([
            'motivoRejeicao' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'motivoRejeicao.required' => 'O motivo da rejeição é obrigatório.',
            'motivoRejeicao.min' => 'O motivo deve conter pelo menos 5 caracteres.',
        ]);

        $pagamento = Pagamento::findOrFail($this->pagamentoId);
        $pagamento->update([
            'estado' => EstadoPagamento::PagamentoRejeitado,
            'motivo_rejeicao' => $this->motivoRejeicao,
        ]);

        $this->fecharRejeitar();
        session()->flash('status', "Pagamento rejeitado. O município foi notificado para submeter novo comprovativo.");
    }

    // ─── Render ─────────────────────────────────────────────────

    public function render()
    {
        $user = Auth::user();
        $baseQuery = Pagamento::query();

        // Se for municipal/técnico, filtrar apenas os pagamentos do seu município
        if ($user && $user->isMunicipal() && $user->municipio_id) {
            $baseQuery->where('municipio_id', $user->municipio_id);
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'aguardando_pagamento' => (clone $baseQuery)->where('estado', EstadoPagamento::AguardandoPagamento)->count(),
            'rupe_gerado' => (clone $baseQuery)->where('estado', EstadoPagamento::RupeGerado)->count(),
            'comprovativo' => (clone $baseQuery)->where('estado', EstadoPagamento::ComproativoSubmetido)->count(),
            'confirmados' => (clone $baseQuery)->whereIn('estado', [EstadoPagamento::Pago, EstadoPagamento::Concluido])->count(),
            'rejeitados' => (clone $baseQuery)->where('estado', EstadoPagamento::PagamentoRejeitado)->count(),
        ];

        $query = (clone $baseQuery)->with(['croqui', 'croqui.operacao.municipio', 'criadoPor'])->latest();

        if ($this->filtroEstado !== 'todos') {
            $estadoEnum = match ($this->filtroEstado) {
                'aguardando_pagamento' => EstadoPagamento::AguardandoPagamento,
                'rupe_gerado' => EstadoPagamento::RupeGerado,
                'comprovativo_submetido' => EstadoPagamento::ComproativoSubmetido,
                'pago' => EstadoPagamento::Pago,
                'pagamento_rejeitado' => EstadoPagamento::PagamentoRejeitado,
                default => null,
            };

            if ($estadoEnum) {
                $query->where('estado', $estadoEnum);
            }
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('rupe_referencia', 'like', '%' . $this->search . '%')
                  ->orWhereHas('croqui', function ($cq) {
                      $cq->where('titulo', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('municipio', function ($mq) {
                      $mq->where('nome', 'like', '%' . $this->search . '%');
                  });
            });
        }

        return view('livewire.gerir-pagamentos', [
            'pagamentos' => $query->paginate(20),
            'stats' => $stats,
        ]);
    }
}

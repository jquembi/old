<?php

namespace App\Livewire;

use App\Models\SystemError;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ErroSistemaDetalhe extends Component
{
    public SystemError $erro;

    public function mount(mixed $error): void
    {
        $user = Auth::user();
        if (!$user || (!$user->isNacional() && !$user->isInotu() && !$user->temPermissao('sistema.ver'))) {
            abort(403, 'Não tem permissão para aceder aos erros do sistema.');
        }

        if ($error instanceof SystemError) {
            $this->erro = $error->load(['user', 'resolvedBy']);
        } else {
            $this->erro = SystemError::with(['user', 'resolvedBy'])->findOrFail((int) $error);
        }
    }

    public function marcarResolvido(): void
    {
        $user = Auth::user();
        if (!$user || (!$user->isNacional() && !$user->isInotu())) {
            abort(403, 'Apenas administradores podem marcar erros como resolvidos.');
        }

        $this->erro->update([
            'status' => 'resolvido',
            'resolved_at' => now(),
            'resolved_by' => $user->id,
        ]);

        session()->flash('status', 'Erro marcado como resolvido.');
    }

    public function reabrir(): void
    {
        $user = Auth::user();
        if (!$user || (!$user->isNacional() && !$user->isInotu())) {
            abort(403, 'Apenas administradores podem reabrir erros.');
        }

        $this->erro->update([
            'status' => 'pendente',
            'resolved_at' => null,
            'resolved_by' => null,
        ]);

        session()->flash('status', 'Erro reaberto para acompanhamento.');
    }

    public function render()
    {
        return view('livewire.erro-sistema-detalhe', [
            'erro' => $this->erro,
        ]);
    }
}

<?php

namespace App\Livewire;

use App\Models\LogAcesso;
use Livewire\Component;

class LogAcessoDetalhe extends Component
{
    public LogAcesso $log;

    public function mount(LogAcesso $log)
    {
        $user = auth()->user();
        if (!$user || (!$user->temPermissao('auditoria.ver') && !$user->isNacional() && !$user->isInotu())) {
            abort(403, 'Não tem permissão para aceder aos detalhes de auditoria.');
        }

        $this->log = $log->load(['user.roles']);
    }

    public function render()
    {
        return view('livewire.log-acesso-detalhe');
    }
}

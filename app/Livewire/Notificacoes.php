<?php

namespace App\Livewire;

use App\Models\Notificacao;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Notificacoes extends Component
{
    public function render()
    {
        $notificacoes = Notificacao::where('user_id', Auth::id())
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.notificacoes', [
            'notificacoes' => $notificacoes,
        ]);
    }
}

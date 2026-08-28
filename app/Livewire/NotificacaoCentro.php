<?php

namespace App\Livewire;

use App\Models\Notificacao;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificacaoCentro extends Component
{
    public function marcarComoLida(int $id): void
    {
        Notificacao::query()
            ->where('user_id', Auth::id())
            ->whereKey($id)
            ->update(['lida' => true]);
    }

    public function marcarTodasComoLidas(): void
    {
        Notificacao::query()
            ->where('user_id', Auth::id())
            ->where('lida', false)
            ->update(['lida' => true]);
    }

    public function render()
    {
        $query = Notificacao::query()->where('user_id', Auth::id());

        return view('livewire.notificacao-centro', [
            'naoLidas' => (clone $query)->where('lida', false)->count(),
            'notificacoes' => $query->latest()->limit(6)->get(),
        ]);
    }
}

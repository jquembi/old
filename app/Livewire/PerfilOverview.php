<?php

namespace App\Livewire;

use App\Models\Auditoria;
use App\Models\LogAcesso;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PerfilOverview extends Component
{
    public function render()
    {
        $user = Auth::user()?->loadMissing([
            'roles',
            'provincia',
            'municipio',
            'criadoPor',
        ]);

        $actividades = Auditoria::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(20)
            ->get();

        $acessos = LogAcesso::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        $ultimoAcesso = $acessos->first()?->created_at;

        return view('livewire.perfil-overview', [
            'user' => $user,
            'actividades' => $actividades,
            'acessos' => $acessos,
            'stats' => [
                'total_acoes' => Auditoria::query()->where('user_id', $user->id)->count(),
                'acoes_hoje' => Auditoria::query()->where('user_id', $user->id)->whereDate('created_at', today())->count(),
                'acessos' => LogAcesso::query()->where('user_id', $user->id)->count(),
                'ultimo_acesso' => $ultimoAcesso,
            ],
        ]);
    }
}

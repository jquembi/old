<?php

namespace App\Livewire;

use App\Models\Croqui;
use App\Models\Instrumento;
use App\Models\Loteamento;
use App\Models\Municipio;
use App\Models\Notificacao;
use App\Models\OperacaoUrbanistica;
use App\Models\ProjectoSona;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $user->loadMissing('roles');

        // Scoped queries based on user level
        $municipiosQuery = Municipio::orderBy('nome');
        $instrumentosQuery = Instrumento::query();
        $loteamentosQuery = Loteamento::query();
        $operacoesQuery = OperacaoUrbanistica::query();
        $sonaQuery = ProjectoSona::query();

        // Municipal/Técnico: apenas o seu município
        if ($user->isMunicipal()) {
            $municipiosQuery->where('id', $user->municipio_id);
            $instrumentosQuery->where('municipio_id', $user->municipio_id);
            $loteamentosQuery->where('municipio_id', $user->municipio_id);
            $operacoesQuery->where('municipio_id', $user->municipio_id);
            $sonaQuery->where('municipio_id', $user->municipio_id);
        }
        // Provincial: apenas municípios da sua província
        elseif ($user->isProvincial()) {
            $municipiosProvinciaIds = Municipio::where('provincia_id', $user->provincia_id)->pluck('id');
            $municipiosQuery->where('provincia_id', $user->provincia_id);
            $instrumentosQuery->whereIn('municipio_id', $municipiosProvinciaIds);
            $loteamentosQuery->whereIn('municipio_id', $municipiosProvinciaIds);
            $operacoesQuery->whereIn('municipio_id', $municipiosProvinciaIds);
            $sonaQuery->whereIn('municipio_id', $municipiosProvinciaIds);
        }
        // INOTU e Nacional: acesso completo (sem filtro)

        $municipios = $municipiosQuery->get();

        $stats = [
            'total_municipios' => $municipios->count(),
            'total_instrumentos' => $instrumentosQuery->count(),
            'total_loteamentos' => $loteamentosQuery->count(),
            'total_operacoes' => $operacoesQuery->count(),
            'total_sona' => $sonaQuery->count(),
            'total_usuarios' => $user->isInotu() || $user->isNacional() ? User::where('activo', true)->count() : 0,
            'notificacoes_nao_lidas' => Notificacao::where('user_id', $user->id)->where('lida', false)->count(),
        ];

        return view('livewire.dashboard', [
            'municipios' => $municipios,
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}


<?php

namespace App\Livewire;

use App\Models\Municipio;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $municipios = Municipio::orderBy('nome')->get();
        $user = auth()->user();
        
        $stats = [
            'total_municipios' => $municipios->count(),
            'total_instrumentos' => \App\Models\Instrumento::count(),
            'total_loteamentos' => \App\Models\Loteamento::count(),
            'total_usuarios' => \App\Models\User::count(),
            'total_operacoes' => \App\Models\OperacaoUrbanistica::count(),
            'total_sona' => \App\Models\ProjectoSona::count(),
        ];
        
        return view('livewire.dashboard', [
            'municipios' => $municipios,
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}

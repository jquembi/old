<?php

namespace App\Livewire;

use App\Models\Municipio;
use App\Models\OutroProjecto;
use App\Models\ProjectoSona;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProjectosRequalificacao extends Component
{
    public Municipio $municipio;

    public function mount(Municipio $municipio): void
    {
        $this->municipio = $municipio;

        $user = Auth::user();
        if (!$user || (!$user->temPermissao('requalificacao.ver') && !$user->temPermissao('sona.ver') && !$user->temPermissao('outros_projectos.ver'))) {
            abort(403, 'Não tem permissão para aceder aos projectos de requalificação.');
        }
    }

    public function render()
    {
        $user = Auth::user();
        $podeCriarSona = $user ? ($user->temPermissao('sona.criar') || $user->temPermissao('instrumentos.criar')) : false;
        $podeCriarOutros = $user ? ($user->temPermissao('outros_projectos.criar') || $user->temPermissao('instrumentos.criar')) : false;

        $sonaQuery = ProjectoSona::query()->where('municipio_id', $this->municipio->id);
        $outrosQuery = OutroProjecto::query()->where('municipio_id', $this->municipio->id);

        $totalSona = (clone $sonaQuery)->count();
        $sonaEmCurso = (clone $sonaQuery)->where('estado', 'em_curso')->count();
        $sonaConcluidos = (clone $sonaQuery)->where('estado', 'concluido')->count();
        $sonaAvancos = (clone $sonaQuery)->where('tipo', 'avancos_obras')->count();

        $totalOutros = (clone $outrosQuery)->count();
        $outrosEmCurso = (clone $outrosQuery)->where('estado', 'em_curso')->count();
        $outrosConcluidos = (clone $outrosQuery)->where('estado', 'concluido')->count();
        $outrosRequalificacao = (clone $outrosQuery)->where('tipo', 'requalificacao')->count();
        $outrosInfraestrutura = (clone $outrosQuery)->where('tipo', 'infraestrutura')->count();
        $orcamentoTotalOutros = (clone $outrosQuery)->sum('orcamento');

        $ultimosSona = (clone $sonaQuery)->latest()->take(3)->get();
        $ultimosOutros = (clone $outrosQuery)->latest()->take(3)->get();

        return view('livewire.projectos-requalificacao', [
            'municipio' => $this->municipio,
            'podeCriarSona' => $podeCriarSona,
            'podeCriarOutros' => $podeCriarOutros,
            'stats' => [
                'total_geral' => $totalSona + $totalOutros,
                'total_em_curso' => $sonaEmCurso + $outrosEmCurso,
                'total_concluidos' => $sonaConcluidos + $outrosConcluidos,
                'sona' => $totalSona,
                'sona_em_curso' => $sonaEmCurso,
                'sona_concluidos' => $sonaConcluidos,
                'sona_avancos' => $sonaAvancos,
                'outros' => $totalOutros,
                'outros_em_curso' => $outrosEmCurso,
                'outros_concluidos' => $outrosConcluidos,
                'outros_requalificacao' => $outrosRequalificacao,
                'outros_infraestrutura' => $outrosInfraestrutura,
                'orcamento_total_outros' => $orcamentoTotalOutros,
            ],
            'ultimosSona' => $ultimosSona,
            'ultimosOutros' => $ultimosOutros,
        ]);
    }
}

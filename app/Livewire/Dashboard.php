<?php

namespace App\Livewire;

use App\Models\Croqui;
use App\Models\Instrumento;
use App\Models\Loteamento;
use App\Models\Municipio;
use App\Models\NationalAuditLog;
use App\Models\Notificacao;
use App\Models\OperacaoUrbanistica;
use App\Models\OutroProjecto;
use App\Models\ProjectoSona;
use App\Models\Provincia;
use App\Models\SyncLog;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PerspetivaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class Dashboard extends Component
{
    #[On('perspetiva-alterada')]
    public function onPerspetivaAlterada(): void
    {
        // Força recarregamento dos dados quando a perspetiva mudar
    }

    public function definirPerspetiva(string $modo, ?int $provinciaId = null): void
    {
        $service = app(PerspetivaService::class);
        if (! $service->isSuperAdmin()) {
            return;
        }

        $service->definirModo($modo, $provinciaId);
        $this->dispatch('toast', type: 'info', message: 'Perspetiva atualizada para ' . $service->label());
        $this->dispatch('perspetiva-alterada', modo: $modo, provinciaId: $provinciaId);
    }

    public function render()
    {
        $user = auth()->user();
        $user->loadMissing(['roles', 'provincia', 'municipio']);

        $perspetivaService = app(PerspetivaService::class);
        $isSuperAdmin = $user->isSuperAdminNacional();

        $perspetivaInfo = [
            'ativo' => $isSuperAdmin,
            'modo' => $isSuperAdmin ? $perspetivaService->getModo() : null,
            'provincia' => $isSuperAdmin && $perspetivaService->isProvincial() ? $perspetivaService->getProvincia() : null,
            'label' => $isSuperAdmin ? $perspetivaService->label() : null,
        ];

        // Scoped queries based on user level and superadmin perspective
        $municipiosQuery = Municipio::query()->where('activo', true)->orderBy('nome');
        $instrumentosQuery = Instrumento::query();
        $loteamentosQuery = Loteamento::query();
        $operacoesQuery = OperacaoUrbanistica::query();
        $sonaQuery = ProjectoSona::query();
        $outrosProjQuery = OutroProjecto::query();

        // 1. Superadmin com Modo Provincial ativo
        if ($isSuperAdmin && $perspetivaService->isProvincial() && $perspetivaInfo['provincia']) {
            $provinciaId = $perspetivaInfo['provincia']->id;
            $municipiosProvinciaIds = Municipio::where('provincia_id', $provinciaId)->pluck('id');
            $municipiosQuery->where('provincia_id', $provinciaId);
            $instrumentosQuery->whereIn('municipio_id', $municipiosProvinciaIds);
            $loteamentosQuery->whereIn('municipio_id', $municipiosProvinciaIds);
            $operacoesQuery->whereIn('municipio_id', $municipiosProvinciaIds);
            $sonaQuery->whereIn('municipio_id', $municipiosProvinciaIds);
            $outrosProjQuery->whereIn('municipio_id', $municipiosProvinciaIds);
        }
        // 2. Municipal/Técnico: apenas o seu município
        elseif ($user->isMunicipal()) {
            $municipiosQuery->where('id', $user->municipio_id);
            $instrumentosQuery->where('municipio_id', $user->municipio_id);
            $loteamentosQuery->where('municipio_id', $user->municipio_id);
            $operacoesQuery->where('municipio_id', $user->municipio_id);
            $sonaQuery->where('municipio_id', $user->municipio_id);
            $outrosProjQuery->where('municipio_id', $user->municipio_id);
        }
        // 3. Provincial: apenas municípios da sua província
        elseif ($user->isProvincial()) {
            $municipiosProvinciaIds = Municipio::where('provincia_id', $user->provincia_id)->pluck('id');
            $municipiosQuery->where('provincia_id', $user->provincia_id);
            $instrumentosQuery->whereIn('municipio_id', $municipiosProvinciaIds);
            $loteamentosQuery->whereIn('municipio_id', $municipiosProvinciaIds);
            $operacoesQuery->whereIn('municipio_id', $municipiosProvinciaIds);
            $sonaQuery->whereIn('municipio_id', $municipiosProvinciaIds);
            $outrosProjQuery->whereIn('municipio_id', $municipiosProvinciaIds);
        }
        // 4. Modo Global / Nacional / INOTU: todas as províncias e municípios (sem restrição local)

        $municipios = $municipiosQuery->get();
        $municipiosIds = $municipios->pluck('id');

        // Totais e Métricas Globais
        $totalMunicipios = $municipios->count();
        $totalInstrumentos = (clone $instrumentosQuery)->count();
        $totalLoteamentos = (clone $loteamentosQuery)->count();
        $totalOperacoes = (clone $operacoesQuery)->count();
        $totalSona = (clone $sonaQuery)->count();
        $totalOutros = (clone $outrosProjQuery)->count();
        $orcamentoTotalOutros = (clone $outrosProjQuery)->sum('orcamento');

        // Total de utilizadores por escopo
        $totalUsuarios = 0;
        if ($isSuperAdmin && $perspetivaService->isProvincial() && $perspetivaInfo['provincia']) {
            $totalUsuarios = User::where('activo', true)->where('provincia_id', $perspetivaInfo['provincia']->id)->count();
        } elseif ($user->isInotu() || $user->isNacional() || $isSuperAdmin) {
            $totalUsuarios = User::where('activo', true)->count();
        }

        $stats = [
            'total_municipios' => $totalMunicipios,
            'total_instrumentos' => $totalInstrumentos,
            'total_loteamentos' => $totalLoteamentos,
            'total_operacoes' => $totalOperacoes,
            'total_sona' => $totalSona,
            'total_outros' => $totalOutros,
            'orcamento_total' => $orcamentoTotalOutros,
            'total_usuarios' => $totalUsuarios,
            'notificacoes_nao_lidas' => Notificacao::where('user_id', $user->id)->where('lida', false)->count(),
        ];

        // Métricas do Núcleo Nacional (quando em Visão Nacional)
        $metricasNacionais = null;
        if ($isSuperAdmin && $perspetivaInfo['modo'] === PerspetivaService::MODO_NACIONAL) {
            $metricasNacionais = [
                'total_nodes' => Tenant::count(),
                'active_nodes' => Tenant::where('is_active', true)->count(),
                'online_nodes' => Tenant::where('sync_status', Tenant::SYNC_ONLINE)->count(),
                'total_syncs' => SyncLog::count(),
                'successful_syncs' => SyncLog::where('status', SyncLog::STATUS_SUCCESS)->count(),
                'failed_syncs' => SyncLog::where('status', SyncLog::STATUS_FAILED)->count(),
                'total_audits' => NationalAuditLog::count(),
            ];
        }

        // ─── 1. Gráfico de Distribuição por Município (Top 8 Municípios) ───
        $distribuicaoMunicipios = [];
        foreach ($municipios->take(8) as $m) {
            $instCount = Instrumento::where('municipio_id', $m->id)->count();
            $loteCount = Loteamento::where('municipio_id', $m->id)->count();
            $projCount = ProjectoSona::where('municipio_id', $m->id)->count() + OutroProjecto::where('municipio_id', $m->id)->count();
            $distribuicaoMunicipios[] = [
                'nome' => $m->nome,
                'instrumentos' => $instCount,
                'loteamentos' => $loteCount,
                'projectos' => $projCount,
                'total' => $instCount + $loteCount + $projCount,
            ];
        }
        usort($distribuicaoMunicipios, fn ($a, $b) => $b['total'] <=> $a['total']);

        // ─── 2. Gráfico de Estados dos Instrumentos de Ordenamento ───
        $estadosInstrumentos = [
            'em_elaboracao' => (clone $instrumentosQuery)->where('estado', 'em_elaboracao')->count(),
            'aprovado' => (clone $instrumentosQuery)->where('estado', 'aprovado')->count(),
            'em_revisao' => (clone $instrumentosQuery)->where('estado', 'em_revisao')->count(),
            'revogado' => (clone $instrumentosQuery)->where('estado', 'revogado')->count(),
        ];

        // ─── 3. Gráfico de Projectos de Requalificação (Tipos) ───
        $tiposProjectos = [
            'sona_requalificacao' => (clone $sonaQuery)->where('tipo', 'requalificacao')->count(),
            'sona_avancos' => (clone $sonaQuery)->where('tipo', 'avancos_obras')->count(),
            'outros_infraestrutura' => (clone $outrosProjQuery)->where('tipo', 'infraestrutura')->count(),
            'outros_social' => (clone $outrosProjQuery)->where('tipo', 'social')->count(),
            'outros_geral' => (clone $outrosProjQuery)->whereIn('tipo', ['requalificacao', 'outro'])->count(),
        ];

        // ─── 4. Tendência Mensal nos Últimos 6 Meses ───
        $mesesLabels = [];
        $instrumentosMensais = [];
        $projectosMensais = [];

        for ($i = 5; $i >= 0; $i--) {
            $dataMes = Carbon::now()->subMonths($i);
            $mesNome = $dataMes->locale('pt')->isoFormat('MMM/YY');
            $mesesLabels[] = ucfirst($mesNome);

            $inicioMes = $dataMes->copy()->startOfMonth();
            $fimMes = $dataMes->copy()->endOfMonth();

            $instMes = (clone $instrumentosQuery)
                ->whereBetween('created_at', [$inicioMes, $fimMes])
                ->count();
            $instrumentosMensais[] = $instMes;

            $sonaMes = (clone $sonaQuery)
                ->whereBetween('created_at', [$inicioMes, $fimMes])
                ->count();
            $outrosMes = (clone $outrosProjQuery)
                ->whereBetween('created_at', [$inicioMes, $fimMes])
                ->count();
            $projectosMensais[] = $sonaMes + $outrosMes;
        }

        // ─── Registos Recentes ───
        $ultimosInstrumentos = (clone $instrumentosQuery)->with('municipio')->latest()->take(5)->get();
        $ultimosProjectosSona = (clone $sonaQuery)->with('municipio')->latest()->take(5)->get();
        $ultimosOutrosProjectos = (clone $outrosProjQuery)->with('municipio')->latest()->take(5)->get();

        return view('livewire.dashboard', [
            'municipios' => $municipios,
            'user' => $user,
            'stats' => $stats,
            'perspetivaInfo' => $perspetivaInfo,
            'metricasNacionais' => $metricasNacionais,
            'todasProvincias' => Provincia::where('activo', true)->orderBy('nome')->get(),
            'chartMunicipios' => [
                'labels' => array_column($distribuicaoMunicipios, 'nome'),
                'instrumentos' => array_column($distribuicaoMunicipios, 'instrumentos'),
                'loteamentos' => array_column($distribuicaoMunicipios, 'loteamentos'),
                'projectos' => array_column($distribuicaoMunicipios, 'projectos'),
            ],
            'chartEstadosInstrumentos' => [
                'labels' => ['Em Elaboração', 'Aprovado', 'Em Revisão', 'Revogado'],
                'data' => [
                    $estadosInstrumentos['em_elaboracao'],
                    $estadosInstrumentos['aprovado'],
                    $estadosInstrumentos['em_revisao'],
                    $estadosInstrumentos['revogado'],
                ],
            ],
            'chartTiposProjectos' => [
                'labels' => ['SONA Requalificação', 'SONA Avanços de Obra', 'Infraestruturas', 'Equipamentos Sociais', 'Outros'],
                'data' => [
                    $tiposProjectos['sona_requalificacao'],
                    $tiposProjectos['sona_avancos'],
                    $tiposProjectos['outros_infraestrutura'],
                    $tiposProjectos['outros_social'],
                    $tiposProjectos['outros_geral'],
                ],
            ],
            'chartTendencia' => [
                'labels' => $mesesLabels,
                'instrumentos' => $instrumentosMensais,
                'projectos' => $projectosMensais,
            ],
            'ultimosInstrumentos' => $ultimosInstrumentos,
            'ultimosProjectosSona' => $ultimosProjectosSona,
            'ultimosOutrosProjectos' => $ultimosOutrosProjectos,
        ]);
    }
}

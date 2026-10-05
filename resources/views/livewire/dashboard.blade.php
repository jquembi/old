<div class="px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-5 sm:space-y-8" x-data="dashboardCharts(@js($chartMunicipios), @js($chartEstadosInstrumentos), @js($chartTiposProjectos), @js($chartTendencia))">

    @php
        $territorioLabel = 'Território Nacional';
        if ($perspetivaInfo['ativo']) {
            if ($perspetivaInfo['modo'] === 'provincial' && $perspetivaInfo['provincia']) {
                $territorioLabel = 'Província de ' . $perspetivaInfo['provincia']->nome;
            } elseif ($perspetivaInfo['modo'] === 'nacional') {
                $territorioLabel = 'Núcleo Central Nacional';
            } else {
                $territorioLabel = 'Âmbito Nacional (Todas as Províncias)';
            }
            $contextLabel = 'Superadmin • ' . ($perspetivaInfo['label'] ?? 'Global');
        } else {
            $contextLabel = $user->isInotu()
                ? 'INOTU'
                : ($user->isNacional() ? 'Nacional' : ($user->isProvincial() ? ($user->provincia?->nome ?? 'Provincial') : ($user->municipio?->nome ?? 'Municipal')));
            $territorioLabel = $user->isProvincial()
                ? 'Província de ' . ($user->provincia?->nome ?? 'Provincial')
                : ($user->isMunicipal() ? 'Município de ' . ($user->municipio?->nome ?? 'Municipal') : 'Território Nacional');
        }
        $firstName = \Illuminate\Support\Str::of($user->name)->explode(' ')->first();
    @endphp

    {{-- SECTION: Cabeçalho / Saudação --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-zinc-200 bg-gradient-to-r from-red-600/10 via-amber-500/5 to-transparent p-4 sm:p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 sm:h-14 sm:w-14 items-center justify-center rounded-xl sm:rounded-2xl bg-red-600 text-white shadow-lg shadow-red-600/20 shrink-0">
                <flux:icon.building-office-2 class="h-7 w-7" />
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-800 dark:bg-red-900/40 dark:text-red-300">
                        G.TERRAS &bull; {{ $contextLabel }}
                    </span>
                    @if($perspetivaInfo['ativo'])
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $perspetivaInfo['modo'] === 'global' ? 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300' : ($perspetivaInfo['modo'] === 'nacional' ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300') }}">
                            Modo: {{ ucfirst($perspetivaInfo['modo']) }}
                        </span>
                    @endif
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-white mt-1">
                    Olá, {{ $firstName }}
                </h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 capitalize">
                    {{ now()->locale('pt')->isoFormat('dddd, D [de] MMMM [de] YYYY') }} &bull; {{ $territorioLabel }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('municipios.index') }}" wire:navigate>
                <flux:button variant="primary" icon="map" size="sm" class="bg-red-600 hover:bg-red-700 text-white">
                    Ver Municípios
                </flux:button>
            </a>
            @if($user->isInotu() || $user->isNacional() || $user->temPermissao('auditoria.ver'))
                <a href="{{ route('auditoria.index') }}" wire:navigate>
                    <flux:button variant="subtle" icon="shield-check" size="sm">Auditoria</flux:button>
                </a>
            @endif
        </div>
    </div>

    {{-- SECTION: Banner Informativo do Modo de Perspetiva (Superadmin) --}}
    @if($perspetivaInfo['ativo'])
        @if($perspetivaInfo['modo'] === 'provincial' && $perspetivaInfo['provincia'])
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/30">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-md shadow-emerald-600/20">
                            <flux:icon.map-pin class="h-5 w-5" />
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Modo de Perspetiva Ativo</span>
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                    Visão por Província
                                </span>
                            </div>
                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mt-0.5">
                                A visualizar exclusivamente os municípios e operações da <span class="text-emerald-700 dark:text-emerald-300 font-bold">Província de {{ $perspetivaInfo['provincia']->nome }}</span> ({{ $stats['total_municipios'] }} municípios)
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                        <div class="relative" x-data="{ openProv: false }">
                            <flux:button size="sm" variant="subtle" icon="arrows-right-left" @click="openProv = !openProv" class="text-xs font-medium">
                                Mudar Província
                            </flux:button>
                            <div x-show="openProv" @click.outside="openProv = false" class="absolute right-0 sm:right-auto sm:left-0 lg:left-auto lg:right-0 mt-1 w-[calc(100vw-2.5rem)] max-w-xs sm:w-56 rounded-xl bg-white p-1.5 shadow-xl border border-zinc-200 dark:bg-zinc-900 dark:border-zinc-800 z-50 max-h-56 overflow-y-auto">
                                @foreach($todasProvincias as $p)
                                    <button
                                        type="button"
                                        wire:click="definirPerspetiva('provincial', {{ $p->id }})"
                                        @click="openProv = false"
                                        class="w-full flex items-center justify-between px-2.5 py-1.5 text-xs rounded-lg text-left transition-colors {{ $perspetivaInfo['provincia']->id === $p->id ? 'bg-emerald-600 text-white font-semibold' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800' }}"
                                    >
                                        <span>{{ $p->nome }}</span>
                                        <span class="text-[10px] opacity-75 uppercase">{{ $p->codigo }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="globe-alt" wire:click="definirPerspetiva('global')" class="text-xs font-medium">
                            Visão Global
                        </flux:button>
                        <flux:button size="sm" variant="ghost" icon="building-library" wire:click="definirPerspetiva('nacional')" class="text-xs font-medium">
                            Visão Nacional
                        </flux:button>
                    </div>
                </div>
            </div>
        @elseif($perspetivaInfo['modo'] === 'nacional')
            <div class="rounded-2xl border border-red-200 bg-red-50/70 p-5 shadow-sm dark:border-red-900/50 dark:bg-red-950/30 space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-600 text-white shadow-md shadow-red-600/20">
                            <flux:icon.building-library class="h-5 w-5" />
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-red-800 dark:text-red-300">Modo de Perspetiva Ativo</span>
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-800 dark:bg-red-900/60 dark:text-red-300">
                                    Visão Nacional &bull; Núcleo Central
                                </span>
                            </div>
                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mt-0.5">
                                Foco em coordenação nacional, supervisão de nós territoriais e governação do sistema.
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('nacional.dashboard') }}" wire:navigate>
                            <flux:button size="sm" variant="primary" icon="arrow-top-right-on-square" class="bg-red-600 hover:bg-red-700 text-white text-xs">
                                Dashboard Nacional
                            </flux:button>
                        </a>
                        <flux:button size="sm" variant="subtle" icon="globe-alt" wire:click="definirPerspetiva('global')" class="text-xs">
                            Visão Global
                        </flux:button>
                    </div>
                </div>

                {{-- Mini KPIs Nacionais --}}
                @if($metricasNacionais)
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 pt-3 border-t border-red-200/60 dark:border-red-900/40">
                        <div class="bg-white/80 dark:bg-zinc-900/80 rounded-xl p-3 border border-red-100 dark:border-red-900/30">
                            <span class="text-[10px] font-semibold text-zinc-500 uppercase tracking-wider">Total de Nodes</span>
                            <p class="text-lg font-bold text-zinc-900 dark:text-white mt-0.5">{{ $metricasNacionais['total_nodes'] }}</p>
                            <p class="text-[10px] text-zinc-500">{{ $metricasNacionais['active_nodes'] }} activos</p>
                        </div>
                        <div class="bg-white/80 dark:bg-zinc-900/80 rounded-xl p-3 border border-red-100 dark:border-red-900/30">
                            <span class="text-[10px] font-semibold text-zinc-500 uppercase tracking-wider">Nodes Online</span>
                            <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $metricasNacionais['online_nodes'] }}</p>
                            <p class="text-[10px] text-zinc-500">em comunicação activa</p>
                        </div>
                        <div class="bg-white/80 dark:bg-zinc-900/80 rounded-xl p-3 border border-red-100 dark:border-red-900/30">
                            <span class="text-[10px] font-semibold text-zinc-500 uppercase tracking-wider">Sincronizações</span>
                            <p class="text-lg font-bold text-zinc-900 dark:text-white mt-0.5">{{ $metricasNacionais['total_syncs'] }}</p>
                            <p class="text-[10px] text-emerald-600">{{ $metricasNacionais['successful_syncs'] }} com sucesso</p>
                        </div>
                        <div class="bg-white/80 dark:bg-zinc-900/80 rounded-xl p-3 border border-red-100 dark:border-red-900/30">
                            <span class="text-[10px] font-semibold text-zinc-500 uppercase tracking-wider">Auditorias</span>
                            <p class="text-lg font-bold text-zinc-900 dark:text-white mt-0.5">{{ $metricasNacionais['total_audits'] }}</p>
                            <p class="text-[10px] text-zinc-500">registos centrais</p>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="rounded-2xl border border-sky-200 bg-sky-50/60 p-4 shadow-sm dark:border-sky-900/40 dark:bg-sky-950/20">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-600 text-white shadow-md shadow-sky-600/20">
                            <flux:icon.globe-alt class="h-5 w-5" />
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-sky-800 dark:text-sky-300">Modo de Perspetiva Ativo</span>
                                <span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-800 dark:bg-sky-900/60 dark:text-sky-300">
                                    Visão Global
                                </span>
                            </div>
                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mt-0.5">
                                A visualizar o panorama completo de todas as 18 províncias e {{ $stats['total_municipios'] }} municípios de Angola.
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="relative" x-data="{ openProv: false }">
                            <flux:button size="sm" variant="subtle" icon="map-pin" @click="openProv = !openProv" class="text-xs font-medium">
                                Filtrar por Província
                            </flux:button>
                            <div x-show="openProv" @click.outside="openProv = false" class="absolute right-0 sm:right-auto sm:left-0 lg:left-auto lg:right-0 mt-1 w-[calc(100vw-2.5rem)] max-w-xs sm:w-56 rounded-xl bg-white p-1.5 shadow-xl border border-zinc-200 dark:bg-zinc-900 dark:border-zinc-800 z-50 max-h-56 overflow-y-auto">
                                @foreach($todasProvincias as $p)
                                    <button
                                        type="button"
                                        wire:click="definirPerspetiva('provincial', {{ $p->id }})"
                                        @click="openProv = false"
                                        class="w-full flex items-center justify-between px-2.5 py-1.5 text-xs rounded-lg text-left text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                    >
                                        <span>{{ $p->nome }}</span>
                                        <span class="text-[10px] opacity-75 uppercase">{{ $p->codigo }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="building-library" wire:click="definirPerspetiva('nacional')" class="text-xs font-medium">
                            Visão Nacional
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- SECTION: Métricas Principais (6 KPIs) --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 sm:grid-cols-3 xl:grid-cols-6">
        {{-- Municípios --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Municípios</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400">
                    <flux:icon.map-pin class="h-4 w-4" />
                </span>
            </div>
            <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total_municipios'] }}</p>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">activos no sistema</p>
        </div>

        {{-- Instrumentos --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Instrumentos</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                    <flux:icon.document-text class="h-4 w-4" />
                </span>
            </div>
            <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total_instrumentos'] }}</p>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">planos & ordenamento</p>
        </div>

        {{-- Loteamentos --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Loteamentos</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-yellow-50 text-yellow-600 dark:bg-yellow-950/40 dark:text-yellow-400">
                    <flux:icon.squares-2x2 class="h-4 w-4" />
                </span>
            </div>
            <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total_loteamentos'] }}</p>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">áreas aprovadas</p>
        </div>

        {{-- Projectos SONA --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Projecto SONA</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-50 text-teal-600 dark:bg-teal-950/40 dark:text-teal-400">
                    <flux:icon.building-office class="h-4 w-4" />
                </span>
            </div>
            <p class="text-2xl font-bold text-teal-600 dark:text-teal-400">{{ $stats['total_sona'] }}</p>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">planos & obras</p>
        </div>

        {{-- Outros Projectos --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Requalificação</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-50 text-cyan-600 dark:bg-cyan-950/40 dark:text-cyan-400">
                    <flux:icon.clipboard-document-list class="h-4 w-4" />
                </span>
            </div>
            <p class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">{{ $stats['total_outros'] }}</p>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">outros projectos</p>
        </div>

        {{-- Operações / Utilizadores --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Operações</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                    <flux:icon.cursor-arrow-rays class="h-4 w-4" />
                </span>
            </div>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_operacoes'] }}</p>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">croquis & terrenos</p>
        </div>
    </div>

    {{-- SECTION: Módulos de Acesso Rápido --}}
    <div>
        <h2 class="text-base font-bold text-zinc-900 dark:text-white mb-4">Módulos do Sistema</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Módulo Municípios --}}
            <a href="{{ route('municipios.index') }}" class="group block" wire:navigate>
                <div class="h-full rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-red-400 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-red-600">
                    <div class="flex items-start gap-3.5">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-400">
                            <flux:icon.map class="h-6 w-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Municípios</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 line-clamp-2">Gestão territorial, instrumentos e operações dos 14 municípios</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-1 text-xs font-semibold text-red-600 group-hover:gap-2 transition-all">
                        Aceder <flux:icon.arrow-right class="h-3.5 w-3.5" />
                    </div>
                </div>
            </a>

            {{-- Módulo Utilizadores --}}
            @if($user->isInotu() || $user->isNacional() || $user->isProvincial() || $user->isAdminMunicipal() || $user->temPermissao('usuarios.ver'))
            <a href="{{ route('usuarios.index') }}" class="group block" wire:navigate>
                <div class="h-full rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-zinc-400 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-600">
                    <div class="flex items-start gap-3.5">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                            <flux:icon.users class="h-6 w-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Utilizadores</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 line-clamp-2">Gestão de contas, atribuição de perfis e permissões</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-1 text-xs font-semibold text-zinc-700 dark:text-zinc-300 group-hover:gap-2 transition-all">
                        Aceder <flux:icon.arrow-right class="h-3.5 w-3.5" />
                    </div>
                </div>
            </a>
            @endif

            {{-- Módulo Auditoria --}}
            @if($user->isInotu() || $user->isNacional() || $user->temPermissao('auditoria.ver'))
            <a href="{{ route('auditoria.index') }}" class="group block" wire:navigate>
                <div class="h-full rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-400 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-amber-600">
                    <div class="flex items-start gap-3.5">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                            <flux:icon.shield-check class="h-6 w-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Auditoria & Logs</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 line-clamp-2">Rastreio de actividades, acessos e autenticidade QR</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-1 text-xs font-semibold text-amber-600 group-hover:gap-2 transition-all">
                        Aceder <flux:icon.arrow-right class="h-3.5 w-3.5" />
                    </div>
                </div>
            </a>
            @endif

            {{-- Módulo Portal de Serviços Públicos --}}
            <a href="{{ route('portal.servicos-publicos') }}" class="group block" target="_blank">
                <div class="h-full rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-400 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-blue-600">
                    <div class="flex items-start gap-3.5">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                            <flux:icon.globe-alt class="h-6 w-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Portal Público</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 line-clamp-2">Consulta de autenticidade documental e serviços aos cidadãos</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-1 text-xs font-semibold text-blue-600 group-hover:gap-2 transition-all">
                        Abrir Portal <flux:icon.arrow-top-right-on-square class="h-3.5 w-3.5" />
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- SECTION: GRÁFICOS E CHARTS REAIS 1 --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Gráfico 1: Processos e Instrumentos por Município (2 Colunas) --}}
        <div class="lg:col-span-2 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 pb-4 dark:border-zinc-800">
                <div>
                    <h3 class="text-base font-bold text-zinc-900 dark:text-white">Distribuição Territorial por Município</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Volume de Instrumentos, Loteamentos e Projectos nos principais municípios</p>
                </div>
                <span class="inline-flex items-center rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    Dados Reais
                </span>
            </div>

            <div class="mt-4 h-72 w-full relative">
                <canvas id="chartMunicipiosCanvas" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- Gráfico 2: Instrumentos por Estado (Doughnut Chart) --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 flex flex-col justify-between">
            <div class="border-b border-zinc-100 pb-4 dark:border-zinc-800">
                <h3 class="text-base font-bold text-zinc-900 dark:text-white">Estado dos Instrumentos</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Fases de elaboração e homologação</p>
            </div>

            <div class="my-auto py-4 h-64 w-full relative flex items-center justify-center">
                <canvas id="chartEstadosCanvas" class="max-h-60"></canvas>
            </div>

            <div class="grid grid-cols-2 gap-2 text-center text-xs pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <div>
                    <span class="text-zinc-400 block text-[10px]">Em Elaboração</span>
                    <span class="font-bold text-amber-600">{{ $chartEstadosInstrumentos['data'][0] ?? 0 }}</span>
                </div>
                <div>
                    <span class="text-zinc-400 block text-[10px]">Aprovados</span>
                    <span class="font-bold text-emerald-600">{{ $chartEstadosInstrumentos['data'][1] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION: GRÁFICOS E CHARTS REAIS 2 --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Gráfico 3: Tendência Semestral (Linha / Área) --}}
        <div class="lg:col-span-2 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 pb-4 dark:border-zinc-800">
                <div>
                    <h3 class="text-base font-bold text-zinc-900 dark:text-white">Evolução Mensal de Registos</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Dinâmica de submissão de instrumentos de planeamento e projectos nos últimos 6 meses</p>
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs text-zinc-500">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-red-600"></span> Instrumentos
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-teal-500 ml-2"></span> Projectos
                </span>
            </div>

            <div class="mt-4 h-64 w-full relative">
                <canvas id="chartTendenciaCanvas" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- Gráfico 4: Tipos de Projectos de Requalificação --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 flex flex-col justify-between">
            <div class="border-b border-zinc-100 pb-4 dark:border-zinc-800">
                <h3 class="text-base font-bold text-zinc-900 dark:text-white">Tipologia de Projectos</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Distribuição das intervenções urbanas</p>
            </div>

            <div class="my-auto py-4 h-64 w-full relative flex items-center justify-center">
                <canvas id="chartTiposCanvas" class="max-h-60"></canvas>
            </div>

            <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 text-center">
                <span class="text-xs text-zinc-500">Orçamento Total Estimado:</span>
                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 block">
                    {{ number_format($stats['orcamento_total'], 2, ',', '.') }} AOA
                </span>
            </div>
        </div>
    </div>

    {{-- SECTION: Actividade e Registos Recentes (Tabs) --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900" x-data="{ tab: 'instrumentos' }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-zinc-100 pb-4 dark:border-zinc-800 gap-3">
            <div>
                <h3 class="text-base font-bold text-zinc-900 dark:text-white">Registos Recentes no Sistema</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Últimas actualizações e processos submetidos</p>
            </div>
            <div class="flex items-center gap-1 rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800 overflow-x-auto max-w-full no-scrollbar">
                <button type="button" @click="tab = 'instrumentos'"
                        :class="tab === 'instrumentos' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900'"
                        class="rounded-lg px-3 py-1 text-xs font-semibold transition">
                    Instrumentos ({{ $ultimosInstrumentos->count() }})
                </button>
                <button type="button" @click="tab = 'sona'"
                        :class="tab === 'sona' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900'"
                        class="rounded-lg px-3 py-1 text-xs font-semibold transition">
                    Projectos SONA ({{ $ultimosProjectosSona->count() }})
                </button>
                <button type="button" @click="tab = 'outros'"
                        :class="tab === 'outros' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900'"
                        class="rounded-lg px-3 py-1 text-xs font-semibold transition">
                    Outros Projectos ({{ $ultimosOutrosProjectos->count() }})
                </button>
            </div>
        </div>

        {{-- Tab 1: Instrumentos Recentes --}}
        <div x-show="tab === 'instrumentos'" class="mt-4 divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse($ultimosInstrumentos as $inst)
                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 shrink-0">
                            <flux:icon.document-text class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $inst->designacao ?? $inst->nome }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $inst->municipio?->nome }} &bull; Ano: {{ $inst->ano ?? 'N/D' }} &bull; {{ $inst->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                    @if($inst->municipio)
                        <a href="{{ route('municipio.instrumentos.detalhe', [$inst->municipio, $inst->id]) }}" wire:navigate class="text-xs font-semibold text-red-600 hover:underline shrink-0">
                            Ver Detalhes
                        </a>
                    @endif
                </div>
            @empty
                <p class="text-xs text-zinc-500 py-4 text-center">Nenhum instrumento recente.</p>
            @endforelse
        </div>

        {{-- Tab 2: SONA Recentes --}}
        <div x-show="tab === 'sona'" class="mt-4 divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse($ultimosProjectosSona as $sona)
                <div class="py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-50 text-teal-600 dark:bg-teal-950/40 dark:text-teal-400 shrink-0">
                            <flux:icon.building-office class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $sona->titulo }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $sona->municipio?->nome }} &bull; {{ $sona->tipo === 'avancos_obras' ? 'Avanços de Obra' : 'Requalificação' }} &bull; {{ $sona->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                    @if($sona->municipio)
                        <a href="{{ route('municipio.requalificacao.sona', $sona->municipio) }}" wire:navigate class="text-xs font-semibold text-teal-600 hover:underline shrink-0">
                            Ver no Módulo
                        </a>
                    @endif
                </div>
            @empty
                <p class="text-xs text-zinc-500 py-4 text-center">Nenhum projecto SONA recente.</p>
            @endforelse
        </div>

        {{-- Tab 3: Outros Projectos Recentes --}}
        <div x-show="tab === 'outros'" class="mt-4 divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse($ultimosOutrosProjectos as $outro)
                <div class="py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-50 text-cyan-600 dark:bg-cyan-950/40 dark:text-cyan-400 shrink-0">
                            <flux:icon.clipboard-document-list class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $outro->titulo }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $outro->municipio?->nome }} &bull; {{ ucfirst($outro->tipo) }} &bull; {{ $outro->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                    @if($outro->municipio)
                        <a href="{{ route('municipio.requalificacao.outros', $outro->municipio) }}" wire:navigate class="text-xs font-semibold text-cyan-600 hover:underline shrink-0">
                            Ver no Módulo
                        </a>
                    @endif
                </div>
            @empty
                <p class="text-xs text-zinc-500 py-4 text-center">Nenhum outro projecto recente.</p>
            @endforelse
        </div>
    </div>
</div>

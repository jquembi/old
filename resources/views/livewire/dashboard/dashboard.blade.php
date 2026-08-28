<div>
    @section('page-title', 'Dashboard')

    <div class="space-y-6">
        {{-- Welcome Banner --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-800 via-slate-900 to-slate-800 p-6 sm:p-8">
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" viewBox="0 0 800 200" fill="none"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="0.5" class="text-emerald-400"/></pattern></defs><rect width="800" height="200" fill="url(#grid)"/></svg>
            </div>
            <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-white">
                        @php
                            $hour = now()->hour;
                            $greeting = $hour < 12 ? 'Bom dia' : ($hour < 18 ? 'Boa tarde' : 'Boa noite');
                        @endphp
                        {{ $greeting }}, {{ explode(' ', $user->name)[0] }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">
                        Painel de gestao territorial da provincia do Huila
                        <span class="inline-flex items-center ml-1 gap-1"><span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span><span class="text-emerald-400 text-xs font-medium">Online</span></span>
                    </p>
                </div>
                <a href="{{ route('croquis.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-semibold rounded-xl shadow-lg shadow-emerald-500/25 transition shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Novo Croqui
                </a>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4">
            @php
                $stats = [
                    ['label' => 'Municipios', 'value' => $municipiosCount, 'color' => 'blue', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-7 0H5m0 0a2 2 0 01-2-2v-4.5M12 9v2m0 4h.01"/>', 'route' => null],
                    ['label' => 'Croquis', 'value' => $croquisCount, 'color' => 'emerald', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>', 'route' => 'croquis.index'],
                    ['label' => 'Instrumentos', 'value' => $instrumentosCount, 'color' => 'amber', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>', 'route' => 'instrumentos-ordenamento.index'],
                    ['label' => 'Terrenos', 'value' => $terrenosCount, 'color' => 'orange', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>', 'route' => 'terrenos-ociosos.index'],
                    ['label' => 'Pareceres', 'value' => $pareceresCount, 'color' => 'violet', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>', 'route' => 'pareceres.index'],
                    ['label' => 'Operacoes', 'value' => $operacoesCount, 'color' => 'rose', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/>', 'route' => 'operacoes.index'],
                ];
            @endphp
            @foreach($stats as $stat)
                @php
                    $colorMap = [
                        'blue' => ['bg' => 'bg-blue-50 dark:bg-blue-900/20', 'icon' => 'text-blue-500 dark:text-blue-400', 'ring' => 'ring-blue-100 dark:ring-blue-800/30'],
                        'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'icon' => 'text-emerald-500 dark:text-emerald-400', 'ring' => 'ring-emerald-100 dark:ring-emerald-800/30'],
                        'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'icon' => 'text-amber-500 dark:text-amber-400', 'ring' => 'ring-amber-100 dark:ring-amber-800/30'],
                        'orange' => ['bg' => 'bg-orange-50 dark:bg-orange-900/20', 'icon' => 'text-orange-500 dark:text-orange-400', 'ring' => 'ring-orange-100 dark:ring-orange-800/30'],
                        'violet' => ['bg' => 'bg-violet-50 dark:bg-violet-900/20', 'icon' => 'text-violet-500 dark:text-violet-400', 'ring' => 'ring-violet-100 dark:ring-violet-800/30'],
                        'rose' => ['bg' => 'bg-rose-50 dark:bg-rose-900/20', 'icon' => 'text-rose-500 dark:text-rose-400', 'ring' => 'ring-rose-100 dark:ring-rose-800/30'],
                    ];
                    $c = $colorMap[$stat['color']];
                @endphp
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/50 p-4 hover:shadow-md hover:shadow-slate-200/50 dark:hover:shadow-slate-900/50 transition-all group">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-9 h-9 {{ $c['bg'] }} rounded-lg ring-1 {{ $c['ring'] }} flex items-center justify-center">
                            <svg class="w-[18px] h-[18px] {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $stat['icon'] !!}</svg>
                        </div>
                        @if($stat['route'])
                        <a href="{{ route($stat['route']) }}" class="opacity-0 group-hover:opacity-100 transition p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        @endif
                    </div>
                    <p class="text-2xl font-bold text-slate-800 dark:text-white tabular-nums">{{ number_format($stat['value']) }}</p>
                    <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
            {{-- Quick Actions --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/50 p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 bg-slate-100 dark:bg-slate-700/60 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Acoes Rapidas</h3>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    @php
                        $actions = [
                            ['route' => 'croquis.create', 'label' => 'Novo Croqui', 'color' => 'blue', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                            ['route' => 'instrumentos-ordenamento.create', 'label' => 'Novo Instrumento', 'color' => 'emerald', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
                            ['route' => 'terrenos-ociosos.create', 'label' => 'Novo Terreno', 'color' => 'amber', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/>'],
                            ['route' => 'pareceres.create', 'label' => 'Novo Parecer', 'color' => 'violet', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>'],
                        ];
                        $actionColors = [
                            'blue' => 'bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-800/30',
                            'emerald' => 'bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/30',
                            'amber' => 'bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-800/30',
                            'violet' => 'bg-violet-50 dark:bg-violet-900/20 hover:bg-violet-100 dark:hover:bg-violet-900/30 text-violet-600 dark:text-violet-400 border-violet-100 dark:border-violet-800/30',
                        ];
                    @endphp
                    @foreach($actions as $action)
                        <a href="{{ route($action['route']) }}"
                           class="flex flex-col items-center gap-2 p-3.5 rounded-xl border transition-all {{ $actionColors[$action['color']] }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $action['icon'] !!}</svg>
                            <span class="text-[11px] font-semibold text-center leading-tight">{{ $action['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Recent Croquis --}}
            <div class="xl:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/50">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700/50">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Croquis Recentes</h3>
                    </div>
                    <a href="{{ route('croquis.index') }}" class="text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:underline">Ver todos</a>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700/40">
                    @forelse($recentCroquis as $croqui)
                        <a href="{{ route('croquis.show', $croqui) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition group">
                            <div class="w-9 h-9 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-200 truncate group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition">
                                    {{ $croqui->proprietario }}
                                </p>
                                <p class="text-[11px] text-slate-400 truncate">
                                    {{ $croqui->municipio?->nome ?? '-' }} &middot; {{ $croqui->endereco ?? 'Sem endereco' }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                @php
                                    $statusColors = [
                                        'pendente' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                        'aprovado' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                        'rejeitado' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    ];
                                @endphp
                                <span class="inline-flex text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $statusColors[$croqui->status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}">
                                    {{ ucfirst($croqui->status ?? 'N/A') }}
                                </span>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $croqui->created_at?->diffForHumans() }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <svg class="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <p class="text-sm text-slate-400">Nenhum croqui registado</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Download Desktop App --}}
        <div class="bg-gradient-to-br from-slate-800 via-slate-900 to-slate-800 rounded-xl overflow-hidden">
            <div class="relative px-5 py-5 sm:py-6 flex flex-col sm:flex-row sm:items-center gap-5">
                <div class="absolute inset-0 opacity-5">
                    <svg class="w-full h-full" viewBox="0 0 800 120" fill="none"><defs><pattern id="grid2" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="0.5" class="text-emerald-400"/></pattern></defs><rect width="800" height="120" fill="url(#grid2)"/></svg>
                </div>
                <div class="relative flex items-center gap-4 flex-1 min-w-0">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-white">Aplicação Desktop — G.TERRAS</p>
                        <p class="text-xs text-slate-400 mt-0.5">Versão 1.0.0 · Windows 10/11 (64-bit)</p>
                    </div>
                </div>
                <div class="relative flex items-center gap-3 flex-shrink-0">
                    <a href="{{ route('downloads.desktop', 'installer') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-white text-xs font-semibold rounded-lg shadow shadow-emerald-500/30 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Instalador (83 MB)
                    </a>
                    <a href="{{ route('downloads.desktop', 'portable') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Portátil (83 MB)
                    </a>
                </div>
            </div>
        </div>

        {{-- Map Section --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/50 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700/50">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.553-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Mapa Territorial — Huila</h3>
                </div>
                <a href="{{ route('croquis.index') }}" class="text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:underline">Ver todos os croquis</a>
            </div>
            <livewire:dashboard.mapa-huila />
        </div>
    </div>
</div>

<div>
    {{-- ═══════════════════════════════════════════════════════════
         ECRÃ DE LOGIN
    ════════════════════════════════════════════════════════════ --}}
    @if(! $autenticado)
    <div class="flex min-h-screen items-center justify-center bg-zinc-950 px-4">
        <div class="w-full max-w-md">
            {{-- Logo / Branding --}}
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-red-600 shadow-lg shadow-red-500/20">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-white">Painel do Sistema</h1>
                <p class="mt-2 text-sm text-zinc-500">INOTU G.TERRAS — Área Reservada</p>
            </div>

            {{-- Card de Login --}}
            <div class="rounded-2xl border border-zinc-800/50 bg-zinc-900/80 p-8 shadow-2xl backdrop-blur-sm">
                <div class="space-y-5">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-zinc-500">Token de Acesso</label>
                        <input type="password" wire:model="token" wire:keydown.enter="verificarToken"
                            placeholder="••••••"
                            autofocus
                            class="w-full rounded-xl border border-zinc-700/50 bg-zinc-800/50 px-4 py-3.5 text-center text-lg tracking-[0.3em] text-white placeholder-zinc-600 transition focus:border-red-500/50 focus:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-500/20" />
                    </div>
                    <button wire:click="verificarToken" wire:loading.attr="disabled"
                        class="group relative w-full overflow-hidden rounded-xl bg-gradient-to-r from-red-600 to-red-500 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-red-500/20 transition-all hover:shadow-red-500/30 hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-zinc-900 disabled:opacity-50">
                        <span wire:loading.remove wire:target="verificarToken">Autenticar</span>
                        <span wire:loading wire:target="verificarToken" class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            A verificar...
                        </span>
                    </button>
                </div>

                @if($tokenError)
                    <div class="mt-5 flex items-center gap-2 rounded-xl border border-red-900/50 bg-red-950/50 p-3.5 text-sm text-red-400">
                        <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        {{ $tokenError }}
                    </div>
                @endif
            </div>

            <p class="mt-6 text-center text-xs text-zinc-600">Acesso restrito a administradores</p>
        </div>
    </div>

    @else
    {{-- ═══════════════════════════════════════════════════════════
         DASHBOARD PRINCIPAL COM SIDEBAR
    ════════════════════════════════════════════════════════════ --}}
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

        {{-- ──── Overlay mobile ──────────────────────────── --}}
        <div x-show="sidebarOpen" x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden"
            style="display: none;"></div>

        {{-- ──── Sidebar ──────────────────────────────────── --}}
        <aside x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col border-r border-zinc-800/50 bg-zinc-900 transition-transform duration-200 ease-in-out lg:relative lg:translate-x-0 lg:bg-zinc-900/50">
            {{-- Brand --}}
            <div class="flex h-16 items-center justify-between border-b border-zinc-800/50 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-red-500 to-red-600">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white">Painel Sistema</p>
                        <p class="text-[10px] uppercase tracking-widest text-zinc-500">INOTU G.TERRAS</p>
                    </div>
                </div>
                {{-- Fechar sidebar (mobile) --}}
                <button @click="sidebarOpen = false" class="rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white lg:hidden">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 painel-scrollbar">
                @php
                    $menuItems = [
                        ['id' => 'visao-geral', 'label' => 'Visão Geral', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
                        ['id' => 'migracoes', 'label' => 'Migrações', 'icon' => 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125'],
                        ['id' => 'seeders', 'label' => 'Seeders', 'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
                        ['id' => 'cache', 'label' => 'Cache & Optimização', 'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z|M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                        ['id' => 'tabelas', 'label' => 'Base de Dados', 'icon' => 'M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M12 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 0v.75'],
                        ['id' => 'api', 'label' => 'API & Controllers', 'icon' => 'M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5'],
                        ['id' => 'rotas', 'label' => 'Rotas da API', 'icon' => 'M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m9.193-4.818a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244'],
                        ['id' => 'logs', 'label' => 'Logs', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z'],
                        ['id' => 'terminal', 'label' => 'Terminal Artisan', 'icon' => 'M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z'],
                    ];
                @endphp

                @foreach($menuItems as $item)
                    <button wire:click="$set('secaoAtiva', '{{ $item['id'] }}')"
                        @click="sidebarOpen = false"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm transition-all
                        {{ $secaoAtiva === $item['id']
                            ? 'bg-red-500/10 text-red-400 font-medium'
                            : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-200' }}">
                        <svg class="h-[18px] w-[18px] shrink-0 {{ $secaoAtiva === $item['id'] ? 'text-red-400' : 'text-zinc-500' }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            @foreach(explode('|', $item['icon']) as $path)
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                            @endforeach
                        </svg>
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @if($item['id'] === 'migracoes')
                            @php $__pendBadge = collect($this->migracoes)->where('executada', false)->count(); @endphp
                            @if($__pendBadge > 0)
                                <span class="flex h-5 min-w-[1.25rem] shrink-0 items-center justify-center rounded-full bg-amber-500/20 px-1.5 text-[10px] font-bold text-amber-400">{{ $__pendBadge }}</span>
                            @endif
                        @endif
                    </button>
                @endforeach
            </nav>

            {{-- Footer --}}
            <div class="border-t border-zinc-800/50 p-3">
                <button wire:click="sair"
                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-sm text-zinc-500 transition hover:bg-red-500/10 hover:text-red-400">
                    <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                    Terminar Sessão
                </button>
            </div>
        </aside>

        {{-- ──── Conteúdo Principal ───────────────────────── --}}
        <main class="flex flex-1 flex-col overflow-hidden">

            {{-- Top bar --}}
            <header class="flex h-16 shrink-0 items-center justify-between border-b border-zinc-800/50 bg-zinc-900/30 px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    {{-- Hamburger (mobile) --}}
                    <button @click="sidebarOpen = true" class="rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white lg:hidden">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    </button>
                    <h1 class="text-base sm:text-lg font-semibold text-white">{{ $this->secaoTitulo }}</h1>
                </div>
                <div class="flex items-center gap-2">
                    <span class="hidden sm:inline-flex items-center rounded-full border border-zinc-700/50 bg-zinc-800/50 px-2.5 py-1 font-mono text-xs text-zinc-400">
                        {{ strtoupper(app()->environment()) }}
                    </span>
                    <span class="flex items-center gap-1.5 rounded-full border border-red-800/50 bg-red-950/50 px-3 py-1 text-xs text-red-400">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-red-400"></span>
                        Online
                    </span>
                </div>
            </header>

            {{-- Output Console (histórico de operações) --}}
            @if(!empty($outputLog))
            <div class="shrink-0 border-b border-zinc-800/30 bg-zinc-950/60">
                <div class="max-h-44 overflow-y-auto painel-scrollbar px-4 sm:px-6 py-2 space-y-1"
                     x-init="$el.scrollTop = $el.scrollHeight"
                     x-on:output-added.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)">
                    @foreach($outputLog as $entry)
                    <div class="flex items-start gap-2">
                        <span class="shrink-0 font-mono text-[10px] text-zinc-600 mt-0.5 tabular-nums">{{ $entry['time'] }}</span>
                        @if($entry['tipo'] === 'success')
                            <svg class="mt-0.5 h-3 w-3 shrink-0 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        @elseif($entry['tipo'] === 'error')
                            <svg class="mt-0.5 h-3 w-3 shrink-0 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        @else
                            <svg class="mt-0.5 h-3 w-3 shrink-0 text-zinc-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        @endif
                        <pre class="text-xs font-mono whitespace-pre-wrap leading-relaxed {{ $entry['tipo'] === 'error' ? 'text-red-300' : ($entry['tipo'] === 'success' ? 'text-red-300' : 'text-zinc-400') }}">{{ $entry['text'] }}</pre>
                    </div>
                    @endforeach
                </div>
                <div class="border-t border-zinc-800/30 px-4 sm:px-6 py-1.5 flex justify-end">
                    <button wire:click="$set('outputLog', [])" class="text-[10px] text-zinc-600 hover:text-zinc-400 transition">
                        Limpar console
                    </button>
                </div>
            </div>
            @endif

            {{-- Scrollable content area --}}
            <div class="flex-1 overflow-y-auto px-4 py-4 sm:px-6 sm:py-6 painel-scrollbar">

                {{-- ════════════════════════════════════════════
                     VISÃO GERAL
                ════════════════════════════════════════════ --}}
                @if($secaoAtiva === 'visao-geral')
                <div class="space-y-6">
                    {{-- Quick stats --}}
                    @php
                        $stats = [
                            ['label' => 'Tabelas', 'value' => count($this->tabelasBd), 'icon' => 'M3.375 19.5h17.25', 'color' => 'cyan'],
                            ['label' => 'Migrações', 'value' => count($this->migracoes), 'icon' => 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375', 'color' => 'violet'],
                            ['label' => 'Rotas API', 'value' => count($this->rotasApi), 'icon' => 'M13.19 8.688a4.5 4.5 0 011.242 7.244', 'color' => 'teal'],
                            ['label' => 'Controllers', 'value' => count($this->controllersApi), 'icon' => 'M17.25 6.75L22.5 12l-5.25 5.25', 'color' => 'orange'],
                        ];
                    @endphp
                    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                        @foreach($stats as $stat)
                            <div class="rounded-xl border border-zinc-800/50 bg-zinc-900/50 p-4">
                                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500">{{ $stat['label'] }}</p>
                                <p class="mt-2 text-2xl font-bold text-white">{{ $stat['value'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        {{-- Info Sistema --}}
                        <div class="rounded-xl border border-zinc-800/50 bg-zinc-900/50 p-5">
                            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-zinc-400">Informações do Sistema</h3>
                            <div class="space-y-1.5">
                                @foreach($this->infoSistema as $chave => $valor)
                                    <div class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-zinc-800/30">
                                        <span class="text-sm text-zinc-500">{{ $chave }}</span>
                                        <span class="text-sm font-mono text-zinc-200">{{ $valor }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Saúde --}}
                        <div class="rounded-xl border border-zinc-800/50 bg-zinc-900/50 p-5">
                            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-zinc-400">Saúde da Aplicação</h3>
                            <div class="space-y-1.5">
                                @foreach($this->saude as $nome => $check)
                                    <div class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-zinc-800/30">
                                        <div class="flex items-center gap-2.5">
                                            @if($check['status'] === 'ok')
                                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-500/10">
                                                    <span class="h-2 w-2 rounded-full bg-red-400"></span>
                                                </span>
                                            @elseif($check['status'] === 'aviso')
                                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-yellow-500/10">
                                                    <span class="h-2 w-2 rounded-full bg-yellow-400"></span>
                                                </span>
                                            @else
                                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-500/10">
                                                    <span class="h-2 w-2 rounded-full bg-red-400"></span>
                                                </span>
                                            @endif
                                            <span class="text-sm text-zinc-300">{{ $nome }}</span>
                                        </div>
                                        <span class="text-xs font-mono text-zinc-500">{{ $check['detalhe'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ════════════════════════════════════════════
                     MIGRAÇÕES
                ════════════════════════════════════════════ --}}
                @elseif($secaoAtiva === 'migracoes')
                <div class="space-y-4">
                    {{-- Stats de migrações --}}
                    @php
                        $__migsExec = collect($this->migracoes)->where('executada', true)->count();
                        $__migsPend = collect($this->migracoes)->where('executada', false)->count();
                    @endphp
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 rounded-xl border border-zinc-800/50 bg-zinc-900/40 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-red-400"></span>
                            <span class="text-xs text-zinc-400"><span class="font-semibold text-red-300">{{ $__migsExec }}</span> executadas</span>
                        </div>
                        @if($__migsPend > 0)
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-amber-400"></span>
                            <span class="text-xs text-zinc-400"><span class="font-semibold text-amber-300">{{ $__migsPend }}</span> pendentes</span>
                        </div>
                        @else
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-zinc-600"></span>
                            <span class="text-xs text-zinc-600">Tudo actualizado</span>
                        </div>
                        @endif
                        <span class="ml-auto text-xs text-zinc-600">{{ count($this->migracoes) }} ficheiros no total</span>
                    </div>

                    {{-- Acções --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <button x-on:click.prevent="$dispatch('confirmar', { title: 'Executar Migrate', message: 'Executar migrate --force?', action: 'executarMigrate', component: $wire.__id, params: [], variant: 'warning', confirmLabel: 'Executar' })"
                            wire:loading.class="opacity-60 !cursor-wait"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-violet-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-violet-500">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            Migrate
                        </button>
                        <button x-on:click.prevent="$dispatch('confirmar', { title: `Rollback (${$wire.rollbackSteps} passo${$wire.rollbackSteps > 1 ? 's' : ''})`, message: `Reverter as últimas ${$wire.rollbackSteps} migração(es)?`, action: 'executarMigrateRollback', component: $wire.__id, params: [], variant: 'warning', confirmLabel: 'Reverter' })"
                            wire:loading.class="opacity-60 !cursor-wait"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-500">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg>
                            Rollback
                        </button>
                        <button x-on:click.prevent="$dispatch('confirmar', { title: 'Reset', message: 'migrate:reset irá reverter TODAS as migrações. A base de dados ficará vazia!', action: 'executarMigrateReset', component: $wire.__id, params: [], variant: 'danger', confirmLabel: 'Resetar Tudo' })"
                            wire:loading.class="opacity-60 !cursor-wait"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-orange-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-orange-500">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                            Reset
                        </button>
                        <button x-on:click.prevent="$dispatch('confirmar', { title: 'ATENÇÃO — Fresh + Seed', message: 'migrate:fresh --seed irá APAGAR TODOS os dados e recriar a base de dados. Esta acção é irreversível!', action: 'executarMigrateFreshSeed', component: $wire.__id, params: [], variant: 'danger', confirmLabel: 'Apagar Tudo e Recriar' })"
                            wire:loading.class="opacity-60 !cursor-wait"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-red-500">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                            Fresh + Seed
                        </button>
                        <button wire:click="executarMigrateStatus" wire:loading.attr="disabled" wire:loading.class="opacity-60"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-700 bg-zinc-800 px-4 py-2 text-xs font-medium text-zinc-300 transition hover:bg-zinc-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="executarMigrateStatus">Status</span>
                            <span wire:loading wire:target="executarMigrateStatus" class="flex items-center gap-1.5">
                                <svg class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                A verificar...
                            </span>
                        </button>
                    </div>

                    {{-- Configurar passos de rollback --}}
                    <div class="flex flex-wrap items-center gap-3 rounded-xl border border-zinc-800/50 bg-zinc-900/30 px-4 py-2.5" x-data>
                        <span class="text-xs text-zinc-500">Passos para rollback:</span>
                        <div class="flex items-center overflow-hidden rounded-lg border border-zinc-700 bg-zinc-800">
                            <button @click="$wire.rollbackSteps = Math.max(1, $wire.rollbackSteps - 1)"
                                class="px-3 py-1.5 text-sm font-bold text-zinc-300 transition hover:bg-zinc-700 select-none">−</button>
                            <span x-text="$wire.rollbackSteps" class="min-w-[2.5rem] px-2 py-1.5 text-center font-mono text-sm text-zinc-200 tabular-nums">{{ $rollbackSteps }}</span>
                            <button @click="$wire.rollbackSteps = Math.min(20, $wire.rollbackSteps + 1)"
                                class="px-3 py-1.5 text-sm font-bold text-zinc-300 transition hover:bg-zinc-700 select-none">+</button>
                        </div>
                        <span class="text-[11px] text-zinc-600">Aplica-se ao botão Rollback</span>
                    </div>

                    {{-- Table --}}
                    <div class="rounded-xl border border-zinc-800/50 bg-zinc-900/50 overflow-hidden">
                        <div class="max-h-[calc(100vh-320px)] overflow-auto painel-scrollbar">
                            <table class="w-full min-w-[400px]">
                                <thead class="sticky top-0 bg-zinc-900 border-b border-zinc-800/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Estado</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Migração</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-800/30">
                                    @foreach($this->migracoes as $m)
                                        <tr class="hover:bg-zinc-800/20 transition-colors">
                                            <td class="px-4 py-2.5 w-20">
                                                @if($m['executada'])
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-500/10 px-2 py-0.5 text-xs font-medium text-red-400">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span> Sim
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-400">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span> Não
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2.5 text-sm font-mono text-zinc-300">{{ $m['nome'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ════════════════════════════════════════════
                     SEEDERS
                ════════════════════════════════════════════ --}}
                @elseif($secaoAtiva === 'seeders')
                <div class="space-y-5">
                    <div class="rounded-xl border border-zinc-800/50 bg-zinc-900/50 p-5">
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-zinc-400">Seleccione os Seeders</h3>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach($seedersDisponiveis as $seeder)
                                <label class="group flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-800/50 bg-zinc-800/20 px-4 py-3 transition hover:border-amber-800/30 hover:bg-amber-500/5 has-[:checked]:border-amber-600/30 has-[:checked]:bg-amber-500/10">
                                    <input type="checkbox" wire:model="seedersSelecionados" value="{{ $seeder }}"
                                        class="rounded border-zinc-600 bg-zinc-700 text-amber-500 focus:ring-amber-500/30 focus:ring-offset-0" />
                                    <span class="text-sm font-mono text-zinc-300 group-has-[:checked]:text-amber-300">{{ $seeder }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button x-on:click.prevent="$dispatch('confirmar', { title: 'Seeders', message: 'Executar os seeders seleccionados?', action: 'executarSeedersSelecionados', component: $wire.__id, params: [], variant: 'warning', confirmLabel: 'Executar' })"
                            wire:loading.class="opacity-60 !cursor-wait"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-500 disabled:cursor-not-allowed disabled:opacity-40"
                            @if(empty($seedersSelecionados)) disabled @endif>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                            Executar Seleccionados
                        </button>
                        <button x-on:click.prevent="$dispatch('confirmar', { title: 'Todos os Seeders', message: 'Executar TODOS os seeders (DatabaseSeeder)? Isto pode demorar.', action: 'executarTodosSeeders', component: $wire.__id, params: [], variant: 'warning', confirmLabel: 'Executar Todos' })"
                            wire:loading.class="opacity-60 !cursor-wait"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-700 bg-zinc-800 px-4 py-2.5 text-xs font-medium text-zinc-300 transition hover:bg-zinc-700">
                            Executar Todos (DatabaseSeeder)
                        </button>
                    </div>
                </div>

                {{-- ════════════════════════════════════════════
                     CACHE & OPTIMIZAÇÃO
                ════════════════════════════════════════════ --}}
                @elseif($secaoAtiva === 'cache')
                <div class="space-y-6">
                    <div class="rounded-xl border border-zinc-800/50 bg-zinc-900/50 p-5">
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-zinc-400">Limpar Cache</h3>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            @php
                                $cacheActions = [
                                    ['method' => 'limparCache', 'label' => 'Application Cache', 'desc' => 'cache:clear'],
                                    ['method' => 'limparConfigCache', 'label' => 'Config Cache', 'desc' => 'config:clear'],
                                    ['method' => 'limparRouteCache', 'label' => 'Route Cache', 'desc' => 'route:clear'],
                                    ['method' => 'limparViewCache', 'label' => 'View Cache', 'desc' => 'view:clear'],
                                ];
                            @endphp
                            @foreach($cacheActions as $action)
                                <button wire:click="{{ $action['method'] }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 !cursor-wait"
                                    class="group rounded-xl border border-zinc-800/50 bg-zinc-800/30 p-4 text-left transition hover:border-zinc-700 hover:bg-zinc-800/50 disabled:opacity-50">
                                    <p class="text-sm font-medium text-zinc-200 group-hover:text-white">{{ $action['label'] }}</p>
                                    <p class="mt-1 font-mono text-xs text-zinc-500">{{ $action['desc'] }}</p>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-xl border border-zinc-800/50 bg-zinc-900/50 p-5">
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-zinc-400">Optimização</h3>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <button wire:click="optimizar" wire:loading.attr="disabled" wire:loading.class="opacity-50 !cursor-wait"
                                class="group flex items-center gap-3 rounded-xl border border-red-800/30 bg-red-500/5 p-4 text-left transition hover:border-red-700/40 hover:bg-red-500/10 disabled:opacity-50">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-500/10">
                                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-red-300">Optimizar</p>
                                    <p class="mt-0.5 text-xs text-zinc-500">php artisan optimize</p>
                                </div>
                            </button>
                            <button wire:click="limparOptimizacao" wire:loading.attr="disabled" wire:loading.class="opacity-50 !cursor-wait"
                                class="group flex items-center gap-3 rounded-xl border border-rose-800/30 bg-rose-500/5 p-4 text-left transition hover:border-rose-700/40 hover:bg-rose-500/10 disabled:opacity-50">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-500/10">
                                    <svg class="h-5 w-5 text-rose-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-rose-300">Limpar Optimização</p>
                                    <p class="mt-0.5 text-xs text-zinc-500">php artisan optimize:clear</p>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ════════════════════════════════════════════
                     BASE DE DADOS
                ════════════════════════════════════════════ --}}
                @elseif($secaoAtiva === 'tabelas')
                <div class="rounded-xl border border-zinc-800/50 bg-zinc-900/50 overflow-hidden">
                    <div class="border-b border-zinc-800/50 px-5 py-3">
                        <p class="text-xs text-zinc-500">{{ count($this->tabelasBd) }} tabelas encontradas</p>
                    </div>
                    <div class="max-h-[calc(100vh-280px)] overflow-y-auto painel-scrollbar">
                        <table class="w-full">
                            <thead class="sticky top-0 bg-zinc-900 border-b border-zinc-800/50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Tabela</th>
                                    <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Registos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/30">
                                @foreach($this->tabelasBd as $tabela)
                                    <tr class="hover:bg-zinc-800/20 transition-colors">
                                        <td class="px-5 py-2.5">
                                            <span class="text-sm font-mono text-zinc-300">{{ $tabela['nome'] }}</span>
                                        </td>
                                        <td class="px-5 py-2.5 text-right">
                                            <span class="inline-flex min-w-[3rem] justify-center rounded-full bg-zinc-800 px-2.5 py-0.5 text-xs font-mono text-zinc-300">
                                                {{ number_format($tabela['registos']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ════════════════════════════════════════════
                     API & CONTROLLERS
                ════════════════════════════════════════════ --}}
                @elseif($secaoAtiva === 'api')
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($this->controllersApi as $ctrl)
                        <div class="rounded-xl border border-zinc-800/50 bg-zinc-900/50 p-4 transition hover:border-zinc-700/50">
                            <div class="mb-3 flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10">
                                    <svg class="h-4 w-4 text-orange-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-orange-300">{{ $ctrl['nome'] }}</p>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                @foreach($ctrl['metodos'] as $metodo)
                                    <span class="rounded bg-zinc-800 px-2 py-0.5 text-[11px] font-mono text-zinc-400">{{ $metodo }}()</span>
                                @endforeach
                            </div>
                            <p class="mt-2 text-[10px] text-zinc-600">{{ count($ctrl['metodos']) }} métodos</p>
                        </div>
                    @endforeach
                </div>

                {{-- ════════════════════════════════════════════
                     ROTAS DA API
                ════════════════════════════════════════════ --}}
                @elseif($secaoAtiva === 'rotas')
                <div class="rounded-xl border border-zinc-800/50 bg-zinc-900/50 overflow-hidden">
                    <div class="border-b border-zinc-800/50 px-5 py-3">
                        <p class="text-xs text-zinc-500">{{ count($this->rotasApi) }} rotas registadas</p>
                    </div>
                    <div class="max-h-[calc(100vh-280px)] overflow-auto painel-scrollbar">
                        <table class="w-full min-w-[600px]">
                            <thead class="sticky top-0 bg-zinc-900 border-b border-zinc-800/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Método</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">URI</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Nome</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Acção</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/30">
                                @foreach($this->rotasApi as $rota)
                                    <tr class="hover:bg-zinc-800/20 transition-colors">
                                        <td class="px-4 py-2.5">
                                            @foreach(explode('|', $rota['metodos']) as $m)
                                                <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold font-mono
                                                    {{ $m === 'GET' ? 'bg-red-500/10 text-red-400' : '' }}
                                                    {{ $m === 'POST' ? 'bg-sky-500/10 text-sky-400' : '' }}
                                                    {{ $m === 'PUT' || $m === 'PATCH' ? 'bg-amber-500/10 text-amber-400' : '' }}
                                                    {{ $m === 'DELETE' ? 'bg-red-500/10 text-red-400' : '' }}
                                                    {{ $m === 'HEAD' ? 'bg-zinc-500/10 text-zinc-500' : '' }}
                                                ">{{ $m }}</span>
                                            @endforeach
                                        </td>
                                        <td class="px-4 py-2.5 font-mono text-sm text-zinc-200">{{ $rota['uri'] }}</td>
                                        <td class="px-4 py-2.5 text-xs text-zinc-500">{{ $rota['nome'] }}</td>
                                        <td class="px-4 py-2.5 font-mono text-xs text-zinc-600 max-w-[200px] truncate">{{ class_basename($rota['accao']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ════════════════════════════════════════════
                     LOGS
                ════════════════════════════════════════════ --}}
                @elseif($secaoAtiva === 'logs')
                <div class="space-y-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-zinc-500">Últimas entradas do ficheiro de log</p>
                        <button x-on:click.prevent="$dispatch('confirmar', { title: 'Limpar Logs', message: 'Limpar ficheiro de log? O conteúdo será apagado permanentemente.', action: 'limparLogs', component: $wire.__id, params: [], variant: 'danger', confirmLabel: 'Limpar' })"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-xs text-zinc-400 transition hover:bg-zinc-700 hover:text-zinc-300">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                            Limpar
                        </button>
                    </div>
                    <div class="rounded-xl border border-zinc-800/50 bg-zinc-950 overflow-hidden">
                        <pre class="max-h-[calc(100vh-280px)] overflow-auto p-4 sm:p-5 text-xs font-mono leading-relaxed text-zinc-500 painel-scrollbar">{{ $this->ultimosLogs }}</pre>
                    </div>
                </div>

                {{-- ════════════════════════════════════════════
                     TERMINAL ARTISAN
                ════════════════════════════════════════════ --}}
                @elseif($secaoAtiva === 'terminal')
                <div class="space-y-6">

                    {{-- Storage Link --}}
                    <div class="rounded-xl border border-zinc-800/50 bg-zinc-900/50 p-5">
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-zinc-400">Atalho Storage (public/storage)</h3>
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($this->storageLinkStatus['existe'])
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-500/10">
                                        <svg class="h-4 w-4 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m9.193-4.818a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-red-300">Symlink activo</p>
                                        <p class="mt-0.5 truncate font-mono text-xs text-zinc-500">{{ $this->storageLinkStatus['destino'] }}</p>
                                    </div>
                                @else
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-500/10">
                                        <svg class="h-4 w-4 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-sm font-medium text-red-300">Symlink não existe</p>
                                        <p class="mt-0.5 text-xs text-zinc-500">Ficheiros em storage/app/public não serão acessíveis via URL</p>
                                    </div>
                                @endif
                            </div>
                            <button wire:click="criarStorageLink"
                                class="w-full sm:w-auto shrink-0 inline-flex items-center justify-center gap-1.5 rounded-lg {{ $this->storageLinkStatus['existe'] ? 'border border-zinc-700 bg-zinc-800 text-zinc-400 hover:bg-zinc-700' : 'bg-red-600 text-white hover:bg-red-500' }} px-4 py-2.5 text-xs font-semibold transition">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m9.193-4.818a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                                {{ $this->storageLinkStatus['existe'] ? 'Verificar' : 'Criar Symlink' }}
                            </button>
                        </div>
                    </div>

                    {{-- Comandos Rápidos --}}
                    <div class="rounded-xl border border-zinc-800/50 bg-zinc-900/50 p-5">
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-zinc-400">Comandos Rápidos</h3>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            @php
                                $quickCmds = [
                                    ['cmd' => 'migrate --force', 'label' => 'Migrate', 'cor' => 'violet'],
                                    ['cmd' => 'migrate:rollback --force', 'label' => 'Rollback', 'cor' => 'amber'],
                                    ['cmd' => 'migrate:reset --force', 'label' => 'Reset All', 'cor' => 'orange'],
                                    ['cmd' => 'migrate:status', 'label' => 'Migrate Status', 'cor' => 'zinc'],
                                    ['cmd' => 'optimize', 'label' => 'Optimize', 'cor' => 'emerald'],
                                    ['cmd' => 'optimize:clear', 'label' => 'Optimize Clear', 'cor' => 'rose'],
                                    ['cmd' => 'cache:clear', 'label' => 'Cache Clear', 'cor' => 'amber'],
                                    ['cmd' => 'config:clear', 'label' => 'Config Clear', 'cor' => 'amber'],
                                    ['cmd' => 'route:clear', 'label' => 'Route Clear', 'cor' => 'amber'],
                                    ['cmd' => 'view:clear', 'label' => 'View Clear', 'cor' => 'amber'],
                                ];
                            @endphp
                            @foreach($quickCmds as $qc)
                                <button wire:click="$set('comandoArtisan', '{{ $qc['cmd'] }}')" wire:click.stop="executarComandoLivre"
                                    class="group rounded-xl border border-zinc-800/50 bg-zinc-800/30 p-3 text-left transition hover:border-{{ $qc['cor'] }}-800/40 hover:bg-{{ $qc['cor'] }}-500/5">
                                    <p class="text-sm font-medium text-zinc-300 group-hover:text-{{ $qc['cor'] }}-300">{{ $qc['label'] }}</p>
                                    <p class="mt-0.5 font-mono text-[11px] text-zinc-600">artisan {{ $qc['cmd'] }}</p>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Terminal interactivo --}}
                    <div class="rounded-xl border border-zinc-800/50 bg-zinc-900/50 p-5">
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-zinc-400">Executar Comando Artisan</h3>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <div class="relative flex-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 font-mono text-xs sm:text-sm text-zinc-600">php artisan</span>
                                <input type="text" wire:model="comandoArtisan"
                                    wire:keydown.enter="executarComandoLivre"
                                    placeholder="migrate:status"
                                    class="w-full rounded-xl border border-zinc-700/50 bg-zinc-800/50 py-3 pl-[6.5rem] sm:pl-[7.5rem] pr-4 font-mono text-sm text-zinc-200 placeholder-zinc-600 transition focus:border-cyan-500/50 focus:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-cyan-500/20" />
                            </div>
                            <button wire:click="executarComandoLivre" wire:loading.attr="disabled"
                                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-500 disabled:opacity-50">
                                <span wire:loading.remove wire:target="executarComandoLivre">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                                </span>
                                <svg wire:loading wire:target="executarComandoLivre" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Executar
                            </button>
                        </div>
                        <p class="mt-3 text-[11px] text-zinc-600">
                            Exemplos: <code class="text-zinc-500">migrate:status</code> · <code class="text-zinc-500">route:list --path=api</code> · <code class="text-zinc-500">storage:link</code>
                            <br>Bloqueados: <code class="text-red-500/60">down, up, serve, tinker, env, key:generate, queue:work, queue:listen, schedule:work</code>
                        </p>
                    </div>
                </div>
                @endif

            </div>
        </main>
    </div>
    @endif
</div>

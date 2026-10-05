<div class="px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">

    {{-- SECTION: Cabeçalho --}}
    <div>
        <nav class="breadcrumb mb-3" aria-label="Caminho de navegação">
            <a href="{{ route('dashboard') }}" wire:navigate>Dashboard</a>
            <span class="breadcrumb-sep" aria-hidden="true">/</span>
            <span class="text-zinc-600 dark:text-zinc-300 font-medium">Municípios</span>
        </nav>

        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50 tracking-tight">Municípios</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Lista de municípios da província
                    @if($municipios->total() > 0)
                        &nbsp;&bull;&nbsp;<span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $municipios->total() }}</span> encontrados
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- SECTION: Filtros e pesquisa --}}
    <div class="flex gap-3 flex-wrap">
        <div class="flex-1 min-w-[220px]">
            <flux:input
                icon="magnifying-glass"
                placeholder="Pesquisar município..."
                wire:model.live.debounce.300ms="search"
                class="w-full"
            />
        </div>
    </div>

    {{-- SECTION: Grid de Municípios --}}
    @if($municipios->isEmpty())
        <div class="card">
            <div class="empty-state">
                <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Nenhum município encontrado</p>
                @if($search)
                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Tente outro termo de pesquisa</p>
                    <button wire:click="$set('search', '')" class="mt-3 text-xs text-red-600 dark:text-red-400 hover:underline">Limpar pesquisa</button>
                @endif
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($municipios as $municipio)
                <a href="{{ route('municipio.show', $municipio) }}" class="group block" wire:navigate>
                    <div class="card card-media card-media-red card-hover p-5 h-full flex flex-col">
                        {{-- Cabeçalho do card --}}
                        <div class="flex items-center gap-3.5 mb-4">
                            <div class="card-icon-badge card-icon-red">
                                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="card-title font-semibold text-sm truncate">{{ $municipio->nome }}</p>
                                @if($municipio->codigo)
                                    <p class="card-subtitle text-xs font-mono mt-0.5">{{ $municipio->codigo }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Stats do município --}}
                        <div class="grid grid-cols-2 gap-2 flex-1">
                            <div class="rounded-lg px-3 py-2 border border-zinc-200 bg-zinc-50">
                                <p class="text-[10px] font-medium text-zinc-600 uppercase tracking-wide">Instrumentos</p>
                                <p class="text-base font-bold text-zinc-900 mt-0.5">{{ $municipio->instrumentos_count }}</p>
                            </div>
                            <div class="rounded-lg px-3 py-2 border border-zinc-200 bg-zinc-50">
                                <p class="text-[10px] font-medium text-zinc-600 uppercase tracking-wide">Loteamentos</p>
                                <p class="text-base font-bold text-zinc-900 mt-0.5">{{ $municipio->loteamentos_count }}</p>
                            </div>
                            <div class="rounded-lg px-3 py-2 border border-zinc-200 bg-zinc-50">
                                <p class="text-[10px] font-medium text-zinc-600 uppercase tracking-wide">Operações</p>
                                <p class="text-base font-bold text-zinc-900 mt-0.5">{{ $municipio->operacoes_urbanisticas_count }}</p>
                            </div>
                            <div class="rounded-lg px-3 py-2 border border-zinc-200 bg-zinc-50">
                                <p class="text-[10px] font-medium text-zinc-600 uppercase tracking-wide">Proj. SONA</p>
                                <p class="text-base font-bold text-zinc-900 mt-0.5">{{ $municipio->projectos_sona_count }}</p>
                            </div>
                        </div>

                        {{-- Acção --}}
                        <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                            <span class="text-xs font-semibold text-angola-red group-hover:gap-2 flex items-center gap-1 transition-all">
                                Aceder ao município
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                            @if($municipio->activo)
                                <span class="badge badge-angola text-[10px]">Activo</span>
                            @else
                                <span class="badge badge-zinc text-[10px]">Inactivo</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Paginação --}}
        @if($municipios->hasPages())
            <div class="mt-4">{{ $municipios->links('vendor.pagination.gterras') }}</div>
        @endif
    @endif
</div>


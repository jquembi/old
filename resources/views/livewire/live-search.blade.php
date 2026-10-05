<div
    x-data="{
        openModal: false,
        active: -1,
        openSearch() {
            this.openModal = true;
            this.active = -1;
            this.$nextTick(() => {
                if (this.$refs.modalSearchInput) {
                    this.$refs.modalSearchInput.focus();
                    this.$refs.modalSearchInput.select();
                }
            });
        },
        closeSearch() {
            this.openModal = false;
            this.active = -1;
        },
        moveNext() {
            const items = this.$refs.modalContent ? this.$refs.modalContent.querySelectorAll('[data-search-result]') : [];
            if (items.length > 0) {
                this.active = Math.min(this.active + 1, items.length - 1);
                items[this.active]?.focus();
                items[this.active]?.scrollIntoView({ block: 'nearest' });
            }
        },
        movePrev() {
            const items = this.$refs.modalContent ? this.$refs.modalContent.querySelectorAll('[data-search-result]') : [];
            if (items.length > 0) {
                this.active = Math.max(this.active - 1, 0);
                if (this.active >= 0) {
                    items[this.active]?.focus();
                    items[this.active]?.scrollIntoView({ block: 'nearest' });
                } else {
                    this.$refs.modalSearchInput?.focus();
                }
            }
        },
        selectActive() {
            const items = this.$refs.modalContent ? this.$refs.modalContent.querySelectorAll('[data-search-result]') : [];
            if (this.active >= 0 && items[this.active]) {
                items[this.active]?.click();
            }
        }
    }"
    x-init="$watch('openModal', value => {
        document.body.classList.toggle('overflow-hidden', value);
    })"
    x-on:keydown.window.prevent.ctrl.k="openSearch()"
    x-on:keydown.window.prevent.cmd.k="openSearch()"
    x-on:keydown.window.slash="
        if (['INPUT', 'TEXTAREA', 'SELECT'].indexOf(document.activeElement.tagName) === -1) {
            $event.preventDefault();
            openSearch();
        }
    "
    class="relative"
>
    {{-- TRIGGER NO HEADER: Botão de Ícone para Pesquisa Global / Spotlight --}}
    <button
        type="button"
        @click="openSearch()"
        class="relative inline-flex size-9 items-center justify-center rounded-lg text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white"
        title="Pesquisar (Ctrl+K ou /)"
        aria-haspopup="dialog"
        :aria-expanded="openModal"
    >
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
        </svg>
        <span class="sr-only">Pesquisar</span>
    </button>

    {{-- MODAL SPOTLIGHT INTELIGENTE (COMMAND PALETTE) --}}
        <div
            x-show="openModal"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @keydown.escape.window="closeSearch()"
            class="fixed inset-0 z-[100] flex items-start justify-center p-2 sm:p-4 md:p-6 pt-2 sm:pt-12 md:pt-16 bg-zinc-950/80 backdrop-blur-xl overflow-y-auto"
            style="display: none;"
        >
            {{-- Painel Central do Modal --}}
            <div
                @click.outside="closeSearch()"
                x-show="openModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 -translate-y-4"
                class="w-full max-w-3xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-2xl rounded-2xl sm:rounded-[28px] shadow-[0_30px_90px_rgba(0,0,0,0.35)] dark:shadow-[0_30px_90px_rgba(0,0,0,0.7)] border border-zinc-200/90 dark:border-zinc-800/90 overflow-hidden flex flex-col max-h-[92vh] sm:max-h-[85vh] text-left ring-1 ring-black/5 dark:ring-white/5"
                role="dialog"
                aria-modal="true"
                aria-label="Pesquisa Inteligente e Atalhos do Sistema"
            >
                {{-- Linha de Acento Superior Brand Gradient --}}
                <div class="h-1 bg-gradient-to-r from-red-600 via-amber-500 to-red-600 opacity-90"></div>

                {{-- 1. Barra de Pesquisa Superior --}}
                <div class="relative flex items-center border-b border-zinc-100 dark:border-zinc-800/90 px-3.5 sm:px-5 py-3 sm:py-4 bg-white dark:bg-zinc-900 gap-2">
                    {{-- Ícone / Loading Spinner --}}
                    <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-red-500/10 dark:bg-red-500/20 text-red-600 dark:text-red-400 shrink-0">
                        <div wire:loading.remove wire:target="query, scope, setQuery, clear">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <div wire:loading wire:target="query, scope, setQuery, clear">
                            <svg class="animate-spin w-5 h-5 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </div>
                    </div>

                    {{-- Input de Busca Principal --}}
                    <input
                        x-ref="modalSearchInput"
                        type="search"
                        wire:model.live.debounce.250ms="query"
                        placeholder="Pesquisar municípios, instrumentos, operações, utilizadores, atalhos..."
                        class="w-full bg-transparent px-2 sm:px-3.5 py-1 text-sm sm:text-base md:text-lg font-sans font-medium text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 outline-none border-none focus:ring-0 min-w-0"
                        autocomplete="off"
                        @keydown.arrow-down.prevent="moveNext()"
                        @keydown.arrow-up.prevent="movePrev()"
                        @keydown.enter.prevent="selectActive()"
                    />

                    {{-- Botões de Limpar e Fechar --}}
                    <div class="flex items-center gap-2 shrink-0">
                        @if($query !== '')
                            <button
                                type="button"
                                wire:click="clear"
                                @click="$refs.modalSearchInput?.focus()"
                                class="flex h-7 w-7 items-center justify-center rounded-xl text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200 transition-colors"
                                title="Limpar pesquisa"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif

                        <button
                            type="button"
                            @click="closeSearch()"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-zinc-500 hover:text-zinc-900 bg-zinc-100 hover:bg-zinc-200/80 rounded-xl dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700/80 dark:hover:text-zinc-200 transition-colors"
                            title="Fechar (Esc)"
                        >
                            <span>Esc</span>
                        </button>
                    </div>
                </div>

                {{-- 2. Barra de Filtros por Categoria (Pill Tabs Elegantes) --}}
                <div class="flex items-center gap-1.5 px-5 py-2.5 border-b border-zinc-100 dark:border-zinc-800/80 overflow-x-auto no-scrollbar bg-zinc-50/50 dark:bg-zinc-900/50">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 shrink-0 me-1">Filtrar:</span>
                    @foreach($scopesDisponiveis as $key => $label)
                        <button
                            type="button"
                            wire:click="$set('scope', '{{ $key }}')"
                            class="inline-flex items-center px-3 py-1 text-xs rounded-full transition-all duration-150 shrink-0
                                {{ $scope === $key
                                    ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 font-semibold shadow-xs ring-1 ring-zinc-900/10'
                                    : 'bg-zinc-100/90 text-zinc-600 hover:bg-zinc-200/90 dark:bg-zinc-800/90 dark:text-zinc-400 dark:hover:bg-zinc-700/90 font-medium'
                                }}
                            "
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- 3. Conteúdo Rolável do Modal --}}
                <div x-ref="modalContent" class="overflow-y-auto flex-1 p-3.5 sm:p-5 md:p-6 space-y-5 sm:space-y-7">

                    {{-- CENÁRIO A: ESTADO INICIAL / ACESSOS RÁPIDOS (query < 2) --}}
                    @if(mb_strlen(trim($query)) < 2)

                        {{-- Sugestões Populares (Click-to-Search) --}}
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Sugestões de Pesquisa</span>
                                </div>
                                <span class="text-[11px] text-zinc-400">Clique para aplicar</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($sugestoesPopulares as $sugestao)
                                    <button
                                        type="button"
                                        wire:click="setQuery('{{ $sugestao }}')"
                                        class="group inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-850 hover:bg-red-50 hover:border-red-200 hover:text-red-700 dark:hover:bg-zinc-800 dark:hover:border-zinc-700 dark:text-zinc-300 transition-all duration-150 shadow-2xs hover:shadow-xs"
                                    >
                                        <span class="text-zinc-400 group-hover:text-red-500 transition-colors font-bold text-[11px]">#</span>
                                        <span>{{ $sugestao }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Acessos Rápidos e Links do Sistema --}}
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Acessos Rápidos do Sistema</span>
                                </div>
                                <span class="text-[11px] text-zinc-400">Atalhos principais</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($atalhos as $atalho)
                                    <a
                                        href="{{ $atalho['url'] }}"
                                        wire:navigate
                                        @click="closeSearch()"
                                        data-search-result
                                        class="group flex items-start gap-3.5 p-3.5 rounded-2xl border border-zinc-200/70 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-850/50 hover:bg-white dark:hover:bg-zinc-800 hover:border-red-300/80 dark:hover:border-zinc-700 hover:shadow-sm transition-all duration-150 text-left outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-400"
                                    >
                                        <div class="p-2.5 rounded-xl shrink-0 transition-all duration-150 shadow-xs
                                            {{ $atalho['cor'] === 'red' ? 'bg-red-50 text-red-600 dark:bg-red-950/60 dark:text-red-400 group-hover:bg-red-600 group-hover:text-white' : '' }}
                                            {{ $atalho['cor'] === 'emerald' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 group-hover:bg-emerald-600 group-hover:text-white' : '' }}
                                            {{ $atalho['cor'] === 'blue' ? 'bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 group-hover:bg-blue-600 group-hover:text-white' : '' }}
                                            {{ $atalho['cor'] === 'amber' ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 group-hover:bg-amber-600 group-hover:text-white' : '' }}
                                            {{ $atalho['cor'] === 'purple' ? 'bg-purple-50 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400 group-hover:bg-purple-600 group-hover:text-white' : '' }}
                                            {{ $atalho['cor'] === 'teal' ? 'bg-teal-50 text-teal-600 dark:bg-teal-950/60 dark:text-teal-400 group-hover:bg-teal-600 group-hover:text-white' : '' }}
                                            {{ $atalho['cor'] === 'indigo' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 group-hover:bg-indigo-600 group-hover:text-white' : '' }}
                                            {{ $atalho['cor'] === 'orange' ? 'bg-orange-50 text-orange-600 dark:bg-orange-950/60 dark:text-orange-400 group-hover:bg-orange-600 group-hover:text-white' : '' }}
                                            {{ $atalho['cor'] === 'pink' ? 'bg-pink-50 text-pink-600 dark:bg-pink-950/60 dark:text-pink-400 group-hover:bg-pink-600 group-hover:text-white' : '' }}
                                            {{ $atalho['cor'] === 'sky' ? 'bg-sky-50 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400 group-hover:bg-sky-600 group-hover:text-white' : '' }}
                                            {{ $atalho['cor'] === 'zinc' ? 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 group-hover:bg-zinc-800 group-hover:text-white' : '' }}
                                        ">
                                            <flux:icon name="{{ $atalho['icone'] }}" class="w-4 h-4" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-zinc-900 dark:text-zinc-100 group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors truncate">
                                                {{ $atalho['titulo'] }}
                                            </p>
                                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 line-clamp-1 mt-0.5 leading-snug">
                                                {{ $atalho['descricao'] }}
                                            </p>
                                        </div>
                                        <span class="flex h-5 w-5 items-center justify-center rounded-lg text-zinc-400 group-hover:text-red-600 group-hover:translate-x-0.5 transition-all shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- Municípios em Destaque --}}
                        @if($municipiosDestaque->isNotEmpty())
                            <div>
                                <div class="flex items-center justify-between mb-2.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Municípios em Destaque</span>
                                    </div>
                                    <a href="{{ route('municipios.index') }}" wire:navigate @click="closeSearch()" class="text-[11px] font-medium text-red-600 dark:text-red-400 hover:underline">Ver todos &rarr;</a>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                    @foreach($municipiosDestaque as $mun)
                                        <a
                                            href="{{ route('municipio.show', $mun) }}"
                                            wire:navigate
                                            @click="closeSearch()"
                                            data-search-result
                                            class="flex items-center justify-between p-3 rounded-2xl border border-zinc-200/70 dark:border-zinc-800 bg-white dark:bg-zinc-850 hover:bg-emerald-50/50 hover:border-emerald-300 dark:hover:bg-zinc-800 transition-all text-left group shadow-2xs"
                                        >
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 truncate">
                                                    {{ $mun->nome }}
                                                </p>
                                                <p class="text-[10px] text-zinc-400 truncate mt-0.5">
                                                    {{ $mun->provincia?->nome ?? 'Província' }}
                                                </p>
                                            </div>
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 group-hover:bg-emerald-100 group-hover:text-emerald-700 uppercase">
                                                {{ $mun->codigo }}
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    {{-- CENÁRIO B: RESULTADOS EM TEMPO REAL (query >= 2) --}}
                    @elseif($resultados->isNotEmpty())
                        <div class="space-y-6">
                            @foreach($resultados as $categoria => $itens)
                                <div>
                                    {{-- Cabeçalho de Categoria --}}
                                    <div class="flex items-center justify-between px-1 mb-2.5">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                                {{ $categoria }}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                                {{ $itens->count() }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Lista de Itens --}}
                                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800/80 rounded-2xl border border-zinc-200/70 dark:border-zinc-800 bg-white dark:bg-zinc-850/60 overflow-hidden shadow-2xs">
                                        @foreach($itens as $item)
                                            <a
                                                href="{{ $item['url'] }}"
                                                wire:navigate
                                                @click="closeSearch()"
                                                data-search-result
                                                class="group flex items-center justify-between gap-3.5 p-3.5 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors text-left outline-none focus:bg-zinc-50 dark:focus:bg-zinc-800 focus:ring-1 focus:ring-red-500/20"
                                            >
                                                <div class="flex items-center gap-3.5 min-w-0">
                                                    {{-- Ícone com Container Colorido --}}
                                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl shadow-xs transition-colors
                                                        {{ ($item['cor'] ?? '') === 'emerald' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 group-hover:bg-emerald-600 group-hover:text-white' : '' }}
                                                        {{ ($item['cor'] ?? '') === 'blue' ? 'bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 group-hover:bg-blue-600 group-hover:text-white' : '' }}
                                                        {{ ($item['cor'] ?? '') === 'amber' ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 group-hover:bg-amber-600 group-hover:text-white' : '' }}
                                                        {{ ($item['cor'] ?? '') === 'orange' ? 'bg-orange-50 text-orange-600 dark:bg-orange-950/60 dark:text-orange-400 group-hover:bg-orange-600 group-hover:text-white' : '' }}
                                                        {{ ($item['cor'] ?? '') === 'purple' ? 'bg-purple-50 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400 group-hover:bg-purple-600 group-hover:text-white' : '' }}
                                                        {{ ($item['cor'] ?? '') === 'indigo' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 group-hover:bg-indigo-600 group-hover:text-white' : '' }}
                                                        {{ ($item['cor'] ?? '') === 'red' ? 'bg-red-50 text-red-600 dark:bg-red-950/60 dark:text-red-400 group-hover:bg-red-600 group-hover:text-white' : '' }}
                                                        {{ empty($item['cor']) ? 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' : '' }}
                                                    ">
                                                        <flux:icon name="{{ $item['icone'] }}" class="w-5 h-5" />
                                                    </div>

                                                    {{-- Título e Detalhe --}}
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <p class="text-xs font-bold text-zinc-900 dark:text-zinc-100 group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors truncate">
                                                                {{ $item['titulo'] }}
                                                            </p>
                                                            @if(!empty($item['badge']))
                                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 shrink-0">
                                                                    {{ $item['badge'] }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate mt-0.5">
                                                            {{ $item['detalhe'] }}
                                                        </p>
                                                    </div>
                                                </div>

                                                {{-- Ação Abrir --}}
                                                <div class="flex items-center gap-1.5 text-xs font-semibold text-zinc-400 group-hover:text-red-600 dark:group-hover:text-red-400 shrink-0 transition-all duration-150">
                                                    <span>Abrir</span>
                                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                                    </svg>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    {{-- CENÁRIO C: NENHUM RESULTADO ENCONTRADO --}}
                    @else
                        <div class="py-14 px-4 text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800/80 text-zinc-400 mx-auto mb-4 border border-zinc-200/50 dark:border-zinc-700/50 shadow-2xs">
                                <svg class="w-8 h-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 16.318A4.486 4.486 0 0012.016 15a4.486 4.486 0 00-3.198 1.318M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                Nenhum resultado para "<span class="text-red-600">{{ $query }}</span>"
                            </h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1.5 max-w-sm mx-auto leading-relaxed">
                                Tente procurar por outro termo, verificar a ortografia ou alternar o filtro de categoria selecionado.
                            </p>
                            <div class="mt-5 flex items-center justify-center gap-2.5">
                                <button
                                    type="button"
                                    wire:click="clear"
                                    class="inline-flex items-center px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 transition-colors"
                                >
                                    Limpar Pesquisa
                                </button>
                                <button
                                    type="button"
                                    wire:click="$set('scope', 'all')"
                                    class="inline-flex items-center px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-red-50 hover:bg-red-100 dark:bg-red-950/40 text-red-700 dark:text-red-400 transition-colors"
                                >
                                    Ver em Todas as Categorias
                                </button>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- 4. Rodapé Informativo com Dicas de Atalhos --}}
                <div class="px-3.5 sm:px-5 py-2.5 sm:py-3.5 bg-zinc-50/90 dark:bg-zinc-900/90 border-t border-zinc-100 dark:border-zinc-800/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-[11px] text-zinc-400">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1">
                            <kbd class="px-1.5 py-0.5 rounded-md bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-mono text-[10px] border border-zinc-200 dark:border-zinc-700 shadow-2xs">↑</kbd>
                            <kbd class="px-1.5 py-0.5 rounded-md bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-mono text-[10px] border border-zinc-200 dark:border-zinc-700 shadow-2xs">↓</kbd>
                            Navegar
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <kbd class="px-1.5 py-0.5 rounded-md bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-mono text-[10px] border border-zinc-200 dark:border-zinc-700 shadow-2xs">↵</kbd>
                            Abrir
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <kbd class="px-1.5 py-0.5 rounded-md bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-mono text-[10px] border border-zinc-200 dark:border-zinc-700 shadow-2xs">Esc</kbd>
                            Fechar
                        </span>
                    </div>

                    @if(mb_strlen(trim($query)) >= 2)
                        <span class="font-bold text-zinc-600 dark:text-zinc-300">
                            {{ $totalResultados }} {{ $totalResultados === 1 ? 'resultado encontrado' : 'resultados encontrados' }}
                        </span>
                    @else
                        <span class="font-medium text-zinc-400">
                            G.TERRAS Command Center
                        </span>
                    @endif
                </div>
            </div>
        </div>
</div>

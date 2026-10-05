@if($isSuperAdmin)
<div class="relative" x-data="{ open: false, showProvincias: {{ $modo === 'provincial' ? 'true' : 'false' }} }" @click.outside="open = false" @keydown.escape.window="open = false">
    {{-- Botão de Alternância de Perspetiva (Apenas Ícone no Header) --}}
    <button
        type="button"
        @click="open = !open"
        class="relative inline-flex size-9 items-center justify-center rounded-lg transition-all duration-200 border
            {{ $modo === 'global' ? 'bg-sky-50 text-sky-700 border-sky-200 hover:bg-sky-100 hover:text-sky-900 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800 dark:hover:bg-sky-900/60' : '' }}
            {{ $modo === 'nacional' ? 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100 hover:text-red-900 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800 dark:hover:bg-red-900/60' : '' }}
            {{ $modo === 'provincial' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100 hover:text-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 dark:hover:bg-emerald-900/60' : '' }}
        "
        title="Perspetiva: {{ $labelAtual }} (Clique para alternar)"
        aria-haspopup="true"
        :aria-expanded="open"
    >
        @if($modo === 'global')
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
            </svg>
        @elseif($modo === 'nacional')
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A1.5 1.5 0 0018 8.833H6a1.5 1.5 0 00-1.5 1.5V21h15z" />
            </svg>
        @else
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
        @endif
        <span class="sr-only">{{ $labelAtual }}</span>
    </button>

    {{-- Dropdown de Seleção de Perspetiva --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute right-0 mt-2 w-[calc(100vw-2rem)] max-w-xs sm:w-80 rounded-2xl bg-white p-2 shadow-xl border border-zinc-200 dark:bg-zinc-900 dark:border-zinc-800 z-50 text-left"
        style="display: none;"
    >
        {{-- Cabeçalho do Menu --}}
        <div class="px-3 py-2 border-b border-zinc-100 dark:border-zinc-800 mb-1">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Modo de Perspetiva</p>
                <span class="inline-flex items-center rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                    Superadmin
                </span>
            </div>
            <p class="text-xs text-zinc-600 dark:text-zinc-300 mt-0.5">Alterne o escopo de supervisão operacional</p>
        </div>

        {{-- 1. Visão Global --}}
        <button
            type="button"
            wire:click="selecionarModo('global')"
            @click="open = false"
            class="w-full flex items-start gap-3 p-2.5 rounded-xl text-left transition-colors duration-150 group
                {{ $modo === 'global' ? 'bg-sky-50/80 dark:bg-sky-950/30 text-sky-950 dark:text-sky-100' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/60 text-zinc-700 dark:text-zinc-300' }}
            "
        >
            <div class="p-2 rounded-lg {{ $modo === 'global' ? 'bg-sky-500 text-white shadow-sm shadow-sky-500/30' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 group-hover:bg-sky-100 group-hover:text-sky-700' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold">Visão Global</span>
                    @if($modo === 'global')
                        <span class="inline-flex items-center text-[11px] font-bold text-sky-600 dark:text-sky-400">Ativa &check;</span>
                    @endif
                </div>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">
                    Consolidado nacional de todas as 18 províncias e municípios.
                </p>
            </div>
        </button>

        {{-- 2. Visão Nacional --}}
        <button
            type="button"
            wire:click="selecionarModo('nacional')"
            @click="open = false"
            class="w-full flex items-start gap-3 p-2.5 rounded-xl text-left transition-colors duration-150 group
                {{ $modo === 'nacional' ? 'bg-red-50/80 dark:bg-red-950/30 text-red-950 dark:text-red-100' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/60 text-zinc-700 dark:text-zinc-300' }}
            "
        >
            <div class="p-2 rounded-lg {{ $modo === 'nacional' ? 'bg-red-600 text-white shadow-sm shadow-red-600/30' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 group-hover:bg-red-100 group-hover:text-red-700' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A1.5 1.5 0 0018 8.833H6a1.5 1.5 0 00-1.5 1.5V21h15z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold">Visão Nacional</span>
                    @if($modo === 'nacional')
                        <span class="inline-flex items-center text-[11px] font-bold text-red-600 dark:text-red-400">Ativa &check;</span>
                    @endif
                </div>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">
                    Núcleo Central, supervisão de nós e governação nacional.
                </p>
            </div>
        </button>

        {{-- 3. Visão por Província --}}
        <div class="rounded-xl border border-transparent transition-colors duration-150 {{ $modo === 'provincial' ? 'bg-emerald-50/80 dark:bg-emerald-950/30 border-emerald-200/50 dark:border-emerald-800/40' : '' }}">
            <button
                type="button"
                @click="showProvincias = !showProvincias"
                class="w-full flex items-start gap-3 p-2.5 rounded-xl text-left transition-colors duration-150 group hover:bg-zinc-50 dark:hover:bg-zinc-800/60 text-zinc-700 dark:text-zinc-300"
            >
                <div class="p-2 rounded-lg {{ $modo === 'provincial' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 group-hover:bg-emerald-100 group-hover:text-emerald-700' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold">Visão por Província</span>
                        <div class="flex items-center gap-1.5">
                            @if($modo === 'provincial')
                                <span class="inline-flex items-center text-[11px] font-bold text-emerald-600 dark:text-emerald-400">Ativa &check;</span>
                            @endif
                            <svg class="w-3.5 h-3.5 text-zinc-400 transition-transform duration-200" :class="{ 'rotate-180': showProvincias }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">
                        {{ $modo === 'provincial' && $provinciaAtual ? "Província atual: {$provinciaAtual->nome}" : "Filtrar e operar no contexto de uma província." }}
                    </p>
                </div>
            </button>

            {{-- Sub-lista colapsável de Províncias --}}
            <div x-show="showProvincias" x-collapse class="px-2 pb-2 pt-1 border-t border-zinc-200/40 dark:border-zinc-800/60 mt-1">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-1.5 px-2">Selecione a Província:</p>
                <div class="max-h-48 overflow-y-auto space-y-0.5 pr-1">
                    @foreach($provincias as $prov)
                        <button
                            type="button"
                            wire:click="selecionarProvincia({{ $prov->id }})"
                            @click="open = false"
                            class="w-full flex items-center justify-between px-2.5 py-1.5 text-xs rounded-lg text-left transition-colors
                                {{ $modo === 'provincial' && $provinciaAtual?->id === $prov->id ? 'bg-emerald-600 text-white font-medium shadow-xs' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/80' }}
                            "
                        >
                            <span>{{ $prov->nome }}</span>
                            @if($prov->codigo)
                                <span class="text-[10px] uppercase opacity-75">{{ $prov->codigo }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div
    class="relative w-72 max-w-[52vw]"
    x-data="{ open: false, active: -1 }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false; active = -1"
>
    <label class="sr-only" for="global-live-search">Pesquisa global</label>

    <div class="relative">
        <flux:icon name="magnifying-glass" class="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
        <input
            id="global-live-search"
            type="search"
            wire:model.live.debounce.300ms="query"
            x-on:focus="open = true"
            x-on:input="open = true; active = -1"
            x-on:keydown.arrow-down.prevent="
                const items = $el.closest('[x-data]').querySelectorAll('[data-search-result]');
                active = Math.min(active + 1, items.length - 1);
                items[active]?.focus();
            "
            x-on:keydown.arrow-up.prevent="
                const items = $el.closest('[x-data]').querySelectorAll('[data-search-result]');
                active = Math.max(active - 1, 0);
                items[active]?.focus();
            "
            x-on:keydown.enter.prevent="
                const items = $el.closest('[x-data]').querySelectorAll('[data-search-result]');
                items[active]?.click();
            "
            placeholder="Pesquisar processos, locais e pessoas"
            class="h-10 w-full rounded-md border border-zinc-200 bg-zinc-50 py-2 ps-9 pe-9 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-angola-red focus:bg-white focus:ring-2 focus:ring-angola-red/15 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:bg-zinc-900"
            autocomplete="off"
            role="combobox"
            aria-autocomplete="list"
            x-bind:aria-expanded="open.toString()"
            aria-controls="global-search-results"
        />

        @if($query !== '')
            <button
                type="button"
                wire:click="clear"
                x-on:click="open = false; active = -1"
                class="absolute end-2 top-1/2 flex size-6 -translate-y-1/2 items-center justify-center rounded text-zinc-400 transition hover:bg-zinc-200 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
                aria-label="Limpar pesquisa"
            >
                <flux:icon name="x-mark" class="size-4" />
            </button>
        @endif
    </div>

    @if(mb_strlen(trim($query)) >= 2)
        <div
            id="global-search-results"
            x-cloak
            x-show="open"
            x-transition.opacity.duration.150ms
            class="absolute end-0 z-50 mt-2 w-[min(36rem,calc(100vw-2rem))] overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
            role="listbox"
        >
            <div class="flex items-center justify-between border-b border-zinc-100 px-3 py-2 dark:border-zinc-800">
                <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                    {{ $totalResultados }} {{ $totalResultados === 1 ? 'resultado' : 'resultados' }}
                </p>
                <button type="button" x-on:click="open = false" class="text-xs font-semibold text-angola-red hover:underline">Fechar</button>
            </div>

            <div class="flex gap-1 overflow-x-auto border-b border-zinc-100 px-2 py-2 dark:border-zinc-800">
                @foreach(['all' => 'Todos', 'municipios' => 'Locais', 'operacoes' => 'Operações', 'instrumentos' => 'Instrumentos', 'loteamentos' => 'Loteamentos', 'pareceres' => 'Pareceres'] as $key => $label)
                    <button
                        type="button"
                        wire:click="$set('scope', '{{ $key }}')"
                        x-on:click="active = -1"
                        @class([
                            'shrink-0 rounded-md px-2.5 py-1 text-xs font-semibold transition-colors',
                            'bg-angola-red text-white' => $scope === $key,
                            'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' => $scope !== $key,
                        ])
                    >{{ $label }}</button>
                @endforeach
                @if(auth()->user()?->temPermissao('usuarios.ver'))
                    <button
                        type="button"
                        wire:click="$set('scope', 'utilizadores')"
                        x-on:click="active = -1"
                        @class([
                            'shrink-0 rounded-md px-2.5 py-1 text-xs font-semibold transition-colors',
                            'bg-angola-red text-white' => $scope === 'utilizadores',
                            'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' => $scope !== 'utilizadores',
                        ])
                    >Utilizadores</button>
                @endif
            </div>

            <div class="max-h-[min(28rem,calc(100vh-8rem))] overflow-y-auto py-1">
                @php($index = 0)
                @forelse($resultados as $categoria => $itens)
                    <div class="px-3 pb-1 pt-3 text-xs font-bold text-zinc-500 dark:text-zinc-400">{{ $categoria }}</div>
                    @foreach($itens as $resultado)
                        <a
                            href="{{ $resultado['url'] }}"
                            wire:navigate
                            data-search-result
                            x-ref="result-{{ $index }}"
                            x-on:focus="active = {{ $index }}"
                            class="flex items-center gap-3 px-3 py-2.5 outline-none transition hover:bg-zinc-50 focus:bg-zinc-50 dark:hover:bg-zinc-800 dark:focus:bg-zinc-800"
                            role="option"
                        >
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-md bg-zinc-100 text-angola-red dark:bg-zinc-800">
                                <flux:icon :name="$resultado['icone']" class="size-4" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $resultado['titulo'] }}</span>
                                <span class="mt-0.5 block truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $resultado['detalhe'] }}</span>
                            </span>
                            <flux:icon name="chevron-right" class="size-4 shrink-0 text-zinc-400" />
                        </a>
                        @php($index++)
                    @endforeach
                @empty
                    <div class="px-5 py-10 text-center">
                        <flux:icon name="magnifying-glass" class="mx-auto size-6 text-zinc-300 dark:text-zinc-600" />
                        <p class="mt-3 text-sm font-semibold text-zinc-700 dark:text-zinc-200">Sem resultados</p>
                        <p class="mt-1 text-xs leading-5 text-zinc-500 dark:text-zinc-400">Tente outro nome, referência ou identificador.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>

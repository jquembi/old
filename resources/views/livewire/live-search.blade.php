<div class="relative w-64 max-w-[45vw]" x-data="{ open: false }" x-on:click.outside="open = false">
    <label class="sr-only" for="global-live-search">Pesquisar</label>
    <input
        id="global-live-search"
        type="search"
        wire:model.live.debounce.250ms="query"
        x-on:focus="open = true"
        x-on:input="open = true"
        placeholder="Pesquisar..."
        class="h-9 w-full rounded-lg border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-red-300 focus:bg-white focus:ring-2 focus:ring-red-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-red-700 dark:focus:bg-zinc-900 dark:focus:ring-red-950"
        autocomplete="off"
    />

    @if(strlen(trim($query)) >= 2)
        <div
            x-cloak
            x-show="open"
            class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
        >
            @forelse($resultados as $resultado)
                <a
                    href="{{ $resultado['url'] }}"
                    wire:navigate
                    class="block border-b border-zinc-100 px-3 py-2.5 last:border-0 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800"
                >
                    <span class="block truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $resultado['titulo'] }}</span>
                    <span class="mt-0.5 block truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $resultado['subtitulo'] }}</span>
                </a>
            @empty
                <div class="px-3 py-6 text-center text-sm text-zinc-500">Sem resultados.</div>
            @endforelse
        </div>
    @endif
</div>

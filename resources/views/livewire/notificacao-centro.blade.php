<div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
    <button
        type="button"
        x-on:click="open = ! open"
        class="relative inline-flex size-9 items-center justify-center rounded-lg text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white"
        title="Notificacoes"
    >
        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.85 23.85 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022 23.85 23.85 0 0 0 5.455 1.31m5.714 0a3 3 0 0 1-5.714 0" />
        </svg>
        @if($naoLidas > 0)
            <span class="absolute -right-0.5 -top-0.5 min-w-4 rounded-full bg-red-600 px-1 text-[10px] font-semibold leading-4 text-white">{{ $naoLidas > 9 ? '9+' : $naoLidas }}</span>
        @endif
    </button>

    <div
        x-cloak
        x-show="open"
        class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
    >
        <div class="flex items-center justify-between border-b border-zinc-100 px-3 py-2 dark:border-zinc-800">
            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Notificacoes</p>
            @if($naoLidas > 0)
                <button type="button" wire:click="marcarTodasComoLidas" class="text-xs font-medium text-red-600 hover:text-red-700 dark:text-red-400">
                    Marcar todas
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse($notificacoes as $notificacao)
                <div class="border-b border-zinc-100 px-3 py-2.5 last:border-0 dark:border-zinc-800">
                    <div class="flex items-start gap-2">
                        @unless($notificacao->lida)
                            <span class="mt-1.5 size-2 shrink-0 rounded-full bg-red-500"></span>
                        @else
                            <span class="mt-1.5 size-2 shrink-0 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                        @endunless

                        <a
                            href="{{ $notificacao->destino() ?? route('notificacoes.index') }}"
                            wire:navigate
                            wire:click="marcarComoLida({{ $notificacao->id }})"
                            class="min-w-0 flex-1"
                        >
                            <span class="block truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $notificacao->titulo }}</span>
                            @if($notificacao->mensagem)
                                <span class="mt-0.5 block line-clamp-2 text-xs text-zinc-500 dark:text-zinc-400">{{ $notificacao->mensagem }}</span>
                            @endif
                            <span class="mt-1 block text-[11px] text-zinc-400">{{ $notificacao->created_at?->diffForHumans() }}</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-3 py-8 text-center text-sm text-zinc-500">Sem notificacoes.</div>
            @endforelse
        </div>

        <a href="{{ route('notificacoes.index') }}" wire:navigate class="block border-t border-zinc-100 px-3 py-2 text-center text-xs font-medium text-red-600 hover:bg-zinc-50 dark:border-zinc-800 dark:text-red-400 dark:hover:bg-zinc-800">
            Ver todas
        </a>
    </div>
</div>

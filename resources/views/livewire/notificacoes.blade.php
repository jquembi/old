<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl">Notificações</flux:heading>
            <flux:text class="mt-1">Consulte todas as notificações do sistema e aceda ao destino de cada item.</flux:text>
        </div>
        <div class="flex gap-3 text-sm">
            <div class="rounded-lg border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs text-zinc-500">Total</p>
                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $total }}</p>
            </div>
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 dark:border-red-900/50 dark:bg-red-950/30">
                <p class="text-xs text-red-600 dark:text-red-300">Não lidas</p>
                <p class="text-2xl font-semibold text-red-700 dark:text-red-200">{{ $naoLidas }}</p>
            </div>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
        <div class="min-w-[220px] flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar notificações..." icon="magnifying-glass" />
        </div>
        <select wire:model.live="filtro" class="w-44 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
            <option value="todas">Todas</option>
            <option value="nao_lidas">Não lidas</option>
        </select>
        @if($naoLidas > 0)
            <flux:button wire:click="marcarTodasComoLidas" variant="ghost" icon="check-circle">Marcar todas</flux:button>
        @endif
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse($notificacoes as $notificacao)
                <div class="flex flex-col gap-3 p-4 transition hover:bg-zinc-50 dark:hover:bg-zinc-800/60 sm:flex-row sm:items-start sm:justify-between {{ ! $notificacao->lida ? 'bg-blue-50/40 dark:bg-zinc-800/30' : '' }}">
                    <button wire:click="abrir({{ $notificacao->id }})" class="flex min-w-0 flex-1 items-start gap-3 text-left">
                        <span @class([
                            'mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg',
                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' => $notificacao->tipo === 'success',
                            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => $notificacao->tipo === 'error',
                            'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' => $notificacao->tipo === 'warning',
                            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' => ! in_array($notificacao->tipo, ['success', 'error', 'warning']),
                        ])>
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="flex items-center gap-2">
                                <span class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $notificacao->titulo }}</span>
                                @unless($notificacao->lida)
                                    <span class="size-2 rounded-full bg-red-500"></span>
                                @endunless
                            </span>
                            @if($notificacao->mensagem)
                                <span class="mt-1 block text-sm text-zinc-600 dark:text-zinc-300">{{ $notificacao->mensagem }}</span>
                            @endif
                            <span class="mt-1 block text-xs text-zinc-400">
                                {{ $notificacao->created_at->format('d/m/Y H:i') }}
                                @if($notificacao->destino())
                                    · Abrir área relacionada
                                @endif
                            </span>
                        </span>
                    </button>

                    <div class="flex shrink-0 justify-end gap-2">
                        @unless($notificacao->lida)
                            <flux:button wire:click="marcarComoLida({{ $notificacao->id }})" size="sm" variant="ghost">Lida</flux:button>
                        @endunless
                        <flux:button wire:click="eliminar({{ $notificacao->id }})" size="sm" variant="ghost" icon="trash">Remover</flux:button>
                    </div>
                </div>
            @empty
                <div class="px-4 py-12 text-center text-sm text-zinc-500">Nenhuma notificação encontrada.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">{{ $notificacoes->links() }}</div>
</div>

<div class="px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">
    <x-ui.page-header eyebrow="Gestão" title="Erros do Sistema" description="Central de acompanhamento de falhas, recorrência e resolução técnica.">
        <x-slot:actions>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Última falha registada:
                <span class="font-medium text-zinc-800 dark:text-zinc-100">{{ $ultimaFalha ? \Illuminate\Support\Carbon::parse($ultimaFalha)->format('d/m/Y H:i:s') : 'Sem registos' }}</span>
            </p>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Total registado</p>
            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $total }}</p>
        </div>
        <div class="rounded-xl border border-red-200 bg-red-50 p-5 dark:border-red-900/50 dark:bg-red-950/30">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-600 dark:text-red-300">Pendentes</p>
            <p class="mt-2 text-3xl font-semibold text-red-700 dark:text-red-200">{{ $pendentes }}</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900/50 dark:bg-amber-950/30">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">Recorrentes</p>
            <p class="mt-2 text-3xl font-semibold text-amber-800 dark:text-amber-200">{{ $recorrentes }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-900/50 dark:bg-emerald-950/30">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">Resolvidos</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-700 dark:text-emerald-200">{{ $resolvidos }}</p>
        </div>
    </div>

    <div class="mb-5 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_180px_160px_160px] xl:items-end">
            <div>
                <flux:input wire:model.live.debounce.300ms="search" label="Pesquisa" placeholder="Pesquisar por exceção, mensagem, URL, ficheiro ou utilizador..." icon="magnifying-glass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Estado</label>
                <select wire:model.live="status" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                    <option value="">Todos</option>
                    <option value="pendente">Pendentes</option>
                    <option value="resolvido">Resolvidos</option>
                </select>
            </div>
            <flux:input wire:model.live="dataInicio" type="date" label="De" />
            <flux:input wire:model.live="dataFim" type="date" label="Até" />
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Estado</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Falha</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Origem</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Ocorrências</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Última falha</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($erros as $erro)
                        <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                            <td class="px-5 py-4 align-top">
                                @if($erro->status === 'resolvido')
                                    <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300">Resolvido</span>
                                @else
                                    <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">Pendente</span>
                                @endif
                            </td>
                            <td class="max-w-[520px] px-5 py-4 align-top">
                                <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ class_basename($erro->exception_class) }}</p>
                                <p class="mt-1 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-300">{{ $erro->message ?: 'Sem mensagem registada.' }}</p>
                                <p class="mt-2 truncate font-mono text-[11px] text-zinc-400">{{ $erro->file }}:{{ $erro->line }}</p>
                            </td>
                            <td class="max-w-[260px] px-5 py-4 align-top text-sm">
                                <p class="truncate font-medium text-zinc-700 dark:text-zinc-200">{{ $erro->user?->name ?? 'Sistema' }}</p>
                                <p class="mt-1 truncate font-mono text-xs text-zinc-400">{{ $erro->method }} {{ $erro->url }}</p>
                                <p class="mt-1 font-mono text-xs text-zinc-400">{{ $erro->ip ?: 'IP não registado' }}</p>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <span class="inline-flex min-w-10 justify-center rounded-full bg-zinc-100 px-3 py-1 text-sm font-semibold text-zinc-800 dark:bg-zinc-800 dark:text-zinc-100">
                                    {{ $erro->occurrences }}
                                </span>
                            </td>
                            <td class="px-5 py-4 align-top text-sm text-zinc-500">
                                {{ $erro->last_seen_at?->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('erros-sistema.show', $erro) }}" wire:navigate>
                                        <flux:button size="sm" variant="ghost" icon="eye">Detalhe</flux:button>
                                    </a>
                                    @if($erro->status === 'resolvido')
                                        <flux:button wire:click="reabrir({{ $erro->id }})" size="sm" variant="ghost">Reabrir</flux:button>
                                    @else
                                        <flux:button wire:click="marcarResolvido({{ $erro->id }})" size="sm" variant="primary">Resolver</flux:button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
                                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Nenhum erro encontrado.</p>
                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Ajuste os filtros ou aguarde novos registos do sistema.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $erros->links() }}</div>
</div>

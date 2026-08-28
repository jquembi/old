<div class="w-full">
    <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                <a href="{{ route('erros-sistema.index') }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-200">Erros do Sistema</a>
                <span>/</span>
                <span>Erro #{{ $erro->id }}</span>
            </div>
            <flux:heading size="xl">Detalhe do Erro #{{ $erro->id }}</flux:heading>
            <flux:text class="mt-1">{{ $erro->exception_class }}</flux:text>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if($erro->status === 'resolvido')
                <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300">Resolvido</span>
            @else
                <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-3 py-1 text-sm font-semibold text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">Pendente</span>
            @endif

            <a href="{{ route('erros-sistema.index') }}" wire:navigate>
                <flux:button variant="ghost" icon="arrow-left">Voltar</flux:button>
            </a>
        </div>
    </div>

    <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Ocorrências</p>
            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $erro->occurrences }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Ambiente</p>
            <p class="mt-2 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $erro->environment ?: '—' }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Primeira falha</p>
            <p class="mt-2 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $erro->first_seen_at?->format('d/m/Y H:i:s') }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Última falha</p>
            <p class="mt-2 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $erro->last_seen_at?->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="space-y-6">
            <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Mensagem da falha</h2>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ class_basename($erro->exception_class) }}</span>
                </div>
                <p class="whitespace-pre-wrap rounded-lg border border-red-100 bg-red-50 p-4 text-sm leading-relaxed text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200">
                    {{ $erro->message ?: 'Sem mensagem registada.' }}
                </p>
            </section>

            <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Localização técnica</h2>
                <dl class="grid gap-4 text-sm lg:grid-cols-2">
                    <div>
                        <dt class="text-zinc-500">Ficheiro</dt>
                        <dd class="break-all font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $erro->file ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Linha</dt>
                        <dd class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $erro->line ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Classe da exceção</dt>
                        <dd class="break-all font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $erro->exception_class }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Fingerprint</dt>
                        <dd class="break-all font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $erro->fingerprint ?: '—' }}</dd>
                    </div>
                </dl>
            </section>

            @if($erro->context)
                <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500">Contexto capturado</h2>
                    <pre class="max-h-96 overflow-auto rounded-lg bg-zinc-100 p-4 text-xs text-zinc-800 dark:bg-zinc-950 dark:text-zinc-100">{{ json_encode($erro->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </section>
            @endif

            <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500">Stack trace</h2>
                <pre class="max-h-[620px] overflow-auto rounded-lg bg-zinc-950 p-4 text-xs leading-relaxed text-zinc-100">{{ $erro->trace ?: 'Trace não registado.' }}</pre>
            </section>
        </div>

        <aside class="space-y-6 xl:sticky xl:top-20 xl:self-start">
            <section class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Requisição</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-zinc-500">URL</dt>
                        <dd class="break-all font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $erro->url ?: '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <dt class="text-zinc-500">Método</dt>
                            <dd class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $erro->method ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">IP</dt>
                            <dd class="font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $erro->ip ?: '—' }}</dd>
                        </div>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Utilizador</dt>
                        <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $erro->user?->name ?? 'Sistema' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">User-Agent</dt>
                        <dd class="break-all font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $erro->user_agent ?: '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Resolução</h2>

                @if($erro->status === 'resolvido')
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300">
                        Resolvido por {{ $erro->resolvedBy?->name ?? 'Sistema' }} em {{ $erro->resolved_at?->format('d/m/Y H:i:s') }}.
                    </div>
                @else
                    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200">
                        Este erro ainda está pendente de análise técnica.
                    </div>
                @endif

                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Nota de resolução</label>
                <textarea wire:model="resolutionNote" rows="6" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 shadow-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"></textarea>

                <div class="mt-4 flex justify-end gap-2">
                    @if($erro->status === 'resolvido')
                        <flux:button wire:click="reabrir" variant="ghost">Reabrir</flux:button>
                    @else
                        <flux:button wire:click="marcarResolvido" variant="primary">Marcar como resolvido</flux:button>
                    @endif
                </div>
            </section>
        </aside>
    </div>
</div>

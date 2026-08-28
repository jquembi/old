<div class="w-full">
    <div class="mb-6 flex items-center justify-between gap-3">
        <div>
            <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                <a href="{{ route('auditoria.index', ['tab' => 'logs']) }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-200">Auditoria</a>
                <span>/</span>
                <span>Log #{{ $log->id }}</span>
            </div>
            <flux:heading size="xl">Detalhe do Log #{{ $log->id }}</flux:heading>
            <flux:text class="mt-1">Rastreamento de acesso, navegacao e contexto tecnico da requisicao.</flux:text>
        </div>

        <a href="{{ route('auditoria.index', ['tab' => 'logs']) }}" wire:navigate>
            <flux:button variant="ghost" icon="arrow-left">Voltar</flux:button>
        </a>
    </div>

    <div class="mb-6 grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase text-zinc-500">Acao</p>
            <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $log->accao }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase text-zinc-500">Recurso</p>
            <p class="mt-1 truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ $log->recurso ?: '—' }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase text-zinc-500">IP</p>
            <p class="mt-1 font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $log->ip_address ?: '—' }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase text-zinc-500">Data</p>
            <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <div class="space-y-6">
        <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Identificacao do utilizador</h2>
            <dl class="grid gap-4 text-sm md:grid-cols-3">
                <div>
                    <dt class="text-zinc-500">Utilizador</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $log->user?->name ?? 'Sistema' }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Email</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $log->user?->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Perfis / regras</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $log->user?->roles?->pluck('nome')->join(', ') ?: '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Recurso e acesso</h2>
            <dl class="grid gap-4 text-sm md:grid-cols-3">
                <div>
                    <dt class="text-zinc-500">Acao</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $log->accao }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Recurso</dt>
                    <dd class="font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $log->recurso ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">ID do recurso</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $log->recurso_id ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">IP de origem</dt>
                    <dd class="font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $log->ip_address ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Criado em</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $log->created_at->format('d/m/Y H:i:s') }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Atualizado em</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $log->updated_at->format('d/m/Y H:i:s') }}</dd>
                </div>
            </dl>
        </section>

        @if($detalhesEstruturados)
            <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500">Detalhes estruturados</h2>
                <pre class="max-h-80 overflow-auto rounded-lg bg-zinc-100 p-3 text-xs dark:bg-zinc-800">{{ json_encode($detalhesEstruturados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </section>
        @elseif($log->detalhes)
            <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500">Detalhes</h2>
                <pre class="max-h-80 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-zinc-100 p-3 text-xs dark:bg-zinc-800">{{ $log->detalhes }}</pre>
            </section>
        @endif

        <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500">Dispositivo / navegador</h2>
            <pre class="max-h-56 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-zinc-100 p-3 text-xs dark:bg-zinc-800">{{ $log->user_agent ?: 'User agent nao registado.' }}</pre>
        </section>
    </div>
</div>

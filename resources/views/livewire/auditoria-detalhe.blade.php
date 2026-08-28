<div class="w-full">
    <div class="mb-6 flex items-center justify-between gap-3">
        <div>
            <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                <a href="{{ route('auditoria.index', ['tab' => 'auditoria']) }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-200">Auditoria</a>
                <span>/</span>
                <span>Registo #{{ $auditoria->id }}</span>
            </div>
            <flux:heading size="xl">Detalhe da Auditoria #{{ $auditoria->id }}</flux:heading>
            <flux:text class="mt-1">Rastreamento institucional da acao, usuario, recurso e contexto da requisicao.</flux:text>
        </div>

        <a href="{{ route('auditoria.index', ['tab' => 'auditoria']) }}" wire:navigate>
            <flux:button variant="ghost" icon="arrow-left">Voltar</flux:button>
        </a>
    </div>

    <div class="mb-6 grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase text-zinc-500">Acao</p>
            <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ ucfirst($auditoria->accao) }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase text-zinc-500">Campos alterados</p>
            <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $resumo['campos_alterados'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase text-zinc-500">Request ID</p>
            <p class="mt-1 truncate font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $auditoria->request_id ?: 'Nao registado' }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase text-zinc-500">Data</p>
            <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $auditoria->created_at->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <div class="space-y-6">
        <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Identificacao e responsabilidade</h2>
            <dl class="grid gap-4 text-sm md:grid-cols-3">
                <div>
                    <dt class="text-zinc-500">Utilizador</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $auditoria->user?->name ?? 'Sistema' }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Email</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $auditoria->user?->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Perfis / regras</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $auditoria->user?->roles?->pluck('nome')->join(', ') ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Municipio do utilizador</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $contexto['usuario']['municipio_id'] ?? $auditoria->user?->municipio_id ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Provincia do utilizador</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $contexto['usuario']['provincia_id'] ?? $auditoria->user?->provincia_id ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">IP de origem</dt>
                    <dd class="font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $auditoria->ip_address ?: '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Recurso auditado</h2>
            <dl class="grid gap-4 text-sm md:grid-cols-3">
                <div>
                    <dt class="text-zinc-500">Modelo</dt>
                    <dd class="font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $auditoria->modelo }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Tabela</dt>
                    <dd class="font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $contexto['modelo_tabela'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">ID do registo</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $auditoria->modelo_id }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Chave do modelo</dt>
                    <dd class="font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $contexto['modelo_chave'] ?? 'id' }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Campos antes</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $resumo['campos_antes'] }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Campos depois</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $resumo['campos_depois'] }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Rastreamento da requisicao</h2>
            <dl class="grid gap-4 text-sm md:grid-cols-2">
                <div>
                    <dt class="text-zinc-500">Metodo HTTP</dt>
                    <dd class="font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $auditoria->method ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Rota</dt>
                    <dd class="font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $auditoria->route_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Sessao</dt>
                    <dd class="font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $auditoria->session_id ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Ambiente</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $contexto['ambiente'] ?? '—' }}</dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-zinc-500">URL</dt>
                    <dd class="break-all font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $auditoria->url ?: '—' }}</dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-zinc-500">User agent</dt>
                    <dd class="break-words font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $auditoria->user_agent ?: '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Comparacao dos dados</h2>

            @if(!empty($camposAlterados))
                <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="min-w-full divide-y divide-zinc-200 text-xs dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-zinc-500">Campo</th>
                                <th class="px-3 py-2 text-left font-medium text-zinc-500">Valor anterior</th>
                                <th class="px-3 py-2 text-left font-medium text-zinc-500">Valor novo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($camposAlterados as $alteracao)
                                <tr>
                                    <td class="px-3 py-2 font-mono text-zinc-700 dark:text-zinc-300">{{ $alteracao['campo'] }}</td>
                                    <td class="px-3 py-2 text-zinc-600 dark:text-zinc-400">
                                        <pre class="whitespace-pre-wrap break-words">{{ is_scalar($alteracao['antigo']) || is_null($alteracao['antigo']) ? ($alteracao['antigo'] ?? '—') : json_encode($alteracao['antigo'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                                    </td>
                                    <td class="px-3 py-2 text-zinc-900 dark:text-zinc-100">
                                        <pre class="whitespace-pre-wrap break-words">{{ is_scalar($alteracao['novo']) || is_null($alteracao['novo']) ? ($alteracao['novo'] ?? '—') : json_encode($alteracao['novo'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-400">
                    Nao foram registadas diferencas campo a campo para esta acao. Consulte os dados brutos abaixo para verificacao.
                </div>
            @endif
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500">Valores anteriores</h2>
                <pre class="max-h-96 overflow-auto rounded-lg bg-zinc-100 p-3 text-xs dark:bg-zinc-800">{{ $auditoria->valores_antigos ? json_encode($auditoria->valores_antigos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'Sem valores anteriores registados.' }}</pre>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500">Valores novos / detalhes</h2>
                <pre class="max-h-96 overflow-auto rounded-lg bg-zinc-100 p-3 text-xs dark:bg-zinc-800">{{ $auditoria->valores_novos ? json_encode($auditoria->valores_novos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'Sem valores novos registados.' }}</pre>
            </div>
        </section>

        @if(!empty($contexto))
            <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500">Contexto tecnico completo</h2>
                <pre class="max-h-96 overflow-auto rounded-lg bg-zinc-100 p-3 text-xs dark:bg-zinc-800">{{ json_encode($contexto, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </section>
        @endif
    </div>
</div>

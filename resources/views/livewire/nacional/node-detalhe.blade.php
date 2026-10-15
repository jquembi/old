<div class="ui-module px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">
    <x-ui.page-header
        eyebrow="Núcleo nacional · Node"
        :title="$tenant->name"
        :description="'Código '.$tenant->code.' · '.($tenant->isCentral() ? 'Núcleo central' : 'Node provincial')"
    >
        <x-slot:actions>
            <a href="{{ route('nacional.nodes') }}" wire:navigate class="btn-ghost border border-zinc-200 dark:border-zinc-700">Voltar</a>
            @if($podeGerir && $tenant->isProvincial())
                <button type="button" wire:click="sincronizar" class="btn-primary">Pedir sincronização</button>
                <button type="button" wire:click="alternarAtivo" wire:confirm="Confirmar a alteração de estado deste node?" class="btn-ghost border border-zinc-200 dark:border-zinc-700">{{ $tenant->is_active ? 'Desativar' : 'Ativar' }}</button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Estado" :value="! $tenant->is_active ? 'Inativo' : ucfirst($tenant->sync_status)" :detail="'Última comunicação: '.($tenant->last_sync_at?->diffForHumans() ?? 'nunca')" tone="red" />
        <x-ui.stat-card label="Sincronizações" :value="$totalSyncs" :detail="$sucessos.' com êxito · '.$falhas.' falhadas'" tone="green" />
        <x-ui.stat-card label="Registos enviados" :value="number_format($registos, 0, ',', '.')" detail="Total acumulado" tone="violet" />
        <x-ui.stat-card label="Utilizadores" :value="$utilizadores ?? '—'" detail="Associados à província" tone="yellow" />
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <x-ui.card title="Configuração" class="xl:col-span-1">
            <dl class="space-y-3 text-sm">
                @foreach([
                    'Base de dados' => $tenant->database_name,
                    'Servidor' => $tenant->database_host ?: 'Predefinido',
                    'Porta' => $tenant->database_port ?: 'Predefinida',
                    'Criado em' => $tenant->created_at?->format('d/m/Y H:i'),
                ] as $rotulo => $valor)
                    <div class="flex justify-between gap-3"><dt class="text-zinc-500 dark:text-zinc-400">{{ $rotulo }}</dt><dd class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $valor ?: '—' }}</dd></div>
                @endforeach
            </dl>
        </x-ui.card>

        <x-ui.card title="Atividade recente" description="Eventos de auditoria deste node." class="xl:col-span-2">
            <div class="space-y-3">
                @forelse($auditoria as $evento)
                    <article class="flex items-start justify-between gap-4 rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/30">
                        <div class="min-w-0"><p class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $evento->action }}</p><p class="mt-1 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $evento->description }}</p></div>
                        <time class="shrink-0 text-xs text-zinc-500">{{ $evento->created_at->diffForHumans() }}</time>
                    </article>
                @empty
                    <x-ui.empty-state title="Sem atividade registada" description="Os eventos deste node aparecerão aqui." />
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <x-ui.card title="Histórico de sincronização" padding="p-0">
        <x-slot:actions>
            <select aria-label="Filtrar por estado" wire:model.live="estadoSync" class="px-3 py-1.5 border rounded-lg text-sm focus:ring-2 focus:ring-angola-red">
                <option value="">Todos</option><option value="success">Sucesso</option><option value="failed">Falhada</option><option value="pending">Pendente</option><option value="partial">Parcial</option>
            </select>
        </x-slot:actions>
        <div class="overflow-x-auto">
            <table class="table-gov min-w-[720px]">
                <thead><tr><th>Início</th><th>Tipo</th><th>Estado</th><th>Registos</th><th>Duração</th><th>Erro</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->started_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>{{ str_replace('_', ' ', $log->sync_type) }}</td>
                            <td><x-ui.status-badge :status="$log->status" /></td>
                            <td>{{ $log->records_count }}</td>
                            <td>{{ $log->started_at && $log->completed_at ? $log->started_at->diffInSeconds($log->completed_at).'s' : '—' }}</td>
                            <td class="max-w-xs truncate text-red-600" title="{{ $log->error_message }}">{{ $log->error_message ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="!p-0"><x-ui.empty-state title="Sem sincronizações" description="Ainda não há comunicação registada para este node." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $logs->links() }}</div>
    </x-ui.card>
</div>

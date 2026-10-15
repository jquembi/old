<div class="ui-module px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6" wire:poll.30s>
    <x-ui.page-header
        eyebrow="Núcleo nacional · Supervisão"
        title="Supervisão de Nodes"
        description="Monitorize a disponibilidade e o estado de sincronização dos nodes provinciais."
    />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Nodes registados" :value="$resumo['total']" :detail="$resumo['ativos'].' ativos'" tone="red" />
        <x-ui.stat-card label="Online" :value="$resumo['online']" detail="Ativos e a comunicar" tone="green" />
        <x-ui.stat-card label="Offline / inativos" :value="$resumo['total'] - $resumo['online']" detail="Requerem atenção" tone="yellow" />
        <x-ui.stat-card label="Falhas (24h)" :value="$resumo['falhas']" detail="Sincronizações falhadas" tone="violet" />
    </div>

    <div class="ui-card p-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" aria-label="Pesquisar nodes" wire:model.live.debounce.300ms="search" placeholder="Pesquisar por nome ou código..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
            <select aria-label="Filtrar por tipo" wire:model.live="tipo" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                <option value="">Todos os tipos</option>
                <option value="central">Central</option>
                <option value="provincial">Provincial</option>
            </select>
            <select aria-label="Filtrar por estado" wire:model.live="estado" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                <option value="">Todos os estados</option>
                <option value="ativo">Ativos</option>
                <option value="inativo">Inativos</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
                <option value="syncing">A sincronizar</option>
            </select>
        </div>
    </div>

    <div class="ui-card overflow-x-auto">
        <table class="table-gov min-w-[860px]">
            <thead>
                <tr><th>Código</th><th>Node</th><th>Tipo</th><th>Disponibilidade</th><th>Última sync</th><th>Falhas</th><th class="text-right">Ações</th></tr>
            </thead>
            <tbody>
                @forelse($nodes as $node)
                    @php
                        $referencia = $node->ultima_sync ? \Carbon\Carbon::parse($node->ultima_sync) : $node->last_sync_at;
                        $emAlerta = $node->is_active && $node->type === 'provincial' && (! $referencia || $referencia->diffInMinutes() > $limiteOffline);
                        $disp = ! $node->is_active ? 'inativo' : $node->sync_status;
                    @endphp
                    <tr>
                        <td class="font-bold text-zinc-900 dark:text-zinc-100">{{ $node->code }}</td>
                        <td>{{ $node->name }}</td>
                        <td><x-ui.status-badge :status="$node->type" /></td>
                        <td>
                            <x-ui.status-badge :status="$disp" />
                            @if($emAlerta)<span class="ml-1 text-xs font-semibold text-amber-600" title="Sem comunicação há mais de {{ $limiteOffline }} min">Em alerta</span>@endif
                        </td>
                        <td class="text-zinc-500 dark:text-zinc-400">{{ $referencia ? $referencia->diffForHumans() : 'Nunca' }}</td>
                        <td>{{ $node->falhas_count }}</td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('nacional.node.detalhe', $node->id) }}" wire:navigate class="text-blue-600 hover:text-blue-900 mr-2">Detalhes</a>
                            @if($podeGerir && $node->type === 'provincial')
                                <button type="button" wire:click="sincronizar({{ $node->id }})" class="text-emerald-600 hover:text-emerald-900 mr-2">Sincronizar</button>
                                <button type="button" wire:click="alternarAtivo({{ $node->id }})" wire:confirm="{{ $node->is_active ? 'Desativar' : 'Ativar' }} o node {{ $node->code }}?" class="text-orange-600 hover:text-orange-900">{{ $node->is_active ? 'Desativar' : 'Ativar' }}</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="!p-0"><x-ui.empty-state title="Nenhum node encontrado" description="Ajuste os filtros ou registe uma província." /></td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $nodes->links() }}</div>
    </div>
</div>

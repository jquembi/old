<div class="ui-module px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">
    <x-ui.page-header
        eyebrow="Núcleo nacional · Auditoria"
        :title="$eventos ? 'Auditoria Nacional' : 'Registos de Auditoria'"
        :description="$eventos ? 'Eventos de auditoria consolidados de toda a plataforma.' : 'Registos técnicos e operacionais: pedidos auditados e alterações de dados.'"
    >
        <x-slot:actions>
            <a href="{{ route($eventos ? 'nacional.auditoria.logs' : 'nacional.auditoria') }}" wire:navigate class="btn-ghost border border-zinc-200 dark:border-zinc-700">{{ $eventos ? 'Ver registos técnicos' : 'Ver eventos' }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Total de registos" :value="number_format($resumo['total'], 0, ',', '.')" detail="Desde o início" tone="red" />
        <x-ui.stat-card label="Últimas 24 horas" :value="number_format($resumo['hoje'], 0, ',', '.')" detail="Atividade recente" tone="green" />
        <x-ui.stat-card label="Erros por resolver" :value="$resumo['erros']" detail="Erros do sistema em aberto" tone="yellow" />
    </div>

    <div class="ui-card p-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-4">
            <input type="text" aria-label="Pesquisar" wire:model.live.debounce.400ms="search" placeholder="Pesquisar..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red xl:col-span-2">
            <select aria-label="Filtrar por ação" wire:model.live="acao" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                <option value="">Todas as ações</option>
                @foreach($acoes as $a)<option value="{{ $a }}">{{ $a }}</option>@endforeach
            </select>
            @if($eventos)
                <select aria-label="Filtrar por node" wire:model.live="tenantId" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todos os nodes</option>
                    @foreach($tenants as $id => $nome)<option value="{{ $id }}">{{ $nome }}</option>@endforeach
                </select>
            @endif
            <input type="date" aria-label="Data inicial" wire:model.live="de" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
            <input type="date" aria-label="Data final" wire:model.live="ate" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
        </div>
        <div class="mt-3"><button type="button" wire:click="limparFiltros" class="btn-ghost">Limpar filtros</button></div>
    </div>

    <div class="ui-card overflow-x-auto">
        <table class="table-gov min-w-[860px]">
            <thead>
                @if($eventos)
                    <tr><th>Data</th><th>Ação</th><th>Node</th><th>Utilizador</th><th>Descrição</th><th>IP</th><th></th></tr>
                @else
                    <tr><th>Data</th><th>Ação</th><th>Modelo</th><th>Utilizador</th><th>Rota / URL</th><th>IP</th><th></th></tr>
                @endif
            </thead>
            <tbody>
                @forelse($registos as $r)
                    <tr wire:key="reg-{{ $r->id }}">
                        <td class="whitespace-nowrap text-zinc-500 dark:text-zinc-400">{{ $r->created_at?->format('d/m/Y H:i') }}</td>
                        @if($eventos)
                            <td><span class="badge badge-zinc">{{ $r->action }}</span></td>
                            <td>{{ $r->tenant?->code ?? '—' }}</td>
                            <td>{{ $r->user?->name ?? 'Sistema' }}</td>
                            <td class="max-w-xs truncate" title="{{ $r->description }}">{{ $r->description ?: ($r->entity_type ?: '—') }}</td>
                        @else
                            <td><span class="badge badge-zinc">{{ $r->accao }}</span></td>
                            <td>{{ class_basename((string) $r->modelo) ?: '—' }}{{ $r->modelo_id ? ' #'.$r->modelo_id : '' }}</td>
                            <td>{{ $r->user?->name ?? 'Sistema' }}</td>
                            <td class="max-w-xs truncate" title="{{ $r->url }}">{{ $r->route_name ?: ($r->url ?: '—') }}</td>
                        @endif
                        <td class="text-zinc-500">{{ $r->ip_address ?: '—' }}</td>
                        <td class="text-right"><button type="button" wire:click="ver({{ $r->id }})" class="text-blue-600 hover:text-blue-900">Detalhes</button></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="!p-0"><x-ui.empty-state title="Nenhum registo encontrado" description="Ajuste os filtros para ampliar a pesquisa." /></td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $registos->links() }}</div>
    </div>

    @if($detalhe)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-zinc-950/60 p-4 backdrop-blur-sm" wire:click.self="fechar">
            <div role="dialog" aria-modal="true" aria-label="Detalhe do registo" class="ui-card relative mx-auto my-8 w-full max-w-2xl p-6">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-50">Registo #{{ $detalhe->id }}</h3>
                    <button type="button" wire:click="fechar" class="btn-ghost" aria-label="Fechar">Fechar</button>
                </div>
                @php
                    $antes = $eventos ? $detalhe->old_values : $detalhe->valores_antigos;
                    $depois = $eventos ? $detalhe->new_values : $detalhe->valores_novos;
                @endphp
                <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-zinc-500">Data</dt><dd class="font-semibold">{{ $detalhe->created_at?->format('d/m/Y H:i:s') }}</dd></div>
                    <div><dt class="text-zinc-500">Utilizador</dt><dd class="font-semibold">{{ $detalhe->user?->name ?? 'Sistema' }}</dd></div>
                    <div><dt class="text-zinc-500">IP</dt><dd class="font-semibold">{{ $detalhe->ip_address ?: '—' }}</dd></div>
                    <div><dt class="text-zinc-500">Dispositivo</dt><dd class="truncate font-semibold" title="{{ $detalhe->user_agent }}">{{ $detalhe->user_agent ?: '—' }}</dd></div>
                </dl>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach(['Antes' => $antes, 'Depois' => $depois] as $titulo => $dados)
                        <div>
                            <p class="mb-1 text-xs font-bold uppercase tracking-wider text-zinc-500">{{ $titulo }}</p>
                            <pre class="max-h-64 overflow-auto rounded-lg bg-zinc-50 p-3 text-xs dark:bg-zinc-800/60">{{ $dados ? json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '—' }}</pre>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

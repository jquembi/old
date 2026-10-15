<div class="ui-module px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">
    <x-ui.page-header
        eyebrow="Núcleo nacional · Administração"
        :title="$regras ? 'Regras Globais' : 'Parâmetros Nacionais'"
        :description="$regras ? 'Regras que orientam a governação nacional da plataforma e a matriz de perfis por nível.' : 'Parâmetros globais que orientam a operação da plataforma. Todas as alterações ficam auditadas.'"
    />

    @unless($podeEditar)
        <div class="border-s-4 border-angola-red bg-zinc-50 px-4 py-3 text-sm text-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">O seu perfil tem acesso de consulta. Apenas administradores nacionais podem alterar valores.</div>
    @endunless

    @unless($regras)
        <div class="ui-card p-5 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" aria-label="Pesquisar parâmetros" wire:model.live.debounce.300ms="search" placeholder="Pesquisar parâmetro..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                <select aria-label="Filtrar por grupo" wire:model.live="grupo" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todos os grupos</option>
                    @foreach($grupos as $chave => $rotulo)@if($chave !== 'regras')<option value="{{ $chave }}">{{ $rotulo }}</option>@endif @endforeach
                </select>
            </div>
        </div>
    @endunless

    @forelse($parametros as $nomeGrupo => $itens)
        <x-ui.card :title="$grupos[$nomeGrupo] ?? ucfirst($nomeGrupo)" padding="p-0">
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($itens as $p)
                    <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6" wire:key="param-{{ $p->id }}">
                        <div class="min-w-0 sm:max-w-md">
                            <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $p->rotulo }}</p>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $p->descricao }}</p>
                            <p class="mt-1 text-xs text-zinc-400">{{ $p->chave }}@if($p->utilizador) · alterado por {{ $p->utilizador->name }} {{ $p->updated_at->diffForHumans() }}@endif</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            @if($p->tipo === 'booleano')
                                <x-ui.status-badge :status="($valores[$p->id] ?? '0') === '1' ? 'ativo' : 'inativo'" :label="($valores[$p->id] ?? '0') === '1' ? 'Ativo' : 'Inativo'" />
                                @if($podeEditar && $p->editavel)
                                    <button type="button" wire:click="alternar({{ $p->id }})" class="btn-ghost border border-zinc-200 dark:border-zinc-700">{{ ($valores[$p->id] ?? '0') === '1' ? 'Desativar' : 'Ativar' }}</button>
                                @endif
                            @else
                                <div>
                                    <input type="{{ $p->tipo === 'numero' ? 'number' : 'text' }}" aria-label="{{ $p->rotulo }}" wire:model="valores.{{ $p->id }}" wire:keydown.enter="guardar({{ $p->id }})" @disabled(! $podeEditar || ! $p->editavel) class="w-48 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                                    @error('valores.'.$p->id)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                @if($podeEditar && $p->editavel)
                                    <button type="button" wire:click="guardar({{ $p->id }})" class="btn-primary">Guardar</button>
                                @endif
                            @endif
                            @if($podeEditar && $p->editavel && (string) $p->valor !== (string) $p->valor_padrao)
                                <button type="button" wire:click="repor({{ $p->id }})" class="btn-ghost" title="Repor valor padrão ({{ $p->valor_padrao }})">Repor</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    @empty
        <x-ui.empty-state title="Nenhum parâmetro encontrado" description="Ajuste a pesquisa ou o grupo." />
    @endforelse

    @if($regras)
        <x-ui.card title="Matriz de perfis" description="Perfis do sistema organizados por nível hierárquico." padding="p-0">
            <div class="overflow-x-auto">
                <table class="table-gov min-w-[640px]">
                    <thead><tr><th>Nível</th><th>Perfil</th><th>Grupo</th><th class="text-right">Permissões</th><th class="text-right">Utilizadores</th></tr></thead>
                    <tbody>
                        @forelse($perfis as $nivel => $roles)
                            @foreach($roles as $i => $role)
                                <tr>
                                    <td class="font-semibold">@if($i === 0){{ $niveis[$nivel] ?? 'Nível '.$nivel }}@endif</td>
                                    <td>{{ $role->descricao ?: $role->nome }}<span class="block text-xs text-zinc-400">{{ $role->nome }}</span></td>
                                    <td>{{ $role->grupo ?: '—' }}</td>
                                    <td class="text-right tabular-nums">{{ $role->permissoes_count }}</td>
                                    <td class="text-right tabular-nums">{{ $role->users_count }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="5" class="!p-0"><x-ui.empty-state title="Sem perfis registados" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end p-4"><a href="{{ route('permissoes.index') }}" wire:navigate class="btn-ghost border border-zinc-200 dark:border-zinc-700">Gerir permissões</a></div>
        </x-ui.card>
    @endif
</div>

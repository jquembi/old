<div>
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.requalificacao', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Requalificação</a>
            <span>/</span>
            <span>Outros Projectos</span>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <flux:heading size="xl">Outros Projectos</flux:heading>
                <flux:text class="mt-1">Projectos de requalificação e infraestrutura — {{ $municipio->nome }}</flux:text>
            </div>
            @if($podeEditar)
                <a href="{{ route('municipio.requalificacao.outros.criar', $municipio) }}" wire:navigate>
                    <flux:button variant="primary" icon="plus">Novo Projecto</flux:button>
                </a>
            @endif
        </div>
    </div>

    <div class="mb-4 grid gap-3 lg:grid-cols-[1fr_12rem_auto] lg:items-center">
        <div class="flex-1 min-w-[200px]">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar projecto, entidade ou fase..." icon="magnifying-glass" />
        </div>
        <flux:select wire:model.live="filtroEstado" class="w-48">
            <flux:select.option value="">Todos os estados</flux:select.option>
            <flux:select.option value="planificado">Planificado</flux:select.option>
            <flux:select.option value="em_curso">Em Curso</flux:select.option>
            <flux:select.option value="concluido">Concluído</flux:select.option>
            <flux:select.option value="suspenso">Suspenso</flux:select.option>
        </flux:select>
        <div class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
            {{ $projectos->total() }} {{ $projectos->total() === 1 ? 'projecto' : 'projectos' }}
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        @forelse($projectos as $proj)
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <flux:heading size="sm">{{ $proj->titulo }}</flux:heading>
                            @php
                                $estadoClasses = match($proj->estado) {
                                    'planificado' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
                                    'em_curso' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
                                    'concluido' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400',
                                    'suspenso' => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
                                    default => 'bg-zinc-100 text-zinc-800',
                                };
                                $tipoLabel = match($proj->tipo) {
                                    'requalificacao' => 'Requalificação',
                                    'infraestrutura' => 'Infraestrutura',
                                    'social' => 'Social',
                                    default => 'Outro',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoClasses }}">
                                {{ str_replace('_', ' ', ucfirst($proj->estado)) }}
                            </span>
                            <span class="inline-flex items-center rounded-full bg-cyan-100 px-2.5 py-0.5 text-xs font-medium text-cyan-800 dark:bg-cyan-900/20 dark:text-cyan-400">
                                {{ $tipoLabel }}
                            </span>
                        </div>
                        <flux:text class="mt-1 text-xs">
                            @if($proj->entidade_responsavel) {{ $proj->entidade_responsavel }} &middot; @endif
                            @if($proj->fase) Fase: {{ $proj->fase }} &middot; @endif
                            {{ $proj->created_at->format('d/m/Y') }}
                        </flux:text>
                        @if($proj->descricao)
                            <flux:text class="mt-2 text-sm">{{ Str::limit($proj->descricao, 200) }}</flux:text>
                        @endif

                        <div class="mt-3 flex flex-wrap gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                            @if($proj->data_inicio)
                                <span>Início: {{ $proj->data_inicio->format('d/m/Y') }}</span>
                            @endif
                            @if($proj->data_previsao_fim)
                                <span>Previsão: {{ $proj->data_previsao_fim->format('d/m/Y') }}</span>
                            @endif
                            @if(!$proj->data_inicio && !$proj->data_previsao_fim)
                                <span>Datas por definir</span>
                            @endif
                        </div>
                    </div>

                    @if($podeEditar)
                        <div class="flex gap-1 shrink-0 ml-2">
                            <a href="{{ route('municipio.requalificacao.outros.editar', [$municipio, $proj->id]) }}" wire:navigate><flux:button size="sm" variant="ghost" icon="pencil" /></a>
                            @if($podeEliminar)
                                <flux:button x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmacao', message: 'Enviar este projecto para a lixeira?', action: 'eliminar', component: $wire.$id, params: [{{ $proj->id }}], variant: 'danger' })" size="sm" variant="ghost" icon="trash" class="text-red-500" />
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700 xl:col-span-2">
                <flux:icon.clipboard-document-list class="w-10 h-10 text-zinc-300 dark:text-zinc-600 mx-auto mb-3" />
                <flux:text class="text-zinc-500">Nenhum projecto encontrado.</flux:text>
                @if($podeEditar)
                    <a href="{{ route('municipio.requalificacao.outros.criar', $municipio) }}" wire:navigate><flux:button variant="subtle" size="sm" class="mt-3">Criar o primeiro projecto</flux:button></a>
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $projectos->links() }}</div>
</div>

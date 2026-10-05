<div class="space-y-6">
    {{-- Cabeçalho e Navegação --}}
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.requalificacao', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Requalificação</a>
            <span>/</span>
            <span class="font-medium text-zinc-800 dark:text-zinc-200">Outros Projectos</span>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 dark:bg-cyan-950/50 dark:text-cyan-400">
                        <flux:icon.clipboard-document-list class="h-6 w-6" />
                    </div>
                    <div>
                        <flux:heading size="xl" class="font-bold text-zinc-900 dark:text-zinc-100">Outros Projectos</flux:heading>
                        <flux:text class="mt-0.5 text-zinc-600 dark:text-zinc-400">
                            Projectos municipais de requalificação, infraestruturas e intervenções comunitárias — {{ $municipio->nome }}
                        </flux:text>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('municipio.requalificacao', $municipio) }}" wire:navigate>
                    <flux:button variant="ghost" icon="arrow-left" size="sm">Voltar ao Hub</flux:button>
                </a>
                @if($podeCriar)
                    <a href="{{ route('municipio.requalificacao.outros.criar', $municipio) }}" wire:navigate>
                        <flux:button variant="primary" icon="plus" size="sm">Novo Projecto</flux:button>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Feedback de Mensagem / Status --}}
    @if(session('status'))
        <div class="flex items-center gap-2 rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/50">
            <flux:icon.check-circle class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    {{-- Indicadores KPI --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Projectos</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $kpis['total'] }}</span>
                <span class="text-xs text-zinc-500">registados</span>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Requalificação</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-teal-600 dark:text-teal-400">{{ $kpis['requalificacao'] }}</span>
                <span class="text-xs text-zinc-500">iniciativas</span>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Infraestrutura</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">{{ $kpis['infraestrutura'] }}</span>
                <span class="text-xs text-zinc-500">obras públicas</span>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Em Execução</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $kpis['em_curso'] }}</span>
                <span class="text-xs text-zinc-500">de {{ $kpis['total'] }} totais</span>
            </div>
        </div>
    </div>

    {{-- Barra de Filtros e Pesquisa --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-1 flex-wrap items-center gap-3">
                <div class="w-full sm:w-72">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar por título, entidade, fase..." icon="magnifying-glass" clearable />
                </div>

                <div class="w-full sm:w-48">
                    <flux:select wire:model.live="filtroTipo">
                        <flux:select.option value="">Todos os tipos</flux:select.option>
                        <flux:select.option value="requalificacao">Requalificação</flux:select.option>
                        <flux:select.option value="infraestrutura">Infraestrutura</flux:select.option>
                        <flux:select.option value="social">Social</flux:select.option>
                        <flux:select.option value="outro">Outro</flux:select.option>
                    </flux:select>
                </div>

                <div class="w-full sm:w-44">
                    <flux:select wire:model.live="filtroEstado">
                        <flux:select.option value="">Todos os estados</flux:select.option>
                        <flux:select.option value="planificado">Planificado</flux:select.option>
                        <flux:select.option value="em_curso">Em Curso</flux:select.option>
                        <flux:select.option value="concluido">Concluído</flux:select.option>
                        <flux:select.option value="suspenso">Suspenso</flux:select.option>
                    </flux:select>
                </div>

                @if($search || $filtroTipo || $filtroEstado)
                    <flux:button wire:click="limparFiltros" variant="ghost" size="sm" icon="x-mark">
                        Limpar Filtros
                    </flux:button>
                @endif
            </div>

            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                A exibir <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $projectos->total() }}</span> {{ $projectos->total() === 1 ? 'projecto' : 'projectos' }}
            </div>
        </div>
    </div>

    {{-- Grid de Projectos --}}
    <div class="grid gap-4 xl:grid-cols-2">
        @forelse($projectos as $proj)
            <div class="flex flex-col justify-between rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                <div class="space-y-3">
                    {{-- Badges e Título --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                    {{ $proj->titulo }}
                                </h3>

                                {{-- Badge Tipo --}}
                                @php
                                    $tipoLabel = match($proj->tipo) {
                                        'requalificacao' => 'Requalificação',
                                        'infraestrutura' => 'Infraestrutura',
                                        'social' => 'Social',
                                        default => 'Outro',
                                    };
                                    $tipoClasses = match($proj->tipo) {
                                        'requalificacao' => 'bg-teal-50 text-teal-800 border-teal-200 dark:bg-teal-950/50 dark:text-teal-300 dark:border-teal-800',
                                        'infraestrutura' => 'bg-cyan-50 text-cyan-800 border-cyan-200 dark:bg-cyan-950/50 dark:text-cyan-300 dark:border-cyan-800',
                                        'social' => 'bg-indigo-50 text-indigo-800 border-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800',
                                        default => 'bg-zinc-100 text-zinc-800 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border {{ $tipoClasses }}">
                                    {{ $tipoLabel }}
                                </span>

                                {{-- Badge Estado com acentuação --}}
                                @php
                                    $estadoClasses = match($proj->estado) {
                                        'planificado' => 'bg-zinc-100 text-zinc-800 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700',
                                        'em_curso' => 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800',
                                        'concluido' => 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800',
                                        'suspenso' => 'bg-red-50 text-red-800 border-red-200 dark:bg-red-950/50 dark:text-red-300 dark:border-red-800',
                                        default => 'bg-zinc-100 text-zinc-800 border-zinc-200',
                                    };
                                    $estadoLabel = match($proj->estado) {
                                        'planificado' => 'Planificado',
                                        'em_curso' => 'Em Curso',
                                        'concluido' => 'Concluído',
                                        'suspenso' => 'Suspenso',
                                        default => ucfirst($proj->estado),
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium border {{ $estadoClasses }}">
                                    {{ $estadoLabel }}
                                </span>
                            </div>

                            {{-- Metadados --}}
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                                @if($proj->entidade_responsavel)
                                    <span class="flex items-center gap-1">
                                        <flux:icon.building-office-2 class="h-3.5 w-3.5 text-zinc-400" />
                                        {{ $proj->entidade_responsavel }}
                                    </span>
                                @endif
                                @if($proj->fase)
                                    <span class="flex items-center gap-1">
                                        <flux:icon.flag class="h-3.5 w-3.5 text-zinc-400" />
                                        Fase: {{ $proj->fase }}
                                    </span>
                                @endif
                                <span class="flex items-center gap-1">
                                    <flux:icon.calendar class="h-3.5 w-3.5 text-zinc-400" />
                                    {{ $proj->created_at->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>

                        {{-- Acções --}}
                        @if($podeEditar || $podeEliminar)
                            <div class="flex items-center gap-1 shrink-0">
                                @if($podeEditar)
                                    <a href="{{ route('municipio.requalificacao.outros.editar', [$municipio, $proj->id]) }}" wire:navigate>
                                        <flux:button size="sm" variant="ghost" icon="pencil" />
                                    </a>
                                @endif
                                @if($podeEliminar)
                                    <flux:button x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmar Eliminação', message: 'Tem a certeza que deseja enviar este projecto para a lixeira?', action: 'eliminar', component: $wire.$id, params: [{{ $proj->id }}], variant: 'danger' })"
                                                 size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-700" />
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Descrição --}}
                    @if($proj->descricao)
                        <p class="text-sm text-zinc-600 dark:text-zinc-300 leading-relaxed line-clamp-3">
                            {{ $proj->descricao }}
                        </p>
                    @endif

                    {{-- Prazos e Orçamento --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 rounded-xl bg-zinc-50 p-3 text-xs dark:bg-zinc-800/50">
                        <div>
                            <span class="text-zinc-400 block text-[10px] uppercase font-semibold">Data de Início</span>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $proj->data_inicio ? $proj->data_inicio->format('d/m/Y') : 'Não definida' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block text-[10px] uppercase font-semibold">Previsão Conclusão</span>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $proj->data_previsao_fim ? $proj->data_previsao_fim->format('d/m/Y') : 'Não definida' }}
                            </span>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <span class="text-zinc-400 block text-[10px] uppercase font-semibold">Orçamento Estimado</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                @if($proj->orcamento > 0)
                                    {{ number_format($proj->orcamento, 2, ',', '.') }} <span class="text-[10px] text-zinc-400">AOA</span>
                                @else
                                    <span class="text-zinc-400 font-normal">Não informado</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Observações --}}
                    @if($proj->observacoes)
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 italic">
                            <span class="font-semibold not-italic">Obs:</span> {{ Str::limit($proj->observacoes, 120) }}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700 xl:col-span-2">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.clipboard-document-list class="h-6 w-6 text-zinc-400" />
                </div>
                <h3 class="mt-3 text-sm font-semibold text-zinc-900 dark:text-white">Nenhum projecto encontrado</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    @if($search || $filtroTipo || $filtroEstado)
                        Tente ajustar os filtros de pesquisa para encontrar os registos desejados.
                    @else
                        Ainda não existem outros projectos registados para o município de {{ $municipio->nome }}.
                    @endif
                </p>
                @if($podeCriar)
                    <div class="mt-4">
                        <a href="{{ route('municipio.requalificacao.outros.criar', $municipio) }}" wire:navigate>
                            <flux:button variant="primary" size="sm" icon="plus">Criar Primeiro Projecto</flux:button>
                        </a>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    @if($projectos->hasPages())
        <div class="mt-6">
            {{ $projectos->links() }}
        </div>
    @endif
</div>

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
            <span class="font-medium text-zinc-800 dark:text-zinc-200">Projectos SONA</span>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 text-teal-600 dark:bg-teal-950/50 dark:text-teal-400">
                        <flux:icon.building-office class="h-6 w-6" />
                    </div>
                    <div>
                        <flux:heading size="xl" class="font-bold text-zinc-900 dark:text-zinc-100">Projectos SONA</flux:heading>
                        <flux:text class="mt-0.5 text-zinc-600 dark:text-zinc-400">
                            Planos de requalificação urbana e acompanhamento de avanços de obras — {{ $municipio->nome }}
                        </flux:text>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('municipio.requalificacao', $municipio) }}" wire:navigate>
                    <flux:button variant="ghost" icon="arrow-left" size="sm">Voltar ao Hub</flux:button>
                </a>
                @if($podeCriar)
                    <a href="{{ route('municipio.requalificacao.sona.criar', $municipio) }}" wire:navigate>
                        <flux:button variant="primary" icon="plus" size="sm">Novo Projecto SONA</flux:button>
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
                <span class="text-xs text-zinc-500">planos urbanos</span>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Avanços de Obras</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">{{ $kpis['avancos'] }}</span>
                <span class="text-xs text-zinc-500">relatórios / fotos</span>
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
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar por título, descrição..." icon="magnifying-glass" clearable />
                </div>

                <div class="w-full sm:w-48">
                    <flux:select wire:model.live="filtroTipo">
                        <flux:select.option value="">Todos os tipos</flux:select.option>
                        <flux:select.option value="requalificacao">Requalificação Urbana</flux:select.option>
                        <flux:select.option value="avancos_obras">Avanços de Obras</flux:select.option>
                    </flux:select>
                </div>

                <div class="w-full sm:w-44">
                    <flux:select wire:model.live="filtroEstado">
                        <flux:select.option value="">Todos os estados</flux:select.option>
                        <flux:select.option value="em_curso">Em Curso</flux:select.option>
                        <flux:select.option value="concluido">Concluído</flux:select.option>
                        <flux:select.option value="suspenso">Suspenso</flux:select.option>
                    </flux:select>
                </div>

                @if($fasesDisponiveis->isNotEmpty())
                    <div class="w-full sm:w-40">
                        <flux:select wire:model.live="filtroFase">
                            <flux:select.option value="">Todas as fases</flux:select.option>
                            @foreach($fasesDisponiveis as $faseItem)
                                <flux:select.option value="{{ $faseItem }}">{{ $faseItem }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif

                @if($search || $filtroTipo || $filtroEstado || $filtroFase)
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

    {{-- Lista de Projectos SONA --}}
    <div class="space-y-4">
        @forelse($projectos as $proj)
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex-1 space-y-3">
                        {{-- Badges e Título --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $proj->titulo }}
                            </h3>

                            {{-- Badge Tipo --}}
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold
                                {{ $proj->tipo === 'requalificacao' ? 'bg-teal-50 text-teal-800 dark:bg-teal-950/50 dark:text-teal-300 border border-teal-200 dark:border-teal-800' : 'bg-cyan-50 text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800' }}">
                                @if($proj->tipo === 'requalificacao')
                                    <flux:icon.building-office class="h-3 w-3" /> Requalificação Urbana
                                @else
                                    <flux:icon.camera class="h-3 w-3" /> Avanços de Obras
                                @endif
                            </span>

                            {{-- Badge Estado com acentuação --}}
                            @php
                                $estadoClasses = match($proj->estado) {
                                    'em_curso' => 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800',
                                    'concluido' => 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800',
                                    'suspenso' => 'bg-red-50 text-red-800 border-red-200 dark:bg-red-950/50 dark:text-red-300 dark:border-red-800',
                                    default => 'bg-zinc-100 text-zinc-800 border-zinc-200',
                                };
                                $estadoLabel = match($proj->estado) {
                                    'em_curso' => 'Em Curso',
                                    'concluido' => 'Concluído',
                                    'suspenso' => 'Suspenso',
                                    default => ucfirst($proj->estado),
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium border {{ $estadoClasses }}">
                                {{ $estadoLabel }}
                            </span>

                            {{-- Badge Fase --}}
                            @if($proj->fase)
                                <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $proj->fase }}
                                </span>
                            @endif
                        </div>

                        {{-- Metadados técnicos --}}
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                            <span class="flex items-center gap-1">
                                <flux:icon.map-pin class="h-3.5 w-3.5 text-zinc-400" />
                                {{ $proj->municipio?->nome ?? $municipio->nome }}
                            </span>
                            <span class="flex items-center gap-1">
                                <flux:icon.calendar class="h-3.5 w-3.5 text-zinc-400" />
                                Registado a {{ $proj->created_at->format('d/m/Y') }}
                            </span>
                            @if($proj->criadoPor)
                                <span class="flex items-center gap-1">
                                    <flux:icon.user class="h-3.5 w-3.5 text-zinc-400" />
                                    {{ $proj->criadoPor->name }}
                                </span>
                            @endif
                        </div>

                        {{-- Descrição --}}
                        @if($proj->descricao)
                            <p class="text-sm text-zinc-600 dark:text-zinc-300 leading-relaxed">
                                {{ $proj->descricao }}
                            </p>
                        @endif

                        {{-- Ficheiros / Galeria de Fotos ou Documentos Anexos --}}
                        @if($proj->ficheiros->isNotEmpty())
                            <div class="mt-3 rounded-xl border border-zinc-100 bg-zinc-50/70 p-3 dark:border-zinc-800 dark:bg-zinc-800/40">
                                <div class="mb-2 flex items-center justify-between text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <span class="flex items-center gap-1">
                                        <flux:icon.paper-clip class="h-3.5 w-3.5" />
                                        Anexos e Documentos ({{ $proj->ficheiros->count() }})
                                    </span>
                                </div>

                                @if($proj->tipo === 'avancos_obras')
                                    {{-- Galeria de Imagens com Legenda e Fase --}}
                                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 md:grid-cols-6">
                                        @foreach($proj->ficheiros as $f)
                                            @if(Str::startsWith($f->mime_type, 'image/'))
                                                <div class="group relative overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800">
                                                    <a href="{{ route('storage.serve', $f->caminho) }}" target="_blank" class="block aspect-square overflow-hidden">
                                                        <img src="{{ route('storage.serve', $f->caminho) }}" alt="{{ $f->legenda ?? $f->nome_original }}"
                                                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" />
                                                    </a>
                                                    @if($f->fase)
                                                        <span class="absolute top-1 left-1 rounded bg-black/70 px-1.5 py-0.5 text-[10px] font-medium text-white backdrop-blur-sm">
                                                            {{ $f->fase }}
                                                        </span>
                                                    @endif
                                                    @if($f->legenda)
                                                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-1.5 text-[11px] text-white">
                                                            <p class="truncate">{{ $f->legenda }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <a href="{{ route('storage.serve', $f->caminho) }}" target="_blank"
                                                   class="flex flex-col items-center justify-center rounded-lg border border-zinc-200 bg-white p-2 text-center text-xs text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                                                    <flux:icon.document-text class="h-6 w-6 text-teal-600 mb-1" />
                                                    <span class="truncate w-full font-medium">{{ $f->nome_original }}</span>
                                                    <span class="text-[10px] text-zinc-400 uppercase">{{ $f->tipo_ficheiro }}</span>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    {{-- Lista de Ficheiros Requalificação --}}
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($proj->ficheiros as $f)
                                            <a href="{{ route('storage.serve', $f->caminho) }}" target="_blank"
                                               class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                                @if(Str::startsWith($f->mime_type, 'image/'))
                                                    <flux:icon.photo class="h-4 w-4 text-cyan-600" />
                                                @else
                                                    <flux:icon.document-text class="h-4 w-4 text-teal-600" />
                                                @endif
                                                <span class="truncate max-w-xs">{{ $f->nome_original }}</span>
                                                <span class="text-[10px] text-zinc-400">({{ strtoupper($f->tipo_ficheiro) }})</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Acções --}}
                    @if($podeEditar || $podeEliminar)
                        <div class="flex items-center gap-2 self-end lg:self-start shrink-0">
                            @if($podeEditar)
                                <a href="{{ route('municipio.requalificacao.sona.editar', [$municipio, $proj->id]) }}" wire:navigate>
                                    <flux:button size="sm" variant="ghost" icon="pencil">Editar</flux:button>
                                </a>
                            @endif
                            @if($podeEliminar)
                                <flux:button x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmar Eliminação', message: 'Tem a certeza que deseja enviar este projecto para a lixeira?', action: 'eliminar', component: $wire.$id, params: [{{ $proj->id }}], variant: 'danger' })"
                                             size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-700">
                                    Eliminar
                                </flux:button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.building-office class="h-6 w-6 text-zinc-400" />
                </div>
                <h3 class="mt-3 text-sm font-semibold text-zinc-900 dark:text-white">Nenhum projecto SONA encontrado</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    @if($search || $filtroTipo || $filtroEstado || $filtroFase)
                        Tente ajustar os filtros de pesquisa para encontrar os registos desejados.
                    @else
                        Ainda não existem projectos SONA registados para o município de {{ $municipio->nome }}.
                    @endif
                </p>
                @if($podeCriar)
                    <div class="mt-4">
                        <a href="{{ route('municipio.requalificacao.sona.criar', $municipio) }}" wire:navigate>
                            <flux:button variant="primary" size="sm" icon="plus">Criar Novo Projecto</flux:button>
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

<div class="space-y-6">
    {{-- Navegação e Cabeçalho --}}
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ $categoriaRoute }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $categoriaNome }}</a>
            <span>/</span>
            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $tipo->nome }}</span>
        </div>

        <div class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400">
                    <flux:icon name="map" class="h-7 w-7" />
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:heading size="xl">{{ $tipo->nome }}</flux:heading>
                        <span class="rounded-full bg-red-100 px-3 py-0.5 text-xs font-semibold text-red-800 dark:bg-red-900/30 dark:text-red-300">
                            {{ $municipio->nome }}
                        </span>
                        @if($tipoSlug === 'pmot')
                            <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                Instrumento Principal
                            </span>
                        @endif
                    </div>
                    <flux:text class="mt-1">
                        Gestão integrada do {{ $tipo->nome }} &bull; Província da Huíla &bull; Com leitura OCR e preenchimento automático
                    </flux:text>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ $categoriaRoute }}" wire:navigate>
                    <flux:button variant="ghost" icon="arrow-left">Voltar</flux:button>
                </a>
                @if($podeEditar)
                    <a href="{{ route('municipio.instrumentos.tipo.criar', [$municipio, $tipoSlug]) }}" wire:navigate>
                        <flux:button variant="primary" icon="plus" class="shadow-sm">Novo {{ $tipo->slug === 'pmot' ? 'PMOT' : 'Registo' }}</flux:button>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Cards de Métricas e Estatísticas Rápidas --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total de Registos</span>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <flux:icon name="folder" class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $stats['total'] }}</span>
                <span class="text-xs text-zinc-500">{{ $stats['comFicheiros'] }} com anexos</span>
            </div>
        </div>

        {{-- Aprovados --}}
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-5 shadow-xs dark:border-emerald-800/40 dark:bg-emerald-950/20">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Aprovados / Em Vigor</span>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300">
                    <flux:icon name="check-badge" class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $stats['aprovados'] }}</span>
                <span class="text-xs text-emerald-600 dark:text-emerald-400">Homologados</span>
            </div>
        </div>

        {{-- Submetidos --}}
        <div class="rounded-2xl border border-sky-200 bg-sky-50/40 p-5 shadow-xs dark:border-sky-800/40 dark:bg-sky-950/20">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-sky-800 dark:text-sky-300">Em Apreciação</span>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-900/60 dark:text-sky-300">
                    <flux:icon name="clock" class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-sky-700 dark:text-sky-300">{{ $stats['submetidos'] }}</span>
                <span class="text-xs text-sky-600 dark:text-sky-400">Submetidos</span>
            </div>
        </div>

        {{-- Rascunhos --}}
        <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-5 shadow-xs dark:border-amber-800/40 dark:bg-amber-950/20">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-800 dark:text-amber-300">Em Elaboração</span>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300">
                    <flux:icon name="pencil-square" class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $stats['rascunhos'] }}</span>
                <span class="text-xs text-amber-600 dark:text-amber-400">Rascunhos</span>
            </div>
        </div>
    </div>

    {{-- Barra de Filtros e Pesquisa --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_12rem_10rem_12rem_auto]">
            {{-- Pesquisa --}}
            <div>
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar por título, ano ou descrição..." icon="magnifying-glass" />
            </div>

            {{-- Filtro de Estado --}}
            <div>
                <flux:select wire:model.live="filtroEstado">
                    <flux:select.option value="todos">Todos os Estados</flux:select.option>
                    <flux:select.option value="aprovado">Aprovado</flux:select.option>
                    <flux:select.option value="submetido">Submetido</flux:select.option>
                    <flux:select.option value="rascunho">Rascunho</flux:select.option>
                    <flux:select.option value="rejeitado">Rejeitado</flux:select.option>
                </flux:select>
            </div>

            {{-- Filtro de Ano --}}
            <div>
                <flux:select wire:model.live="filtroAno">
                    <flux:select.option value="todos">Todos os Anos</flux:select.option>
                    @foreach($anosDisponiveis as $anoItem)
                        <flux:select.option value="{{ $anoItem }}">{{ $anoItem }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Ordenação --}}
            <div>
                <flux:select wire:model.live="ordenacao">
                    <flux:select.option value="recente">Mais Recentes</flux:select.option>
                    <flux:select.option value="antigo">Mais Antigos</flux:select.option>
                    <flux:select.option value="ano_desc">Ano (Decrescente)</flux:select.option>
                    <flux:select.option value="ano_asc">Ano (Crescente)</flux:select.option>
                    <flux:select.option value="titulo_asc">Título (A-Z)</flux:select.option>
                </flux:select>
            </div>

            {{-- Limpar Filtros --}}
            <div class="flex items-center">
                @if($search !== '' || $filtroEstado !== 'todos' || $filtroAno !== 'todos' || $ordenacao !== 'recente')
                    <flux:button size="sm" variant="ghost" wire:click="limparFiltros" icon="x-mark">
                        Limpar
                    </flux:button>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabela de Registos --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50/80 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:bg-zinc-950/60 dark:text-zinc-400">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Instrumento de Ordenamento</th>
                        <th class="px-4 py-3.5 text-center">Ano</th>
                        <th class="px-4 py-3.5 text-left">Estado</th>
                        <th class="px-4 py-3.5 text-left">Peças Técnicas & Ficheiros</th>
                        <th class="px-4 py-3.5 text-left">Responsável</th>
                        <th class="px-4 py-3.5 text-left">Data de Registo</th>
                        <th class="px-5 py-3.5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 bg-white text-sm dark:divide-zinc-800 dark:bg-zinc-900">
                    @forelse($instrumentos as $inst)
                        <tr class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/50">
                            {{-- Título e Descrição --}}
                            <td class="px-5 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-950/60 dark:text-red-400">
                                        <flux:icon name="document-text" class="h-5 w-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('instrumento.detalhe', $inst) }}" wire:navigate class="font-bold text-zinc-900 hover:text-red-600 dark:text-zinc-100 dark:hover:text-red-400">
                                            {{ $inst->titulo }}
                                        </a>
                                        @if($inst->descricao)
                                            <p class="mt-0.5 text-xs text-zinc-500 line-clamp-1 dark:text-zinc-400">
                                                {{ $inst->descricao }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Ano --}}
                            <td class="px-4 py-4 text-center">
                                @if($inst->ano)
                                    <span class="inline-flex items-center rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-bold text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                        {{ $inst->ano }}
                                    </span>
                                @else
                                    <span class="text-xs text-zinc-400">-</span>
                                @endif
                            </td>

                            {{-- Estado --}}
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ $inst->estado === 'aprovado'  ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                                    {{ $inst->estado === 'submetido' ? 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-400' : '' }}
                                    {{ $inst->estado === 'rascunho'  ? 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' : '' }}
                                    {{ $inst->estado === 'rejeitado' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                                ">
                                    <span class="h-1.5 w-1.5 rounded-full
                                        {{ $inst->estado === 'aprovado'  ? 'bg-emerald-500' : '' }}
                                        {{ $inst->estado === 'submetido' ? 'bg-sky-500' : '' }}
                                        {{ $inst->estado === 'rascunho'  ? 'bg-zinc-400' : '' }}
                                        {{ $inst->estado === 'rejeitado' ? 'bg-red-500' : '' }}
                                    "></span>
                                    {{ ucfirst($inst->estado) }}
                                </span>
                            </td>

                            {{-- Ficheiros --}}
                            <td class="px-4 py-4">
                                @if($inst->ficheiros->count())
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($inst->ficheiros as $f)
                                            <a href="{{ route('storage.serve', $f->caminho) }}" target="_blank"
                                                class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2 py-1 text-xs font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                                                title="{{ $f->nome_original }} ({{ strtoupper($f->tipo_ficheiro) }})">
                                                <flux:icon name="arrow-down-tray" class="h-3 w-3 text-zinc-400" />
                                                <span class="max-w-[120px] truncate">{{ $f->nome_original }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400 italic">Sem ficheiros</span>
                                @endif
                            </td>

                            {{-- Criado por --}}
                            <td class="px-4 py-4 text-xs text-zinc-600 dark:text-zinc-300">
                                {{ $inst->criadoPor?->name ?? 'Sistema' }}
                            </td>

                            {{-- Data --}}
                            <td class="px-4 py-4 text-xs text-zinc-500">
                                {{ $inst->created_at?->format('d/m/Y') }}
                            </td>

                            {{-- Ações --}}
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('instrumento.detalhe', $inst) }}" wire:navigate title="Ver Detalhes">
                                        <flux:button size="sm" variant="ghost" icon="eye" />
                                    </a>

                                    @if($podeEditar)
                                        <a href="{{ route('municipio.instrumentos.tipo.editar', [$municipio, $tipoSlug, $inst->id]) }}" wire:navigate title="Editar">
                                            <flux:button size="sm" variant="ghost" icon="pencil" />
                                        </a>
                                    @endif

                                    @if($podeEliminar)
                                        <flux:button size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-700"
                                            x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmar Exclusão', message: 'Deseja mover este instrumento para a lixeira do sistema?', action: 'eliminar', component: $wire.$id, params: [{{ $inst->id }}], variant: 'danger' })"
                                            title="Enviar para Lixeira" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center gap-3">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">
                                        <flux:icon name="document-magnifying-glass" class="h-7 w-7" />
                                    </div>
                                    <div>
                                        <p class="font-bold text-zinc-800 dark:text-zinc-200">Nenhum registo encontrado</p>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            Não foram localizados registos de {{ $tipo->nome }} com os filtros selecionados para o município de {{ $municipio->nome }}.
                                        </p>
                                    </div>
                                    @if($podeEditar)
                                        <div class="mt-2">
                                            <a href="{{ route('municipio.instrumentos.tipo.criar', [$municipio, $tipoSlug]) }}" wire:navigate>
                                                <flux:button size="sm" variant="primary" icon="plus">
                                                    Criar Primeiro {{ $tipo->slug === 'pmot' ? 'PMOT' : 'Registo' }}
                                                </flux:button>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if($instrumentos->hasPages())
            <div class="border-t border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-950/40">
                {{ $instrumentos->links() }}
            </div>
        @endif
    </div>
</div>

<div class="space-y-6">
    {{-- Navegação e Cabeçalho --}}
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <span class="font-medium text-zinc-800 dark:text-zinc-200">Cartas Municipais</span>
        </div>

        <div class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                    <flux:icon name="map" class="h-7 w-7" />
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:heading size="xl">Cartas Municipais</flux:heading>
                        <span class="rounded-full bg-amber-100 px-3 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                            {{ $municipio->nome }}
                        </span>
                        <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            Acervo Cartográfico Oficial
                        </span>
                    </div>
                    <flux:text class="mt-1">
                        Cartas base, topográficas, temáticas, cartas de uso do solo e ficheiros SIG/CAD &bull; Província da Huíla
                    </flux:text>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('municipio.show', $municipio) }}" wire:navigate>
                    <flux:button variant="ghost" icon="arrow-left">Voltar</flux:button>
                </a>
                @if($podeEditar)
                    <a href="{{ route('municipio.cartas.criar', $municipio) }}" wire:navigate>
                        <flux:button variant="primary" icon="plus" class="shadow-sm">Nova Carta (OCR)</flux:button>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Cards de Métricas (KPIs) --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total de Cartas</span>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <flux:icon name="folder" class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $stats['total'] }}</span>
                <span class="text-xs text-zinc-500">Cartas cadastradas</span>
            </div>
        </div>

        {{-- Aprovadas --}}
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-5 shadow-xs dark:border-emerald-800/40 dark:bg-emerald-950/20">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Cartas Homologadas</span>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300">
                    <flux:icon name="check-badge" class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $stats['aprovadas'] }}</span>
                <span class="text-xs text-emerald-600 dark:text-emerald-400">Oficiais em vigor</span>
            </div>
        </div>

        {{-- Submetidas --}}
        <div class="rounded-2xl border border-sky-200 bg-sky-50/40 p-5 shadow-xs dark:border-sky-800/40 dark:bg-sky-950/20">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-sky-800 dark:text-sky-300">Em Apreciação</span>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-900/60 dark:text-sky-300">
                    <flux:icon name="clock" class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-sky-700 dark:text-sky-300">{{ $stats['submetidas'] }}</span>
                <span class="text-xs text-sky-600 dark:text-sky-400">Em validação técnica</span>
            </div>
        </div>

        {{-- Ficheiros Digitais / SIG --}}
        <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-5 shadow-xs dark:border-amber-800/40 dark:bg-amber-950/20">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-800 dark:text-amber-300">Cartografia Digital</span>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300">
                    <flux:icon name="arrow-down-tray" class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $stats['comFicheiros'] }}</span>
                <span class="text-xs text-amber-600 dark:text-amber-400">Com ficheiros digitais</span>
            </div>
        </div>
    </div>

    {{-- Modal / Painel de Detalhes da Carta Selecionada --}}
    @if($detalhe)
        <div class="rounded-2xl border-2 border-amber-300 bg-white shadow-lg dark:border-amber-700/60 dark:bg-zinc-900 overflow-hidden">
            <div class="border-b border-zinc-200 bg-amber-50/50 p-6 dark:border-zinc-800 dark:bg-amber-950/20">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:heading size="lg">{{ $detalhe->titulo }}</flux:heading>
                            @if($detalhe->ano)
                                <span class="rounded-lg bg-zinc-100 px-2.5 py-0.5 text-xs font-bold text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                    Ano: {{ $detalhe->ano }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold
                                {{ $detalhe->estado === 'aprovado'  ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                                {{ $detalhe->estado === 'submetido' ? 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-400' : '' }}
                                {{ $detalhe->estado === 'rascunho'  ? 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' : '' }}
                                {{ $detalhe->estado === 'rejeitado' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                            ">
                                {{ ucfirst($detalhe->estado) }}
                            </span>
                        </div>
                        <flux:text class="mt-1 text-xs text-zinc-500">
                            Registo #{{ $detalhe->id }} &bull; Adicionado por {{ $detalhe->criadoPor?->name ?? 'Sistema' }} em {{ $detalhe->created_at->format('d/m/Y H:i') }}
                        </flux:text>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($podeEditar)
                            <a href="{{ route('municipio.cartas.editar', [$municipio, $detalhe->id]) }}" wire:navigate>
                                <flux:button size="sm" variant="primary" icon="pencil">Editar</flux:button>
                            </a>
                        @endif
                        <a href="{{ route('instrumento.detalhe', $detalhe) }}" wire:navigate>
                            <flux:button size="sm" variant="ghost" icon="eye">Ficha Completa</flux:button>
                        </a>
                        <flux:button wire:click="fecharDetalhe" size="sm" variant="ghost" icon="x-mark">Fechar</flux:button>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                @if($detalhe->descricao)
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-400">Descrição / Legenda Cartográfica</span>
                        <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed whitespace-pre-line">{{ $detalhe->descricao }}</p>
                    </div>
                @endif

                {{-- Pré-Visualização de PDF Embutido --}}
                @if($ficheiroAtivoCaminho && str_ends_with(strtolower($ficheiroAtivoCaminho), '.pdf'))
                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden bg-zinc-100 dark:bg-zinc-950">
                        <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50 px-4 py-2 text-xs font-semibold text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                            <span class="flex items-center gap-1.5">
                                <flux:icon name="document-text" class="h-4 w-4 text-amber-600" />
                                Visualização da Peça Cartográfica
                            </span>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('storage.serve', $ficheiroAtivoCaminho) }}" target="_blank" class="hover:underline text-indigo-600 dark:text-indigo-400">
                                    Abrir em Nova Aba
                                </a>
                                <a href="{{ route('storage.download', $ficheiroAtivoCaminho) }}" class="rounded bg-zinc-900 px-2 py-0.5 text-white dark:bg-zinc-100 dark:text-zinc-900">
                                    Descarregar
                                </a>
                            </div>
                        </div>
                        <iframe src="{{ route('storage.serve', $ficheiroAtivoCaminho) }}" class="w-full h-[520px]" title="Visualização da Carta Municipal"></iframe>
                    </div>
                @endif

                {{-- Ficheiros da Carta --}}
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-400 block mb-2">
                        Ficheiros e Peças Técnicas Anexas ({{ $detalhe->ficheiros->count() }})
                    </span>

                    @if($detalhe->ficheiros->count())
                        <div class="divide-y divide-zinc-100 rounded-xl border border-zinc-200 bg-zinc-50/60 dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-950/40">
                            @foreach($detalhe->ficheiros as $f)
                                @php
                                    $isPdf = strtolower($f->tipo_ficheiro) === 'pdf';
                                    $isAtivo = $ficheiroAtivoCaminho === $f->caminho;
                                @endphp
                                <div class="flex flex-col gap-2 p-3 sm:flex-row sm:items-center sm:justify-between text-xs">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                            <flux:icon name="paper-clip" class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ $f->nome_original }}</p>
                                            <p class="text-[11px] text-zinc-400">
                                                {{ strtoupper($f->tipo_ficheiro) }} &bull; {{ number_format($f->tamanho / 1024, 1) }} KB &bull; {{ $f->created_at?->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        @if($isPdf)
                                            <button type="button" wire:click="selecionarFicheiroAtivo('{{ $f->caminho }}')"
                                                class="rounded-md px-2 py-1 font-semibold text-zinc-600 hover:bg-zinc-200 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                {{ $isAtivo ? 'A visualisar' : 'Visualisar' }}
                                            </button>
                                        @endif
                                        <a href="{{ route('storage.serve', $f->caminho) }}" target="_blank"
                                            class="rounded-md px-2 py-1 font-semibold text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/50">
                                            Abrir
                                        </a>
                                        <a href="{{ route('storage.download', $f->caminho) }}"
                                            class="rounded-md bg-zinc-900 px-2.5 py-1 font-semibold text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900">
                                            Baixar
                                        </a>
                                        @if($podeEliminar)
                                            <button type="button" wire:click="eliminarFicheiro({{ $f->id }})"
                                                onclick="return confirm('Deseja eliminar este anexo?')"
                                                class="rounded-md p-1 text-zinc-400 hover:text-red-600">
                                                <flux:icon name="trash" class="h-4 w-4" />
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-zinc-400 italic">Nenhum ficheiro anexado a esta carta.</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Barra de Filtros, Pesquisa e Alternador de Visualização --}}
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

            {{-- Alternador de Visualização (Grade / Tabela) & Limpar --}}
            <div class="flex items-center gap-1.5">
                <button type="button" wire:click="setVisualizacao('grade')"
                    class="rounded-xl p-2 transition {{ $visualizacao === 'grade' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800' }}"
                    title="Visualização em Grade de Mapas">
                    <flux:icon name="squares-2x2" class="h-4 w-4" />
                </button>
                <button type="button" wire:click="setVisualizacao('tabela')"
                    class="rounded-xl p-2 transition {{ $visualizacao === 'tabela' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800' }}"
                    title="Visualização em Tabela">
                    <flux:icon name="bars-3" class="h-4 w-4" />
                </button>

                @if($search !== '' || $filtroEstado !== 'todos' || $filtroAno !== 'todos' || $ordenacao !== 'recente')
                    <flux:button size="sm" variant="ghost" wire:click="limparFiltros" icon="x-mark">
                        Limpar
                    </flux:button>
                @endif
            </div>
        </div>
    </div>

    {{-- Visualização em Grade (Grid View de Cartas) --}}
    @if($visualizacao === 'grade')
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($cartas as $carta)
                <div class="group flex flex-col justify-between rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                    <div>
                        {{-- Topo do Card com Ano e Estado --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                                <flux:icon name="map" class="h-6 w-6" />
                            </div>
                            <div class="flex items-center gap-1.5">
                                @if($carta->ano)
                                    <span class="rounded-lg bg-zinc-100 px-2 py-0.5 text-xs font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $carta->ano }}
                                    </span>
                                @endif
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    {{ $carta->estado === 'aprovado'  ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                                    {{ $carta->estado === 'submetido' ? 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-400' : '' }}
                                    {{ $carta->estado === 'rascunho'  ? 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' : '' }}
                                    {{ $carta->estado === 'rejeitado' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                                ">
                                    {{ ucfirst($carta->estado) }}
                                </span>
                            </div>
                        </div>

                        {{-- Título e Descrição --}}
                        <h4 class="mt-4 font-bold text-zinc-900 group-hover:text-amber-600 dark:text-zinc-100 dark:group-hover:text-amber-400">
                            {{ $carta->titulo }}
                        </h4>

                        @if($carta->descricao)
                            <p class="mt-1 text-xs leading-relaxed text-zinc-500 dark:text-zinc-400 line-clamp-3">
                                {{ $carta->descricao }}
                            </p>
                        @endif

                        {{-- Ficheiros da Carta --}}
                        <div class="mt-3 flex flex-wrap gap-1">
                            @forelse($carta->ficheiros as $f)
                                <a href="{{ route('storage.serve', $f->caminho) }}" target="_blank"
                                    class="inline-flex items-center gap-1 rounded-md bg-zinc-100 px-2 py-0.5 text-[11px] font-medium text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300"
                                    title="{{ $f->nome_original }}">
                                    <flux:icon name="paper-clip" class="h-3 w-3 text-zinc-400" />
                                    {{ strtoupper($f->tipo_ficheiro) }}
                                </a>
                            @empty
                                <span class="text-[11px] text-zinc-400 italic">Sem ficheiros anexos</span>
                            @endforelse
                        </div>
                    </div>

                    {{-- Rodapé do Card --}}
                    <div class="mt-5 flex items-center justify-between border-t border-zinc-100 pt-3 dark:border-zinc-800 text-xs">
                        <span class="text-zinc-400 text-[11px]">
                            {{ $carta->created_at?->format('d/m/Y') }}
                        </span>

                        <div class="flex items-center gap-1">
                            <button type="button" wire:click="verDetalhe({{ $carta->id }})"
                                class="rounded-lg px-2 py-1 font-semibold text-amber-700 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-950/40">
                                Ver Ficha
                            </button>
                            @if($podeEditar)
                                <a href="{{ route('municipio.cartas.editar', [$municipio, $carta->id]) }}" wire:navigate
                                    class="rounded-lg p-1 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200" title="Editar">
                                    <flux:icon name="pencil" class="h-4 w-4" />
                                </a>
                            @endif
                            @if($podeEliminar)
                                <button type="button"
                                    x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmar Exclusão', message: 'Enviar esta carta para a lixeira?', action: 'eliminar', component: $wire.$id, params: [{{ $carta->id }}], variant: 'danger' })"
                                    class="rounded-lg p-1 text-zinc-400 hover:text-red-600 dark:hover:text-red-400" title="Eliminar">
                                    <flux:icon name="trash" class="h-4 w-4" />
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 rounded-2xl border border-dashed border-zinc-300 p-12 text-center text-sm text-zinc-500 dark:border-zinc-800">
                    <div class="mx-auto flex max-w-sm flex-col items-center gap-3">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">
                            <flux:icon name="map" class="h-7 w-7" />
                        </div>
                        <p class="font-bold text-zinc-800 dark:text-zinc-200">Nenhuma carta municipal encontrada</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            Não existem cartas registadas com os critérios selecionados para {{ $municipio->nome }}.
                        </p>
                        @if($podeEditar)
                            <div class="mt-2">
                                <a href="{{ route('municipio.cartas.criar', $municipio) }}" wire:navigate>
                                    <flux:button size="sm" variant="primary" icon="plus">Criar Primeira Carta Municipal</flux:button>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>
    @else
        {{-- Visualização em Tabela --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="bg-zinc-50/80 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:bg-zinc-950/60 dark:text-zinc-400">
                        <tr>
                            <th class="px-5 py-3.5 text-left">Carta Municipal</th>
                            <th class="px-4 py-3.5 text-center">Ano</th>
                            <th class="px-4 py-3.5 text-left">Estado</th>
                            <th class="px-4 py-3.5 text-left">Ficheiros / Cartografia</th>
                            <th class="px-4 py-3.5 text-left">Técnico</th>
                            <th class="px-4 py-3.5 text-left">Data</th>
                            <th class="px-5 py-3.5 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 bg-white text-sm dark:divide-zinc-800 dark:bg-zinc-900">
                        @forelse($cartas as $carta)
                            <tr class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/50">
                                <td class="px-5 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400">
                                            <flux:icon name="map" class="h-5 w-5" />
                                        </div>
                                        <div class="min-w-0">
                                            <button type="button" wire:click="verDetalhe({{ $carta->id }})"
                                                class="text-left font-bold text-zinc-900 hover:text-amber-600 dark:text-zinc-100 dark:hover:text-amber-400">
                                                {{ $carta->titulo }}
                                            </button>
                                            @if($carta->descricao)
                                                <p class="mt-0.5 text-xs text-zinc-500 line-clamp-1 dark:text-zinc-400">{{ $carta->descricao }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex items-center rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-bold text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                        {{ $carta->ano ?: '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                        {{ $carta->estado === 'aprovado'  ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                                        {{ $carta->estado === 'submetido' ? 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-400' : '' }}
                                        {{ $carta->estado === 'rascunho'  ? 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' : '' }}
                                        {{ $carta->estado === 'rejeitado' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                                    ">
                                        {{ ucfirst($carta->estado) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    @if($carta->ficheiros->count())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($carta->ficheiros as $f)
                                                <a href="{{ route('storage.serve', $f->caminho) }}" target="_blank"
                                                    class="inline-flex items-center gap-1 rounded bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300"
                                                    title="{{ $f->nome_original }}">
                                                    <flux:icon name="paper-clip" class="h-3 w-3 text-zinc-400" />
                                                    {{ strtoupper($f->tipo_ficheiro) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-400 italic">Sem ficheiros</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-xs text-zinc-600 dark:text-zinc-300">{{ $carta->criadoPor?->name ?? 'Sistema' }}</td>
                                <td class="px-4 py-4 text-xs text-zinc-500">{{ $carta->created_at?->format('d/m/Y') }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" wire:click="verDetalhe({{ $carta->id }})" class="p-1 text-zinc-400 hover:text-amber-600" title="Ver Detalhes">
                                            <flux:icon name="eye" class="h-4 w-4" />
                                        </button>
                                        @if($podeEditar)
                                            <a href="{{ route('municipio.cartas.editar', [$municipio, $carta->id]) }}" wire:navigate class="p-1 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200" title="Editar">
                                                <flux:icon name="pencil" class="h-4 w-4" />
                                            </a>
                                        @endif
                                        @if($podeEliminar)
                                            <button type="button"
                                                x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmar Exclusão', message: 'Enviar esta carta para a lixeira?', action: 'eliminar', component: $wire.$id, params: [{{ $carta->id }}], variant: 'danger' })"
                                                class="p-1 text-zinc-400 hover:text-red-600" title="Eliminar">
                                                <flux:icon name="trash" class="h-4 w-4" />
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-zinc-500">
                                    Nenhuma carta municipal encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Paginação --}}
    @if($cartas->hasPages())
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            {{ $cartas->links() }}
        </div>
    @endif
</div>

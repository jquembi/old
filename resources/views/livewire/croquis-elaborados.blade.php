<div class="space-y-6">

    {{-- ── Breadcrumbs ───────────────────────────────────────────── --}}
    <nav class="flex items-center gap-1.5 text-sm text-zinc-500 dark:text-zinc-400">
        <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors" wire:navigate>Dashboard</a>
        <svg class="h-3.5 w-3.5 shrink-0 text-zinc-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors" wire:navigate>{{ $municipio->nome }}</a>
        <svg class="h-3.5 w-3.5 shrink-0 text-zinc-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('municipio.operacoes', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors" wire:navigate>Operações</a>
        <svg class="h-3.5 w-3.5 shrink-0 text-zinc-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-zinc-700 dark:text-zinc-200 font-medium">Croquis Elaborados</span>
    </nav>

    {{-- ── Cabeçalho ─────────────────────────────────────────────── --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50 tracking-tight">Croquis Elaborados</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Gestão e acompanhamento do workflow de publicação — <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $municipio->nome }}</span>
            </p>
        </div>
        @if($podeEditar)
            <a href="{{ route('municipio.operacoes.croqui.criar', $municipio) }}" wire:navigate>
                <flux:button variant="primary" icon="plus">Novo Croqui</flux:button>
            </a>
        @endif
    </div>

    {{-- ── Legenda do workflow ────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400">
        <span class="font-semibold text-zinc-600 dark:text-zinc-300 mr-1">Workflow:</span>
        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-blue-500"></span> PDF carregado</span>
        <svg class="h-3 w-3 text-zinc-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-violet-500"></span> Submetido ao INOTU</span>
        <svg class="h-3 w-3 text-zinc-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-green-500"></span> Pagamento RUPE confirmado</span>
        <svg class="h-3 w-3 text-zinc-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-sky-500"></span> Homologado (download livre)</span>
    </div>

    {{-- ── Barra de pesquisa ─────────────────────────────────────── --}}
    <flux:input
        wire:model.live.debounce.300ms="search"
        placeholder="Pesquisar por título ou nome do ficheiro…"
        icon="magnifying-glass"
        clearable
    />

    {{-- ── Lista de cards ────────────────────────────────────────── --}}
    <div class="space-y-3">
        @forelse($croquis as $croqui)
            @php
                $hasPdf      = (bool) $croqui->ficheiro_pdf_caminho;
                $hasMemoria  = (bool) $croqui->memoria_descritiva_caminho;
                $publicado   = $croqui->publicado;
                $rupPago     = $croqui->rup_pago;
                $homologado  = $croqui->download_liberado;
                $podeBaixar  = $croqui->podeBaixar(auth()->user());

                $passo = 0;
                if ($hasPdf)     $passo = 1;
                if ($publicado)  $passo = 2;
                if ($rupPago)    $passo = 3;
                if ($homologado) $passo = 4;

                $iconColor = match($passo) {
                    4 => 'bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400',
                    3 => 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
                    2 => 'bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400',
                    1 => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
                    default => 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500',
                };
            @endphp

            <div class="group bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 hover:shadow-sm hover:border-zinc-300 dark:hover:border-zinc-600 transition-all duration-200">
                <div class="flex items-start gap-4">

                    {{-- Ícone lateral --}}
                    <div class="shrink-0 h-11 w-11 rounded-xl {{ $iconColor }} flex items-center justify-center">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>

                    {{-- Conteúdo principal --}}
                    <div class="flex-1 min-w-0">

                        {{-- Linha 1: Título + Acções --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 truncate text-sm leading-5">
                                    {{ $croqui->titulo ?? 'Sem título' }}
                                </h3>
                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400 flex items-center gap-2 flex-wrap">
                                    @if($croqui->criadoPor)
                                        <span class="flex items-center gap-1">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            {{ $croqui->criadoPor->name }}
                                        </span>
                                    @endif
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        {{ $croqui->created_at->format('d/m/Y') }}
                                    </span>
                                    @if($hasPdf)
                                        <span class="flex items-center gap-1 text-blue-500 dark:text-blue-400">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                            {{ Str::limit($croqui->ficheiro_pdf_nome ?? 'PDF', 28) }}
                                        </span>
                                    @endif
                                    @if($hasMemoria)
                                        <span class="flex items-center gap-1 text-sky-500 dark:text-sky-400">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 3h7l5 5v13H7z"/></svg>
                                            {{ Str::limit($croqui->memoria_descritiva_nome ?? 'Memoria descritiva', 28) }}
                                        </span>
                                    @endif
                                </p>
                            </div>

                            {{-- Botões de acção --}}
                            <div class="flex items-center gap-1 shrink-0">

                                @if($podePublicar && ! $publicado && $hasPdf && $hasMemoria)
                                    <flux:button
                                        wire:click="publicarCroqui({{ $croqui->id }})"
                                        size="sm" variant="ghost" icon="paper-airplane"
                                        class="text-violet-500 hover:text-violet-700 hover:bg-violet-50 dark:hover:bg-violet-900/20"
                                        title="Submeter ao INOTU"
                                    />
                                @endif

                                @if(($podePublicar || $podeHomologar) && $publicado)
                                    <flux:button
                                        x-on:click.prevent="$dispatch('confirmar', {
                                            title: 'Cancelar submissão',
                                            message: 'Repõe o RUP e a homologação. Confirmar?',
                                            action: 'despublicarCroqui',
                                            component: $wire.$id,
                                            params: [{{ $croqui->id }}],
                                            variant: 'danger'
                                        })"
                                        size="sm" variant="ghost" icon="arrow-uturn-left"
                                        class="text-zinc-400 hover:text-zinc-600"
                                        title="Cancelar submissão"
                                    />
                                @endif

                                @if($publicado && $croqui->pagamentoActivo)
                                    <a href="{{ route('pagamento.detalhe', $croqui->pagamentoActivo) }}" wire:navigate title="Ver pagamento RUPE">
                                        <flux:button
                                            size="sm" variant="ghost" icon="currency-dollar"
                                            class="text-green-600 hover:text-green-700 hover:bg-green-50 dark:hover:bg-green-900/20"
                                        />
                                    </a>
                                @endif
                                @if($podeHomologar && $rupPago)
                                    @if(! $homologado)
                                        <flux:button
                                            wire:click="homologarDownload({{ $croqui->id }})"
                                            size="sm" variant="ghost" icon="check-badge"
                                            class="text-sky-600 hover:text-sky-700 hover:bg-sky-50 dark:hover:bg-sky-900/20"
                                            title="Homologar e liberar download"
                                        />
                                    @else
                                        <flux:button
                                            wire:click="revogarDownload({{ $croqui->id }})"
                                            size="sm" variant="ghost" icon="lock-closed"
                                            class="text-amber-600 hover:text-amber-700 hover:bg-amber-50 dark:hover:bg-amber-900/20"
                                            title="Revogar homologação"
                                        />
                                    @endif
                                @endif

                                <span class="h-4 w-px bg-zinc-200 dark:bg-zinc-700 mx-0.5"></span>

                                <a href="{{ route('croqui.detalhe', $croqui) }}" wire:navigate title="Ver detalhe">
                                    <flux:button size="sm" variant="ghost" icon="document-magnifying-glass" class="text-zinc-500 hover:text-zinc-700" />
                                </a>

                                @if($podeEditar)
                                    <a href="{{ route('municipio.operacoes.parecer.criar', $municipio) }}?recurso_tipo=croqui&recurso_id={{ $croqui->id }}" wire:navigate title="Criar parecer INOTU para este croqui">
                                        <flux:button size="sm" variant="ghost" icon="clipboard-document-check" class="text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20" />
                                    </a>
                                    <a href="{{ route('municipio.operacoes.croqui.editar', [$municipio, $croqui->id]) }}" wire:navigate title="Editar">
                                        <flux:button size="sm" variant="ghost" icon="pencil" class="text-zinc-500 hover:text-zinc-700" />
                                    </a>
                                    @if($podeEliminar)
                                        <flux:button
                                            x-on:click.prevent="$dispatch('confirmar', {
                                                title: 'Eliminar croqui',
                                                message: 'O croqui será enviado para a lixeira. Confirmar?',
                                                action: 'eliminar',
                                                component: $wire.$id,
                                                params: [{{ $croqui->id }}],
                                                variant: 'danger'
                                            })"
                                            size="sm" variant="ghost" icon="trash"
                                            class="text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                                            title="Eliminar"
                                        />
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Linha 2: Stepper de workflow --}}
                        <div class="mt-4 flex items-center gap-0">

                            {{-- Passo 1: PDF --}}
                            <div class="flex items-center gap-1.5">
                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $hasPdf ? 'bg-blue-500 text-white' : 'bg-zinc-200 text-zinc-400 dark:bg-zinc-700 dark:text-zinc-500' }}">
                                    @if($hasPdf)
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        1
                                    @endif
                                </div>
                                <span class="text-xs {{ $hasPdf ? 'text-blue-600 dark:text-blue-400 font-medium' : 'text-zinc-400 dark:text-zinc-500' }}">PDF</span>
                            </div>

                            <div class="mx-2 h-px w-8 {{ $publicado ? 'bg-violet-400' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>

                            {{-- Passo 2: Publicado --}}
                            <div class="flex items-center gap-1.5">
                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $publicado ? 'bg-violet-500 text-white' : ($hasPdf ? 'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400 ring-2 ring-violet-200 dark:ring-violet-900/40' : 'bg-zinc-200 text-zinc-400 dark:bg-zinc-700 dark:text-zinc-500') }}">
                                    @if($publicado)
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        2
                                    @endif
                                </div>
                                <span class="text-xs {{ $publicado ? 'text-violet-600 dark:text-violet-400 font-medium' : 'text-zinc-400 dark:text-zinc-500' }}">Publicado</span>
                            </div>

                            <div class="mx-2 h-px w-8 {{ $rupPago ? 'bg-green-400' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>

                            {{-- Passo 3: RUP --}}
                            <div class="flex items-center gap-1.5">
                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $rupPago ? 'bg-green-500 text-white' : ($publicado ? 'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400 ring-2 ring-green-200 dark:ring-green-900/40' : 'bg-zinc-200 text-zinc-400 dark:bg-zinc-700 dark:text-zinc-500') }}">
                                    @if($rupPago)
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        3
                                    @endif
                                </div>
                                <span class="text-xs {{ $rupPago ? 'text-green-600 dark:text-green-400 font-medium' : 'text-zinc-400 dark:text-zinc-500' }}">RUP</span>
                            </div>

                            <div class="mx-2 h-px w-8 {{ $homologado ? 'bg-sky-400' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>

                            {{-- Passo 4: Homologado --}}
                            <div class="flex items-center gap-1.5">
                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $homologado ? 'bg-sky-500 text-white' : ($rupPago ? 'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400 ring-2 ring-sky-200 dark:ring-sky-900/40' : 'bg-zinc-200 text-zinc-400 dark:bg-zinc-700 dark:text-zinc-500') }}">
                                    @if($homologado)
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        4
                                    @endif
                                </div>
                                <span class="text-xs {{ $homologado ? 'text-sky-600 dark:text-sky-400 font-medium' : 'text-zinc-400 dark:text-zinc-500' }}">Homologado</span>
                            </div>

                            @if($rupPago && $croqui->rup_referencia)
                                <span class="ml-4 inline-flex items-center gap-1 rounded-md bg-green-50 dark:bg-green-900/20 px-2 py-0.5 text-xs text-green-700 dark:text-green-400 font-mono border border-green-200 dark:border-green-800/50">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    {{ $croqui->rup_referencia }}
                                </span>
                            @elseif($publicado && $croqui->pagamentoActivo?->rupe_referencia)
                                <span class="ml-4 inline-flex items-center gap-1 rounded-md bg-violet-50 dark:bg-violet-900/20 px-2 py-0.5 text-xs text-violet-700 dark:text-violet-400 font-mono border border-violet-200 dark:border-violet-800/50">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    RUPE: {{ $croqui->pagamentoActivo->rupe_referencia }}
                                </span>
                            @endif

                            @if($passo === 0)
                                <span class="ml-3 text-xs text-zinc-400 italic">Aguarda PDF</span>
                            @elseif($passo === 1 && $podePublicar && ! $hasMemoria)
                                <span class="ml-3 text-xs text-amber-500 dark:text-amber-400 font-medium">Aguarda memoria descritiva</span>
                            @elseif($passo === 1 && $podePublicar)
                                <span class="ml-3 text-xs text-violet-500 dark:text-violet-400 font-medium animate-pulse">← Submeter ao INOTU</span>
                            @elseif($passo === 2 && $podePublicar && ! $croqui->pagamentoActivo?->rupe_referencia)
                                <span class="ml-3 text-xs text-amber-500 dark:text-amber-400 font-medium">Aguarda referência RUPE do INOTU</span>
                            @elseif($passo === 2 && $podePublicar && $croqui->pagamentoActivo?->rupe_referencia)
                                <span class="ml-3 text-xs text-green-500 dark:text-green-400 font-medium animate-pulse">← Ver pagamento</span>
                            @elseif($passo === 3 && $podeHomologar)
                                <span class="ml-3 text-xs text-sky-500 dark:text-sky-400 font-medium animate-pulse">← Homologar</span>
                            @endif

                        </div>

                    </div>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-16 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <svg class="h-7 w-7 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                @if($search)
                    <p class="font-medium text-zinc-700 dark:text-zinc-300">Nenhum resultado para «{{ $search }}»</p>
                    <p class="mt-1 text-sm text-zinc-400">Tente pesquisar com outras palavras.</p>
                @else
                    <p class="font-medium text-zinc-700 dark:text-zinc-300">Sem croquis elaborados</p>
                    <p class="mt-1 text-sm text-zinc-400">Comece por carregar o primeiro croqui em PDF.</p>
                    @if($podeEditar)
                        <div class="mt-4">
                            <a href="{{ route('municipio.operacoes.croqui.criar', $municipio) }}" wire:navigate>
                                <flux:button variant="primary" icon="plus" size="sm">Novo Croqui</flux:button>
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        @endforelse
    </div>

    {{-- ── Paginação ──────────────────────────────────────────────── --}}
    <div class="pt-1">{{ $croquis->links() }}</div>

    {{-- ── Modal: Registar Pagamento RUP ─────────────────────────── --}}
    <flux:modal wire:model="showRupModal" max-width="sm">
        <div class="p-6 space-y-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                    <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <flux:heading size="lg">Registar Pagamento RUP</flux:heading>
                    <flux:text class="text-zinc-500 text-sm">Insira a referência do comprovativo de pagamento.</flux:text>
                </div>
            </div>

            <flux:field>
                <flux:label for="rup_referencia">Referência do pagamento <span class="text-red-500">*</span></flux:label>
                <flux:input
                    id="rup_referencia"
                    wire:model="rup_referencia"
                    placeholder="Ex: RUP-2026-00123"
                    autocomplete="off"
                    icon="hashtag"
                />
                @error('rup_referencia')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <div class="flex justify-end gap-2 pt-1">
                <flux:button variant="ghost" wire:click="fecharModalRup">Cancelar</flux:button>
                <flux:button
                    variant="primary"
                    wire:click="registarRup($wire.rupCroquiId)"
                    icon="check"
                >
                    Confirmar pagamento
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>

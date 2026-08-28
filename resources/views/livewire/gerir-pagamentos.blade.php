@php
    use App\Enums\EstadoPagamento;

    $tabs = [
        'todos'                  => ['label' => 'Todos',           'count' => $stats['total'],                'cor' => 'zinc'],
        'aguardando_pagamento'   => ['label' => 'Aguardando RUPE', 'count' => $stats['aguardando_pagamento'], 'cor' => 'amber'],
        'rupe_gerado'            => ['label' => 'RUPE gerado',     'count' => $stats['rupe_gerado'],          'cor' => 'blue'],
        'comprovativo_submetido' => ['label' => 'Comprovativo',    'count' => $stats['comprovativo'],         'cor' => 'violet'],
        'pago'                   => ['label' => 'Confirmados',     'count' => $stats['confirmados'],          'cor' => 'green'],
        'pagamento_rejeitado'    => ['label' => 'Rejeitados',      'count' => $stats['rejeitados'],           'cor' => 'red'],
    ];

    $corBadge = [
        'amber'  => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
        'blue'   => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'violet' => 'bg-violet-100 text-violet-800 dark:bg-violet-900/30 dark:text-violet-300',
        'green'  => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'red'    => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        'sky'    => 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-300',
        'zinc'   => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
    ];
@endphp

<div class="space-y-6">

    {{-- ─── Cabeçalho ─── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Gestão de Pagamentos RUPE</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Gerir referências RUPE e confirmar pagamentos dos croquis elaborados.
            </p>
        </div>
    </div>

    {{-- ─── Tabs de filtro ─── --}}
    <div class="flex flex-wrap gap-2">
        @foreach($tabs as $valor => $tab)
            @php
                $ativo = $filtroEstado === $valor;
                $corAtivoClasses = match($tab['cor']) {
                    'amber'  => 'border-amber-500 text-amber-700 dark:text-amber-400',
                    'blue'   => 'border-blue-500 text-blue-700 dark:text-blue-400',
                    'violet' => 'border-violet-500 text-violet-700 dark:text-violet-400',
                    'green'  => 'border-green-500 text-green-700 dark:text-green-400',
                    'red'    => 'border-red-500 text-red-700 dark:text-red-400',
                    default  => 'border-zinc-500 text-zinc-700 dark:text-zinc-300',
                };
            @endphp
            <button
                wire:click="$set('filtroEstado', '{{ $valor }}')"
                class="inline-flex items-center gap-1.5 rounded-full border px-4 py-1.5 text-sm font-medium transition-colors
                    {{ $ativo
                        ? $corAtivoClasses . ' bg-white dark:bg-zinc-800 border-2'
                        : 'border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 hover:border-zinc-300 bg-white dark:bg-zinc-800/50' }}"
            >
                {{ $tab['label'] }}
                <span class="rounded-full px-1.5 py-0.5 text-xs {{ $corBadge[$tab['cor']] }}">{{ $tab['count'] }}</span>
            </button>
        @endforeach
    </div>

    {{-- ─── Barra de pesquisa ─── --}}
    <div class="relative">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <x-heroicon-o-magnifying-glass class="h-4 w-4 text-zinc-400" />
        </div>
        <input
            type="text"
            wire:model.live.debounce.400ms="search"
            placeholder="Pesquisar por croqui, referência ou município…"
            class="block w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800
                   pl-10 pr-4 py-2.5 text-sm text-zinc-900 dark:text-white placeholder:text-zinc-400
                   focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
        />
    </div>

    {{-- ─── Lista de Pagamentos ─── --}}
    <div class="space-y-3">
        @forelse($pagamentos as $pag)
            @php
                /** @var \App\Models\Pagamento $pag */
                $estado      = $pag->estado;
                $cor         = $estado->cor();
                $iconeBadge  = $estado->icone();
                $labelEstado = $estado->label();
                $badgeClass  = $corBadge[$cor] ?? $corBadge['zinc'];
            @endphp

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 shadow-sm p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between gap-4">

                    {{-- Ícone + Info --}}
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="shrink-0 flex h-10 w-10 items-center justify-center rounded-xl
                            {{ match($cor) {
                                'amber'  => 'bg-amber-100 dark:bg-amber-900/30',
                                'blue'   => 'bg-blue-100 dark:bg-blue-900/30',
                                'violet' => 'bg-violet-100 dark:bg-violet-900/30',
                                'green'  => 'bg-green-100 dark:bg-green-900/30',
                                'red'    => 'bg-red-100 dark:bg-red-900/30',
                                default  => 'bg-zinc-100 dark:bg-zinc-700',
                            } }}">
                            @if($iconeBadge === 'clock')
                                <x-heroicon-o-clock class="h-5 w-5 {{ match($cor) {
                                    'amber' => 'text-amber-600', 'blue' => 'text-blue-600',
                                    'violet' => 'text-violet-600', 'green' => 'text-green-600',
                                    'red' => 'text-red-600', default => 'text-zinc-500',
                                } }}" />
                            @elseif($iconeBadge === 'document-text')
                                <x-heroicon-o-document-text class="h-5 w-5 text-blue-600" />
                            @elseif($iconeBadge === 'paper-clip')
                                <x-heroicon-o-paper-clip class="h-5 w-5 text-violet-600" />
                            @elseif($iconeBadge === 'check-circle')
                                <x-heroicon-o-check-circle class="h-5 w-5 text-green-600" />
                            @elseif($iconeBadge === 'x-circle')
                                <x-heroicon-o-x-circle class="h-5 w-5 text-red-600" />
                            @endif
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold text-zinc-900 dark:text-white truncate">
                                    {{ $pag->croqui?->titulo ?? '(croqui removido)' }}
                                </h3>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                    {{ $labelEstado }}
                                </span>
                            </div>

                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                                @if($pag->municipio)
                                    <span class="inline-flex items-center gap-1">
                                        <x-heroicon-o-map-pin class="h-3.5 w-3.5" />
                                        {{ $pag->municipio->nome }}
                                    </span>
                                @endif
                                @if($pag->rupe_referencia)
                                    <span class="inline-flex items-center gap-1 font-mono">
                                        <x-heroicon-o-hashtag class="h-3.5 w-3.5" />
                                        {{ $pag->rupe_referencia }}
                                    </span>
                                @endif
                                @if($pag->valor)
                                    <span class="inline-flex items-center gap-1 font-medium text-zinc-700 dark:text-zinc-300">
                                        <x-heroicon-o-banknotes class="h-3.5 w-3.5" />
                                        {{ number_format((float) $pag->valor, 2, ',', '.') }} Kz
                                    </span>
                                @endif
                                @if($pag->rupe_data_limite)
                                    <span class="inline-flex items-center gap-1 {{ $pag->rupe_data_limite->isPast() && !$pag->isPago() ? 'text-red-500' : '' }}">
                                        <x-heroicon-o-calendar class="h-3.5 w-3.5" />
                                        Limite: {{ $pag->rupe_data_limite->format('d/m/Y') }}
                                    </span>
                                @endif
                                <span class="inline-flex items-center gap-1">
                                    <x-heroicon-o-clock class="h-3.5 w-3.5" />
                                    {{ $pag->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Acções --}}
                    <div class="shrink-0 flex flex-wrap items-center gap-1.5">

                        {{-- Ver detalhe --}}
                        <a href="{{ route('pagamento.detalhe', $pag) }}"
                           class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 dark:border-zinc-600 px-2.5 py-1.5 text-xs text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                            <x-heroicon-o-eye class="h-3.5 w-3.5" />
                            Ver
                        </a>

                        {{-- Gerar RUPE (só INOTU, quando aguardando) --}}
                        @if($pag->podeGerarRupe())
                            <button wire:click="abrirGerarRupe({{ $pag->id }})"
                                    class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-2.5 py-1.5 text-xs text-white hover:bg-blue-700 transition-colors font-medium">
                                <x-heroicon-o-document-text class="h-3.5 w-3.5" />
                                Gerar RUPE
                            </button>
                        @endif

                        {{-- Comprovativo submetido → Confirmar / Rejeitar --}}
                        @if($pag->podeConfirmarRejeitar())
                            <button wire:click="abrirVerComprovativo({{ $pag->id }})"
                                    class="inline-flex items-center gap-1 rounded-lg border border-violet-300 dark:border-violet-700 px-2.5 py-1.5 text-xs text-violet-700 dark:text-violet-300 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-colors">
                                <x-heroicon-o-paper-clip class="h-3.5 w-3.5" />
                                Ver comprovativo
                            </button>
                            <button wire:click="abrirConfirmar({{ $pag->id }})"
                                    class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-2.5 py-1.5 text-xs text-white hover:bg-green-700 transition-colors font-medium">
                                <x-heroicon-o-check-circle class="h-3.5 w-3.5" />
                                Confirmar
                            </button>
                            <button wire:click="abrirRejeitar({{ $pag->id }})"
                                    class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-2.5 py-1.5 text-xs text-white hover:bg-red-700 transition-colors font-medium">
                                <x-heroicon-o-x-circle class="h-3.5 w-3.5" />
                                Rejeitar
                            </button>
                        @endif

                        {{-- Imprimir RUPE --}}
                        @if($pag->rupe_referencia)
                            <a href="{{ route('pagamento.rupe', $pag) }}" target="_blank"
                               class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 dark:border-zinc-600 px-2.5 py-1.5 text-xs text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                                <x-heroicon-o-printer class="h-3.5 w-3.5" />
                                RUPE
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800/50 p-12 text-center">
                <x-heroicon-o-banknotes class="mx-auto h-10 w-10 text-zinc-400" />
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Nenhum pagamento encontrado.</p>
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div>{{ $pagamentos->links() }}</div>

    {{-- ══════════════════════════════════════════════════════════
         Modal: Gerar RUPE
    ══════════════════════════════════════════════════════════ --}}
    @if($showGerarRupeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             wire:click.self="fecharGerarRupe">
            <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-zinc-800 shadow-xl p-6 space-y-5"
                 wire:click.stop>
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Gerar Referência RUPE</h2>
                    <button wire:click="fecharGerarRupe" class="rounded-lg p-1 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Referência RUPE --}}
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Referência RUPE <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="rupeReferencia" placeholder="Ex: 012345678901234"
                               class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 font-mono" />
                        @error('rupeReferencia') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Entidade --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Entidade <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="rupeEntidade" placeholder="Ex: 00001"
                               class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 font-mono" />
                        @error('rupeEntidade') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Valor --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Valor (Kz) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" wire:model="rupeValor" placeholder="0.00" step="0.01" min="0.01"
                               class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                        @error('rupeValor') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Data Limite --}}
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Data Limite de Pagamento <span class="text-red-500">*</span>
                        </label>
                        <input type="date" wire:model="rupeDataLimite"
                               class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                        @error('rupeDataLimite') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Observações --}}
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Observações</label>
                        <textarea wire:model="rupeObservacoes" rows="3" placeholder="Observações opcionais…"
                                  class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"></textarea>
                        @error('rupeObservacoes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                    <button wire:click="fecharGerarRupe"
                            class="rounded-xl border border-zinc-200 dark:border-zinc-600 px-4 py-2 text-sm text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        Cancelar
                    </button>
                    <button wire:click="gerarRupe" wire:loading.attr="disabled"
                            class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="gerarRupe">Gerar RUPE</span>
                        <span wire:loading wire:target="gerarRupe">A gerar…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════
         Modal: Ver Comprovativo
    ══════════════════════════════════════════════════════════ --}}
    @if($showVerComproativoModal)
        @php
            $pagComp = \App\Models\Pagamento::find($comproativoPagamentoId);
        @endphp
        @if($pagComp && $pagComp->comprovativo_path)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                 wire:click.self="fecharVerComprovativo">
                <div class="w-full max-w-2xl rounded-2xl bg-white dark:bg-zinc-800 shadow-xl p-6 space-y-4"
                     wire:click.stop>
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Comprovativo de Pagamento</h2>
                        <button wire:click="fecharVerComprovativo" class="rounded-lg p-1 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                            <x-heroicon-o-x-mark class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="text-sm text-zinc-600 dark:text-zinc-400">
                        <span class="font-medium">Ficheiro:</span> {{ $pagComp->comprovativo_nome }}
                        @if($pagComp->comprovativo_em)
                            · Submetido {{ $pagComp->comprovativo_em->format('d/m/Y H:i') }}
                        @endif
                    </div>

                    @if(str_starts_with($pagComp->comprovativo_mime ?? '', 'image/'))
                        <img src="{{ route('storage.serve', ['path' => $pagComp->comprovativo_path]) }}"
                             alt="Comprovativo" class="max-h-96 mx-auto rounded-lg object-contain" />
                    @else
                        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 p-8 text-center">
                            <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-zinc-400 mb-3" />
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Documento PDF</p>
                            <a href="{{ route('storage.serve', ['path' => $pagComp->comprovativo_path]) }}"
                               target="_blank"
                               class="mt-3 inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
                                <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                                Abrir PDF
                            </a>
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                        <button wire:click="fecharVerComprovativo"
                                class="rounded-xl border border-zinc-200 dark:border-zinc-600 px-4 py-2 text-sm text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                            Fechar
                        </button>
                        <button wire:click="abrirConfirmar({{ $pagComp->id }}); fecharVerComprovativo()"
                                class="rounded-xl bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                            Confirmar Pagamento
                        </button>
                        <button wire:click="abrirRejeitar({{ $pagComp->id }}); fecharVerComprovativo()"
                                class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                            Rejeitar
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- ══════════════════════════════════════════════════════════
         Modal: Confirmar Pagamento
    ══════════════════════════════════════════════════════════ --}}
    @if($showConfirmarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             wire:click.self="fecharConfirmar">
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-zinc-800 shadow-xl p-6 space-y-4"
                 wire:click.stop>
                <div class="flex items-start gap-3">
                    <div class="shrink-0 flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                        <x-heroicon-o-check-circle class="h-5 w-5 text-green-600" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Confirmar Pagamento</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Ao confirmar, o download do croqui será libertado para o município.
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Observações (opcional)</label>
                    <textarea wire:model="observacoesConfirmacao" rows="3"
                              class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500 resize-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                    <button wire:click="fecharConfirmar"
                            class="rounded-xl border border-zinc-200 dark:border-zinc-600 px-4 py-2 text-sm text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        Cancelar
                    </button>
                    <button wire:click="confirmarPagamento" wire:loading.attr="disabled"
                            class="rounded-xl bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="confirmarPagamento">Confirmar Pagamento</span>
                        <span wire:loading wire:target="confirmarPagamento">A confirmar…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════
         Modal: Rejeitar Pagamento
    ══════════════════════════════════════════════════════════ --}}
    @if($showRejeitarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             wire:click.self="fecharRejeitar">
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-zinc-800 shadow-xl p-6 space-y-4"
                 wire:click.stop>
                <div class="flex items-start gap-3">
                    <div class="shrink-0 flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                        <x-heroicon-o-x-circle class="h-5 w-5 text-red-600" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Rejeitar Pagamento</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            O município será notificado para submeter um novo comprovativo.
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Motivo da Rejeição <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="motivoRejeicao" rows="4" placeholder="Descreva o motivo da rejeição…"
                              class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500 resize-none"></textarea>
                    @error('motivoRejeicao') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                    <button wire:click="fecharRejeitar"
                            class="rounded-xl border border-zinc-200 dark:border-zinc-600 px-4 py-2 text-sm text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        Cancelar
                    </button>
                    <button wire:click="rejeitarPagamento" wire:loading.attr="disabled"
                            class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="rejeitarPagamento">Rejeitar</span>
                        <span wire:loading wire:target="rejeitarPagamento">A rejeitar…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

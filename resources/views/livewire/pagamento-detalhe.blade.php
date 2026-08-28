@php
    use App\Enums\EstadoPagamento;

    /** @var \App\Models\Pagamento $pagamento */

    // Passos do timeline
    $passos = [
        1 => [
            'titulo'  => 'Croqui publicado',
            'sub'     => 'INOTU publicou o croqui',
            'feito'   => true,
            'data'    => $pagamento->croqui?->publicado_em,
            'agente'  => $pagamento->criadoPor?->name,
        ],
        2 => [
            'titulo'  => 'RUPE gerado',
            'sub'     => 'INOTU gerou a referência de pagamento',
            'feito'   => !$pagamento->aguardandoPagamento(),
            'activo'  => $pagamento->aguardandoPagamento(),
            'data'    => $pagamento->rupe_gerado_em,
            'agente'  => $pagamento->rupeGeradoPor?->name,
        ],
        3 => [
            'titulo'  => 'Comprovativo submetido',
            'sub'     => 'Município submeteu o comprovativo de pagamento',
            'feito'   => in_array($pagamento->estado, [
                EstadoPagamento::ComproativoSubmetido,
                EstadoPagamento::Pago,
                EstadoPagamento::Concluido,
            ]),
            'activo'  => $pagamento->rupeGerado() || $pagamento->isRejeitado(),
            'rejeitado' => $pagamento->isRejeitado(),
            'data'    => $pagamento->comprovativo_em,
            'agente'  => $pagamento->comprovativoPor?->name,
        ],
        4 => [
            'titulo'  => 'Pagamento confirmado',
            'sub'     => 'INOTU confirmou a recepção do pagamento',
            'feito'   => $pagamento->isPago(),
            'activo'  => $pagamento->comproativoSubmetido(),
            'data'    => $pagamento->confirmado_em,
            'agente'  => $pagamento->confirmadoPor?->name,
        ],
        5 => [
            'titulo'  => 'Documentos liberados',
            'sub'     => 'O download do croqui está disponível',
            'feito'   => $pagamento->croqui?->download_liberado ?? false,
            'activo'  => false,
            'data'    => $pagamento->croqui?->liberado_em,
            'agente'  => null,
        ],
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

    {{-- ─── Breadcrumb ─── --}}
    <nav class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
        <a href="{{ route('pagamentos.gerir') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200">Pagamentos RUPE</a>
        <x-heroicon-o-chevron-right class="h-4 w-4" />
        <span class="text-zinc-900 dark:text-white font-medium truncate">{{ $pagamento->croqui?->titulo ?? 'Pagamento #' . $pagamento->id }}</span>
    </nav>

    {{-- ─── Cabeçalho ─── --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                    {{ $pagamento->croqui?->titulo ?? 'Croqui removido' }}
                </h1>
                @php
                    $cor = $pagamento->estado->cor();
                @endphp
                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $corBadge[$cor] ?? $corBadge['zinc'] }}">
                    {{ $pagamento->estado->label() }}
                </span>
            </div>
            @if($pagamento->municipio)
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    <x-heroicon-o-map-pin class="inline h-4 w-4 mr-1" />
                    {{ $pagamento->municipio->nome }}
                </p>
            @endif
        </div>

        <div class="flex items-center gap-2">
            @if($pagamento->rupe_referencia)
                <a href="{{ route('pagamento.rupe', $pagamento) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-600 px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                    <x-heroicon-o-printer class="h-4 w-4" />
                    Imprimir RUPE
                </a>
            @endif
            @if($pagamento->croqui && $pagamento->croqui->download_liberado)
                <a href="{{ route('croqui.detalhe', $pagamento->croqui) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                    <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                    Ver croqui
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- ─── Timeline ─── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Notificação de rejeição --}}
            @if($pagamento->isRejeitado())
                <div class="rounded-2xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4">
                    <div class="flex items-start gap-3">
                        <x-heroicon-o-exclamation-triangle class="mt-0.5 h-5 w-5 shrink-0 text-red-600" />
                        <div>
                            <h3 class="font-semibold text-red-800 dark:text-red-300">Pagamento rejeitado</h3>
                            <p class="mt-1 text-sm text-red-700 dark:text-red-400">{{ $pagamento->motivo_rejeicao }}</p>
                            @if($podeSubmeter)
                                <button wire:click="abrirUpload"
                                        class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700">
                                    <x-heroicon-o-paper-clip class="h-3.5 w-3.5" />
                                    Submeter novo comprovativo
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Notificação de pagamento confirmado --}}
            @if($pagamento->isPago())
                <div class="rounded-2xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-check-badge class="h-5 w-5 shrink-0 text-green-600" />
                        <div>
                            <h3 class="font-semibold text-green-800 dark:text-green-300">Pagamento confirmado</h3>
                            <p class="mt-1 text-sm text-green-700 dark:text-green-400">
                                O download do croqui está disponível.
                                @if($pagamento->confirmado_em)
                                    Confirmado em {{ $pagamento->confirmado_em->format('d/m/Y H:i') }}.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Timeline --}}
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6">
                <h2 class="font-semibold text-zinc-900 dark:text-white mb-5">Progresso do Processo</h2>

                <ol class="relative border-l-2 border-zinc-200 dark:border-zinc-700 ml-3 space-y-6">
                    @foreach($passos as $num => $passo)
                        @php
                            $feito    = $passo['feito'] ?? false;
                            $activo   = ($passo['activo'] ?? false) && !$feito;
                            $rejeitado = $passo['rejeitado'] ?? false;
                        @endphp
                        <li class="relative ml-6">
                            {{-- Bolinha --}}
                            <span class="absolute -left-9 flex h-6 w-6 items-center justify-center rounded-full border-2 ring-4 ring-white dark:ring-zinc-800
                                {{ $feito
                                    ? 'bg-green-600 border-green-600'
                                    : ($rejeitado
                                        ? 'bg-red-500 border-red-500'
                                        : ($activo
                                            ? 'bg-blue-500 border-blue-500 animate-pulse'
                                            : 'bg-zinc-200 border-zinc-300 dark:bg-zinc-700 dark:border-zinc-600')) }}">
                                @if($feito)
                                    <x-heroicon-s-check class="h-3 w-3 text-white" />
                                @elseif($rejeitado)
                                    <x-heroicon-s-x-mark class="h-3 w-3 text-white" />
                                @else
                                    <span class="text-xs font-bold {{ $activo ? 'text-white' : 'text-zinc-500 dark:text-zinc-400' }}">{{ $num }}</span>
                                @endif
                            </span>

                            <div class="pb-1">
                                <h3 class="text-sm font-semibold {{ $feito ? 'text-zinc-900 dark:text-white' : ($activo ? 'text-blue-700 dark:text-blue-400' : 'text-zinc-500 dark:text-zinc-400') }}">
                                    {{ $passo['titulo'] }}
                                </h3>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $passo['sub'] }}</p>
                                @if($passo['data'])
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">
                                        {{ $passo['data'] instanceof \Carbon\Carbon ? $passo['data']->format('d/m/Y H:i') : \Carbon\Carbon::parse($passo['data'])->format('d/m/Y H:i') }}
                                        @if($passo['agente'])
                                            · {{ $passo['agente'] }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            {{-- Upload comprovativo (município, estado rupe_gerado) --}}
            @if($podeSubmeter && !$pagamento->isRejeitado())
                <div class="rounded-2xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-5">
                    <div class="flex items-start gap-3">
                        <x-heroicon-o-paper-clip class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" />
                        <div class="flex-1">
                            <h3 class="font-semibold text-blue-900 dark:text-blue-200">Submeter Comprovativo</h3>
                            <p class="mt-1 text-sm text-blue-700 dark:text-blue-400">
                                Após efectuar o pagamento RUPE, submeta o comprovativo (PDF, JPG ou PNG, máx. 5 MB).
                            </p>
                            <button wire:click="abrirUpload"
                                    class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                <x-heroicon-o-paper-clip class="h-4 w-4" />
                                Submeter comprovativo
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ─── Painel lateral: Detalhes RUPE ─── --}}
        <div class="space-y-4">

            {{-- Dados do croqui --}}
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-5 space-y-3">
                <h2 class="font-semibold text-zinc-900 dark:text-white">Croqui</h2>
                @if($pagamento->croqui)
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">Título</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $pagamento->croqui->titulo }}</dd>
                        </div>
                        @if($pagamento->croqui->tipo)
                            <div>
                                <dt class="text-zinc-500 dark:text-zinc-400">Tipo</dt>
                                <dd class="text-zinc-900 dark:text-white">{{ $pagamento->croqui->tipo }}</dd>
                            </div>
                        @endif
                        @if($pagamento->croqui->publicado_em)
                            <div>
                                <dt class="text-zinc-500 dark:text-zinc-400">Publicado em</dt>
                                <dd class="text-zinc-900 dark:text-white">{{ $pagamento->croqui->publicado_em->format('d/m/Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                @endif
            </div>

            {{-- Dados RUPE --}}
            @if($pagamento->rupe_referencia)
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold text-zinc-900 dark:text-white">Referência RUPE</h2>
                        <a href="{{ route('pagamento.rupe', $pagamento) }}" target="_blank"
                           class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                            <x-heroicon-o-printer class="h-3.5 w-3.5" />
                            Imprimir
                        </a>
                    </div>

                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">Referência</dt>
                            <dd class="font-mono font-bold text-zinc-900 dark:text-white text-base">{{ $pagamento->rupe_referencia }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">Entidade</dt>
                            <dd class="font-mono text-zinc-900 dark:text-white">{{ $pagamento->rupe_entidade }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">Valor</dt>
                            <dd class="font-semibold text-zinc-900 dark:text-white">
                                {{ number_format((float) $pagamento->valor, 2, ',', '.') }} Kz
                            </dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">Data limite</dt>
                            <dd class="{{ $pagamento->rupe_data_limite?->isPast() && !$pagamento->isPago() ? 'text-red-600 font-semibold' : 'text-zinc-900 dark:text-white' }}">
                                {{ $pagamento->rupe_data_limite?->format('d/m/Y') }}
                                @if($pagamento->rupe_data_limite?->isPast() && !$pagamento->isPago())
                                    <span class="text-xs font-medium">(vencido)</span>
                                @endif
                            </dd>
                        </div>
                        @if($pagamento->observacoes)
                            <div>
                                <dt class="text-zinc-500 dark:text-zinc-400">Observações</dt>
                                <dd class="text-zinc-700 dark:text-zinc-300">{{ $pagamento->observacoes }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif

            {{-- Comprovativo --}}
            @if($pagamento->comprovativo_path)
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-5 space-y-3">
                    <h2 class="font-semibold text-zinc-900 dark:text-white">Comprovativo</h2>
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900/30">
                            <x-heroicon-o-paper-clip class="h-4 w-4 text-violet-600" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-zinc-900 dark:text-white truncate">{{ $pagamento->comprovativo_nome }}</p>
                            @if($pagamento->comprovativo_em)
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $pagamento->comprovativo_em->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('storage.serve', ['path' => $pagamento->comprovativo_path]) }}"
                       target="_blank"
                       class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-zinc-200 dark:border-zinc-600 px-3 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                        Ver comprovativo
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         Modal: Upload Comprovativo
    ══════════════════════════════════════════════════════════ --}}
    @if($showUploadModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             wire:click.self="fecharUpload">
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-zinc-800 shadow-xl p-6 space-y-5"
                 wire:click.stop>
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Submeter Comprovativo</h2>
                    <button wire:click="fecharUpload" class="rounded-lg p-1 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                        Ficheiro do comprovativo <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed rounded-xl cursor-pointer
                                      border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-900
                                      hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                @if($comprovativo)
                                    <x-heroicon-o-document-check class="h-8 w-8 text-green-500 mb-2" />
                                    <p class="text-sm text-zinc-700 dark:text-zinc-300 font-medium">{{ $comprovativo->getClientOriginalName() }}</p>
                                    <p class="text-xs text-zinc-500 mt-1">{{ number_format($comprovativo->getSize() / 1024, 1) }} KB</p>
                                @else
                                    <x-heroicon-o-paper-clip class="h-8 w-8 text-zinc-400 mb-2" />
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        <span class="font-medium text-blue-600">Clique para seleccionar</span>
                                        ou arraste o ficheiro
                                    </p>
                                    <p class="text-xs text-zinc-400 mt-1">PDF, JPG, PNG — máx. 5 MB</p>
                                @endif
                            </div>
                            <input type="file" wire:model="comprovativo" accept=".pdf,.jpg,.jpeg,.png" class="hidden" />
                        </label>
                    </div>
                    @error('comprovativo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="comprovativo" class="mt-3 space-y-2">
                        <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <span>A carregar ficheiro...</span>
                            <span>{{ $this->uploadProgress }}%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                            <div class="h-full rounded-full bg-blue-600 transition-all duration-300"
                                 style="width: {{ $this->uploadProgress }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                    <button wire:click="fecharUpload"
                            class="rounded-xl border border-zinc-200 dark:border-zinc-600 px-4 py-2 text-sm text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        Cancelar
                    </button>
                    <button wire:click="submeterComprovativo" wire:loading.attr="disabled" wire:target="submeterComprovativo,comprovativo"
                            class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="submeterComprovativo">Submeter</span>
                        <span wire:loading wire:target="submeterComprovativo">A submeter…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

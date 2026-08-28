<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 py-8 px-4">
    <div class="mx-auto max-w-3xl">

        {{-- Cabeçalho da página --}}
        <div class="mb-6 flex items-center gap-3">
            <a href="{{ url()->previous() }}"
               class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar
            </a>
            <flux:heading size="xl">{{ $instrumento->titulo }}</flux:heading>
        </div>

        {{-- Card Principal --}}
        <div class="relative rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

            {{-- QR Code — canto superior direito --}}
            <div class="absolute right-6 top-6">
                <div class="rounded-xl border border-zinc-200 bg-white p-2 shadow dark:border-zinc-700 dark:bg-zinc-800"
                     title="Aponte a câmara para partilhar este documento">
                    <canvas
                        x-data
                        x-init="renderQrCode($el, '{{ route('instrumento.detalhe', $instrumento->id) }}', 96)"
                        width="96"
                        height="96"
                        class="block"
                    ></canvas>
                    <p class="mt-1 text-center text-[10px] text-zinc-400">Documento #{{ $instrumento->id }}</p>
                </div>
            </div>

            {{-- Informações do instrumento (deixando espaço para o QR) --}}
            <div class="pr-28">
                {{-- Badges --}}
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                        {{ $instrumento->estado === 'aprovado'  ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                        {{ $instrumento->estado === 'submetido' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                        {{ $instrumento->estado === 'rascunho'  ? 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' : '' }}
                        {{ $instrumento->estado === 'rejeitado' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                    ">
                        {{ ucfirst($instrumento->estado) }}
                    </span>
                    @if($instrumento->tipoInstrumento?->categoria)
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300">
                            {{ $instrumento->tipoInstrumento->categoria->nome }}
                        </span>
                    @endif
                    @if($instrumento->tipoInstrumento)
                        <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-sky-700 dark:bg-sky-900/20 dark:text-sky-300">
                            {{ $instrumento->tipoInstrumento->nome }}
                        </span>
                    @endif
                </div>

                {{-- Metadados --}}
                <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Município</dt>
                        <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $instrumento->municipio?->nome ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Ano</dt>
                        <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $instrumento->ano ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Criado por</dt>
                        <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $instrumento->criadoPor?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Data</dt>
                        <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $instrumento->created_at?->format('d/m/Y') }}</dd>
                    </div>
                </dl>

                @if($instrumento->descricao)
                    <div class="mt-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Descrição</dt>
                        <dd class="mt-1 text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">{{ $instrumento->descricao }}</dd>
                    </div>
                @endif
            </div>

            {{-- Separador --}}
            <div class="mt-6 border-t border-zinc-100 dark:border-zinc-800"></div>

            {{-- Ficheiros --}}
            <div class="mt-4">
                <flux:heading size="sm" class="mb-3">
                    Ficheiros ({{ $instrumento->ficheiros->count() }})
                </flux:heading>

                @if($instrumento->ficheiros->isNotEmpty())
                    <ul class="space-y-2">
                        @foreach($instrumento->ficheiros as $f)
                            <li class="flex items-center justify-between rounded-lg border border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white shadow-sm dark:bg-zinc-700">
                                        <svg class="h-5 w-5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $f->nome_original }}</p>
                                        <p class="text-xs text-zinc-400">
                                            {{ strtoupper($f->tipo_ficheiro) }} &middot;
                                            {{ number_format($f->tamanho / 1024, 1) }} KB &middot;
                                            {{ $f->created_at?->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>
                                <a href="{{ route('storage.serve', $f->caminho) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Descarregar
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-zinc-400">Nenhum ficheiro associado.</p>
                @endif
            </div>
        </div>

    </div>
</div>

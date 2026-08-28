<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 py-8 px-4">
    <div class="mx-auto max-w-3xl">

        <div class="mb-6 flex items-center gap-3">
            <a href="{{ url()->previous() }}"
               class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar
            </a>
            <flux:heading size="xl">{{ $parecer->titulo }}</flux:heading>
        </div>

        <div class="relative rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

            {{-- QR Code — canto superior direito --}}
            <div class="absolute right-6 top-6">
                <div class="rounded-xl border border-zinc-200 bg-white p-2 shadow dark:border-zinc-700 dark:bg-zinc-800">
                    <canvas
                        x-data
                        x-init="renderQrCode($el, '{{ route('parecer.detalhe', $parecer->id) }}', 96)"
                        width="96"
                        height="96"
                        class="block"
                    ></canvas>
                    <p class="mt-1 text-center text-[10px] text-zinc-400">Parecer #{{ $parecer->id }}</p>
                </div>
            </div>

            <div class="pr-28">
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                        {{ $parecer->estado === 'aprovado'  ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                        {{ $parecer->estado === 'pendente'  ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}
                        {{ $parecer->estado === 'rejeitado' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                    ">
                        {{ ucfirst($parecer->estado) }}
                    </span>
                </div>

                <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Projecto</dt>
                        <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $parecer->projecto_nome }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Município</dt>
                        <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $parecer->municipio?->nome ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Criado por</dt>
                        <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $parecer->criadoPor?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Data</dt>
                        <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $parecer->created_at?->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Servico</dt>
                        <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $parecer->servicoLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Area reservada</dt>
                        <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $parecer->areaReservadaLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Classificacao</dt>
                        <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $parecer->classificacao_area ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Uso do solo</dt>
                        <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $parecer->uso_solo ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Zona</dt>
                        <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $parecer->zona_ordenamento ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Tipo de concessao</dt>
                        <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $parecer->tipo_concessao ?: '—' }}</dd>
                    </div>
                </dl>

                @if($parecer->descricao)
                    <div class="mt-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Descrição</dt>
                        <dd class="mt-1 text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">{{ $parecer->descricao }}</dd>
                    </div>
                @endif

                @if($parecer->fundamentacao || $parecer->conclusao)
                    <div class="mt-5 grid gap-4">
                        @if($parecer->fundamentacao)
                            <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950/60">
                                <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Fundamentacao tecnica</dt>
                                <dd class="mt-2 text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">{{ $parecer->fundamentacao }}</dd>
                            </div>
                        @endif

                        @if($parecer->conclusao)
                            <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950/60">
                                <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Conclusao</dt>
                                <dd class="mt-2 text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">{{ $parecer->conclusao }}</dd>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            @if($parecer->ficheiro_caminho)
                <div class="mt-6 border-t border-zinc-100 dark:border-zinc-800"></div>
                <div class="mt-4">
                    <flux:heading size="sm" class="mb-3">Ficheiro PDF</flux:heading>
                    <div class="flex items-center justify-between rounded-lg border border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white shadow-sm dark:bg-zinc-700">
                                <svg class="h-5 w-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $parecer->ficheiro_nome ?? 'Parecer.pdf' }}
                            </p>
                        </div>
                        <a href="{{ route('storage.serve', $parecer->ficheiro_caminho) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Descarregar PDF
                        </a>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

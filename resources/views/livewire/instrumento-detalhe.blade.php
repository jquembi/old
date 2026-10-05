<div class="space-y-6">
    {{-- Navegação e Cabeçalho --}}
    <div class="no-print">
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $instrumento->municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $instrumento->municipio->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.instrumentos-ordenamento', $instrumento->municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Instrumentos de Ordenamento</a>
            <span>/</span>
            <a href="{{ route('municipio.instrumentos.tipo', [$instrumento->municipio, $instrumento->tipoInstrumento->slug]) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $instrumento->tipoInstrumento->nome }}</a>
            <span>/</span>
            <span class="font-medium text-zinc-800 dark:text-zinc-200">#{{ $instrumento->id }}</span>
        </div>

        <div class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400">
                    <flux:icon name="map" class="h-7 w-7" />
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:heading size="xl">{{ $instrumento->titulo }}</flux:heading>
                        @if($instrumento->ano)
                            <span class="rounded-lg bg-zinc-100 px-2.5 py-0.5 text-xs font-bold text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                Ano: {{ $instrumento->ano }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold
                            {{ $instrumento->estado === 'aprovado'  ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                            {{ $instrumento->estado === 'submetido' ? 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-400' : '' }}
                            {{ $instrumento->estado === 'rascunho'  ? 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' : '' }}
                            {{ $instrumento->estado === 'rejeitado' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                        ">
                            <span class="h-1.5 w-1.5 rounded-full
                                {{ $instrumento->estado === 'aprovado'  ? 'bg-emerald-500' : '' }}
                                {{ $instrumento->estado === 'submetido' ? 'bg-sky-500' : '' }}
                                {{ $instrumento->estado === 'rascunho'  ? 'bg-zinc-400' : '' }}
                                {{ $instrumento->estado === 'rejeitado' ? 'bg-red-500' : '' }}
                            "></span>
                            {{ ucfirst($instrumento->estado) }}
                        </span>
                    </div>
                    <flux:text class="mt-1">
                        {{ $instrumento->tipoInstrumento->nome }} &bull; {{ $instrumento->municipio->nome }} &bull; Província da Huíla
                    </flux:text>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('municipio.instrumentos.tipo', [$instrumento->municipio, $instrumento->tipoInstrumento->slug]) }}" wire:navigate>
                    <flux:button variant="ghost" icon="arrow-left">Voltar</flux:button>
                </a>
                <flux:button variant="ghost" icon="printer" onclick="window.print()">
                    Imprimir Ficha
                </flux:button>
                @if(auth()->user()?->temPermissao('instrumentos.editar_municipio'))
                    <a href="{{ route('municipio.instrumentos.tipo.editar', [$instrumento->municipio, $instrumento->tipoInstrumento->slug, $instrumento->id]) }}" wire:navigate>
                        <flux:button variant="primary" icon="pencil">Editar</flux:button>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Layout em Duas Colunas --}}
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        {{-- Coluna Principal: Ficha Técnica e Visualizador de PDF --}}
        <div class="space-y-6">
            {{-- Metadados Principais --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-4">
                    Ficha Técnica do Instrumento
                </h3>

                <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-xl bg-zinc-50 p-3.5 dark:bg-zinc-950/40">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Município</dt>
                        <dd class="mt-1 font-bold text-zinc-900 dark:text-zinc-100">{{ $instrumento->municipio->nome }}</dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3.5 dark:bg-zinc-950/40">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Ano de Vigência</dt>
                        <dd class="mt-1 font-bold text-red-600 dark:text-red-400">{{ $instrumento->ano ?? 'Não especificado' }}</dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3.5 dark:bg-zinc-950/40">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Categoria</dt>
                        <dd class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $instrumento->tipoInstrumento->categoria?->nome ?? 'Ordenamento do Território' }}
                        </dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3.5 dark:bg-zinc-950/40">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Tipo de Instrumento</dt>
                        <dd class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ $instrumento->tipoInstrumento->nome }}</dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3.5 dark:bg-zinc-950/40">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Técnico / Responsável</dt>
                        <dd class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ $instrumento->criadoPor?->name ?? 'Sistema' }}</dd>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3.5 dark:bg-zinc-950/40">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Data de Entrada no Sistema</dt>
                        <dd class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ $instrumento->created_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>

                {{-- Descrição / Memória Descritiva --}}
                @if($instrumento->descricao)
                    <div class="mt-6 border-t border-zinc-100 pt-5 dark:border-zinc-800">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2">
                            Memória Descritiva & Resumo do Plano
                        </h4>
                        <div class="rounded-xl bg-zinc-50/70 p-4 text-sm leading-relaxed text-zinc-700 dark:bg-zinc-950/30 dark:text-zinc-300 whitespace-pre-line">
                            {{ $instrumento->descricao }}
                        </div>
                    </div>
                @endif
            </div>

            {{-- Pré-Visualizador de PDF Embutido --}}
            @if($ficheiroAtivo && str_ends_with(strtolower($ficheiroAtivo), '.pdf'))
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                <flux:icon name="document-text" class="h-4 w-4 text-red-600" />
                                Visualização do Documento Técnico
                            </h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                Leitura digital do documento do plano directamente na plataforma
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('storage.serve', $ficheiroAtivo) }}" target="_blank">
                                <flux:button size="sm" variant="subtle" icon="arrow-top-right-on-square">
                                    Abrir em Nova Aba
                                </flux:button>
                            </a>
                            <a href="{{ route('storage.download', $ficheiroAtivo) }}">
                                <flux:button size="sm" variant="primary" icon="arrow-down-tray">
                                    Descarregar PDF
                                </flux:button>
                            </a>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-950">
                        <iframe src="{{ route('storage.serve', $ficheiroAtivo) }}" class="w-full h-[680px] rounded-xl" title="Documento do Instrumento"></iframe>
                    </div>
                </div>
            @endif
        </div>

        {{-- Coluna Lateral: Ficheiros & QR Code Oficial --}}
        <aside class="space-y-6">
            {{-- QR Code Oficial de Autenticidade e Partilha --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 text-center">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-400">
                    Identificação & QR Oficial
                </span>

                <div class="mx-auto my-4 flex h-36 w-36 items-center justify-center rounded-2xl border border-zinc-200 bg-white p-3 shadow-xs dark:border-zinc-700 dark:bg-zinc-800">
                    <canvas
                        x-data
                        x-init="renderQrCode($el, '{{ route('instrumento.detalhe', $instrumento->id) }}', 120)"
                        width="120"
                        height="120"
                        class="block"
                    ></canvas>
                </div>

                <p class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                    Documento #{{ str_pad($instrumento->id, 5, '0', STR_PAD_LEFT) }}
                </p>
                <p class="mt-0.5 text-[11px] text-zinc-400">
                    Aponte a câmara para validar ou consultar o instrumento
                </p>
            </div>

            {{-- Lista de Peças Técnicas e Ficheiros --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Ficheiros e Cartografia ({{ $instrumento->ficheiros->count() }})
                    </h4>
                </div>

                @if($instrumento->ficheiros->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($instrumento->ficheiros as $f)
                            @php
                                $isPdf = strtolower($f->tipo_ficheiro) === 'pdf';
                                $isAtivo = $ficheiroAtivo === $f->caminho;
                            @endphp
                            <div class="rounded-xl border p-3 text-xs transition
                                {{ $isAtivo
                                    ? 'border-red-300 bg-red-50/40 dark:border-red-800 dark:bg-red-950/20'
                                    : 'border-zinc-100 bg-zinc-50/60 dark:border-zinc-800 dark:bg-zinc-950/40 hover:bg-zinc-100/80 dark:hover:bg-zinc-800/50' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                                            {{ $isPdf ? 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' }}">
                                            <flux:icon name="document-text" class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-zinc-900 dark:text-zinc-100" title="{{ $f->nome_original }}">
                                                {{ $f->nome_original }}
                                            </p>
                                            <p class="text-[11px] text-zinc-400">
                                                {{ strtoupper($f->tipo_ficheiro) }} &bull; {{ number_format($f->tamanho / 1024, 1) }} KB
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2.5 flex items-center justify-end gap-1.5 border-t border-zinc-200/60 pt-2 dark:border-zinc-800">
                                    @if($isPdf)
                                        <button type="button" wire:click="selecionarFicheiro('{{ $f->caminho }}')"
                                            class="rounded-md px-2 py-1 text-[11px] font-semibold text-zinc-600 hover:bg-zinc-200 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            {{ $isAtivo ? 'A visualisar' : 'Visualisar' }}
                                        </button>
                                    @endif
                                    <a href="{{ route('storage.serve', $f->caminho) }}" target="_blank"
                                        class="rounded-md px-2 py-1 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/50">
                                        Abrir
                                    </a>
                                    <a href="{{ route('storage.download', $f->caminho) }}"
                                        class="rounded-md bg-zinc-900 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white">
                                        Baixar
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-zinc-400 italic text-center py-4">Nenhum ficheiro anexado a este instrumento.</p>
                @endif
            </div>
        </aside>
    </div>
</div>

<div class="space-y-6">
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio?->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.operacoes.ids-terreno.detalhe', [$municipio, $croqui]) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>ID de Terreno</a>
            <span>/</span>
            <span>Memoria Descritiva</span>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <flux:heading size="xl">Memoria Descritiva</flux:heading>
                    <flux:text class="mt-1">{{ $croqui->memoria_descritiva_nome ?? $croqui->titulo }}</flux:text>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                        <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $croqui->codigo_territorial }}</span>
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">QR de verificacao aplicado</span>
                    </div>
                </div>
                <a href="{{ route('municipio.operacoes.ids-terreno.detalhe', [$municipio, $croqui]) }}" wire:navigate>
                    <flux:button variant="ghost" icon="arrow-left">Voltar ao ID</flux:button>
                </a>
            </div>
        </div>
    </div>

    @if($conversorWordDisponivel)
        <livewire:visualizador-pdf
            :caminho="$croqui->memoria_descritiva_caminho"
            :label="$croqui->memoria_descritiva_nome ?? 'Memoria descritiva'"
            :podeDownload="false"
        />
    @else
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 border-b border-amber-200 bg-amber-50 px-5 py-4 text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-3">
                    <flux:icon name="exclamation-triangle" class="mt-0.5 h-5 w-5 flex-none" />
                    <div>
                        <p class="font-semibold">Pre-visualizacao simplificada</p>
                        <p class="mt-1 text-sm">
                            O servidor ainda nao tem conversor Word instalado. Enquanto isso, o sistema exibe uma leitura interna protegida do conteudo, sem permitir acesso directo ao ficheiro.
                        </p>
                    </div>
                </div>

                @if($qrUrl)
                    <div class="flex items-center gap-3 rounded-lg bg-white px-3 py-2 text-zinc-700 shadow-sm dark:bg-zinc-950 dark:text-zinc-200">
                        <canvas
                            width="72"
                            height="72"
                            x-data
                            x-init="renderQrCode($el, '{{ $qrUrl }}', 72)"
                            aria-label="QR de validacao"
                        ></canvas>
                        <div class="text-xs">
                            <p class="font-semibold">QR de validacao</p>
                            <p class="text-zinc-500 dark:text-zinc-400">Verificacao publica do documento</p>
                        </div>
                    </div>
                @endif
            </div>

            @if($memoriaHtmlPreview !== '')
                <div class="bg-zinc-100 px-3 py-4 dark:bg-zinc-950 sm:px-6">
                    <div class="mx-auto min-h-[640px] max-w-4xl bg-white p-6 text-sm leading-7 text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-zinc-100 sm:p-10">
                        <div class="docx-preview space-y-4">
                            {!! $memoriaHtmlPreview !!}
                        </div>
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center gap-2 py-12 text-zinc-400 dark:text-zinc-500">
                    <flux:icon name="document" class="h-10 w-10" />
                    <span class="text-sm">Nao foi possivel gerar a pre-visualizacao deste Word.</span>
                </div>
            @endif
        </div>
    @endif

    <style>
        .docx-preview h3 {
            margin: 1rem 0 .5rem;
            font-size: 1rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .docx-preview p {
            margin: .35rem 0;
        }

        .docx-preview table {
            width: 100%;
            margin: .75rem 0;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .docx-preview td {
            border: 1px solid #d4d4d8;
            padding: .45rem .55rem;
            vertical-align: top;
            word-break: break-word;
        }

        .dark .docx-preview td {
            border-color: #52525b;
        }
    </style>
</div>

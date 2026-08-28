@if($caminho)
    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50 px-4 py-2 dark:border-zinc-700 dark:bg-zinc-800/70">
            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">
                <flux:icon name="document" class="inline-block w-4 h-4 mr-1 text-red-500" />
                {{ $label }}
            </span>

            @if($podeDownload)
                <a
                    href="{{ $urlDownload }}"
                    class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs text-emerald-600 hover:bg-emerald-50 hover:text-emerald-800 dark:text-emerald-400 dark:hover:bg-emerald-950/40 dark:hover:text-emerald-300"
                >
                    <flux:icon name="arrow-down-tray" class="w-3.5 h-3.5" />
                    Download
                </a>
            @endif
        </div>

        @if($tipoPorExtensao === 'pdf')
            <iframe
                src="{{ $urlVisualizar }}"
                class="w-full bg-zinc-100 dark:bg-zinc-950"
                style="min-height: 480px; height: 60vh; border: none;"
                title="{{ $label }}"
                loading="lazy"
            ></iframe>
        @elseif($tipoPorExtensao === 'imagem')
            <div class="flex items-center justify-center bg-zinc-100 p-4 dark:bg-zinc-950">
                <img
                    src="{{ $urlVisualizar }}"
                    alt="{{ $label }}"
                    class="max-w-full rounded shadow"
                    style="max-height: 480px;"
                />
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-2 py-10 text-zinc-400 dark:text-zinc-500">
                <flux:icon name="document" class="w-10 h-10" />
                <span class="text-sm">Pre-visualizacao nao disponivel</span>
            </div>
        @endif
    </div>
@else
    <div class="flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-zinc-300 py-8 text-zinc-400 dark:border-zinc-700 dark:text-zinc-500">
        <flux:icon name="document-text" class="w-8 h-8" />
        <span class="text-sm">Nenhum documento disponivel</span>
    </div>
@endif

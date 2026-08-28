@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginação" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-zinc-600 dark:text-zinc-400">
            Mostrando
            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $paginator->firstItem() }}</span>
            a
            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $paginator->lastItem() }}</span>
            de
            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $paginator->total() }}</span>
            municípios
        </p>

        <div class="flex flex-wrap items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-zinc-200 bg-zinc-100 px-3 text-sm font-medium text-zinc-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-600">
                    Anterior
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" wire:navigate class="inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-zinc-200 bg-white px-3 text-sm font-medium text-zinc-700 transition hover:border-red-300 hover:text-red-700 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300 dark:hover:border-red-700 dark:hover:text-red-300">
                    Anterior
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-500">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-red-600 bg-red-600 px-3 text-sm font-semibold text-white">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" wire:navigate class="inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-zinc-200 bg-white px-3 text-sm font-medium text-zinc-700 transition hover:border-red-300 hover:text-red-700 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300 dark:hover:border-red-700 dark:hover:text-red-300" aria-label="Ir para página {{ $page }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" wire:navigate class="inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-zinc-200 bg-white px-3 text-sm font-medium text-zinc-700 transition hover:border-red-300 hover:text-red-700 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300 dark:hover:border-red-700 dark:hover:text-red-300">
                    Próximo
                </a>
            @else
                <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-zinc-200 bg-zinc-100 px-3 text-sm font-medium text-zinc-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-600">
                    Próximo
                </span>
            @endif
        </div>
    </nav>
@endif

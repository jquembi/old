@props([
    'sidebar' => false,
])

<a {{ $attributes->class([
    'flex min-w-0 items-center gap-3 rounded-md text-zinc-900 transition hover:text-angola-red dark:text-white dark:hover:text-white',
    'px-2 py-1.5' => $sidebar,
]) }}>
    <span class="flex aspect-square size-8 shrink-0 items-center justify-center rounded-md bg-white p-0.5 ring-1 ring-white/20">
        <img src="/imagens/no-background/INOTU.png" alt="INOTU" class="size-8 object-contain" />
    </span>
    <span class="truncate text-sm font-semibold tracking-normal">G.TERRAS</span>
</a>

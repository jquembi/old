@props([
    'title',
    'description',
])

<div class="flex w-full flex-col gap-2 text-left">
    <p class="text-xs font-bold uppercase tracking-[.16em] text-[#C30E20]">Conta institucional</p>
    <h1 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-white">{{ $title }}</h1>
    <p class="text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
</div>

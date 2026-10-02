@props([
    'title' => 'Sem dados para apresentar',
    'description' => null,
])

<div {{ $attributes->class('ui-empty-state') }}>
    <div class="ui-empty-state-icon">
        @isset($icon)
            {{ $icon }}
        @else
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 11.5A7.5 7.5 0 1 1 4.5 8.75M5 4v4.5h4.5" /></svg>
        @endisset
    </div>
    <p class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $title }}</p>
    @if($description)<p class="mt-1 max-w-sm text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>@endif
    @isset($action)<div class="mt-5">{{ $action }}</div>@endisset
</div>

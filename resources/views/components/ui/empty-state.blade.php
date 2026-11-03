@props([
    'title' => 'Sem dados para apresentar',
    'description' => null,
])

<div {{ $attributes->class('ui-empty-state') }}>
    <div class="ui-empty-state-icon" aria-hidden="true">
        @isset($icon)
            {{ $icon }}
        @else
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5h5.1l1.8 2.25h9.6a1.5 1.5 0 0 1 1.5 1.5v7.5a1.5 1.5 0 0 1-1.5 1.5h-15a1.5 1.5 0 0 1-1.5-1.5V9a1.5 1.5 0 0 1 1.5-1.5Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 14.25h7.5m-7.5 3h4.5" />
            </svg>
        @endisset
    </div>
    <h3 class="ui-empty-state-title">{{ $title }}</h3>
    @if($description)<p class="mt-1 max-w-sm text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>@endif
    @isset($action)<div class="mt-5">{{ $action }}</div>@endisset
</div>

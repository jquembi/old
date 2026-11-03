@props([
    'columns' => 'md:grid-cols-3',
    'clear' => null,
])

<div {{ $attributes->class('ui-filter-bar') }} role="search">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <div class="grid min-w-0 flex-1 grid-cols-1 gap-3 {{ $columns }}">{{ $slot }}</div>
        @isset($trailing)<div class="shrink-0">{{ $trailing }}</div>@endisset
    </div>

    @if($clear || isset($actions))
        <div class="ui-filter-actions">
            {{ $actions ?? '' }}
            @if($clear)
                <button type="button" wire:click="{{ $clear }}" class="btn-table btn-table-neutral">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    Limpar filtros
                </button>
            @endif
        </div>
    @endif
</div>

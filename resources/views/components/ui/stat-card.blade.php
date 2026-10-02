@props([
    'label',
    'value',
    'detail' => null,
    'tone' => 'red',
])

<article {{ $attributes->class('ui-stat-card') }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="ui-stat-label">{{ $label }}</p>
            <p class="ui-stat-value">{{ $value }}</p>
        </div>
        @isset($icon)
            <div class="ui-icon-tile ui-icon-tile-{{ $tone }}">{{ $icon }}</div>
        @endisset
    </div>

    @if($detail)
        <p class="ui-stat-detail">{{ $detail }}</p>
    @endif
</article>

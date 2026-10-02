@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<header {{ $attributes->class('ui-page-header') }}>
    <div class="min-w-0">
        @if($eyebrow)
            <p class="ui-eyebrow">{{ $eyebrow }}</p>
        @endif

        <h1 class="ui-page-title">{{ $title }}</h1>

        @if($description)
            <p class="ui-page-description">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="ui-page-actions">{{ $actions }}</div>
    @endisset
</header>

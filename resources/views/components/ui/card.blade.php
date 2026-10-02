@props([
    'title' => null,
    'description' => null,
    'padding' => 'p-5 sm:p-6',
])

<section {{ $attributes->class('ui-card') }}>
    @if($title || $description || isset($actions))
        <div class="ui-card-header">
            <div>
                @if($title)<h2 class="ui-card-title">{{ $title }}</h2>@endif
                @if($description)<p class="ui-card-description">{{ $description }}</p>@endif
            </div>
            @isset($actions)<div class="shrink-0">{{ $actions }}</div>@endisset
        </div>
    @endif

    <div class="{{ $padding }}">{{ $slot }}</div>
</section>

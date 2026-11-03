@props([
    'wrapperClass' => '',
])

<div class="ui-search {{ $wrapperClass }}">
    <svg class="ui-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path stroke-linecap="round" stroke-linejoin="round" d="m20 20-4.2-4.2"/></svg>
    <input {{ $attributes->merge([
        'type' => 'search',
        'class' => 'ui-control ui-search-input',
        'placeholder' => 'Pesquisar...',
        'aria-label' => 'Pesquisar',
        'autocomplete' => 'off',
    ]) }}>
</div>

@props([
    'type' => 'select',
    'label' => null,
    'error' => null,
    'wrapperClass' => '',
])

@php($id = $attributes->get('id', 'ff-'.\Illuminate\Support\Str::random(6)))

<div class="ui-filter-field {{ $wrapperClass }}">
    @if($label)<label for="{{ $id }}" class="ui-filter-label">{{ $label }}</label>@endif

    @if($type === 'select')
        <select {{ $attributes->merge(['id' => $id, 'class' => 'ui-control ui-select']) }}>{{ $slot }}</select>
    @else
        <input {{ $attributes->merge(['id' => $id, 'type' => $type, 'class' => 'ui-control']) }}>
    @endif

    @if($error)<p class="mt-1 text-xs text-red-600">{{ $error }}</p>@endif
</div>

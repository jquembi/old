@props(['status', 'label' => null])

@php
    $status = strtolower((string) $status);
    $tones = [
        'online' => 'success', 'active' => 'success', 'ativo' => 'success', 'success' => 'success', 'synced' => 'success',
        'pending' => 'warning', 'offline' => 'warning', 'pendente' => 'warning',
        'failed' => 'danger', 'error' => 'danger', 'inativo' => 'danger', 'inactive' => 'danger',
        'central' => 'brand', 'provincial' => 'neutral',
    ];
    $tone = $tones[$status] ?? 'neutral';
@endphp

<span {{ $attributes->class("ui-status ui-status-{$tone}") }}>
    <span class="ui-status-dot" aria-hidden="true"></span>
    {{ $label ?? ucfirst($status) }}
</span>

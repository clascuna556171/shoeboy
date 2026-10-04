@props([
    'value' => null,
    'kind' => 'order',
    'label' => null,
    'dot' => false,
])

@php
    $variant = \App\Support\StatusBadge::variant($kind, $value);
    $text = $label ?? \App\Support\StatusBadge::label($kind, $value);
@endphp

<span {{ $attributes->class(['badge', $variant]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-80" aria-hidden="true"></span>
    @endif
    {{ $text }}
</span>

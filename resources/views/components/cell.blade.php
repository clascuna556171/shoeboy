@props(['value' => null, 'muted' => false])

@php
    $text = $value ?? trim((string) $slot);
    $classes = 'cell-text'.($muted ? ' text-neutral-500 dark:text-neutral-400' : '');
@endphp

<span {{ $attributes->merge(['class' => $classes]) }} data-tip="{{ $text }}">{{ $text }}</span>

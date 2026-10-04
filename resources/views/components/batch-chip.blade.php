@props([
    'batch' => null,
    'code' => null,
])

@php($label = $code ?? $batch?->batch_code)

@if($label)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md bg-neutral-100 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300']) }}>{{ $label }}</span>
@endif

@props([
    'title' => '',
    'eyebrow' => null,
    'accent' => 'neutral',
    'close' => '',
    'size' => 'md',
    'scroll' => false,
])

@php
    $accents = [
        'emerald' => ['dot' => 'bg-emerald-500', 'btn' => 'app-btn-emerald'],
        'amber'   => ['dot' => 'bg-amber-500',   'btn' => 'app-btn-amber'],
        'rose'    => ['dot' => 'bg-rose-500',    'btn' => 'app-btn-rose'],
        'sky'     => ['dot' => 'bg-sky-500',     'btn' => 'app-btn-sky'],
        'indigo'  => ['dot' => 'bg-indigo-500',  'btn' => 'app-btn-indigo'],
        'violet'  => ['dot' => 'bg-violet-500',  'btn' => 'app-btn-violet'],
        'teal'    => ['dot' => 'bg-teal-500',    'btn' => 'app-btn-teal'],
        'blue'    => ['dot' => 'bg-blue-500',    'btn' => 'app-btn-blue'],
        'neutral' => ['dot' => 'bg-neutral-400', 'btn' => 'app-btn-primary'],
    ];
    $a = $accents[$accent] ?? $accents['neutral'];
    $maxWidth = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg'][$size] ?? 'max-w-md';
    $showExpr = $attributes->get('x-show');
@endphp

<template x-teleport="body">
    <div {{ $attributes->merge(['class' => 'fixed inset-0 z-50 overflow-y-auto bg-black/30 dark:bg-black/50 backdrop-blur-[2px] app-modal-backdrop']) }}
         @if($showExpr) x-effect="({{ $showExpr }}) ? window.appScrollLock?.lock() : window.appScrollLock?.unlock()" @endif>
        <div class="flex min-h-[100dvh] items-center justify-center p-4">
            <div class="app-modal-panel bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 rounded-3xl w-full {{ $maxWidth }} p-6 shadow-2xl {{ $scroll ? 'max-h-[90vh] overflow-y-auto' : '' }}"
                 @click.outside="{{ $close }}">

                <div class="flex items-center justify-between gap-3 pb-3 border-b border-neutral-200 dark:border-neutral-800">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="h-2.5 w-2.5 rounded-full {{ $a['dot'] }} shrink-0"></span>
                        <div class="min-w-0">
                            @if($eyebrow)
                                <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">{{ $eyebrow }}</div>
                            @endif
                            <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white truncate">
                                {{ $heading ?? $title }}
                            </h3>
                        </div>
                    </div>
                    <button type="button" @click="{{ $close }}" class="p-2 -m-1 rounded-xl text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="pt-4">{{ $slot }}</div>
            </div>
        </div>
    </div>
</template>

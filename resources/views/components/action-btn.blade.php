@props([
    'icon' => null,
    'tone' => 'secondary',
    'href' => null,
])

@php
    $tones = [
        'secondary' => 'app-btn-secondary',
        'primary' => 'app-btn-primary',
        'danger' => 'app-btn-danger',
        'rose' => 'app-btn-rose',
        'emerald' => 'app-btn-emerald',
        'ghost-danger' => 'app-btn-ghost hover:text-rose-600 dark:hover:text-rose-400',
    ];
    $toneClass = $tones[$tone] ?? $tones['secondary'];

    $icons = [
        'edit' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'trash' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
        'open' => 'M14 5l7 7m0 0l-7 7m7-7H3',
        'eye' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
        'check' => 'M5 13l4 4L19 7',
        'x' => 'M6 18L18 6M6 6l12 12',
        'power' => 'M13 10V3L4 14h7v7l9-11h-7z',
        'receipt' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    ];
    $iconPath = $icon ? ($icons[$icon] ?? null) : null;
    $classes = trim("app-btn app-btn-sm {$toneClass} " . ($attributes->get('class') ?? ''));
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->except('class')->merge(['class' => $classes]) }}>
        @if($iconPath)<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/></svg>@endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->except('class')->merge(['type' => 'button', 'class' => $classes]) }}>
        @if($iconPath)<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/></svg>@endif
        {{ $slot }}
    </button>
@endif

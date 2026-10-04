@props(['counts' => []])

@php
    $tones = [
        'emerald' => 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-300',
        'amber' => 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-900/50 text-amber-800 dark:text-amber-300',
        'blue' => 'bg-blue-50 dark:bg-blue-950/40 border-blue-200 dark:border-blue-900/50 text-blue-800 dark:text-blue-300',
        'rose' => 'bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-900/50 text-rose-800 dark:text-rose-300',
        'neutral' => 'bg-neutral-100 dark:bg-neutral-800/60 border-neutral-200 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300',
    ];
@endphp

<div class="flex flex-wrap items-center gap-2 text-xs">
    @foreach($counts as $count)
        <div class="px-3 py-2 rounded-xl border {{ $tones[$count['tone'] ?? 'neutral'] ?? $tones['neutral'] }}">
            <span class="font-mono font-bold">{{ $count['value'] }}</span> {{ $count['label'] }}
        </div>
    @endforeach
</div>

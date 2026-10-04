@props([
    'column',
    'label',
    'align' => 'left',
    'method' => 'sortBy',
    'active' => 'orderBy',
    'dir' => 'orderDir',
])

@php
    $alignClass = $align === 'right' ? 'text-right' : ($align === 'center' ? 'text-center' : 'text-left');
    $justifyClass = $align === 'right' ? 'justify-end' : ($align === 'center' ? 'justify-center' : 'justify-start');
@endphp

<th class="py-3 px-4 font-semibold uppercase {{ $alignClass }}">
    <button type="button"
            @click="{{ $method }}('{{ $column }}', $event)"
            class="flex w-full items-center gap-1 uppercase {{ $justifyClass }}"
            :class="{{ $active }} === '{{ $column }}' ? 'text-[#0071E3] dark:text-[#0A84FF]' : 'hover:text-neutral-700 dark:hover:text-neutral-200'">
        <span>{{ $label }}</span>
        <svg class="w-3 h-3 shrink-0"
             :class="{{ $active }} === '{{ $column }}' ? '' : 'opacity-30'"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  :d="({{ $active }} === '{{ $column }}' && {{ $dir }} === 'asc') ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'"/>
        </svg>
    </button>
</th>

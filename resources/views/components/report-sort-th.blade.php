@props([
    'column',
    'label',
    'align' => 'left',
])

@php
    $alignClass = $align === 'right' ? 'text-right' : ($align === 'center' ? 'text-center' : 'text-left');
    $justifyClass = $align === 'right' ? 'justify-end' : ($align === 'center' ? 'justify-center' : 'justify-start');
@endphp

{{-- Report sortable header: sorts server-side via a same-page GET link
     (scroll position is preserved by the layout's scroll memory). The href is
     built client-side so the current breakdown tab is carried across the reload. --}}
<th class="py-3 px-4 font-semibold {{ $alignClass }}">
    <a :href="sortUrl('{{ $column }}')"
       class="flex w-full items-center gap-1 {{ $justifyClass }}"
       :class="sort === '{{ $column }}' ? 'text-[#0071E3] dark:text-[#0A84FF]' : 'hover:text-neutral-700 dark:hover:text-neutral-200'">
        <span>{{ $label }}</span>
        <svg class="w-3 h-3 shrink-0" :class="sort === '{{ $column }}' ? '' : 'opacity-30'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"
                  :class="(sort === '{{ $column }}' && dir === 'asc') ? 'hidden' : ''"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"
                  :class="(sort === '{{ $column }}' && dir === 'asc') ? '' : 'hidden'"/>
        </svg>
    </a>
</th>

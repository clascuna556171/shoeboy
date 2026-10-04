@php
    $align = $align ?? 'left';
    $isActive = request('sort') === $column;
    $dir = request('direction') === 'asc' ? 'asc' : 'desc';
    $nextDir = ($isActive && $dir === 'asc') ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDir]);
    $alignClass = $align === 'right' ? 'text-right' : ($align === 'center' ? 'text-center' : 'text-left');
    $justifyClass = $align === 'right' ? 'justify-end' : ($align === 'center' ? 'justify-center' : 'justify-start');
@endphp
<th class="py-3 px-4 font-semibold {{ $alignClass }}">
    <a href="{{ $url }}"
       class="flex w-full items-center gap-1 {{ $justifyClass }} {{ $isActive ? 'text-[#0071E3] dark:text-[#0A84FF]' : 'hover:text-neutral-700 dark:hover:text-neutral-200' }}">
        <span>{{ $label }}</span>
        <svg class="w-3 h-3 shrink-0 {{ $isActive ? '' : 'opacity-30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            @if($isActive && $dir === 'asc')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            @else
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            @endif
        </svg>
    </a>
</th>

@props([
    'action',
    'filters' => [],
    'search' => null,
    'searchPlaceholder' => 'Search...',
    'resetUrl' => null,
    'sorts' => [],
])

@php
    $sortCol = request('sort');
    $sortDir = request('direction', 'desc');
    $currentSort = $sortCol ? $sortCol.'~'.$sortDir : '~';
    $hasActive = $search || $sortCol || collect($filters)->contains(fn ($f) => ! empty($f['selected']));
@endphp

{{-- One-row toolbar: search, filters, sort and actions all stay on a single
     scrollable line on phones (no wrapping into stacked rows). --}}
<div class="app-card p-3 lg:p-4">
    <form method="GET" action="{{ $action }}"
          x-data="{ sortSel: '{{ $currentSort }}' }"
          @submit="const p = sortSel.split('~'); $refs.sortEl.value = p[0] || ''; $refs.dirEl.value = p[1] || 'desc';"
          class="flex flex-nowrap items-center gap-2 overflow-x-auto">
        <input type="hidden" name="sort" x-ref="sortEl" value="{{ $sortCol }}">
        <input type="hidden" name="direction" x-ref="dirEl" value="{{ $sortDir }}">

        <div class="relative flex-1 min-w-[9rem]">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-neutral-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" name="search" data-tour="search" value="{{ $search }}" placeholder="{{ $searchPlaceholder }}"
                   class="app-input app-input-sm pl-9">
        </div>

        @foreach($filters as $filter)
            <select name="{{ $filter['name'] }}" @if($loop->first) data-tour="filters" @endif
                    class="app-select app-input-sm !w-auto shrink-0">
                @foreach($filter['options'] as $value => $label)
                    <option value="{{ $value }}" @selected((string) ($filter['selected'] ?? '') === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
        @endforeach

        @if(count($sorts))
            <select x-model="sortSel" class="app-select app-input-sm !w-auto shrink-0" title="Sort">
                <option value="~">Sort: Default</option>
                @foreach($sorts as $s)
                    <option value="{{ $s['column'] }}~{{ $s['direction'] ?? 'desc' }}">{{ $s['label'] }}</option>
                @endforeach
            </select>
        @endif

        <button type="submit" class="app-btn app-btn-primary app-btn-sm shrink-0">Apply</button>

        @if($hasActive)
            <a href="{{ $resetUrl ?? $action }}" class="app-btn app-btn-secondary app-btn-sm shrink-0">Reset</a>
        @endif
    </form>
</div>

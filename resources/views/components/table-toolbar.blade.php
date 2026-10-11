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
    $activeFilters = collect($filters)->filter(fn ($f) => ! empty($f['selected']));
    $hasActive = $search || $sortCol || $activeFilters->isNotEmpty();
    $hasOptions = count($filters) || count($sorts);
@endphp

{{-- Single-row toolbar: search + Options popover (filters + sort) + actions.
     Everything else lives in the popover so the row never scrolls or wraps. --}}
<div class="app-card p-3 lg:p-4">
    <form method="GET" action="{{ $action }}"
          x-data="{ sortSel: '{{ $currentSort }}', filtersOpen: false }"
          @submit="const p = sortSel.split('~'); $refs.sortEl.value = p[0] || ''; $refs.dirEl.value = p[1] || 'desc';"
          @click.outside="filtersOpen = false"
          class="relative flex flex-wrap items-center gap-2">
        <input type="hidden" name="sort" x-ref="sortEl" value="{{ $sortCol }}">
        <input type="hidden" name="direction" x-ref="dirEl" value="{{ $sortDir }}">

        <div class="relative flex-1 min-w-[8rem]">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-neutral-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" name="search" data-tour="search" value="{{ $search }}" placeholder="{{ $searchPlaceholder }}"
                   class="app-input app-input-sm pl-9">
        </div>

        @if($hasOptions)
            <div class="relative shrink-0">
                <button type="button" @click="filtersOpen = !filtersOpen" data-tour="filters"
                        class="app-btn app-btn-secondary app-btn-sm flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L14 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 018 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                    <span>Filters</span>
                    @if($activeFilters->isNotEmpty())
                        <span class="ml-0.5 rounded-full bg-[#0071E3] text-white text-[10px] font-bold px-1.5 leading-4">{{ $activeFilters->count() }}</span>
                    @endif
                    <svg class="w-3 h-3 transition-transform" :class="filtersOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="filtersOpen" x-cloak x-transition.opacity.duration.120ms
                     class="absolute right-0 z-40 mt-2 w-56 rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] shadow-xl p-3 space-y-2.5">
                    @foreach($filters as $filter)
                        <div>
                            <label class="app-label">{{ $filter['label'] ?? \Illuminate\Support\Str::headline($filter['name']) }}</label>
                            <select name="{{ $filter['name'] }}" class="app-select app-input-sm">
                                @foreach($filter['options'] as $value => $label)
                                    <option value="{{ $value }}" @selected((string) ($filter['selected'] ?? '') === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach

                    @if(count($sorts))
                        <div>
                            <label class="app-label">Sort</label>
                            <select x-model="sortSel" class="app-select app-input-sm">
                                <option value="~">Default</option>
                                @foreach($sorts as $s)
                                    <option value="{{ $s['column'] }}~{{ $s['direction'] ?? 'desc' }}">{{ $s['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <button type="submit" class="app-btn app-btn-primary app-btn-sm shrink-0">Apply</button>

        @if($hasActive)
            <a href="{{ $resetUrl ?? $action }}" class="app-btn app-btn-secondary app-btn-sm shrink-0">Reset</a>
        @endif
    </form>
</div>

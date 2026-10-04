@php
    $filters = $filters ?? [];
    $searchPlaceholder = $searchPlaceholder ?? 'Search...';
    $search = $search ?? null;
@endphp
<div class="app-card p-4">
    <form method="GET" action="{{ $action }}" class="flex flex-col md:flex-row md:items-center gap-3">
        <div class="relative flex-1 min-w-[220px]">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-neutral-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ $searchPlaceholder }}" class="app-input pl-10">
        </div>

        @foreach($filters as $filter)
            <select name="{{ $filter['name'] }}" class="app-select md:w-44">
                @foreach($filter['options'] as $value => $label)
                    <option value="{{ $value }}" @selected((string) ($filter['selected'] ?? '') === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
        @endforeach

        <div class="flex items-center gap-2 shrink-0">
            <button type="submit" class="app-btn app-btn-primary">Apply</button>
            <a href="{{ $resetUrl }}" class="app-btn app-btn-secondary">Reset</a>
        </div>
    </form>

    @if(request('sort'))
        <div class="mt-2.5 flex items-center gap-2 text-xs text-neutral-500">
            <span>Sorted by <strong class="font-semibold text-neutral-700 dark:text-neutral-200">{{ \Illuminate\Support\Str::headline(request('sort')) }}</strong> ({{ request('direction') === 'asc' ? 'ascending' : 'descending' }})</span>
            <a href="{{ request()->fullUrlWithQuery(['sort' => null, 'direction' => null]) }}" class="font-semibold text-[#0071E3] dark:text-[#0A84FF] hover:underline">Clear sort</a>
        </div>
    @endif
</div>

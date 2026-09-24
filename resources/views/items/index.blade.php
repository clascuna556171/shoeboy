@extends('layouts.app')

@section('title', 'Inventory Master Catalog')

@section('content')
<div class="space-y-6">

    {{-- Page header --}}
    <div class="app-card p-5 lg:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="min-w-0">
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Stock Catalog</div>
                <h1 class="text-xl md:text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-0.5">Footwear Inventory Units</h1>
                </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="badge badge-neutral">{{ $items->total() }} units</span>
        </div>
    </div>

    {{-- Filters --}}
    <div class="app-card p-4">
        <form method="GET" action="{{ route('items.index') }}" class="flex flex-col lg:flex-row lg:items-center gap-3">
            <div class="relative flex-1 min-w-[220px]">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-neutral-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search SKU, brand, or model..."
                       class="w-full pl-10 pr-4 py-2.5 bg-neutral-100 dark:bg-neutral-800/80 border border-neutral-200 dark:border-neutral-700 rounded-xl text-sm apple-focus-ring">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <select name="batch_id" onchange="this.form.submit()" class="px-3 py-2.5 bg-neutral-100 dark:bg-neutral-800/80 border border-neutral-200 dark:border-neutral-700 rounded-xl text-sm apple-focus-ring">
                    <option value="">All Batches</option>
                    @foreach($batches as $b)
                        <option value="{{ $b->id }}" {{ $selectedBatchId == $b->id ? 'selected' : '' }}>{{ $b->batch_code }}</option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()" class="px-3 py-2.5 bg-neutral-100 dark:bg-neutral-800/80 border border-neutral-200 dark:border-neutral-700 rounded-xl text-sm apple-focus-ring">
                    <option value="">All Statuses</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Reserved</option>
                    <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>Sold</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2.5 border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm rounded-xl shadow-sm transition-all">Apply</button>
                <a href="{{ route('items.index') }}" class="px-4 py-2.5 bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200/70 dark:hover:bg-neutral-700 rounded-xl font-semibold text-sm transition-colors">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="app-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <th class="py-3 px-4 font-semibold">SKU</th>
                        <th class="py-3 px-4 font-semibold">Batch</th>
                        <th class="py-3 px-4 font-semibold">Brand &amp; Model</th>
                        <th class="py-3 px-4 font-semibold">Size</th>
                        <th class="py-3 px-4 font-semibold">Condition</th>
                        <th class="py-3 px-4 font-semibold">Price Tier</th>
                        <th class="py-3 px-4 font-semibold text-right">Target Price</th>
                        <th class="py-3 px-4 font-semibold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    @forelse($items as $item)
                    <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3.5 px-4 font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">{{ $item->sku }}</td>
                        <td class="py-3.5 px-4 font-mono text-neutral-500">{{ $item->batch->batch_code }}</td>
                        <td class="py-3.5 px-4 font-semibold text-neutral-800 dark:text-neutral-200">{{ $item->brand }} {{ $item->model }}</td>
                        <td class="py-3.5 px-4 font-mono text-neutral-600 dark:text-neutral-300">{{ $item->size }}</td>
                        <td class="py-3.5 px-4 text-neutral-600 dark:text-neutral-300">{{ $item->condition }}</td>
                        <td class="py-3.5 px-4 text-neutral-500">{{ $item->price_tier }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-neutral-900 dark:text-white">₱{{ number_format($item->listed_price, 2) }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="badge badge-{{ $item->status }}">{{ strtoupper($item->status) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="app-empty">
                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h6m6-7l4 4m0 0l-4 4m4-4H10"/></svg>
                                <span class="text-xs font-medium">No inventory items match the current filters.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-neutral-800">
            {{ $items->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
@extends('layouts.app')

@section('title', 'Inventory Master Catalog')

@section('content')
<div class="space-y-6">


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Stock Catalog</span>
                <span class="text-xs text-neutral-400">Serialized Item Management</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-[#1D1D1F] dark:text-white mt-1">Footwear Inventory Units</h1>
            <p class="text-xs text-neutral-500 mt-0.5">Filter by batch and status; view price tiers, condition grades, and individual unit specs.</p>
        </div>
    </div>


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-3 text-xs">
        <form method="GET" action="{{ route('items.index') }}" class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <div class="flex items-center gap-1.5">
                <span class="text-neutral-400">Batch:</span>
                <select name="batch_id" onchange="this.form.submit()" class="px-2.5 py-1.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                    <option value="">All Batches</option>
                    @foreach($batches as $b)
                        <option value="{{ $b->id }}" {{ $selectedBatchId == $b->id ? 'selected' : '' }}>{{ $b->batch_code }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-neutral-400">Status:</span>
                <select name="status" onchange="this.form.submit()" class="px-2.5 py-1.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                    <option value="">All Statuses</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Reserved</option>
                    <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>Sold</option>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search SKU, brand, or model..." class="w-full px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
            </div>

            <button type="submit" class="px-3 py-1.5 bg-[#0071E3] text-white font-medium rounded-xl">Filter</button>
            <a href="{{ route('items.index') }}" class="px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 rounded-xl">Reset</a>
        </form>
    </div>


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-[11px] text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800 pb-2">
                    <tr>
                        <th class="py-2.5 px-3">SKU</th>
                        <th class="py-2.5 px-3">Batch</th>
                        <th class="py-2.5 px-3">Brand & Model</th>
                        <th class="py-2.5 px-3">Size</th>
                        <th class="py-2.5 px-3">Condition</th>
                        <th class="py-2.5 px-3">Price Tier</th>
                        <th class="py-2.5 px-3 text-right">Target Price</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60 font-sans">
                    @forelse($items as $item)
                    <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3 px-3 font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">{{ $item->sku }}</td>
                        <td class="py-3 px-3 font-mono text-neutral-500">{{ $item->batch->batch_code }}</td>
                        <td class="py-3 px-3 font-semibold text-neutral-800 dark:text-neutral-200">{{ $item->brand }} {{ $item->model }}</td>
                        <td class="py-3 px-3 font-mono">{{ $item->size }}</td>
                        <td class="py-3 px-3">{{ $item->condition }}</td>
                        <td class="py-3 px-3 text-neutral-500">{{ $item->price_tier }}</td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-neutral-900 dark:text-white">₱{{ number_format($item->listed_price, 2) }}</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold badge-{{ $item->status }}">
                                {{ strtoupper($item->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-neutral-400">No inventory items match the current filters.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800">
            {{ $items->links() }}
        </div>
    </div>

</div>
@endsection

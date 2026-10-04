@extends('layouts.app')

@section('title', 'Inventory Master Catalog')

@section('content')
<div class="space-y-6" x-data="{ editItem: null }">

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

    @include('partials.table-toolbar', [
        'action' => route('items.index'),
        'search' => request('search'),
        'searchPlaceholder' => 'Search SKU, brand, or model...',
        'resetUrl' => route('items.index'),
        'filters' => [
            ['name' => 'batch_id', 'selected' => $selectedBatchId, 'options' => ['' => 'All Batches'] + $batches->pluck('batch_code', 'id')->all()],
            ['name' => 'status', 'selected' => request('status'), 'options' => ['' => 'All Statuses', 'available' => 'Available', 'reserved' => 'Reserved', 'sold' => 'Sold']],
        ],
    ])

    {{-- Table --}}
    <div class="app-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        @include('partials.sortable-th', ['column' => 'sku', 'label' => 'SKU'])
                        <th class="py-3 px-4 font-semibold">Batch</th>
                        @include('partials.sortable-th', ['column' => 'brand', 'label' => 'Brand & Model'])
                        @include('partials.sortable-th', ['column' => 'size', 'label' => 'Size'])
                        @include('partials.sortable-th', ['column' => 'condition', 'label' => 'Condition'])
                        <th class="py-3 px-4 font-semibold">Price Tier</th>
                        @include('partials.sortable-th', ['column' => 'listed_price', 'label' => 'Target Price', 'align' => 'right'])
                        @include('partials.sortable-th', ['column' => 'status', 'label' => 'Status', 'align' => 'center'])
                        <th class="py-3 px-4 font-semibold text-right">Action</th>
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
                        <td class="py-3.5 px-4 text-right">
                            @if($item->status === 'available')
                                <button type="button"
                                        @click="editItem = {{ Js::from([
                                            'id' => $item->id,
                                            'sku' => $item->sku,
                                            'brand' => $item->brand,
                                            'model' => $item->model,
                                            'size' => $item->size,
                                            'condition' => $item->condition,
                                            'listed_price' => $item->listed_price,
                                            'repair_cost' => $item->repair_cost,
                                            'status' => $item->status,
                                            'category' => $item->category,
                                        ]) }}"
                                        class="inline-flex items-center gap-1 min-h-8 px-3 py-1.5 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] text-neutral-700 dark:text-neutral-200 text-xs font-semibold hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </button>
                            @else
                                <span class="text-xs text-neutral-500">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
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

    {{-- Edit item modal (only available pairs are editable) --}}
    <x-modal eyebrow="Edit Pair" size="lg" accent="emerald" close="editItem = null"
             x-show="editItem" x-cloak @keydown.escape.window="editItem = null">
        <x-slot:heading><span x-text="editItem ? editItem.sku : ''"></span></x-slot:heading>
            <form :action="editItem ? '/items/' + editItem.id : '#'" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Brand</label>
                        <input type="text" name="brand" :value="editItem?.brand" class="app-input">
                    </div>
                    <div>
                        <label class="app-label">Model</label>
                        <input type="text" name="model" :value="editItem?.model" class="app-input">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Size</label>
                        <input type="text" name="size" :value="editItem?.size" required class="app-input font-mono">
                    </div>
                    <div>
                        <label class="app-label">Condition</label>
                        <input type="text" name="condition" :value="editItem?.condition" required class="app-input">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Target Price (₱)</label>
                        <input type="number" step="0.01" name="listed_price" :value="editItem?.listed_price" required class="app-input font-mono">
                        <p class="mt-1 text-[11px] text-neutral-500">Tier auto-updates from this price.</p>
                    </div>
                    <div>
                        <label class="app-label">Repair Cost (₱)</label>
                        <input type="number" step="0.01" name="repair_cost" :value="editItem?.repair_cost" class="app-input font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Status</label>
                        <select name="status" class="app-select">
                            <option value="available">Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="sold">Sold</option>
                        </select>
                    </div>
                    <div>
                        <label class="app-label">Category</label>
                        <input type="text" name="category" :value="editItem?.category" class="app-input">
                    </div>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="button" @click="editItem = null" class="app-btn app-btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="app-btn app-btn-emerald flex-1">Save changes</button>
                </div>
            </form>
    </x-modal>

</div>
@endsection
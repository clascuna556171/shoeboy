@extends('layouts.app')

@section('title', 'Inventory Master Catalog')

@section('content')
<div class="space-y-6" x-data="{ editItem: null, showAddItem: false }">

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
            <button type="button" @click="showAddItem = true"
                    class="px-5 py-2.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm shadow-sm flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Pair</span>
            </button>
        </div>
    </div>

    <x-table-toolbar :action="route('items.index')" :search="request('search')"
                     search-placeholder="Search SKU, brand, or model..."
                     :reset-url="route('items.index')"
                     :filters="[
                         ['name' => 'batch_id', 'selected' => $selectedBatchId, 'options' => ['' => 'All Batches'] + $batches->pluck('batch_code', 'id')->all()],
                         ['name' => 'status', 'selected' => request('status'), 'options' => ['' => 'All Statuses', 'available' => 'Available', 'reserved' => 'Reserved', 'sold' => 'Sold']],
                     ]" />

    {{-- Table --}}
    <div class="app-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <x-sort-th-server column="sku" label="SKU" />
                        <th class="py-3 px-4 font-semibold">Batch</th>
                        <x-sort-th-server column="brand" label="Brand & Model" />
                        <x-sort-th-server column="size" label="Size" />
                        <x-sort-th-server column="condition" label="Condition" />
                        <th class="py-3 px-4 font-semibold">Price Tier</th>
                        <x-sort-th-server column="listed_price" label="Target Price" align="right" />
                        <x-sort-th-server column="status" label="Status" align="center" />
                        <th class="py-3 px-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    @forelse($items as $item)
                    <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3.5 px-4 font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">{{ $item->sku }}</td>
                        <td class="py-3.5 px-4"><x-batch-chip :batch="$item->batch" /></td>
                        <td class="py-3.5 px-4 font-semibold text-neutral-800 dark:text-neutral-200">{{ $item->brand }} {{ $item->model }}</td>
                        <td class="py-3.5 px-4 font-mono text-neutral-600 dark:text-neutral-300">{{ $item->size }}</td>
                        <td class="py-3.5 px-4 text-neutral-600 dark:text-neutral-300">{{ $item->condition }}</td>
                        <td class="py-3.5 px-4 text-neutral-500">{{ $item->price_tier }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-neutral-900 dark:text-white"><x-money :value="$item->listed_price" /></td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="badge badge-{{ $item->status }}">{{ strtoupper($item->status) }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($item->status === 'available')
                                <x-action-btn icon="edit" tone="secondary"
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
                                              ]) }}">Edit</x-action-btn>
                            @elseif($item->status === 'sold' && ($soldOrder = $item->orders->whereIn('status', ['paid', 'fulfilled'])->sortByDesc('date_awarded')->first()))
                                <div x-data="{ open: false }" class="inline-block">
                                    <x-action-btn icon="eye" tone="secondary" @click="open = true">View</x-action-btn>
                                    <x-order-modal :order="$soldOrder" x-show="open" close="open = false" />
                                </div>
                            @elseif($item->status === 'reserved' && ($resOrder = $item->orders->where('status', 'reserved')->sortByDesc('date_awarded')->first()))
                                <div x-data="{ open: false }" class="inline-block">
                                    <x-action-btn icon="eye" tone="secondary" @click="open = true">View</x-action-btn>
                                    <x-order-modal :order="$resOrder" x-show="open" close="open = false" />
                                </div>
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

    {{-- Add pair modal (pick a batch) --}}
    <x-modal title="Add Pair to Inventory" accent="emerald" size="lg" close="showAddItem = false"
             x-show="showAddItem" x-cloak @keydown.escape.window="showAddItem = false">
        <form action="{{ route('items.store') }}" method="POST" class="space-y-3.5 text-sm">
            @csrf
            <x-add-pair-fields :batches="$batches" />
            <div class="flex gap-2 pt-4">
                <button type="button" @click="showAddItem = false" class="app-btn app-btn-secondary flex-1">Cancel</button>
                <button type="submit" class="app-btn app-btn-emerald flex-1">Save Pair</button>
            </div>
        </form>
    </x-modal>

    {{-- Edit item modal (only available pairs are editable) --}}
    <x-modal eyebrow="Edit Pair" size="lg" accent="emerald" close="editItem = null"
             x-show="editItem" x-cloak @keydown.escape.window="editItem = null">
        <x-slot:heading><span x-text="editItem ? editItem.sku : ''"></span></x-slot:heading>
            <form :action="editItem ? '/items/' + editItem.id : '#'" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Brand <span class="app-req">*</span></label>
                        <input type="text" name="brand" :value="editItem?.brand" required class="app-input">
                    </div>
                    <div>
                        <label class="app-label">Model <span class="app-req">*</span></label>
                        <input type="text" name="model" :value="editItem?.model" required class="app-input">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Size <span class="app-req">*</span></label>
                        <input type="text" name="size" :value="editItem?.size" required class="app-input font-mono">
                    </div>
                    <div>
                        <label class="app-label">Condition <span class="app-req">*</span></label>
                        <input type="text" name="condition" :value="editItem?.condition" required class="app-input">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Target Price (₱) <span class="app-req">*</span></label>
                        <input type="number" step="0.01" name="listed_price" :value="editItem?.listed_price" required class="app-input font-mono">
                        <p class="mt-1 text-[11px] text-neutral-500">Tier auto-updates from this price.</p>
                    </div>
                    <div>
                        <label class="app-label">Repair Cost (₱) <span class="app-optional">(optional)</span></label>
                        <input type="number" step="0.01" name="repair_cost" :value="editItem?.repair_cost" class="app-input font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Status <span class="app-req">*</span></label>
                        <select name="status" required class="app-select">
                            <option value="available">Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="sold">Sold</option>
                        </select>
                    </div>
                    <div>
                        <label class="app-label">Category <span class="app-optional">(optional)</span></label>
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
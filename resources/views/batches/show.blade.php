@extends('layouts.app')

@section('title', "Batch {$batch->batch_code}")

@section('content')
<div class="space-y-6" x-data="{ showAddItemModal: false }">

    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('batches.index') }}" class="text-xs text-[#0071E3] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>All Batches</span>
                </a>
                <span class="text-neutral-400">•</span>
                <span class="font-mono text-xs text-neutral-500">Acquired {{ $batch->date_acquired->format('M d, Y') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-[#1D1D1F] dark:text-white mt-1">Batch {{ $batch->batch_code }} — {{ $batch->supplier->name }}</h1>
            <p class="text-xs text-neutral-500 mt-0.5">Wholesale Outlay: ₱{{ number_format($batch->total_cost, 2) }} • Allocated Baseline Unit Cost: <strong>₱{{ number_format($batch->average_item_cost, 2) }}</strong></p>
        </div>

        <button type="button"
                @click="showAddItemModal = true"
                class="px-5 py-2.5 rounded-2xl bg-[#0071E3] hover:bg-[#0077ED] text-white font-semibold text-xs shadow-sm flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add Pair to Batch</span>
        </button>
    </div>

    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
            <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Serialized Pairs ({{ $items->total() }})</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-[11px] text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800 pb-2">
                    <tr>
                        <th class="py-2.5 px-3">SKU</th>
                        <th class="py-2.5 px-3">Brand & Model</th>
                        <th class="py-2.5 px-3">Size</th>
                        <th class="py-2.5 px-3">Condition</th>
                        <th class="py-2.5 px-3 text-right">Base Cost</th>
                        <th class="py-2.5 px-3 text-right">Repair Cost</th>
                        <th class="py-2.5 px-3 text-right">Listed Price</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60 font-sans">
                    @forelse($items as $item)
                    <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3 px-3 font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">{{ $item->sku }}</td>
                        <td class="py-3 px-3 font-semibold text-neutral-800 dark:text-neutral-200">{{ $item->brand }} {{ $item->model }}</td>
                        <td class="py-3 px-3 font-mono">{{ $item->size }}</td>
                        <td class="py-3 px-3">{{ $item->condition }}</td>
                        <td class="py-3 px-3 text-right font-mono text-neutral-500">₱{{ number_format($batch->average_item_cost, 2) }}</td>
                        <td class="py-3 px-3 text-right font-mono text-neutral-500">₱{{ number_format($item->repair_cost, 2) }}</td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-neutral-900 dark:text-white">₱{{ number_format($item->listed_price, 2) }}</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold badge-{{ $item->status }}">
                                {{ strtoupper($item->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-neutral-400">No shoes added to this batch yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800">
            {{ $items->links() }}
        </div>
    </div>

    <div x-show="showAddItemModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
                <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Add Pair to Batch {{ $batch->batch_code }}</h3>
                <button @click="showAddItemModal = false" class="text-neutral-400 hover:text-neutral-600 text-sm">✕</button>
            </div>

            <form action="{{ route('items.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $batch->id }}">

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Brand:</label>
                        <input type="text" name="brand" required placeholder="Li-Ning, Nike..." class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Model Name:</label>
                        <input type="text" name="model" required placeholder="Way of Wade 10..." class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Size:</label>
                        <input type="text" name="size" required placeholder="US 10.5" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border rounded-xl font-mono apple-focus-ring">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Condition:</label>
                        <select name="condition" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                            <option value="Pristine">Pristine</option>
                            <option value="Good" selected>Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Needs Repair">Needs Repair</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Status:</label>
                        <select name="status" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                            <option value="available" selected>Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="sold">Sold</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Price Tier:</label>
                        <select name="price_tier" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                            <option value="Tier 1 (High Value)">Tier 1 (High Value: >₱4000)</option>
                            <option value="Tier 2 (Mid Range)">Tier 2 (Mid Range: ₱2500–₱3900)</option>
                            <option value="Tier 3 (Budget/Fair)">Tier 3 (Budget/Fair: <₱2500)</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Target Listed Price (₱):</label>
                        <input type="number" step="0.01" name="listed_price" required placeholder="3500.00" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border rounded-xl font-mono apple-focus-ring">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Custom SKU (Optional):</label>
                        <input type="text" name="sku" placeholder="Auto-generated if empty" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border rounded-xl font-mono uppercase apple-focus-ring">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Repair Cost (₱):</label>
                        <input type="number" step="0.01" name="repair_cost" value="0.00" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border rounded-xl font-mono apple-focus-ring">
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="showAddItemModal = false" class="flex-1 py-2.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-semibold">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-[#0071E3] hover:bg-[#0077ED] text-white font-semibold">Save Pair</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

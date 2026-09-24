@extends('layouts.app')

@section('title', "Batch {$batch->batch_code}")

@section('content')
<div class="space-y-6" x-data="{ showAddItemModal: false }">

    {{-- Page header --}}
    <div class="app-card p-5 lg:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('batches.index') }}" class="text-xs text-[#0071E3] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <span>All Batches</span>
            </a>
            <h1 class="text-xl md:text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-1.5">
                Batch <span class="font-mono">{{ $batch->batch_code }}</span>
                <span class="text-neutral-500 font-normal">— {{ $batch->supplier->name }}</span>
            </h1>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-neutral-500 mt-1.5">
                <span class="font-mono">Acquired {{ $batch->date_acquired->format('M d, Y') }}</span>
                <span class="hidden sm:inline text-neutral-300 dark:text-neutral-600">•</span>
                <span>Outlay <strong class="font-mono text-neutral-700 dark:text-neutral-300">₱{{ number_format($batch->total_cost, 2) }}</strong></span>
                <span class="hidden sm:inline text-neutral-300 dark:text-neutral-600">•</span>
                <span>Base Unit Cost <strong class="font-mono text-emerald-600 dark:text-emerald-400">₱{{ number_format($batch->average_item_cost, 2) }}</strong></span>
            </div>
        </div>

        <button type="button"
                @click="showAddItemModal = true"
                class="px-5 py-2.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm shadow-sm flex items-center gap-2 transition-all shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add Pair to Batch</span>
        </button>
    </div>

    {{-- Pairs table --}}
    <div class="app-card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-neutral-200 dark:border-neutral-800">
            <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white">Serialized Pairs</h3>
            <span class="badge badge-neutral">{{ $items->total() }} pairs</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <th class="py-3 px-4 font-semibold">SKU</th>
                        <th class="py-3 px-4 font-semibold">Brand &amp; Model</th>
                        <th class="py-3 px-4 font-semibold">Size</th>
                        <th class="py-3 px-4 font-semibold">Condition</th>
                        <th class="py-3 px-4 font-semibold text-right">Base Cost</th>
                        <th class="py-3 px-4 font-semibold text-right">Repair</th>
                        <th class="py-3 px-4 font-semibold text-right">Listed Price</th>
                        <th class="py-3 px-4 font-semibold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    @forelse($items as $item)
                    <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3.5 px-4 font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">{{ $item->sku }}</td>
                        <td class="py-3.5 px-4 font-semibold text-neutral-800 dark:text-neutral-200">{{ $item->brand }} {{ $item->model }}</td>
                        <td class="py-3.5 px-4 font-mono text-neutral-600 dark:text-neutral-300">{{ $item->size }}</td>
                        <td class="py-3.5 px-4 text-neutral-600 dark:text-neutral-300">{{ $item->condition }}</td>
                        <td class="py-3.5 px-4 text-right font-mono text-neutral-500">₱{{ number_format($batch->average_item_cost, 2) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono text-neutral-500">₱{{ number_format($item->repair_cost, 2) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-neutral-900 dark:text-white">₱{{ number_format($item->listed_price, 2) }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="badge badge-{{ $item->status }}">{{ strtoupper($item->status) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="app-empty">
                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span class="text-xs font-medium">No shoes added to this batch yet.</span>
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

    {{-- Add pair modal --}}
    <div x-show="showAddItemModal"
         x-cloak
         class="app-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="app-modal-panel bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
                <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white">Add Pair to Batch {{ $batch->batch_code }}</h3>
                <button @click="showAddItemModal = false" class="text-neutral-500 hover:text-neutral-600 text-sm">✕</button>
            </div>

            <form action="{{ route('items.store') }}" method="POST" class="space-y-3.5 text-sm">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $batch->id }}">

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Brand:</label>
                        <input type="text" name="brand" required placeholder="Li-Ning, Nike..." class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Model Name:</label>
                        <input type="text" name="model" required placeholder="Way of Wade 10..." class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Size:</label>
                        <input type="text" name="size" required placeholder="US 10.5" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl font-mono apple-focus-ring">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Condition:</label>
                        <select name="condition" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                            <option value="Pristine">Pristine</option>
                            <option value="Good" selected>Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Needs Repair">Needs Repair</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Status:</label>
                        <select name="status" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                            <option value="available" selected>Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="sold">Sold</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Price Tier:</label>
                        <select name="price_tier" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                            <option value="Tier 1 (High Value)">Tier 1 (High Value: >₱4000)</option>
                            <option value="Tier 2 (Mid Range)">Tier 2 (Mid Range: ₱2500–₱3900)</option>
                            <option value="Tier 3 (Budget/Fair)">Tier 3 (Budget/Fair: <₱2500)</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Target Listed Price (₱):</label>
                        <input type="number" step="0.01" name="listed_price" required placeholder="3500.00" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl font-mono apple-focus-ring">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Custom SKU (Optional):</label>
                        <input type="text" name="sku" placeholder="Auto-generated if empty" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl font-mono uppercase apple-focus-ring">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Repair Cost (₱):</label>
                        <input type="number" step="0.01" name="repair_cost" value="0.00" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl font-mono apple-focus-ring">
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="showAddItemModal = false" class="flex-1 py-2.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-semibold">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold">Save Pair</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
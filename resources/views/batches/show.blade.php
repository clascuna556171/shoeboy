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
                <span class="hidden sm:inline text-neutral-300 dark:text-neutral-600">•</span>
                <span>{{ $batch->total_sacks }} sack(s)</span>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('items.index', ['batch_id' => $batch->id]) }}"
               class="px-5 py-2.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm shadow-sm flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h10"/></svg>
                <span>Manage Pairs in Inventory</span>
            </a>
            <button type="button"
                    @click="showAddItemModal = true"
                    class="px-5 py-2.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm shadow-sm flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Pair</span>
            </button>
        </div>
    </div>

    {{-- Batch summary hub --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="app-card p-5">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Stock Logged</div>
            <div class="mt-1 font-mono text-2xl font-bold text-[#1D1D1F] dark:text-white">{{ $stats['logged'] }}<span class="text-neutral-400 text-lg"> / {{ $stats['total_pairs'] }}</span></div>
            <p class="text-xs text-neutral-500 mt-1">Pairs serialized into this batch.</p>
        </div>
        <div class="app-card p-5">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Available</div>
            <div class="mt-1 font-mono text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $stats['available'] }}</div>
            <p class="text-xs text-neutral-500 mt-1">Ready to sell or claim.</p>
        </div>
        <div class="app-card p-5">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Reserved</div>
            <div class="mt-1 font-mono text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $stats['reserved'] }}</div>
            <p class="text-xs text-neutral-500 mt-1">Held by active claims.</p>
        </div>
        <div class="app-card p-5">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Sold</div>
            <div class="mt-1 font-mono text-2xl font-bold text-neutral-800 dark:text-neutral-200">{{ $stats['sold'] }}</div>
            <p class="text-xs text-neutral-500 mt-1">Settled and paid out.</p>
        </div>
    </div>

    {{-- Manage prompt --}}
    <div class="app-card p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
            <div class="text-sm font-semibold text-neutral-900 dark:text-white">Editing &amp; triage happen in Inventory</div>
            <p class="text-xs text-neutral-500 mt-0.5">The Inventory page is the single place to edit pair details, grade conditions, and manage status for this batch.</p>
        </div>
        <a href="{{ route('items.index', ['batch_id' => $batch->id]) }}"
           class="px-4 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm shadow-sm transition-all shrink-0">
            Open Inventory for {{ $batch->batch_code }}
        </a>
    </div>

    {{-- Add pair modal --}}
    <x-modal title="Add Pair to Batch {{ $batch->batch_code }}" accent="amber" close="showAddItemModal = false"
             x-show="showAddItemModal" x-cloak @keydown.escape.window="showAddItemModal = false">
            <form action="{{ route('items.store') }}" method="POST" class="space-y-3.5 text-sm">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $batch->id }}">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Brand</label>
                        <input type="text" name="brand" required placeholder="Li-Ning, Nike..." class="app-input">
                    </div>
                    <div>
                        <label class="app-label">Model Name</label>
                        <input type="text" name="model" required placeholder="Way of Wade 10..." class="app-input">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="app-label">Size</label>
                        <input type="text" name="size" required placeholder="US 10.5" class="app-input font-mono">
                    </div>
                    <div>
                        <label class="app-label">Condition</label>
                        <select name="condition" class="app-select">
                            <option value="Pristine">Pristine</option>
                            <option value="Good" selected>Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Needs Repair">Needs Repair</option>
                        </select>
                    </div>
                    <div>
                        <label class="app-label">Status</label>
                        <select name="status" class="app-select">
                            <option value="available" selected>Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="sold">Sold</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Target Listed Price (₱)</label>
                        <input type="number" step="0.01" name="listed_price" required placeholder="3500.00" class="app-input font-mono">
                        <p class="mt-1 text-[11px] text-neutral-500">Tier auto-assigns — T1 &lt; ₱1k · T2 ₱1k–2k · T3 ₱2k+</p>
                    </div>
                    <div>
                        <label class="app-label">Repair Cost (₱)</label>
                        <input type="number" step="0.01" name="repair_cost" value="0.00" class="app-input font-mono">
                    </div>
                </div>

                <div>
                    <label class="app-label">Custom SKU (optional)</label>
                    <input type="text" name="sku" placeholder="Auto-generated if empty" class="app-input font-mono uppercase">
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="button" @click="showAddItemModal = false" class="app-btn app-btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="app-btn app-btn-amber flex-1">Save Pair</button>
                </div>
            </form>
    </x-modal>

</div>
@endsection

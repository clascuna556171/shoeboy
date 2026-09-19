@extends('layouts.app')

@section('title', 'Batch Intake & Registry')

@section('content')
<div class="space-y-6" x-data="{ showIntakeModal: false }">

    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">Inventory Intake Engine</span>
                <span class="text-xs text-neutral-400">Batch-to-Unit Serialization</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-[#1D1D1F] dark:text-white mt-1">Footwear Batch Intakes</h1>
            <p class="text-xs text-neutral-500 mt-0.5">Record incoming footwear sacks/bales, wholesale costs, and compute baseline unit allocation.</p>
        </div>

        <button type="button"
                @click="showIntakeModal = true"
                class="px-5 py-2.5 rounded-2xl bg-[#0071E3] hover:bg-[#0077ED] text-white font-semibold text-xs shadow-sm flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Record New Batch Intake</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($batches as $batch)
        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between gap-5 group">
            
            <div class="space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-bold text-[#0071E3] dark:text-[#0A84FF] font-mono">BATCH</span>
                        <h3 class="text-xl font-bold text-[#1D1D1F] dark:text-white">{{ $batch->batch_code }}</h3>
                    </div>
                    <span class="text-xs font-mono text-neutral-400">{{ $batch->date_acquired->format('M d, Y') }}</span>
                </div>

                <div class="text-xs text-neutral-600 dark:text-neutral-400">
                    Supplier: <strong class="text-neutral-800 dark:text-neutral-200">{{ $batch->supplier->name }}</strong>
                </div>

                <div class="grid grid-cols-2 gap-2 p-3 rounded-2xl bg-neutral-100/70 dark:bg-neutral-800/60 text-xs">
                    <div>
                        <span class="text-[10px] text-neutral-400 block uppercase font-medium">Bale Intake Outlay:</span>
                        <span class="font-mono font-bold text-neutral-800 dark:text-neutral-200">₱{{ number_format($batch->total_cost, 2) }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-neutral-400 block uppercase font-medium">Allocated Base Cost:</span>
                        <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">₱{{ number_format($batch->average_item_cost, 2) }}</span>
                    </div>
                </div>

                <div class="space-y-1.5 pt-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-neutral-500">Stock Status:</span>
                        <span class="font-mono text-neutral-800 dark:text-neutral-200">{{ $batch->items->count() }} / {{ $batch->total_pairs }} pairs logged</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-mono">
                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 text-[11px] font-semibold">{{ $batch->available_count }} Available</span>
                        <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 text-[11px] font-semibold">{{ $batch->reserved_count }} Reserved</span>
                        <span class="px-2 py-0.5 rounded-md bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 text-[11px] font-semibold">{{ $batch->sold_count }} Sold</span>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
                <span class="text-xs text-neutral-400 font-mono">{{ $batch->total_sacks }} sack(s)</span>
                <a href="{{ route('batches.show', $batch->id) }}" class="px-3.5 py-1.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 group-hover:bg-[#0071E3] group-hover:text-white text-neutral-700 dark:text-neutral-200 text-xs font-semibold transition-all">
                    View Pairs
                </a>
            </div>

        </div>
        @empty
        <div class="col-span-3 text-center py-16 text-neutral-400 text-xs">No batches recorded yet.</div>
        @endforelse
    </div>

    <div x-show="showIntakeModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         x-data="{
             cost: 18000,
             pairs: 24,
             get avg() {
                 const p = Math.max(1, parseInt(this.pairs) || 1);
                 const c = parseFloat(this.cost) || 0;
                 return (c / p).toFixed(2);
             }
         }">
        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Record New Batch Intake</h3>
                </div>
                <button @click="showIntakeModal = false" class="text-neutral-400 hover:text-neutral-600 text-sm">✕</button>
            </div>

            <form action="{{ route('batches.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Supplier Record:</label>
                    <select name="supplier_id" required class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Batch Short Code:</label>
                        <input type="text" name="batch_code" required placeholder="e.g. B06" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl font-mono uppercase apple-focus-ring">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Date Acquired:</label>
                        <input type="date" name="date_acquired" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Total Sacks / Bales:</label>
                        <input type="number" name="total_sacks" required min="1" value="1" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl font-mono apple-focus-ring">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Total Footwear Pairs:</label>
                        <input type="number" name="total_pairs" x-model="pairs" required min="1" value="24" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl font-mono apple-focus-ring">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Total Batch Cost (PHP):</label>
                    <input type="number" step="0.01" name="total_cost" x-model="cost" required min="0" value="18000" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl font-mono text-sm apple-focus-ring">
                </div>

                <div class="p-3 rounded-2xl bg-neutral-100 dark:bg-neutral-800/70 text-xs flex items-center justify-between">
                    <span class="text-neutral-500">Auto-Computed Base Unit Cost:</span>
                    <span class="font-mono font-bold text-sm text-[#0071E3] dark:text-[#0A84FF]" x-text="'₱' + avg"></span>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="showIntakeModal = false" class="flex-1 py-2.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-semibold">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-[#0071E3] hover:bg-[#0077ED] text-white font-semibold">Save Batch</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

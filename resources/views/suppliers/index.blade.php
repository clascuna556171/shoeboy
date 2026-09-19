@extends('layouts.app')

@section('title', 'Supplier Records')

@section('content')
<div class="space-y-6" x-data="{ showSupplierModal: false }">


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300">Supplier Registry</span>
                <span class="text-xs text-neutral-400">Wholesale Sourcing Partners</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-[#1D1D1F] dark:text-white mt-1">Wholesale Bale Suppliers</h1>
            <p class="text-xs text-neutral-500 mt-0.5">Manage contacts and terms for bale importers (Suntop, Cebu Port, CAQ, Geety).</p>
        </div>

        <button type="button"
                @click="showSupplierModal = true"
                class="px-5 py-2.5 rounded-2xl bg-[#0071E3] hover:bg-[#0077ED] text-white font-semibold text-xs shadow-sm flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add New Supplier</span>
        </button>
    </div>


    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($suppliers as $sup)
        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm flex flex-col justify-between gap-4">
            <div class="space-y-2">
                <div class="flex items-start justify-between">
                    <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white">{{ $sup->name }}</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 font-mono">
                        {{ $sup->batches_count }} batch(es)
                    </span>
                </div>
                <div class="text-xs text-neutral-500 font-mono">
                    Contact: <strong class="text-neutral-800 dark:text-neutral-200">{{ $sup->contact_number ?? 'None provided' }}</strong>
                </div>
                @if($sup->notes)
                <p class="text-xs text-neutral-400 bg-neutral-50 dark:bg-neutral-800/40 p-3 rounded-xl border border-neutral-100 dark:border-neutral-800">
                    {{ $sup->notes }}
                </p>
                @endif
            </div>

            <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-end gap-2 text-xs">
                <form action="{{ route('suppliers.destroy', $sup->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Delete this supplier record?')" class="text-rose-500 hover:underline">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-16 text-neutral-400 text-xs">No suppliers added yet.</div>
        @endforelse
    </div>


    <div x-show="showSupplierModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
                <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Add Supplier Record</h3>
                <button @click="showSupplierModal = false" class="text-neutral-400 hover:text-neutral-600 text-sm">✕</button>
            </div>

            <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                
                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Supplier Name:</label>
                    <input type="text" name="name" required placeholder="e.g. Suntop Bales Warehouse" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                </div>

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Contact Number (Optional, Numeric):</label>
                    <input type="text" name="contact_number" placeholder="09171234567" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                </div>

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Notes & Inspection Terms:</label>
                    <textarea name="notes" rows="3" placeholder="Inspection terms, return policy, bale quality notes..." class="w-full px-3.5 py-2 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring"></textarea>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="showSupplierModal = false" class="flex-1 py-2.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-semibold">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-[#0071E3] hover:bg-[#0077ED] text-white font-semibold">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@extends('layouts.app')

@section('title', 'Supplier Records')

@section('content')
<div class="space-y-6" x-data="{ showSupplierModal: false, view: 'cards' }">

    {{-- Page header --}}
    <div class="app-card p-5 lg:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="min-w-0">
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Supplier Registry</div>
                <h1 class="text-xl md:text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-0.5">Wholesale Bale Suppliers</h1>
                </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center bg-neutral-100 dark:bg-neutral-800 p-1 rounded-xl">
                <button type="button" @click="view = 'cards'"
                        :class="view === 'cards' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-semibold text-neutral-900 dark:text-white' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
                        class="px-3 py-1.5 rounded-lg text-sm transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Cards
                </button>
                <button type="button" @click="view = 'table'"
                        :class="view === 'table' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-semibold text-neutral-900 dark:text-white' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
                        class="px-3 py-1.5 rounded-lg text-sm transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    Table
                </button>
            </div>

            <button type="button"
                    @click="showSupplierModal = true"
                    class="px-5 py-2.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm shadow-sm flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add New Supplier</span>
            </button>
        </div>
    </div>

    {{-- Cards --}}
    <div x-show="view === 'cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($suppliers as $sup)
        <div class="app-card app-card-hover p-5 flex flex-col justify-between gap-4">
            <div class="space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-10 h-10 rounded-xl bg-teal-600 dark:bg-teal-500 text-white flex items-center justify-center shrink-0 font-bold">
                            {{ strtoupper(substr($sup->name, 0, 1)) }}
                        </span>
                        <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white truncate">{{ $sup->name }}</h3>
                    </div>
                    <span class="badge badge-neutral shrink-0">{{ $sup->batches_count }} batch(es)</span>
                </div>

                <div class="text-base text-neutral-500 font-mono flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <strong class="text-neutral-800 dark:text-neutral-200">{{ $sup->contact_number ?? 'None provided' }}</strong>
                </div>

                @if($sup->notes)
                    <p class="text-sm leading-6 text-neutral-600 dark:text-neutral-400">{{ $sup->notes }}</p>
                @endif
            </div>

            <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-end gap-2 text-xs">
                <form action="{{ route('suppliers.destroy', $sup->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Delete this supplier record?')"
                            class="px-2.5 py-1 rounded-lg font-semibold text-neutral-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full app-card">
            <div class="app-empty">
                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5"/></svg>
                <span class="text-xs font-medium">No suppliers added yet.</span>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Table view --}}
    <div x-show="view === 'table'" x-cloak class="app-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <th class="py-3 px-4 font-semibold">Supplier</th>
                        <th class="py-3 px-4 font-semibold">Contact Number</th>
                        <th class="py-3 px-4 text-center font-semibold">Batches</th>
                        <th class="py-3 px-4 font-semibold">Notes</th>
                        <th class="py-3 px-4 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    @forelse($suppliers as $sup)
                    <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg bg-teal-600 dark:bg-teal-500 text-white flex items-center justify-center shrink-0 font-bold text-xs">{{ strtoupper(substr($sup->name, 0, 1)) }}</span>
                                <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $sup->name }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-neutral-600 dark:text-neutral-300">{{ $sup->contact_number ?? '—' }}</td>
                        <td class="py-3.5 px-4 text-center"><span class="badge badge-neutral">{{ $sup->batches_count }}</span></td>
                        <td class="py-3.5 px-4"><span class="block max-w-[260px] truncate text-neutral-500 dark:text-neutral-400">{{ $sup->notes ?? '—' }}</span></td>
                        <td class="py-3.5 px-4 text-right">
                            <form action="{{ route('suppliers.destroy', $sup->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this supplier record?')"
                                        class="px-2.5 py-1.5 rounded-lg font-semibold text-neutral-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="app-empty">
                                <span class="text-xs font-medium">No suppliers added yet.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <div x-show="showSupplierModal"
         x-cloak
         class="app-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="app-modal-panel bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
                <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white">Add Supplier Record</h3>
                <button @click="showSupplierModal = false" class="text-neutral-500 hover:text-neutral-600 text-sm">✕</button>
            </div>

            <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-3.5 text-sm">
                @csrf

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Supplier Name:</label>
                    <input type="text" name="name" required placeholder="e.g. Suntop Bales Warehouse" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                </div>

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Contact Number (Optional, Numeric):</label>
                    <input type="text" name="contact_number" placeholder="09171234567" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                </div>

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Notes &amp; Inspection Terms:</label>
                    <textarea name="notes" rows="3" placeholder="Inspection terms, return policy, bale quality notes..." class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring"></textarea>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="showSupplierModal = false" class="flex-1 py-2.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-semibold">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
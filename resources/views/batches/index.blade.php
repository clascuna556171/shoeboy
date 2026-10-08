@extends('layouts.app')

@section('title', 'Batch Intake & Registry')

@section('content')
<div class="space-y-6" x-data="{ showIntakeModal: false, view: localStorage.getItem('shoeboy.batches.view') || 'cards', editingBatch: null, setView(v){ this.view = v; localStorage.setItem('shoeboy.batches.view', v); } }">

    {{-- Page header --}}
    <div class="app-card p-5 lg:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="min-w-0">
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Inventory Intake Engine</div>
                <h1 class="text-xl md:text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-0.5">Footwear Batch Intakes</h1>
                </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div data-tour="view" class="flex items-center bg-neutral-100 dark:bg-neutral-800 p-1 rounded-xl">
                <button type="button" @click="setView('cards')"
                        :class="view === 'cards' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-semibold text-neutral-900 dark:text-white' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
                        class="px-3 py-1.5 rounded-lg text-sm transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Cards
                </button>
                <button type="button" @click="setView('table')"
                        :class="view === 'table' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-semibold text-neutral-900 dark:text-white' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
                        class="px-3 py-1.5 rounded-lg text-sm transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    Table
                </button>
            </div>

            <button type="button"
                    @click="showIntakeModal = true"
                    data-tour="primary"
                    class="px-5 py-2.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm shadow-sm flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Record New Batch Intake</span>
            </button>
        </div>
    </div>

    <x-table-toolbar :action="route('batches.index')" :search="$search"
                     search-placeholder="Search batch code or supplier..."
                     :reset-url="route('batches.index')" :filters="[]" />

    {{-- Batch cards --}}
    <div x-show="view === 'cards'" data-view-panel="cards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($batches as $batch)
        <div class="app-card app-card-hover p-5 flex flex-col justify-between gap-5 group">
            <div class="space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-neutral-500">Batch</span>
                        <h3 class="text-xl font-bold font-mono text-[#1D1D1F] dark:text-white">{{ $batch->batch_code }}</h3>
                    </div>
                    <span class="text-xs font-mono text-neutral-500">{{ $batch->date_acquired->format('M d, Y') }}</span>
                </div>

                <div class="text-sm text-neutral-600 dark:text-neutral-400">
                    Supplier: <strong class="text-neutral-800 dark:text-neutral-200">{{ $batch->supplier->name }}</strong>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-neutral-50/70 dark:bg-neutral-800/40 p-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Bale Outlay</div>
                        <div class="mt-1 font-mono text-lg font-bold text-neutral-900 dark:text-white">₱{{ number_format($batch->total_cost, 2) }}</div>
                    </div>
                    <div class="rounded-xl border border-emerald-200/70 dark:border-emerald-900/50 bg-emerald-50/50 dark:bg-emerald-950/20 p-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Base Unit Cost</div>
                        <div class="mt-1 font-mono text-lg font-bold text-emerald-700 dark:text-emerald-300">₱{{ number_format($batch->average_item_cost, 2) }}</div>
                    </div>
                </div>

                <div class="space-y-2 pt-1">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-neutral-500">Stock Logged:</span>
                        <span class="font-mono text-neutral-800 dark:text-neutral-200">{{ $batch->items->count() }} / {{ $batch->total_pairs }} pairs</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-[11px] font-mono font-semibold">
                        <span class="badge badge-available">{{ $batch->available_count }} avail</span>
                        <span class="badge badge-reserved">{{ $batch->reserved_count }} res</span>
                        <span class="badge badge-sold">{{ $batch->sold_count }} sold</span>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800 flex flex-wrap items-center justify-between gap-2">
                <span class="text-sm text-neutral-500 font-mono whitespace-nowrap">{{ $batch->total_sacks }} sack(s)</span>
                <div class="flex items-center gap-1.5 shrink-0">
                    <x-action-btn icon="edit" tone="secondary"
                                  @click="editingBatch = {{ Js::from(['id' => $batch->id, 'supplier_id' => $batch->supplier_id, 'batch_code' => $batch->batch_code, 'date_acquired' => $batch->date_acquired->format('Y-m-d'), 'total_sacks' => $batch->total_sacks, 'total_pairs' => $batch->total_pairs, 'total_cost' => (float) $batch->total_cost]) }}">Edit</x-action-btn>
                    <form action="{{ route('batches.destroy', $batch->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <x-action-btn icon="trash" tone="ghost-danger" type="submit"
                                      data-confirm="Delete batch {{ $batch->batch_code }}?"
                                      data-confirm-variant="danger"
                                      data-confirm-message="Batches that still contain pairs or expenses cannot be deleted."
                                      data-confirm-label="Delete">Delete</x-action-btn>
                    </form>
                    <x-action-btn icon="open" tone="secondary" :href="route('batches.show', $batch->id)">Open</x-action-btn>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full app-card">
            <div class="app-empty">
                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span class="text-xs font-medium">No batches recorded yet.</span>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Table view --}}
    <div x-show="view === 'table'" data-view-panel="table" class="app-card">
        <div class="overflow-x-auto lg:overflow-visible">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <x-sort-th-server column="batch_code" label="Batch" />
                        <th class="py-3 px-4 font-semibold">Supplier</th>
                        <x-sort-th-server column="total_sacks" label="Sacks" align="center" />
                        <x-sort-th-server column="total_cost" label="Bale Outlay" align="right" />
                        <th class="py-3 px-4 text-right font-semibold">Base Unit Cost</th>
                        <th class="py-3 px-4 text-center font-semibold">Stock (A / R / S)</th>
                        <th class="py-3 px-4 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    @forelse($batches as $batch)
                    <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3.5 px-4">
                            <span class="block font-mono font-bold text-[#1D1D1F] dark:text-white">{{ $batch->batch_code }}</span>
                            <span class="font-mono text-xs text-neutral-500">{{ $batch->date_acquired->format('M d, Y') }}</span>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-neutral-800 dark:text-neutral-200">{{ $batch->supplier->name }}</td>
                        <td class="py-3.5 px-4 text-center font-mono text-neutral-600 dark:text-neutral-300">{{ $batch->items->count() }} / {{ $batch->total_pairs }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-neutral-800 dark:text-neutral-200">₱{{ number_format($batch->total_cost, 2) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">₱{{ number_format($batch->average_item_cost, 2) }}</td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center justify-center gap-1.5">
                                <span class="badge badge-available">{{ $batch->available_count }}</span>
                                <span class="badge badge-reserved">{{ $batch->reserved_count }}</span>
                                <span class="badge badge-sold">{{ $batch->sold_count }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <x-action-btn icon="edit" tone="secondary"
                                              @click="editingBatch = {{ Js::from(['id' => $batch->id, 'supplier_id' => $batch->supplier_id, 'batch_code' => $batch->batch_code, 'date_acquired' => $batch->date_acquired->format('Y-m-d'), 'total_sacks' => $batch->total_sacks, 'total_pairs' => $batch->total_pairs, 'total_cost' => (float) $batch->total_cost]) }}">Edit</x-action-btn>
                                <x-action-btn icon="open" tone="secondary" :href="route('batches.show', $batch->id)">Open</x-action-btn>
                                <form action="{{ route('batches.destroy', $batch->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-action-btn icon="trash" tone="ghost-danger" type="submit"
                                                  data-confirm="Delete batch {{ $batch->batch_code }}?"
                                                  data-confirm-variant="danger"
                                                  data-confirm-message="Batches that still contain pairs or expenses cannot be deleted."
                                                  data-confirm-label="Delete">Delete</x-action-btn>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="app-empty">
                                <span class="text-xs font-medium">No batches recorded yet.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Intake modal --}}
    <x-modal title="Record New Batch Intake" accent="amber" close="showIntakeModal = false"
             x-show="showIntakeModal" x-cloak @keydown.escape.window="showIntakeModal = false"
             x-data="{
                 cost: 18000,
                 pairs: 24,
                 get avg() {
                     const p = Math.max(1, parseInt(this.pairs) || 1);
                     const c = parseFloat(this.cost) || 0;
                     return (c / p).toFixed(2);
                 }
             }">
            <form action="{{ route('batches.store') }}" method="POST" class="space-y-3.5 text-sm">
                @csrf

                <div>
                    <label class="app-label">Supplier Record <span class="app-req">*</span></label>
                    <select name="supplier_id" required class="app-select">
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Batch Short Code <span class="app-req">*</span></label>
                        <input type="text" name="batch_code" required placeholder="e.g. B06" class="app-input font-mono uppercase">
                    </div>
                    <div>
                        <label class="app-label">Date Acquired <span class="app-req">*</span></label>
                        <input type="date" name="date_acquired" required value="{{ date('Y-m-d') }}" class="app-input">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Total Sacks / Bales <span class="app-req">*</span></label>
                        <input type="number" name="total_sacks" required min="1" value="1" class="app-input font-mono">
                    </div>
                    <div>
                        <label class="app-label">Total Footwear Pairs <span class="app-req">*</span></label>
                        <input type="number" name="total_pairs" x-model="pairs" required min="1" value="24" class="app-input font-mono">
                    </div>
                </div>

                <div>
                    <label class="app-label">Total Batch Cost (₱) <span class="app-req">*</span></label>
                    <input type="number" step="0.01" name="total_cost" x-model="cost" required min="0" value="18000" class="app-input font-mono">
                </div>

                <div class="p-3 rounded-2xl bg-neutral-100 dark:bg-neutral-800/70 text-xs flex items-center justify-between">
                    <span class="text-neutral-500">Auto-Computed Base Unit Cost:</span>
                    <span class="font-mono font-bold text-sm text-[#0071E3] dark:text-[#0A84FF]" x-text="'₱' + avg"></span>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="button" @click="showIntakeModal = false" class="app-btn app-btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="app-btn app-btn-amber flex-1">Save Batch</button>
                </div>
            </form>
    </x-modal>

    {{-- Edit batch modal --}}
    <x-modal accent="amber" close="editingBatch = null"
             x-show="editingBatch" x-cloak @keydown.escape.window="editingBatch = null">
        <x-slot:heading>Edit Batch <span x-text="editingBatch ? editingBatch.batch_code : ''"></span></x-slot:heading>
            <form :action="editingBatch ? '/batches/' + editingBatch.id : '#'" method="POST" class="space-y-3.5 text-sm">
                @csrf
                @method('PUT')

                <div>
                    <label class="app-label">Supplier Record <span class="app-req">*</span></label>
                    <select name="supplier_id" :value="editingBatch?.supplier_id" required class="app-select">
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Batch Short Code <span class="app-req">*</span></label>
                        <input type="text" name="batch_code" :value="editingBatch?.batch_code" required class="app-input font-mono uppercase">
                    </div>
                    <div>
                        <label class="app-label">Date Acquired <span class="app-req">*</span></label>
                        <input type="date" name="date_acquired" :value="editingBatch?.date_acquired" required class="app-input">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Total Sacks / Bales <span class="app-req">*</span></label>
                        <input type="number" name="total_sacks" :value="editingBatch?.total_sacks" required min="1" class="app-input font-mono">
                    </div>
                    <div>
                        <label class="app-label">Total Footwear Pairs <span class="app-req">*</span></label>
                        <input type="number" name="total_pairs" :value="editingBatch?.total_pairs" required min="1" class="app-input font-mono">
                    </div>
                </div>

                <div>
                    <label class="app-label">Total Batch Cost (₱) <span class="app-req">*</span></label>
                    <input type="number" step="0.01" name="total_cost" :value="editingBatch?.total_cost" required min="0" class="app-input font-mono">
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="button" @click="editingBatch = null" class="app-btn app-btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="app-btn app-btn-amber flex-1">Save changes</button>
                </div>
            </form>
    </x-modal>

    {{-- Show the stored view (cards/table) before Alpine boots. --}}
    <script>
        (function () {
            var view = localStorage.getItem('shoeboy.batches.view') || 'cards';
            document.querySelectorAll('[data-view-panel]').forEach(function (el) {
                el.style.display = el.getAttribute('data-view-panel') === view ? '' : 'none';
            });
        })();
    </script>

</div>
@endsection
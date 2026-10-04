@extends('layouts.app')

@section('title', 'Supplier Records')

@section('content')
<div class="space-y-6" x-data="{ showSupplierModal: false, view: localStorage.getItem('shoeboy.suppliers.view') || 'cards', editingSupplier: null, setView(v){ this.view = v; localStorage.setItem('shoeboy.suppliers.view', v); } }">

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
                    @click="showSupplierModal = true"
                    class="px-5 py-2.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm shadow-sm flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add New Supplier</span>
            </button>
        </div>
    </div>

    <x-table-toolbar :action="route('suppliers.index')" :search="$search"
                     search-placeholder="Search supplier name or contact..."
                     :reset-url="route('suppliers.index')" :filters="[]" />

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

            <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-end gap-1.5">
                <x-action-btn icon="edit" tone="secondary"
                              @click="editingSupplier = {{ Js::from(['id' => $sup->id, 'name' => $sup->name, 'contact_number' => $sup->contact_number, 'notes' => $sup->notes]) }}">Edit</x-action-btn>
                <form action="{{ route('suppliers.destroy', $sup->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <x-action-btn icon="trash" tone="ghost-danger" type="submit"
                                  data-confirm="Delete this supplier?"
                                  data-confirm-variant="danger"
                                  data-confirm-message="You can undo this right after from the notification toast."
                                  data-confirm-label="Delete">Delete</x-action-btn>
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
                        <x-sort-th-server column="name" label="Supplier" />
                        <x-sort-th-server column="contact_number" label="Contact Number" />
                        <x-sort-th-server column="batches_count" label="Batches" align="center" />
                        <th class="py-3 px-4 font-semibold">Notes</th>
                        <th class="py-3 px-4 font-semibold text-right">Actions</th>
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
                            <div class="inline-flex items-center gap-1.5">
                                <x-action-btn icon="edit" tone="secondary"
                                              @click="editingSupplier = {{ Js::from(['id' => $sup->id, 'name' => $sup->name, 'contact_number' => $sup->contact_number, 'notes' => $sup->notes]) }}">Edit</x-action-btn>
                                <form action="{{ route('suppliers.destroy', $sup->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-action-btn icon="trash" tone="ghost-danger" type="submit"
                                                  data-confirm="Delete this supplier?"
                                                  data-confirm-variant="danger"
                                                  data-confirm-message="You can undo this right after from the notification toast."
                                                  data-confirm-label="Delete">Delete</x-action-btn>
                                </form>
                            </div>
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
    <x-modal title="Add Supplier Record" accent="teal" close="showSupplierModal = false"
             x-show="showSupplierModal" x-cloak @keydown.escape.window="showSupplierModal = false">
            <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-3.5 text-sm">
                @csrf

                <div>
                    <label class="app-label">Supplier Name <span class="app-req">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Suntop Bales Warehouse" class="app-input">
                </div>

                <div>
                    <label class="app-label">Contact Number <span class="app-optional">(optional, numeric)</span></label>
                    <input type="text" name="contact_number" placeholder="09171234567" class="app-input">
                </div>

                <div>
                    <label class="app-label">Notes &amp; Inspection Terms <span class="app-optional">(optional)</span></label>
                    <textarea name="notes" rows="3" placeholder="Inspection terms, return policy, bale quality notes..." class="app-textarea"></textarea>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="button" @click="showSupplierModal = false" class="app-btn app-btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="app-btn app-btn-teal flex-1">Save Supplier</button>
                </div>
            </form>
    </x-modal>

    {{-- Edit supplier modal --}}
    <x-modal title="Edit Supplier" accent="teal" close="editingSupplier = null"
             x-show="editingSupplier" x-cloak @keydown.escape.window="editingSupplier = null">
            <form :action="editingSupplier ? '/suppliers/' + editingSupplier.id : '#'" method="POST" class="space-y-3.5 text-sm">
                @csrf
                @method('PUT')

                <div>
                    <label class="app-label">Supplier Name <span class="app-req">*</span></label>
                    <input type="text" name="name" :value="editingSupplier?.name" required class="app-input">
                </div>

                <div>
                    <label class="app-label">Contact Number <span class="app-optional">(optional)</span></label>
                    <input type="text" name="contact_number" :value="editingSupplier?.contact_number" class="app-input">
                </div>

                <div>
                    <label class="app-label">Notes &amp; Inspection Terms <span class="app-optional">(optional)</span></label>
                    <textarea name="notes" rows="3" class="app-textarea" x-text="editingSupplier?.notes"></textarea>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="button" @click="editingSupplier = null" class="app-btn app-btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="app-btn app-btn-teal flex-1">Save changes</button>
                </div>
            </form>
    </x-modal>

</div>
@endsection
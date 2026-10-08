@extends('layouts.app')

@section('title', "Batch {$batch->batch_code}")

@section('content')
<div class="space-y-6" x-data="{ showAddItemModal: false, editItem: null }">

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

    {{-- Pairs in this batch --}}
    <div class="app-card">
        <div class="px-5 lg:px-6 py-4 border-b border-neutral-200 dark:border-neutral-800">
            <h2 class="font-bold text-base text-[#1D1D1F] dark:text-white">Pairs in this batch</h2>
            <p class="text-xs text-neutral-500 mt-0.5">Add, edit, triage, or remove pairs here. The Inventory page lists every batch together.</p>
        </div>

        <div class="overflow-x-auto lg:overflow-visible">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <x-sort-th-server column="sku" label="SKU" />
                        <x-sort-th-server column="brand" label="Brand & Model" />
                        <x-sort-th-server column="size" label="Size" />
                        <x-sort-th-server column="condition" label="Condition" />
                        <x-sort-th-server column="status" label="Sale" align="center" />
                        <x-sort-th-server column="triage_status" label="Triage" align="center" />
                        <th class="py-3 px-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    @forelse($items as $item)
                    <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3.5 px-4 font-mono font-bold text-[#0071E3] dark:text-[#0A84FF] whitespace-nowrap">{{ $item->sku }}</td>
                        <td class="py-3.5 px-4 font-semibold text-neutral-800 dark:text-neutral-200">{{ $item->brand }} {{ $item->model }}</td>
                        <td class="py-3.5 px-4 font-mono text-neutral-600 dark:text-neutral-300">{{ $item->size }}</td>
                        <td class="py-3.5 px-4 text-neutral-600 dark:text-neutral-300">{{ $item->condition }}</td>
                        <td class="py-3.5 px-4 text-center"><x-status-badge kind="item" :value="$item->status" /></td>
                        <td class="py-3.5 px-4 text-center">
                            @if($item->status === 'available')
                                <x-status-badge kind="triage" :value="$item->triage_status" />
                            @else
                                <span class="text-neutral-300 dark:text-neutral-700">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center justify-end gap-1.5">
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
                                                      'category' => $item->category,
                                                      'triage_status' => $item->triage_status,
                                                  ]) }}">Edit</x-action-btn>

                                    <form action="{{ route('items.destroy', $item) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                title="Delete pair"
                                                data-confirm="Delete pair {{ $item->sku }}?"
                                                data-confirm-message="The pair is removed from inventory. An Undo option appears right after."
                                                data-confirm-variant="danger"
                                                data-confirm-icon="trash"
                                                data-confirm-label="Delete"
                                                class="p-2 rounded-xl text-neutral-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-neutral-500">—</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="app-empty">
                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                <span class="text-xs font-medium">No pairs logged for this batch yet.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add pair modal --}}
    <x-modal title="Add Pair to Batch {{ $batch->batch_code }}" accent="amber" close="showAddItemModal = false"
             x-show="showAddItemModal" x-cloak @keydown.escape.window="showAddItemModal = false">
            <form action="{{ route('items.store') }}" method="POST" class="space-y-3.5 text-sm">
                @csrf
                <x-add-pair-fields :locked-batch="$batch" />

                <div class="flex gap-2 pt-4">
                    <button type="button" @click="showAddItemModal = false" class="app-btn app-btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="app-btn app-btn-amber flex-1">Save Pair</button>
                </div>
            </form>
    </x-modal>

    {{-- Edit pair modal (only available pairs are editable) --}}
    <x-modal eyebrow="Edit Pair" size="lg" accent="emerald" close="editItem = null"
             x-show="editItem" x-cloak @keydown.escape.window="editItem = null">
        <x-slot:heading><span x-text="editItem ? editItem.sku : ''"></span></x-slot:heading>
            <form :action="editItem ? '{{ route('items.update', ['item' => '__ID__']) }}'.replace('__ID__', editItem.id) : '#'" method="POST" class="space-y-3.5">
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

                <div>
                    <label class="app-label">Category <span class="app-optional">(optional)</span></label>
                    <input type="text" name="category" :value="editItem?.category" class="app-input">
                </div>

                <div>
                    <label class="app-label">Triage Stage <span class="app-req">*</span></label>
                    <select name="triage_status" :value="editItem?.triage_status" required class="app-select">
                        <option value="washing">Washing</option>
                        <option value="under_repair">Under repair</option>
                        <option value="available">Ready to sell</option>
                    </select>
                    <p class="mt-1 text-[11px] text-neutral-500">Only <strong>Ready</strong> pairs can be reserved or sold.</p>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="button" @click="editItem = null" class="app-btn app-btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="app-btn app-btn-emerald flex-1">Save changes</button>
                </div>
            </form>
    </x-modal>

</div>
@endsection

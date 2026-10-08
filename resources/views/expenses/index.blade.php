@extends('layouts.app')

@section('title', 'Shop Operating Expenses')

@section('content')
<div class="space-y-6" x-data="{ showExpenseModal: false }">

    {{-- Page header --}}
    <div class="app-card p-5 lg:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="min-w-0">
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Financial Ledger</div>
                <h1 class="text-xl md:text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-0.5">Store Operating Expenses</h1>
                </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="text-right">
                <div class="text-[11px] uppercase tracking-wider text-neutral-500 font-medium">Total Outlay</div>
                <div class="font-mono font-bold text-xl text-rose-600 dark:text-rose-400">₱{{ number_format($totalExpenses, 2) }}</div>
            </div>
            <button type="button"
                    @click="showExpenseModal = true"
                    data-tour="primary"
                    class="px-5 py-2.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm shadow-sm flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Record Expense</span>
            </button>
        </div>
    </div>

    <x-table-toolbar :action="route('expenses.index')" :search="$search"
                     search-placeholder="Search description or reference no..."
                     :reset-url="route('expenses.index')"
                     :filters="[
                         ['name' => 'category', 'selected' => $category, 'options' => ['' => 'All Categories'] + $categories->mapWithKeys(fn ($c) => [$c => $c])->all()],
                     ]" />

    {{-- Table --}}
    <div class="app-card" data-tour="list">
        <div class="overflow-x-auto lg:overflow-visible">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <x-sort-th-server column="date" label="Date" />
                        <x-sort-th-server column="category" label="Category" />
                        <x-sort-th-server column="description" label="Description" />
                        <x-sort-th-server column="reference_no" label="Reference No." />
                        <th class="py-3 px-4 font-semibold" data-tour="batch-link">Batch Link</th>
                        <x-sort-th-server column="amount" label="Amount" align="right" />
                        <th class="py-3 px-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    @forelse($expenses as $exp)
                    <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3.5 px-4 font-mono text-neutral-600 dark:text-neutral-300">{{ $exp->date->format('Y-m-d') }}</td>
                        <td class="py-3.5 px-4">
                            <span class="badge badge-neutral">{{ $exp->category }}</span>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-neutral-800 dark:text-neutral-200">{{ $exp->description }}</td>
                        <td class="py-3.5 px-4 font-mono text-neutral-500">{{ $exp->reference_no ?? '—' }}</td>
                        <td class="py-3.5 px-4 font-mono text-neutral-500">{{ $exp->batch?->batch_code ?? 'General Overhead' }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-rose-600 dark:text-rose-400">−₱{{ number_format($exp->amount, 2) }}</td>
                        <td class="py-3.5 px-4 text-right">
                            <form action="{{ route('expenses.destroy', $exp->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <x-action-btn icon="trash" tone="ghost-danger" type="submit"
                                              data-confirm="Delete this expense?"
                                              data-confirm-variant="danger"
                                              data-confirm-message="You can undo this right after from the notification toast."
                                              data-confirm-label="Delete">Delete</x-action-btn>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="app-empty">
                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m0 0l-6-6m6 6H3"/></svg>
                                <span class="text-xs font-medium">No expenses recorded yet.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-neutral-800">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>

    {{-- Modal --}}
    <x-modal title="Record Shop Expense" accent="rose" close="showExpenseModal = false"
             x-show="showExpenseModal" x-cloak @keydown.escape.window="showExpenseModal = false">
            <form action="{{ route('expenses.store') }}" method="POST" class="space-y-3.5 text-sm">
                @csrf

                <div>
                    <label class="app-label">Category <span class="app-req">*</span></label>
                    <select name="category" required class="app-select">
                        <option value="Sack Purchase">Sack / Bale Purchase</option>
                        <option value="Shipping & Freight">Shipping & Freight</option>
                        <option value="Shoe Restoration">Shoe Restoration & Wash</option>
                        <option value="Packaging & Labels">Packaging & Thermal Labels</option>
                        <option value="Store Utilities">Shop Utilities & Lighting</option>
                        <option value="Miscellaneous">Miscellaneous</option>
                    </select>
                </div>

                <div>
                    <label class="app-label">Description <span class="app-req">*</span></label>
                    <input type="text" name="description" required placeholder="e.g. Freight cargo from Cebu port" class="app-input">
                </div>

                <div>
                    <label class="app-label">Reference No. <span class="app-optional">(optional — OR / receipt no.)</span></label>
                    <input type="text" name="reference_no" placeholder="e.g. OR-2026-00123 / Bill #4567" class="app-input font-mono">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Amount (₱) <span class="app-req">*</span></label>
                        <input type="number" step="0.01" name="amount" required placeholder="0.00" class="app-input font-mono">
                    </div>
                    <div>
                        <label class="app-label">Date <span class="app-req">*</span></label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="app-input">
                    </div>
                </div>

                <div>
                    <label class="app-label">Tie to Batch <span class="app-optional">(optional)</span></label>
                    <select name="batch_id" class="app-select">
                        <option value="">General Shop Overhead (No Batch Link)</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}">{{ $b->batch_code }} ({{ $b->supplier->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="button" @click="showExpenseModal = false" class="app-btn app-btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="app-btn app-btn-rose flex-1">Save Expense</button>
                </div>
            </form>
    </x-modal>

</div>
@endsection
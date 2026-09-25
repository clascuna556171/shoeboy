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
                    class="px-5 py-2.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm shadow-sm flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Record Expense</span>
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="app-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <th class="py-3 px-4 font-semibold">Date</th>
                        <th class="py-3 px-4 font-semibold">Category</th>
                        <th class="py-3 px-4 font-semibold">Description</th>
                        <th class="py-3 px-4 font-semibold">Reference No.</th>
                        <th class="py-3 px-4 font-semibold">Batch Link</th>
                        <th class="py-3 px-4 font-semibold text-right">Amount</th>
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
                                <button type="submit" onclick="return confirm('Delete this expense?')"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold text-neutral-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                                    Delete
                                </button>
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
    <div x-show="showExpenseModal"
         x-cloak
         class="app-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="app-modal-panel bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white">Record Shop Expense</h3>
                </div>
                <button @click="showExpenseModal = false" class="text-neutral-500 hover:text-neutral-600 text-sm">✕</button>
            </div>

            <form action="{{ route('expenses.store') }}" method="POST" class="space-y-3.5 text-sm">
                @csrf

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Category:</label>
                    <select name="category" required class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                        <option value="Sack Purchase">Sack / Bale Purchase</option>
                        <option value="Shipping & Freight">Shipping & Freight</option>
                        <option value="Shoe Restoration">Shoe Restoration & Wash</option>
                        <option value="Packaging & Labels">Packaging & Thermal Labels</option>
                        <option value="Store Utilities">Shop Utilities & Lighting</option>
                        <option value="Miscellaneous">Miscellaneous</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Description:</label>
                    <input type="text" name="description" required placeholder="e.g. Freight cargo from Cebu port" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                </div>

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Reference No. <span class="text-neutral-500 font-normal">(optional — OR / receipt no.)</span>:</label>
                    <input type="text" name="reference_no" placeholder="e.g. OR-2026-00123 / Bill #4567" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl font-mono apple-focus-ring">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Amount (₱):</label>
                        <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl font-mono apple-focus-ring">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Date:</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Tie to Batch (Optional):</label>
                    <select name="batch_id" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                        <option value="">General Shop Overhead (No Batch Link)</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}">{{ $b->batch_code }} ({{ $b->supplier->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="showExpenseModal = false" class="flex-1 py-2.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-semibold">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold">Save Expense</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
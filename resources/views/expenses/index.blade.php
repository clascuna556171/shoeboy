@extends('layouts.app')

@section('title', 'Shop Operating Expenses')

@section('content')
<div class="space-y-6" x-data="{ showExpenseModal: false }">


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">Financial Ledger</span>
                <span class="text-xs text-neutral-400">Expense Tracking Module</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-[#1D1D1F] dark:text-white mt-1">Store Operating Expenses</h1>
            <p class="text-xs text-neutral-500 mt-0.5">Total Outlay Recorded: <strong class="font-mono text-rose-600 dark:text-rose-400">₱{{ number_format($totalExpenses, 2) }}</strong></p>
        </div>

        <button type="button"
                @click="showExpenseModal = true"
                class="px-5 py-2.5 rounded-2xl bg-[#0071E3] hover:bg-[#0077ED] text-white font-semibold text-xs shadow-sm flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Record Shop Expense</span>
        </button>
    </div>


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-[11px] text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800 pb-2">
                    <tr>
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Category</th>
                        <th class="py-2.5 px-3">Description</th>
                        <th class="py-2.5 px-3">Batch Link</th>
                        <th class="py-2.5 px-3 text-right">Amount</th>
                        <th class="py-2.5 px-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60 font-sans">
                    @forelse($expenses as $exp)
                    <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3 px-3 font-mono text-neutral-700 dark:text-neutral-300">{{ $exp->date->format('Y-m-d') }}</td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                                {{ $exp->category }}
                            </span>
                        </td>
                        <td class="py-3 px-3 font-semibold text-neutral-800 dark:text-neutral-200">{{ $exp->description }}</td>
                        <td class="py-3 px-3 font-mono text-neutral-500">
                            {{ $exp->batch?->batch_code ?? 'General Overhead' }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-rose-600 dark:text-rose-400">
                            -₱{{ number_format($exp->amount, 2) }}
                        </td>
                        <td class="py-3 px-3 text-right">
                            <form action="{{ route('expenses.destroy', $exp->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this expense?')" class="text-neutral-400 hover:text-rose-600 text-xs">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-neutral-400">No expenses recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800">
            {{ $expenses->links() }}
        </div>
    </div>


    <div x-show="showExpenseModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Record Shop Expense</h3>
                </div>
                <button @click="showExpenseModal = false" class="text-neutral-400 hover:text-neutral-600 text-sm">✕</button>
            </div>

            <form action="{{ route('expenses.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Category:</label>
                    <select name="category" required class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
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
                    <input type="text" name="description" required placeholder="e.g. Freight cargo from Cebu port" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Amount (₱):</label>
                        <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl font-mono apple-focus-ring">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Date:</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Tie to Batch (Optional):</label>
                    <select name="batch_id" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
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

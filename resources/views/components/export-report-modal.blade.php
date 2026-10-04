@php
    $batches = \App\Models\Batch::orderBy('batch_code')->get(['id', 'batch_code']);
    $staffList = \App\Models\User::orderBy('name')->get(['id', 'name']);
    $sectionDefs = [
        'summary' => ['label' => 'Executive Summary', 'hint' => 'Net balance & headline totals'],
        'sales' => ['label' => 'Sales Ledger', 'hint' => 'Every sold pair (or per order)'],
        'expenses' => ['label' => 'Expense Ledger', 'hint' => 'Every operating expense'],
        'sessions' => ['label' => 'Session / Date Summary', 'hint' => 'Daily sales & collection'],
        'batches' => ['label' => 'Batch Profitability', 'hint' => 'Profit per shipment'],
        'tiers' => ['label' => 'Price Tier Performance', 'hint' => 'Sales by price band'],
        'inventory' => ['label' => 'Inventory Snapshot', 'hint' => 'Counts by status'],
    ];
@endphp

<x-modal eyebrow="Custom Export" title="Build your report" accent="indigo" size="lg" scroll
         close="$store.exportModal.open = false"
         x-show="$store.exportModal.open" x-cloak
         @keydown.escape.window="$store.exportModal.open = false"
         x-data="exportBuilder()">

    <form method="GET" action="{{ route('reports.export') }}" data-progress="download" class="space-y-5 text-sm">

        {{-- Date range --}}
        <div class="space-y-2">
            <label class="app-label">1 · Date range</label>
            <div class="grid grid-cols-3 gap-1.5 text-xs">
                @foreach([
                    'all' => 'All Time', 'today' => 'Today', 'week' => 'This Week',
                    'month' => 'This Month', 'last30' => 'Last 30 Days', 'year' => 'This Year',
                ] as $val => $label)
                    <button type="button" @click="preset = '{{ $val }}'"
                            :class="preset === '{{ $val }}' ? 'bg-[#1D1D1F] text-white dark:bg-white dark:text-[#1D1D1F] font-semibold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700'"
                            class="px-2.5 py-2 rounded-xl transition-all">{{ $label }}</button>
                @endforeach
                <button type="button" @click="preset = 'custom'"
                        :class="preset === 'custom' ? 'bg-[#1D1D1F] text-white dark:bg-white dark:text-[#1D1D1F] font-semibold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700'"
                        class="px-2.5 py-2 rounded-xl transition-all">Custom…</button>
            </div>
            <input type="hidden" name="preset" :value="preset">
            <div x-show="isCustom" x-cloak class="grid grid-cols-2 gap-3 pt-1">
                <div>
                    <label class="app-label">Start Date <span class="app-req">*</span></label>
                    <input type="date" name="start_date" :required="isCustom" class="app-input">
                </div>
                <div>
                    <label class="app-label">End Date <span class="app-req">*</span></label>
                    <input type="date" name="end_date" :required="isCustom" class="app-input">
                </div>
            </div>
        </div>

        {{-- Quick presets --}}
        <div class="space-y-2">
            <label class="app-label">2 · Quick content</label>
            <div class="flex items-center bg-neutral-100 dark:bg-neutral-800 p-1 rounded-xl text-xs">
                <button type="button" @click="applyContent('all')" :class="content === 'all' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#1D1D1F] dark:text-white' : 'text-neutral-500'" class="flex-1 px-3 py-2 rounded-lg transition-all">Everything</button>
                <button type="button" @click="applyContent('sales')" :class="content === 'sales' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#1D1D1F] dark:text-white' : 'text-neutral-500'" class="flex-1 px-3 py-2 rounded-lg transition-all">Sales</button>
                <button type="button" @click="applyContent('expenses')" :class="content === 'expenses' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#1D1D1F] dark:text-white' : 'text-neutral-500'" class="flex-1 px-3 py-2 rounded-lg transition-all">Expenses</button>
            </div>
        </div>

        {{-- Sheets --}}
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <label class="app-label">3 · Sheets to include <span class="text-neutral-400 font-normal">(<span x-text="sectionCount"></span> selected)</span></label>
                <div class="flex items-center gap-2 text-[11px]">
                    <button type="button" @click="toggleAll(true)" class="text-[#0071E3] dark:text-[#0A84FF] font-semibold hover:underline">All</button>
                    <button type="button" @click="toggleAll(false)" class="text-neutral-500 hover:underline">None</button>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($sectionDefs as $key => $def)
                    <label class="flex items-start gap-2.5 px-3 py-2 rounded-xl border border-neutral-200 dark:border-neutral-800 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                        <input type="checkbox" name="sections[]" value="{{ $key }}" x-model="sections.{{ $key }}" class="mt-0.5 w-4 h-4 rounded text-[#0071E3] border-neutral-300 dark:border-neutral-700 focus:ring-[#0071E3]">
                        <span>
                            <span class="block font-medium text-neutral-800 dark:text-neutral-200">{{ $def['label'] }}</span>
                            <span class="block text-[11px] text-neutral-500">{{ $def['hint'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Sales detail level --}}
        <div class="space-y-2">
            <label class="app-label">4 · Sales ledger detail</label>
            <div class="flex items-center bg-neutral-100 dark:bg-neutral-800 p-1 rounded-xl text-xs">
                <button type="button" @click="granularity = 'pair'" :class="granularity === 'pair' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#1D1D1F] dark:text-white' : 'text-neutral-500'" class="flex-1 px-3 py-2 rounded-lg transition-all">Per pair (line items)</button>
                <button type="button" @click="granularity = 'order'" :class="granularity === 'order' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#1D1D1F] dark:text-white' : 'text-neutral-500'" class="flex-1 px-3 py-2 rounded-lg transition-all">Per order (aggregate)</button>
            </div>
            <input type="hidden" name="sales_granularity" :value="granularity">
        </div>

        {{-- Filters --}}
        <div class="space-y-2">
            <label class="app-label">5 · Filters</label>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] text-neutral-500 mb-1">Channel</label>
                    <select name="channel" x-model="channel" class="app-select app-input-sm">
                        <option value="all">All channels</option>
                        <option value="live_stream">Live Stream</option>
                        <option value="walkin_pos">Walk-in POS</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] text-neutral-500 mb-1">Payment method</label>
                    <select name="payment_method" x-model="paymentMethod" class="app-select app-input-sm">
                        <option value="all">All methods</option>
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] text-neutral-500 mb-1">Order status</label>
                    <select name="order_status" x-model="orderStatus" class="app-select app-input-sm">
                        <option value="paid_fulfilled">Paid + Fulfilled</option>
                        <option value="fulfilled">Fulfilled only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] text-neutral-500 mb-1">Batch</label>
                    <select name="batch_id" x-model="batchId" class="app-select app-input-sm">
                        <option value="">All batches</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}">{{ $b->batch_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] text-neutral-500 mb-1">Staff</label>
                    <select name="staff_id" x-model="staffId" class="app-select app-input-sm">
                        <option value="">All staff</option>
                        @foreach($staffList as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] text-neutral-500 mb-1">Inventory view</label>
                    <select name="inventory_status" x-model="inventoryStatus" class="app-select app-input-sm">
                        <option value="all">All statuses</option>
                        <option value="available">Available only</option>
                        <option value="reserved">Reserved only</option>
                        <option value="sold">Sold only</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Columns --}}
        <div class="space-y-2">
            <label class="app-label">6 · Columns & extras</label>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <label class="flex items-center gap-2 px-3 py-2 rounded-xl border border-neutral-200 dark:border-neutral-800 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                    <input type="hidden" name="include_repair" value="0">
                    <input type="checkbox" name="include_repair" value="1" x-model="includeRepair" class="w-4 h-4 rounded text-[#0071E3] border-neutral-300 dark:border-neutral-700 focus:ring-[#0071E3]">
                    <span class="text-neutral-700 dark:text-neutral-200">Repair cost</span>
                </label>
                <label class="flex items-center gap-2 px-3 py-2 rounded-xl border border-neutral-200 dark:border-neutral-800 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                    <input type="hidden" name="include_payment_ref" value="0">
                    <input type="checkbox" name="include_payment_ref" value="1" x-model="includePaymentRef" class="w-4 h-4 rounded text-[#0071E3] border-neutral-300 dark:border-neutral-700 focus:ring-[#0071E3]">
                    <span class="text-neutral-700 dark:text-neutral-200">Payment reference</span>
                </label>
                <label class="flex items-center gap-2 px-3 py-2 rounded-xl border border-neutral-200 dark:border-neutral-800 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                    <input type="hidden" name="include_customer" value="0">
                    <input type="checkbox" name="include_customer" value="1" x-model="includeCustomer" class="w-4 h-4 rounded text-[#0071E3] border-neutral-300 dark:border-neutral-700 focus:ring-[#0071E3]">
                    <span class="text-neutral-700 dark:text-neutral-200">Customer</span>
                </label>
                <label class="flex items-center gap-2 px-3 py-2 rounded-xl border border-neutral-200 dark:border-neutral-800 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                    <input type="hidden" name="include_notes" value="0">
                    <input type="checkbox" name="include_notes" value="1" x-model="includeNotes" class="w-4 h-4 rounded text-[#0071E3] border-neutral-300 dark:border-neutral-700 focus:ring-[#0071E3]">
                    <span class="text-neutral-700 dark:text-neutral-200">Order notes</span>
                </label>
            </div>
        </div>

        <div class="flex gap-2 pt-2 border-t border-neutral-100 dark:border-neutral-800">
            <button type="button" @click="$store.exportModal.open = false" class="app-btn app-btn-secondary flex-1">Cancel</button>
            <button type="submit" class="app-btn app-btn-indigo flex-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download Excel
            </button>
        </div>
    </form>
</x-modal>

@extends('layouts.app')

@section('title', 'Financial & Profit Reports')

@php
    $net = $metrics['net_operating_balance'];
    $netPositive = $net >= 0;
@endphp

@section('content')
<div class="space-y-6" x-data="{
        reportTab: @js(request('reportTab', 'batches')),
        showExportModal: false,
        exportPreset: 'all',
        exportContent: 'all',
        exportSections: { summary: true, transactions: true, batches: true, tiers: true, expenses: true, inventory: true },
        setReportTab(key) {
            this.reportTab = key;
            const u = new URL(location.href);
            u.searchParams.set('reportTab', key);
            history.replaceState(null, '', u);
        },
        applyContent(mode) {
            this.exportContent = mode;
            if (mode === 'sales') {
                this.exportSections = { summary: true, transactions: true, batches: true, tiers: true, expenses: false, inventory: false };
            } else if (mode === 'expenses') {
                this.exportSections = { summary: true, transactions: false, batches: false, tiers: false, expenses: true, inventory: false };
            } else {
                this.exportSections = { summary: true, transactions: true, batches: true, tiers: true, expenses: true, inventory: true };
            }
        }
     }">

    {{-- Page header --}}
    <div class="app-card p-5 lg:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="min-w-0">
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Owner Access Only</div>
                <h1 class="text-xl md:text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-0.5">Consolidated Profit Report</h1>
                </div>
        </div>

        <button type="button" @click="showExportModal = true"
                class="px-5 py-2.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm shadow-sm flex items-center gap-2 transition-all shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Export Report</span>
        </button>
    </div>

    {{-- Headline numbers: net balance is the takeaway, the rest support it --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="rounded-3xl p-5 border shadow-sm {{ $netPositive ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-200/70 dark:border-emerald-900/50' : 'bg-amber-50/60 dark:bg-amber-950/20 border-amber-200/70 dark:border-amber-900/50' }}">
            <div class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Net Operating Balance</div>
            <div class="mt-1 text-5xl font-bold font-mono tracking-tight {{ $netPositive ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">
                {{ $net < 0 ? '−' : '' }}₱{{ number_format(abs($net), 2) }}
            </div>
            <p class="text-xs text-neutral-500 mt-1">Gross profit minus store expenses.</p>
        </div>

        <div class="lg:col-span-2 app-card p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-neutral-900 dark:text-white">How the net balance is built</div>
                        <div class="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">These totals feed every breakdown below.</div>
                    </div>
                    <span class="badge badge-neutral shrink-0">Totals</span>
                </div>

                <div class="mt-4 flex flex-col items-stretch gap-2 xl:flex-row xl:items-center">
                    <div class="flex-1 min-w-0 rounded-xl border border-neutral-200 dark:border-neutral-800 bg-neutral-50/60 dark:bg-neutral-800/30 p-3">
                        <div class="text-xs font-medium uppercase tracking-wider text-neutral-500">Gross Sales</div>
                        <x-money :value="$metrics['total_revenue']" class="mt-1 block font-mono text-lg font-bold text-[#1D1D1F] dark:text-white" />
                    </div>

                    <div class="self-center font-mono text-lg text-neutral-500 dark:text-neutral-400">−</div>

                    <div class="flex-1 min-w-0 rounded-xl border border-neutral-200 dark:border-neutral-800 bg-neutral-50/60 dark:bg-neutral-800/30 p-3">
                        <div class="text-xs font-medium uppercase tracking-wider text-neutral-500">Allocated COGS</div>
                        <x-money :value="$metrics['total_cogs']" class="mt-1 block font-mono text-lg font-bold text-neutral-800 dark:text-neutral-200" />
                    </div>

                    <div class="self-center font-mono text-lg text-neutral-500 dark:text-neutral-400">=</div>

                    <div class="flex-1 min-w-0 rounded-xl border border-emerald-200/70 dark:border-emerald-900/50 bg-emerald-50/50 dark:bg-emerald-950/20 p-3">
                        <div class="text-xs font-medium uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Gross Profit</div>
                        <x-money :value="$metrics['gross_profit']" class="mt-1 block font-mono text-lg font-bold text-emerald-700 dark:text-emerald-300" />
                    </div>

                    <div class="self-center font-mono text-lg text-neutral-500 dark:text-neutral-400">−</div>

                    <div class="flex-1 min-w-0 rounded-xl border border-neutral-200 dark:border-neutral-800 bg-neutral-50/60 dark:bg-neutral-800/30 p-3">
                        <div class="text-xs font-medium uppercase tracking-wider text-neutral-500">Store Expenses</div>
                        <x-money :value="$metrics['total_expenses']" class="mt-1 block font-mono text-lg font-bold text-rose-600 dark:text-rose-400" />
                    </div>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2 border-t border-neutral-100 dark:border-neutral-800 pt-3">
                <span class="font-mono text-lg text-neutral-500 dark:text-neutral-400">=</span>
                <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">Net Operating Balance</span>
                <span class="font-mono text-lg font-bold {{ $netPositive ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400' }}">{{ $net < 0 ? '−' : '' }}₱{{ number_format(abs($net), 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Breakdown tabs --}}
    <div class="app-card overflow-hidden">
        <div class="p-4 border-b border-neutral-200 dark:border-neutral-800">
            <div class="mb-3">
                <div class="text-sm font-semibold text-neutral-900 dark:text-white">Breakdown</div>
                <p class="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">Each view below sums to the totals above.</p>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center bg-neutral-100 dark:bg-neutral-800 p-1 rounded-xl text-xs">
                @foreach(['batches' => 'By Batch Intake', 'sessions' => 'By Session / Date', 'tiers' => 'By Price Tier'] as $key => $label)
                    <button type="button"
                            @click="setReportTab('{{ $key }}')"
                            :class="reportTab === '{{ $key }}' ? 'bg-white dark:bg-[#2C2C2E] font-bold text-[#1D1D1F] dark:text-white shadow-sm' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
                            class="px-3 py-1.5 rounded-lg transition-all">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <form x-show="reportTab === 'sessions'" x-cloak method="GET" action="{{ route('reports.index') }}" class="flex items-center gap-2 text-xs">
                <input type="hidden" name="reportTab" :value="reportTab">
                <input type="date" name="start_date" value="{{ $startDate }}" class="app-input app-input-sm !w-auto">
                <span class="text-neutral-500">to</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="app-input app-input-sm !w-auto">
                <button type="submit" class="app-btn app-btn-secondary app-btn-sm">Filter</button>
            </form>
            </div>
        </div>

        {{-- By batch --}}
        <div x-show="reportTab === 'batches'">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                        <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                            <th class="py-3 px-4 font-semibold">Batch</th>
                            <th class="py-3 px-4 font-semibold">Supplier</th>
                            <th class="py-3 px-4 font-semibold text-center">Pairs (S/R/A)</th>
                            <th class="py-3 px-4 font-semibold text-right">Batch Cost</th>
                            <th class="py-3 px-4 font-semibold text-right">Realized Sales</th>
                            <th class="py-3 px-4 font-semibold text-right">Order Profit</th>
                            <th class="py-3 px-4 font-semibold text-right">Expenses</th>
                            <th class="py-3 px-4 font-semibold text-right">Net Proceeds</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                        @foreach($batchReports as $b)
                        <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                            <td class="py-3.5 px-4 font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">
                                {{ $b['batch_code'] }}
                                <span class="block text-[10px] text-neutral-500">{{ $b['date_acquired'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-neutral-800 dark:text-neutral-200">{{ $b['supplier_name'] }}</td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                <span class="text-neutral-900 dark:text-white font-bold">{{ $b['sold_pairs'] }}</span> /
                                <span class="text-amber-600">{{ $b['reserved_pairs'] }}</span> /
                                <span class="text-emerald-600">{{ $b['available_pairs'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-neutral-600 dark:text-neutral-400"><x-money :value="$b['total_cost']" /></td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]"><x-money :value="$b['realized_revenue']" /></td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">+₱{{ number_format($b['order_profit_sum'], 2) }}</td>
                            <td class="py-3.5 px-4 text-right font-mono text-rose-500"><x-money :value="$b['batch_expenses']" /></td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold {{ $b['net_proceeds'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-500' }}">
                                {{ $b['net_proceeds'] >= 0 ? '+' : '' }}₱{{ number_format($b['net_proceeds'], 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- By session --}}
        <div x-show="reportTab === 'sessions'" x-cloak>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                        <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                            <th class="py-3 px-4 font-semibold">Date</th>
                            <th class="py-3 px-4 font-semibold text-center">Completed Orders</th>
                            <th class="py-3 px-4 font-semibold text-right">Gross Sales</th>
                            <th class="py-3 px-4 font-semibold text-right">Realized Net Profit</th>
                            <th class="py-3 px-4 font-semibold text-right">Cash Collected</th>
                            <th class="py-3 px-4 font-semibold text-right">GCash Verified</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                        @forelse($sessionReports as $s)
                        <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                            <td class="py-3.5 px-4 font-mono font-bold text-neutral-800 dark:text-neutral-200">{{ $s['date'] }}</td>
                            <td class="py-3.5 px-4 text-center font-mono font-semibold">{{ $s['orders_count'] }}</td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]"><x-money :value="$s['gross_sales']" /></td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">+₱{{ number_format($s['net_profit'], 2) }}</td>
                            <td class="py-3.5 px-4 text-right font-mono text-neutral-700 dark:text-neutral-300"><x-money :value="$s['cash_collected']" /></td>
                            <td class="py-3.5 px-4 text-right font-mono text-[#0071E3] dark:text-[#0A84FF]"><x-money :value="$s['gcash_collected']" /></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="app-empty">
                                    <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="text-xs font-medium">No session transactions match the date range.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- By tier --}}
        <div x-show="reportTab === 'tiers'" x-cloak>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                        <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                            <th class="py-3 px-4 font-semibold">Price Tier</th>
                            <th class="py-3 px-4 font-semibold text-center">Units Sold / Total</th>
                            <th class="py-3 px-4 font-semibold text-right">Gross Sales</th>
                            <th class="py-3 px-4 font-semibold text-right">Allocated COGS</th>
                            <th class="py-3 px-4 font-semibold text-right">Net Profit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                        @foreach($tierReports as $t)
                        <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                            <td class="py-3.5 px-4 font-semibold text-neutral-800 dark:text-neutral-200">{{ $t['tier'] }}</td>
                            <td class="py-3.5 px-4 text-center font-mono">{{ $t['sold_count'] }} / {{ $t['total_items'] }}</td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]"><x-money :value="$t['total_revenue']" /></td>
                            <td class="py-3.5 px-4 text-right font-mono text-neutral-500"><x-money :value="$t['total_cogs']" /></td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">+₱{{ number_format($t['net_profit'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Export modal --}}
    <x-modal eyebrow="Custom Export" title="Build your report" accent="indigo" size="lg" scroll
             close="showExportModal = false"
             x-show="showExportModal" x-cloak @keydown.escape.window="showExportModal = false">
            <form method="GET" action="{{ route('reports.export') }}" data-progress="download" class="space-y-4">
                <div>
                    <label class="app-label">Date Range</label>
                    <select name="preset" x-model="exportPreset" class="app-select">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="last30">Last 30 Days</option>
                        <option value="year">This Year</option>
                        <option value="custom">Custom Range…</option>
                    </select>
                </div>

                <div x-show="exportPreset === 'custom'" x-cloak class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Start Date <span class="app-req">*</span></label>
                        <input type="date" name="start_date" :required="exportPreset === 'custom'" class="app-input">
                    </div>
                    <div>
                        <label class="app-label">End Date <span class="app-req">*</span></label>
                        <input type="date" name="end_date" :required="exportPreset === 'custom'" class="app-input">
                    </div>
                </div>

                <div>
                    <label class="app-label">Content Preset</label>
                    <div class="flex items-center bg-neutral-100 dark:bg-neutral-800 p-1 rounded-xl text-xs">
                        <button type="button" @click="applyContent('all')" :class="exportContent === 'all' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#1D1D1F] dark:text-white' : 'text-neutral-500'" class="flex-1 px-3 py-2 rounded-lg transition-all">Everything</button>
                        <button type="button" @click="applyContent('sales')" :class="exportContent === 'sales' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#1D1D1F] dark:text-white' : 'text-neutral-500'" class="flex-1 px-3 py-2 rounded-lg transition-all">Sales only</button>
                        <button type="button" @click="applyContent('expenses')" :class="exportContent === 'expenses' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#1D1D1F] dark:text-white' : 'text-neutral-500'" class="flex-1 px-3 py-2 rounded-lg transition-all">Expenses only</button>
                    </div>
                </div>

                <div>
                    <label class="app-label">Sections to include</label>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        @foreach([
                            'summary' => 'Sales & Executive Summary',
                            'transactions' => 'Transactions Ledger',
                            'batches' => 'Batch Profitability',
                            'tiers' => 'Price Tier Performance',
                            'expenses' => 'Operating Expenses',
                            'inventory' => 'Inventory Snapshot',
                        ] as $key => $label)
                        <label class="flex items-center gap-2 px-3 py-2 rounded-xl border border-neutral-200 dark:border-neutral-800 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                            <input type="checkbox" name="sections[]" value="{{ $key }}" x-model="exportSections.{{ $key }}" class="w-4 h-4 rounded text-[#0071E3] border-neutral-300 dark:border-neutral-700 focus:ring-[#0071E3]">
                            <span class="text-neutral-700 dark:text-neutral-200">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="button" @click="showExportModal = false" class="app-btn app-btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="app-btn app-btn-indigo flex-1">Download Excel</button>
                </div>
            </form>
    </x-modal>

</div>
@endsection
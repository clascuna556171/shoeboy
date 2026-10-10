@extends('layouts.app')

@section('title', 'Financial & Profit Reports')

@php
    $net = $metrics['net_operating_balance'];
    $netPositive = $net >= 0;
@endphp

@section('content')
<div class="space-y-6" x-data="{
        reportTab: @js(request('reportTab', 'batches')),
        setReportTab(key) {
            this.reportTab = key;
            const u = new URL(location.href);
            u.searchParams.set('reportTab', key);
            history.replaceState(null, '', u);
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

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('backups.index') }}"
               class="px-4 py-2.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm shadow-sm flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-5L9 5H6a2 2 0 00-2 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12v4m-2-2h4"/></svg>
                <span>Backups</span>
            </a>

            <button type="button" @click="$store.exportModal.open = true"
                    data-tour="export"
                    class="px-5 py-2.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm shadow-sm flex items-center gap-2 transition-all shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Report</span>
            </button>
        </div>
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

                <div class="mt-4 divide-y divide-neutral-100 dark:divide-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">
                    <div class="flex items-center justify-between gap-3 px-3 py-2.5">
                        <span class="text-xs font-medium text-neutral-500">Gross Sales</span>
                        <x-money :value="$metrics['total_revenue']" class="font-mono text-sm font-bold text-[#1D1D1F] dark:text-white" />
                    </div>
                    <div class="flex items-center justify-between gap-3 px-3 py-2.5">
                        <span class="text-xs font-medium text-neutral-500">− Allocated COGS</span>
                        <x-money :value="$metrics['total_cogs']" class="font-mono text-sm font-bold text-neutral-800 dark:text-neutral-200" />
                    </div>
                    <div class="flex items-center justify-between gap-3 px-3 py-2.5 bg-emerald-50/40 dark:bg-emerald-950/10">
                        <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">= Gross Profit</span>
                        <x-money :value="$metrics['gross_profit']" class="font-mono text-sm font-bold text-emerald-700 dark:text-emerald-300" />
                    </div>
                    <div class="flex items-center justify-between gap-3 px-3 py-2.5">
                        <span class="text-xs font-medium text-neutral-500">− Store Expenses</span>
                        <x-money :value="$metrics['total_expenses']" class="font-mono text-sm font-bold text-rose-600 dark:text-rose-400" />
                    </div>
                    <div class="flex items-center justify-between gap-3 px-3 py-2.5 bg-neutral-50 dark:bg-neutral-800/40">
                        <span class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Net Balance</span>
                        <span class="font-mono text-base font-bold {{ $netPositive ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400' }}">{{ $net < 0 ? '−' : '' }}₱{{ number_format(abs($net), 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Breakdown tabs --}}
    <div class="app-card" data-tour="list">
        <div class="p-4 border-b border-neutral-200 dark:border-neutral-800">
            <div class="mb-3">
                <div class="text-sm font-semibold text-neutral-900 dark:text-white">Breakdown</div>
                <p class="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">Each view below sums to the totals above.</p>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div data-tour="tabs" class="flex flex-nowrap items-center bg-neutral-100 dark:bg-neutral-800 p-1 rounded-xl text-xs overflow-x-auto max-w-full">
                @foreach(['batches' => 'By Batch Intake', 'sessions' => 'By Session / Date', 'tiers' => 'By Price Tier', 'sales' => 'Sales Ledger', 'expenses' => 'Expenses Ledger'] as $key => $label)
                    <button type="button"
                            @click="setReportTab('{{ $key }}')"
                            :class="reportTab === '{{ $key }}' ? 'bg-white dark:bg-[#2C2C2E] font-bold text-[#1D1D1F] dark:text-white shadow-sm' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
                            class="px-3 py-1.5 rounded-lg transition-all whitespace-nowrap">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-nowrap items-center gap-2 text-xs overflow-x-auto max-w-full">
                <input type="hidden" name="reportTab" :value="reportTab">
                <span class="hidden lg:inline text-neutral-500 shrink-0">Date range</span>
                <input type="date" name="start_date" value="{{ $startDate }}" class="app-input app-input-sm !w-auto shrink-0">
                <span class="text-neutral-500 shrink-0">to</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="app-input app-input-sm !w-auto shrink-0">
                <button type="submit" class="app-btn app-btn-secondary app-btn-sm shrink-0">Filter</button>
                @if($startDate || $endDate)
                    <a href="{{ route('reports.index', ['reportTab' => request('reportTab', 'batches')]) }}" class="app-btn app-btn-secondary app-btn-sm shrink-0">Reset</a>
                @endif
            </form>
            </div>
        </div>

        {{-- By batch --}}
        <div x-show="reportTab === 'batches'">
            <div class="app-list-cards md:grid-cols-2 xl:grid-cols-3 p-4">
                @foreach($batchReports as $b)
                <div class="app-card app-list-card flex flex-col gap-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <span class="font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">{{ $b['batch_code'] }}</span>
                            <div class="mt-0.5 text-[11px] text-neutral-500">{{ $b['date_acquired'] }} · {{ $b['supplier_name'] }}</div>
                        </div>
                        <div class="text-right shrink-0 font-mono font-bold {{ $b['net_proceeds'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-500' }}">{{ $b['net_proceeds'] >= 0 ? '+' : '' }}₱{{ number_format($b['net_proceeds'], 2) }}</div>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="badge badge-sold">{{ $b['sold_pairs'] }} sold</span>
                        <span class="badge badge-reserved">{{ $b['reserved_pairs'] }} reserved</span>
                        <span class="badge badge-available">{{ $b['available_pairs'] }} available</span>
                    </div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                        <div class="flex justify-between"><span class="text-neutral-500">Batch cost</span><span class="font-mono"><x-money :value="$b['total_cost']" /></span></div>
                        <div class="flex justify-between"><span class="text-neutral-500">Sales</span><span class="font-mono"><x-money :value="$b['realized_revenue']" /></span></div>
                        <div class="flex justify-between"><span class="text-neutral-500">Profit</span><span class="font-mono text-emerald-600 dark:text-emerald-400">+₱{{ number_format($b['order_profit_sum'], 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-neutral-500">Expenses</span><span class="font-mono text-rose-500"><x-money :value="$b['batch_expenses']" /></span></div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="app-list-table">
            <div class="app-scroll-x">
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
        </div>

        {{-- By session --}}
        <div x-show="reportTab === 'sessions'" x-cloak>
            <div class="app-list-cards md:grid-cols-2 xl:grid-cols-3 p-4">
                @forelse($sessionReports as $s)
                <div class="app-card app-list-card flex flex-col gap-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <span class="font-mono font-bold text-neutral-800 dark:text-neutral-200">{{ $s['date'] }}</span>
                        <div class="text-right shrink-0 font-mono font-bold text-emerald-600 dark:text-emerald-400">+₱{{ number_format($s['net_profit'], 2) }}</div>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="badge badge-neutral">{{ $s['orders_count'] }} orders</span>
                    </div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                        <div class="flex justify-between"><span class="text-neutral-500">Gross</span><span class="font-mono"><x-money :value="$s['gross_sales']" /></span></div>
                        <div class="flex justify-between"><span class="text-neutral-500">Cash</span><span class="font-mono"><x-money :value="$s['cash_collected']" /></span></div>
                        <div class="flex justify-between"><span class="text-neutral-500">GCash</span><span class="font-mono text-[#0071E3] dark:text-[#0A84FF]"><x-money :value="$s['gcash_collected']" /></span></div>
                    </div>
                </div>
                @empty
                <div class="col-span-full app-empty"><span class="text-xs font-medium">No session transactions match the date range.</span></div>
                @endforelse
            </div>
            <div class="app-list-table">
            <div class="app-scroll-x">
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
        </div>

        {{-- By tier --}}
        <div x-show="reportTab === 'tiers'" x-cloak>
            <div class="app-list-cards md:grid-cols-2 xl:grid-cols-3 p-4">
                @foreach($tierReports as $t)
                <div class="app-card app-list-card flex flex-col gap-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $t['tier'] }}</span>
                        <div class="text-right shrink-0 font-mono font-bold text-emerald-600 dark:text-emerald-400">+₱{{ number_format($t['net_profit'], 2) }}</div>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="badge badge-neutral">{{ $t['sold_count'] }} / {{ $t['total_items'] }} sold</span>
                    </div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                        <div class="flex justify-between"><span class="text-neutral-500">Gross</span><span class="font-mono"><x-money :value="$t['total_revenue']" /></span></div>
                        <div class="flex justify-between"><span class="text-neutral-500">COGS</span><span class="font-mono text-neutral-500"><x-money :value="$t['total_cogs']" /></span></div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="app-list-table">
            <div class="app-scroll-x">
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

        {{-- Sales ledger --}}
        @php($salesGross = collect($salesLedger)->sum('awarded_price'))
        @php($salesProfit = collect($salesLedger)->sum('unit_profit'))
        <div x-show="reportTab === 'sales'" x-cloak>
            <div class="app-list-cards md:grid-cols-2 xl:grid-cols-3 p-4">
                @forelse($salesLedger as $s)
                <div class="app-card app-list-card flex flex-col gap-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <span class="font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">{{ $s['order_number'] }}</span>
                            <div class="mt-0.5 text-[11px] text-neutral-500 whitespace-nowrap">{{ $s['date'] }}</div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="font-mono font-bold text-neutral-900 dark:text-white"><x-money :value="$s['awarded_price']" /></div>
                            <div class="text-[11px] font-mono font-bold {{ $s['unit_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">{{ $s['unit_profit'] >= 0 ? '+' : '−' }}₱{{ number_format(abs($s['unit_profit']), 2) }} profit</div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="badge {{ $s['channel'] === 'Live Stream' ? 'badge-live' : 'badge-pos' }}">{{ $s['channel'] }}</span>
                        <span class="font-mono text-[11px] text-neutral-500">{{ $s['sku'] }}</span>
                    </div>
                    <div class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 truncate">{{ $s['brand_model'] }}</div>
                    <div class="app-list-card-extra grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                        <div class="flex justify-between"><span class="text-neutral-500">Size</span><span class="font-mono">{{ $s['size'] }}</span></div>
                        <div class="flex justify-between"><span class="text-neutral-500">Customer</span><span class="truncate">{{ $s['customer'] }}</span></div>
                        <div class="flex justify-between"><span class="text-neutral-500">Payment</span><span class="uppercase">{{ $s['payment_method'] }}</span></div>
                        <div class="flex justify-between"><span class="text-neutral-500">Staff</span><span class="truncate">{{ $s['staff'] }}</span></div>
                    </div>
                </div>
                @empty
                <div class="col-span-full app-empty"><span class="text-xs font-medium">No individual sales match the date range.</span></div>
                @endforelse
            </div>
            <div class="app-list-table">
            <div class="app-scroll-x">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                        <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                            <th class="py-3 px-4 font-semibold">Order</th>
                            <th class="py-3 px-4 font-semibold">Item</th>
                            <th class="py-3 px-4 font-semibold">Channel</th>
                            <th class="py-3 px-4 font-semibold text-right">Price</th>
                            <th class="py-3 px-4 font-semibold text-right">Profit</th>
                            <th class="py-3 px-4 font-semibold">Customer</th>
                            <th class="py-3 px-4 font-semibold">Settlement</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                        @forelse($salesLedger as $s)
                        <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                            <td class="py-3.5 px-4">
                                <span class="block font-mono font-semibold text-[#0071E3] dark:text-[#0A84FF] whitespace-nowrap">{{ $s['order_number'] }}</span>
                                <span class="text-[11px] text-neutral-500 whitespace-nowrap">{{ $s['date'] }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs text-neutral-500 whitespace-nowrap">{{ $s['sku'] }}</span>
                                    <x-cell :value="$s['brand_model']" class="font-semibold text-neutral-800 dark:text-neutral-200" />
                                </div>
                                <span class="mt-0.5 block font-mono text-[11px] text-neutral-500">{{ $s['size'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap"><span class="badge {{ $s['channel'] === 'Live Stream' ? 'badge-live' : 'badge-pos' }}">{{ $s['channel'] }}</span></td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold"><x-money :value="$s['awarded_price']" /></td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold {{ $s['unit_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">{{ $s['unit_profit'] >= 0 ? '+' : '−' }}₱{{ number_format(abs($s['unit_profit']), 2) }}</td>
                            <td class="py-3.5 px-4">
                                <x-cell :value="$s['customer']" class="text-neutral-700 dark:text-neutral-300" />
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="badge {{ strtolower($s['payment_method']) === 'gcash' ? 'badge-gcash' : 'badge-cash' }}">{{ $s['payment_method'] }}</span>
                                    @if($s['payment_ref'])
                                        <x-cell :value="$s['payment_ref']" class="font-mono text-[11px] text-neutral-500 dark:text-neutral-400" />
                                    @endif
                                </div>
                                <x-cell :value="$s['staff']" muted class="mt-0.5 text-[11px]" />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="app-empty">
                                    <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <span class="text-xs font-medium">No individual sales match the date range.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($salesLedger))
                    <tfoot class="border-t border-neutral-200 dark:border-neutral-800 bg-neutral-50/60 dark:bg-neutral-800/30">
                        <tr>
                            <td colspan="3" class="py-3 px-4 text-right text-xs font-semibold uppercase tracking-wider text-neutral-500">Totals — {{ count($salesLedger) }} {{ count($salesLedger) === 1 ? 'pair' : 'pairs' }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold">₱{{ number_format($salesGross, 2) }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold {{ $salesProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">{{ $salesProfit >= 0 ? '+' : '−' }}₱{{ number_format(abs($salesProfit), 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            </div>
        </div>

        {{-- Expenses ledger --}}
        @php($expenseTotal = collect($expenseLedger)->sum('amount'))
        <div x-show="reportTab === 'expenses'" x-cloak>
            <div class="app-list-cards md:grid-cols-2 xl:grid-cols-3 p-4">
                @forelse($expenseLedger as $e)
                <div class="app-card app-list-card flex flex-col gap-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <x-cell :value="$e['description']" class="font-semibold text-neutral-800 dark:text-neutral-200" />
                        <div class="text-right shrink-0 font-mono font-bold text-rose-600 dark:text-rose-400">−₱{{ number_format($e['amount'], 2) }}</div>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="badge badge-neutral">{{ $e['category'] }}</span>
                        <span class="font-mono text-[11px] text-neutral-500">{{ $e['date'] }}</span>
                    </div>
                    <div class="app-list-card-extra grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                        <div class="flex justify-between"><span class="text-neutral-500">Reference</span><span class="font-mono truncate">{{ $e['reference_no'] ?? '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-neutral-500">Batch</span><span class="font-mono truncate">{{ $e['batch'] }}</span></div>
                    </div>
                </div>
                @empty
                <div class="col-span-full app-empty"><span class="text-xs font-medium">No individual expenses match the date range.</span></div>
                @endforelse
            </div>
            <div class="app-list-table">
            <div class="app-scroll-x">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                        <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                            <th class="py-3 px-4 font-semibold">Date</th>
                            <th class="py-3 px-4 font-semibold">Category</th>
                            <th class="py-3 px-4 font-semibold">Description</th>
                            <th class="py-3 px-4 font-semibold">Reference No</th>
                            <th class="py-3 px-4 font-semibold">Batch</th>
                            <th class="py-3 px-4 font-semibold text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                        @forelse($expenseLedger as $e)
                        <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                            <td class="py-3.5 px-4 font-mono text-neutral-600 dark:text-neutral-400">{{ $e['date'] }}</td>
                            <td class="py-3.5 px-4"><span class="badge badge-neutral">{{ $e['category'] }}</span></td>
                            <td class="py-3.5 px-4 text-neutral-800 dark:text-neutral-200">{{ $e['description'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-neutral-500">{{ $e['reference_no'] ?? '—' }}</td>
                            <td class="py-3.5 px-4 font-mono text-neutral-600 dark:text-neutral-400">{{ $e['batch'] }}</td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-rose-500"><x-money :value="$e['amount']" /></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="app-empty">
                                    <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a4 4 0 00-8 0v2M5 9h14l1 12H4L5 9z"/></svg>
                                    <span class="text-xs font-medium">No individual expenses match the date range.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($expenseLedger))
                    <tfoot class="border-t border-neutral-200 dark:border-neutral-800 bg-neutral-50/60 dark:bg-neutral-800/30">
                        <tr>
                            <td colspan="5" class="py-3 px-4 text-right text-xs font-semibold uppercase tracking-wider text-neutral-500">Total — {{ count($expenseLedger) }} {{ count($expenseLedger) === 1 ? 'entry' : 'entries' }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-rose-500">₱{{ number_format($expenseTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            </div>
        </div>
    </div>

    {{-- Export modal (shared component) --}}
    <x-export-report-modal />

</div>
@endsection
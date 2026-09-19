@extends('layouts.app')

@section('title', 'Financial & Profit Reports')

@section('content')
<div class="space-y-6" x-data="{ reportTab: 'batches' }">


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300">Owner Access Only</span>
                <span class="text-xs text-neutral-400">Financial Intelligence Module</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-[#1D1D1F] dark:text-white mt-1">Consolidated Profit & Sales Analytics</h1>
            <p class="text-xs text-neutral-500 mt-0.5">Order Profit = Awarded Price − Average Item Cost. Restricted strictly to Adrian Dael.</p>
        </div>

        <a href="{{ route('reports.export') }}"
           class="px-5 py-2.5 rounded-2xl bg-[#0071E3] hover:bg-[#0077ED] text-white font-semibold text-xs shadow-sm flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Export to Excel (.csv)</span>
        </a>
    </div>


    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 lg:gap-4">
        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
            <div class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Gross Sales</div>
            <div class="text-lg lg:text-xl font-bold font-mono text-[#1D1D1F] dark:text-white mt-1">₱{{ number_format($metrics['total_revenue'], 2) }}</div>
            <div class="text-[11px] text-neutral-400 mt-0.5">All settled orders</div>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
            <div class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Allocated COGS</div>
            <div class="text-lg lg:text-xl font-bold font-mono text-neutral-800 dark:text-neutral-200 mt-1">₱{{ number_format($metrics['total_cogs'], 2) }}</div>
            <div class="text-[11px] text-neutral-400 mt-0.5">Base outlay + repairs</div>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
            <div class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Gross Footwear Profit</div>
            <div class="text-lg lg:text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-1">₱{{ number_format($metrics['gross_profit'], 2) }}</div>
            <div class="text-[11px] text-neutral-400 mt-0.5">Revenue minus COGS</div>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
            <div class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Store Expenses</div>
            <div class="text-lg lg:text-xl font-bold font-mono text-rose-600 dark:text-rose-400 mt-1">₱{{ number_format($metrics['total_expenses'], 2) }}</div>
            <div class="text-[11px] text-neutral-400 mt-0.5">Shop overhead & freight</div>
        </div>

        <div class="col-span-2 lg:col-span-1 bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
            <div class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Net Operating Balance</div>
            <div class="text-lg lg:text-xl font-bold font-mono mt-1 {{ $metrics['net_operating_balance'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600' }}">
                ₱{{ number_format($metrics['net_operating_balance'], 2) }}
            </div>
            <div class="text-[11px] text-neutral-400 mt-0.5">Realized net proceeds</div>
        </div>
    </div>


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm space-y-5">
        

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-neutral-200/80 dark:border-neutral-800">
            <div class="flex items-center bg-neutral-100 dark:bg-neutral-800 p-1 rounded-xl text-xs">
                <button type="button"
                        @click="reportTab = 'batches'"
                        :class="reportTab === 'batches' ? 'bg-white dark:bg-[#2C2C2E] font-bold text-[#1D1D1F] dark:text-white shadow-sm' : 'text-neutral-500'"
                        class="px-3 py-1.5 rounded-lg transition-all">
                    By Batch Intake
                </button>
                <button type="button"
                        @click="reportTab = 'sessions'"
                        :class="reportTab === 'sessions' ? 'bg-white dark:bg-[#2C2C2E] font-bold text-[#1D1D1F] dark:text-white shadow-sm' : 'text-neutral-500'"
                        class="px-3 py-1.5 rounded-lg transition-all">
                    By Session / Date
                </button>
                <button type="button"
                        @click="reportTab = 'tiers'"
                        :class="reportTab === 'tiers' ? 'bg-white dark:bg-[#2C2C2E] font-bold text-[#1D1D1F] dark:text-white shadow-sm' : 'text-neutral-500'"
                        class="px-3 py-1.5 rounded-lg transition-all">
                    By Price Tier
                </button>
            </div>


            <form x-show="reportTab === 'sessions'" method="GET" action="{{ route('reports.index') }}" class="flex items-center gap-2 text-xs">
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-2.5 py-1 bg-neutral-100 dark:bg-neutral-800 border rounded-lg">
                <span class="text-neutral-400">to</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-2.5 py-1 bg-neutral-100 dark:bg-neutral-800 border rounded-lg">
                <button type="submit" class="px-3 py-1 bg-[#0071E3] text-white rounded-lg font-medium">Filter</button>
            </form>
        </div>


        <div x-show="reportTab === 'batches'">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-[11px] text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800 pb-2">
                        <tr>
                            <th class="py-2.5 px-3">Batch</th>
                            <th class="py-2.5 px-3">Supplier</th>
                            <th class="py-2.5 px-3 text-center">Pairs (S/R/A)</th>
                            <th class="py-2.5 px-3 text-right">Batch Cost</th>
                            <th class="py-2.5 px-3 text-right">Avg Unit Cost</th>
                            <th class="py-2.5 px-3 text-right">Realized Sales</th>
                            <th class="py-2.5 px-3 text-right">Order Profit</th>
                            <th class="py-2.5 px-3 text-right">Expenses</th>
                            <th class="py-2.5 px-3 text-right">Net Proceeds</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60 font-sans">
                        @foreach($batchReports as $b)
                        <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                            <td class="py-3 px-3 font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">
                                {{ $b['batch_code'] }}
                                <span class="block text-[10px] text-neutral-400">{{ $b['date_acquired'] }}</span>
                            </td>
                            <td class="py-3 px-3 font-semibold text-neutral-800 dark:text-neutral-200">{{ $b['supplier_name'] }}</td>
                            <td class="py-3 px-3 text-center font-mono">
                                <span class="text-neutral-900 dark:text-white font-bold">{{ $b['sold_pairs'] }}</span> /
                                <span class="text-amber-600">{{ $b['reserved_pairs'] }}</span> /
                                <span class="text-emerald-600">{{ $b['available_pairs'] }}</span>
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-neutral-600 dark:text-neutral-400">₱{{ number_format($b['total_cost'], 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-neutral-900 dark:text-white">₱{{ number_format($b['average_item_cost'], 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">₱{{ number_format($b['realized_revenue'], 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">+₱{{ number_format($b['order_profit_sum'], 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono text-rose-500">₱{{ number_format($b['batch_expenses'], 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono font-bold {{ $b['net_proceeds'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-500' }}">
                                {{ $b['net_proceeds'] >= 0 ? '+' : '' }}₱{{ number_format($b['net_proceeds'], 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>


        <div x-show="reportTab === 'sessions'" x-cloak>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-[11px] text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800 pb-2">
                        <tr>
                            <th class="py-2.5 px-3">Date</th>
                            <th class="py-2.5 px-3 text-center">Completed Orders</th>
                            <th class="py-2.5 px-3 text-right">Gross Sales</th>
                            <th class="py-2.5 px-3 text-right">Realized Net Profit</th>
                            <th class="py-2.5 px-3 text-right">Cash Collected</th>
                            <th class="py-2.5 px-3 text-right">GCash Verified</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60 font-sans">
                        @forelse($sessionReports as $s)
                        <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                            <td class="py-3 px-3 font-mono font-bold text-neutral-800 dark:text-neutral-200">{{ $s['date'] }}</td>
                            <td class="py-3 px-3 text-center font-mono font-semibold">{{ $s['orders_count'] }}</td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">₱{{ number_format($s['gross_sales'], 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">+₱{{ number_format($s['net_profit'], 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono text-neutral-700 dark:text-neutral-300">₱{{ number_format($s['cash_collected'], 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono text-[#0071E3] dark:text-[#0A84FF]">₱{{ number_format($s['gcash_collected'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-neutral-400">No session transactions match the date range.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


        <div x-show="reportTab === 'tiers'" x-cloak>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-[11px] text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800 pb-2">
                        <tr>
                            <th class="py-2.5 px-3">Price Tier</th>
                            <th class="py-2.5 px-3 text-center">Units Sold / Total</th>
                            <th class="py-2.5 px-3 text-right">Gross Sales</th>
                            <th class="py-2.5 px-3 text-right">Allocated COGS</th>
                            <th class="py-2.5 px-3 text-right">Net Profit</th>
                            <th class="py-2.5 px-3 text-right">Gross Margin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60 font-sans">
                        @foreach($tierReports as $t)
                        <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                            <td class="py-3 px-3 font-semibold text-neutral-800 dark:text-neutral-200">{{ $t['tier'] }}</td>
                            <td class="py-3 px-3 text-center font-mono">{{ $t['sold_count'] }} / {{ $t['total_items'] }}</td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">₱{{ number_format($t['total_revenue'], 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono text-neutral-500">₱{{ number_format($t['total_cogs'], 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">+₱{{ number_format($t['net_profit'], 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $t['margin_percent'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection

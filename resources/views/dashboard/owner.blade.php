@extends('layouts.app')

@section('title', 'Owner Executive Center')

@section('content')
<div class="space-y-6">

    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">Owner Command Center</span>
                <span class="text-xs text-neutral-400 font-mono">Role: {{ auth()->user()->name }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-[#1D1D1F] dark:text-white mt-1">Executive Financial & Operations Overview</h1>
            <p class="text-xs text-neutral-500 mt-0.5">Real-time consolidated profitability, batch unit margins, and staff oversight.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs">
            <a href="{{ route('reports.index') }}"
               class="px-4 py-2 rounded-xl bg-[#0071E3] hover:bg-[#0077ED] text-white font-semibold flex items-center gap-1.5 shadow-sm transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Full Reports</span>
            </a>
            <a href="{{ route('reports.export') }}"
               class="px-4 py-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 text-neutral-800 dark:text-neutral-200 font-semibold flex items-center gap-1.5 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('staff.index') }}"
               class="px-4 py-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 text-neutral-800 dark:text-neutral-200 font-semibold flex items-center gap-1.5 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Manage Staff</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 lg:gap-4">
        
        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Gross Sales</span>
                <div class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-[#0071E3] dark:text-[#0A84FF] flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-lg lg:text-xl font-bold font-mono text-[#1D1D1F] dark:text-white mt-1">₱{{ number_format($metrics['total_revenue'], 2) }}</div>
            <div class="text-[11px] text-neutral-400 mt-0.5">{{ $metrics['sold_inventory'] }} sold pairs</div>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Unit Cost (COGS)</span>
                <div class="w-6 h-6 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-500 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="text-lg lg:text-xl font-bold font-mono text-neutral-800 dark:text-neutral-200 mt-1">₱{{ number_format($metrics['total_cogs'], 2) }}</div>
            <div class="text-[11px] text-neutral-400 mt-0.5">Allocated batch base + repairs</div>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Total Expenses</span>
                <div class="w-6 h-6 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-500 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m0 0l-6-6m6 6H3"/></svg>
                </div>
            </div>
            <div class="text-lg lg:text-xl font-bold font-mono text-rose-600 dark:text-rose-400 mt-1">₱{{ number_format($metrics['total_expenses'], 2) }}</div>
            <div class="text-[11px] text-neutral-400 mt-0.5">Sacks, freight, supplies & shop</div>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Gross Profit (Sales)</span>
                <div class="w-6 h-6 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="text-lg lg:text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-1">₱{{ number_format($metrics['gross_profit'], 2) }}</div>
            <div class="text-[11px] text-neutral-400 mt-0.5">Gross Sales minus COGS</div>
        </div>

        <div class="col-span-2 lg:col-span-1 bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Net Store Balance</span>
                <div class="w-6 h-6 rounded-lg bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="text-lg lg:text-xl font-bold font-mono mt-1 {{ $metrics['net_operating_balance'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                ₱{{ number_format($metrics['net_operating_balance'], 2) }}
            </div>
            <div class="text-[11px] text-neutral-400 mt-0.5">Realized net cashflow</div>
        </div>

    </div>

    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-6 text-xs">
            <div class="flex items-center gap-2">
                <div class="p-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <span class="text-neutral-400 block text-[10px] uppercase font-medium">Cash in Drawer</span>
                    <span class="font-mono font-bold text-sm text-neutral-800 dark:text-neutral-200">₱{{ number_format($metrics['cash_total'], 2) }}</span>
                </div>
            </div>

            <div class="h-8 w-px bg-neutral-200 dark:bg-neutral-800 hidden sm:block"></div>

            <div class="flex items-center gap-2">
                <div class="p-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-[#0071E3] dark:text-[#0A84FF]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <span class="text-neutral-400 block text-[10px] uppercase font-medium">GCash Settled</span>
                    <span class="font-mono font-bold text-sm text-[#0071E3] dark:text-[#0A84FF]">₱{{ number_format($metrics['gcash_total'], 2) }}</span>
                </div>
            </div>

            <div class="h-8 w-px bg-neutral-200 dark:bg-neutral-800 hidden sm:block"></div>

            <div class="flex items-center gap-2">
                <div class="p-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-amber-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-neutral-400 block text-[10px] uppercase font-medium">Active Reservations</span>
                    <span class="font-mono font-bold text-sm text-amber-600 dark:text-amber-400">{{ $metrics['reserved_inventory'] }} pairs</span>
                </div>
            </div>
        </div>

        <div class="text-xs text-neutral-400">
            Available Floor Stock: <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $metrics['available_inventory'] }}</span> / {{ $metrics['total_inventory'] }} pairs
        </div>
    </div>

    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-neutral-200/80 dark:border-neutral-800">
            <div>
                <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Batch Unit Cost & Profitability Matrix</h3>
                <p class="text-[11px] text-neutral-400">Average cost calculation: Total Batch Cost ÷ Total Pairs</p>
            </div>
            <a href="{{ route('batches.index') }}" class="text-xs text-[#0071E3] dark:text-[#0A84FF] font-medium hover:underline flex items-center gap-1">
                <span>View All Batches</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-[11px] text-neutral-400 uppercase tracking-wider border-b border-neutral-200/80 dark:border-neutral-800 pb-2">
                    <tr>
                        <th class="py-2.5 px-3">Batch Code</th>
                        <th class="py-2.5 px-3">Supplier</th>
                        <th class="py-2.5 px-3 text-center">Pairs (S/R/A)</th>
                        <th class="py-2.5 px-3 text-right">Batch Outlay</th>
                        <th class="py-2.5 px-3 text-right">Avg Unit Cost</th>
                        <th class="py-2.5 px-3 text-right">Realized Sales</th>
                        <th class="py-2.5 px-3 text-right">Order Profit</th>
                        <th class="py-2.5 px-3 text-right">Net Proceeds</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60 font-sans">
                    @foreach($batchSummaries as $batch)
                    <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30 transition-colors">
                        <td class="py-3 px-3 font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">
                            <a href="{{ route('batches.show', $batch['batch_id']) }}" class="hover:underline">
                                {{ $batch['batch_code'] }}
                            </a>
                            <span class="block text-[10px] text-neutral-400">{{ $batch['date_acquired'] }}</span>
                        </td>
                        <td class="py-3 px-3">
                            <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $batch['supplier_name'] }}</span>
                            <span class="block text-[10px] text-neutral-400">{{ $batch['total_sacks'] }} sack(s)</span>
                        </td>
                        <td class="py-3 px-3 text-center font-mono">
                            <span class="text-neutral-800 dark:text-neutral-200 font-semibold">{{ $batch['sold_pairs'] }}</span> sold /
                            <span class="text-amber-600">{{ $batch['reserved_pairs'] }}</span> res /
                            <span class="text-emerald-600 font-semibold">{{ $batch['available_pairs'] }}</span> avail
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-neutral-700 dark:text-neutral-300">
                            ₱{{ number_format($batch['total_cost'], 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-neutral-900 dark:text-white">
                            ₱{{ number_format($batch['average_item_cost'], 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-semibold text-[#0071E3] dark:text-[#0A84FF]">
                            ₱{{ number_format($batch['realized_revenue'], 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            +₱{{ number_format($batch['order_profit_sum'], 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold {{ $batch['net_proceeds'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-500' }}">
                            {{ $batch['net_proceeds'] >= 0 ? '+' : '' }}₱{{ number_format($batch['net_proceeds'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-neutral-200/80 dark:border-neutral-800">
                    <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Recent Settled Sales</h3>
                    <a href="{{ route('orders.index') }}" class="text-xs text-[#0071E3] dark:text-[#0A84FF] hover:underline">All Orders</a>
                </div>

                <div class="space-y-3">
                    @forelse($recentTransactions as $tx)
                    <div class="p-3.5 rounded-2xl bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-200/60 dark:border-neutral-800 flex items-start justify-between gap-3 text-xs">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">{{ $tx->order_number }}</span>
                                <span class="text-neutral-400">•</span>
                                <span class="font-semibold text-neutral-800 dark:text-neutral-200 truncate">{{ $tx->item->brand }} {{ $tx->item->model }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-[11px] text-neutral-400 mt-1">
                                <span>Buyer: <strong class="text-neutral-700 dark:text-neutral-300">{{ $tx->customer->name }}</strong></span>
                                <span>•</span>
                                <span>Staff: {{ $tx->staff->name }}</span>
                                <span>•</span>
                                <span>{{ $tx->date_awarded->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="font-mono font-bold text-neutral-900 dark:text-white block">₱{{ number_format($tx->awarded_price, 2) }}</span>
                            <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-semibold font-mono {{ $tx->payment?->method === 'gcash' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300' }}">
                                {{ strtoupper($tx->payment?->method ?? 'Cash') }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-neutral-400 text-xs">No settled transactions yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-neutral-200/80 dark:border-neutral-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Security & Audit Log</h3>
                    </div>
                    <span class="text-[11px] text-neutral-400 font-mono">Live Activity</span>
                </div>

                <div class="space-y-3 max-h-[480px] overflow-y-auto pr-1">
                    @forelse($recentAuditLogs as $log)
                    <div class="p-3 rounded-xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200/60 dark:border-neutral-700/40 text-xs space-y-1">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-mono font-semibold text-[#0071E3] dark:text-[#0A84FF]">{{ $log->action }}</span>
                            <span class="text-neutral-400">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-neutral-600 dark:text-neutral-300 text-[11px]">
                            Actor: <strong class="text-neutral-800 dark:text-white">{{ $log->user?->name ?? 'System' }}</strong>
                            @if($log->details)
                                <div class="mt-1 font-mono text-[10px] text-neutral-500 dark:text-neutral-400 bg-neutral-100 dark:bg-neutral-900 p-1.5 rounded truncate">
                                    {{ json_encode($log->details) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-neutral-400 text-xs">No audit logs recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

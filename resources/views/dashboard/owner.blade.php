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

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('reports.index') }}"
               title="Open the full financial and profitability reports"
               class="group inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#0071E3] hover:bg-[#0077ED] active:bg-[#006EDB] text-white text-xs font-semibold shadow-sm hover:shadow transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0071E3]/50 focus-visible:ring-offset-1 dark:focus-visible:ring-offset-[#1C1C1E]">
                <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Full Reports</span>
            </a>
            <a href="{{ route('reports.export') }}"
               title="Download the current financial report as a CSV file"
               class="group inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-700/60 hover:border-neutral-300 dark:hover:border-neutral-600 text-neutral-700 dark:text-neutral-200 text-xs font-semibold shadow-sm transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-neutral-400/50 focus-visible:ring-offset-1 dark:focus-visible:ring-offset-[#1C1C1E]">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 transition-transform group-hover:translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('staff.index') }}"
               title="Manage staff accounts and access"
               class="group inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-700/60 hover:border-neutral-300 dark:hover:border-neutral-600 text-neutral-700 dark:text-neutral-200 text-xs font-semibold shadow-sm transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-neutral-400/50 focus-visible:ring-offset-1 dark:focus-visible:ring-offset-[#1C1C1E]">
                <svg class="w-4 h-4 text-purple-500 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Manage Staff</span>
            </a>
        </div>
    </div>

    @php
        $net = $metrics['net_operating_balance'];
        $netPositive = $net >= 0;
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 lg:gap-5">

        {{-- The single most important number, with plain-language context --}}
        <div class="lg:col-span-3 rounded-3xl p-6 lg:p-7 shadow-sm border {{ $netPositive ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-200/70 dark:border-emerald-900/50' : 'bg-amber-50/60 dark:bg-amber-950/20 border-amber-200/70 dark:border-amber-900/50' }}">
            <div class="flex items-center justify-between gap-3">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Net Store Balance</span>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $netPositive ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' }}">
                    {{ $netPositive ? 'In profit' : 'Still recovering costs' }}
                </span>
            </div>

            <div class="mt-3 text-3xl lg:text-4xl font-bold font-mono tracking-tight {{ $netPositive ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">
                {{ $net < 0 ? '−' : '' }}₱{{ number_format(abs($net), 2) }}
            </div>

            <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400 max-w-xl">
                What the shop keeps after paying for the shoes it sold and covering running costs.
                @if(! $netPositive)
                    A negative balance usually means unsold stock and setup costs are still being carried, not that the pairs you sold lost money.
                @endif
            </p>

            <div class="mt-5 flex flex-wrap items-center gap-x-2.5 gap-y-2 text-xs">
                <span class="font-mono font-semibold text-neutral-800 dark:text-neutral-200">₱{{ number_format($metrics['total_revenue'], 2) }}</span>
                <span class="text-neutral-400">sales</span>
                <span class="text-neutral-300 dark:text-neutral-600">−</span>
                <span class="font-mono font-semibold text-neutral-800 dark:text-neutral-200">₱{{ number_format($metrics['total_cogs'], 2) }}</span>
                <span class="text-neutral-400">cost of shoes sold</span>
                <span class="text-neutral-300 dark:text-neutral-600">−</span>
                <span class="font-mono font-semibold text-neutral-800 dark:text-neutral-200">₱{{ number_format($metrics['total_expenses'], 2) }}</span>
                <span class="text-neutral-400">shop expenses</span>
            </div>
        </div>

        {{-- Same numbers, ordered top to bottom so the arithmetic is obvious --}}
        <div class="lg:col-span-2 rounded-3xl p-5 shadow-sm bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-neutral-500">How the money moved</h3>
            <dl class="mt-3 space-y-2.5 text-sm">
                <div class="flex items-center justify-between">
                    <dt class="text-neutral-500">Sales collected</dt>
                    <dd class="font-mono font-semibold text-neutral-800 dark:text-neutral-200">₱{{ number_format($metrics['total_revenue'], 2) }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-neutral-500">Cost of shoes sold</dt>
                    <dd class="font-mono text-neutral-500">−₱{{ number_format($metrics['total_cogs'], 2) }}</dd>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-neutral-100 dark:border-neutral-800">
                    <dt class="font-semibold text-neutral-700 dark:text-neutral-300">Profit on sales</dt>
                    <dd class="font-mono font-bold text-emerald-600 dark:text-emerald-400">₱{{ number_format($metrics['gross_profit'], 2) }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-neutral-500">Shop expenses</dt>
                    <dd class="font-mono text-neutral-500">−₱{{ number_format($metrics['total_expenses'], 2) }}</dd>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-neutral-100 dark:border-neutral-800">
                    <dt class="font-semibold text-neutral-700 dark:text-neutral-300">Net balance</dt>
                    <dd class="font-mono font-bold {{ $netPositive ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">{{ $net < 0 ? '−' : '' }}₱{{ number_format(abs($net), 2) }}</dd>
                </div>
            </dl>
        </div>

    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">

        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <span class="text-[11px] uppercase tracking-wider font-medium text-neutral-500">Cash in Drawer</span>
            </div>
            <div class="mt-2 text-lg font-bold font-mono text-neutral-800 dark:text-neutral-100">₱{{ number_format($metrics['cash_total'], 2) }}</div>
            <div class="text-[11px] text-neutral-400 mt-0.5">Physical cash collected</div>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-[#0071E3] dark:text-[#0A84FF] shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <span class="text-[11px] uppercase tracking-wider font-medium text-neutral-500">GCash Settled</span>
            </div>
            <div class="mt-2 text-lg font-bold font-mono text-[#0071E3] dark:text-[#0A84FF]">₱{{ number_format($metrics['gcash_total'], 2) }}</div>
            <div class="text-[11px] text-neutral-400 mt-0.5">Verified digital payments</div>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-[11px] uppercase tracking-wider font-medium text-neutral-500">Active Reservations</span>
            </div>
            <div class="mt-2 text-lg font-bold font-mono text-amber-600 dark:text-amber-400">{{ $metrics['reserved_inventory'] }} <span class="text-xs font-semibold">pairs</span></div>
            <div class="text-[11px] text-neutral-400 mt-0.5">Awaiting payment</div>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <span class="text-[11px] uppercase tracking-wider font-medium text-neutral-500">Available Floor Stock</span>
            </div>
            <div class="mt-2 text-lg font-bold font-mono text-emerald-600 dark:text-emerald-400">{{ $metrics['available_inventory'] }} <span class="text-xs font-semibold text-neutral-400">/ {{ $metrics['total_inventory'] }} pairs</span></div>
            <div class="text-[11px] text-neutral-400 mt-0.5">Ready to sell</div>
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

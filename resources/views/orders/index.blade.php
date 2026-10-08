@extends('layouts.app')

@section('title', 'Orders Registry')

@section('content')
<div class="space-y-6">

    {{-- Page header --}}
    <div class="app-card p-5 lg:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="min-w-0">
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Sales Ledger</div>
                <h1 class="text-xl md:text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-0.5">Customer Orders Registry</h1>
                </div>
        </div>
        <x-header-counts :counts="[
            ['label' => 'Reserved', 'value' => $reservedCount, 'tone' => 'amber'],
            ['label' => 'Paid', 'value' => $paidCount, 'tone' => 'blue'],
            ['label' => 'Fulfilled', 'value' => $fulfilledCount, 'tone' => 'emerald'],
            ['label' => 'Cancelled', 'value' => $cancelledCount, 'tone' => 'rose'],
        ]" />
    </div>

    <x-table-toolbar :action="route('orders.index')" :search="request('search')"
                     search-placeholder="Order #, SKU, customer..."
                     :reset-url="route('orders.index')"
                     :filters="[
                         ['name' => 'status', 'selected' => request('status'), 'options' => ['' => 'All Statuses', 'reserved' => 'Reserved', 'paid' => 'Paid', 'fulfilled' => 'Fulfilled', 'cancelled' => 'Cancelled']],
                         ['name' => 'type', 'selected' => request('type'), 'options' => ['' => 'All Channels', 'live_stream' => 'Live Stream', 'walkin_pos' => 'POS Walk-In']],
                     ]" />

    {{-- Table --}}
    <div class="app-card" data-tour="list">
        <div class="overflow-x-auto lg:overflow-visible">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <x-sort-th-server column="order_number" label="Order & Date" />
                        <th class="py-3 px-4 font-semibold">Items</th>
                        <th class="py-3 px-4 font-semibold">Customer</th>
                        <th class="py-3 px-4 font-semibold">Sold By</th>
                        <x-sort-th-server column="awarded_price" label="Order Total" align="right" />
                        <x-sort-th-server column="status" label="Status" align="center" />
                        <th class="py-3 px-4 font-semibold text-center">Settlement</th>
                    </tr>
                </thead>
                @forelse($orders as $ord)
                @php($isFocused = $focus && $focus === $ord->order_number)
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    <tr id="order-{{ $ord->order_number }}"
                        @if($isFocused) data-scroll-to @endif
                        class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30 {{ $isFocused ? 'ring-2 ring-inset ring-[#0071E3] bg-[#0071E3]/5 dark:bg-[#0A84FF]/10' : '' }}">
                        <td class="py-3.5 px-4">
                            <span class="font-mono font-bold text-[#0071E3] dark:text-[#0A84FF] block">{{ $ord->order_number }}</span>
                            <span class="text-xs text-neutral-500 font-mono">{{ $ord->date_awarded->format('M d, Y H:i') }}</span>
                            <x-status-badge kind="channel" :value="$ord->order_type" class="mt-1" />
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <span class="badge badge-neutral">{{ $ord->items->count() }} {{ $ord->items->count() === 1 ? 'pair' : 'pairs' }}</span>
                                <button type="button" data-panel-url="{{ route('panels.show', ['type' => 'order', 'id' => $ord->id]) }}"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] text-[11px] font-semibold text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>View</span>
                                </button>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $ord->customer->display_handle }}</div>
                            @if($ord->customer->messenger_contact && $ord->customer->messenger_contact !== $ord->customer->name)
                                <span class="text-xs text-neutral-500">{{ $ord->customer->name }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-neutral-700 dark:text-neutral-300">{{ $ord->staff->name }}</td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-neutral-900 dark:text-white"><x-money :value="$ord->awarded_price" /></td>
                        <td class="py-3.5 px-4 text-center">
                            <x-status-badge kind="order" :value="$ord->status" />
                        </td>
                        <td class="py-3.5 px-4 text-center" data-tour="row-actions">
                            @if($ord->payment)
                                <div class="inline-flex flex-col items-center gap-1">
                                    <x-status-badge kind="payment" :value="$ord->payment->method" />
                                    @if($ord->payment->method === 'gcash')
                                        <span class="max-w-[140px] truncate font-mono text-xs text-neutral-500 dark:text-neutral-400">{{ $ord->payment->reference_no }}</span>
                                    @endif
                                    <button type="button" data-panel-url="{{ route('panels.show', ['type' => 'receipt', 'id' => $ord->id]) }}" class="text-[11px] font-semibold text-[#0071E3] dark:text-[#0A84FF] hover:underline">Receipt</button>
                                </div>
                            @elseif($ord->status === 'reserved')
                                <span class="badge badge-pending">Awaiting Payment</span>
                            @else
                                <span class="text-xs text-neutral-500">—</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
                @empty
                <tbody>
                    <tr>
                        <td colspan="7">
                            <div class="app-empty">
                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9"/></svg>
                                <span class="text-xs font-medium">No orders found.</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
                @endforelse
            </table>
        </div>

        @if($orders->hasPages())
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-neutral-800">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

</div>

@endsection

@extends('layouts.app')

@section('title', 'Orders Registry')

@php
    $statusBadge = [
        'fulfilled' => 'badge-fulfilled',
        'paid'      => 'badge-paid',
        'reserved'  => 'badge-pending',
        'cancelled' => 'badge-cancelled',
    ];
@endphp

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
        <span class="badge badge-neutral">{{ $orders->total() }} orders</span>
    </div>

    @include('partials.table-toolbar', [
        'action' => route('orders.index'),
        'search' => request('search'),
        'searchPlaceholder' => 'Order #, SKU, customer...',
        'resetUrl' => route('orders.index'),
        'filters' => [
            ['name' => 'status', 'selected' => request('status'), 'options' => ['' => 'All Statuses', 'reserved' => 'Reserved', 'paid' => 'Paid', 'fulfilled' => 'Fulfilled', 'cancelled' => 'Cancelled']],
            ['name' => 'type', 'selected' => request('type'), 'options' => ['' => 'All Channels', 'live_stream' => 'Live Stream', 'walkin_pos' => 'POS Walk-In']],
        ],
    ])

    {{-- Table --}}
    <div class="app-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        @include('partials.sortable-th', ['column' => 'order_number', 'label' => 'Order & Date'])
                        <th class="py-3 px-4 font-semibold">Items</th>
                        <th class="py-3 px-4 font-semibold">Customer</th>
                        <th class="py-3 px-4 font-semibold">Sold By</th>
                        @include('partials.sortable-th', ['column' => 'awarded_price', 'label' => 'Order Total', 'align' => 'right'])
                        @include('partials.sortable-th', ['column' => 'status', 'label' => 'Status', 'align' => 'center'])
                        <th class="py-3 px-4 font-semibold text-center">Settlement</th>
                    </tr>
                </thead>
                @forelse($orders as $ord)
                @php($isFocused = $focus && $focus === $ord->order_number)
                <tbody x-data="{ open: false }" class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    <tr id="order-{{ $ord->order_number }}"
                        @if($isFocused) data-scroll-to @endif
                        class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30 {{ $isFocused ? 'ring-2 ring-inset ring-[#0071E3] bg-[#0071E3]/5 dark:bg-[#0A84FF]/10' : '' }}">
                        <td class="py-3.5 px-4">
                            <span class="font-mono font-bold text-[#0071E3] dark:text-[#0A84FF] block">{{ $ord->order_number }}</span>
                            <span class="text-xs text-neutral-500 font-mono">{{ $ord->date_awarded->format('M d, Y H:i') }}</span>
                            <span class="badge mt-1 {{ $ord->order_type === 'live_stream' ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' : 'badge-neutral' }}">
                                {{ $ord->order_type === 'live_stream' ? 'Live Stream' : 'POS Walk-In' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <span class="badge badge-neutral">{{ $ord->items->count() }} {{ $ord->items->count() === 1 ? 'pair' : 'pairs' }}</span>
                                <button type="button" @click="open = !open"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] text-[11px] font-semibold text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span x-text="open ? 'Hide' : 'View'"></span>
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
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-neutral-900 dark:text-white">₱{{ number_format($ord->awarded_price, 2) }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="badge {{ $statusBadge[$ord->status] ?? 'badge-neutral' }}">{{ $ord->status }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($ord->payment)
                                <div class="inline-flex flex-col items-center gap-1">
                                    <span class="badge {{ $ord->payment->method === 'gcash' ? 'badge-paid' : 'badge-neutral' }}">{{ strtoupper($ord->payment->method) }}</span>
                                    @if($ord->payment->method === 'gcash')
                                        <span class="max-w-[140px] truncate font-mono text-xs text-neutral-500 dark:text-neutral-400">{{ $ord->payment->reference_no }}</span>
                                    @endif
                                    <a href="{{ route('orders.receipt', $ord) }}" class="text-[11px] font-semibold text-[#0071E3] dark:text-[#0A84FF] hover:underline">Receipt</a>
                                </div>
                            @elseif($ord->status === 'reserved')
                                <span class="badge badge-pending">Awaiting Payment</span>
                            @else
                                <span class="text-xs text-neutral-500">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr x-show="open" x-cloak>
                        <td colspan="7" class="p-0">
                            <div class="border-t border-neutral-200 dark:border-neutral-800 bg-neutral-50/70 dark:bg-neutral-800/20">
                                <div class="px-4 py-2.5 flex items-center justify-between gap-3 border-b border-neutral-200 dark:border-neutral-800">
                                    <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Order Details</span>
                                    @if($ord->payment)
                                    <a href="{{ route('orders.receipt', $ord) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#0071E3] dark:text-[#0A84FF] hover:underline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Print receipt
                                    </a>
                                    @endif
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 p-4">
                                    <div class="lg:col-span-3">
                                        <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500 mb-2">Purchased Pairs ({{ $ord->items->count() }})</div>
                                        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 overflow-hidden divide-y divide-neutral-100 dark:divide-neutral-800/60">
                                            @foreach($ord->items as $item)
                                            <div class="flex items-center gap-3 px-3.5 py-2.5 bg-white dark:bg-[#1C1C1E]">
                                                <span class="w-9 h-9 rounded-xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center shrink-0 text-neutral-500">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 15s1-2 3-2 2.5 1 4 1 3-1 5-1 4 1 6 3v1a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 13c1.5-1 2.5-3 4-3s2 1 3 2 2 2 4 2"/></svg>
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="font-semibold text-neutral-800 dark:text-neutral-200 truncate">{{ $item->brand }} {{ $item->model }}</div>
                                                    <div class="text-xs text-neutral-500 font-mono">{{ $item->sku }} · Size {{ $item->size }} · {{ $item->condition }}</div>
                                                </div>
                                                <span class="font-mono font-semibold text-neutral-900 dark:text-white shrink-0">₱{{ number_format((float) ($item->pivot->awarded_price ?? $item->listed_price), 2) }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="lg:col-span-2 space-y-3">
                                        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] p-3.5">
                                            <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500 mb-1.5">Buyer</div>
                                            <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $ord->customer->display_handle }}</div>
                                            @if($ord->customer->name !== $ord->customer->display_handle)<div class="text-xs text-neutral-500">{{ $ord->customer->name }}</div>@endif
                                            @if($ord->customer->phone)<div class="text-xs text-neutral-500 font-mono">{{ $ord->customer->phone }}</div>@endif
                                        </div>

                                        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] p-3.5">
                                            <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500 mb-1.5">Payment</div>
                                            @if($ord->payment)
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">{{ strtoupper($ord->payment->method) }}</span>
                                                    <span class="font-mono text-sm font-bold text-neutral-900 dark:text-white">₱{{ number_format($ord->payment->amount, 2) }}</span>
                                                </div>
                                                @if($ord->payment->method === 'gcash' && $ord->payment->reference_no)
                                                    <div class="mt-0.5 text-xs text-neutral-500 font-mono break-all">{{ $ord->payment->reference_no }}</div>
                                                @endif
                                            @else
                                                <div class="text-sm text-neutral-500">Unpaid — awaiting settlement</div>
                                            @endif
                                        </div>

                                        @if($ord->notes)
                                        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] p-3.5">
                                            <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500 mb-1.5">Notes</div>
                                            <div class="text-xs text-neutral-600 dark:text-neutral-300">{{ $ord->notes }}</div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
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

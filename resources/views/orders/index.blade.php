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

    {{-- Filters --}}
    <div class="app-card p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-1.5">
            @php
                $tabs = [
                    ['label' => 'All',        'value' => null,        'active' => 'bg-[#1D1D1F] text-white dark:bg-white dark:text-[#1D1D1F]'],
                    ['label' => 'Reserved',   'value' => 'reserved',  'active' => 'bg-amber-500 text-white'],
                    ['label' => 'Paid',       'value' => 'paid',      'active' => 'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900'],
                    ['label' => 'Fulfilled',  'value' => 'fulfilled', 'active' => 'bg-emerald-600 text-white'],
                    ['label' => 'Cancelled',  'value' => 'cancelled', 'active' => 'bg-rose-500 text-white'],
                ];
            @endphp
            @foreach($tabs as $tab)
                <a href="{{ route('orders.index', array_merge(request()->query(), ['status' => $tab['value']])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ request('status') === $tab['value'] || ($tab['value'] === null && !request('status')) ? $tab['active'] : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200/70 dark:hover:bg-neutral-700' }}">
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('orders.index') }}" class="relative w-full lg:w-72">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-neutral-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Order #, SKU, customer..."
                   class="w-full pl-10 pr-4 py-2.5 bg-neutral-100 dark:bg-neutral-800/80 border border-neutral-200 dark:border-neutral-700 rounded-xl text-sm apple-focus-ring">
        </form>
    </div>

    {{-- Table --}}
    <div class="app-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <th class="py-3 px-4 font-semibold">Order &amp; Date</th>
                        <th class="py-3 px-4 font-semibold">Item Specs</th>
                        <th class="py-3 px-4 font-semibold">Customer</th>
                        <th class="py-3 px-4 font-semibold">Staff Awarded</th>
                        <th class="py-3 px-4 font-semibold text-right">Awarded Price</th>
                        <th class="py-3 px-4 font-semibold text-center">Status</th>
                        <th class="py-3 px-4 font-semibold text-center">Settlement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    @forelse($orders as $ord)
                    <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3.5 px-4">
                            <span class="font-mono font-bold text-[#0071E3] dark:text-[#0A84FF] block">{{ $ord->order_number }}</span>
                            <span class="text-xs text-neutral-500 font-mono">{{ $ord->date_awarded->format('M d, Y H:i') }}</span>
                            <span class="badge mt-1 {{ $ord->order_type === 'live_stream' ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' : 'badge-neutral' }}">
                                {{ $ord->order_type === 'live_stream' ? 'Live Stream' : 'POS Walk-In' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $ord->item->brand }} {{ $ord->item->model }}</div>
                            <span class="text-xs text-neutral-500 font-mono">{{ $ord->item->sku }} • Size {{ $ord->item->size }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $ord->customer->name }}</div>
                            <span class="text-xs text-neutral-500 font-mono">{{ $ord->customer->messenger_contact }}</span>
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
                                    <span class="max-w-[140px] truncate font-mono text-xs text-neutral-500 dark:text-neutral-400">{{ $ord->payment->reference_no }}</span>
                                </div>
                            @elseif($ord->status === 'reserved')
                                <span class="badge badge-pending">Awaiting Payment</span>
                            @else
                                <span class="text-xs text-neutral-500">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="app-empty">
                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9"/></svg>
                                <span class="text-xs font-medium">No orders found.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
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

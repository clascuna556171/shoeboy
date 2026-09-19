@extends('layouts.app')

@section('title', 'Orders Registry')

@section('content')
<div class="space-y-6">


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">Sales Ledger</span>
                <span class="text-xs text-neutral-400">Order Management Module</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-[#1D1D1F] dark:text-white mt-1">Customer Orders Registry</h1>
            <p class="text-xs text-neutral-500 mt-0.5">Filter by status: Reserved, Paid, Fulfilled, or Cancelled. Tracks staff award attribution.</p>
        </div>
    </div>


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-neutral-400 font-medium">Status:</span>
            <a href="{{ route('orders.index', array_merge(request()->query(), ['status' => null])) }}"
               class="px-2.5 py-1 rounded-lg {{ !request('status') ? 'bg-[#1D1D1F] text-white dark:bg-white dark:text-[#1D1D1F] font-bold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300' }}">All</a>
            <a href="{{ route('orders.index', array_merge(request()->query(), ['status' => 'reserved'])) }}"
               class="px-2.5 py-1 rounded-lg {{ request('status') === 'reserved' ? 'bg-amber-500 text-white font-bold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300' }}">Reserved</a>
            <a href="{{ route('orders.index', array_merge(request()->query(), ['status' => 'paid'])) }}"
               class="px-2.5 py-1 rounded-lg {{ request('status') === 'paid' ? 'bg-[#0071E3] text-white font-bold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300' }}">Paid</a>
            <a href="{{ route('orders.index', array_merge(request()->query(), ['status' => 'fulfilled'])) }}"
               class="px-2.5 py-1 rounded-lg {{ request('status') === 'fulfilled' ? 'bg-emerald-600 text-white font-bold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300' }}">Fulfilled</a>
            <a href="{{ route('orders.index', array_merge(request()->query(), ['status' => 'cancelled'])) }}"
               class="px-2.5 py-1 rounded-lg {{ request('status') === 'cancelled' ? 'bg-rose-500 text-white font-bold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300' }}">Cancelled</a>
        </div>

        <form method="GET" action="{{ route('orders.index') }}" class="flex items-center gap-2">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Order #, SKU, customer..." class="px-3 py-1 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
            <button type="submit" class="px-3 py-1 bg-[#0071E3] text-white rounded-xl font-medium">Search</button>
        </form>
    </div>


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-[11px] text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800 pb-2">
                    <tr>
                        <th class="py-2.5 px-3">Order Number & Date</th>
                        <th class="py-2.5 px-3">Item Specs</th>
                        <th class="py-2.5 px-3">Customer</th>
                        <th class="py-2.5 px-3">Staff Awarded</th>
                        <th class="py-2.5 px-3 text-right">Awarded Price</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-center">Settlement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60 font-sans">
                    @forelse($orders as $ord)
                    <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3 px-3">
                            <span class="font-mono font-bold text-[#0071E3] dark:text-[#0A84FF] block">{{ $ord->order_number }}</span>
                            <span class="text-[10px] text-neutral-400 font-mono">{{ $ord->date_awarded->format('M d, Y H:i') }}</span>
                            <span class="inline-block px-1.5 py-0.2 rounded text-[9px] uppercase font-semibold {{ $ord->order_type === 'live_stream' ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' : 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' }}">
                                {{ $ord->order_type === 'live_stream' ? 'Live Stream' : 'POS Walk-In' }}
                            </span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $ord->item->brand }} {{ $ord->item->model }}</div>
                            <span class="text-[10px] text-neutral-400 font-mono">{{ $ord->item->sku }} • Size {{ $ord->item->size }}</span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $ord->customer->name }}</div>
                            <span class="text-[10px] text-neutral-400 font-mono">{{ $ord->customer->messenger_contact }}</span>
                        </td>
                        <td class="py-3 px-3 text-neutral-700 dark:text-neutral-300">
                            {{ $ord->staff->name }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-neutral-900 dark:text-white">
                            ₱{{ number_format($ord->awarded_price, 2) }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $ord->status === 'fulfilled' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' :
                                  ($ord->status === 'paid' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' :
                                  ($ord->status === 'reserved' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300')) }}">
                                {{ $ord->status }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if($ord->payment)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-neutral-100 dark:bg-neutral-800 font-mono">
                                    {{ strtoupper($ord->payment->method) }}: {{ $ord->payment->reference_no }}
                                </span>
                            @elseif($ord->status === 'reserved')
                                <span class="text-[10px] text-amber-600 dark:text-amber-400 font-mono">Awaiting Payment</span>
                            @else
                                <span class="text-[10px] text-neutral-400">None</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-neutral-400">No orders found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800">
            {{ $orders->links() }}
        </div>
    </div>

</div>
@endsection

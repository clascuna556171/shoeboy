@extends('layouts.app')

@section('title', 'Deliveries & Fulfillment')

@php
    $statusBadge = [
        'pending'   => 'badge-pending',
        'shipped'   => 'badge-shipped',
        'completed' => 'badge-fulfilled',
    ];
@endphp

@section('content')
<div class="space-y-6">

    {{-- Page header --}}
    <div class="app-card p-5 lg:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="min-w-0">
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Fulfillment Pipeline</div>
                <h1 class="text-xl md:text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-0.5">Delivery &amp; Parcel Dispatch</h1>
                </div>
        </div>

        <div class="flex items-center gap-2 text-xs">
            <div class="px-3 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/50 text-amber-800 dark:text-amber-300">
                <span class="font-mono font-bold">{{ $pendingCount }}</span> Pending
            </div>
            <div class="px-3 py-2 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/50 text-blue-800 dark:text-blue-300">
                <span class="font-mono font-bold">{{ $shippedCount }}</span> Shipped
            </div>
            <div class="px-3 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-300">
                <span class="font-mono font-bold">{{ $completedCount }}</span> Completed
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="app-card p-4 flex flex-col xl:flex-row xl:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-neutral-500 font-medium text-xs mr-0.5">Status:</span>
                @php
                    $statusTabs = [
                        ['label' => 'All',       'value' => null,        'active' => 'bg-[#1D1D1F] text-white dark:bg-white dark:text-[#1D1D1F]'],
                        ['label' => 'Pending',   'value' => 'pending',   'active' => 'bg-amber-500 text-white'],
                        ['label' => 'Shipped',   'value' => 'shipped',   'active' => 'bg-blue-500 text-white'],
                        ['label' => 'Completed', 'value' => 'completed', 'active' => 'bg-emerald-600 text-white'],
                    ];
                @endphp
                @foreach($statusTabs as $tab)
                    <a href="{{ route('deliveries.index', array_merge(request()->query(), ['status' => $tab['value']])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ request('status') === $tab['value'] || ($tab['value'] === null && !request('status')) ? $tab['active'] : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200/70 dark:hover:bg-neutral-700' }}">
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-neutral-500 font-medium text-xs mr-0.5">Method:</span>
                @php
                    $methodTabs = [
                        ['label' => 'All',      'value' => null,           'active' => 'bg-[#1D1D1F] text-white dark:bg-white dark:text-[#1D1D1F]'],
                        ['label' => 'Pickup',   'value' => 'pickup',       'active' => 'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900'],
                        ['label' => 'J&T',      'value' => 'jnt_delivery', 'active' => 'bg-rose-500 text-white'],
                    ];
                @endphp
                @foreach($methodTabs as $tab)
                    <a href="{{ route('deliveries.index', array_merge(request()->query(), ['method' => $tab['value']])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ request('method') === $tab['value'] || ($tab['value'] === null && !request('method')) ? $tab['active'] : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200/70 dark:hover:bg-neutral-700' }}">
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
        <span class="badge badge-neutral self-start xl:self-auto">{{ $deliveries->total() }} parcels</span>
    </div>

    {{-- Table --}}
    <div class="app-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <th class="py-3 px-4 font-semibold">Order &amp; Shoe Specs</th>
                        <th class="py-3 px-4 font-semibold">Recipient Customer</th>
                        <th class="py-3 px-4 font-semibold text-center">Method</th>
                        <th class="py-3 px-4 font-semibold">Tracking / Waybill</th>
                        <th class="py-3 px-4 font-semibold text-center">Status</th>
                        <th class="py-3 px-4 font-semibold text-right">Update</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    @forelse($deliveries as $del)
                    <tr x-data="{
                            orig: { method: @js($del->method), tracking: @js($del->tracking_number), status: @js($del->status) },
                            method: @js($del->method),
                            tracking: @js($del->tracking_number),
                            status: @js($del->status),
                            get changed() {
                                return this.method !== this.orig.method
                                    || this.tracking !== this.orig.tracking
                                    || this.status !== this.orig.status;
                            }
                        }"
                        :class="changed ? 'bg-amber-50/50 dark:bg-amber-950/10' : ''"
                        class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30 transition-colors">
                        <td class="py-3.5 px-4">
                            @php($pairs = $del->order->items)
                            @php($firstPair = $pairs->first())
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">{{ $del->order->order_number }}</span>
                                <a href="{{ route('orders.index', ['focus' => $del->order->order_number]) }}"
                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] text-[11px] font-semibold text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View order
                                </a>
                            </div>
                            <span class="font-semibold text-neutral-800 dark:text-neutral-200 block mt-0.5">{{ $firstPair?->brand }} {{ $firstPair?->model }}</span>
                            <span class="text-xs text-neutral-500 font-mono">{{ $firstPair?->sku }} • Size {{ $firstPair?->size }}</span>
                            @if($pairs->count() > 1)
                                <span class="badge badge-neutral mt-1">+{{ $pairs->count() - 1 }} more pair(s)</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $del->order->customer->name }}</div>
                            <div class="text-xs text-neutral-500 font-mono">{{ $del->order->customer->messenger_contact }}</div>
                            @if($del->order->customer->shipping_address)
                                <div class="text-xs text-neutral-500 truncate max-w-xs">{{ $del->order->customer->shipping_address }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="badge {{ $del->method === 'pickup' ? 'badge-neutral' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' }}">
                                {{ $del->method === 'pickup' ? 'Store Pickup' : 'J&T Express' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-neutral-600 dark:text-neutral-300">
                            {{ $del->tracking_number ?? '—' }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="badge {{ $statusBadge[$del->status] ?? 'badge-neutral' }}">{{ $del->status }}</span>
                            @if($del->date_completed)
                                <span class="block text-[11px] text-neutral-500 mt-1">{{ $del->date_completed->format('M d, H:i') }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <form action="{{ route('deliveries.update', $del->id) }}" method="POST" class="flex items-center justify-end gap-2">
                                @csrf
                                @method('PUT')
                                <div class="flex items-center gap-1.5 rounded-xl border p-1.5 transition-colors"
                                     :class="changed
                                         ? 'border-amber-300 dark:border-amber-900/60 bg-amber-50/40 dark:bg-amber-950/10'
                                         : 'border-neutral-200 dark:border-neutral-700 bg-neutral-50/60 dark:bg-neutral-900/40'">
                                    <select name="method" x-model="method"
                                            class="px-2.5 py-2 bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-700 rounded-lg text-sm apple-focus-ring">
                                        <option value="pickup" {{ $del->method === 'pickup' ? 'selected' : '' }}>Pickup</option>
                                        <option value="jnt_delivery" {{ $del->method === 'jnt_delivery' ? 'selected' : '' }}>J&amp;T</option>
                                    </select>
                                    <input type="text" name="tracking_number" x-model="tracking" placeholder="Tracking #"
                                           class="w-32 px-2.5 py-2 bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-700 rounded-lg text-sm font-mono apple-focus-ring">
                                    <select name="status" x-model="status"
                                            class="px-2.5 py-2 bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-700 rounded-lg text-sm apple-focus-ring">
                                        <option value="pending" {{ $del->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="shipped" {{ $del->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                        <option value="completed" {{ $del->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>

                                <div class="flex flex-col items-end gap-0.5">
                                    <button type="submit" :disabled="!changed"
                                            :class="changed
                                                ? 'bg-neutral-900 text-white hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200'
                                                : 'border border-neutral-200 dark:border-neutral-700 text-neutral-500 dark:text-neutral-500 cursor-not-allowed'"
                                            class="px-3 py-2 rounded-lg font-semibold text-sm whitespace-nowrap transition-colors">
                                        Save
                                    </button>
                                    <span x-show="changed" x-cloak
                                          class="text-[11px] font-medium text-amber-600 dark:text-amber-400">Unsaved</span>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="app-empty">
                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9"/></svg>
                                <span class="text-xs font-medium">No deliveries found.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deliveries->hasPages())
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-neutral-800">
            {{ $deliveries->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
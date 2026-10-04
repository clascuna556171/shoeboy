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

    <x-table-toolbar :action="route('deliveries.index')" :search="$search"
                     search-placeholder="Search order #, buyer, tracking..."
                     :reset-url="route('deliveries.index')"
                     :filters="[
                         ['name' => 'status', 'selected' => request('status'), 'options' => ['' => 'All Statuses', 'pending' => 'Pending', 'shipped' => 'Shipped', 'completed' => 'Completed']],
                         ['name' => 'method', 'selected' => request('method'), 'options' => ['' => 'All Methods', 'pickup' => 'Pickup', 'jnt_delivery' => 'J&T']],
                     ]" />

    {{-- Table --}}
    <div class="app-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <th class="py-3 px-4 font-semibold">Order</th>
                        <th class="py-3 px-4 font-semibold">Recipient</th>
                        <x-sort-th-server column="method" label="Method & Payment" align="center" />
                        <x-sort-th-server column="tracking_number" label="Tracking / Waybill" />
                        <x-sort-th-server column="status" label="Status" align="center" />
                        <th class="py-3 px-4 font-semibold text-right">Update</th>
                    </tr>
                </thead>
                @forelse($deliveries as $del)
                <tbody x-data="{
                        open: false,
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
                    class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30 transition-colors">
                        <td class="py-3.5 px-4">
                            @php($pairs = $del->order->items)
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">{{ $del->order->order_number }}</span>
                                <span class="badge badge-neutral">{{ $pairs->count() }} {{ $pairs->count() === 1 ? 'pair' : 'pairs' }}</span>
                                <button type="button" @click="open = true"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] text-[11px] font-semibold text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Details</span>
                                </button>
                            </div>
                            <span class="block text-xs text-neutral-500 font-mono mt-1">Ordered {{ $del->order->date_awarded?->format('M d, Y H:i') }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $del->order->customer->display_handle }}</div>
                            @if($del->order->customer->messenger_contact && $del->order->customer->messenger_contact !== $del->order->customer->name)
                                <div class="text-xs text-neutral-500">{{ $del->order->customer->name }}</div>
                            @endif
                            @if($del->order->customer->phone)
                                <div class="text-xs text-neutral-500 font-mono">{{ $del->order->customer->phone }}</div>
                            @endif
                            @if($del->order->customer->shipping_address)
                                <div class="text-xs text-neutral-500 truncate max-w-xs">{{ $del->order->customer->shipping_address }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="inline-flex flex-col items-center gap-1">
                                <span class="badge {{ $del->method === 'pickup' ? 'badge-neutral' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' }}">
                                    {{ $del->method === 'pickup' ? 'Store Pickup' : 'J&T Express' }}
                                </span>
                                @if($del->order->payment)
                                    <span class="badge {{ $del->order->payment->method === 'gcash' ? 'badge-paid' : 'badge-neutral' }}">{{ strtoupper($del->order->payment->method) }}</span>
                                @endif
                            </div>
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
                            @if($del->status === 'completed')
                                <div class="flex items-center justify-end">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/60 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 text-xs font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Completed &amp; locked
                                    </span>
                                </div>
                            @else
                            <form action="{{ route('deliveries.update', $del->id) }}" method="POST"
                                  @submit.prevent="status === 'completed'
                                      ? $store.dialog.show({ variant: 'warning', title: 'Complete this delivery?', message: 'Marking as completed is permanent and cannot be undone. The order will be marked as fulfilled.', confirmLabel: 'Complete permanently' }).then(ok => ok && $el.submit())
                                      : $el.submit()"
                                  class="flex flex-wrap items-center justify-end gap-1.5">
                                @csrf
                                @method('PUT')
                                <select name="method" x-model="method" class="app-select app-input-sm !w-auto">
                                    <option value="pickup">Pickup</option>
                                    <option value="jnt_delivery">J&amp;T</option>
                                </select>
                                <input type="text" name="tracking_number" x-model="tracking" placeholder="Tracking #" class="app-input app-input-sm !w-32 font-mono">
                                <select name="status" x-model="status" class="app-select app-input-sm !w-auto">
                                    <option value="pending">Pending</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="completed">Completed</option>
                                </select>
                                <button type="submit" :disabled="!changed"
                                        class="app-btn app-btn-sm app-btn-primary">Save</button>
                                <span x-show="changed" x-cloak class="w-full text-right text-[11px] font-medium text-amber-600 dark:text-amber-400">Unsaved changes</span>
                            </form>
                            @endif
                        </td>
                    </tr>
                    <x-order-modal :order="$del->order" :delivery="$del" x-show="open" close="open = false" />
                </tbody>
                @empty
                <tbody>
                    <tr>
                        <td colspan="6">
                            <div class="app-empty">
                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9"/></svg>
                                <span class="text-xs font-medium">No deliveries found.</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
                @endforelse
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

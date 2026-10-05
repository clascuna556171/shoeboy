@extends('layouts.app')

@section('title', 'Deliveries & Fulfillment')

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

        <x-header-counts :counts="[
            ['label' => 'Pending', 'value' => $pendingCount, 'tone' => 'amber'],
            ['label' => 'Shipped', 'value' => $shippedCount, 'tone' => 'blue'],
            ['label' => 'Completed', 'value' => $completedCount, 'tone' => 'emerald'],
        ]" />
    </div>

    <x-table-toolbar :action="route('deliveries.index')" :search="$search"
                     search-placeholder="Search order #, buyer, tracking..."
                     :reset-url="route('deliveries.index')"
                     :filters="[
                         ['name' => 'status', 'selected' => request('status'), 'options' => ['' => 'All Statuses', 'pending' => 'Pending', 'shipped' => 'Shipped', 'completed' => 'Completed']],
                         ['name' => 'method', 'selected' => request('method'), 'options' => ['' => 'All Methods', 'pickup' => 'Pickup', 'jnt_delivery' => 'J&T']],
                     ]" />

    {{-- Table --}}
    <div class="app-card">
        <div class="overflow-x-auto lg:overflow-visible">
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
                                <button type="button" data-panel-url="{{ route('panels.show', ['type' => 'order', 'id' => $del->order->id]) }}"
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
                                <x-status-badge kind="method" :value="$del->method" />
                                @if($del->order->payment)
                                    <x-status-badge kind="payment" :value="$del->order->payment->method" />
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-neutral-600 dark:text-neutral-300">
                            {{ $del->tracking_number ?? '—' }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <x-status-badge kind="delivery" :value="$del->status" />
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
                                      ? $store.dialog.show({ variant: 'success', icon: 'truck', title: 'Complete this delivery?', message: 'Marking as completed is permanent and cannot be undone. The order will be marked as fulfilled.', confirmLabel: 'Complete permanently' }).then(ok => ok && $el.submit())
                                      : (window.appLoading.start($event.submitter), $el.submit())"
                                  class="relative flex items-center justify-end gap-1.5 whitespace-nowrap">
                                @csrf
                                @method('PUT')
                                <select name="method" x-model="method" @change="if (method === 'pickup') tracking = null" class="app-select app-input-sm !w-auto shrink-0">
                                    <option value="pickup">Pickup</option>
                                    <option value="jnt_delivery">J&amp;T</option>
                                </select>
                                <input type="text" name="tracking_number" x-model="tracking" x-show="method === 'jnt_delivery'"
                                       @if($del->method !== 'jnt_delivery') style="display:none" @endif
                                       :required="method === 'jnt_delivery'" minlength="6" maxlength="40"
                                       placeholder="Tracking / waybill #" class="app-input app-input-sm !w-36 shrink-0 font-mono">
                                <select name="status" x-model="status" class="app-select app-input-sm !w-auto shrink-0">
                                    <option value="pending">Pending</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="completed">Completed</option>
                                </select>
                                <button type="submit" :disabled="!changed"
                                        class="app-btn app-btn-sm app-btn-primary shrink-0">Save</button>
                                <span x-show="changed" x-cloak class="absolute right-0 top-full mt-1 text-[11px] font-medium text-amber-600 dark:text-amber-400">Unsaved changes</span>
                            </form>
                            @endif
                        </td>
                    </tr>
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

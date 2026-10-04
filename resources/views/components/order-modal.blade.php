@props([
    'order',
    'delivery' => null,
])

@php
    $statusBadge = [
        'fulfilled' => 'badge-fulfilled',
        'paid'      => 'badge-paid',
        'reserved'  => 'badge-pending',
        'cancelled' => 'badge-cancelled',
    ];
@endphp

<x-modal eyebrow="Order Details" :title="$order->order_number" accent="neutral" size="lg" scroll {{ $attributes }}>
    <div class="space-y-4 text-sm">

        {{-- Meta --}}
        <div class="flex flex-wrap items-center gap-2">
            <span class="badge {{ $statusBadge[$order->status] ?? 'badge-neutral' }}">{{ $order->status }}</span>
            <span class="badge badge-neutral">{{ $order->order_type === 'live_stream' ? 'Live Stream' : 'POS Walk-In' }}</span>
            <span class="font-mono text-xs text-neutral-500">{{ $order->date_awarded?->format('M d, Y H:i') }}</span>
            <x-money :value="$order->awarded_price" class="ml-auto font-mono text-lg font-bold text-neutral-900 dark:text-white" />
        </div>

        {{-- Purchased pairs --}}
        <div>
            <div class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Purchased Pairs ({{ $order->items->count() }})</div>
            <div class="overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-800 divide-y divide-neutral-100 dark:divide-neutral-800/60">
                @forelse($order->items as $item)
                <div class="flex items-center gap-3 bg-white dark:bg-[#1C1C1E] px-3.5 py-2.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-neutral-100 text-neutral-500 dark:bg-neutral-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 15s1-2 3-2 2.5 1 4 1 3-1 5-1 4 1 6 3v1a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 13c1.5-1 2.5-3 4-3s2 1 3 2 2 2 4 2"/></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-semibold text-neutral-800 dark:text-neutral-200">{{ $item->brand }} {{ $item->model }}</div>
                        <div class="flex items-center gap-1.5 font-mono text-xs text-neutral-500">
                            <x-batch-chip :batch="$item->batch" />
                            <span>{{ $item->sku }} &middot; Size {{ $item->size }} &middot; {{ $item->condition }}</span>
                        </div>
                    </div>
                    <x-money :value="$item->pivot->awarded_price ?? $item->listed_price" class="shrink-0 font-mono font-semibold text-neutral-900 dark:text-white" />
                </div>
                @empty
                <div class="px-3.5 py-3 text-xs text-neutral-500">No pairs recorded.</div>
                @endforelse
            </div>
        </div>

        {{-- Buyer & Payment --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] p-3.5">
                <div class="mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Buyer</div>
                <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $order->customer->display_handle }}</div>
                @if($order->customer->name !== $order->customer->display_handle)
                    <div class="text-xs text-neutral-500">{{ $order->customer->name }}</div>
                @endif
                @if($order->customer->phone)
                    <div class="font-mono text-xs text-neutral-500">{{ $order->customer->phone }}</div>
                @endif
                @if($order->customer->shipping_address)
                    <div class="text-xs text-neutral-500">{{ $order->customer->shipping_address }}</div>
                @endif
            </div>

            <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] p-3.5">
                <div class="mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Payment</div>
                @if($order->payment)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">{{ strtoupper($order->payment->method) }}</span>
                        <x-money :value="$order->payment->amount" class="font-mono text-sm font-bold text-neutral-900 dark:text-white" />
                    </div>
                    @if($order->payment->method === 'gcash' && $order->payment->reference_no)
                        <div class="mt-0.5 break-all font-mono text-xs text-neutral-500">{{ $order->payment->reference_no }}</div>
                    @endif
                @else
                    <div class="text-sm text-neutral-500">Unpaid — awaiting settlement</div>
                @endif
                <div class="mt-2 text-xs text-neutral-500">Sold by <strong class="font-medium text-neutral-700 dark:text-neutral-300">{{ $order->staff?->name ?? '—' }}</strong></div>
            </div>
        </div>

        {{-- Delivery (optional) --}}
        @if($delivery)
        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] p-3.5">
            <div class="mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Delivery</div>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div><span class="text-neutral-500">Method</span><div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $delivery->method === 'pickup' ? 'Store Pickup' : 'J&T Express' }}</div></div>
                <div><span class="text-neutral-500">Status</span><div class="font-semibold capitalize text-neutral-800 dark:text-neutral-200">{{ $delivery->status }}</div></div>
                <div><span class="text-neutral-500">Tracking</span><div class="font-mono text-neutral-800 dark:text-neutral-200">{{ $delivery->tracking_number ?? '—' }}</div></div>
                @if($delivery->date_completed)
                <div><span class="text-neutral-500">Completed</span><div class="text-neutral-800 dark:text-neutral-200">{{ $delivery->date_completed->format('M d, Y H:i') }}</div></div>
                @endif
            </div>
        </div>
        @endif

        {{-- Notes --}}
        @if($order->notes)
        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] p-3.5">
            <div class="mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Notes</div>
            <div class="text-xs text-neutral-600 dark:text-neutral-300">{{ $order->notes }}</div>
        </div>
        @endif

        {{-- Actions --}}
        @if($order->payment)
        <div class="flex justify-end">
            <a href="{{ route('orders.receipt', $order) }}" class="app-btn app-btn-secondary app-btn-sm">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Print receipt
            </a>
        </div>
        @endif

    </div>
</x-modal>

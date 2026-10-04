@props(['order'])

<div class="app-receipt-printable">
    <div class="text-center border-b border-dashed border-neutral-300 dark:border-neutral-700 pb-4">
        <div class="text-lg font-bold tracking-tight text-neutral-900 dark:text-white">THE SHOE BOY</div>
        <div class="text-[11px] uppercase tracking-wider text-neutral-500">Davao &middot; Sneakers &amp; Streetwear</div>
    </div>

    <div class="grid grid-cols-2 gap-y-1 text-xs py-4 border-b border-dashed border-neutral-300 dark:border-neutral-700">
        <span class="text-neutral-500">Receipt No.</span>
        <span class="text-right font-mono font-bold text-neutral-900 dark:text-white">{{ $order->order_number }}</span>
        <span class="text-neutral-500">Date</span>
        <span class="text-right font-mono text-neutral-700 dark:text-neutral-200">{{ $order->date_awarded?->format('M d, Y H:i') }}</span>
        <span class="text-neutral-500">Channel</span>
        <span class="text-right text-neutral-700 dark:text-neutral-200">{{ $order->order_type === 'walkin_pos' ? 'Walk-In POS' : 'Live Stream' }}</span>
        <span class="text-neutral-500">Buyer</span>
        <span class="text-right font-semibold text-neutral-900 dark:text-white">{{ $order->customer?->display_handle ?? '—' }}</span>
        <span class="text-neutral-500">Served by</span>
        <span class="text-right text-neutral-700 dark:text-neutral-200">{{ $order->staff?->name ?? '—' }}</span>
    </div>

    <div class="py-4 border-b border-dashed border-neutral-300 dark:border-neutral-700">
        <table class="w-full text-xs">
            <thead>
                <tr class="text-neutral-500 uppercase tracking-wider text-[10px]">
                    <th class="text-left font-semibold pb-2">Item</th>
                    <th class="text-center font-semibold pb-2">Qty</th>
                    <th class="text-right font-semibold pb-2">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @foreach($order->items as $item)
                <tr>
                    <td class="py-2">
                        <div class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $item->brand }} {{ $item->model }}</div>
                        <div class="text-neutral-500 font-mono text-[10px]">{{ $item->sku }} &middot; Size {{ $item->size }}</div>
                    </td>
                    <td class="py-2 text-center font-mono text-neutral-700 dark:text-neutral-200">1</td>
                    <td class="py-2 text-right font-mono text-neutral-800 dark:text-neutral-100"><x-money :value="$item->pivot->awarded_price ?? $item->listed_price" /></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="py-4 space-y-1 text-sm border-b border-dashed border-neutral-300 dark:border-neutral-700">
        <div class="flex items-center justify-between">
            <span class="text-neutral-500">Total</span>
            <x-money :value="$order->awarded_price" class="font-mono text-lg font-bold text-neutral-900 dark:text-white" />
        </div>
        <div class="flex items-center justify-between text-xs">
            <span class="text-neutral-500">Payment</span>
            <span class="font-semibold uppercase text-neutral-700 dark:text-neutral-200">{{ $order->payment?->method ?? 'Unpaid' }}</span>
        </div>
        @if($order->payment?->reference_no)
        <div class="flex items-center justify-between text-xs">
            <span class="text-neutral-500">Reference</span>
            <span class="font-mono text-neutral-700 dark:text-neutral-200">{{ $order->payment->reference_no }}</span>
        </div>
        @endif
        @if($order->payment)
        <div class="flex items-center justify-between text-xs">
            <span class="text-neutral-500">Amount Paid</span>
            <span class="font-mono font-bold text-neutral-900 dark:text-white"><x-money :value="$order->payment->amount" /></span>
        </div>
        @php($balance = round((float) $order->awarded_price - (float) $order->payment->amount, 2))
        @if(abs($balance) > 0.005)
        <div class="flex items-center justify-between text-xs">
            <span class="text-neutral-500">{{ $balance > 0 ? 'Balance Due' : 'Overpaid' }}</span>
            <span class="font-mono font-bold {{ $balance > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' }}"><x-money :value="abs($balance)" /></span>
        </div>
        @endif
        @if($order->payment->date_paid)
        <div class="flex items-center justify-between text-xs">
            <span class="text-neutral-500">Paid on</span>
            <span class="font-mono text-neutral-700 dark:text-neutral-200">{{ $order->payment->date_paid->format('M d, Y H:i') }}</span>
        </div>
        @endif
        @if($order->payment->verifier)
        <div class="flex items-center justify-between text-xs">
            <span class="text-neutral-500">Verified by</span>
            <span class="text-neutral-700 dark:text-neutral-200">{{ $order->payment->verifier->name }}</span>
        </div>
        @endif
        @endif
    </div>

    @if($order->notes)
    <p class="pt-3 text-[11px] text-neutral-500">{{ $order->notes }}</p>
    @endif

    <div class="text-center text-[11px] text-neutral-500 pt-5">
        Thank you for your purchase. This serves as your official receipt.
    </div>
</div>

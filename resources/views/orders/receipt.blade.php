<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $order->order_number }} - The Shoe Boy</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F5F5F7] text-[#1D1D1F] font-sans antialiased min-h-screen py-8 print:bg-white print:py-0">

    <div class="max-w-md mx-auto px-4 print:max-w-none print:px-0">

        {{-- Controls --}}
        <div class="flex items-center justify-between gap-2 mb-4 print:hidden">
            <a href="{{ url()->previous() }}"
               class="px-4 py-2 rounded-xl border border-neutral-200 bg-white hover:bg-neutral-50 text-neutral-700 font-semibold text-sm shadow-sm transition-colors">
                Back
            </a>
            <button type="button" data-print
                    class="px-4 py-2 rounded-xl bg-neutral-900 text-white hover:bg-neutral-800 font-semibold text-sm shadow-sm transition-colors">
                Print receipt
            </button>
        </div>

        {{-- Receipt --}}
        <div class="bg-white rounded-2xl border border-neutral-200 p-6 shadow-sm print:border-0 print:shadow-none print:rounded-none">
            <div class="text-center border-b border-dashed border-neutral-300 pb-4">
                <div class="text-lg font-bold tracking-tight">THE SHOE BOY</div>
                <div class="text-[11px] uppercase tracking-wider text-neutral-500">Davao · Sneakers &amp; Streetwear</div>
            </div>

            <div class="grid grid-cols-2 gap-y-1 text-xs py-4 border-b border-dashed border-neutral-300">
                <span class="text-neutral-500">Receipt No.</span>
                <span class="text-right font-mono font-bold">{{ $order->order_number }}</span>
                <span class="text-neutral-500">Date</span>
                <span class="text-right font-mono">{{ $order->date_awarded?->format('M d, Y H:i') }}</span>
                <span class="text-neutral-500">Channel</span>
                <span class="text-right">{{ $order->order_type === 'walkin_pos' ? 'Walk-In POS' : 'Live Stream' }}</span>
                <span class="text-neutral-500">Buyer</span>
                <span class="text-right font-semibold">{{ $order->customer?->display_handle ?? '—' }}</span>
                <span class="text-neutral-500">Served by</span>
                <span class="text-right">{{ $order->staff?->name ?? '—' }}</span>
            </div>

            <div class="py-4 border-b border-dashed border-neutral-300">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-neutral-500 uppercase tracking-wider text-[10px]">
                            <th class="text-left font-semibold pb-2">Item</th>
                            <th class="text-center font-semibold pb-2">Qty</th>
                            <th class="text-right font-semibold pb-2">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach($order->items as $item)
                        <tr>
                            <td class="py-2">
                                <div class="font-semibold">{{ $item->brand }} {{ $item->model }}</div>
                                <div class="text-neutral-500 font-mono text-[10px]">{{ $item->sku }} · Size {{ $item->size }}</div>
                            </td>
                            <td class="py-2 text-center font-mono">1</td>
                            <td class="py-2 text-right font-mono">₱{{ number_format((float) ($item->pivot->awarded_price ?? $item->listed_price), 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="py-4 space-y-1 text-sm border-b border-dashed border-neutral-300">
                <div class="flex items-center justify-between">
                    <span class="text-neutral-500">Total</span>
                    <span class="font-mono text-lg font-bold">₱{{ number_format($order->awarded_price, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-neutral-500">Payment</span>
                    <span class="font-semibold uppercase">{{ $order->payment?->method ?? 'Unpaid' }}</span>
                </div>
                @if($order->payment?->reference_no)
                <div class="flex items-center justify-between text-xs">
                    <span class="text-neutral-500">Reference</span>
                    <span class="font-mono">{{ $order->payment->reference_no }}</span>
                </div>
                @endif
            </div>

            @if($order->notes)
            <p class="pt-3 text-[11px] text-neutral-500">{{ $order->notes }}</p>
            @endif

            <div class="text-center text-[11px] text-neutral-500 pt-5">
                Thank you for your purchase. This serves as your official receipt.
            </div>
        </div>

    </div>

</body>
</html>

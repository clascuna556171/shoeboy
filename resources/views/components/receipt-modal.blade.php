@props([
    'order',
])

<x-modal eyebrow="Receipt" :title="$order->order_number" accent="neutral" size="md" scroll {{ $attributes }}>
    <x-receipt-body :order="$order" />

    <div class="mt-4 flex justify-end print:hidden">
        <button type="button" data-print class="app-btn app-btn-primary app-btn-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print receipt
        </button>
    </div>
</x-modal>

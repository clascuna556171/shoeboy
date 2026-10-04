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
            <x-receipt-body :order="$order" />
        </div>

    </div>

</body>
</html>

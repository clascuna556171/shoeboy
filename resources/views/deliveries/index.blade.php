@extends('layouts.app')

@section('title', 'Deliveries & Fulfillment')

@section('content')
<div class="space-y-6">


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300">Fulfillment Pipeline</span>
                <span class="text-xs text-neutral-400">J&T Express & Counter Pickup Logistics</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-[#1D1D1F] dark:text-white mt-1">Delivery & Parcel Dispatch</h1>
            <p class="text-xs text-neutral-500 mt-0.5">Track parcel preparation, waybill tracking numbers, and completion timestamps.</p>
        </div>


        <div class="flex items-center gap-3 text-xs">
            <div class="px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 text-amber-800 dark:text-amber-300 font-mono">
                <span class="font-bold">{{ $pendingCount }}</span> Pending
            </div>
            <div class="px-3 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 text-blue-800 dark:text-blue-300 font-mono">
                <span class="font-bold">{{ $shippedCount }}</span> Shipped
            </div>
            <div class="px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 text-emerald-800 dark:text-emerald-300 font-mono">
                <span class="font-bold">{{ $completedCount }}</span> Completed
            </div>
        </div>
    </div>


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-neutral-400 font-medium">Status:</span>
            <a href="{{ route('deliveries.index', array_merge(request()->query(), ['status' => null])) }}"
               class="px-2.5 py-1 rounded-lg {{ !request('status') ? 'bg-[#1D1D1F] text-white dark:bg-white dark:text-[#1D1D1F] font-bold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300' }}">All</a>
            <a href="{{ route('deliveries.index', array_merge(request()->query(), ['status' => 'pending'])) }}"
               class="px-2.5 py-1 rounded-lg {{ request('status') === 'pending' ? 'bg-amber-500 text-white font-bold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300' }}">Pending</a>
            <a href="{{ route('deliveries.index', array_merge(request()->query(), ['status' => 'shipped'])) }}"
               class="px-2.5 py-1 rounded-lg {{ request('status') === 'shipped' ? 'bg-blue-500 text-white font-bold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300' }}">Shipped</a>
            <a href="{{ route('deliveries.index', array_merge(request()->query(), ['status' => 'completed'])) }}"
               class="px-2.5 py-1 rounded-lg {{ request('status') === 'completed' ? 'bg-emerald-600 text-white font-bold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300' }}">Completed</a>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-neutral-400 font-medium">Method:</span>
            <a href="{{ route('deliveries.index', array_merge(request()->query(), ['method' => null])) }}"
               class="px-2.5 py-1 rounded-lg {{ !request('method') ? 'bg-[#1D1D1F] text-white dark:bg-white dark:text-[#1D1D1F] font-bold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300' }}">All</a>
            <a href="{{ route('deliveries.index', array_merge(request()->query(), ['method' => 'pickup'])) }}"
               class="px-2.5 py-1 rounded-lg {{ request('method') === 'pickup' ? 'bg-[#0071E3] text-white font-bold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300' }}">Pickup</a>
            <a href="{{ route('deliveries.index', array_merge(request()->query(), ['method' => 'jnt_delivery'])) }}"
               class="px-2.5 py-1 rounded-lg {{ request('method') === 'jnt_delivery' ? 'bg-rose-500 text-white font-bold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300' }}">J&T Express</a>
        </div>
    </div>


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-[11px] text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800 pb-2">
                    <tr>
                        <th class="py-2.5 px-3">Order & Shoe Specs</th>
                        <th class="py-2.5 px-3">Recipient Customer</th>
                        <th class="py-2.5 px-3 text-center">Fulfillment Method</th>
                        <th class="py-2.5 px-3">Tracking / Waybill</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-right">Update Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60 font-sans">
                    @forelse($deliveries as $del)
                    <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3.5 px-3">
                            <span class="font-mono font-bold text-[#0071E3] dark:text-[#0A84FF] block">{{ $del->order->order_number }}</span>
                            <span class="font-semibold text-neutral-800 dark:text-neutral-200 block">{{ $del->order->item->brand }} {{ $del->order->item->model }}</span>
                            <span class="text-[10px] text-neutral-400 font-mono">{{ $del->order->item->sku }} • Size {{ $del->order->item->size }}</span>
                        </td>
                        <td class="py-3.5 px-3">
                            <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $del->order->customer->name }}</div>
                            <div class="text-[10px] text-neutral-400 font-mono">{{ $del->order->customer->messenger_contact }}</div>
                            @if($del->order->customer->shipping_address)
                                <div class="text-[10px] text-neutral-500 truncate max-w-xs">{{ $del->order->customer->shipping_address }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $del->method === 'pickup' ? 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400' }}">
                                {{ $del->method === 'pickup' ? 'Store Pickup' : 'J&T Express' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-3 font-mono">
                            {{ $del->tracking_number ?? 'Not assigned' }}
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $del->status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' :
                                  ($del->status === 'shipped' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' :
                                  'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300') }}">
                                {{ $del->status }}
                            </span>
                            @if($del->date_completed)
                                <span class="block text-[9px] text-neutral-400 mt-0.5">{{ $del->date_completed->format('M d, H:i') }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-right">
                            <form action="{{ route('deliveries.update', $del->id) }}" method="POST" class="inline-flex items-center gap-1.5">
                                @csrf
                                @method('PUT')
                                <select name="method" class="px-2 py-1 bg-neutral-100 dark:bg-neutral-800 border rounded-lg text-xs">
                                    <option value="pickup" {{ $del->method === 'pickup' ? 'selected' : '' }}>Pickup</option>
                                    <option value="jnt_delivery" {{ $del->method === 'jnt_delivery' ? 'selected' : '' }}>J&T</option>
                                </select>
                                <input type="text" name="tracking_number" value="{{ $del->tracking_number }}" placeholder="Tracking #" class="w-24 px-2 py-1 bg-neutral-100 dark:bg-neutral-800 border rounded-lg text-xs font-mono">
                                <select name="status" class="px-2 py-1 bg-neutral-100 dark:bg-neutral-800 border rounded-lg text-xs font-semibold">
                                    <option value="pending" {{ $del->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="shipped" {{ $del->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="completed" {{ $del->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                                <button type="submit" class="px-2.5 py-1 bg-[#0071E3] text-white rounded-lg font-semibold hover:bg-[#0077ED] transition-colors">
                                    Save
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-neutral-400">No deliveries found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800">
            {{ $deliveries->links() }}
        </div>
    </div>

</div>
@endsection

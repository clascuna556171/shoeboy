@extends('layouts.app')

@section('title', 'Staff Operations Console')

@section('content')
<div class="space-y-6"
     x-data="{
         activeTab: 'claims', // 'claims', 'pos', 'triage'
         activeBatchCode: '{{ $activeBatch?->batch_code ?? 'B04' }}',
         
         // Live Claims State
         claimInput: '',
         buyerName: '',
         buyerHandle: '',
         selectedClaimShoe: null,
         shoes: {{ Js::from($items) }},
         
         // POS State
         posSearch: '',
         posBrandFilter: 'All',
         posCart: [],
         posDiscount: 0,
         posDiscountNote: '',
         posPaymentMethod: 'cash',
         posCashTendered: '',
         posGcashRef: '',

         // Payment Modal State
         showPayModal: false,
         payOrder: null,
         payAmount: '',
         payMethod: 'gcash',
         payRef: '',

         handleSearch(query) {
             if (!query || !query.trim()) {
                 this.selectedClaimShoe = null;
                 return;
             }
             const q = query.trim().toUpperCase();
             this.selectedClaimShoe = this.shoes.find(s => s.sku.toUpperCase() === q) ||
                                     this.shoes.find(s => s.sku.toUpperCase().includes(q)) ||
                                     this.shoes.find(s => `${s.brand} ${s.model}`.toUpperCase().includes(q)) || null;
         },

         addToPos(shoe) {
             if (this.posCart.some(i => i.id === shoe.id)) return;
             this.posCart.push(shoe);
         },
         removeFromPos(id) {
             this.posCart = this.posCart.filter(i => i.id !== id);
         },
         get posSubtotal() {
             return this.posCart.reduce((acc, i) => acc + parseFloat(i.listed_price), 0);
         },
         get posFinal() {
             return Math.max(0, this.posSubtotal - (parseFloat(this.posDiscount) || 0));
         },
         get posChange() {
             if (this.posPaymentMethod !== 'cash') return 0;
             const t = parseFloat(this.posCashTendered) || 0;
             return Math.max(0, t - this.posFinal);
         },

         openPaymentModal(orderId, orderNo, amount) {
             this.payOrder = { id: orderId, number: orderNo };
             this.payAmount = amount;
             this.payMethod = 'gcash';
             this.payRef = '';
             this.showPayModal = true;
         }
     }">

     {{-- Header ug selector --}}
    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Live Operations Console</span>
                <span class="text-neutral-400 text-xs">•</span>
                <span class="text-xs text-neutral-500">Active Batch: <strong>{{ $activeBatch?->batch_code }}</strong> ({{ $activeBatch?->supplier?->name }})</span>
            </div>
            <h1 class="text-lg md:text-xl font-bold text-[#1D1D1F] dark:text-white mt-1">Multi-Channel Order & Claim Console</h1>
        </div>

        <div class="flex items-center bg-neutral-200/70 dark:bg-[#2C2C2E] p-1 rounded-2xl border border-neutral-300/60 dark:border-neutral-700 text-xs">
            <button type="button"
                    @click="activeTab = 'claims'"
                    :class="activeTab === 'claims' ? 'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-white font-bold shadow-sm' : 'text-neutral-600 dark:text-neutral-400'"
                    class="px-4 py-2 rounded-xl transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span>Live Claims</span>
            </button>

            <button type="button"
                    @click="activeTab = 'pos'"
                    :class="activeTab === 'pos' ? 'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-white font-bold shadow-sm' : 'text-neutral-600 dark:text-neutral-400'"
                    class="px-4 py-2 rounded-xl transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-[#0071E3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>Walk-in POS</span>
                <template x-if="posCart.length > 0">
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono bg-[#0071E3] text-white" x-text="posCart.length"></span>
                </template>
            </button>

            <button type="button"
                    @click="activeTab = 'triage'"
                    :class="activeTab === 'triage' ? 'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-white font-bold shadow-sm' : 'text-neutral-600 dark:text-neutral-400'"
                    class="px-4 py-2 rounded-xl transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Triage Table</span>
            </button>
        </div>
    </div>

    {{-- Tab 1: Live Selling Claims --}}
    <div x-show="activeTab === 'claims'" x-cloak class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <div class="lg:col-span-7 space-y-4">
                
                {{-- Pangita pinaagi sa short-code --}}
                <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-neutral-500 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#0071E3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Shoe Short-Code Lookup
                        </span>
                        <span class="text-[11px] text-neutral-400 font-mono">Scan barcode or type code</span>
                    </div>

                    <div class="relative">
                        <input type="text"
                               x-model="claimInput"
                               @input="handleSearch(claimInput)"
                               placeholder="Type short code (e.g. B04-001, Panda, Kobe, Wade)..."
                               class="w-full px-4 py-3.5 bg-neutral-100 dark:bg-neutral-800/80 border border-neutral-200 dark:border-neutral-700 rounded-2xl text-sm font-medium text-[#1D1D1F] dark:text-white placeholder-neutral-400 apple-focus-ring">
                    </div>

                    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                        <span class="text-[11px] text-neutral-400 shrink-0">Available pairs:</span>
                        <template x-for="shoe in shoes.filter(s => s.status === 'available').slice(0, 6)" :key="shoe.id">
                            <button @click="claimInput = shoe.sku; handleSearch(shoe.sku)"
                                    type="button"
                                    class="px-2.5 py-1 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:bg-[#0071E3] hover:text-white transition-all font-mono text-[11px]">
                                <span x-text="shoe.sku"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <template x-if="selectedClaimShoe">
                    <div class="bg-white dark:bg-[#1C1C1E] border-2 rounded-3xl p-6 shadow-md transition-all space-y-5"
                         :class="{
                             'border-emerald-500/40': selectedClaimShoe.status === 'available',
                             'border-amber-500/40': selectedClaimShoe.status === 'reserved',
                             'border-neutral-300 dark:border-neutral-700': selectedClaimShoe.status === 'sold'
                         }">
                        
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-sm text-[#0071E3] dark:text-[#0A84FF]" x-text="selectedClaimShoe.sku"></span>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold uppercase"
                                          :class="'badge-' + selectedClaimShoe.status"
                                          x-text="selectedClaimShoe.status"></span>
                                    <span class="text-xs text-neutral-400" x-text="selectedClaimShoe.category"></span>
                                </div>
                                <h3 class="font-bold text-lg text-[#1D1D1F] dark:text-white mt-1" x-text="selectedClaimShoe.brand + ' ' + selectedClaimShoe.model"></h3>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-bold font-mono text-[#1D1D1F] dark:text-white" x-text="'₱' + Number(selectedClaimShoe.listed_price).toLocaleString()"></div>
                                <div class="text-[11px] text-neutral-400">Listed Price</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 p-3.5 rounded-2xl bg-neutral-100/70 dark:bg-neutral-800/60 text-xs">
                            <div>
                                <span class="text-[11px] text-neutral-400 block">Size:</span>
                                <span class="font-semibold text-neutral-800 dark:text-neutral-200 font-mono" x-text="selectedClaimShoe.size"></span>
                            </div>
                            <div>
                                <span class="text-[11px] text-neutral-400 block">Condition:</span>
                                <span class="font-semibold text-neutral-800 dark:text-neutral-200" x-text="selectedClaimShoe.condition"></span>
                            </div>
                            <div>
                                <span class="text-[11px] text-neutral-400 block">Price Tier:</span>
                                <span class="text-neutral-700 dark:text-neutral-300" x-text="selectedClaimShoe.price_tier"></span>
                            </div>
                        </div>

                        {{-- I-award sa customer --}}
                        <template x-if="selectedClaimShoe.status === 'available'">
                            <form action="{{ route('orders.award') }}" method="POST" class="space-y-4 pt-1">
                                @csrf
                                <input type="hidden" name="item_id" :value="selectedClaimShoe.id">
                                <input type="hidden" name="order_type" value="live_stream">

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div class="space-y-1">
                                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Buyer FB Handle:</label>
                                        <input type="text"
                                               name="messenger_contact"
                                               required
                                               placeholder="@username (e.g. @ken_hoops23)"
                                               class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-sm apple-focus-ring">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Customer Full Name:</label>
                                        <input type="text"
                                               name="customer_name"
                                               required
                                               placeholder="e.g. Ken Hoops"
                                               class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-sm apple-focus-ring">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div class="space-y-1">
                                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Awarded Price (₱):</label>
                                        <input type="number"
                                               step="0.01"
                                               name="awarded_price"
                                               required
                                               :value="selectedClaimShoe.listed_price"
                                               class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl font-mono text-sm apple-focus-ring">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Reservation Window:</label>
                                        <select name="reservation_minutes" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-xs apple-focus-ring">
                                            <option value="120">2 Hours (Standard Live Window)</option>
                                            <option value="60">1 Hour (Flash Claim)</option>
                                            <option value="1440">24 Hours (Next Day Settlement)</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit"
                                        class="w-full py-3 rounded-2xl bg-[#0071E3] hover:bg-[#0077ED] text-white font-bold text-sm shadow-sm active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span>Lock Live Reservation</span>
                                </button>
                            </form>
                        </template>

                        {{-- Naka-reserve pa --}}
                        <template x-if="selectedClaimShoe.status === 'reserved'">
                            <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs space-y-2">
                                <div class="font-bold text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Item is currently Reserved</span>
                                </div>
                                <p class="text-amber-700 dark:text-amber-400">This pair is locked in an active customer reservation. Duplicate claims are prevented.</p>
                            </div>
                        </template>

                        {{-- Nahalin na --}}
                        <template x-if="selectedClaimShoe.status === 'sold'">
                            <div class="p-4 rounded-2xl bg-neutral-100 dark:bg-neutral-800 text-xs text-neutral-600 dark:text-neutral-400">
                                This pair has already been sold and payment verified.
                            </div>
                        </template>

                    </div>
                </template>

                <template x-if="!selectedClaimShoe">
                    <div class="border border-neutral-200 dark:border-neutral-800 bg-white/50 dark:bg-[#1C1C1E]/50 rounded-3xl p-8 text-center space-y-3">
                        <img src="{{ asset('logo.png') }}" alt="The Shoe Boy" class="w-14 h-14 rounded-2xl object-cover shadow-sm mx-auto opacity-95 border border-neutral-200 dark:border-neutral-700">
                        <div>
                            <h4 class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">Awaiting Short-Code Input</h4>
                            <p class="text-[11px] text-neutral-400 max-w-xs mx-auto mt-0.5">Type the shoe short code or scan barcode to view pair specifications and lock customer claim.</p>
                        </div>
                    </div>
                </template>

            </div>

            {{-- Mga naka-reserve nga claims --}}
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-neutral-200/80 dark:border-neutral-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Active Stream Claims</h3>
                        </div>
                        <span class="text-[11px] font-mono font-bold text-amber-600 dark:text-amber-400">{{ $activeClaims->count() }} pairs</span>
                    </div>

                    <div class="space-y-3 max-h-[580px] overflow-y-auto pr-1">
                        @forelse($activeClaims as $claim)
                        <div class="p-3.5 rounded-2xl border transition-all space-y-2.5 {{ $claim->status === 'reserved' ? 'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-900/40' : 'bg-neutral-50 dark:bg-neutral-800/40 border-neutral-200 dark:border-neutral-800' }}">
                            
                            <div class="flex items-start justify-between gap-2 text-xs">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">{{ $claim->item->sku }}</span>
                                        <span class="text-neutral-400">•</span>
                                        <span class="font-semibold text-neutral-800 dark:text-neutral-200 truncate">{{ $claim->item->brand }} {{ $claim->item->model }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5 text-[11px]">
                                        <span class="font-semibold text-amber-700 dark:text-amber-400">{{ $claim->customer->messenger_contact }}</span>
                                        <span class="text-neutral-400">Size {{ $claim->item->size }}</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-mono font-bold text-neutral-900 dark:text-white">₱{{ number_format($claim->awarded_price, 2) }}</span>
                                    <span class="block text-[10px] uppercase font-bold {{ $claim->status === 'reserved' ? 'text-amber-600' : 'text-emerald-600' }}">
                                        {{ $claim->status }}
                                    </span>
                                </div>
                            </div>

                            @if($claim->status === 'reserved')
                            <div class="pt-1 flex items-center justify-end gap-2">
                                <button type="button"
                                        @click="openPaymentModal({{ $claim->id }}, '{{ $claim->order_number }}', {{ $claim->awarded_price }})"
                                        class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold transition-colors flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Verify Payment</span>
                                </button>

                                <form action="{{ route('orders.cancel', $claim->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Release this reservation back to stock?')"
                                            class="px-2 py-1 rounded-lg bg-neutral-200 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 hover:text-rose-600 text-[11px] font-medium transition-colors">
                                        Release
                                    </button>
                                </form>
                            </div>
                            @elseif($claim->payment)
                            <div class="text-[10px] font-mono text-neutral-400 pt-1 border-t border-neutral-100 dark:border-neutral-800">
                                Paid via {{ strtoupper($claim->payment->method) }}: {{ $claim->payment->reference_no }}
                            </div>
                            @endif

                        </div>
                        @empty
                        <div class="text-center py-12 text-neutral-400 text-xs">No active stream claims currently.</div>
                        @endforelse
                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- Tab 2: Walk-In POS --}}
    <div x-show="activeTab === 'pos'" x-cloak class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <div class="lg:col-span-7 space-y-4">
                
                <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm space-y-3">
                    <input type="text"
                           x-model="posSearch"
                           placeholder="Filter catalog by brand, model or SKU..."
                           class="w-full px-4 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-xs sm:text-sm apple-focus-ring">

                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="text-[11px] text-neutral-400 font-medium mr-1">Brand:</span>
                        <template x-for="brand in ['All', 'Li-Ning', 'Anta', 'Peak', 'Nike', 'Jordan', 'Asics', 'New Balance']" :key="brand">
                            <button @click="posBrandFilter = brand"
                                    :class="posBrandFilter === brand ? 'bg-[#1D1D1F] text-white dark:bg-white dark:text-[#1D1D1F] font-semibold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300'"
                                    class="px-2.5 py-1 rounded-lg text-[11px] transition-all"
                                    x-text="brand"></button>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 max-h-[600px] overflow-y-auto pr-1">
                    <template x-for="shoe in shoes.filter(s => s.status === 'available' && (posBrandFilter === 'All' || s.brand === posBrandFilter) && (!posSearch.trim() || `${s.sku} ${s.brand} ${s.model}`.toLowerCase().includes(posSearch.toLowerCase())))" :key="shoe.id">
                        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 hover:border-[#0071E3]/50 rounded-2xl p-4 shadow-sm transition-all flex flex-col justify-between gap-3">
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-mono font-semibold text-[#0071E3] dark:text-[#0A84FF]" x-text="shoe.sku"></span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold badge-available">Available</span>
                                </div>
                                <h4 class="font-bold text-sm text-[#1D1D1F] dark:text-white line-clamp-1" x-text="shoe.brand + ' ' + shoe.model"></h4>
                                <div class="flex items-center gap-2 text-xs text-neutral-500 mt-1">
                                    <span class="font-mono" x-text="shoe.size"></span>
                                    <span>•</span>
                                    <span x-text="shoe.condition"></span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-neutral-100 dark:border-neutral-800">
                                <span class="font-mono font-bold text-base text-[#1D1D1F] dark:text-white" x-text="'₱' + Number(shoe.listed_price).toLocaleString()"></span>
                                <button @click="addToPos(shoe)"
                                        type="button"
                                        class="p-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 hover:bg-[#0071E3] hover:text-white transition-all text-neutral-700 dark:text-neutral-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

            </div>

            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm space-y-5">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-neutral-200/80 dark:border-neutral-800">
                        <div>
                            <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white">Walk-in Sale Ticket</h3>
                            <span class="text-xs text-neutral-400" x-text="posCart.length + ' pair(s)'"></span>
                        </div>
                        <template x-if="posCart.length > 0">
                            <button @click="posCart = []" class="text-xs text-rose-500 hover:underline">Clear Ticket</button>
                        </template>
                    </div>

                    <div class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
                        <template x-for="item in posCart" :key="item.id">
                            <div class="flex items-center justify-between p-3 rounded-xl bg-neutral-100/70 dark:bg-neutral-800/60 text-xs">
                                <div class="min-w-0 flex-1 mr-2">
                                    <div class="font-semibold text-neutral-800 dark:text-neutral-200 truncate" x-text="item.brand + ' ' + item.model"></div>
                                    <div class="text-[10px] text-neutral-400 font-mono" x-text="item.sku + ' • ' + item.size"></div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-mono font-bold" x-text="'₱' + Number(item.listed_price).toLocaleString()"></span>
                                    <button @click="removeFromPos(item.id)" class="text-neutral-400 hover:text-rose-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template x-if="posCart.length === 0">
                            <div class="text-center py-8 text-neutral-400 text-xs">Ticket is empty. Click (+) on any pair.</div>
                        </template>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-neutral-100 dark:border-neutral-800 text-xs">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] text-neutral-500 font-medium mb-1">Discount (₱):</label>
                                <input type="number" x-model="posDiscount" placeholder="0.00" class="w-full px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg text-xs font-mono apple-focus-ring">
                            </div>
                            <div>
                                <label class="block text-[11px] text-neutral-500 font-medium mb-1">Reason / Note:</label>
                                <input type="text" x-model="posDiscountNote" placeholder="Regular buyer" class="w-full px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg text-xs apple-focus-ring">
                            </div>
                        </div>

                        <div class="flex justify-between items-baseline pt-2 border-t border-neutral-200 dark:border-neutral-700">
                            <span class="font-bold text-sm text-[#1D1D1F] dark:text-white">Amount Due:</span>
                            <span class="font-mono font-bold text-xl text-[#0071E3] dark:text-[#0A84FF]" x-text="'₱' + posFinal.toLocaleString()"></span>
                        </div>
                    </div>

                    <form action="{{ route('orders.pos-checkout') }}" method="POST" class="space-y-3 pt-2">
                        @csrf
                        <template x-for="item in posCart" :key="item.id">
                            <input type="hidden" name="item_ids[]" :value="item.id">
                        </template>
                        <input type="hidden" name="discount" :value="posDiscount">
                        <input type="hidden" name="discount_note" :value="posDiscountNote">

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <button @click="posPaymentMethod = 'cash'"
                                    type="button"
                                    :class="posPaymentMethod === 'cash' ? 'bg-[#1D1D1F] text-white dark:bg-white dark:text-[#1D1D1F] font-bold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300'"
                                    class="py-2.5 rounded-xl transition-all text-center">Cash Drawer</button>
                            <button @click="posPaymentMethod = 'gcash'"
                                    type="button"
                                    :class="posPaymentMethod === 'gcash' ? 'bg-[#0071E3] text-white font-bold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300'"
                                    class="py-2.5 rounded-xl transition-all text-center">GCash Transfer</button>
                        </div>
                        <input type="hidden" name="payment_method" :value="posPaymentMethod">

                        <template x-if="posPaymentMethod === 'cash'">
                            <div class="p-3 rounded-xl bg-neutral-100/70 dark:bg-neutral-800/60 text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-neutral-500">Cash Received (₱):</label>
                                    <input type="number" x-model="posCashTendered" name="cash_tendered" class="w-28 px-2 py-1 bg-white dark:bg-neutral-900 border rounded font-mono text-right">
                                </div>
                                <div class="flex items-center justify-between pt-1 border-t border-neutral-200 dark:border-neutral-700">
                                    <span class="text-neutral-500">Change:</span>
                                    <span class="font-mono font-bold text-emerald-600" x-text="'₱' + posChange.toLocaleString()"></span>
                                </div>
                            </div>
                        </template>

                        <template x-if="posPaymentMethod === 'gcash'">
                            <div class="p-3 rounded-xl bg-neutral-100/70 dark:bg-neutral-800/60 text-xs space-y-1">
                                <label class="text-neutral-500 block">GCash Reference No:</label>
                                <input type="text" x-model="posGcashRef" name="gcash_ref" placeholder="e.g. 1092837482" class="w-full px-3 py-1.5 bg-white dark:bg-neutral-900 border rounded font-mono">
                            </div>
                        </template>

                        <button type="submit"
                                :disabled="posCart.length === 0"
                                class="w-full py-3.5 rounded-2xl bg-[#0071E3] hover:bg-[#0077ED] disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Complete Sale</span>
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>

    {{-- Tab 3: Triage Table --}}
    <div x-show="activeTab === 'triage'" x-cloak class="space-y-6">
        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-200/80 dark:border-neutral-800">
                <div>
                    <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Batch {{ $activeBatch?->batch_code }} Serialized Pair Triage</h3>
                    <p class="text-[11px] text-neutral-400">Manage individual pair statuses, condition grading, and repair costs.</p>
                </div>
                <span class="font-mono text-xs text-neutral-500">{{ $items->count() }} pairs in batch</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-[11px] text-neutral-400 uppercase tracking-wider border-b border-neutral-200/80 dark:border-neutral-800 pb-2">
                        <tr>
                            <th class="py-2.5 px-3">SKU</th>
                            <th class="py-2.5 px-3">Brand & Model</th>
                            <th class="py-2.5 px-3">Size</th>
                            <th class="py-2.5 px-3">Condition</th>
                            <th class="py-2.5 px-3 text-right">Repair Cost</th>
                            <th class="py-2.5 px-3 text-right">Listed Price</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60 font-sans">
                        @foreach($items as $item)
                        <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                            <td class="py-3 px-3 font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">{{ $item->sku }}</td>
                            <td class="py-3 px-3 font-semibold text-neutral-800 dark:text-neutral-200">{{ $item->brand }} {{ $item->model }}</td>
                            <td class="py-3 px-3 font-mono">{{ $item->size }}</td>
                            <td class="py-3 px-3">{{ $item->condition }}</td>
                            <td class="py-3 px-3 text-right font-mono text-neutral-500">₱{{ number_format($item->repair_cost, 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-neutral-900 dark:text-white">₱{{ number_format($item->listed_price, 2) }}</td>
                            <td class="py-3 px-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold badge-{{ $item->status }}">
                                    {{ strtoupper($item->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <form action="{{ route('items.triage', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    @if($item->status === 'available')
                                        <input type="hidden" name="status" value="reserved">
                                        <button type="submit" class="px-2 py-1 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 rounded-lg text-[10px] font-semibold hover:bg-amber-200">Hold</button>
                                    @elseif($item->status === 'reserved')
                                        <input type="hidden" name="status" value="available">
                                        <button type="submit" class="px-2 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-lg text-[10px] font-semibold hover:bg-emerald-200">Release</button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal para bayad --}}
    <div x-show="showPayModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Verify Payment</h3>
                </div>
                <button @click="showPayModal = false" class="text-neutral-400 hover:text-neutral-600 text-sm">✕</button>
            </div>

            <form action="{{ route('payments.verify') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="order_id" :value="payOrder ? payOrder.id : ''">

                <div>
                    <span class="text-neutral-400 block text-[11px]">Order Number:</span>
                    <span class="font-mono font-bold text-sm text-[#1D1D1F] dark:text-white" x-text="payOrder ? payOrder.number : ''"></span>
                </div>

                <div>
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Payment Amount (₱):</label>
                    <input type="number" step="0.01" name="amount" x-model="payAmount" required class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border rounded-xl font-mono text-sm apple-focus-ring">
                </div>

                <div>
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Payment Method:</label>
                    <select name="method" x-model="payMethod" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border rounded-xl text-xs apple-focus-ring">
                        <option value="gcash">GCash Transfer</option>
                        <option value="cash">Cash Drawer</option>
                    </select>
                </div>

                <div x-show="payMethod === 'gcash'">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300 mb-1">GCash Reference Number:</label>
                    <input type="text" name="reference_no" x-model="payRef" placeholder="e.g. 1092837482" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border rounded-xl font-mono text-xs uppercase apple-focus-ring">
                    <span class="text-[10px] text-neutral-400 mt-0.5 block">Unique reference check is enforced.</span>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="showPayModal = false" class="flex-1 py-2.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-semibold">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold flex items-center justify-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Confirm Paid</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

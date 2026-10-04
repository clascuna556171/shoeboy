@extends('layouts.app')

@section('title', 'Staff Operations Console')

@section('content')
<div class="space-y-6"
     x-data="{
         activeTab: @js(request('tab')) || localStorage.getItem('shoeboy.staff.tab') || 'claims', // 'claims', 'pos', 'triage'
         setTab(tab) {
             this.activeTab = tab;
             localStorage.setItem('shoeboy.staff.tab', tab);
         },
         
         // Live Claims State
         claimInput: '',
         buyerName: '',
         buyerHandle: '',
         selectedClaimShoe: null,
         claimCart: [],
         claimReservation: '120',
         shoes: {{ Js::from($items) }},

         // Triage Table State
         triageSearch: '',
         triageStatus: 'all',
         triageSort: 'sku',
         triageDir: 'asc',
         selectedSoldId: null,
         
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

         addToClaim(shoe) {
             if (this.claimCart.some(i => i.id === shoe.id)) return;
             this.claimCart.push({ id: shoe.id, sku: shoe.sku, brand: shoe.brand, model: shoe.model, size: shoe.size, price: shoe.listed_price, batch: shoe.batch });
         },
         removeFromClaim(id) {
             this.claimCart = this.claimCart.filter(i => i.id !== id);
         },
         get claimTotal() {
             return this.claimCart.reduce((acc, i) => acc + (parseFloat(i.price) || 0), 0);
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
         },

         get triageItems() {
             let list = this.shoes;
             if (this.triageStatus !== 'all') {
                 list = list.filter(s => s.status === this.triageStatus);
             }
             const q = (this.triageSearch || '').trim().toLowerCase();
             if (q) {
                 list = list.filter(s => `${s.sku} ${s.brand} ${s.model} ${s.condition} ${s.size}`.toLowerCase().includes(q));
             }
             const numeric = ['size', 'repair_cost', 'listed_price'];
             const dir = this.triageDir === 'asc' ? 1 : -1;
             return [...list].sort((a, b) => {
                 const av = a[this.triageSort];
                 const bv = b[this.triageSort];
                 if (numeric.includes(this.triageSort)) {
                     return ((parseFloat(av) || 0) - (parseFloat(bv) || 0)) * dir;
                 }
                 return String(av ?? '').localeCompare(String(bv ?? '')) * dir;
             });
         },
         sortTriage(column) {
             if (this.triageSort === column) {
                 this.triageDir = this.triageDir === 'asc' ? 'desc' : 'asc';
             } else {
                 this.triageSort = column;
                 this.triageDir = 'asc';
             }
         },
         countByStatus(status) {
             return this.shoes.filter(s => s.status === status).length;
         },
         triageAction(id) {
             return `{{ url('/items') }}/${id}/triage`;
         }
     }">

     {{-- Header ug selector --}}
    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm flex flex-col gap-4">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Live Operations Console</span>
                <span class="text-neutral-300 dark:text-neutral-600">•</span>
                <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-1.5">
                    <input type="hidden" name="tab" :value="activeTab">
                    <label for="batch-picker" class="text-xs text-neutral-500">Batch:</label>
                    <select id="batch-picker" name="batch" onchange="this.form.requestSubmit()"
                            class="app-select app-input-sm !w-auto font-semibold">
                        <option value="all" {{ $activeBatch === null ? 'selected' : '' }}>All batches</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}" {{ $activeBatch && $activeBatch->id === $b->id ? 'selected' : '' }}>{{ $b->batch_code }} — {{ $b->supplier?->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <h1 class="text-xl md:text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-1">Multi-Channel Order & Claim Console</h1>
        </div>

        <div class="flex items-center bg-neutral-100 dark:bg-neutral-800 p-1 rounded-xl border border-neutral-200/70 dark:border-neutral-700">
            <button type="button"
                    @click="setTab('claims')"
                    :class="activeTab === 'claims' ? 'bg-white dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white font-semibold shadow-sm' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
                    class="px-4 py-2 rounded-lg text-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span>Live Claims</span>
                <template x-if="claimCart.length > 0">
                    <span class="px-1.5 py-0.5 rounded-full text-[11px] font-mono bg-neutral-900 text-white dark:bg-white dark:text-neutral-900" x-text="claimCart.length"></span>
                </template>
            </button>

            <button type="button"
                    @click="setTab('pos')"
                    :class="activeTab === 'pos' ? 'bg-white dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white font-semibold shadow-sm' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
                    class="px-4 py-2 rounded-lg text-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>Walk-in POS</span>
                <template x-if="posCart.length > 0">
                    <span class="px-1.5 py-0.5 rounded-full text-[11px] font-mono bg-neutral-900 text-white dark:bg-white dark:text-neutral-900" x-text="posCart.length"></span>
                </template>
            </button>

            <button type="button"
                    @click="setTab('triage')"
                    :class="activeTab === 'triage' ? 'bg-white dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white font-semibold shadow-sm' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
                    class="px-4 py-2 rounded-lg text-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Triage Table</span>
            </button>
        </div>
        </div>

        {{-- Live Claims how-to (steps inside header) --}}
        <div x-show="activeTab === 'claims'" x-cloak
             class="flex flex-wrap items-center gap-x-3 gap-y-2 pt-3 border-t border-neutral-100 dark:border-neutral-800 text-sm">
            <span class="flex items-center gap-2 text-neutral-600 dark:text-neutral-300">
                <span class="w-5 h-5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 ring-1 ring-inset ring-neutral-200 dark:ring-neutral-700 text-[11px] font-bold flex items-center justify-center">1</span>
                Search or scan a shoe short code
            </span>
            <span class="text-neutral-300 dark:text-neutral-600">→</span>
            <span class="flex items-center gap-2 text-neutral-600 dark:text-neutral-300">
                <span class="w-5 h-5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 ring-1 ring-inset ring-neutral-200 dark:ring-neutral-700 text-[11px] font-bold flex items-center justify-center">2</span>
                Add the pairs to the claim ticket
            </span>
            <span class="text-neutral-300 dark:text-neutral-600">→</span>
            <span class="flex items-center gap-2 text-neutral-600 dark:text-neutral-300">
                <span class="w-5 h-5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 ring-1 ring-inset ring-neutral-200 dark:ring-neutral-700 text-[11px] font-bold flex items-center justify-center">3</span>
                Enter buyer details &amp; lock the reservation
            </span>
        </div>

        {{-- POS how-to (steps inside header) --}}
        <div x-show="activeTab === 'pos'" x-cloak
             class="flex flex-wrap items-center gap-x-3 gap-y-2 pt-3 border-t border-neutral-100 dark:border-neutral-800 text-sm">
            <span class="flex items-center gap-2 text-neutral-600 dark:text-neutral-300">
                <span class="w-5 h-5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 ring-1 ring-inset ring-neutral-200 dark:ring-neutral-700 text-[11px] font-bold flex items-center justify-center">1</span>
                Pick pairs from the catalog
            </span>
            <span class="text-neutral-300 dark:text-neutral-600">→</span>
            <span class="flex items-center gap-2 text-neutral-600 dark:text-neutral-300">
                <span class="w-5 h-5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 ring-1 ring-inset ring-neutral-200 dark:ring-neutral-700 text-[11px] font-bold flex items-center justify-center">2</span>
                Review the ticket &amp; discounts
            </span>
            <span class="text-neutral-300 dark:text-neutral-600">→</span>
            <span class="flex items-center gap-2 text-neutral-600 dark:text-neutral-300">
                <span class="w-5 h-5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 ring-1 ring-inset ring-neutral-200 dark:ring-neutral-700 text-[11px] font-bold flex items-center justify-center">3</span>
                Take payment &amp; complete the sale
            </span>
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
                        <span class="text-xs text-neutral-500 font-mono">Scan barcode or type code</span>
                    </div>

                    <div class="relative">
                        <input type="text"
                               x-model="claimInput"
                               @input="handleSearch(claimInput)"
                               placeholder="Type short code (e.g. B04-001, Panda, Kobe, Wade)..."
                               class="app-input">
                    </div>

                    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                        <span class="text-xs text-neutral-500 shrink-0">Available pairs:</span>
                        <template x-for="shoe in shoes.filter(s => s.status === 'available').slice(0, 6)" :key="shoe.id">
                            <button @click="claimInput = shoe.sku; handleSearch(shoe.sku)"
                                    type="button"
                                    class="px-2.5 py-1 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-900 dark:hover:bg-white hover:text-white dark:hover:text-neutral-900 transition-all font-mono text-xs">
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
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold uppercase"
                                          :class="'badge-' + selectedClaimShoe.status"
                                          x-text="selectedClaimShoe.status"></span>
                                    <span class="text-xs text-neutral-500" x-text="selectedClaimShoe.category"></span>
                                </div>
                                <h3 class="font-bold text-lg text-[#1D1D1F] dark:text-white mt-1" x-text="selectedClaimShoe.brand + ' ' + selectedClaimShoe.model"></h3>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-bold font-mono text-[#1D1D1F] dark:text-white" x-text="'₱' + Number(selectedClaimShoe.listed_price).toLocaleString()"></div>
                                <div class="text-xs text-neutral-500">Listed Price</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 p-3.5 rounded-2xl bg-neutral-100/70 dark:bg-neutral-800/60 text-sm">
                            <div>
                                <span class="text-xs text-neutral-500 block">Size:</span>
                                <span class="font-semibold text-neutral-800 dark:text-neutral-200 font-mono" x-text="selectedClaimShoe.size"></span>
                            </div>
                            <div>
                                <span class="text-xs text-neutral-500 block">Condition:</span>
                                <span class="font-semibold text-neutral-800 dark:text-neutral-200" x-text="selectedClaimShoe.condition"></span>
                            </div>
                            <div>
                                <span class="text-xs text-neutral-500 block">Price Tier:</span>
                                <span class="text-neutral-700 dark:text-neutral-300" x-text="selectedClaimShoe.price_tier"></span>
                            </div>
                        </div>

                        {{-- Idugang sa claim ticket (bulk) --}}
                        <template x-if="selectedClaimShoe.status === 'available'">
                            <button type="button"
                                    @click="addToClaim(selectedClaimShoe)"
                                    :disabled="claimCart.some(i => i.id === selectedClaimShoe.id)"
                                    class="app-btn app-btn-primary w-full py-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span x-text="claimCart.some(i => i.id === selectedClaimShoe.id) ? 'Added to Claim Ticket' : 'Add to Claim Ticket'"></span>
                            </button>
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
                            <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">Awaiting Short-Code Input</h4>
                            <p class="text-xs text-neutral-500 max-w-xs mx-auto mt-0.5">Type the shoe short code or scan barcode to view pair specifications and lock customer claim.</p>
                        </div>
                    </div>
                </template>

            </div>

            <div class="lg:col-span-5 space-y-4">

                {{-- Live Claim Ticket (bulk) --}}
                <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-neutral-200/80 dark:border-neutral-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#0071E3]"></span>
                            <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white">Live Claim Ticket</h3>
                        </div>
                        <template x-if="claimCart.length > 0">
                            <button @click="claimCart = []" type="button" class="text-xs text-rose-500 hover:underline">Clear Ticket</button>
                        </template>
                    </div>

                    <form action="{{ route('orders.award') }}" method="POST" class="space-y-4 text-sm">
                        @csrf
                        <input type="hidden" name="order_type" value="live_stream">
                        <template x-for="item in claimCart" :key="item.id">
                            <span>
                                <input type="hidden" name="item_ids[]" :value="item.id">
                                <input type="hidden" name="prices[]" :value="item.price">
                            </span>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Buyer FB Handle <span class="app-req">*</span>:</label>
                                <input type="text" name="messenger_contact" x-model="buyerHandle" required placeholder="@username (e.g. @ken_hoops23)" class="app-input">
                            </div>
                            <div class="space-y-1">
                                <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Customer Full Name <span class="app-req">*</span>:</label>
                                <input type="text" name="customer_name" x-model="buyerName" required placeholder="e.g. Ken Hoops" class="app-input">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Pairs (<span x-text="claimCart.length"></span>)</div>
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                <template x-for="item in claimCart" :key="item.id">
                                    <div class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-neutral-100/70 dark:bg-neutral-800/60">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="inline-flex items-center rounded-md bg-neutral-100 dark:bg-neutral-800 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-neutral-600 dark:text-neutral-300" x-text="item.batch ? item.batch.batch_code : ''"></span>
                                                <span class="font-semibold text-neutral-800 dark:text-neutral-200 truncate text-xs" x-text="item.brand + ' ' + item.model"></span>
                                            </div>
                                            <div class="text-[11px] text-neutral-500 font-mono" x-text="item.sku + ' • ' + item.size"></div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="number" step="0.01" min="0" x-model="item.price" class="app-input app-input-sm font-mono text-right w-24">
                                            <button type="button" @click="removeFromClaim(item.id)" class="text-neutral-500 hover:text-rose-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="claimCart.length === 0">
                                    <div class="text-center py-6 text-neutral-500 text-xs">Ticket is empty. Search a short code, then tap <strong>Add to Claim Ticket</strong>.</div>
                                </template>
                            </div>
                        </div>

                        <div class="flex items-end justify-between gap-3 pt-2 border-t border-neutral-100 dark:border-neutral-800">
                            <div>
                                <span class="text-xs text-neutral-500 block">Reservation Window:</span>
                                <select name="reservation_minutes" x-model="claimReservation" class="app-select mt-1">
                                    <option value="120">2 Hours (Standard Live Window)</option>
                                    <option value="60">1 Hour (Flash Claim)</option>
                                    <option value="1440">24 Hours (Next Day Settlement)</option>
                                </select>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-neutral-500 block">Ticket Total</span>
                                <span class="font-mono font-bold text-lg text-[#1D1D1F] dark:text-white" x-text="'₱' + claimTotal.toLocaleString()"></span>
                            </div>
                        </div>

                        <button type="submit"
                                :disabled="claimCart.length === 0"
                                class="app-btn app-btn-primary w-full py-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span x-text="claimCart.length === 0 ? 'Add a pair to start' : 'Lock Reservation · ' + claimCart.length + ' pair(s)'"></span>
                        </button>
                    </form>
                </div>

                {{-- Mga naka-reserve nga claims --}}
                <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-neutral-200/80 dark:border-neutral-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white">Active Stream Claims</h3>
                        </div>
                        <span class="text-xs font-mono font-bold text-amber-600 dark:text-amber-400">{{ $activeClaims->count() }} pairs</span>
                    </div>

                    <div class="space-y-3 max-h-[580px] overflow-y-auto pr-1">
                        @forelse($activeClaims as $claim)
                        <div class="p-3.5 rounded-2xl border transition-all space-y-2.5 {{ $claim->status === 'reserved' ? 'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-900/40' : 'bg-neutral-50 dark:bg-neutral-800/40 border-neutral-200 dark:border-neutral-800' }}">
                            
                            <div class="flex items-start justify-between gap-2 text-sm">
                                <div class="min-w-0">
                                    @php($claimFirst = $claim->items->first())
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]">{{ $claimFirst?->sku }}</span>
                                        <span class="text-neutral-500">•</span>
                                        <span class="font-semibold text-neutral-800 dark:text-neutral-200 truncate">{{ $claimFirst?->brand }} {{ $claimFirst?->model }}</span>
                                        @if($claim->items->count() > 1)
                                            <span class="badge badge-neutral">+{{ $claim->items->count() - 1 }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5 text-xs">
                                        <span class="font-semibold text-amber-700 dark:text-amber-400">{{ $claim->customer->display_handle }}</span>
                                        <span class="text-neutral-500">Size {{ $claimFirst?->size }}</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-mono font-bold text-neutral-900 dark:text-white">₱{{ number_format($claim->awarded_price, 2) }}</span>
                                    <span class="block text-[11px] uppercase font-bold {{ $claim->status === 'reserved' ? 'text-amber-600' : 'text-emerald-600' }}">
                                        {{ $claim->status }}
                                    </span>
                                </div>
                            </div>

                            @if($claim->status === 'reserved')
                            <div class="pt-1 flex items-center justify-end gap-2">
                                <button type="button"
                                        @click="openPaymentModal({{ $claim->id }}, '{{ $claim->order_number }}', {{ $claim->awarded_price }})"
                                        class="inline-flex items-center justify-center gap-1 min-h-8 px-3 py-1.5 rounded-lg border border-emerald-200 dark:border-emerald-900/60 bg-white dark:bg-[#1C1C1E] text-emerald-700 dark:text-emerald-400 text-xs font-semibold hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Verify Payment</span>
                                </button>

                                <form action="{{ route('orders.cancel', $claim->id) }}" method="POST" class="inline">
                                    @csrf
                                    <x-action-btn tone="secondary" type="submit"
                                                  data-confirm="Release this reservation?"
                                                  data-confirm-variant="danger"
                                                  data-confirm-message="The pair returns to available stock and the claim is cancelled."
                                                  data-confirm-label="Release">Release</x-action-btn>
                                </form>
                            </div>
                            @elseif($claim->payment)
                            <div class="text-[11px] font-mono text-neutral-500 pt-1 border-t border-neutral-100 dark:border-neutral-800">
                                Paid via {{ strtoupper($claim->payment->method) }}: {{ $claim->payment->reference_no }}
                            </div>
                            @endif

                        </div>
                        @empty
                        <div class="app-empty">
                            <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span class="text-xs font-medium">No active stream claims currently.</span>
                        </div>
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
                           class="app-input">

                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="text-xs text-neutral-500 font-medium mr-1">Brand:</span>
                        <template x-for="brand in ['All', 'Li-Ning', 'Anta', 'Peak', 'Nike', 'Jordan', 'Asics', 'New Balance']" :key="brand">
                            <button @click="posBrandFilter = brand"
                                    :class="posBrandFilter === brand ? 'bg-[#1D1D1F] text-white dark:bg-white dark:text-[#1D1D1F] font-semibold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-900 dark:hover:bg-white hover:text-white dark:hover:text-neutral-900'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition-all"
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
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold badge-available">Available</span>
                                </div>
                                <h4 class="font-bold text-sm text-[#1D1D1F] dark:text-white line-clamp-1" x-text="shoe.brand + ' ' + shoe.model"></h4>
                                <div class="flex items-center gap-2 text-xs text-neutral-500 mt-1">
                                    <span class="inline-flex items-center rounded-md bg-neutral-100 dark:bg-neutral-800 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-neutral-600 dark:text-neutral-300" x-text="shoe.batch ? shoe.batch.batch_code : ''"></span>
                                    <span class="font-mono" x-text="shoe.size"></span>
                                    <span>•</span>
                                    <span x-text="shoe.condition"></span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-neutral-100 dark:border-neutral-800">
                                <span class="font-mono font-bold text-base text-[#1D1D1F] dark:text-white" x-text="'₱' + Number(shoe.listed_price).toLocaleString()"></span>
                                <button @click="addToPos(shoe)"
                                        type="button"
                                        class="app-btn app-btn-primary app-btn-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add
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
                            <span class="text-xs text-neutral-500" x-text="posCart.length + ' pair(s)'"></span>
                        </div>
                        <template x-if="posCart.length > 0">
                            <button @click="posCart = []" class="text-xs text-rose-500 hover:underline">Clear Ticket</button>
                        </template>
                    </div>

                    <template x-if="posCart.length > 0">
                        <div class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Items</div>
                    </template>

                    <div class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
                        <template x-for="item in posCart" :key="item.id">
                            <div class="flex items-center justify-between p-3 rounded-xl bg-neutral-100/70 dark:bg-neutral-800/60 text-sm">
                                <div class="min-w-0 flex-1 mr-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex items-center rounded-md bg-neutral-100 dark:bg-neutral-800 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-neutral-600 dark:text-neutral-300" x-text="item.batch ? item.batch.batch_code : ''"></span>
                                        <span class="font-semibold text-neutral-800 dark:text-neutral-200 truncate" x-text="item.brand + ' ' + item.model"></span>
                                    </div>
                                    <div class="text-[11px] text-neutral-500 font-mono" x-text="item.sku + ' • ' + item.size"></div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-mono font-bold" x-text="'₱' + Number(item.listed_price).toLocaleString()"></span>
                                    <button @click="removeFromPos(item.id)" class="text-neutral-500 hover:text-rose-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template x-if="posCart.length === 0">
                            <div class="text-center py-8 text-neutral-500 text-xs flex flex-col items-center gap-1">
                                <svg class="w-7 h-7 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span>Ticket is empty. Click <strong>Add</strong> on any pair.</span>
                            </div>
                        </template>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-neutral-100 dark:border-neutral-800 text-sm">
                        <div class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Adjustments</div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs text-neutral-500 font-medium mb-1">Discount (₱):</label>
                                <input type="number" x-model="posDiscount" placeholder="0.00" class="app-input app-input-sm font-mono">
                            </div>
                            <div>
                                <label class="block text-xs text-neutral-500 font-medium mb-1">Reason / Note:</label>
                                <input type="text" x-model="posDiscountNote" placeholder="Regular buyer" class="app-input app-input-sm">
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

                        <div class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Payment</div>

                        <div class="flex items-center bg-neutral-100 dark:bg-neutral-800 p-1 rounded-xl border border-neutral-200/70 dark:border-neutral-700">
                            <button @click="posPaymentMethod = 'cash'"
                                    type="button"
                                    :class="posPaymentMethod === 'cash' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm text-neutral-900 dark:text-white font-semibold' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
                                    class="flex-1 py-2 rounded-lg text-sm transition-all flex items-center justify-center gap-1.5">
                                <svg x-show="posPaymentMethod === 'cash'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Cash Drawer</span>
                            </button>
                            <button @click="posPaymentMethod = 'gcash'"
                                    type="button"
                                    :class="posPaymentMethod === 'gcash' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm text-neutral-900 dark:text-white font-semibold' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
                                    class="flex-1 py-2 rounded-lg text-sm transition-all flex items-center justify-center gap-1.5">
                                <svg x-show="posPaymentMethod === 'gcash'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>GCash Transfer</span>
                            </button>
                        </div>
                        <input type="hidden" name="payment_method" :value="posPaymentMethod">

                        <template x-if="posPaymentMethod === 'cash'">
                            <div class="p-3 rounded-xl bg-neutral-100/70 dark:bg-neutral-800/60 text-sm space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-neutral-500">Cash Received (₱) <span class="app-req">*</span>:</label>
                                    <input type="number" step="0.01" min="0" x-model="posCashTendered" name="cash_tendered" :required="posPaymentMethod === 'cash'" class="app-input app-input-sm font-mono text-right w-28">
                                </div>
                                <div class="flex items-center justify-between pt-1 border-t border-neutral-200 dark:border-neutral-700">
                                    <span class="text-sm text-neutral-500">Change:</span>
                                    <span class="font-mono text-lg font-bold text-emerald-600 dark:text-emerald-400" x-text="'₱' + posChange.toLocaleString()"></span>
                                </div>
                            </div>
                        </template>

                        <template x-if="posPaymentMethod === 'gcash'">
                            <div class="p-3 rounded-xl bg-neutral-100/70 dark:bg-neutral-800/60 text-sm space-y-1">
                                <label class="text-neutral-500 block">GCash Reference No <span class="app-req">*</span>:</label>
                                <input type="text" x-model="posGcashRef" name="gcash_ref" :required="posPaymentMethod === 'gcash'" placeholder="e.g. 1092837482" class="app-input app-input-sm font-mono">
                            </div>
                        </template>

                        <button type="submit"
                                :disabled="posCart.length === 0"
                                class="app-btn app-btn-primary w-full py-3.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="posCart.length === 0 ? 'Add a pair to start' : 'Complete Sale · ₱' + posFinal.toLocaleString()"></span>
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
                    <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white">{{ $activeBatch?->batch_code ?? 'All Batches' }} Serialized Pair Triage</h3>
                    <p class="text-xs text-neutral-500">Manage individual pair statuses, condition grading, and repair costs.</p>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-neutral-500">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" x-model="triageSearch" placeholder="Search SKU, brand, model..."
                               class="app-input app-input-sm !w-56 pl-9">
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button type="button" @click="triageStatus = 'all'"
                                class="app-btn app-btn-sm"
                                :class="triageStatus === 'all' ? 'app-btn-primary' : 'app-btn-secondary'">
                            All
                            <span class="ml-0.5 rounded-full px-1.5 py-0.5 text-[10px] font-mono leading-none"
                                  :class="triageStatus === 'all' ? 'bg-white/20 text-white' : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300'"
                                  x-text="shoes.length"></span>
                        </button>
                        <button type="button" @click="triageStatus = 'available'"
                                class="app-btn app-btn-sm"
                                :class="triageStatus === 'available' ? 'app-btn-primary' : 'app-btn-secondary'">
                            Available
                            <span class="ml-0.5 rounded-full px-1.5 py-0.5 text-[10px] font-mono leading-none"
                                  :class="triageStatus === 'available' ? 'bg-white/20 text-white' : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300'"
                                  x-text="countByStatus('available')"></span>
                        </button>
                        <button type="button" @click="triageStatus = 'reserved'"
                                class="app-btn app-btn-sm"
                                :class="triageStatus === 'reserved' ? 'app-btn-primary' : 'app-btn-secondary'">
                            Reserved
                            <span class="ml-0.5 rounded-full px-1.5 py-0.5 text-[10px] font-mono leading-none"
                                  :class="triageStatus === 'reserved' ? 'bg-white/20 text-white' : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300'"
                                  x-text="countByStatus('reserved')"></span>
                        </button>
                        <button type="button" @click="triageStatus = 'sold'"
                                class="app-btn app-btn-sm"
                                :class="triageStatus === 'sold' ? 'app-btn-primary' : 'app-btn-secondary'">
                            Sold
                            <span class="ml-0.5 rounded-full px-1.5 py-0.5 text-[10px] font-mono leading-none"
                                  :class="triageStatus === 'sold' ? 'bg-white/20 text-white' : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300'"
                                  x-text="countByStatus('sold')"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200/80 dark:border-neutral-800 pb-2">
                        <tr>
                            <x-sort-th column="sku" label="SKU" method="sortTriage" active="triageSort" dir="triageDir" />
                            <x-sort-th column="brand" label="Brand & Model" method="sortTriage" active="triageSort" dir="triageDir" />
                            <x-sort-th column="size" label="Size" method="sortTriage" active="triageSort" dir="triageDir" />
                            <x-sort-th column="condition" label="Condition" method="sortTriage" active="triageSort" dir="triageDir" />
                            <x-sort-th column="repair_cost" label="Repair Cost" align="right" method="sortTriage" active="triageSort" dir="triageDir" />
                            <x-sort-th column="listed_price" label="Listed Price" align="right" method="sortTriage" active="triageSort" dir="triageDir" />
                            <x-sort-th column="status" label="Status" align="center" method="sortTriage" active="triageSort" dir="triageDir" />
                            <th class="py-2.5 px-3 text-right font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60 font-sans">
                        <template x-for="item in triageItems" :key="item.id">
                        <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                            <td class="py-3 px-3 font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]" x-text="item.sku"></td>
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center rounded-md bg-neutral-100 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300" x-text="item.batch ? item.batch.batch_code : ''"></span>
                                    <span class="font-semibold text-neutral-800 dark:text-neutral-200" x-text="`${item.brand} ${item.model}`"></span>
                                </div>
                            </td>
                            <td class="py-3 px-3 font-mono" x-text="item.size"></td>
                            <td class="py-3 px-3" x-text="item.condition"></td>
                            <td class="py-3 px-3 text-right font-mono text-neutral-500" x-text="'₱' + Number(item.repair_cost).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-neutral-900 dark:text-white" x-text="'₱' + Number(item.listed_price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></td>
                            <td class="py-3 px-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold" :class="'badge-' + item.status" x-text="item.status.toUpperCase()"></span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <template x-if="item.status === 'sold'">
                                    <x-action-btn icon="eye" tone="secondary" @click="selectedSoldId = item.id">View</x-action-btn>
                                </template>
                                <form :action="triageAction(item.id)" method="POST" class="inline" x-show="item.status !== 'sold'">
                                    @csrf
                                    @method('PATCH')
                                    <template x-if="item.status === 'available'">
                                        <span>
                                            <input type="hidden" name="status" value="reserved">
                                            <x-action-btn tone="secondary" type="submit"
                                                          class="text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-900/60"
                                                          data-confirm="Put this pair on hold?"
                                                          data-confirm-variant="warning"
                                                          x-bind:data-confirm-message="`${item.sku} will be marked as reserved and held out of available stock.`"
                                                          data-confirm-label="Hold pair">Hold</x-action-btn>
                                        </span>
                                    </template>
                                    <template x-if="item.status === 'reserved'">
                                        <span>
                                            <input type="hidden" name="status" value="available">
                                            <x-action-btn tone="secondary" type="submit"
                                                          class="text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/60"
                                                          data-confirm="Release this pair?"
                                                          data-confirm-variant="success"
                                                          x-bind:data-confirm-message="`${item.sku} will return to available stock.`"
                                                          data-confirm-label="Release pair">Release</x-action-btn>
                                        </span>
                                    </template>
                                </form>
                            </td>
                        </tr>
                        </template>
                        <tr x-show="triageItems.length === 0" x-cloak>
                            <td colspan="8">
                                <div class="app-empty">
                                    <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    <span class="text-xs font-medium">No pairs match your search or filter.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @foreach($items as $item)
        @php($soldOrder = $item->status === 'sold' ? $item->orders->first() : null)
        @if($soldOrder)
            <x-order-modal :order="$soldOrder" x-show="selectedSoldId === {{ $item->id }}" close="selectedSoldId = null" />
        @endif
    @endforeach

    {{-- Modal para bayad --}}
    <x-modal title="Verify Payment" accent="emerald" close="showPayModal = false"
             x-show="showPayModal" x-cloak @keydown.escape.window="showPayModal = false">
            <form action="{{ route('payments.verify') }}" method="POST" class="space-y-4 text-sm">
                @csrf
                <input type="hidden" name="order_id" :value="payOrder ? payOrder.id : ''">

                <div>
                    <span class="text-neutral-500 block text-xs">Order Number:</span>
                    <span class="font-mono font-bold text-sm text-[#1D1D1F] dark:text-white" x-text="payOrder ? payOrder.number : ''"></span>
                </div>

                <div>
                    <label class="app-label">Payment Amount (₱) <span class="app-req">*</span></label>
                    <input type="number" step="0.01" name="amount" x-model="payAmount" required class="app-input font-mono">
                </div>

                <div>
                    <label class="app-label">Payment Method <span class="app-req">*</span></label>
                    <select name="method" x-model="payMethod" class="app-select">
                        <option value="gcash">GCash Transfer</option>
                        <option value="cash">Cash Drawer</option>
                    </select>
                </div>

                <div x-show="payMethod === 'gcash'">
                    <label class="app-label">GCash Reference Number <span class="app-req">*</span></label>
                    <input type="text" name="reference_no" x-model="payRef" :required="payMethod === 'gcash'" placeholder="e.g. 1092837482" class="app-input font-mono uppercase">
                    <span class="text-xs text-neutral-500 mt-1 block">Unique reference check is enforced.</span>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="button" @click="showPayModal = false" class="app-btn app-btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="app-btn app-btn-emerald flex-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Confirm Paid</span>
                    </button>
                </div>
            </form>
    </x-modal>

</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- ========================================================================================= -->
    <!-- VIEW 1: REAL-TIME LIVE CLAIM CONSOLE (LIVE-SELLING MODE)                                  -->
    <!-- ========================================================================================= -->
    <div x-show="activeTab === 'claims'" x-cloak class="space-y-6">
        
        <!-- Live Session Metrics Header -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 lg:gap-4">
            <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Active Batch</div>
                <div class="text-lg lg:text-xl font-bold text-[#1D1D1F] dark:text-white mt-1 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span x-text="currentBatchMeta.code + ' Live Stream'"></span>
                </div>
                <div class="text-[11px] text-neutral-400 mt-0.5" x-text="currentBatchMeta.name"></div>
            </div>

            <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Active Claims</div>
                <div class="text-lg lg:text-xl font-bold text-amber-600 dark:text-amber-400 mt-1 font-mono" x-text="activeClaimsCount + ' Pairs'"></div>
                <div class="text-[11px] text-neutral-400 mt-0.5">Pending GCash payment</div>
            </div>

            <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Claimed Value</div>
                <div class="text-lg lg:text-xl font-bold text-[#0071E3] dark:text-[#0A84FF] mt-1 font-mono" x-text="'₱' + totalClaimedValue.toLocaleString()"></div>
                <div class="text-[11px] text-neutral-400 mt-0.5">Total reserved & verified</div>
            </div>

            <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Reservation Expiry</div>
                <div class="text-lg lg:text-xl font-bold text-neutral-800 dark:text-neutral-200 mt-1">2-Hour Window</div>
                <div class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5">Auto-returns unverified claims</div>
            </div>
        </div>

        <!-- Main Live Claim Grid (Command Bar & Active Claims Drawer) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left 7 Cols: Command Bar & Live Shoe Preview Card -->
            <div class="lg:col-span-7 space-y-4">
                
                <!-- Command Bar Card -->
                <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#0071E3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Shoe Short-Code Lookup
                        </span>
                        <span class="text-[11px] text-neutral-400 font-mono">Press ⌘K to focus</span>
                    </div>

                    <!-- Short-code Search Input -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-neutral-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input id="claimSearchInput"
                               type="text"
                               x-model="claimInput"
                               @keydown.enter="if(selectedClaimShoe && selectedClaimShoe.status === 'available') { document.getElementById('buyerHandleInput')?.focus(); }"
                               placeholder="Scan barcode or type code (e.g. B04-001, Panda, Kobe)..."
                               autocomplete="off"
                               class="w-full pl-12 pr-12 py-3.5 bg-neutral-100/80 dark:bg-neutral-800/80 border border-transparent dark:border-neutral-700/60 rounded-2xl text-sm lg:text-base font-medium text-[#1D1D1F] dark:text-white placeholder-neutral-400 apple-focus-ring transition-all">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                            <kbd class="px-1.5 py-0.5 text-[10px] font-mono bg-neutral-200 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 rounded">⌘K</kbd>
                        </div>
                    </div>

                    <!-- Quick Code Suggestion Chips -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                        <span class="text-[11px] text-neutral-400 shrink-0">Sample codes:</span>
                        <template x-for="chip in ['B04-001', 'B04-003', 'B04-004', 'B04-009', 'B04-011', 'B04-012']" :key="chip">
                            <button @click="claimInput = chip"
                                    type="button"
                                    class="px-2.5 py-1 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:bg-[#0071E3] hover:text-white dark:hover:bg-[#0A84FF] transition-all font-mono text-[11px]">
                                <span x-text="chip"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Shoe Preview & Lock Card -->
                <template x-if="selectedClaimShoe">
                    <div class="bg-white dark:bg-[#1C1C1E] border-2 rounded-3xl p-6 shadow-md transition-all space-y-5"
                         :class="{
                             'border-emerald-500/40 dark:border-emerald-500/30': selectedClaimShoe.status === 'available',
                             'border-amber-500/40 dark:border-amber-500/30': selectedClaimShoe.status === 'reserved',
                             'border-neutral-300 dark:border-neutral-700': selectedClaimShoe.status === 'sold'
                         }">
                        
                        <!-- Card Header & Status -->
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-sm text-[#0071E3] dark:text-[#0A84FF]" x-text="selectedClaimShoe.sku"></span>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wide"
                                          :class="'badge-' + selectedClaimShoe.status"
                                          x-text="selectedClaimShoe.status.replace('_', ' ')"></span>
                                    <span class="text-xs text-neutral-400" x-text="selectedClaimShoe.category"></span>
                                </div>
                                <h3 class="font-bold text-lg lg:text-xl text-[#1D1D1F] dark:text-white mt-1" x-text="selectedClaimShoe.brand + ' ' + selectedClaimShoe.model"></h3>
                            </div>
                            <div class="text-right">
                                <div class="text-xl lg:text-2xl font-bold font-mono text-[#1D1D1F] dark:text-white" x-text="'₱' + selectedClaimShoe.target_price.toLocaleString()"></div>
                                <div class="text-[11px] text-neutral-400">Target Selling Price</div>
                            </div>
                        </div>

                        <!-- Sneaker Details Grid -->
                        <div class="grid grid-cols-3 gap-3 p-3.5 rounded-2xl bg-neutral-100/70 dark:bg-neutral-800/60 border border-neutral-200/60 dark:border-neutral-700/40 text-xs">
                            <div>
                                <span class="text-[11px] text-neutral-400 block">Size:</span>
                                <span class="font-semibold text-neutral-800 dark:text-neutral-200 font-mono text-sm" x-text="selectedClaimShoe.size"></span>
                            </div>
                            <div>
                                <span class="text-[11px] text-neutral-400 block">Condition:</span>
                                <span class="font-semibold text-neutral-800 dark:text-neutral-200" x-text="selectedClaimShoe.condition"></span>
                            </div>
                            <div>
                                <span class="text-[11px] text-neutral-400 block">Allocated Cost:</span>
                                <span class="font-mono text-neutral-600 dark:text-neutral-300" x-text="'₱' + selectedClaimShoe.base_cost"></span>
                            </div>
                        </div>

                        <!-- Action Form -->
                        <template x-if="selectedClaimShoe.status === 'available'">
                            <div class="space-y-3 pt-1">
                                <div class="space-y-1.5">
                                    <label for="buyerHandleInput" class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300">
                                        Buyer Facebook Handle:
                                    </label>
                                    <div class="relative">
                                        <input id="buyerHandleInput"
                                               type="text"
                                               x-model="buyerInput"
                                               @keydown.enter="confirmClaim()"
                                               placeholder="@username or customer name"
                                               class="w-full px-4 py-3 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-sm font-medium text-[#1D1D1F] dark:text-white placeholder-neutral-400 apple-focus-ring">
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button @click="confirmClaim()"
                                            type="button"
                                            class="flex-1 py-3 px-4 rounded-xl bg-[#0071E3] hover:bg-[#0077ED] text-white font-semibold text-sm shadow-sm active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span>Lock 2-Hour Reservation</span>
                                    </button>
                                    <button @click="selectedClaimShoe = null; claimInput = ''"
                                            type="button"
                                            class="px-4 py-3 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 text-sm font-medium hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Held / Reserved Warning State -->
                        <template x-if="selectedClaimShoe.status === 'reserved'">
                            <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 space-y-3">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-bold text-amber-800 dark:text-amber-300">Currently Reserved</div>
                                        <div class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                                            Claimed by <span class="font-bold font-mono" x-text="selectedClaimShoe.claimed_by"></span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-2">
                                            <div class="text-xs font-mono font-semibold text-amber-900 dark:text-amber-200" x-text="'Time left: ' + getTimeRemaining(selectedClaimShoe.expires_at).text"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2 pt-1">
                                    <button @click="markPaid(selectedClaimShoe.sku)"
                                            type="button"
                                            class="flex-1 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition-colors flex items-center justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Verify GCash Payment</span>
                                    </button>
                                    <button @click="releaseClaim(selectedClaimShoe.sku)"
                                            type="button"
                                            class="py-2 px-3 rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 font-semibold text-xs hover:bg-rose-200 transition-colors">
                                        Release to Stock
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Sold State -->
                        <template x-if="selectedClaimShoe.status === 'sold'">
                            <div class="p-4 rounded-2xl bg-neutral-100 dark:bg-neutral-800/80 border border-neutral-200 dark:border-neutral-700 space-y-1">
                                <div class="text-xs font-bold text-neutral-800 dark:text-white">Sold & Settled</div>
                                <div class="text-xs text-neutral-600 dark:text-neutral-400">
                                    Buyer: <span class="font-bold" x-text="selectedClaimShoe.claimed_by || 'Walk-in Customer'"></span> • Ref: <span class="font-mono" x-text="selectedClaimShoe.payment_ref || 'Verified'"></span>
                                </div>
                            </div>
                        </template>

                    </div>
                </template>

                <!-- Empty State prompt -->
                <template x-if="!selectedClaimShoe">
                    <div class="border border-neutral-200 dark:border-neutral-800 bg-white/50 dark:bg-[#1C1C1E]/50 rounded-3xl p-8 text-center space-y-3">
                        <img src="{{ asset('logo.png') }}" alt="The Shoe Boy" class="w-14 h-14 rounded-2xl object-cover shadow-sm mx-auto opacity-95 border border-neutral-200 dark:border-neutral-700">
                        <div>
                            <h4 class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">Awaiting Short-Code Input</h4>
                            <p class="text-[11px] text-neutral-400 max-w-xs mx-auto mt-0.5">Type the shoe short code from your stream or scan barcode to view pair specs and confirm reservations.</p>
                        </div>
                    </div>
                </template>

            </div>

            <!-- Right 5 Cols: Active Stream Reservations -->
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm space-y-4">
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Active Reservations</h3>
                        </div>
                        
                        <div class="flex items-center bg-neutral-100 dark:bg-neutral-800 p-0.5 rounded-lg text-[11px]">
                            <button @click="claimFilter = 'all'" :class="claimFilter === 'all' ? 'bg-white dark:bg-[#2C2C2E] font-semibold text-[#1D1D1F] dark:text-white shadow-xs' : 'text-neutral-500'" class="px-2 py-0.5 rounded-md transition-all">All</button>
                            <button @click="claimFilter = 'pending'" :class="claimFilter === 'pending' ? 'bg-white dark:bg-[#2C2C2E] font-semibold text-[#1D1D1F] dark:text-white shadow-xs' : 'text-neutral-500'" class="px-2 py-0.5 rounded-md transition-all">Pending</button>
                            <button @click="claimFilter = 'paid'" :class="claimFilter === 'paid' ? 'bg-white dark:bg-[#2C2C2E] font-semibold text-[#1D1D1F] dark:text-white shadow-xs' : 'text-neutral-500'" class="px-2 py-0.5 rounded-md transition-all">Paid</button>
                        </div>
                    </div>

                    <!-- Claims List -->
                    <div class="space-y-3 max-h-[580px] overflow-y-auto pr-1">
                        <template x-for="shoe in activeClaimsList" :key="shoe.sku">
                            <div class="p-3.5 rounded-2xl border transition-all space-y-2.5"
                                 :class="{
                                     'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200/80 dark:border-amber-900/40': shoe.status === 'reserved',
                                     'bg-neutral-50 dark:bg-neutral-800/40 border-neutral-200 dark:border-neutral-800': shoe.status === 'sold'
                                 }">
                                
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono font-bold text-xs text-[#0071E3] dark:text-[#0A84FF]" x-text="shoe.sku"></span>
                                            <span class="text-xs text-neutral-400">•</span>
                                            <span class="font-semibold text-xs text-neutral-800 dark:text-neutral-200 truncate" x-text="shoe.brand + ' ' + shoe.model"></span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5 text-[11px]">
                                            <span class="font-medium text-amber-700 dark:text-amber-400" x-text="shoe.claimed_by"></span>
                                            <span class="text-neutral-400">Size <span x-text="shoe.size"></span></span>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="font-mono font-bold text-xs text-neutral-900 dark:text-white" x-text="'₱' + shoe.target_price.toLocaleString()"></span>
                                        <template x-if="shoe.status === 'sold'">
                                            <span class="block text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">Paid</span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Reservation Timer Bar -->
                                <template x-if="shoe.status === 'reserved'">
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span class="text-neutral-500 dark:text-neutral-400">Timer:</span>
                                            <span class="font-mono font-bold"
                                                  :class="getTimeRemaining(shoe.expires_at).isUrgent ? 'text-rose-600 dark:text-rose-400' : 'text-amber-700 dark:text-amber-300'"
                                                  x-text="getTimeRemaining(shoe.expires_at).text"></span>
                                        </div>
                                        <div class="w-full h-1.5 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-1000"
                                                 :class="getTimeRemaining(shoe.expires_at).isUrgent ? 'bg-rose-500' : 'bg-amber-500'"
                                                 :style="'width: ' + getTimeRemaining(shoe.expires_at).percent + '%'"></div>
                                        </div>
                                        <div class="flex items-center justify-end gap-2 pt-1">
                                            <button @click="markPaid(shoe.sku)"
                                                    class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold transition-colors">
                                                Verify Paid
                                            </button>
                                            <button @click="releaseClaim(shoe.sku)"
                                                    class="px-2 py-1 rounded-lg bg-neutral-200 dark:bg-neutral-700 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-neutral-700 dark:text-neutral-300 hover:text-rose-600 text-[11px] font-medium transition-colors">
                                                Release
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <!-- Paid Details -->
                                <template x-if="shoe.status === 'sold' && shoe.payment_ref">
                                    <div class="text-[11px] text-neutral-500 dark:text-neutral-400 font-mono bg-white dark:bg-neutral-900 p-1.5 rounded-lg border border-neutral-200/60 dark:border-neutral-800">
                                        Ref: <span x-text="shoe.payment_ref"></span>
                                    </div>
                                </template>

                            </div>
                        </template>

                        <template x-if="activeClaimsList.length === 0">
                            <div class="text-center py-10 text-neutral-400 text-xs">
                                No active claims in this filter.
                            </div>
                        </template>
                    </div>

                </div>
            </div>

        </div>

    </div>

    <!-- ========================================================================================= -->
    <!-- VIEW 2: SINGLE-SCREEN WALK-IN POS (60/40 SPLIT VIEW)                                      -->
    <!-- ========================================================================================= -->
    <div x-show="activeTab === 'pos'" x-cloak class="space-y-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left 60% (7 cols): Available Inventory Catalog Grid -->
            <div class="lg:col-span-7 space-y-4">
                
                <!-- Search & Filter Controls -->
                <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm space-y-3.5">
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input id="posSearchInput"
                                   type="text"
                                   x-model="posSearch"
                                   placeholder="Search available pairs by brand, model, or SKU..."
                                   class="w-full pl-10 pr-4 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-xs sm:text-sm font-medium text-[#1D1D1F] dark:text-white placeholder-neutral-400 apple-focus-ring">
                        </div>
                    </div>

                    <!-- Filter Rows -->
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-[11px] text-neutral-400 font-medium">Brand:</span>
                        <template x-for="brand in ['All', 'Li-Ning', 'Anta', 'Peak', 'Nike', 'Jordan', 'Asics', 'New Balance']" :key="brand">
                            <button @click="posBrandFilter = brand"
                                    :class="posBrandFilter === brand ? 'bg-[#1D1D1F] text-white dark:bg-white dark:text-[#1D1D1F] font-semibold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200'"
                                    class="px-2.5 py-1 rounded-lg transition-all text-[11px]"
                                    x-text="brand"></button>
                        </template>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-[11px] text-neutral-400 font-medium">Size:</span>
                        <template x-for="size in ['All', 'US 8.0', 'US 8.5', 'US 9.0', 'US 9.5', 'US 10.0', 'US 10.5', 'US 11.0', 'US 11.5']" :key="size">
                            <button @click="posSizeFilter = size"
                                    :class="posSizeFilter === size ? 'bg-[#0071E3] text-white font-semibold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200'"
                                    class="px-2 py-0.5 rounded-md font-mono transition-all text-[10px]"
                                    x-text="size"></button>
                        </template>
                    </div>
                </div>

                <!-- Available Inventory Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 max-h-[640px] overflow-y-auto pr-1">
                    <template x-for="shoe in availablePosInventory" :key="shoe.sku">
                        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 hover:border-[#0071E3]/50 dark:hover:border-[#0A84FF]/50 rounded-2xl p-4 shadow-sm transition-all flex flex-col justify-between gap-3 group">
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-mono font-semibold text-[#0071E3] dark:text-[#0A84FF]" x-text="shoe.sku"></span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold badge-available">Available</span>
                                </div>
                                <h4 class="font-bold text-sm text-[#1D1D1F] dark:text-white line-clamp-1" x-text="shoe.brand + ' ' + shoe.model"></h4>
                                <div class="flex items-center gap-2 text-xs text-neutral-500 mt-1">
                                    <span class="font-mono font-medium" x-text="shoe.size"></span>
                                    <span>•</span>
                                    <span x-text="shoe.condition"></span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-neutral-100 dark:border-neutral-800">
                                <div>
                                    <span class="font-mono font-bold text-base text-[#1D1D1F] dark:text-white" x-text="'₱' + shoe.target_price.toLocaleString()"></span>
                                </div>
                                <button @click="addToPosCart(shoe)"
                                        type="button"
                                        class="p-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 group-hover:bg-[#0071E3] group-hover:text-white transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <template x-if="availablePosInventory.length === 0">
                        <div class="col-span-2 text-center py-16 text-neutral-400 text-xs">
                            No available shoes match your current filters.
                        </div>
                    </template>
                </div>

            </div>

            <!-- Right 40% (5 cols): Current Cart Ticket & Checkout -->
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm space-y-5">
                    
                    <!-- Ticket Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-neutral-200/80 dark:border-neutral-800">
                        <div>
                            <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white">Sale Ticket</h3>
                            <span class="text-xs text-neutral-400" x-text="posCart.length + ' pair(s)'"></span>
                        </div>
                        <template x-if="posCart.length > 0">
                            <button @click="clearPosCart()" class="text-xs text-rose-500 hover:underline">Clear Ticket</button>
                        </template>
                    </div>

                    <!-- Itemized List -->
                    <div class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
                        <template x-for="item in posCart" :key="item.sku">
                            <div class="flex items-center justify-between p-3 rounded-xl bg-neutral-100/70 dark:bg-neutral-800/60 border border-neutral-200/60 dark:border-neutral-700/40 text-xs">
                                <div class="min-w-0 flex-1 mr-2">
                                    <div class="font-semibold text-neutral-800 dark:text-neutral-200 truncate" x-text="item.brand + ' ' + item.model"></div>
                                    <div class="text-[10px] text-neutral-400 font-mono" x-text="item.sku + ' • ' + item.size"></div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-mono font-bold" x-text="'₱' + item.target_price.toLocaleString()"></span>
                                    <button @click="removeFromPosCart(item.sku)" class="text-neutral-400 hover:text-rose-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="posCart.length === 0">
                            <div class="text-center py-10 text-neutral-400 text-xs">
                                Ticket is empty. Select (+) on any pair.
                            </div>
                        </template>
                    </div>

                    <!-- Discount Adjustment -->
                    <div class="space-y-2 pt-2 border-t border-neutral-100 dark:border-neutral-800 text-xs">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] text-neutral-500 font-medium mb-1">Manual Discount (₱):</label>
                                <input type="number"
                                       x-model="posDiscount"
                                       min="0"
                                       placeholder="0.00"
                                       class="w-full px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg text-xs font-mono apple-focus-ring">
                            </div>
                            <div>
                                <label class="block text-[11px] text-neutral-500 font-medium mb-1">Reason:</label>
                                <input type="text"
                                       x-model="posDiscountNote"
                                       placeholder="e.g. Regular buyer discount"
                                       class="w-full px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg text-xs apple-focus-ring">
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="space-y-1.5 pt-2 border-t border-neutral-100 dark:border-neutral-800 text-xs">
                        <div class="flex justify-between text-neutral-500">
                            <span>Subtotal:</span>
                            <span class="font-mono" x-text="'₱' + posSubtotal.toLocaleString()"></span>
                        </div>
                        <template x-if="posDiscount > 0">
                            <div class="flex justify-between text-rose-500">
                                <span>Discount:</span>
                                <span class="font-mono" x-text="'-₱' + (parseFloat(posDiscount) || 0).toLocaleString()"></span>
                            </div>
                        </template>
                        <div class="flex justify-between items-baseline pt-2 border-t border-neutral-200/80 dark:border-neutral-700">
                            <span class="font-bold text-sm text-[#1D1D1F] dark:text-white">Amount Due:</span>
                            <span class="font-mono font-bold text-xl text-[#0071E3] dark:text-[#0A84FF]" x-text="'₱' + posFinalTotal.toLocaleString()"></span>
                        </div>
                    </div>

                    <!-- Payment Method Toggle (Cash vs GCash with Clean SVG Icons) -->
                    <div class="space-y-3 pt-2">
                        <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300">Payment Method:</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="posPaymentMethod = 'cash'"
                                    type="button"
                                    :class="posPaymentMethod === 'cash' ? 'bg-[#1D1D1F] text-white dark:bg-white dark:text-[#1D1D1F] font-bold shadow-sm' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300'"
                                    class="py-2.5 rounded-xl text-xs font-medium transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span>Cash Drawer</span>
                            </button>
                            <button @click="posPaymentMethod = 'gcash'"
                                    type="button"
                                    :class="posPaymentMethod === 'gcash' ? 'bg-[#0071E3] text-white font-bold shadow-sm' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300'"
                                    class="py-2.5 rounded-xl text-xs font-medium transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>GCash Transfer</span>
                            </button>
                        </div>

                        <!-- Cash Settlement Inputs -->
                        <template x-if="posPaymentMethod === 'cash'">
                            <div class="p-3.5 rounded-2xl bg-neutral-100/70 dark:bg-neutral-800/60 space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <label class="text-neutral-600 dark:text-neutral-400">Cash Received (₱):</label>
                                    <input type="number"
                                           x-model="posCashTendered"
                                           placeholder="0.00"
                                           class="w-32 px-3 py-1.5 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-lg font-mono text-right apple-focus-ring">
                                </div>
                                <div class="flex items-center justify-between pt-1 border-t border-neutral-200/60 dark:border-neutral-700">
                                    <span class="text-neutral-600 dark:text-neutral-400">Change Due:</span>
                                    <span class="font-mono font-bold text-sm text-emerald-600 dark:text-emerald-400" x-text="'₱' + posChangeDue.toLocaleString()"></span>
                                </div>
                            </div>
                        </template>

                        <!-- GCash Settlement Inputs -->
                        <template x-if="posPaymentMethod === 'gcash'">
                            <div class="p-3.5 rounded-2xl bg-neutral-100/70 dark:bg-neutral-800/60 space-y-2 text-xs">
                                <label class="block text-neutral-600 dark:text-neutral-400">GCash Reference Number:</label>
                                <input type="text"
                                       x-model="posGcashRef"
                                       placeholder="e.g. 1092837482"
                                       class="w-full px-3 py-2 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-lg font-mono uppercase apple-focus-ring">
                                <span class="text-[10px] text-neutral-400 block">Unique validation to prevent double-spending.</span>
                            </div>
                        </template>
                    </div>

                    <!-- Complete Sale Action -->
                    <button @click="completePosSale()"
                            type="button"
                            :disabled="posCart.length === 0"
                            class="w-full py-3.5 rounded-2xl bg-[#0071E3] hover:bg-[#0077ED] disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-sm shadow-md active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Complete Sale</span>
                    </button>

                </div>
            </div>

        </div>

    </div>

    <!-- ========================================================================================= -->
    <!-- VIEW 3: BATCH INGESTION & TRIAGE WORKSPACE                                                -->
    <!-- ========================================================================================= -->
    <div x-show="activeTab === 'triage'" x-cloak class="space-y-6">
        
        <!-- Batch Financial Overview Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 lg:gap-4">
            
            <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Active Sack</div>
                <div class="text-base lg:text-lg font-bold text-[#1D1D1F] dark:text-white mt-1" x-text="currentBatchMeta.name"></div>
                <div class="text-[11px] text-neutral-400 mt-0.5" x-text="currentBatchMeta.supplier"></div>
            </div>

            <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Sack Purchase Cost</div>
                <div class="text-base lg:text-lg font-bold font-mono text-[#1D1D1F] dark:text-white mt-1" x-text="'₱' + currentBatchMeta.sackCost.toLocaleString()"></div>
                <div class="text-[11px] text-neutral-400 mt-0.5" x-text="currentBatchMeta.pairCount + ' pairs received'"></div>
            </div>

            <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Base Unit Cost</div>
                <div class="text-base lg:text-lg font-bold font-mono text-neutral-800 dark:text-neutral-200 mt-1" x-text="'₱' + currentBatchMeta.baseCost"></div>
                <div class="text-[11px] text-neutral-400 mt-0.5">Sack Cost ÷ Pair Count</div>
            </div>

            <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Target Revenue</div>
                <div class="text-base lg:text-lg font-bold font-mono text-[#0071E3] dark:text-[#0A84FF] mt-1" x-text="'₱' + batchStats.totalTargetRevenue.toLocaleString()"></div>
                <div class="text-[11px] text-neutral-400 mt-0.5">Estimated gross proceeds</div>
            </div>

            <div class="col-span-2 lg:col-span-1 bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Projected Net Margin</div>
                <div class="text-base lg:text-lg font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-1" x-text="'₱' + batchStats.projectedNetProfit.toLocaleString()"></div>
                <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5" x-text="batchStats.marginPercent + '% Gross Margin'"></div>
            </div>

        </div>

        <!-- Triage Data Table Card -->
        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm space-y-4">
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-neutral-200/80 dark:border-neutral-800">
                <div class="flex items-center gap-3">
                    <span class="font-bold text-sm text-[#1D1D1F] dark:text-white">Serialized Pairs Triage Table</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-mono bg-neutral-100 dark:bg-neutral-800 text-neutral-500" x-text="triageInventory.length + ' pairs'"></span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <input id="triageSearchInput"
                           type="text"
                           x-model="triageSearch"
                           placeholder="Filter pairs..."
                           class="px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-xs text-[#1D1D1F] dark:text-white apple-focus-ring">
                    
                    <select x-model="triageStatusFilter"
                            class="px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-xs text-[#1D1D1F] dark:text-white apple-focus-ring">
                        <option value="all">All Statuses</option>
                        <option value="washing">Washing</option>
                        <option value="under_repair">Under Repair</option>
                        <option value="available">Available</option>
                        <option value="reserved">Reserved</option>
                        <option value="sold">Sold</option>
                    </select>

                    <select x-model="triageConditionFilter"
                            class="px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-xs text-[#1D1D1F] dark:text-white apple-focus-ring">
                        <option value="all">All Conditions</option>
                        <option value="Pristine">Pristine</option>
                        <option value="Good">Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Needs Repair">Needs Repair</option>
                    </select>
                </div>
            </div>

            <!-- Scannable Data Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-[11px] text-neutral-400 uppercase tracking-wider border-b border-neutral-200/80 dark:border-neutral-800 pb-2">
                        <tr>
                            <th class="py-2.5 px-3">SKU Code</th>
                            <th class="py-2.5 px-3">Brand & Model</th>
                            <th class="py-2.5 px-3">Size</th>
                            <th class="py-2.5 px-3">Condition</th>
                            <th class="py-2.5 px-3 text-right">Base Cost</th>
                            <th class="py-2.5 px-3 text-right">Repair Cost</th>
                            <th class="py-2.5 px-3 text-right">Target Price</th>
                            <th class="py-2.5 px-3 text-right">Unit Margin</th>
                            <th class="py-2.5 px-3 text-center">Triage Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60 font-sans">
                        <template x-for="shoe in triageInventory" :key="shoe.sku">
                            <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30 transition-colors">
                                <td class="py-3 px-3 font-mono font-bold text-[#0071E3] dark:text-[#0A84FF]" x-text="shoe.sku"></td>
                                <td class="py-3 px-3">
                                    <div class="font-semibold text-neutral-800 dark:text-neutral-200" x-text="shoe.brand + ' ' + shoe.model"></div>
                                    <span class="text-[10px] text-neutral-400" x-text="shoe.category"></span>
                                </td>
                                <td class="py-3 px-3 font-mono text-neutral-700 dark:text-neutral-300" x-text="shoe.size"></td>
                                <td class="py-3 px-3">
                                    <select :value="shoe.condition"
                                            @change="updateShoeCondition(shoe.sku, $event.target.value)"
                                            class="px-2 py-1 bg-transparent hover:bg-neutral-100 dark:hover:bg-neutral-800 border border-transparent hover:border-neutral-200 rounded-lg text-xs font-medium cursor-pointer">
                                        <option value="Pristine">Pristine</option>
                                        <option value="Good">Good</option>
                                        <option value="Fair">Fair</option>
                                        <option value="Needs Repair">Needs Repair</option>
                                    </select>
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-neutral-500" x-text="'₱' + shoe.base_cost"></td>
                                <td class="py-3 px-3 text-right">
                                    <input type="number"
                                           :value="shoe.repair_cost"
                                           @change="updateRepairCost(shoe.sku, $event.target.value)"
                                           class="w-16 px-1.5 py-0.5 text-right font-mono bg-transparent border-b border-dashed border-neutral-300 dark:border-neutral-700 focus:border-[#0071E3] text-xs">
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-semibold text-neutral-900 dark:text-white" x-text="'₱' + shoe.target_price.toLocaleString()"></td>
                                <td class="py-3 px-3 text-right font-mono font-bold"
                                    :class="(shoe.target_price - shoe.base_cost - shoe.repair_cost) > 1500 ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-600 dark:text-neutral-300'"
                                    x-text="'₱' + (shoe.target_price - shoe.base_cost - shoe.repair_cost).toLocaleString()"></td>
                                <td class="py-3 px-3 text-center">
                                    <button @click="cycleStatus(shoe.sku)"
                                            type="button"
                                            title="Click to cycle status"
                                            class="px-2.5 py-1 rounded-full text-[10px] font-semibold tracking-wide transition-all transform active:scale-95 cursor-pointer"
                                            :class="'badge-' + shoe.status">
                                        <span x-text="shoe.status.replace('_', ' ').toUpperCase()"></span>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <!-- ========================================================================================= -->
    <!-- VIEW 4: FINANCIAL LEDGER, SALES, MONEY, PROFIT, EXPENSES & EXCEL EXPORT                   -->
    <!-- ========================================================================================= -->
    <div x-show="activeTab === 'ledger'" x-cloak class="space-y-6">
        
        <!-- Top Executive Financial Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 lg:gap-4">
            
            <!-- Total Gross Sales -->
            <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Gross Sales</span>
                    <div class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-[#0071E3] dark:text-[#0A84FF] flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-lg lg:text-xl font-bold font-mono text-[#1D1D1F] dark:text-white mt-1" x-text="'₱' + totalGrossSales.toLocaleString()"></div>
                <div class="text-[11px] text-neutral-400 mt-0.5" x-text="salesLedger.length + ' settled transactions'"></div>
            </div>

            <!-- Cost of Goods Sold (COGS) -->
            <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Unit Inventory COGS</span>
                    <div class="w-6 h-6 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-500 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
                <div class="text-lg lg:text-xl font-bold font-mono text-neutral-800 dark:text-neutral-200 mt-1" x-text="'₱' + totalCogs.toLocaleString()"></div>
                <div class="text-[11px] text-neutral-400 mt-0.5">Base cost + repairs of sold units</div>
            </div>

            <!-- Operating Expenses -->
            <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Total Expenses</span>
                    <div class="w-6 h-6 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-500 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m0 0l-6-6m6 6H3"/></svg>
                    </div>
                </div>
                <div class="text-lg lg:text-xl font-bold font-mono text-rose-600 dark:text-rose-400 mt-1" x-text="'₱' + totalOperatingExpenses.toLocaleString()"></div>
                <div class="text-[11px] text-neutral-400 mt-0.5">Sacks, freight, supplies & shop</div>
            </div>

            <!-- Gross Profit on Footwear -->
            <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Gross Profit (Sales)</span>
                    <div class="w-6 h-6 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                </div>
                <div class="text-lg lg:text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-1" x-text="'₱' + grossSalesProfit.toLocaleString()"></div>
                <div class="text-[11px] text-neutral-400 mt-0.5">Gross Sales minus COGS</div>
            </div>

            <!-- Net Cashflow / Operating Margin -->
            <div class="col-span-2 lg:col-span-1 bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Net Operating Balance</span>
                    <div class="w-6 h-6 rounded-lg bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div class="text-lg lg:text-xl font-bold font-mono mt-1"
                     :class="netStoreProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-800 dark:text-neutral-200'"
                     x-text="'₱' + netStoreProfit.toLocaleString()"></div>
                <div class="text-[11px] text-neutral-400 mt-0.5">Realized cash after all expenses</div>
            </div>

        </div>

        <!-- Export Action & Channels Banner -->
        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            
            <div class="flex flex-wrap items-center gap-4 text-xs">
                <div class="flex items-center gap-2">
                    <div class="p-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-neutral-400 block text-[10px] uppercase font-medium">Cash in Drawer</span>
                        <span class="font-mono font-bold text-sm text-neutral-800 dark:text-neutral-200" x-text="'₱' + cashCollectedTotal.toLocaleString()"></span>
                    </div>
                </div>

                <div class="h-8 w-px bg-neutral-200 dark:bg-neutral-800 hidden sm:block"></div>

                <div class="flex items-center gap-2">
                    <div class="p-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-[#0071E3] dark:text-[#0A84FF]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <span class="text-neutral-400 block text-[10px] uppercase font-medium">GCash Settled</span>
                        <span class="font-mono font-bold text-sm text-[#0071E3] dark:text-[#0A84FF]" x-text="'₱' + gcashCollectedTotal.toLocaleString()"></span>
                    </div>
                </div>
            </div>

            <!-- Excel Export Button -->
            <button @click="exportToExcel()"
                    type="button"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-2xl bg-[#0071E3] hover:bg-[#0077ED] text-white font-semibold text-xs shadow-sm active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                <!-- Clean Excel / Spreadsheet Download SVG Icon -->
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export to Excel (.csv)</span>
            </button>

        </div>

        <!-- Two Columns: Left Expense Logger & Records, Right Sales Ledger -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left 5 Cols: Operating Expense Logger & Expense Records -->
            <div class="lg:col-span-5 space-y-4">
                
                <!-- Add Expense Card -->
                <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm space-y-3.5">
                    <div class="flex items-center gap-2 pb-2 border-b border-neutral-200/80 dark:border-neutral-800">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Record Shop Expense</h3>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div>
                            <label class="block text-[11px] text-neutral-500 font-medium mb-1">Expense Category:</label>
                            <select x-model="newExpenseCategory"
                                    class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-xs text-[#1D1D1F] dark:text-white apple-focus-ring">
                                <option value="Sack Purchase">Sack / Bale Purchase</option>
                                <option value="Shipping & Freight">Shipping & Freight</option>
                                <option value="Shoe Restoration">Shoe Restoration & Wash</option>
                                <option value="Packaging & Labels">Packaging & Thermal Labels</option>
                                <option value="Store Utilities">Shop Utilities & Overhead</option>
                                <option value="Miscellaneous">Miscellaneous</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] text-neutral-500 font-medium mb-1">Description:</label>
                            <input type="text"
                                   x-model="newExpenseDescription"
                                   placeholder="e.g. Courier waybill pouches (100 pcs)"
                                   class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-xs text-[#1D1D1F] dark:text-white apple-focus-ring">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] text-neutral-500 font-medium mb-1">Amount (₱):</label>
                                <input type="number"
                                       x-model="newExpenseAmount"
                                       placeholder="0.00"
                                       class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-xs font-mono text-[#1D1D1F] dark:text-white apple-focus-ring">
                            </div>
                            <div>
                                <label class="block text-[11px] text-neutral-500 font-medium mb-1">Date:</label>
                                <input type="date"
                                       x-model="newExpenseDate"
                                       class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl text-xs text-[#1D1D1F] dark:text-white apple-focus-ring">
                            </div>
                        </div>

                        <button @click="addExpense()"
                                type="button"
                                class="w-full py-2.5 rounded-xl bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 font-semibold text-xs hover:opacity-90 transition-opacity flex items-center justify-center gap-1.5 pt-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Save Expense</span>
                        </button>
                    </div>
                </div>

                <!-- Expense History Table -->
                <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-neutral-200/80 dark:border-neutral-800">
                        <span class="font-bold text-xs text-[#1D1D1F] dark:text-white">Expense Records</span>
                        <span class="text-[11px] font-mono text-neutral-400" x-text="expenses.length + ' items'"></span>
                    </div>

                    <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                        <template x-for="exp in expenses" :key="exp.id">
                            <div class="p-3 rounded-xl bg-neutral-100/70 dark:bg-neutral-800/60 border border-neutral-200/60 dark:border-neutral-700/40 flex items-start justify-between gap-3 text-xs">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-neutral-800 dark:text-neutral-200 truncate" x-text="exp.description"></span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] text-neutral-400 mt-0.5">
                                        <span class="px-1.5 py-0.2 bg-neutral-200 dark:bg-neutral-700 rounded text-neutral-600 dark:text-neutral-300" x-text="exp.category"></span>
                                        <span x-text="exp.date"></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2.5 shrink-0">
                                    <span class="font-mono font-bold text-rose-600 dark:text-rose-400" x-text="'-₱' + exp.amount.toLocaleString()"></span>
                                    <button @click="removeExpense(exp.id)"
                                            type="button"
                                            class="text-neutral-400 hover:text-rose-500 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Right 7 Cols: Master Sales Ledger Table -->
            <div class="lg:col-span-7 space-y-4">
                <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-5 shadow-sm space-y-4">
                    
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-neutral-200/80 dark:border-neutral-800">
                        <div>
                            <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Sales Transactions Ledger</h3>
                            <span class="text-[11px] text-neutral-400">Chronological history of settled orders</span>
                        </div>

                        <!-- Channel Filter -->
                        <div class="flex items-center bg-neutral-100 dark:bg-neutral-800 p-0.5 rounded-lg text-[11px]">
                            <button @click="ledgerChannelFilter = 'all'" :class="ledgerChannelFilter === 'all' ? 'bg-white dark:bg-[#2C2C2E] font-semibold text-[#1D1D1F] dark:text-white shadow-xs' : 'text-neutral-500'" class="px-2.5 py-0.5 rounded-md transition-all">All</button>
                            <button @click="ledgerChannelFilter = 'Live Stream'" :class="ledgerChannelFilter === 'Live Stream' ? 'bg-white dark:bg-[#2C2C2E] font-semibold text-[#1D1D1F] dark:text-white shadow-xs' : 'text-neutral-500'" class="px-2.5 py-0.5 rounded-md transition-all">Live Stream</button>
                            <button @click="ledgerChannelFilter = 'Walk-In POS'" :class="ledgerChannelFilter === 'Walk-In POS' ? 'bg-white dark:bg-[#2C2C2E] font-semibold text-[#1D1D1F] dark:text-white shadow-xs' : 'text-neutral-500'" class="px-2.5 py-0.5 rounded-md transition-all">Walk-In</button>
                        </div>
                    </div>

                    <!-- Ledger Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="text-[11px] text-neutral-400 uppercase tracking-wider border-b border-neutral-200/80 dark:border-neutral-800 pb-2">
                                <tr>
                                    <th class="py-2.5 px-3">Transaction</th>
                                    <th class="py-2.5 px-3">Item / Specs</th>
                                    <th class="py-2.5 px-3 text-right">Sale Price</th>
                                    <th class="py-2.5 px-3 text-right">Unit Margin</th>
                                    <th class="py-2.5 px-3 text-center">Settlement</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60 font-sans">
                                <template x-for="tx in filteredSalesLedger" :key="tx.id">
                                    <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30 transition-colors">
                                        <td class="py-3 px-3 font-mono">
                                            <span class="font-semibold text-neutral-800 dark:text-neutral-200 block" x-text="tx.id"></span>
                                            <span class="text-[10px] text-neutral-400" x-text="tx.date"></span>
                                            <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[9px] uppercase font-semibold tracking-wider"
                                                  :class="tx.channel === 'Live Stream' ? 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400' : 'bg-blue-50 dark:bg-blue-950/50 text-[#0071E3] dark:text-[#0A84FF]'"
                                                  x-text="tx.channel"></span>
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="font-semibold text-neutral-800 dark:text-neutral-200" x-text="tx.brand + ' ' + tx.model"></div>
                                            <div class="text-[10px] text-neutral-400 font-mono" x-text="tx.sku + ' • ' + tx.size + ' • ' + tx.condition"></div>
                                            <div class="text-[10px] text-neutral-500 mt-0.5" x-text="'Buyer: ' + tx.buyer"></div>
                                        </td>
                                        <td class="py-3 px-3 text-right font-mono font-bold text-neutral-900 dark:text-white" x-text="'₱' + tx.grossSale.toLocaleString()"></td>
                                        <td class="py-3 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="'+₱' + tx.netProfit.toLocaleString()"></td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold"
                                                  :class="tx.paymentMethod === 'Cash' ? 'bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300' : 'bg-blue-50 dark:bg-blue-950/50 text-[#0071E3] dark:text-[#0A84FF]'"
                                                  x-text="tx.paymentMethod"></span>
                                            <span class="block text-[9px] text-neutral-400 font-mono mt-0.5 truncate max-w-[110px] mx-auto" x-text="tx.paymentRef"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>

    </div>

</div>
@endsection

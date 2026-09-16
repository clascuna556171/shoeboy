<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="shoeBoyApp"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'The Shoe Boy') }} - Inventory & Sales</title>

    <!-- Immediate theme setter to prevent theme flash -->
    <script>
        if (localStorage.getItem('shoeboy_theme') === 'dark' || (!('shoeboy_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Scripts and Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F5F5F7] dark:bg-[#121214] text-[#1D1D1F] dark:text-[#F5F5F7] font-sans antialiased min-h-screen flex flex-col transition-colors duration-200 selection:bg-[#0071E3] selection:text-white">

    <!-- TOP NAVIGATION BAR (Apple Minimal Canvas Header) -->
    <header class="sticky top-0 z-40 w-full apple-glass border-b border-[#E5E5EA] dark:border-[#2C2C2E] px-4 lg:px-6 py-2.5 transition-colors duration-200">
        <div class="max-w-[1720px] mx-auto flex items-center justify-between gap-4">
            
            <!-- Left: Brand Identity with Official Logo -->
            <div class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="The Shoe Boy" class="w-9 h-9 rounded-xl object-cover shadow-sm border border-neutral-200 dark:border-neutral-700 shrink-0">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm tracking-tight text-[#1D1D1F] dark:text-white">THE SHOE BOY</span>
                        <span class="text-xs text-neutral-400 font-normal">Davao</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-[#86868B] dark:text-[#98989D]">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span class="font-medium">Batch <span x-text="activeBatch"></span></span>
                        <span>•</span>
                        <span class="font-mono text-[11px]" x-text="currentTime"></span>
                    </div>
                </div>
            </div>

            <!-- Center: Segmented Navigation -->
            <div class="flex items-center bg-neutral-200/70 dark:bg-[#1C1C1E] p-1 rounded-xl border border-neutral-300/60 dark:border-neutral-800 shadow-inner">
                <!-- Tab 1: Live Claims -->
                <button @click="activeTab = 'claims'"
                        :class="activeTab === 'claims' ? 'bg-white dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white shadow-sm font-semibold' : 'text-[#86868B] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-white font-medium'"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs transition-all duration-150">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span>Live Claims</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono"
                          :class="activeClaimsCount > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 font-bold' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-500'"
                          x-text="activeClaimsCount"></span>
                    <span class="hidden md:inline-block text-[9px] font-mono text-neutral-400 bg-neutral-100 dark:bg-neutral-800/80 px-1 rounded">1</span>
                </button>

                <!-- Tab 2: Walk-In POS -->
                <button @click="activeTab = 'pos'"
                        :class="activeTab === 'pos' ? 'bg-white dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white shadow-sm font-semibold' : 'text-[#86868B] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-white font-medium'"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs transition-all duration-150">
                    <svg class="w-3.5 h-3.5 text-[#0071E3] dark:text-[#0A84FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Point of Sale</span>
                    <template x-if="posCart.length > 0">
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono bg-[#0071E3] text-white font-bold" x-text="posCart.length"></span>
                    </template>
                    <span class="hidden md:inline-block text-[9px] font-mono text-neutral-400 bg-neutral-100 dark:bg-neutral-800/80 px-1 rounded">2</span>
                </button>

                <!-- Tab 3: Batch Ingestion & Triage -->
                <button @click="activeTab = 'triage'"
                        :class="activeTab === 'triage' ? 'bg-white dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white shadow-sm font-semibold' : 'text-[#86868B] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-white font-medium'"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs transition-all duration-150">
                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Inventory</span>
                    <span class="hidden md:inline-block text-[9px] font-mono text-neutral-400 bg-neutral-100 dark:bg-neutral-800/80 px-1 rounded">3</span>
                </button>

                <!-- Tab 4: Financial Ledger & Analytics -->
                <button @click="activeTab = 'ledger'"
                        :class="activeTab === 'ledger' ? 'bg-white dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white shadow-sm font-semibold' : 'text-[#86868B] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-white font-medium'"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs transition-all duration-150">
                    <svg class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Sales & Ledger</span>
                    <span class="hidden md:inline-block text-[9px] font-mono text-neutral-400 bg-neutral-100 dark:bg-neutral-800/80 px-1 rounded">4</span>
                </button>
            </div>

            <!-- Right: Quick Metrics, Appearance Toggle, Shortcuts -->
            <div class="flex items-center gap-2.5">
                <div class="hidden xl:flex items-center gap-2 text-xs">
                    <div class="px-2.5 py-1 rounded-lg bg-white/70 dark:bg-[#1C1C1E]/80 border border-neutral-200/80 dark:border-neutral-800 flex items-center gap-1.5">
                        <span class="text-neutral-500 text-[11px]">Available:</span>
                        <span class="font-semibold text-emerald-600 dark:text-emerald-400" x-text="globalStats.totalAvailable"></span>
                    </div>
                    <div class="px-2.5 py-1 rounded-lg bg-white/70 dark:bg-[#1C1C1E]/80 border border-neutral-200/80 dark:border-neutral-800 flex items-center gap-1.5">
                        <span class="text-neutral-500 text-[11px]">Reserved:</span>
                        <span class="font-semibold text-amber-600 dark:text-amber-400" x-text="globalStats.totalReserved"></span>
                    </div>
                    <div class="px-2.5 py-1 rounded-lg bg-white/70 dark:bg-[#1C1C1E]/80 border border-neutral-200/80 dark:border-neutral-800 flex items-center gap-1.5">
                        <span class="text-neutral-500 text-[11px]">Sales:</span>
                        <span class="font-semibold text-[#0071E3] dark:text-[#0A84FF]" x-text="'₱' + globalStats.grossRevenue.toLocaleString()"></span>
                    </div>
                </div>

                <!-- Command Key Helper -->
                <button @click="if (activeTab === 'claims') document.getElementById('claimSearchInput')?.focus(); else if (activeTab === 'pos') document.getElementById('posSearchInput')?.focus(); else if (activeTab === 'triage') document.getElementById('triageSearchInput')?.focus();"
                        class="hidden sm:flex items-center gap-1.5 px-2.5 py-1.2 rounded-lg bg-neutral-200/60 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 text-xs hover:bg-neutral-300/60 dark:hover:bg-neutral-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span class="font-mono text-[11px]">⌘K</span>
                </button>

                <!-- Appearance Toggle (Light / Dark Mode) -->
                <button @click="toggleTheme()"
                        title="Toggle Light / Dark Mode"
                        class="p-2 rounded-xl text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200/60 dark:hover:bg-neutral-800 transition-colors border border-transparent hover:border-neutral-300/70 dark:hover:border-neutral-700">
                    <template x-if="!darkMode">
                        <!-- Clean Moon SVG -->
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </template>
                    <template x-if="darkMode">
                        <!-- Clean Sun SVG -->
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </template>
                </button>

                <!-- Keyboard Help -->
                <button @click="showShortcutsModal = true"
                        title="Keyboard Shortcuts"
                        class="p-2 rounded-xl text-neutral-500 dark:text-neutral-400 hover:bg-neutral-200/60 dark:hover:bg-neutral-800 transition-colors">
                    <span class="font-mono text-xs font-semibold">?</span>
                </button>
            </div>
        </div>
    </header>

    <!-- MAIN VIEW CONTAINER -->
    <main class="flex-1 w-full max-w-[1720px] mx-auto p-4 lg:p-6 transition-all duration-200">
        @yield('content')
    </main>

    <!-- GLOBAL TOAST NOTIFICATIONS -->
    <div class="fixed bottom-6 right-6 z-50 flex flex-col gap-2.5 max-w-sm w-full pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="pointer-events-auto p-4 rounded-2xl shadow-lg border backdrop-blur-xl transition-all duration-200 transform translate-y-0 opacity-100"
                 :class="{
                     'bg-white/95 dark:bg-[#1C1C1E]/95 border-emerald-500/30 text-emerald-700 dark:text-emerald-400': toast.type === 'success',
                     'bg-white/95 dark:bg-[#1C1C1E]/95 border-amber-500/30 text-amber-700 dark:text-amber-400': toast.type === 'warning',
                     'bg-white/95 dark:bg-[#1C1C1E]/95 border-rose-500/30 text-rose-700 dark:text-rose-400': toast.type === 'error',
                     'bg-white/95 dark:bg-[#1C1C1E]/95 border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200': toast.type === 'info'
                 }">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5">
                        <template x-if="toast.type === 'success'">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <template x-if="toast.type === 'warning'">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </template>
                        <template x-if="toast.type === 'error'">
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </template>
                        <template x-if="toast.type === 'info'">
                            <svg class="w-4 h-4 text-[#0071E3] dark:text-[#0A84FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </template>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold" x-text="toast.title"></p>
                        <p class="text-[11px] text-neutral-600 dark:text-neutral-400 mt-0.5 leading-snug" x-text="toast.message"></p>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- KEYBOARD SHORTCUTS MODAL -->
    <div x-show="showShortcutsModal"
         x-cloak
         @keydown.escape.window="showShortcutsModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-neutral-200 dark:border-neutral-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#0071E3]"></span>
                    <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Keyboard Navigation</h3>
                </div>
                <button @click="showShortcutsModal = false" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-white text-sm font-semibold">✕</button>
            </div>
            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 dark:border-neutral-800/60">
                    <span class="text-neutral-600 dark:text-neutral-400">Live Claims Console</span>
                    <kbd class="px-2 py-1 bg-neutral-100 dark:bg-neutral-800 rounded font-mono text-[11px] border border-neutral-200 dark:border-neutral-700">1</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 dark:border-neutral-800/60">
                    <span class="text-neutral-600 dark:text-neutral-400">Walk-In Point of Sale</span>
                    <kbd class="px-2 py-1 bg-neutral-100 dark:bg-neutral-800 rounded font-mono text-[11px] border border-neutral-200 dark:border-neutral-700">2</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 dark:border-neutral-800/60">
                    <span class="text-neutral-600 dark:text-neutral-400">Inventory & Triage</span>
                    <kbd class="px-2 py-1 bg-neutral-100 dark:bg-neutral-800 rounded font-mono text-[11px] border border-neutral-200 dark:border-neutral-700">3</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 dark:border-neutral-800/60">
                    <span class="text-neutral-600 dark:text-neutral-400">Financial Ledger & Expenses</span>
                    <kbd class="px-2 py-1 bg-neutral-100 dark:bg-neutral-800 rounded font-mono text-[11px] border border-neutral-200 dark:border-neutral-700">4</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 dark:border-neutral-800/60">
                    <span class="text-neutral-600 dark:text-neutral-400">Focus Search Bar</span>
                    <kbd class="px-2 py-1 bg-neutral-100 dark:bg-neutral-800 rounded font-mono text-[11px] border border-neutral-200 dark:border-neutral-700">⌘K / Ctrl+K</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 dark:border-neutral-800/60">
                    <span class="text-neutral-600 dark:text-neutral-400">Confirm Reservation</span>
                    <kbd class="px-2 py-1 bg-neutral-100 dark:bg-neutral-800 rounded font-mono text-[11px] border border-neutral-200 dark:border-neutral-700">Enter</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-neutral-600 dark:text-neutral-400">Dismiss / Clear</span>
                    <kbd class="px-2 py-1 bg-neutral-100 dark:bg-neutral-800 rounded font-mono text-[11px] border border-neutral-200 dark:border-neutral-700">Esc</kbd>
                </div>
            </div>
            <div class="pt-2 text-center">
                <button @click="showShortcutsModal = false" class="px-4 py-1.5 bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-semibold rounded-xl hover:opacity-90 transition-opacity">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- POS RECEIPT MODAL -->
    <div x-show="showReceiptModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <template x-if="lastTransaction">
            <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="text-center space-y-1.5">
                    <img src="{{ asset('logo.png') }}" alt="The Shoe Boy" class="w-16 h-16 rounded-2xl object-cover shadow-md mx-auto mb-1 border border-neutral-200 dark:border-neutral-700">
                    <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white">The Shoe Boy</h3>
                    <p class="text-xs text-neutral-500 font-mono" x-text="'Receipt: ' + lastTransaction.receiptNo"></p>
                    <p class="text-[11px] text-neutral-400" x-text="lastTransaction.timestamp"></p>
                </div>

                <div class="border-t border-b border-dashed border-neutral-200 dark:border-neutral-700 py-3 space-y-2">
                    <template x-for="item in lastTransaction.items" :key="item.sku">
                        <div class="flex items-center justify-between text-xs">
                            <div>
                                <span class="font-semibold text-neutral-800 dark:text-neutral-200" x-text="item.brand + ' ' + item.model"></span>
                                <span class="text-[10px] text-neutral-400 block" x-text="item.sku + ' • ' + item.size"></span>
                            </div>
                            <span class="font-mono font-medium" x-text="'₱' + item.target_price.toLocaleString()"></span>
                        </div>
                    </template>
                </div>

                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between text-neutral-500">
                        <span>Subtotal:</span>
                        <span class="font-mono" x-text="'₱' + lastTransaction.subtotal.toLocaleString()"></span>
                    </div>
                    <template x-if="lastTransaction.discount > 0">
                        <div class="flex justify-between text-rose-500">
                            <span>Discount:</span>
                            <span class="font-mono" x-text="'-₱' + lastTransaction.discount.toLocaleString()"></span>
                        </div>
                    </template>
                    <div class="flex justify-between font-bold text-sm text-[#1D1D1F] dark:text-white pt-1 border-t border-neutral-100 dark:border-neutral-800">
                        <span>Total Paid:</span>
                        <span class="font-mono text-[#0071E3] dark:text-[#0A84FF]" x-text="'₱' + lastTransaction.total.toLocaleString()"></span>
                    </div>
                    <div class="flex justify-between text-neutral-500 text-[11px] pt-1">
                        <span>Payment Method:</span>
                        <span class="font-semibold uppercase" x-text="lastTransaction.paymentMethod"></span>
                    </div>
                    <template x-if="lastTransaction.paymentMethod === 'cash'">
                        <div class="flex justify-between text-neutral-500 text-[11px]">
                            <span>Change Due:</span>
                            <span class="font-mono text-emerald-600 dark:text-emerald-400 font-bold" x-text="'₱' + lastTransaction.changeDue.toLocaleString()"></span>
                        </div>
                    </template>
                    <template x-if="lastTransaction.paymentMethod === 'gcash'">
                        <div class="flex justify-between text-neutral-500 text-[11px]">
                            <span>GCash Reference:</span>
                            <span class="font-mono font-semibold text-neutral-700 dark:text-neutral-300" x-text="lastTransaction.gcashRef"></span>
                        </div>
                    </template>
                </div>

                <div class="flex gap-2 pt-2">
                    <button @click="showReceiptModal = false" class="flex-1 py-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 text-xs font-semibold hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                        Close
                    </button>
                    <button @click="window.print()" class="flex-1 py-2 rounded-xl bg-[#0071E3] text-white text-xs font-semibold hover:bg-[#0077ED] transition-colors flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Print Ticket</span>
                    </button>
                </div>
            </div>
        </template>
    </div>

</body>
</html>

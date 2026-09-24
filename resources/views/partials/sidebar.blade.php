@auth
<div x-show="sidebarOpen"
     x-cloak
     @click="toggleSidebar()"
     x-transition:enter="transition-opacity ease-linear duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-40 bg-black/40 md:hidden"></div>

<aside
    x-cloak
    @mouseenter="sidebarHoverCapable && !sidebarOpen && (sidebarHovering = true)"
    @mouseleave="sidebarHovering = false"
    :class="[
        (sidebarOpen || sidebarHovering) ? 'translate-x-0 md:w-64' : '-translate-x-full md:translate-x-0 md:w-[72px]',
        (sidebarHovering && !sidebarOpen) ? 'md:shadow-2xl' : ''
    ]"
    class="fixed z-50 md:z-30 inset-y-0 left-0 w-64 shrink-0
           bg-white dark:bg-[#1C1C1E] border-r border-[#E5E5EA] dark:border-[#2C2C2E]
           transition-all duration-200 ease-in-out
           flex flex-col">

    <div class="flex items-center gap-3 px-4 py-3.5 shrink-0"
         :class="!(sidebarOpen || sidebarHovering) && 'md:justify-center md:px-0'">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group min-w-0">
            <img src="{{ asset('logo.png') }}" alt="The Shoe Boy" class="w-9 h-9 rounded-xl object-cover shadow-sm border border-neutral-200 dark:border-neutral-700 shrink-0 group-hover:scale-105 transition-transform">
            <div class="flex flex-col leading-tight min-w-0" x-show="(sidebarOpen || sidebarHovering)" x-transition.opacity>
                <span class="font-bold text-sm tracking-tight text-[#1D1D1F] dark:text-white whitespace-nowrap">THE SHOE BOY</span>
                <span class="text-[11px] text-gray-800 font-normal">Davao</span>
            </div> 
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto py-3 px-2.5 space-y-1 text-xs text-gray-900">
        <a href="{{ route('dashboard') }}"
           title="Workspace"
           class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition-all {{ request()->routeIs('dashboard') ? 'bg-gray-50 dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white font-semibold shadow-sm' : 'text-gray-700 dark:text-[#98989D] hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-[#2C2C2E]' }}"
           :class="!(sidebarOpen || sidebarHovering) && 'md:justify-center'">
            <svg class="w-4 h-4 text-[#0071E3] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            <span x-show="(sidebarOpen || sidebarHovering)" x-transition.opacity class="whitespace-nowrap">Workspace</span>
        </a>

        <a href="{{ route('items.index') }}"
           title="Inventory"
           class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition-all {{ request()->routeIs('items.*') ? 'bg-gray-50 dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white font-semibold shadow-sm' : 'text-[#86868B] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-white hover:bg-gray-50 dark:hover:bg-[#2C2C2E]' }}"
           :class="!(sidebarOpen || sidebarHovering) && 'md:justify-center'">
            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span x-show="(sidebarOpen || sidebarHovering)" x-transition.opacity class="whitespace-nowrap">Inventory</span>
        </a>

        <a href="{{ route('batches.index') }}"
           title="Batches"
           class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition-all {{ request()->routeIs('batches.*') ? 'bg-gray-50 dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white font-semibold shadow-sm' : 'text-[#86868B] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-white hover:bg-gray-50 dark:hover:bg-[#2C2C2E]' }}"
           :class="!(sidebarOpen || sidebarHovering) && 'md:justify-center'">
            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <span x-show="(sidebarOpen || sidebarHovering)" x-transition.opacity class="whitespace-nowrap">Batches</span>
        </a>

        <a href="{{ route('orders.index') }}"
           title="Orders"
           class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition-all {{ request()->routeIs('orders.*') ? 'bg-gray-50 dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white font-semibold shadow-sm' : 'text-[#86868B] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-white hover:bg-gray-50 dark:hover:bg-[#2C2C2E]' }}"
           :class="!(sidebarOpen || sidebarHovering) && 'md:justify-center'">
            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <span x-show="(sidebarOpen || sidebarHovering)" x-transition.opacity class="whitespace-nowrap">Orders</span>
        </a>

        <a href="{{ route('deliveries.index') }}"
           title="Deliveries"
           class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition-all {{ request()->routeIs('deliveries.*') ? 'bg-gray-50 dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white font-semibold shadow-sm' : 'text-[#86868B] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-white hover:bg-gray-50 dark:hover:bg-[#2C2C2E]' }}"
           :class="!(sidebarOpen || sidebarHovering) && 'md:justify-center'">
            <svg class="w-4 h-4 text-sky-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
            <span x-show="(sidebarOpen || sidebarHovering)" x-transition.opacity class="whitespace-nowrap">Deliveries</span>
        </a>

        <a href="{{ route('expenses.index') }}"
           title="Expenses"
           class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition-all {{ request()->routeIs('expenses.*') ? 'bg-gray-50 dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white font-semibold shadow-sm' : 'text-[#86868B] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-white hover:bg-gray-50 dark:hover:bg-[#2C2C2E]' }}"
           :class="!(sidebarOpen || sidebarHovering) && 'md:justify-center'">
            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span x-show="(sidebarOpen || sidebarHovering)" x-transition.opacity class="whitespace-nowrap">Expenses</span>
        </a>

        @if(auth()->user()->isOwner())
        <div class="pt-2.5 mt-2.5 border-t border-[#E5E5EA] dark:border-[#2C2C2E] space-y-1">

            <a href="{{ route('reports.index') }}"
               title="Reports"
               class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition-all {{ request()->routeIs('reports.*') ? 'bg-gray-50 dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white font-semibold shadow-sm' : 'text-[#86868B] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-white hover:bg-gray-50 dark:hover:bg-[#2C2C2E]' }}"
               :class="!(sidebarOpen || sidebarHovering) && 'md:justify-center'">
                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span x-show="(sidebarOpen || sidebarHovering)" x-transition.opacity class="whitespace-nowrap">Reports</span>
            </a>

            <a href="{{ route('staff.index') }}"
               title="Staff"
               class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition-all {{ request()->routeIs('staff.*') ? 'bg-gray-50 dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white font-semibold shadow-sm' : 'text-[#86868B] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-white hover:bg-gray-50 dark:hover:bg-[#2C2C2E]' }}"
               :class="!(sidebarOpen || sidebarHovering) && 'md:justify-center'">
                <svg class="w-4 h-4 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span x-show="(sidebarOpen || sidebarHovering)" x-transition.opacity class="whitespace-nowrap">Staff</span>
            </a>

            <a href="{{ route('suppliers.index') }}"
               title="Suppliers"
               class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition-all {{ request()->routeIs('suppliers.*') ? 'bg-gray-50 dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white font-semibold shadow-sm' : 'text-[#86868B] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-white hover:bg-gray-50 dark:hover:bg-[#2C2C2E]' }}"
               :class="!(sidebarOpen || sidebarHovering) && 'md:justify-center'">
                <svg class="w-4 h-4 text-teal-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span x-show="(sidebarOpen || sidebarHovering)" x-transition.opacity class="whitespace-nowrap">Suppliers</span>
            </a>

        </div>
        @endif
    </nav>
</aside>
@endauth
<header class="sticky top-0 z-30 w-full apple-glass border-b border-[#E5E5EA] dark:border-[#2C2C2E] px-4 lg:px-6 py-2.5 transition-colors duration-200">
    <div class="flex items-center justify-between gap-4">

        <div class="flex items-center gap-3">
            @auth
            <button @click="toggleSidebar()"
                    title="Toggle sidebar"
                    class="p-2 rounded-xl text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200/60 dark:hover:bg-neutral-800 transition-colors border border-transparent hover:border-neutral-300/70 dark:hover:border-neutral-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            @else
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('logo.png') }}" alt="The Shoe Boy" class="w-9 h-9 rounded-xl object-cover shadow-sm border border-neutral-200 dark:border-neutral-700 shrink-0 group-hover:scale-105 transition-transform">
                <span class="font-bold text-sm tracking-tight text-[#1D1D1F] dark:text-white">THE SHOE BOY</span>
            </a>
            @endauth
        </div>

        <div class="flex items-center gap-2.5">
            <button @click="toggleTheme()"
                    title="Toggle Light / Dark Mode"
                    class="p-2 rounded-xl text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200/60 dark:hover:bg-neutral-800 transition-colors border border-transparent hover:border-neutral-300/70 dark:hover:border-neutral-700">
                <template x-if="!darkMode">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </template>
                <template x-if="darkMode">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </template>
            </button>

            @auth
            <div class="flex items-center gap-2 pl-2 border-l border-gray-200 dark:border-neutral-800">
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-bold text-[#1D1D1F] dark:text-white leading-none">{{ auth()->user()->name }}</div>
                    <div class="text-[10px] uppercase font-semibold tracking-wider mt-0.5 {{ auth()->user()->isOwner() ? 'text-amber-600 dark:text-amber-400' : 'text-[#0071E3] dark:text-[#0A84FF]' }}">
                        {{ auth()->user()->role }}
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            title="Sign out"
                            class="p-2 rounded-xl text-neutral-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
            @else
            <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-xl bg-[#0071E3] hover:bg-[#0077ED] text-white text-xs font-semibold shadow-sm transition-all">
                Sign In
            </a>
            @endauth
        </div>

    </div>
</header>
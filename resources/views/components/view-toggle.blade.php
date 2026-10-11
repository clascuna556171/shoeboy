<div data-view-toggle class="flex items-center bg-neutral-100 dark:bg-neutral-800 p-1 rounded-xl">
    <button type="button" @click="setView('cards')"
            :class="view === 'cards' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-semibold text-neutral-900 dark:text-white' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
            class="px-3 py-1.5 rounded-lg text-sm transition-all flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
        Cards
    </button>
    <button type="button" @click="setView('table')"
            :class="view === 'table' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-semibold text-neutral-900 dark:text-white' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
            class="px-3 py-1.5 rounded-lg text-sm transition-all flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        Table
    </button>
</div>

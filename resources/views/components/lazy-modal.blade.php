<div x-data
     x-show="$store.lazyModal.open"
     x-cloak
     @keydown.escape.window="$store.lazyModal.close()"
     x-effect="document.documentElement.style.overflow = $store.lazyModal.open ? 'hidden' : ''; document.body.style.overflow = $store.lazyModal.open ? 'hidden' : ''"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 dark:bg-black/50 backdrop-blur-[2px] app-modal-backdrop">
    <div class="w-full bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 rounded-3xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto"
         data-lazy-panel
         :class="{
             'max-w-sm': $store.lazyModal.size === 'sm',
             'max-w-md': $store.lazyModal.size === 'md',
             'max-w-lg': $store.lazyModal.size === 'lg'
         }"
         @click.outside="$store.lazyModal.close()">

        <div class="flex items-center justify-between gap-3 pb-3 border-b border-neutral-200 dark:border-neutral-800">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="h-2.5 w-2.5 rounded-full bg-neutral-400 shrink-0"></span>
                <div class="min-w-0">
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500" x-show="$store.lazyModal.eyebrow" x-text="$store.lazyModal.eyebrow"></div>
                    <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white truncate" x-text="$store.lazyModal.title || 'Loading…'"></h3>
                </div>
            </div>
            <button type="button" @click="$store.lazyModal.close()" class="p-2 -m-1 rounded-xl text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="lazy-modal-body pt-4 min-h-[180px]">
            {{-- Per-type skeletons (hidden instantly on load so the target height measures content only) --}}
            <div x-show="$store.lazyModal.loading && $store.lazyModal.type === 'order'">
                @include('components.skeletons.order-panel')
            </div>
            <div x-show="$store.lazyModal.loading && $store.lazyModal.type === 'receipt'">
                @include('components.skeletons.receipt-panel')
            </div>
            <div x-show="$store.lazyModal.loading && $store.lazyModal.type === 'audit'">
                @include('components.skeletons.audit-panel')
            </div>

            {{-- Fetched content (fades in as the panel height animates) --}}
            <div x-show="!$store.lazyModal.loading" x-transition.opacity.duration.200ms x-html="$store.lazyModal.html"></div>
        </div>
    </div>
</div>

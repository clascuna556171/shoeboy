<div class="space-y-4 text-sm animate-pulse" aria-hidden="true">
    {{-- Meta row --}}
    <div class="flex flex-wrap items-center gap-2">
        <div class="h-5 w-20 rounded-full bg-neutral-200 dark:bg-neutral-800"></div>
        <div class="h-5 w-24 rounded-full bg-neutral-200 dark:bg-neutral-800"></div>
        <div class="h-4 w-28 rounded bg-neutral-200 dark:bg-neutral-800"></div>
        <div class="ml-auto h-6 w-24 rounded-lg bg-neutral-200 dark:bg-neutral-800"></div>
    </div>

    {{-- Purchased pairs --}}
    <div class="space-y-2">
        <div class="h-3 w-40 rounded bg-neutral-200 dark:bg-neutral-800"></div>
        <div class="overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-800 divide-y divide-neutral-100 dark:divide-neutral-800/60">
            @for ($i = 0; $i < 2; $i++)
                <div class="flex items-center gap-3 bg-white dark:bg-[#1C1C1E] px-3.5 py-2.5">
                    <div class="h-9 w-9 shrink-0 rounded-xl bg-neutral-200 dark:bg-neutral-800"></div>
                    <div class="min-w-0 flex-1 space-y-2">
                        <div class="h-3 w-1/2 rounded bg-neutral-200 dark:bg-neutral-800"></div>
                        <div class="h-2.5 w-2/3 rounded bg-neutral-200 dark:bg-neutral-800"></div>
                    </div>
                    <div class="h-4 w-16 rounded bg-neutral-200 dark:bg-neutral-800"></div>
                </div>
            @endfor
        </div>
    </div>

    {{-- Buyer / Payment cards --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        @for ($i = 0; $i < 2; $i++)
            <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] p-3.5 space-y-2">
                <div class="h-2.5 w-16 rounded bg-neutral-200 dark:bg-neutral-800"></div>
                <div class="h-3.5 w-2/3 rounded bg-neutral-200 dark:bg-neutral-800"></div>
                <div class="h-2.5 w-1/2 rounded bg-neutral-200 dark:bg-neutral-800"></div>
            </div>
        @endfor
    </div>
</div>

<div class="space-y-4 text-sm animate-pulse" aria-hidden="true">
    {{-- Badge + time --}}
    <div class="flex flex-wrap items-center gap-2">
        <div class="h-5 w-16 rounded-full bg-neutral-200 dark:bg-neutral-800"></div>
        <div class="h-3 w-32 rounded bg-neutral-200 dark:bg-neutral-800"></div>
    </div>

    {{-- Sentence --}}
    <div class="space-y-2">
        <div class="h-3.5 w-5/6 rounded bg-neutral-200 dark:bg-neutral-800"></div>
        <div class="h-3.5 w-2/3 rounded bg-neutral-200 dark:bg-neutral-800"></div>
    </div>

    {{-- Actor / IP / When cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        @for ($i = 0; $i < 3; $i++)
            <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] p-3.5 space-y-2">
                <div class="h-2.5 w-14 rounded bg-neutral-200 dark:bg-neutral-800"></div>
                <div class="h-3.5 w-2/3 rounded bg-neutral-200 dark:bg-neutral-800"></div>
            </div>
        @endfor
    </div>

    {{-- Footer --}}
    <div class="flex items-center justify-between gap-3">
        <div class="h-2.5 w-40 rounded bg-neutral-200 dark:bg-neutral-800"></div>
        <div class="h-8 w-20 rounded-lg bg-neutral-200 dark:bg-neutral-800"></div>
    </div>
</div>

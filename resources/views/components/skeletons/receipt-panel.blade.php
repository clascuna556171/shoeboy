<div class="animate-pulse" aria-hidden="true">
    {{-- Header --}}
    <div class="text-center border-b border-dashed border-neutral-300 dark:border-neutral-700 pb-4 space-y-2">
        <div class="mx-auto h-5 w-40 rounded bg-neutral-200 dark:bg-neutral-800"></div>
        <div class="mx-auto h-2.5 w-32 rounded bg-neutral-200 dark:bg-neutral-800"></div>
    </div>

    {{-- Meta rows --}}
    <div class="space-y-2 text-xs py-4 border-b border-dashed border-neutral-300 dark:border-neutral-700">
        @for ($i = 0; $i < 5; $i++)
            <div class="flex items-center justify-between">
                <div class="h-3 w-16 rounded bg-neutral-200 dark:bg-neutral-800"></div>
                <div class="h-3 w-24 rounded bg-neutral-200 dark:bg-neutral-800"></div>
            </div>
        @endfor
    </div>

    {{-- Items table --}}
    <div class="py-4 border-b border-dashed border-neutral-300 dark:border-neutral-700">
        <div class="flex items-center justify-between mb-3">
            <div class="h-2.5 w-10 rounded bg-neutral-200 dark:bg-neutral-800"></div>
            <div class="h-2.5 w-8 rounded bg-neutral-200 dark:bg-neutral-800"></div>
            <div class="h-2.5 w-12 rounded bg-neutral-200 dark:bg-neutral-800"></div>
        </div>
        <div class="space-y-3">
            @for ($i = 0; $i < 2; $i++)
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1.5">
                        <div class="h-3 w-32 rounded bg-neutral-200 dark:bg-neutral-800"></div>
                        <div class="h-2.5 w-24 rounded bg-neutral-200 dark:bg-neutral-800"></div>
                    </div>
                    <div class="h-3 w-12 rounded bg-neutral-200 dark:bg-neutral-800"></div>
                </div>
            @endfor
        </div>
    </div>

    {{-- Totals --}}
    <div class="py-4 space-y-2 border-b border-dashed border-neutral-300 dark:border-neutral-700">
        @for ($i = 0; $i < 4; $i++)
            <div class="flex items-center justify-between">
                <div class="h-3 w-16 rounded bg-neutral-200 dark:bg-neutral-800"></div>
                <div class="h-3 w-20 rounded bg-neutral-200 dark:bg-neutral-800"></div>
            </div>
        @endfor
    </div>

    {{-- Footer + print button --}}
    <div class="pt-5">
        <div class="mx-auto h-2.5 w-48 rounded bg-neutral-200 dark:bg-neutral-800"></div>
    </div>
    <div class="mt-4 flex justify-end">
        <div class="h-8 w-28 rounded-lg bg-neutral-200 dark:bg-neutral-800"></div>
    </div>
</div>

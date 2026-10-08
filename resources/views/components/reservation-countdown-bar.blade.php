@props(['hint' => 'Auto-releases'])

{{-- Rendered inside an element that carries x-data="reservationCountdown(...)";
     it reads the `urgent`, `label` and `percent` state from that scope. --}}
<div class="space-y-1.5">
    <div class="flex items-center justify-between gap-2 text-[11px]">
        <span class="inline-flex items-center gap-1 font-semibold"
              :class="urgent ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400'">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span x-text="'Expires in ' + label"></span>
        </span>
        <span class="text-neutral-400 dark:text-neutral-500">{{ $hint }}</span>
    </div>
    <div class="h-1.5 w-full overflow-hidden rounded-full bg-amber-200/60 dark:bg-amber-900/40">
        <div class="h-full rounded-full transition-[width] duration-1000 ease-linear"
             :class="urgent ? 'bg-rose-500' : 'bg-amber-500'"
             :style="'width:' + percent + '%'"></div>
    </div>
</div>

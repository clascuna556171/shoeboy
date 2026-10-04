@props(['log'])

<x-modal eyebrow="Audit Entry" :title="$log->description" accent="neutral" {{ $attributes }}>
    <div class="space-y-4 text-sm">
        <div class="flex flex-wrap items-center gap-2">
            <span class="badge {{ $log->category_badge_class }}">{{ $log->category }}</span>
            <span class="font-mono text-xs text-neutral-500">{{ $log->created_at->format('M d, Y g:i:s A') }}</span>
        </div>

        <p class="leading-snug text-neutral-700 dark:text-neutral-200">{{ $log->sentence }}</p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] p-3.5">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500 mb-1">Actor</div>
                <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $log->user?->name ?? 'System' }}</div>
                @if($log->user?->role)
                    <div class="text-xs text-neutral-500 capitalize">{{ $log->user->role }}</div>
                @endif
            </div>
            <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] p-3.5">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500 mb-1">IP Address</div>
                <div class="font-mono text-neutral-800 dark:text-neutral-200">{{ $log->ip_address ?? '—' }}</div>
            </div>
            <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] p-3.5">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500 mb-1">When</div>
                <div class="font-mono text-neutral-800 dark:text-neutral-200">{{ $log->created_at->format('M d, Y g:i:s A') }}</div>
            </div>
        </div>

        @if($log->detail_items)
        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-neutral-50/70 dark:bg-neutral-800/30 p-3.5">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500 mb-2">Recorded details</div>
            <x-audit-details :log="$log" />
        </div>
        @endif

        <div class="flex items-center justify-between gap-3">
            <div class="text-[11px] text-neutral-500 font-mono">action: {{ $log->action }} · id: {{ $log->id }}</div>
            @if($log->link)
                <x-action-btn icon="eye" tone="secondary" :href="$log->link['url']">{{ $log->link['label'] }}</x-action-btn>
            @endif
        </div>
    </div>
</x-modal>

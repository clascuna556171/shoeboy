@extends('layouts.app')

@section('title', 'Security & Audit Log')

@section('content')
<div class="space-y-6">

    {{-- Page header --}}
    <div class="app-card p-5 lg:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="min-w-0">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Owner Access Only</div>
            <h1 class="text-xl md:text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-0.5">Security &amp; Audit Log</h1>
            <p class="text-xs text-neutral-500 mt-1">Every sign-in and system change, in order.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-[#0071E3] dark:text-[#0A84FF] hover:underline shrink-0">
            &larr; Back to workspace
        </a>
    </div>

    <x-table-toolbar :action="route('audit.index')" :search="$search"
                     search-placeholder="Search action, user, or IP..."
                     :reset-url="route('audit.index')"
                     :filters="[
                         ['name' => 'category', 'selected' => $category, 'options' => [
                             '' => 'All Categories',
                             'security' => 'Security & Sign-ins',
                             'inventory' => 'Inventory & Batches',
                             'sales' => 'Sales & Payments',
                             'finance' => 'Finance',
                             'admin' => 'Admin',
                         ]],
                     ]" />

    {{-- Table --}}
    <div class="app-card">
        <div class="overflow-x-auto lg:overflow-visible">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <x-sort-th-server column="created_at" label="When" />
                        <th class="py-3 px-4 font-semibold">Event</th>
                        <th class="py-3 px-4 font-semibold">Category</th>
                        <th class="py-3 px-4 font-semibold">Actor</th>
                        <th class="py-3 px-4 font-semibold">IP</th>
                        <th class="py-3 px-4 font-semibold text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    @forelse($logs as $log)
                    <tr class="app-row align-top hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="block font-mono text-xs text-neutral-700 dark:text-neutral-200">{{ $log->created_at->format('M d, Y') }}</span>
                            <span class="text-[11px] text-neutral-500 font-mono">{{ $log->created_at->format('g:i:s A') }}</span>
                        </td>
                        <td class="py-3.5 px-4 max-w-xl">
                            <p class="leading-snug text-neutral-700 dark:text-neutral-200">{{ $log->sentence }}</p>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="badge {{ $log->category_badge_class }}">{{ $log->category }}</span>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="font-medium text-neutral-700 dark:text-neutral-200">{{ $log->user?->name ?? 'System' }}</span>
                            @if($log->user?->role)
                                <span class="block text-[11px] text-neutral-500 capitalize">{{ $log->user->role }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-neutral-500 whitespace-nowrap">{{ $log->ip_address ?? '—' }}</td>
                        <td class="py-3.5 px-4 text-right">
                            <button type="button" data-panel-url="{{ route('panels.show', ['type' => 'audit', 'id' => $log->id]) }}"
                                    class="app-btn app-btn-sm app-btn-secondary">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="app-empty">
                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3l7 4v5c0 4.418-3.582 8-7 9-3.418-1-7-4.582-7-9V7l7-4z"/></svg>
                                <span class="text-xs font-medium">No audit entries match.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-neutral-800">
            {{ $logs->links() }}
        </div>
        @endif

    </div>

</div>
@endsection

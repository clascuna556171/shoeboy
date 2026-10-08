@extends('layouts.app')

@section('title', 'Data & Backups')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="app-card p-5 lg:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="min-w-0">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Owner Tools</div>
            <h1 class="text-xl md:text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-0.5">Data Backup &amp; Recovery</h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Keep local copies of the database and restore the business data whenever needed.</p>
        </div>
        <div data-tour="primary" class="flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('backups.download') }}"
               class="app-btn app-btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                <span>Download backup</span>
            </a>
            <form action="{{ route('backups.store') }}" method="POST">
                @csrf
                <button class="app-btn app-btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Create backup</span>
                </button>
            </form>
        </div>
    </div>

    @if($isEmpty)
    {{-- Empty install: nudge an import --}}
    <div class="app-card p-6 border-2 border-dashed border-emerald-300 dark:border-emerald-900/60 bg-emerald-50/40 dark:bg-emerald-950/10">
        <div class="flex items-start gap-4">
            <span class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 bg-emerald-100 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5-5 5 5M12 5v12"/></svg>
            </span>
            <div class="min-w-0">
                <h2 class="font-bold text-base text-[#1D1D1F] dark:text-white">This installation is empty</h2>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-0.5">Import an existing <span class="font-mono">.sqlite</span> backup below to bring your inventory, orders, and records back.</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Import / restore from file --}}
    <div class="app-card p-5 lg:p-6">
        <h2 class="font-bold text-base text-[#1D1D1F] dark:text-white">Restore / Import a backup</h2>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Upload a <span class="font-mono">.sqlite</span> backup file. The current database is snapshotted first, so an import can always be rolled back.</p>

        <form action="{{ route('backups.import') }}" method="POST" enctype="multipart/form-data" class="mt-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            @csrf
            <input type="file" name="backup" accept=".sqlite,.db,application/x-sqlite3,application/octet-stream" required
                   class="app-input flex-1 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-neutral-100 dark:file:bg-neutral-800 file:text-neutral-700 dark:file:text-neutral-200 file:text-xs file:font-semibold">
            <button type="button"
                    data-confirm="Import and restore this backup?"
                    data-confirm-message="Your current database will be replaced with the uploaded file. A safety snapshot of the current data is taken first."
                    data-confirm-variant="danger"
                    data-confirm-icon="warning"
                    data-confirm-label="Import &amp; restore"
                    class="app-btn app-btn-amber shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Import backup</span>
            </button>
        </form>
    </div>

    {{-- Stored backups --}}
    <div class="app-card" data-tour="list">
        <div class="px-5 lg:px-6 py-4 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-base text-[#1D1D1F] dark:text-white">Stored backups</h2>
                <p class="text-xs text-neutral-500 mt-0.5">{{ count($backups) }} snapshot(s) on this device. The most recent {{ \App\Services\BackupService::MAX_BACKUPS }} are kept automatically.</p>
            </div>
        </div>

        <div class="overflow-x-auto lg:overflow-visible">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <th class="py-3 px-4 font-semibold">File</th>
                        <th class="py-3 px-4 font-semibold">Created</th>
                        <th class="py-3 px-4 font-semibold text-right">Size</th>
                        <th class="py-3 px-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    @forelse($backups as $b)
                    <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3.5 px-4 font-mono text-neutral-800 dark:text-neutral-200">{{ $b['name'] }}</td>
                        <td class="py-3.5 px-4 text-neutral-600 dark:text-neutral-300">
                            {{ \Illuminate\Support\Carbon::createFromTimestamp($b['modified'])->format('M d, Y g:i A') }}
                            <span class="block text-[11px] text-neutral-400">{{ \Illuminate\Support\Carbon::createFromTimestamp($b['modified'])->diffForHumans() }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-neutral-600 dark:text-neutral-300">
                            {{ $b['size'] >= 1048576 ? number_format($b['size'] / 1048576, 2).' MB' : number_format($b['size'] / 1024, 1).' KB' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('backups.file', $b['name']) }}" class="app-btn app-btn-secondary app-btn-sm">Download</a>

                                <form action="{{ route('backups.restore', $b['name']) }}" method="POST">
                                    @csrf
                                    <button type="button"
                                            data-confirm="Restore this backup?"
                                            data-confirm-message="The current database will be replaced with {{ $b['name'] }}. A safety snapshot is taken first."
                                            data-confirm-variant="danger"
                                            data-confirm-icon="warning"
                                            data-confirm-label="Restore"
                                            class="app-btn app-btn-amber app-btn-sm">Restore</button>
                                </form>

                                <form action="{{ route('backups.destroy', $b['name']) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            data-confirm="Delete this backup?"
                                            data-confirm-message="{{ $b['name'] }} will be permanently removed."
                                            data-confirm-variant="danger"
                                            data-confirm-icon="trash"
                                            data-confirm-label="Delete"
                                            class="app-btn app-btn-ghost app-btn-sm text-rose-600 dark:text-rose-400">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">
                            <div class="app-empty">
                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-5L9 5H6a2 2 0 00-2 2z"/></svg>
                                <span class="text-xs font-medium">No backups yet. Create one above.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

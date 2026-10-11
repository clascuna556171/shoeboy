@props(['variant' => 'header', 'showIdentity' => true])

@php
    $user = auth()->user();
    $initials = collect(explode(' ', trim((string) $user->name)))
        ->filter()
        ->map(fn ($word) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($word, 0, 1)))
        ->take(2)
        ->implode('');
    $isOwner = $user->isOwner();
@endphp

<div class="relative {{ $variant === 'rail' ? 'w-full' : '' }}"
     x-data="{ open: false }"
     @keydown.escape.window="open = false">
    <button type="button" @click="open = !open" title="{{ $user->name }}"
            data-tour="profile"
            class="flex items-center gap-2.5 rounded-xl px-2 py-1.5 transition-colors hover:bg-neutral-200/60 dark:hover:bg-neutral-800 {{ $variant === 'rail' ? 'w-full app-rail-item' : '' }}">
        <span class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-bold bg-neutral-700 text-white dark:bg-neutral-600 dark:text-neutral-100 shrink-0">{{ $initials }}</span>
        @if($showIdentity)
        <span class="app-rail-label flex flex-col items-start leading-tight min-w-0 text-left">
            <span class="text-xs font-bold text-[#1D1D1F] dark:text-white truncate max-w-[10rem]">{{ $user->name }}</span>
            <span class="text-[10px] uppercase tracking-wider {{ $isOwner ? 'text-amber-600 dark:text-amber-400' : 'text-neutral-500 dark:text-neutral-400' }}">{{ $user->role }}</span>
        </span>
        <svg class="app-rail-label w-3.5 h-3.5 text-neutral-400 shrink-0 {{ $variant === 'rail' ? 'ml-auto' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        @endif
    </button>

    <div x-show="open" x-cloak
         @click.outside="open = false"
         x-transition:enter="transition ease-out duration-120"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute z-[120] w-64 rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] shadow-xl p-1.5 {{ $variant === 'rail' ? 'left-full bottom-0 ml-2' : 'right-0 mt-2' }}">
        <div class="px-3 py-2 border-b border-neutral-100 dark:border-neutral-800 mb-1">
            <div class="text-xs font-bold text-[#1D1D1F] dark:text-white truncate">{{ $user->name }}</div>
            <div class="text-[10px] uppercase tracking-wider {{ $isOwner ? 'text-amber-600 dark:text-amber-400' : 'text-neutral-500 dark:text-neutral-400' }}">{{ $user->role }}</div>
        </div>

        <a href="{{ route('help.index') }}"
           class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Help &amp; Guide</span>
        </a>

        <button type="button" @click="open = false; settingsOpen = true"
                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Settings</span>
        </button>

        @if($isOwner)
        <a href="{{ route('backups.index') }}"
           class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-5L9 5H6a2 2 0 00-2 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12v4m-2-2h4"/></svg>
            <span>Data &amp; Backups</span>
        </a>
        @endif

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="button"
                    data-confirm="Sign out?"
                    data-confirm-variant="neutral"
                    data-confirm-icon="logout"
                    data-confirm-message="You will be returned to the login screen."
                    data-confirm-label="Sign out"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>Sign out</span>
            </button>
        </form>
    </div>
</div>

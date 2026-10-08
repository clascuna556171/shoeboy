@props(['variant' => 'header', 'showIdentity' => true, 'pages' => []])

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

        @if(!empty($pages))
        <div x-data="{ guides: false }">
            <button type="button" @click="guides = !guides"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                <svg class="w-4 h-4 text-[#0071E3] dark:text-[#0A84FF] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span>Page guides</span>
                <svg class="w-3.5 h-3.5 ml-auto text-neutral-400 transition-transform" :class="guides && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>

            <div x-show="guides" x-cloak x-transition.opacity.duration.120ms
                 class="max-h-72 overflow-y-auto pr-0.5 pb-1 space-y-0.5">
                @foreach($pages as $page)
                    @if(!empty($page['children']))
                    <div x-data="{ sub: false }">
                        <button type="button" @click="sub = !sub"
                                class="w-full flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-sm text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                            <svg class="w-4 h-4 text-sky-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span>{{ $page['label'] }}</span>
                            <svg class="w-3.5 h-3.5 ml-auto text-neutral-400 transition-transform" :class="sub && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                        <div x-show="sub" x-cloak class="ml-5 border-l border-neutral-200 dark:border-neutral-800 pl-2 pb-1 space-y-0.5">
                            <a href="{{ $page['url'] }}"
                               onclick="try{localStorage.setItem('shoeboy.tour.pending','{{ $page['key'] }}')}catch(e){}"
                               @click="open = false"
                               class="block px-3 py-1.5 rounded-lg text-sm text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">{{ $page['label'] }} overview</a>
                            @foreach($page['children'] as $child)
                            <a href="{{ $child['url'] }}"
                               onclick="try{localStorage.setItem('shoeboy.tour.pending','{{ $child['key'] }}')}catch(e){}"
                               @click="open = false"
                               class="block px-3 py-1.5 rounded-lg text-sm text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">{{ $child['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <a href="{{ $page['url'] }}"
                       onclick="try{localStorage.setItem('shoeboy.tour.pending','{{ $page['key'] }}')}catch(e){}"
                       @click="open = false"
                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-sm text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-4 h-4 text-[#0071E3] dark:text-[#0A84FF] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $page['label'] }}</span>
                    </a>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="my-1 border-t border-neutral-100 dark:border-neutral-800"></div>
        @endif

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
                    data-confirm-variant="danger"
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

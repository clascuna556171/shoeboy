<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      :class="{ 'dark': darkMode }"
      x-data="{
          theme: (localStorage.getItem('shoeboy.theme') || (localStorage.getItem('shoeboy_theme') === 'dark' ? 'dark' : (localStorage.getItem('shoeboy_theme') === 'light' ? 'light' : null)) || 'system'),
          darkMode: false,
          density: localStorage.getItem('shoeboy.density') || 'compact',
          navMode: localStorage.getItem('shoeboy.nav') || 'top',
          railCollapsed: localStorage.getItem('shoeboy.nav.collapsed') === '1',
          settingsOpen: false,
          showTourPrompt: false,
          init() {
              this.applyTheme();
              this.applyDensity();
              this.applyNav();
              try { this.showTourPrompt = !localStorage.getItem('shoeboy.tour.seen'); } catch (e) {}
              try {
                  var pending = localStorage.getItem('shoeboy.tour.pending');
                  if (pending) {
                      localStorage.removeItem('shoeboy.tour.pending');
                      this.showTourPrompt = false;
                      setTimeout(function () { window.appTour && window.appTour.start(pending); }, 450);
                  }
              } catch (e) {}
              window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => { if (this.theme === 'system') this.applyTheme(); });
          },
          dismissTourPrompt() {
              this.showTourPrompt = false;
              try { localStorage.setItem('shoeboy.tour.seen', '1'); } catch (e) {}
          },
          resolvedDark() {
              return this.theme === 'dark' || (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
          },
          applyTheme() {
              this.darkMode = this.resolvedDark();
              document.documentElement.classList.toggle('dark', this.darkMode);
          },
          setTheme(t) {
              this.theme = t;
              localStorage.setItem('shoeboy.theme', t);
              localStorage.setItem('shoeboy_theme', this.resolvedDark() ? 'dark' : 'light');
              this.applyTheme();
          },
          toggleTheme() { this.setTheme(this.darkMode ? 'light' : 'dark'); },
          setDensity(d) { this.density = d; localStorage.setItem('shoeboy.density', d); this.applyDensity(); },
          applyDensity() { document.documentElement.setAttribute('data-density', this.density); },
          setNav(m) { this.navMode = m; localStorage.setItem('shoeboy.nav', m); this.applyNav(); },
          applyNav() {
              document.documentElement.setAttribute('data-nav', this.navMode);
              document.documentElement.setAttribute('data-rail', this.railCollapsed ? 'collapsed' : 'expanded');
          },
          toggleRail() { this.railCollapsed = !this.railCollapsed; localStorage.setItem('shoeboy.nav.collapsed', this.railCollapsed ? '1' : '0'); this.applyNav(); }
      }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'The Shoe Boy') }} - @yield('title', 'Order & Inventory System')</title>

    <script>
        (function () {
            var t = localStorage.getItem('shoeboy.theme') || localStorage.getItem('shoeboy_theme');
            var dark = t === 'dark' || ((t === 'system' || t === null) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.setAttribute('data-density', localStorage.getItem('shoeboy.density') || 'compact');
            document.documentElement.setAttribute('data-nav', localStorage.getItem('shoeboy.nav') || 'top');
            document.documentElement.setAttribute('data-rail', localStorage.getItem('shoeboy.nav.collapsed') === '1' ? 'collapsed' : 'expanded');
        })();
    </script>

    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ Vite::fonts() }}
</head>
@php
    // Current page's tour key (used by the body data-page + Help "Take the tour").
    $routeName = request()->route()?->getName() ?? '';
    $isOwnerPage = auth()->check() && auth()->user()->isOwner();
    if ($routeName === 'dashboard') {
        $pageKey = $isOwnerPage ? 'dashboard' : 'console';
    } else {
        $pageKey = match (true) {
            str_starts_with($routeName, 'batches.') => 'batches',
            str_starts_with($routeName, 'items.') => 'inventory',
            $routeName === 'staff.workspace' => 'console',
            str_starts_with($routeName, 'orders.') => 'orders',
            str_starts_with($routeName, 'deliveries.') => 'deliveries',
            str_starts_with($routeName, 'expenses.') => 'expenses',
            str_starts_with($routeName, 'reports.') => 'reports',
            $routeName === 'staff.index' => 'staff',
            str_starts_with($routeName, 'suppliers.') => 'suppliers',
            str_starts_with($routeName, 'backups.') => 'backups',
            default => 'dashboard',
        };
    }
@endphp
<body data-page="{{ $pageKey }}" class="bg-[#F5F5F7] dark:bg-[#121214] text-[#1D1D1F] dark:text-[#F5F5F7] font-sans antialiased min-h-screen flex flex-col transition-colors duration-200 selection:bg-[#0071E3] selection:text-white">

    <div id="app-progress" class="app-progress" aria-hidden="true">
        <div class="app-progress__bar"></div>
    </div>

    @php
        $isOwner = auth()->check() && auth()->user()->isOwner();

        // Ordered by daily workflow: workspace → intake → sell → fulfil → money → admin.
        $navItems = [
            ['route' => 'dashboard',        'pattern' => 'dashboard',   'label' => 'Workspace',  'tint' => 'text-blue-400',    'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
            ['route' => 'batches.index',    'pattern' => 'batches.*',   'label' => 'Batches',    'tint' => 'text-amber-400',    'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['route' => 'items.index',      'pattern' => 'items.*',     'label' => 'Inventory',  'tint' => 'text-emerald-400', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
        ];

        if (! $isOwner) {
            // Staff reach their console at both / and /console.
            $navItems[0]['pattern'] = ['dashboard', 'staff.workspace'];
        }

        if ($isOwner) {
            $navItems[] = ['route' => 'staff.workspace', 'pattern' => 'staff.workspace', 'label' => 'Console', 'tint' => 'text-sky-400', 'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'];
        }

        $navItems[] = ['route' => 'orders.index',     'pattern' => 'orders.*',    'label' => 'Orders',     'tint' => 'text-rose-400',     'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'];
        $navItems[] = ['route' => 'deliveries.index', 'pattern' => 'deliveries.*','label' => 'Deliveries', 'tint' => 'text-sky-400',      'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'];
        $navItems[] = ['route' => 'expenses.index',   'pattern' => 'expenses.*',  'label' => 'Expenses',   'tint' => 'text-rose-400',     'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];

        if ($isOwner) {
            $navItems[] = ['route' => 'reports.index',  'pattern' => 'reports.*',  'label' => 'Reports',  'tint' => 'text-indigo-400', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'];
            $navItems[] = ['route' => 'staff.index',    'pattern' => 'staff.index',    'label' => 'Staff',    'tint' => 'text-violet-400', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'];
            $navItems[] = ['route' => 'suppliers.index','pattern' => 'suppliers.*','label' => 'Suppliers','tint' => 'text-teal-400',   'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'];
        }

        // Anchor key per nav route, used by the general tour to name each module.
        $navTour = [
            'dashboard' => 'nav-workspace',
            'batches.index' => 'nav-batches',
            'items.index' => 'nav-inventory',
            'staff.workspace' => 'nav-console',
            'orders.index' => 'nav-orders',
            'deliveries.index' => 'nav-deliveries',
            'expenses.index' => 'nav-expenses',
            'reports.index' => 'nav-reports',
            'staff.index' => 'nav-staff',
            'suppliers.index' => 'nav-suppliers',
        ];

        // Page guides (profile dropdown) — derived from the shared guide config.
        $guideRole = $isOwner ? 'owner' : 'staff';
        $guide = config('guides');
        $allowed = fn ($page) => empty($page['roles']) || in_array($guideRole, $page['roles'], true);
        $consoleTabs = [
            ['key' => 'console:claims', 'label' => 'Live Claims', 'url' => route('staff.workspace', ['tab' => 'claims'])],
            ['key' => 'console:pos', 'label' => 'Walk-in POS', 'url' => route('staff.workspace', ['tab' => 'pos'])],
            ['key' => 'console:triage', 'label' => 'Triage Table', 'url' => route('staff.workspace', ['tab' => 'triage'])],
        ];
        $guidePages = [];
        $tourPages = [];
        foreach ($guide['pages'] as $key => $page) {
            if (! $allowed($page)) {
                continue;
            }
            $tourPages[$key] = ['label' => $page['label'] ?? $key, 'steps' => $page['steps'] ?? []];

            if (empty($page['route'])) {
                continue; // tab-only entries (console:claims, …) have no menu link
            }
            $entry = [
                'key' => $key,
                'label' => (! $isOwner && $key === 'console') ? 'Workspace' : ($page['label'] ?? $key),
                'url' => route($page['route']),
            ];
            if ($key === 'console') {
                $entry['children'] = $consoleTabs;
            }
            $guidePages[] = $entry;
        }

        $guideJs = [
            'role' => $guideRole,
            'helpUrl' => route('help.index'),
            'general' => ['label' => $guide['general']['label'] ?? 'Overview', 'steps' => $guide['general']['steps'] ?? []],
            'pages' => $tourPages,
        ];
    @endphp

    <script>window.__GUIDES__ = @json($guideJs);</script>

    <div class="app-shell">

    @auth
    {{-- Optional left-rail navigation (Settings → Navigation) --}}
    <aside class="app-rail" aria-label="Primary">
        <div class="flex h-full flex-col">
            <nav data-tour="nav" class="flex-1 overflow-y-auto px-2 py-3 space-y-1">
                @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}"
                   data-tour="{{ $navTour[$item['route']] ?? '' }}"
                   class="app-rail-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition-colors {{ request()->routeIs($item['pattern']) ? 'app-rail-item--active' : 'text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200/60 dark:hover:bg-neutral-800' }}">
                    <svg class="shrink-0 {{ $item['tint'] }}" style="width:1.125rem;height:1.125rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                    <span class="app-rail-label truncate">{{ $item['label'] }}</span>
                </a>
                @endforeach
            </nav>
            <div class="p-2 space-y-1 border-t border-neutral-200 dark:border-neutral-800">
                    <x-user-menu variant="rail" :pages="$guidePages" />
                <button type="button" @click="toggleRail()" title="Collapse / expand menu"
                        class="app-rail-item w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-neutral-500 hover:bg-neutral-200/60 dark:hover:bg-neutral-800 transition-colors">
                    <svg class="app-rail-toggle shrink-0" style="width:1.125rem;height:1.125rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                    <span class="app-rail-label truncate">Collapse</span>
                </button>
            </div>
        </div>
    </aside>
    @endauth

    <header x-data="{ mobileNav: false }"
            class="sticky top-0 z-40 w-full apple-glass border-b border-[#E5E5EA] dark:border-[#2C2C2E] px-4 lg:px-6 py-2.5 transition-colors duration-200">
        <div class="max-w-[1720px] mx-auto flex items-center justify-between gap-4">

            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('logo.png') }}" alt="The Shoe Boy" class="w-9 h-9 rounded-xl object-cover shadow-sm border border-neutral-200 dark:border-neutral-700 shrink-0 group-hover:scale-105 transition-transform">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm tracking-tight text-[#1D1D1F] dark:text-white whitespace-nowrap">THE SHOE BOY</span>
                        <span class="text-[11px] text-neutral-500 font-normal hidden sm:inline">Davao</span>
                    </div>
                </a>
            </div>

            @auth
            <nav data-tour="nav" class="app-topnav hidden lg:flex items-center bg-neutral-100 dark:bg-neutral-800 p-1 rounded-xl border border-neutral-200 dark:border-neutral-800 text-xs">
                @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   data-tour="{{ $navTour[$item['route']] ?? '' }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-all {{ request()->routeIs($item['pattern']) ? 'bg-white dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white font-semibold shadow-sm' : 'text-neutral-500 dark:text-neutral-400 hover:bg-neutral-200/60 dark:hover:bg-neutral-700/30 hover:text-[#1D1D1F] dark:hover:text-white' }}">
                    <svg class="w-3.5 h-3.5 {{ $item['tint'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                    <span class="whitespace-nowrap">{{ $item['label'] }}</span>
                </a>
                @endforeach
            </nav>
            @endauth

            <div class="flex items-center gap-2.5 shrink-0">
                @auth
                <div x-show="navMode !== 'rail'" class="pl-2 border-l border-neutral-200 dark:border-neutral-800">
                    <x-user-menu variant="header" :show-identity="false" :pages="$guidePages" />
                </div>

                <button type="button"
                        @click="mobileNav = !mobileNav"
                        title="Menu"
                        class="lg:hidden p-2 rounded-xl text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200/60 dark:hover:bg-neutral-800 transition-colors">
                    <svg x-show="!mobileNav" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileNav" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                @else
                <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 text-xs font-semibold shadow-sm transition-all">
                    Sign In
                </a>
                @endauth
            </div>

        </div>

        @auth
        <div x-show="mobileNav"
             x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="lg:hidden mt-3 max-w-[1720px] mx-auto grid grid-cols-2 sm:grid-cols-3 gap-2 pb-1">
            @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}"
               data-tour="{{ $navTour[$item['route']] ?? '' }}"
               class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-xs border transition-colors {{ request()->routeIs($item['pattern']) ? 'bg-neutral-900 text-white border-transparent font-semibold dark:bg-white dark:text-neutral-900' : 'bg-white dark:bg-[#1C1C1E] border-neutral-200 dark:border-neutral-800 text-neutral-700 dark:text-neutral-200' }}">
                <svg class="w-4 h-4 {{ $item['tint'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                <span>{{ $item['label'] }}</span>
            </a>
            @endforeach
        </div>
        @endauth
    </header>

    <main class="flex-1 w-full max-w-[1720px] mx-auto p-4 lg:p-6 transition-all duration-200">
        @php
            $receiptLink = session('receipt');
            $toasts = [];
            if (session('success')) { $toasts[] = ['type' => 'success', 'message' => session('success'), 'link' => $receiptLink]; }
            if (session('info')) { $toasts[] = ['type' => 'info', 'message' => session('info')]; }
            if (session('error')) { $toasts[] = ['type' => 'error', 'message' => session('error')]; }
            if (session('undo')) { $toasts[] = ['type' => 'undo', 'message' => session('undo')['message'] ?? 'Record deleted.', 'undo' => session('undo')['url'] ?? null]; }

            // Validation errors become toasts (login keeps its inline field hints).
            if (! request()->routeIs('login')) {
                foreach ($errors->all() as $err) {
                    $toasts[] = ['type' => 'error', 'message' => $err, 'duration' => 6000];
                }
            }
        @endphp

        {{-- Floating toasts --}}
        <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 w-[min(92vw,22rem)] pointer-events-none"
             x-data="toastHub({{ Js::from($toasts) }})">
            <template x-for="t in toasts" :key="t.id">
                <div class="pointer-events-auto relative overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] shadow-lg p-3.5 flex items-start gap-3 app-modal-panel">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                          :class="{
                              'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400': t.type === 'success',
                              'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400': t.type === 'error',
                              'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300': t.type === 'info',
                              'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400': t.type === 'undo'
                          }">
                        <template x-if="t.type === 'success'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <template x-if="t.type === 'error'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        </template>
                        <template x-if="t.type === 'info'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </template>
                        <template x-if="t.type === 'undo'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </template>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-neutral-700 dark:text-neutral-200" x-text="t.message"></p>
                        <form x-show="t.undo" :action="t.undo" method="POST" class="mt-1.5">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Undo
                            </button>
                        </form>
                        <template x-if="t.link">
                            <a :href="t.link.url" class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold text-[#0071E3] dark:text-[#0A84FF] hover:underline">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span x-text="t.link.label"></span>
                            </a>
                        </template>
                    </div>
                    <button type="button" @click="dismiss(t.id)" class="p-1 rounded-lg text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="app-toast-progress" :class="'type-' + t.type" :style="'animation-duration: ' + t.duration + 'ms'"></div>
                </div>
            </template>
        </div>

        @yield('content')
    </main>

    <footer class="mt-auto py-6 border-t border-neutral-200/80 dark:border-neutral-800/80 text-center text-xs text-neutral-500">
        <div class="max-w-[1720px] mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                <span class="font-semibold text-neutral-600 dark:text-neutral-400">The Shoe Boy</span> &copy; {{ date('Y') }}
            </div>
            <div class="text-[11px] text-neutral-500">
                Order &amp; Inventory Management Console
            </div>
        </div>
    </footer>

    </div>{{-- /.app-shell --}}

    @auth
    {{-- First-run tour prompt --}}
    <div x-show="showTourPrompt" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed bottom-4 left-1/2 -translate-x-1/2 z-[105] w-[min(92vw,26rem)] pointer-events-none">
        <div class="pointer-events-auto flex items-center gap-3 rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1C1C1E] shadow-xl p-3.5">
            <span class="w-9 h-9 rounded-xl flex items-center justify-center bg-[#0071E3]/10 text-[#0071E3] dark:text-[#0A84FF] shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-semibold text-[#1D1D1F] dark:text-white">New here?</div>
                <div class="text-xs text-neutral-500">Take a quick or full tour of the system.</div>
            </div>
            <button type="button" @click="showTourPrompt = false; window.appTour && window.appTour.start('', 'essentials')" class="app-btn app-btn-secondary app-btn-sm shrink-0">Quick</button>
            <button type="button" @click="showTourPrompt = false; window.appTour && window.appTour.start('', 'full')" class="app-btn app-btn-primary app-btn-sm shrink-0">Full tour</button>
            <button type="button" @click="dismissTourPrompt()" title="Dismiss"
                    class="p-1.5 rounded-lg text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    @endauth

    {{-- Settings modal (theme / density / navigation) --}}
    <div x-show="settingsOpen" x-cloak
         x-effect="settingsOpen ? window.appScrollLock?.lock() : window.appScrollLock?.unlock()"
         @keydown.escape.window="settingsOpen = false"
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/30 dark:bg-black/50 backdrop-blur-[2px] app-modal-backdrop">
        <div class="app-modal-panel w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-3xl border border-neutral-200 dark:border-neutral-800 shadow-2xl p-6"
             @click.outside="settingsOpen = false">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white">Settings</h3>
                <button type="button" @click="settingsOpen = false" class="p-1.5 rounded-lg text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Theme --}}
            <div class="mt-6">
                <div class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Theme</div>
                <div class="mt-2 grid grid-cols-3 gap-2">
                    <button type="button" @click="setTheme('light')"
                            class="app-btn app-btn-sm" :class="theme === 'light' ? 'app-btn-primary' : 'app-btn-secondary'">Light</button>
                    <button type="button" @click="setTheme('dark')"
                            class="app-btn app-btn-sm" :class="theme === 'dark' ? 'app-btn-primary' : 'app-btn-secondary'">Dark</button>
                    <button type="button" @click="setTheme('system')"
                            class="app-btn app-btn-sm" :class="theme === 'system' ? 'app-btn-primary' : 'app-btn-secondary'">System</button>
                </div>
            </div>

            {{-- Density --}}
            <div class="mt-6">
                <div class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Table density</div>
                <div class="mt-2 grid grid-cols-3 gap-2">
                    <button type="button" @click="setDensity('comfortable')"
                            class="app-btn app-btn-sm" :class="density === 'comfortable' ? 'app-btn-primary' : 'app-btn-secondary'">Comfortable</button>
                    <button type="button" @click="setDensity('cozy')"
                            class="app-btn app-btn-sm" :class="density === 'cozy' ? 'app-btn-primary' : 'app-btn-secondary'">Cozy</button>
                    <button type="button" @click="setDensity('compact')"
                            class="app-btn app-btn-sm" :class="density === 'compact' ? 'app-btn-primary' : 'app-btn-secondary'">Compact</button>
                </div>
                <p class="mt-1.5 text-[11px] text-neutral-500">Controls row padding across all tables.</p>
            </div>

            {{-- Navigation --}}
            <div class="mt-6">
                <div class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Navigation</div>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <button type="button" @click="setNav('top')"
                            class="app-btn app-btn-sm" :class="navMode === 'top' ? 'app-btn-primary' : 'app-btn-secondary'">Top bar</button>
                    <button type="button" @click="setNav('rail')"
                            class="app-btn app-btn-sm" :class="navMode === 'rail' ? 'app-btn-primary' : 'app-btn-secondary'">Left rail</button>
                </div>
                <p class="mt-1.5 text-[11px] text-neutral-500">The left rail can be collapsed from the menu header.</p>
            </div>
        </div>
    </div>

    {{-- Unified confirm dialog --}}
    <div x-data
         x-show="$store.dialog.open"
         x-cloak
         x-effect="$store.dialog.open ? window.appScrollLock?.lock() : window.appScrollLock?.unlock()"
         @keydown.escape.window="$store.dialog.cancel()"
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/30 dark:bg-black/50 backdrop-blur-[2px] app-modal-backdrop">
        <div class="app-modal-panel w-full max-w-sm bg-white dark:bg-[#1C1C1E] rounded-3xl border border-neutral-200 dark:border-neutral-800 shadow-2xl p-6"
             @click.outside="$store.dialog.cancel()">
            <div class="flex items-start gap-3.5">
                <span class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0"
                      :class="{
                          'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400': $store.dialog.variant === 'danger',
                          'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400': $store.dialog.variant === 'warning',
                          'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400': $store.dialog.variant === 'success',
                          'bg-blue-50 text-[#0071E3] dark:bg-blue-950/40 dark:text-[#0A84FF]': $store.dialog.variant === 'info',
                          'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300': $store.dialog.variant === 'neutral'
                      }">
                    <svg x-show="$store.dialog.icon === 'trash'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <svg x-show="$store.dialog.icon === 'logout'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <svg x-show="$store.dialog.icon === 'user-x'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm9 1l4 4m0-4l-4 4"/></svg>
                    <svg x-show="$store.dialog.icon === 'user-check'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm8.5 0l2 2 4-4"/></svg>
                    <svg x-show="$store.dialog.icon === 'unlock'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 017.9-.9M6 11h12a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2z"/></svg>
                    <svg x-show="$store.dialog.icon === 'truck'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    <svg x-show="$store.dialog.icon === 'ban'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    <svg x-show="$store.dialog.icon === 'warning'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    <svg x-show="$store.dialog.icon === 'check'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <svg x-show="$store.dialog.icon === 'info'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <svg x-show="$store.dialog.icon === 'question'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white" x-text="$store.dialog.title"></h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400" x-show="$store.dialog.message" x-text="$store.dialog.message"></p>
                </div>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button"
                        @click="$store.dialog.cancel()"
                        :disabled="$store.dialog.loading"
                        class="app-btn app-btn-secondary"
                        x-text="$store.dialog.cancelLabel"></button>
                <button type="button"
                        @click="$store.dialog.confirm()"
                        :disabled="$store.dialog.loading"
                        :class="{
                            'app-btn-danger': $store.dialog.variant === 'danger',
                            'app-btn-amber': $store.dialog.variant === 'warning',
                            'app-btn-emerald': $store.dialog.variant === 'success',
                            'app-btn-blue': $store.dialog.variant === 'info',
                            'app-btn-primary': $store.dialog.variant === 'neutral',
                            'is-loading': $store.dialog.loading,
                            'is-compact': $store.dialog.loading
                        }"
                        class="app-btn"
                        x-text="$store.dialog.confirmLabel"></button>
            </div>
        </div>
    </div>

    {{-- Shared lazy detail panel (order / receipt / audit) --}}
    <x-lazy-modal />

    {{-- Restore scroll position before first paint. Runs during parse, after the
         render-blocking stylesheet, so the page never visibly jumps from the top. --}}
    <script>
        (function () {
            // Pin the sticky table-header offset before first paint so it never
            // renders at the fallback height and then snaps into place.
            var header = document.querySelector('header');
            if (header) document.documentElement.style.setProperty('--app-header-h', header.offsetHeight + 'px');

            if (document.querySelector('[data-scroll-to]') || document.querySelector('[data-autofocus]')) return;

            var saved = null;
            try {
                var params = new URLSearchParams(location.search || '');
                var sorted = Array.from(params.entries()).sort(function (a, b) { return a[0] < b[0] ? -1 : a[0] > b[0] ? 1 : 0; });
                var qs = new URLSearchParams(sorted).toString();
                var key = 'shoeboy:scroll:' + location.pathname + (qs ? '?' + qs : '');
                saved = sessionStorage.getItem(key);
                if (saved !== null) sessionStorage.removeItem(key);
            } catch (e) { return; }

            if (saved === null) return;
            var y = parseInt(saved, 10);
            if (!isFinite(y) || y <= 0) return;

            if (window.history && 'scrollRestoration' in window.history) {
                window.history.scrollRestoration = 'manual';
            }
            window.scrollTo(0, y);
        })();
    </script>

</body>
</html>

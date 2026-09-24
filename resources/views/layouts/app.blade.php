<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      :class="{ 'dark': darkMode }"
      x-data="{
          darkMode: localStorage.getItem('shoeboy_theme') === 'dark' || (!('shoeboy_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
          sidebarOpen: localStorage.getItem('shoeboy_sidebar') !== 'closed',
          sidebarHovering: false,
          sidebarHoverCapable: window.matchMedia('(hover: hover) and (pointer: fine)').matches,
          toggleTheme() {
              this.darkMode = !this.darkMode;
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
                  localStorage.setItem('shoeboy_theme', 'dark');
              } else {
                  document.documentElement.classList.remove('dark');
                  localStorage.setItem('shoeboy_theme', 'light');
              }
          },
          toggleSidebar() {
              this.sidebarOpen = !this.sidebarOpen;
              localStorage.setItem('shoeboy_sidebar', this.sidebarOpen ? 'open' : 'closed');
          }
      }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'The Shoe Boy') }} - @yield('title', 'Order & Inventory System')</title>

    <script>
        if (localStorage.getItem('shoeboy_theme') === 'dark' || (!('shoeboy_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-[#121214] text-[#1D1D1F] dark:text-[#F5F5F7] font-sans antialiased min-h-screen transition-colors duration-200 selection:bg-[#0071E3] selection:text-white">

    <div class="flex min-h-screen">

        @include('partials.sidebar')

        {{-- Main column: header + page content --}}
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-200"
             @auth
             :class="(sidebarOpen || sidebarHovering) ? 'md:ml-64' : 'md:ml-[72px]'"
             @endauth>

            @include('partials.navbar')

            <main class="flex-1 w-full p-4 lg:p-6 transition-all duration-200">
                @if(session('success'))
                    <div class="mb-4 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-xs font-medium flex items-center gap-2.5 shadow-sm">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-4 p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 text-blue-800 dark:text-blue-300 text-xs font-medium flex items-center gap-2.5 shadow-sm">
                        <svg class="w-4 h-4 text-[#0071E3] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-300 text-xs font-medium flex items-center gap-2.5 shadow-sm">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-300 text-xs space-y-1 shadow-sm">
                        <div class="font-bold flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Please correct the following errors:</span>
                        </div>
                        <ul class="list-disc pl-6 space-y-0.5">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="mt-auto py-6 border-t border-neutral-200/80 dark:border-neutral-800/80 text-center text-xs text-neutral-400">
                <div class="px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
                    <div>
                        <span class="font-semibold text-neutral-600 dark:text-neutral-400">The Shoe Boy</span> &copy; {{ date('Y') }}
                    </div>
                    <div class="text-[11px] text-neutral-400">
                        Order &amp; Inventory Management Console
                    </div>
                </div>
            </footer>

        </div>
    </div>

</body>
</html>
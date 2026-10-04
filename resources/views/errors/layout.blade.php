<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Error') &middot; {{ config('app.name', 'The Shoe Boy') }}</title>

    <script>
        if (localStorage.getItem('shoeboy_theme') === 'dark' || (!('shoeboy_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

    @vite('resources/css/app.css')
</head>
<body class="min-h-screen flex flex-col items-center justify-center bg-[#F5F5F7] dark:bg-[#121214] text-[#1D1D1F] dark:text-[#F5F5F7] font-sans antialiased p-6 transition-colors duration-200 selection:bg-[#0071E3] selection:text-white">

    <main class="w-full max-w-md">

        <div class="flex items-center justify-center mb-5">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 group">
                <img src="{{ asset('logo.png') }}" alt="The Shoe Boy" class="w-10 h-10 rounded-xl object-cover shadow-sm border border-neutral-200 dark:border-neutral-700 group-hover:scale-105 transition-transform">
                <span class="font-bold text-sm tracking-tight text-[#1D1D1F] dark:text-white">THE SHOE BOY</span>
            </a>
        </div>

        <div class="app-card p-8 text-center shadow-sm">

            <div class="font-mono text-5xl font-bold tracking-tight text-[#0071E3] dark:text-[#0A84FF]">
                @yield('code', 'Error')
            </div>

            <h1 class="mt-3 text-lg font-semibold text-[#1D1D1F] dark:text-white">
                @yield('title')
            </h1>

            <p class="mt-2 text-sm leading-6 text-neutral-500 dark:text-neutral-400">
                @yield('message')
            </p>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                @yield('actions')
            </div>

        </div>

        <p class="mt-5 text-center text-[11px] text-neutral-500 dark:text-neutral-500">
            The Shoe Boy &middot; Order &amp; Inventory Management Console
        </p>

    </main>

</body>
</html>

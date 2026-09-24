@extends('layouts.app')

@section('title', 'Sign In')

@section('content')
<div class="min-h-[78vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md" x-data="{ showPassword: false }">

        <div class="text-center mb-6">
            <img src="{{ asset('logo.png') }}" alt="The Shoe Boy" class="w-16 h-16 rounded-2xl object-cover shadow-md mx-auto border border-neutral-200 dark:border-neutral-700">
            <h2 class="text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-4">Welcome back</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Sign in to the order &amp; inventory console</p>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] p-6 sm:p-8 rounded-3xl border border-neutral-200/80 dark:border-neutral-800 shadow-xl transition-all">

            <form class="space-y-4" action="{{ route('login') }}" method="POST">
                @csrf

                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300">Email Address</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-neutral-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                        <input id="email"
                               name="email"
                               type="email"
                               required
                               autofocus
                               autocomplete="email"
                               placeholder="you@theshoeboy.com"
                               value="{{ old('email') }}"
                               class="w-full pl-10 pr-4 py-3 bg-neutral-100 dark:bg-neutral-800/80 border @error('email') border-rose-500 @else border-neutral-200 dark:border-neutral-700 @enderror rounded-xl font-medium text-sm text-[#1D1D1F] dark:text-white placeholder-neutral-400 apple-focus-ring">
                    </div>
                    @error('email')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300">Password</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-neutral-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input id="password"
                               name="password"
                               :type="showPassword ? 'text' : 'password'"
                               required
                               autocomplete="current-password"
                               placeholder="Enter your password"
                               class="w-full pl-10 pr-11 py-3 bg-neutral-100 dark:bg-neutral-800/80 border @error('password') border-rose-500 @else border-neutral-200 dark:border-neutral-700 @enderror rounded-xl font-medium text-sm text-[#1D1D1F] dark:text-white placeholder-neutral-400 apple-focus-ring">
                        <button type="button"
                                @click="showPassword = !showPassword"
                                title="Show / hide password"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-neutral-500 hover:text-neutral-600 dark:hover:text-neutral-200">
                            <template x-if="!showPassword">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </template>
                            <template x-if="showPassword">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 012.223-3.362m3.357-2.317A9.965 9.965 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411M3 3l18 18"/></svg>
                            </template>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#0071E3] border-neutral-300 dark:border-neutral-700 focus:ring-[#0071E3]">
                        <span class="text-neutral-600 dark:text-neutral-400 text-xs">Remember session</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-3.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 active:scale-[0.99] text-neutral-700 dark:text-neutral-200 font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Sign In to Console</span>
                </button>
            </form>

        </div>

        <p class="text-center text-[11px] text-neutral-500 mt-5">
            The Shoe Boy &middot; Order &amp; Inventory Management Console
        </p>
    </div>
</div>
@endsection
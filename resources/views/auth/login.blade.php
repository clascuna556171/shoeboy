@extends('layouts.app')

@section('title', 'Sign In')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6 bg-white dark:bg-[#1C1C1E] p-8 rounded-3xl border border-neutral-200/80 dark:border-neutral-800 shadow-xl transition-all">
        
        <div class="text-center space-y-3">
            <img src="{{ asset('logo.png') }}" alt="The Shoe Boy" class="w-16 h-16 rounded-2xl object-cover shadow-md mx-auto border border-neutral-200 dark:border-neutral-700">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-[#1D1D1F] dark:text-white">Sign in to The Shoe Boy</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Order & Inventory Management Console</p>
            </div>
        </div>

        <form class="space-y-4 text-xs" action="{{ route('login') }}" method="POST">
            @csrf

            <div class="space-y-1.5">
                <label for="email" class="block font-semibold text-neutral-700 dark:text-neutral-300">Email Address</label>
                <input id="email"
                       name="email"
                       type="email"
                       required
                       autofocus
                       autocomplete="email"
                       placeholder="Enter your email address"
                       value="{{ old('email') }}"
                       class="w-full px-4 py-3 bg-neutral-100 dark:bg-neutral-800/80 border @error('email') border-rose-500 @else border-neutral-200 dark:border-neutral-700 @enderror rounded-xl font-medium text-sm text-[#1D1D1F] dark:text-white placeholder-neutral-400 apple-focus-ring">
                @error('email')
                    <p class="text-rose-600 dark:text-rose-400 text-[11px] font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label for="password" class="block font-semibold text-neutral-700 dark:text-neutral-300">Password</label>
                <input id="password"
                       name="password"
                       type="password"
                       required
                       autocomplete="current-password"
                       placeholder="Enter your password"
                       class="w-full px-4 py-3 bg-neutral-100 dark:bg-neutral-800/80 border @error('password') border-rose-500 @else border-neutral-200 dark:border-neutral-700 @enderror rounded-xl font-medium text-sm text-[#1D1D1F] dark:text-white placeholder-neutral-400 apple-focus-ring">
                @error('password')
                    <p class="text-rose-600 dark:text-rose-400 text-[11px] font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#0071E3] border-neutral-300 dark:border-neutral-700 focus:ring-[#0071E3]">
                    <span class="text-neutral-600 dark:text-neutral-400 text-xs">Remember session</span>
                </label>
            </div>

            <button type="submit"
                    class="w-full py-3.5 rounded-2xl bg-[#0071E3] hover:bg-[#0077ED] text-white font-bold text-sm shadow-md active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                <span>Sign In to Console</span>
            </button>
        </form>

    </div>
</div>
@endsection

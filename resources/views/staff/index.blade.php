@extends('layouts.app')

@section('title', 'Staff Management')

@section('content')
<div class="space-y-6" x-data="{ showCreateModal: false }">

    {{-- Page header --}}
    <div class="app-card p-5 lg:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="min-w-0">
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">User Administration</div>
                <h1 class="text-xl md:text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-0.5">Staff Account Management</h1>
                </div>
        </div>

        <button type="button"
                @click="showCreateModal = true"
                class="px-5 py-2.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm shadow-sm flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add New Staff Account</span>
        </button>
    </div>

    {{-- Table --}}
    <div class="app-card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-neutral-200 dark:border-neutral-800">
            <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white">Active Team Members &amp; Permissions</h3>
            <span class="badge badge-neutral">{{ $users->count() }} accounts</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <th class="py-3 px-4 font-semibold">Name &amp; Contact</th>
                        <th class="py-3 px-4 font-semibold">Email Address</th>
                        <th class="py-3 px-4 font-semibold text-center">Role</th>
                        <th class="py-3 px-4 font-semibold text-center">Orders Awarded</th>
                        <th class="py-3 px-4 font-semibold text-center">Verified Payments</th>
                        <th class="py-3 px-4 font-semibold text-center">Status</th>
                        <th class="py-3 px-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    @foreach($users as $user)
                    <tr class="app-row hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 flex items-center justify-center shrink-0 text-[11px] font-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                                <div>
                                    <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $user->name }}</div>
                                    <span class="text-[10px] text-neutral-500 font-mono">{{ $user->contact_number ?? 'No contact number' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-neutral-700 dark:text-neutral-300">{{ $user->email }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="badge {{ $user->isOwner() ? 'badge-owner' : 'badge-staff' }}">{{ $user->role }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono font-semibold">{{ $user->orders_count }}</td>
                        <td class="py-3.5 px-4 text-center font-mono font-semibold">{{ $user->verified_payments_count }}</td>
                        <td class="py-3.5 px-4 text-center">
                            @if($user->is_active)
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-rose-600 dark:text-rose-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Deactivated
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($user->id !== auth()->id())
                            <form action="{{ route('staff.toggle', $user->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        onclick="return confirm('Toggle active status for {{ $user->name }}?')"
                                        class="inline-flex items-center justify-center min-h-8 px-3 py-1.5 rounded-lg border text-xs font-semibold transition-colors {{ $user->is_active ? 'border-rose-200 dark:border-rose-900/60 bg-white dark:bg-[#1C1C1E] text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40' : 'border-emerald-200 dark:border-emerald-900/60 bg-white dark:bg-[#1C1C1E] text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }}">
                                    {{ $user->is_active ? 'Deactivate' : 'Reactivate' }}
                                </button>
                            </form>
                            @else
                            <span class="text-[11px] text-neutral-500 italic">Current Session</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <div x-show="showCreateModal"
         x-cloak
         class="app-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="app-modal-panel bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
                <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white">Create Staff Account</h3>
                <button @click="showCreateModal = false" class="text-neutral-500 hover:text-neutral-600 text-sm">✕</button>
            </div>

            <form action="{{ route('staff.store') }}" method="POST" class="space-y-3.5 text-sm">
                @csrf

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Staff Full Name:</label>
                    <input type="text" name="name" required placeholder="e.g. Maria Helper" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                </div>

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Email Address (Login):</label>
                    <input type="email" name="email" required placeholder="staff@theshoeboy.com" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Role:</label>
                        <select name="role" class="w-full px-3 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                            <option value="staff">Staff (Operations)</option>
                            <option value="owner">Owner (Administrator)</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Contact Number:</label>
                        <input type="text" name="contact_number" placeholder="09171234567" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Password (Min 8 characters):</label>
                    <input type="password" name="password" required class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                </div>

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Confirm Password:</label>
                    <input type="password" name="password_confirmation" required class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl apple-focus-ring">
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="showCreateModal = false" class="flex-1 py-2.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-semibold">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold">Create Account</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
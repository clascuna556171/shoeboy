@extends('layouts.app')

@section('title', 'Staff Management')

@section('content')
<div class="space-y-6" x-data="{ showCreateModal: false }">


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300">Owner Access Only</span>
                <span class="text-xs text-neutral-400">User Administration Module</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-[#1D1D1F] dark:text-white mt-1">Staff Account Management</h1>
            <p class="text-xs text-neutral-500 mt-0.5">Authorize shop workers, helpers, and repair personnel with role-based access control.</p>
        </div>

        <button type="button"
                @click="showCreateModal = true"
                class="px-5 py-2.5 rounded-2xl bg-[#0071E3] hover:bg-[#0077ED] text-white font-semibold text-xs shadow-sm flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add New Staff Account</span>
        </button>
    </div>


    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
            <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Active Team Members & Permissions</h3>
            <span class="text-xs font-mono text-neutral-400">{{ $users->count() }} accounts</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-[11px] text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800 pb-2">
                    <tr>
                        <th class="py-2.5 px-3">Name & Contact</th>
                        <th class="py-2.5 px-3">Email Address</th>
                        <th class="py-2.5 px-3 text-center">System Role</th>
                        <th class="py-2.5 px-3 text-center">Orders Awarded</th>
                        <th class="py-2.5 px-3 text-center">Verified Payments</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60 font-sans">
                    @foreach($users as $user)
                    <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">
                        <td class="py-3 px-3">
                            <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $user->name }}</div>
                            <span class="text-[10px] text-neutral-400 font-mono">{{ $user->contact_number ?? 'No contact number' }}</span>
                        </td>
                        <td class="py-3 px-3 font-mono text-neutral-700 dark:text-neutral-300">{{ $user->email }}</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $user->isOwner() ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-blue-50 text-[#0071E3] dark:bg-blue-950/60 dark:text-[#0A84FF]' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center font-mono font-semibold">{{ $user->orders_count }}</td>
                        <td class="py-3 px-3 text-center font-mono font-semibold">{{ $user->verified_payments_count }}</td>
                        <td class="py-3 px-3 text-center">
                            @if($user->is_active)
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-600 dark:text-rose-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Deactivated
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-right">
                            @if($user->id !== auth()->id())
                            <form action="{{ route('staff.toggle', $user->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        onclick="return confirm('Toggle active status for {{ $user->name }}?')"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $user->is_active ? 'bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-950/50 dark:hover:bg-rose-900' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:hover:bg-emerald-900' }}">
                                    {{ $user->is_active ? 'Deactivate' : 'Reactivate' }}
                                </button>
                            </form>
                            @else
                            <span class="text-[11px] text-neutral-400 italic">Current Session</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


    <div x-show="showCreateModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
                <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-white">Create Staff Account</h3>
                <button @click="showCreateModal = false" class="text-neutral-400 hover:text-neutral-600 text-sm">✕</button>
            </div>

            <form action="{{ route('staff.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                
                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Staff Full Name:</label>
                    <input type="text" name="name" required placeholder="e.g. Maria Helper" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                </div>

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Email Address (Login):</label>
                    <input type="email" name="email" required placeholder="staff@theshoeboy.com" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Role:</label>
                        <select name="role" class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                            <option value="staff">Staff (Operations)</option>
                            <option value="owner">Owner (Administrator)</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Contact Number:</label>
                        <input type="text" name="contact_number" placeholder="09171234567" class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Password (Min 8 characters):</label>
                    <input type="password" name="password" required class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                </div>

                <div class="space-y-1">
                    <label class="block font-semibold text-neutral-700 dark:text-neutral-300">Confirm Password:</label>
                    <input type="password" name="password_confirmation" required class="w-full px-3.5 py-2.5 bg-neutral-100 dark:bg-neutral-800 border rounded-xl apple-focus-ring">
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="showCreateModal = false" class="flex-1 py-2.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-semibold">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-[#0071E3] hover:bg-[#0077ED] text-white font-semibold">Create Account</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

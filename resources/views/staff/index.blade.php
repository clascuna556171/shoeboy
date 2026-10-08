@extends('layouts.app')

@section('title', 'Staff Management')

@section('content')
<div class="space-y-6" x-data="{ showCreateModal: false, editingUser: null }">

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
                data-tour="primary"
                class="px-5 py-2.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-sm shadow-sm flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add New Staff Account</span>
        </button>
    </div>

    <x-table-toolbar :action="route('staff.index')" :search="$search"
                     search-placeholder="Search name or email..."
                     :reset-url="route('staff.index')"
                     :filters="[
                         ['name' => 'role', 'selected' => $role, 'options' => ['' => 'All Roles', 'owner' => 'Owner', 'staff' => 'Staff']],
                     ]" />

    {{-- Table --}}
    <div class="app-card" data-tour="list">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-neutral-200 dark:border-neutral-800">
            <h3 class="font-bold text-base text-[#1D1D1F] dark:text-white">Active Team Members &amp; Permissions</h3>
            <span class="badge badge-neutral">{{ $users->count() }} accounts</span>
        </div>

        <div class="overflow-x-auto lg:overflow-visible">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                    <tr class="bg-neutral-50/60 dark:bg-neutral-800/30">
                        <x-sort-th-server column="name" label="Name & Contact" />
                        <x-sort-th-server column="email" label="Email Address" />
                        <x-sort-th-server column="role" label="Role" align="center" />
                        <x-sort-th-server column="orders_count" label="Orders Awarded" align="center" />
                        <x-sort-th-server column="verified_payments_count" label="Verified Payments" align="center" />
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
                            <x-status-badge kind="role" :value="$user->role" />
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
                            <div class="inline-flex items-center gap-1.5">
                                <x-action-btn icon="edit" tone="secondary"
                                              @click="editingUser = {{ Js::from(['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role, 'contact_number' => $user->contact_number, 'is_active' => (bool) $user->is_active]) }}">Edit</x-action-btn>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('staff.toggle', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <x-action-btn icon="power" tone="secondary" type="submit"
                                                  class="{{ $user->is_active ? 'text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-900/60' : 'text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/60' }}"
                                                  data-confirm="{{ $user->is_active ? 'Deactivate' : 'Reactivate' }} {{ $user->name }}?"
                                                  data-confirm-variant="{{ $user->is_active ? 'warning' : 'success' }}"
                                                  data-confirm-icon="{{ $user->is_active ? 'user-x' : 'user-check' }}"
                                                  data-confirm-message="{{ $user->is_active ? 'They will no longer be able to sign in.' : 'They will regain access to the console.' }}"
                                                  data-confirm-label="{{ $user->is_active ? 'Deactivate' : 'Reactivate' }}">{{ $user->is_active ? 'Deactivate' : 'Reactivate' }}</x-action-btn>
                                </form>
                                @else
                                <span class="text-[11px] text-neutral-500 italic">Current Session</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <x-modal title="Create Staff Account" accent="violet" close="showCreateModal = false"
             x-show="showCreateModal" x-cloak @keydown.escape.window="showCreateModal = false">
            <form action="{{ route('staff.store') }}" method="POST" class="space-y-3.5 text-sm">
                @csrf

                <div>
                    <label class="app-label">Staff Full Name <span class="app-req">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Maria Helper" class="app-input">
                </div>

                <div>
                    <label class="app-label">Email Address (Login) <span class="app-req">*</span></label>
                    <input type="email" name="email" required placeholder="staff@theshoeboy.com" class="app-input">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Role <span class="app-req">*</span></label>
                        <select name="role" required class="app-select">
                            <option value="staff">Staff (Operations)</option>
                            <option value="owner">Owner (Administrator)</option>
                        </select>
                    </div>
                    <div>
                        <label class="app-label">Contact Number <span class="app-optional">(optional)</span></label>
                        <input type="text" name="contact_number" placeholder="09171234567" class="app-input">
                    </div>
                </div>

                <div>
                    <label class="app-label">Password (min 8 characters) <span class="app-req">*</span></label>
                    <input type="password" name="password" required minlength="8" class="app-input">
                </div>

                <div>
                    <label class="app-label">Confirm Password <span class="app-req">*</span></label>
                    <input type="password" name="password_confirmation" required minlength="8" class="app-input">
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="button" @click="showCreateModal = false" class="app-btn app-btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="app-btn app-btn-violet flex-1">Create Account</button>
                </div>
            </form>
    </x-modal>

    {{-- Edit staff modal --}}
    <x-modal title="Edit Staff Account" accent="violet" close="editingUser = null"
             x-show="editingUser" x-cloak @keydown.escape.window="editingUser = null">
            <form :action="editingUser ? '{{ route('staff.update', ['user' => '__ID__']) }}'.replace('__ID__', editingUser.id) : '#'" method="POST" class="space-y-3.5 text-sm">
                @csrf
                @method('PUT')

                <div>
                    <label class="app-label">Full Name <span class="app-req">*</span></label>
                    <input type="text" name="name" :value="editingUser?.name" required class="app-input">
                </div>

                <div>
                    <label class="app-label">Email Address (Login) <span class="app-req">*</span></label>
                    <input type="email" name="email" :value="editingUser?.email" required class="app-input">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">Role <span class="app-req">*</span></label>
                        <select name="role" :value="editingUser?.role" required class="app-select">
                            <option value="staff">Staff (Operations)</option>
                            <option value="owner">Owner (Administrator)</option>
                        </select>
                    </div>
                    <div>
                        <label class="app-label">Contact Number <span class="app-optional">(optional)</span></label>
                        <input type="text" name="contact_number" :value="editingUser?.contact_number" class="app-input">
                    </div>
                </div>

                <div>
                    <label class="app-label">Account Status</label>
                    <select name="is_active" :value="editingUser?.is_active ? '1' : '0'" class="app-select">
                        <option value="1">Active</option>
                        <option value="0">Deactivated</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="app-label">New Password <span class="app-optional">(optional)</span></label>
                        <input type="password" name="password" minlength="8" class="app-input">
                    </div>
                    <div>
                        <label class="app-label">Confirm Password <span class="app-optional">(optional)</span></label>
                        <input type="password" name="password_confirmation" minlength="8" class="app-input">
                    </div>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="button" @click="editingUser = null" class="app-btn app-btn-secondary flex-1">Cancel</button>
                    <button type="submit" class="app-btn app-btn-violet flex-1">Save changes</button>
                </div>
            </form>
    </x-modal>

</div>
@endsection
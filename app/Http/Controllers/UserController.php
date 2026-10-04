<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SortsQueries;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    use SortsQueries;

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $role = $request->query('role');

        $query = User::withCount(['orders', 'verifiedPayments']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        $this->applySort($query, ['name', 'email', 'role', 'orders_count', 'verified_payments_count', 'created_at'], 'created_at', 'desc');
        $users = $query->get();

        return view('staff.index', compact('users', 'search', 'role'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:owner,staff'],
            'contact_number' => ['nullable', 'numeric'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'contact_number' => $validated['contact_number'] ?? null,
            'is_active' => true,
        ]);

        AuditService::log('staff_account_created', $user, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return back()->with('success', "Staff account for '{$user->name}' created successfully.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:owner,staff'],
            'contact_number' => ['nullable', 'numeric'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'contact_number' => $validated['contact_number'] ?? null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $user->is_active,
        ];

        // Owners cannot lock themselves out of their own account.
        if ($user->id === auth()->id()) {
            $updateData['is_active'] = true;
            $updateData['role'] = 'owner';
        }

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        AuditService::log('staff_account_updated', $user, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
        ]);

        return back()->with('success', "Account for '{$user->name}' updated.");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own owner account.');
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $status = $user->is_active ? 'reactivated' : 'deactivated';

        AuditService::log('staff_status_toggled', $user, [
            'name' => $user->name,
            'is_active' => $user->is_active,
        ]);

        return back()->with('info', "Account for '{$user->name}' has been {$status}.");
    }
}

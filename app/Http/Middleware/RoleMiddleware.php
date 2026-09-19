<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact the owner.');
        }

        if ($role === 'owner' && ! $user->isOwner()) {
            abort(403, 'Unauthorized. Owner access required for this action.');
        }

        if ($role === 'staff' && ! $user->isStaff()) {
            abort(403, 'Unauthorized. Staff access required.');
        }

        return $next($request);
    }
}

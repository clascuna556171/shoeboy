<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // The 'auth' and 'active' middleware already guarantee an active user;
        // this only enforces the role gate.
        $user = $request->user();

        if ($role === 'owner' && ! $user?->isOwner()) {
            abort(403, 'Unauthorized. Owner access required for this action.');
        }

        if ($role === 'staff' && ! $user?->isStaff()) {
            abort(403, 'Unauthorized. Staff access required.');
        }

        return $next($request);
    }
}

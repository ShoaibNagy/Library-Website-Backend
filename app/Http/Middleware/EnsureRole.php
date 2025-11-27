<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    /**
     * Handle an incoming request.
     * Expected usage: ->middleware('role:staff') or ->middleware('role:patron')
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Allow admin to pass all checks
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        if (! method_exists($user, 'hasRole')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! $user->hasRole($role) && ! ($role === 'patron' && $user->isStaff())) {
            return response()->json(['message' => 'Forbidden: insufficient role'], 403);
        }

        return $next($request);
    }
}

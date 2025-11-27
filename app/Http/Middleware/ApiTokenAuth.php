<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-API-TOKEN') ?? $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = User::where('api_token', $token)->first();
        if (! $user) {
            return response()->json(['message' => 'Invalid API token'], 401);
        }

        // Set the current user for the request
        Auth::login($user);

        return $next($request);
    }
}

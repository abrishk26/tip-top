<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // When using Sanctum tokens, ensure the token belongs to an Admin model.
        $user = $request->user();

        if (!$user || !($user instanceof \App\Models\Admin)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        if (method_exists($user, 'isActive') && !$user->isActive()) {
            return response()->json(['error' => 'Admin account inactive'], 403);
        }

        return $next($request);
    }
}

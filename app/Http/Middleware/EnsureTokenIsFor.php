<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class EnsureTokenIsFor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $modelClass): Response
    {
        $user = $request->user();

        if (!$user instanceof $modelClass) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}

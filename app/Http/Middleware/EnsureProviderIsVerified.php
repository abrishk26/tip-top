<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderIsVerified
{
    /*
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $provider = $request->user();

        if (!$provider->is_verified) {
            return response()->json([
                'status' => 'email_unverified',
                'message' => 'Please verify your email to continue.',
            ], 403);
        }

        if ($provider->registration_status != "accepted") {
            return response()->json([
                'status' => 'license_'.$provider->registration_status,
                'message' => match ($provider->registration_status) {
                   'pending' => 'Your license is under review.',
                   'rejected' => 'Your license was rejected. Please resubmit.',
                   default => 'License verification required.',
                }
            ], 403);
        }

        return $next($request);
    }
}

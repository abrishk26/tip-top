<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Admin;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:100',
        ]);

        $admin = Admin::where('email', $validated['email'])->first();
        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }
        if (!$admin->is_active) {
            return response()->json(['error' => 'Admin account inactive'], 403);
        }
        if (!$admin->password_hash || !Hash::check($validated['password'], $admin->password_hash)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $admin->forceFill(['last_login_at' => now()])->save();

        $token = $admin->createToken('admin-auth')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'admin' => $admin->only(['id', 'name', 'email']),
            'token' => $token,
        ]);
    }

    public function profile(Request $request)
    {
        /** @var Admin $admin */
        $admin = $request->user();
        return response()->json([
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'email_verified' => (bool)$admin->email_verified,
            'is_active' => (bool)$admin->is_active,
            'last_login_at' => $admin->last_login_at,
            'created_at' => $admin->created_at,
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Throwable $e) {
            Log::error('Admin logout error: '.$e->getMessage());
            return response()->json(['error' => 'Failed to logout'], 500);
        }
    }
}

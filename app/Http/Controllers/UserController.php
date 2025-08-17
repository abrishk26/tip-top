<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use App\Models\User;
use App\Exceptions\DuplicateEmailException;
use App\Exceptions\InvalidVerificationTokenException;
use App\Exceptions\EmailNotVerifiedException;
use App\Exceptions\UserInactiveException;
use App\Exceptions\InvalidCredentialsException;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:200',
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:30',
            'phone' => 'nullable|string|max:20',
        ]);

        try {
            // Generate verification token here
            $verificationToken = Str::random(64);
            $expiresAt = Carbon::now()->addHours(24);
            
            $userData = array_merge($validated, [
                'password_hash' => Hash::make($validated['password']),
                'verification_token' => $verificationToken,
                'verification_token_expires_at' => $expiresAt,
            ]);

            $user = User::create($userData);
            
            // Send verification email with magic link
            $this->sendVerificationEmail($user);
            
            return response()->json([
                "message" => "Registration successful. Please check your email for verification link.",
                "user_id" => $user->id
            ], 201);
            
        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? null) === 1062) {
                return response()->json(['error' => 'Email already exists'], 409);
            }
            Log::error('User registration error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        } catch (Exception $e) {
            Log::error('User registration error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function verifyEmail(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|size:64'
        ]);

        try {
            // Find user with valid token
            $user = User::where('verification_token', $validated['token'])
                        ->where('verification_token_expires_at', '>', Carbon::now())
                        ->where('email_verified', false)
                        ->first();

            if (!$user) {
                throw new InvalidVerificationTokenException("Invalid or expired verification token");
            }
            
            // Activate user immediately (password already set during registration)
            $user->update([
                'email_verified' => true,
                'email_verified_at' => Carbon::now(),
                'is_active' => true,
                'verification_token' => null,
                'verification_token_expires_at' => null,
            ]);

            // Create authentication token
            $token = $user->createToken('auth-token')->plainTextToken;
            
            // Return JSON response for API calls
            return response()->json([
                'message' => 'Email verified successfully! You are now logged in.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone
                ],
                'token' => $token,
                'verified' => true
            ], 200);
            
        } catch (InvalidVerificationTokenException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (Exception $e) {
            Log::error('Email verification error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:30'
        ]);

        try {
            $user = User::where('email', $validated['email'])->first();
            
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            if (!$user->isVerified()) {
                throw new EmailNotVerifiedException("Please verify your email first");
            }

            if (!$user->isActive()) {
                throw new UserInactiveException("Account is inactive");
            }

            if (!$user->password_hash || !Hash::check($validated['password'], $user->password_hash)) {
                throw new InvalidCredentialsException("Invalid password");
            }
            // Update last login
            $user->update(['last_login_at' => Carbon::now()]);

            // Create token
            $token = $user->createToken('auth-token')->plainTextToken;

            $result = [
                'user' => $user->only(['id', 'name', 'email', 'role', 'phone']),
                'token' => $token
            ];
            
            return response()->json([
                'message' => 'Login successful',
                'user' => $result['user'],
                'token' => $result['token']
            ], 200);
            
        } catch (EmailNotVerifiedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (UserInactiveException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (InvalidCredentialsException $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'email_verified' => $user->email_verified,
                'created_at' => $user->created_at,
                'last_login_at' => $user->last_login_at
            ]);
        } catch (Exception $e) {
            Log::error('Profile retrieval error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve profile'], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|min:2|max:200',
            'phone' => 'sometimes|string|max:20',
        ]);

        try {
            $user = $request->user();
            
           
            $allowedFields = ['name', 'phone'];
            $updateData = array_intersect_key($validated, array_flip($allowedFields));
            
            $user->update($updateData);
            
            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role
                ]
            ], 200);
            
        } catch (Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update profile'], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|max:30'
        ]);

        try {
            $user = $request->user();
            
            if (!Hash::check($validated['current_password'], $user->password_hash)) {
                return response()->json(['error' => 'Current password is incorrect'], 401);
            }
            
          
            $user->update(['password_hash' => Hash::make($validated['new_password'])]);
            
            // Revoke all tokens to force re-login
            $user->tokens()->delete();
            
            return response()->json(['message' => 'Password changed successfully. Please login again.'], 200);
            
        } catch (Exception $e) {
            Log::error('Password change error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to change password'], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out successfully'], 200);
        } catch (Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to logout'], 500);
        }
    }

    public function resendVerification(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email'
        ]);

        try {
            $user = User::where('email', $validated['email'])
                        ->where('email_verified', false)
                        ->first();
            
            if (!$user) {
                return response()->json(['error' => 'User not found or already verified'], 404);
            }

            // Generate new verification token
            $verificationToken = Str::random(64);
            $expiresAt = Carbon::now()->addHours(24);
            
            $user->update([
                'verification_token' => $verificationToken,
                'verification_token_expires_at' => $expiresAt
            ]);

            // Send new verification email
            $this->sendVerificationEmail($user);
            
            return response()->json(['message' => 'Verification email sent successfully'], 200);
            
        } catch (Exception $e) {
            Log::error('Resend verification error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
  // get verification token for email verification test
    public function getVerificationToken(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email'
        ]);

        try {
            $user = User::where('email', $validated['email'])->first();
            
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            if ($user->email_verified) {
                return response()->json(['error' => 'User already verified'], 400);
            }

            return response()->json([
                'token' => $user->verification_token,
                'expires_at' => $user->verification_token_expires_at
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    private function sendVerificationEmail($user)
    {
        try {
            // Generate direct API verification URL
            $verificationUrl = config('app.frontend_url', 'http://localhost:8000') . '/api/users/verify-email?token=' . $user->verification_token;
            
            // Send verification email
            Mail::to($user->email)->send(new \App\Mail\VerificationEmail($user, $verificationUrl));
            
            Log::info("Verification email sent successfully to {$user->email}");
        } catch (Exception $e) {
            Log::error("Failed to send verification email to {$user->email}: " . $e->getMessage());
            // Don't throw exception here to avoid breaking the registration flow
        }
    }
}

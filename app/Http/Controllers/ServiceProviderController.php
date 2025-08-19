<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

use App\Mail\VerificationEmail;
use App\Exceptions\DuplicateEmployeeException;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\InvalidCredentialsException;
use App\Models\ServiceProvider;
use App\Models\VerificationToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class ServiceProviderController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|min:2|max:200',
            'category_id'   => 'required|ulid',
            'email'         => 'required|email',
            'password'      => ['required', 'string', 'min:6', 'max:30'],
            'contact_phone' => [
                'required',
                'string',
                'regex:/^\+251(9|7)[0-9]{8}$/'
            ],
            'tax_id' => 'sometimes|string|max:100',
            'description' => 'sometimes|string',
            'address.street_address' => 'required|string|max:150',
            'address.city' => 'required|string|max:150',
            'address.region' => 'required|string|max:150',
            'image_url'     => 'sometimes|url'
        ]);

        $check = ServiceProvider::where('email', $validated['email'])->first();

        if ($check) {
            return response()->json(["error" => "the email has already been taken"], 409);
        }

        $validated['password_hash'] = bcrypt($validated['password']);
        unset($validated['password']);

        $street = $validated['address']['street_address'];
        $city = $validated['address']['city'];
        $region = $validated['address']['region'];

        unset($validated['address']);

        $provider = ServiceProvider::register($validated);

        $provider->address()->create([
            'street_address' => $street,
            'city' => $city,
            'region' => $region
        ]);


        $token = Str::random(64);
        VerificationToken::create([
            'token' => $token,
            'tokenable_type' => 'provider',
            'tokenable_id' => $provider->id,
            'expires_at' => now()->addHours(24),
        ]);

        $verificationLink = config('app.frontend_url', 'http://localhost:8000') . 'api/service-provider/verify-token/?token=' . $token;

        Mail::to($validated['email'])->queue(new VerificationEmail($verificationLink));

        return response()->json(['message' => 'registration completed successfully'], 201);
    }

    public static function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:30',
        ]);

        try {
            $result = ServiceProvider::login($validated);
            return response()->json(['message' => 'login successful', 'token' => $result]);
        } catch (InvalidCredentialsException $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function verifyEmail(Request $request) {
        $token = $request->query('token');

        $record = VerificationToken::where('token', $token)->first();

        if (!$record) {
            return response()->json(['error' => 'invalid token'], 400);
        }

        if ($record->expires_at->isPast()) {
            $record->delete();
            return response()->json(['error' => 'token expired'], 400);
        }

        $provider = ServiceProvider::where('id', $record->tokenable_id)->first();
        $provider->is_verified = true;
        $provider->save();

        $record->delete();

        return response()->json(['message' => 'email verified successfully.']);
    }


    public function profile(Request $request)
    {
        try {
            $provider = $request->user();
            return response()->json([
                'id' => $provider->id,
                'name' => $provider->name,
                'email' => $provider->email,
                'category_id' => $provider->category_id,
                'description' => $provider->description,
                'tax_id' => $provider->tax_id,
                'address' => $provider->address,
                'contact_phone' => $provider->contact_phone,
                'image_url' => $provider->image_url,
                'created_at' => $provider->created_at,
                'updated_at' => $provider->updated_at
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Failed to retrieve profile'], 500);
        }
    }

    public function getEmployees(ServiceProvider $serviceProvider)
    {
        return response()->json($serviceProvider->getEmployees(), 200);
    }

    // public function logout(Request $request)
    // {
    //     try {
    //         // Revoke the current user's token
    //         $request->user()->currentAccessToken()->delete();

    //         return response()->json(['message' => 'Logged out successfully']);
    //     } catch (\Exception $e) {
    //         Log::error($e);
    //         return response()->json(['error' => 'Failed to logout'], 500);
    //     }
    // }

    // Get employee summary
    public function employeeSummary(ServiceProvider $serviceProvider)
    {
        return response()->json($serviceProvider->employeeSummary(), 200);
    }

    // Activate an employee
    public function activateEmployee(ServiceProvider $serviceProvider, $employeeId)
    {
        $employee = $serviceProvider->employees()->findOrFail($employeeId);
        $serviceProvider->activateEmployee($employee);

        return response()->json(['message' => 'Employee activated'], 200);
    }

    // Deactivate an employee
    public function deactivateEmployee(ServiceProvider $serviceProvider, $employeeId)
    {
        $employee = $serviceProvider->employees()->findOrFail($employeeId);
        $serviceProvider->deactivateEmployee($employee);

        return response()->json(['message' => 'Employee deactivated'], 200);
    }

    // Bulk update employees' active status
    public function setEmployeesStatus(Request $request, ServiceProvider $serviceProvider)
    {
        $validated = $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'ulid',
            'active' => 'required|boolean'
        ]);

        $employees = $serviceProvider->employees()->whereIn('id', $validated['employee_ids'])->get();
        $serviceProvider->setEmployeesStatus($employees, $validated['active']);

        return response()->json(['message' => 'Employees status updated'], 200);
    }

    // Register multiple employees
    public function registerEmployees(Request $request)
    {
        $validated = $request->validate([
            'count' => 'required|integer|min:1|max:100'
        ]);

        $provider = $request->user();

        try {
            $employees = $provider->registerEmployees($validated['count']);
            return response()->json(['message' => 'Employees registered', 'employees' => $employees], 201);
        } catch (DuplicateEmployeeException $e) {
            return response()->json(['error' => 'Duplicate employee IDs detected'], 409);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}

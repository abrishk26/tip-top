<?php

namespace App\Http\Controllers;

use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Exceptions\DuplicateEmailException;
use App\Exceptions\DuplicateEmployeeException;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\EmployeeNotFoundException;

use App\Models\ServiceProvider;
use App\Models\VerificationToken;
use App\Http\Resources\ServiceProviderResource;

class ServiceProviderController extends Controller
{
    public function register(Request $request)
    {
        // extract the provider data from the form-data and validate
        $data = json_decode($request->input('provider_data'), true);
        $validated = validator($data, [
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
        ])->validate();

        // check if there exist license file
        if ($request->hasFile('license')) {
            $path = Storage::disk('cloudinary')->put('licenses', $request->file('license'), 'public');
            $url = Storage::disk('cloudinary')->url($path);

            //store the provider in the database
            $validated['license'] = $url;
            try {
                ServiceProvider::register($validated);
                return response()->json(['message' => 'registration completed successfully'], 201);
            } catch (DuplicateEmailException $e) {
                return response()->json(["error" => "the email have already been taken"], 409);
            } catch (\Exception $e) {
                Log::error($e);
                return response()->json(['error' => 'Internal server error'], 500);
            }
        } else {
            return response()->json(["error" => "license file missing"], 400);
        }
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

    public function logout(Request $request)
    {
        try {
            // Revoke the current user's token
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Failed to logout'], 500);
        }
    }

    public function verifyEmail(Request $request)
    {
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

    // Get service provider profile
    public function profile(Request $request)
    {
        $provider = $request->user();
        return new ServiceProviderResource($provider);
    }

    // Register multiple or one employee
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

    // Get employees data
    public function getEmployeesData(Request $request)
    {
        $provider = $request->user();
        return response()->json(['employees' => $provider->getEmployees()], 200);
    }

    // Get employee summary
    public function employeesSummary(Request $request)
    {
        $provider = $request->user();
        return response()->json($provider->employeeSummary(), 200);
    }

    // Activate an employee
    public function activateEmployee(Request $request, $employeeID)
    {
        $provider = $request->user();
        try {
            $provider->activateEmployee($employeeID);
            return response()->json(['message' => 'Employee activated'], 200);
        } catch (EmployeeNotFoundException $e) {
            return response()->json(['error' => 'employee not found'], 409);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    // Deactivate an employee
    public function deactivateEmployee(Request $request, $employeeID)
    {
        $provider = $request->user();
        try {
            $provider->deactivateEmployee($employeeID);
            return response()->json(['message' => 'Employee deactivated'], 200);
        } catch (EmployeeNotFoundException $e) {
            return response()->json(['error' => 'employee not found'], 404);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}

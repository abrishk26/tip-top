<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

use App\Exceptions\DuplicateEmailException;
use App\Exceptions\DuplicateEmployeeException;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\InvalidCredentialsException;
use App\Models\ServiceProvider;
use Illuminate\Support\Facades\Log;


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
            'image_url'     => 'required|url'
        ]);

        $validated['password_hash'] = bcrypt($validated['password']);
        unset($validated['password']);

        try {
            ServiceProvider::register($validated);

            return response()->json(["message" => "Registered successfully"], 201);
        } catch (DuplicateEmailException $e) {
            return response()->json(['error' => 'Email already exists'], 409);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:30',
        ]);

        try {
            $result = ServiceProvider::login($validated);
            return response()->json($result);
        } catch (UserNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (InvalidCredentialsException $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            Log::error($e);
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
                'category_id' => $user->category_id,
                'contact_phone' => $user->contact_phone,
                'image_url' => $user->image_url,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
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
    public function registerEmployees(Request $request, ServiceProvider $serviceProvider)
    {
        $validated = $request->validate([
            'count' => 'required|integer|min:1|max:100'
        ]);

        try {
            $employees = $serviceProvider->registerEmployees($validated['count']);
            return response()->json(['message' => 'Employees registered', 'employees' => $employees], 201);
        } catch (DuplicateEmployeeException $e) {
            return response()->json(['error' => 'Duplicate employee IDs detected'], 409);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}

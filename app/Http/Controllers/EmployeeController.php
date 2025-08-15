<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\EmployeeNotFoundException;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    // Get all employees
    public function index()
    {
        $employees = Employee::with(['data:id,employee_id'])
            ->select(['id', 'unique_id', 'is_active'])
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'unique_id' => $employee->unique_id,
                    'is_active' => $employee->is_active,
                   
                ];
            });

        return response()->json($employees);
    }

    // Get employee profile
    public function show($id)
    {
        $employee = Employee::with('data')->find($id);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        return response()->json($employee->getProfile());
    }

    // Update profile
    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('employees_data', 'email')->ignore($id, 'employee_id')
            ],
            'image_url' => 'sometimes|string',
            'sub_account_id' => 'sometimes|string',
        ]);

        $employee = Employee::with('data')->find($id);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $profile = $employee->updateProfile($request->all());

        return response()->json($profile);
    }

    // Change password
    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $employee = Employee::with('data')->find($id);

        if (!$employee || !$employee->data) {
            return response()->json(['error' => 'Employee or profile not found'], 404);
        }

        $employee->changePassword($request->password);

        return response()->json(['message' => 'Password updated successfully']);
    }

    // Complete registration
    public function completeRegistration(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees_data,email',
            'password' => 'required|string|min:8|confirmed',
            'image_url' => 'sometimes|string',
            'sub_account_id' => 'sometimes|string',
        ]);

        $employee = Employee::with('data')->find($id);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        try {
            // Convert password to password_hash for the database
            $data = $request->all();
            $data['password_hash'] = Hash::make($data['password']);
            unset($data['password']);

            $employee->completeRegistration($data);
            return response()->json(['message' => 'Registration completed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

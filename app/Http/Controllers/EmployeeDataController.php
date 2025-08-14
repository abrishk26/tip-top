<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeDataController extends Controller
{
    // Get all employee data
    public function index()
    {
        $employeeData = EmployeeData::select([
            'id',
            'employee_id',
            'first_name',
            'last_name',
            'email',
            'image_url',
            'sub_account_id',
        ])
        ->get();

        return response()->json($employeeData);
    }

    // Get specific employee data
    public function show($id)
    {
        $employeeData = EmployeeData::find($id);

        if (!$employeeData) {
            return response()->json(['error' => 'Employee data not found'], 404);
        }

        return response()->json($employeeData);
    }

    // Create new employee data
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|exists:employees,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees_data,email',
            'password' => 'required|string|min:8|confirmed',
            'image_url' => 'sometimes|string',
            'sub_account_id' => 'sometimes|string',
        ]);

        try {
            $data = $request->only([
                'employee_id',
                'first_name', 
                'last_name',
                'email',
                'image_url',
                'sub_account_id'
            ]);
            
            $data['password_hash'] = Hash::make($request->password);

            $employeeData = EmployeeData::create($data);

            return response()->json($employeeData, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create employee data: ' . $e->getMessage()], 500);
        }
    }

    // Update employee data
    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('employees_data', 'email')->ignore($id)
            ],
            'image_url' => 'sometimes|string',
            'sub_account_id' => 'sometimes|string',
        ]);

        $employeeData = EmployeeData::find($id);

        if (!$employeeData) {
            return response()->json(['error' => 'Employee data not found'], 404);
        }

        try {
            $data = $request->only([
                'first_name',
                'last_name',
                'email',
                'image_url',
                'sub_account_id'
            ]);
            
            $employeeData->update($data);
            return response()->json($employeeData);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update employee data: ' . $e->getMessage()], 500);
        }
    }

    // Update password
    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $employeeData = EmployeeData::find($id);

        if (!$employeeData) {
            return response()->json(['error' => 'Employee data not found'], 404);
        }

        try {
            $employeeData->update([
                'password_hash' => Hash::make($request->password)
            ]);

            return response()->json(['message' => 'Password updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update password: ' . $e->getMessage()], 500);
        }
    }

    // Delete employee data
    public function destroy($id)
    {
        $employeeData = EmployeeData::find($id);

        if (!$employeeData) {
            return response()->json(['error' => 'Employee data not found'], 404);
        }

        try {
            $employeeData->delete();
            return response()->json(['message' => 'Employee data deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete employee data: ' . $e->getMessage()], 500);
        }
    }

    // Get employee data by employee ID
    public function getByEmployeeId($employeeId)
    {
        $employeeData = EmployeeData::where('employee_id', $employeeId)->first();

        if (!$employeeData) {
            return response()->json(['error' => 'Employee data not found for this employee'], 404);
        }

        return response()->json($employeeData);
    }
}
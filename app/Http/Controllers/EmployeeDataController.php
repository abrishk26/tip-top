<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeDataController extends Controller
{
    // Get specific employee data
    public function show($id)
    {
        $employeeData = EmployeeData::find($id);

        if (!$employeeData) {
            return response()->json(['error' => 'Employee data not found'], 404);
        }

        return response()->json($employeeData);
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
}
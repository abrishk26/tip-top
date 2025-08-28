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

    /**
     * Get authenticated employee profile information
     */
    public function getProfile(Request $request)
    {
        try {
            $employee = $request->user();
            $profile = $employee->data;

            if (!$profile) {
                return response()->json(['error' => 'Profile not found'], 404);
            }

            return response()->json([
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'id' => $employee->id,
                    'unique_id' => $employee->unique_id,
                    'is_active' => $employee->is_active,
                    'is_verified' => $employee->is_verified,
                    'first_name' => $profile->first_name,
                    'last_name' => $profile->last_name,
                    'email' => $profile->email,
                    'image_url' => $profile->image_url,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Update authenticated employee profile information
     */
    public function updateProfile(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'image_url' => 'sometimes|url|nullable',
            ]);

            if (empty($validated)) {
                return response()->json(['error' => 'No fields to update'], 400);
            }

            $employee = $request->user();
            $profile = $employee->data;

            if (!$profile) {
                return response()->json(['error' => 'Profile not found'], 404);
            }

            $profile->update($validated);

            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => [
                    'first_name' => $profile->first_name,
                    'last_name' => $profile->last_name,
                    'image_url' => $profile->image_url,
                    'updated_at' => $profile->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Change authenticated employee password
     */
    public function changePassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|max:200',
                'confirm_password' => 'required|same:new_password',
            ]);

            $employee = $request->user();
            $profile = $employee->data;

            if (!$profile) {
                return response()->json(['error' => 'Profile not found'], 404);
            }

            // Verify current password
            if (!Hash::check($validated['current_password'], $profile->password_hash)) {
                return response()->json(['error' => 'Current password is incorrect'], 401);
            }

            // Update password
            $profile->update([
                'password_hash' => Hash::make($validated['new_password'])
            ]);

            return response()->json(['message' => 'Password changed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Deactivate authenticated employee account
     */
    public function deactivateAccount(Request $request)
    {
        try {
            $employee = $request->user();
            
            // Set employee as inactive instead of deleting
            $employee->update(['is_active' => false]);
            
            // Revoke all tokens
            $employee->tokens()->delete();

            return response()->json(['message' => 'Account deactivated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
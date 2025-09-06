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
        $employeeData = EmployeeData::with('employee')->get();
        return response()->json(['data' => $employeeData]);
    }

    // Create new employee data
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|ulid|exists:employees,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees_data,email',
            'image_url' => 'sometimes|url',
        ]);

        try {
            $employeeData = EmployeeData::create($request->only([
                'employee_id',
                'first_name',
                'last_name',
                'email',
                'image_url',
            ]));

            return response()->json([
                'message' => 'Employee data created successfully',
                'data' => $employeeData
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create employee data'], 500);
        }
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

    // Get employee data by employee ID
    public function getByEmployeeId($employeeId)
    {
        $employeeData = EmployeeData::where('employee_id', $employeeId)->with('employee')->first();

        if (!$employeeData) {
            return response()->json(['error' => 'Employee data not found'], 404);
        }

        return response()->json(['data' => $employeeData]);
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
    public function updatePassword(Request $request)
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

            return response()->json(['message' => 'Password updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
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

            // Get bank account information if it exists
            $bankAccount = $employee->subAccount;
            $bankAccountInfo = null;
            
            if ($bankAccount) {
                $bankAccountInfo = [
                    'sub_account_id' => $bankAccount->sub_account,
                    'has_bank_account' => true,
                    'bank_account_updated_at' => $bankAccount->updated_at,
                ];
            } else {
                $bankAccountInfo = [
                    'has_bank_account' => false,
                    'sub_account_id' => null,
                    'bank_account_updated_at' => null,
                ];
            }

            return response()->json([
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'id' => $employee->id,
                    'tip_code' => $employee->tip_code,
                    'is_active' => $employee->is_active,
                    'is_verified' => $employee->is_verified,
                    'first_name' => $profile->first_name,
                    'last_name' => $profile->last_name,
                    'email' => $profile->email,
                    'image_url' => $profile->image_url,
                    'bank_account' => $bankAccountInfo,
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

            // Get bank account information if it exists
            $bankAccount = $employee->subAccount;
            $bankAccountInfo = null;
            
            if ($bankAccount) {
                $bankAccountInfo = [
                    'sub_account_id' => $bankAccount->sub_account,
                    'has_bank_account' => true,
                    'bank_account_updated_at' => $bankAccount->updated_at,
                ];
            } else {
                $bankAccountInfo = [
                    'has_bank_account' => false,
                    'sub_account_id' => null,
                    'bank_account_updated_at' => null,
                ];
            }

            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => [
                    'first_name' => $profile->first_name,
                    'last_name' => $profile->last_name,
                    'image_url' => $profile->image_url,
                    'updated_at' => $profile->updated_at,
                    'bank_account' => $bankAccountInfo,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Get authenticated employee subaccount information
     */
    public function getBankAccount(Request $request)
    {
        try {
            $employee = $request->user();
            $subAccount = $employee->subAccount;

            if (!$subAccount) {
                return response()->json(['error' => 'Bank account not found'], 404);
            }

            return response()->json([
                'message' => 'Subaccount retrieved successfully',
                'data' => [
                    'sub_account_id' => $subAccount->sub_account,
                    'updated_at' => $subAccount->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Update authenticated employee bank account information
     * This sends a request to Chapa to recreate the subaccount with new bank details
     */
    public function updateBankAccount(Request $request)
    {
        try {
            $validated = $request->validate([
                'business_name' => 'required|string|max:255',
                'account_name' => 'required|string|max:255',
                'bank_code' => 'required|integer',
                'account_number' => 'required|string',
            ]);

            $employee = $request->user();
            $existingSubAccount = $employee->subAccount;

            if (!$existingSubAccount) {
                return response()->json(['error' => 'Bank account not found'], 404);
            }

            // Get Chapa configuration
            $chapaConfig = config('services.chapa');

            // Send request to Chapa to create new subaccount with updated bank details
            $chapa_response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $chapaConfig['key'],
            ])->post($chapaConfig['url']['subaccount'], [
                'business_name' => $validated['business_name'],
                'account_name'  => $validated['account_name'],
                'bank_code'     => $validated['bank_code'],
                'account_number' => $validated['account_number'],
                'split_value'   => $chapaConfig['split_value'],
                'split_type'    => $chapaConfig['split_type'],
            ]);

            $response = $chapa_response->json();
            $statusCode = $chapa_response->status();

            if ($chapa_response->clientError()) {
                $message = $response['message'] ?? 'Unknown error';

                $serverErrors = [
                    'Authorization required',
                    'Invalid API Key or User doesn\'t exist',
                    'To create subaccounts via API you need to be on live mode',
                    'You Can\'t create a subaccount via API, try to create from dashboard.',
                    'Required Attribute: [ "validation.required" ]',
                ];

                if (in_array($message, $serverErrors)) {
                    \Illuminate\Support\Facades\Log::error('Chapa server/auth error: ' . json_encode($response));
                    return response()->json([
                        'error' => 'Payment service is temporarily unavailable. Please try again later.',
                    ], 500);
                }

                $userErrors = [
                    'The account number is not valid for bank name',
                    'This bank is not longer supported or banned by National bank of Ethiopia',
                    'This subaccount does exist',
                    'The Bank Code is incorrect please check if it does exist with our getbanks endpoint.',
                    'Something went wrong while creating the subaccount.',
                ];

                if (in_array($message, $userErrors)) {
                    return response()->json([
                        'error' => $message,
                    ], 400);
                }

                \Illuminate\Support\Facades\Log::error('Unhandled Chapa client error: ' . json_encode($response));
                return response()->json([
                    'error' => 'Could not process your request at the moment. Please try again later.',
                ], 500);
            } else if ($chapa_response->serverError()) {
                \Illuminate\Support\Facades\Log::error('Chapa unexpected response: ' . json_encode($response));
                return response()->json([
                    'error' => 'Something went wrong with user account processing.',
                ], 500);
            }

            $newSubAccountID = $response['data']['subaccount_id'] ?? null;

            if (!$newSubAccountID) {
                return response()->json([
                    'error' => 'Unable to create subaccount. Please try again later.',
                ], 500);
            }

            // Update the existing subaccount record with the new Chapa subaccount ID
            $existingSubAccount->update([
                'sub_account' => $newSubAccountID
            ]);

            return response()->json([
                'message' => 'Bank account updated successfully',
                'data' => [
                    'sub_account_id' => $newSubAccountID,
                    'updated_at' => $existingSubAccount->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating bank account: ' . $e->getMessage());
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
            

            $employee->tokens()->delete();

            return response()->json(['message' => 'Account deactivated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
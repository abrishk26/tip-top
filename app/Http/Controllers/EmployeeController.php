<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\SubAccount;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\EmployeeData;

class EmployeeController extends Controller
{
    // Get all employees
    public function index()
    {
        $employees = Employee::with(['data:id,employee_id'])
            ->select(['id', 'unique_id', 'is_active', 'first_name', 'last_name'])
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'unique_id' => $employee->unique_id,
                    'is_active' => $employee->is_active,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
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

    public function completeRegistration(Request $request) {
        $validated = $request->validate([
            'employee_code' => 'required|ulid',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees_data,email',
            'password' => 'required|string|min:8|max:200',
            'image_url' => 'sometimes|url',
        ]);

        $check = EmployeeData::where('email', $validated['email'])->first();

        if ($check) {
            return response()->json(["error" => "the email has already been taken"], 422);
        }

        try {
            $employee = Employee::findOrFail($validated['employee_code']);

            $validated['password_hash'] = Hash::make($validated['password']);

            unset($validated['password']);

            $employee->create($validated);

            //TODO: send email here

            return response()->json(['message' => 'Registration completed successfully']);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Employee not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => 'Internal server error. Please try again later.'
            ], 500);
        }
    }

    public function completeBankInfo(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|ulid',
            'business_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'bank_code' => 'required|integer',
            'account_number' => 'required|string'
        ]);

        $check = Employee::where('id', $validated['id'])->first();

        if (!$check) {
            return response()->json(["error" => "invalid credential"], 422);
        }

        $chapaConfig = config('services.chapa');

        $chapa_response = Http::withHeaders([
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
                'Invalid API Key or User doesn’t exist',
                'To create subaccounts via API you need to be on live mode',
                'You Can’t create a subaccount via API, try to create from dashboard.',
                'Required Attribute: [ “validation.required” ]',
            ];

            if (in_array($message, $serverErrors)) {
                Log::error('Chapa server/auth error: ' . json_encode($response));
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

            Log::error('Unhandled Chapa client error: ' . json_encode($response));
            return response()->json([
                'error' => 'Could not process your request at the moment. Please try again later.',
            ], 500);

        } else {
            Log::error('Chapa unexpected response: ' . json_encode($response));
            return response()->json([
                'error' => 'Something went wrong with user account processing.',
            ], 500);
        }


            $subAccountID = $response['data']['subaccounts_id'] ?? null;

            if (!$subaccountId) {
                return response()->json([
                    'error' => 'Unable to create subaccount. Please try again later.',
                ], 500);
            }

            $subAccount = new SubAccount;
            $subAccount->sub_account = $subAccountID;
            $subAccount->employee_id = $validated['id'];

            $subAccount->save();

            return response()->json(['message' => 'account registered successfully']);

    }
}

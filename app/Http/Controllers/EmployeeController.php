<?php

namespace App\Http\Controllers;

use App\Mail\VerificationEmail;
use App\Models\Employee;
use App\Models\SubAccount;
use App\Models\EmployeeData;
use App\Models\VerificationToken;
use App\Exceptions\InvalidCredentialsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmployeeController extends Controller
{
    public function completeRegistration(Request $request)
    {
        // Validate request input
        $validated = $request->validate([
            'employee_code' => 'required|ulid',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees_data,email',
            'password' => 'required|string|min:8|max:200',
            'image_url' => 'sometimes|url',
        ]);

        // check if the given email already exists
        $check = EmployeeData::where('email', $validated['email'])->first();
        if ($check) {
            return response()->json(["error" => "the email has already been taken"], 409);
        }


        // check if the employee is registered by the provider
        $employee = Employee::where('id', $validated['employee_code'])->first();
        if (!$employee) {
            return response()->json(['error' => 'employee not found'], 404);
        }

        // store the employee data
        $validated['password_hash'] = Hash::make($validated['password']);
        unset($validated['password']);

        unset($validated['employee_code']);
        $employee->create($validated);


        // send verification email
        $token = Str::random(64);
        VerificationToken::create([
            'token' => $token,
            'tokenable_type' => 'employee',
            'tokenable_id' => $employee->id,
            'expires_at' => now()->addHours(24),
        ]);
        $verificationLink = config('app.frontend_url', 'http://localhost:8000') . '/api/employees/verify-email/?token=' . $token;
        Mail::to($validated['email'])->queue(new VerificationEmail($verificationLink));


        return response()->json(['message' => 'Registration completed successfully']);
    }

    public function login(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:30',
        ]);

        try {
            $result = $employee->login($validated);
            return response()->json(['message' => 'login successful', 'token' => $result]);
        } catch (InvalidCredentialsException $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        } catch (\App\Exceptions\EmailNotVerifiedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $employee = $request->user();
            $employee->tokens()->delete();
            
            return response()->json(['message' => 'logout successful']);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    // list transactions for the authenticated employee
    public function transactions(Request $request)
    {
        $employee = $request->user();
        $list = \App\Models\Transaction::query()
            ->join('tips', 'transactions.tip_id', '=', 'tips.id')
            ->join('payments', 'payments.tip_id', '=', 'tips.id')
            ->where('tips.employee_id', $employee->id)
            ->orderByDesc('transactions.created_at')
            ->select(['transactions.id', 'transactions.tx_ref', 'transactions.status', 'transactions.created_at', 'payments.amount'])
            ->get();

        return response()->json(['transactions' => $list]);
    }

    
    
    public function completeBankInfo(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'bank_code' => 'required|integer',
            'account_number' => 'required|string'
        ]);

        $employee = $request->user();

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
        } else if ($chapa_response->serverError()) {
            Log::error('Chapa unexpected response: ' . json_encode($response));
            return response()->json([
                'error' => 'Something went wrong with user account processing.',
            ], 500);
        }


        $subAccountID = $response['data']['subaccount_id'] ?? null;

        if (!$subAccountID) {
            return response()->json([
                'error' => 'Unable to create subaccount. Please try again later.',
            ], 500);
        }

        $subAccount = new SubAccount;
        $subAccount->sub_account = $subAccountID;
        $subAccount->employee_id = $employee->id;

        $subAccount->save();

        return response()->json(['message' => 'account registered successfully']);
    }

    // Get all employees
    public function index()
    {
        $employees = Employee::with(['data:id,employee_id, first_name, last_name'])
            ->select(['id', 'is_active'])
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'is_active' => $employee->is_active,
                    'first_name' => $employee->data?->first_name,
                    'last_name' => $employee->data?->last_name,
                ];
            });

        return response()->json($employees);
    }

    public function verifyEmail(Request $request)
    {
        $token = $request->query('token');

        $record = VerificationToken::where('token', $token)->first();

        if (!$record) {
            return response()->json(['error' => 'invalid token.'], 400);
        }

        if ($record->expires_at->isPast()) {
            $record->delete();
            return response()->json(['error' => 'token expired'], 400);
        }

        $employee = Employee::where('id', $record->tokenable_id)->first();
        $employee->is_verified = true;
        $employee->save();

        $record->delete();

        return response()->json(['message' => 'email verified successfully']);
    }
}

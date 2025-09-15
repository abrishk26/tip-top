<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\SubAccount;
use App\Models\Transaction;
use App\Models\Tip;
use App\Models\Payment;
use App\Models\Employee;

class TipController extends Controller
{
    public function processTip(Request $request, $tipCode)
    {
        $amount = $request->query('amount');
        if (!$amount) {
            return response()->json(['error' => 'tip amount missing'], 409);
        }

        $employee = Employee::where('tip_code', $tipCode)->first();

        if (!$employee) {
            return response()->json(['error' => 'employee not found'], 409);
        }

        $sub_account = SubAccount::where('employee_id', $employee->id)->first();
        $tx_ref = Str::random(10);
        $chapaConfig = config('services.chapa');
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $chapaConfig['key'],
                'Content-Type' => 'application/json',
            ])->post($chapaConfig['url']['transaction'], [
                'amount' => $amount,
                'tx_ref' => $tx_ref,
                'customization' => [
                    'subaccounts' => [
                        'id' => $sub_account->sub_account,
                    ],
                ],
            ]);

            $tip = Tip::create([
                'employee_id' => $employee->id,
                'service_provider_id' => $employee->serviceProvider->id,
                'amount' => $amount,
            ]);

            Transaction::create([
                'tx_ref' => $tx_ref,
                'tip_id' => $tip->id,
            ]);

            Log::info($response);
            return response()->json([
                'link' => $response['data']['checkout_url'],
                'tx_ref' => $tx_ref,
            ], 200);

        } catch (ConnectionException $e) {
            return response()->json(['error' => 'internal server error'], 500);
        }
    }

    public function verifyTipPayment(Request $request)
    {
        Log::info('processing request from chapa');

        $amount = (float) $request->input('amount');
        $charge = (float) $request->input('charge');
        if (!is_numeric($request->input('amount')) || !is_numeric($request->input('charge'))) {
            return response()->json(['error' => 'invalid amount or charge'], 422);
        }
        $tx_ref = $request->input('tx_ref');

        if (!$tx_ref) {
            return response()->json(['error' => 'tx_ref is required'], 422);
        }

        $transaction = Transaction::where('tx_ref', $tx_ref)->first();
        if (!$transaction) {
            return response()->json(['error' => 'transaction not found'], 404);
        }
        if ($transaction->status === 'completed') {
            return response('Already processed', 200);
        }
        $transaction->update(['status' => 'completed']);

        $tip = Tip::where('id', $transaction->tip_id)->first();
        if (!$tip) {
            return response()->json(['error' => 'tip not found'], 404);
        }
        $tip->status = 'completed';
        $tip->save();

        $chapaConfig = config('services.chapa');

        $employee = Employee::where('id', $tip->employee_id)->first();
        if (!$employee) {
            return response()->json(['error' => 'employee not found'], 404);
        }
        Payment::create([
            'tip_id' => $tip->id,
            'employee_id' => $employee->id,
            'amount' => $amount - $charge - ($amount * $chapaConfig['split_value']),
            'service_fee' => $amount * $chapaConfig['split_value'],
            'chapa_fee' => $charge,
        ]);

        return response('holla', 200);
    }
}

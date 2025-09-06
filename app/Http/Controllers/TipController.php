<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Number;
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
            return response()->json(['link' => $response['data']['checkout_url']], 200);

        } catch (ConnectionException $e) {
            return response()->json(['error' => 'internal server error'], 500);
        }
    }

    public function verifyTipPayment(Request $request)
    {
        Log::info('processing request from chapa');

        $amount = Number::parseFloat($request->input('amount'));
        $charge = Number::parseFloat($request->input('charge'));
        $tx_ref = $request->input('tx_ref');

        $transaction = Transaction::where('tx_ref', $tx_ref)->first();
        if ($transaction->status === 'completed') {
            return response('Already processed', 200);
        }
        $transaction->update(['status' => 'completed']);

        $tip = Tip::where('id', $transaction->tip_id)->first();
        $tip->status = 'completed';
        $tip->save();

        $chapaConfig = config('services.chapa');

        $employee = Employee::where('id', $tip->employee_id)->first();
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

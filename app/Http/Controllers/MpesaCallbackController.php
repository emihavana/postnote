<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MpesaCallbackController extends Controller
{
    /**
     * Handle the M-Pesa STK Push Webhook Callback.
     * Safaricom hits this endpoint asynchronously after the user enters their PIN or cancels.
     */
    public function handleCallback(Request $request)
    {
        // 1. Instantly capture and log the payload for debugging and transparency
        $payload = $request->all();
        Log::info('M-Pesa Webhook Incoming Payload:', $payload);

        // 2. Safely parse Safaricom's nested JSON structure
        $stkCallback = $payload['Body']['stkCallback'] ?? null;
        if (!$stkCallback) {
            Log::error('M-Pesa Callback received an invalid data structure.');
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid Payload Structure']);
        }

        $resultCode = $stkCallback['ResultCode'];
        $checkoutRequestID = $stkCallback['CheckoutRequestID'];

        // 3. Locate the pending transaction tracking row in your database
        $transaction = DB::table('mpesa_transactions')
            ->where('checkout_request_id', $checkoutRequestID)
            ->first();

        if (!$transaction) {
            Log::error("M-Pesa CheckoutID {$checkoutRequestID} could not be matched to an existing record.");
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Transaction reference record missing']);
        }

        // 4. Process based on the Result Code (0 means Success)
        if ($resultCode == 0) {

            // Extract the metadata items array
            $metadata = $stkCallback['CallbackMetadata']['Item'] ?? [];

            // Extract specific item values out of Safaricom's key-value pair formatting
            $mpesaReceipt = collect($metadata)->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;

            // Update database tracking state to COMPLETED
            DB::table('mpesa_transactions')
                ->where('checkout_request_id', $checkoutRequestID)
                ->update([
                    'status' => 'COMPLETED',
                    'mpesa_receipt' => $mpesaReceipt,
                    'updated_at' => now()
                ]);

            Log::info("Payment successful for CheckoutID: {$checkoutRequestID}. Receipt Registered: {$mpesaReceipt}");

            // TODO: Place any additional custom business actions here (e.g., dispatching order invoices, dispatching texts)

        } else {
            // Transaction failed or was manually cancelled by the customer (e.g., Code 1032)
            $resultDesc = $stkCallback['ResultDesc'] ?? 'Cancelled/Failed';

            DB::table('mpesa_transactions')
                ->where('checkout_request_id', $checkoutRequestID)
                ->update([
                    'status' => 'FAILED',
                    'updated_at' => now()
                ]);

            Log::warning("Payment failure/cancellation recorded for CheckoutID: {$checkoutRequestID}. Reason: {$resultDesc}");
        }

        // 5. Always respond to Safaricom with a JSON success structure to prevent them from retrying
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Callback processed and recorded successfully.'
        ]);
    }
}

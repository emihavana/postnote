<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Iankumu\Mpesa\Facades\Mpesa; // Handles the background Daraja API call

class Mpesapay extends Component
{
    // Keeping data types flexible so leading zeros aren't dropped by PHP math rules
    public $phoneNumber;
    public $amount;

    // Tracking states
    public $checkoutRequestId = null;
    public $paymentStatus = null; // null, 'PENDING', 'COMPLETED', 'FAILED'
    public $errorMessage = null;
    public $mpesaReceipt = null;

    /**
     * Your custom Kenyan phone & amount validation rules
     */
    protected function rules()
    {
        return [
            'phoneNumber' => ['required', 'regex:/^(?:254|\+254|0)?(7[0-9]{8}|1[0-9]{8})$/'],
            'amount' => 'required|numeric|min:1',
        ];
    }

    /**
     * Triggers when user clicks your pay button
     */
    public function pay()
    {
        // 1. Run your custom validation rules
        $this->validate();
        $this->errorMessage = null;

        try {
            $reference = 'INV-' . time();
            $description = 'Online Checkout Payment';

            // 2. Fire the STK push via the package
            // The package formats your validated number into Safaricom's standard 254... structure automatically
            $response = Mpesa::stkpush($this->phoneNumber, $this->amount, $reference, $description);
            $result = json_decode($response);

            // 3. If Safaricom accepts the payload structure
            if (isset($result->ResponseCode) && $result->ResponseCode == "0") {

                $this->checkoutRequestId = $result->CheckoutRequestID;
                $this->paymentStatus = 'PENDING';

                // 4. Save to the database table with 'PENDING' status
                DB::table('mpesa_transactions')->insert([
                    'checkout_request_id' => $this->checkoutRequestId,
                    'phone_number' => $this->phoneNumber,
                    'amount' => $this->amount,
                    'status' => 'PENDING',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $this->errorMessage = $result->ResponseDescription ?? "Safaricom rejected the request structure.";
            }
        } catch (\Exception $e) {
            $this->errorMessage = "System Error: " . $e->getMessage();
        }
    }

    /**
     * Triggered automatically every 2 seconds by wire:poll in your blade file
     */
    public function checkPaymentStatus()
    {
        if (!$this->checkoutRequestId) {
            return;
        }

        // Scan the table to see if your API controller has written the webhook confirmation yet
        $transaction = DB::table('mpesa_transactions')
            ->where('checkout_request_id', $this->checkoutRequestId)
            ->first();

        if ($transaction) {
            $this->paymentStatus = $transaction->status;

            if ($transaction->status === 'COMPLETED') {
                $this->mpesaReceipt = $transaction->mpesa_receipt;
            }
        }
    }

    public function render()
    {
        return view('livewire.mpesapay');
    }
}

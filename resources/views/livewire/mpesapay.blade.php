<div class="p-6 bg-white rounded-xl shadow-md border border-gray-100">
    @if ($errorMessage)
    <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-200">
        {{ $errorMessage }}
    </div>
    @endif

    {{-- STATE 1: Show Form (Only if payment hasn't started yet) --}}
    @if (!$paymentStatus)
    <form wire:submit.prevent="pay" class="space-y-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider">M-Pesa Mobile
                Number</label>
            <input type="text" wire:model="phoneNumber" placeholder="e.g. 0712345678"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-3 bg-gray-50 text-black focus:ring-2 focus:ring-green-500 focus:border-green-500">
            @error('phoneNumber') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount (KES)</label>
            {{-- If you pass an amount from the parent page, we can make this readonly --}}
            <input type="number" wire:model="amount"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-3 bg-gray-50 text-black font-semibold focus:ring-2 focus:ring-green-500 focus:border-green-500">
            @error('amount') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <button type="submit"
            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-md flex items-center justify-center space-x-2">
            <span>Pay KES {{ number_format($amount) }} via M-Pesa</span>
        </button>
    </form>

    {{-- STATE 2: Waiting for M-Pesa PIN (Polls every 2 seconds) --}}
    @elseif ($paymentStatus === 'PENDING')
    <div wire:poll.2s="checkPaymentStatus" class="text-center py-8 space-y-4">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-green-600 border-t-transparent mx-auto"></div>
        <div>
            <p class="text-gray-800 font-bold text-lg">STK Push Sent!</p>
            <p class="text-sm text-gray-500 px-4 mt-1">Please check your phone ({{ $phoneNumber }}) and enter your
                M-Pesa PIN to authorize the payment.</p>
        </div>
    </div>

    {{-- STATE 3: Payment Success --}}
    @elseif ($paymentStatus === 'COMPLETED')
    <div class="text-center py-8 space-y-3">
        <div
            class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full text-green-600 text-3xl font-bold mx-auto">
            ✓</div>
        <div>
            <p class="text-xl font-black text-green-700">Payment Verified!</p>
            <p class="text-gray-600 text-sm mt-1">Receipt Number: <span
                    class="font-mono font-bold text-gray-900 bg-gray-100 px-2 py-0.5 rounded">{{ $mpesaReceipt }}</span>
            </p>
        </div>
    </div>

    {{-- STATE 4: Payment Failed / Cancelled --}}
    @elseif ($paymentStatus === 'FAILED')
    <div class="text-center py-8 space-y-4">
        <div
            class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full text-red-600 text-3xl font-bold mx-auto">
            ✕</div>
        <div>
            <p class="text-lg font-bold text-red-600">Payment Request Failed</p>
            <p class="text-gray-500 text-sm mt-1">The request was cancelled or timed out on your device.</p>
        </div>
        <button wire:click="resetCheckout"
            class="text-sm font-semibold text-green-600 hover:text-green-700 underline transition">
            Try Again
        </button>
    </div>
    @endif
</div>
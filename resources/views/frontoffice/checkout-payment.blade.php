<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Check-out & Payment</h1>
        <p class="text-gray-600">Room {{ $roomStay->hotelRoom->room_number }} - {{ $roomStay->guest->full_name }}</p>
    </div>

    @if(session('error'))
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Bill Summary -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Bill Summary</h2>

            <!-- Room Charges -->
            <div class="border-b pb-4 mb-4">
                <h3 class="font-semibold text-gray-700 mb-2">Room Charges</h3>
                <div class="flex justify-between text-sm mb-1">
                    <span>{{ $roomStay->hotelRoom->room_number }} - {{ $roomStay->hotelRoom->roomType->name }}</span>
                    <span>{{ $roomStay->nights }} nights</span>
                </div>
                <div class="flex justify-between text-sm mb-1">
                    <span>Rate per night</span>
                    <span>Rp {{ number_format($roomStay->room_rate_per_night, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-semibold">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($roomStay->total_room_charge, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- F&B Charges -->
            @if($roomStay->fnbOrders->count() > 0)
            <div class="border-b pb-4 mb-4">
                <h3 class="font-semibold text-gray-700 mb-2">F&B Charges</h3>
                @foreach($roomStay->fnbOrders as $order)
                    <div class="mb-3">
                        <div class="text-sm font-medium text-gray-600">#{{ $order->order_number }} - {{ $order->order_time->format('d M, H:i') }}</div>
                        @foreach($order->items as $item)
                        <div class="flex justify-between text-sm pl-3">
                            <span>{{ $item->quantity }}x {{ $item->menuItem->name }}</span>
                            <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                @endforeach
                <div class="flex justify-between font-semibold mt-2">
                    <span>F&B Subtotal</span>
                    <span>Rp {{ number_format($roomStay->fnbOrders->sum('total_amount'), 0, ',', '.') }}</span>
                </div>
            </div>
            @endif

            <!-- Taxes & Charges -->
            <div class="border-b pb-4 mb-4">
                <h3 class="font-semibold text-gray-700 mb-2">Taxes & Service</h3>
                <div class="flex justify-between text-sm mb-1">
                    <span>Tax (10%)</span>
                    <span>Rp {{ number_format($roomStay->tax_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>Service Charge (5%)</span>
                    <span>Rp {{ number_format($roomStay->service_charge, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Grand Total -->
            <div class="bg-blue-50 p-4 rounded mb-4">
                <div class="flex justify-between items-center">
                    <span class="text-xl font-bold text-gray-800">GRAND TOTAL:</span>
                    <span id="grand-total" class="text-2xl font-bold text-blue-600">
                        Rp {{ number_format($roomStay->total_room_charge + $roomStay->fnbOrders->sum('total_amount') + $roomStay->tax_amount + $roomStay->service_charge, 0, ',', '.') }}
                    </span>
                </div>
                <input type="hidden" id="grand-total-value" value="{{ $roomStay->total_room_charge + $roomStay->fnbOrders->sum('total_amount') + $roomStay->tax_amount + $roomStay->service_charge }}">
            </div>

            <!-- Deposit Applied -->
            @if($roomStay->paid_amount > 0)
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded mb-4">
                <h3 class="font-semibold text-green-900 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Deposit Applied
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-700">Deposit/Prepayment:</span>
                        <span class="font-semibold text-green-700">- Rp {{ number_format($roomStay->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    @php
                        $grandTotal = $roomStay->total_room_charge + $roomStay->fnbOrders->sum('total_amount') + $roomStay->tax_amount + $roomStay->service_charge;
                        $balanceDue = $grandTotal - $roomStay->paid_amount;
                    @endphp
                    <div class="flex justify-between border-t border-green-200 pt-2">
                        <span class="font-bold text-gray-900">Balance Due:</span>
                        <span class="font-bold text-xl {{ $balanceDue > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format($balanceDue, 0, ',', '.') }}
                        </span>
                    </div>
                    @if($balanceDue < 0)
                    <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-3 py-2 rounded mt-2">
                        <div class="flex items-center mb-1">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="font-semibold">Refund Due to Guest:</div>
                        </div>
                        <div class="text-2xl font-bold">Rp {{ number_format(abs($balanceDue), 0, ',', '.') }}</div>
                        <div class="text-xs mt-1">Deposit melebihi total tagihan, refund akan diproses</div>
                    </div>
                    @endif
                </div>

                <!-- Deposit Payment History -->
                @php
                    $depositPayments = $roomStay->payments->where('notes', 'like', '%Deposit%')->sortBy('payment_date');
                @endphp
                @if($depositPayments->count() > 0)
                <div class="mt-3 pt-3 border-t border-green-200">
                    <div class="text-xs font-semibold text-green-800 mb-2 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                        Deposit Payment History:
                    </div>
                    <div class="space-y-1">
                        @foreach($depositPayments as $payment)
                        <div class="bg-white bg-opacity-50 px-3 py-2 rounded text-xs">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="font-semibold text-green-800">
                                        {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                    </div>
                                    <div class="text-gray-600 text-[10px]">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $payment->payment_date->format('d M Y, H:i') }}
                                        @if($payment->payment_date->isToday())
                                            <span class="text-blue-600 font-semibold">(Hari ini)</span>
                                        @elseif($payment->payment_date->diffInDays(now()) <= 7)
                                            <span class="text-gray-500">({{ $payment->payment_date->diffForHumans() }})</span>
                                        @endif
                                    </div>
                                    @if($payment->notes && !str_contains($payment->notes, 'Deposit'))
                                    <div class="text-gray-500 text-[10px] italic">{{ $payment->notes }}</div>
                                    @endif
                                </div>
                                <div class="font-bold text-green-700 ml-2">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-2 pt-2 border-t border-green-200 flex justify-between text-xs font-semibold">
                        <span class="text-green-800">Total Deposits Received:</span>
                        <span class="text-green-700">Rp {{ number_format($depositPayments->sum('amount'), 0, ',', '.') }}</span>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Payment Form -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Payment Details</h2>

            <form method="POST" action="{{ route('frontoffice.checkout.process', $roomStay) }}" id="payment-form">
                @csrf

                {{-- Display validation errors --}}
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 dark:border-red-400 rounded">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <h3 class="text-sm font-bold text-red-800 dark:text-red-200 mb-1">Validation Error:</h3>
                                <ul class="text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @php
                    $grandTotal = $roomStay->total_room_charge + $roomStay->fnbOrders->sum('total_amount') + $roomStay->tax_amount + $roomStay->service_charge;
                    $balanceDue = $grandTotal - $roomStay->paid_amount;
                    $isRefundScenario = $balanceDue < 0;
                @endphp

                <!-- Refund Section (shown when deposit > total bill) -->
                @if($isRefundScenario)
                <div id="refund-section" class="mb-6 bg-yellow-50 border-2 border-yellow-400 rounded-lg p-4">
                    <div class="flex items-center mb-4">
                        <svg class="w-6 h-6 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <h3 class="text-lg font-bold text-yellow-900">Refund Processing Required</h3>
                    </div>

                    <div class="bg-white p-4 rounded mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-semibold text-gray-700">Refund Amount:</span>
                            <span class="text-2xl font-bold text-yellow-700">Rp {{ number_format(abs($balanceDue), 0, ',', '.') }}</span>
                        </div>
                        <p class="text-sm text-gray-600">The deposit paid exceeds the total bill. Please process a refund to the guest.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Refund Method <span class="text-red-500">*</span>
                            </label>
                            <select name="refund_method" id="refund_method" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                                <option value="">-- Select Refund Method --</option>
                                <option value="cash">💵 Cash</option>
                                <option value="bank_transfer">🏦 Bank Transfer</option>
                                <option value="credit_card">💳 Credit Card Reversal</option>
                                <option value="debit_card">💳 Debit Card Reversal</option>
                                <option value="other">💰 Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Bank Transfer Details (shown when bank_transfer selected) -->
                    <div id="refund-bank-details" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="font-semibold text-blue-900 mb-3">Bank Transfer Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bank Name</label>
                                <input type="text" name="refund_bank_name" class="w-full border-gray-300 rounded-lg shadow-sm" placeholder="e.g., BCA, Mandiri">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                                <input type="text" name="refund_account_number" class="w-full border-gray-300 rounded-lg shadow-sm" placeholder="Account number">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Account Holder Name</label>
                                <input type="text" name="refund_account_holder" class="w-full border-gray-300 rounded-lg shadow-sm" placeholder="Account holder name">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Refund Notes (Optional)</label>
                        <textarea name="refund_notes" rows="2" class="w-full border-gray-300 rounded-lg shadow-sm" placeholder="Additional notes about the refund..."></textarea>
                    </div>

                    <div class="mt-4 p-3 bg-blue-50 border-l-4 border-blue-500 rounded">
                        <p class="text-sm text-blue-800">
                            <strong>Note:</strong> No additional payment is required. The refund will be processed after checkout completion.
                        </p>
                    </div>
                </div>
                @endif

                <div id="payment-methods-container" class="{{ $isRefundScenario ? 'hidden' : '' }}">
                    <!-- Payment Method 1 (Default) -->
                    <div class="payment-method-item border rounded-lg p-4 mb-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold text-gray-700">Payment #1</h3>
                            <button type="button" class="remove-payment-btn hidden text-red-600 hover:text-red-800 text-sm">
                                Remove
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                                <select name="payments[0][payment_method]" class="payment-method-select w-full border-gray-300 rounded-lg shadow-sm" required>
                                    <option value="">Select Method</option>
                                    <option value="cash">💵 Cash</option>
                                    <option value="credit_card">💳 Credit Card</option>
                                    <option value="debit_card">💳 Debit Card</option>
                                    <option value="bank_transfer">🏦 Bank Transfer</option>
                                    <option value="other">💰 Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                                <input type="number" name="payments[0][amount]" class="payment-amount-input w-full border-gray-300 rounded-lg shadow-sm" step="0.01" min="0" required>
                            </div>
                        </div>

                        <!-- Card Details (hidden by default) -->
                        <div class="card-details hidden">
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Card Holder Name</label>
                                    <input type="text" name="payments[0][card_holder_name]" class="w-full border-gray-300 rounded-lg shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Last 4 Digits</label>
                                    <input type="text" name="payments[0][card_number_last4]" class="w-full border-gray-300 rounded-lg shadow-sm" maxlength="4" pattern="\d{4}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Card Type</label>
                                <select name="payments[0][card_type]" class="w-full border-gray-300 rounded-lg shadow-sm">
                                    <option value="">Select Type</option>
                                    <option value="visa">Visa</option>
                                    <option value="mastercard">Mastercard</option>
                                    <option value="amex">American Express</option>
                                    <option value="jcb">JCB</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Bank Transfer Details (hidden by default) -->
                        <div class="bank-details hidden">
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                                    <input type="text" name="payments[0][bank_name]" class="w-full border-gray-300 rounded-lg shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                                    <input type="text" name="payments[0][reference_number]" class="w-full border-gray-300 rounded-lg shadow-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <input type="text" name="payments[0][notes]" class="w-full border-gray-300 rounded-lg shadow-sm" placeholder="Optional notes">
                        </div>
                    </div>
                </div>

                <!-- Add Payment Button -->
                <button type="button" id="add-payment-btn" class="mb-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm {{ $isRefundScenario ? 'hidden' : '' }}">
                    + Add Another Payment Method (Split Payment)
                </button>

                <!-- Payment Summary -->
                <div class="bg-gray-50 p-4 rounded mb-4 {{ $isRefundScenario ? 'hidden' : '' }}">
                    <div class="flex justify-between mb-2">
                        <span class="font-medium">Total Bill:</span>
                        <span id="summary-total-bill" class="font-bold">Rp {{ number_format($roomStay->total_room_charge + $roomStay->fnbOrders->sum('total_amount') + $roomStay->tax_amount + $roomStay->service_charge, 0, ',', '.') }}</span>
                    </div>
                    @if($roomStay->paid_amount > 0)
                    <div class="flex justify-between mb-2 text-green-600">
                        <span class="font-medium">Deposit Applied:</span>
                        <span class="font-bold">- Rp {{ number_format($roomStay->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between mb-2 border-t pt-2">
                        <span class="font-medium text-gray-900">Balance Due:</span>
                        <span class="font-bold text-gray-900">Rp {{ number_format(($roomStay->total_room_charge + $roomStay->fnbOrders->sum('total_amount') + $roomStay->tax_amount + $roomStay->service_charge) - $roomStay->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between mb-2 {{ $roomStay->paid_amount > 0 ? '' : 'border-t pt-2' }}">
                        <span class="font-medium">Paying Now:</span>
                        <span id="summary-total-paid" class="font-bold text-blue-600">Rp 0</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="font-bold">Remaining:</span>
                        <span id="summary-remaining" class="font-bold text-red-600">
                            Rp {{ number_format(($roomStay->total_room_charge + $roomStay->fnbOrders->sum('total_amount') + $roomStay->tax_amount + $roomStay->service_charge) - $roomStay->paid_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex space-x-3">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                        Complete Check-out
                    </button>
                    <a href="{{ route('frontoffice.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let paymentMethodIndex = 1;
    const grandTotal = parseFloat(document.getElementById('grand-total-value').value);
    const depositPaid = {{ $roomStay->paid_amount ?? 0 }};
    const balanceDue = grandTotal - depositPaid;

    // Add new payment method
    document.getElementById('add-payment-btn').addEventListener('click', function() {
        const container = document.getElementById('payment-methods-container');
        const newPaymentMethod = `
            <div class="payment-method-item border rounded-lg p-4 mb-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-gray-700">Payment #${paymentMethodIndex + 1}</h3>
                    <button type="button" class="remove-payment-btn text-red-600 hover:text-red-800 text-sm">
                        Remove
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <select name="payments[${paymentMethodIndex}][payment_method]" class="payment-method-select w-full border-gray-300 rounded-lg shadow-sm" required>
                            <option value="">Select Method</option>
                            <option value="cash">💵 Cash</option>
                            <option value="credit_card">💳 Credit Card</option>
                            <option value="debit_card">💳 Debit Card</option>
                            <option value="bank_transfer">🏦 Bank Transfer</option>
                            <option value="other">💰 Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                        <input type="number" name="payments[${paymentMethodIndex}][amount]" class="payment-amount-input w-full border-gray-300 rounded-lg shadow-sm" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="card-details hidden">
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Card Holder Name</label>
                            <input type="text" name="payments[${paymentMethodIndex}][card_holder_name]" class="w-full border-gray-300 rounded-lg shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last 4 Digits</label>
                            <input type="text" name="payments[${paymentMethodIndex}][card_number_last4]" class="w-full border-gray-300 rounded-lg shadow-sm" maxlength="4" pattern="\\d{4}">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Card Type</label>
                        <select name="payments[${paymentMethodIndex}][card_type]" class="w-full border-gray-300 rounded-lg shadow-sm">
                            <option value="">Select Type</option>
                            <option value="visa">Visa</option>
                            <option value="mastercard">Mastercard</option>
                            <option value="amex">American Express</option>
                            <option value="jcb">JCB</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="bank-details hidden">
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                            <input type="text" name="payments[${paymentMethodIndex}][bank_name]" class="w-full border-gray-300 rounded-lg shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                            <input type="text" name="payments[${paymentMethodIndex}][reference_number]" class="w-full border-gray-300 rounded-lg shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <input type="text" name="payments[${paymentMethodIndex}][notes]" class="w-full border-gray-300 rounded-lg shadow-sm" placeholder="Optional notes">
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', newPaymentMethod);
        paymentMethodIndex++;
        updateRemoveButtons();
        attachEventListeners();
    });

    // Remove payment method
    function updateRemoveButtons() {
        const items = document.querySelectorAll('.payment-method-item');
        items.forEach((item, index) => {
            const removeBtn = item.querySelector('.remove-payment-btn');
            if (items.length > 1) {
                removeBtn.classList.remove('hidden');
            } else {
                removeBtn.classList.add('hidden');
            }
        });
    }

    // Attach event listeners to dynamic elements
    function attachEventListeners() {
        // Remove payment method buttons
        document.querySelectorAll('.remove-payment-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.payment-method-item').remove();
                updateRemoveButtons();
                updatePaymentSummary();
            });
        });

        // Payment method select change
        document.querySelectorAll('.payment-method-select').forEach(select => {
            select.addEventListener('change', function() {
                const parent = this.closest('.payment-method-item');
                const cardDetails = parent.querySelector('.card-details');
                const bankDetails = parent.querySelector('.bank-details');

                cardDetails.classList.add('hidden');
                bankDetails.classList.add('hidden');

                if (this.value === 'credit_card' || this.value === 'debit_card') {
                    cardDetails.classList.remove('hidden');
                } else if (this.value === 'bank_transfer') {
                    bankDetails.classList.remove('hidden');
                }
            });
        });

        // Payment amount input
        document.querySelectorAll('.payment-amount-input').forEach(input => {
            input.addEventListener('input', updatePaymentSummary);
        });
    }

    // Update payment summary
    function updatePaymentSummary() {
        let totalPaid = 0;
        document.querySelectorAll('.payment-amount-input').forEach(input => {
            const value = parseFloat(input.value) || 0;
            totalPaid += value;
        });

        const remaining = balanceDue - totalPaid;

        document.getElementById('summary-total-paid').textContent = 'Rp ' + totalPaid.toLocaleString('id-ID');
        document.getElementById('summary-remaining').textContent = 'Rp ' + remaining.toLocaleString('id-ID');

        if (Math.abs(remaining) < 0.01) {
            document.getElementById('summary-remaining').classList.remove('text-red-600');
            document.getElementById('summary-remaining').classList.add('text-green-600');
        } else {
            document.getElementById('summary-remaining').classList.remove('text-green-600');
            document.getElementById('summary-remaining').classList.add('text-red-600');
        }
    }

    // Initial setup
    attachEventListeners();
    updateRemoveButtons();

    // Refund method selection handler
    const refundMethodSelect = document.getElementById('refund_method');
    const refundBankDetails = document.getElementById('refund-bank-details');

    if (refundMethodSelect && refundBankDetails) {
        refundMethodSelect.addEventListener('change', function() {
            if (this.value === 'bank_transfer') {
                refundBankDetails.classList.remove('hidden');
            } else {
                refundBankDetails.classList.add('hidden');
            }
        });
    }

    // Check if this is a refund scenario
    const isRefundScenario = balanceDue < 0;

    // Form validation
    document.getElementById('payment-form').addEventListener('submit', function(e) {
        // Skip payment validation if refund scenario
        if (isRefundScenario) {
            // Just validate that refund method is selected
            if (refundMethodSelect && !refundMethodSelect.value) {
                e.preventDefault();
                alert('Silakan pilih metode refund sebelum melanjutkan checkout.');
                return;
            }
            // Allow form to submit - no payment needed
            return;
        }

        // Normal payment scenario validation
        let totalPaid = 0;
        document.querySelectorAll('.payment-amount-input').forEach(input => {
            totalPaid += parseFloat(input.value) || 0;
        });

        if (Math.abs(totalPaid - balanceDue) > 0.01) {
            e.preventDefault();
            let message = `Total pembayaran harus sama dengan saldo yang harus dibayar!\n\n`;
            message += `Grand Total: Rp ${grandTotal.toLocaleString('id-ID')}\n`;
            if (depositPaid > 0) {
                message += `Deposit Terbayar: Rp ${depositPaid.toLocaleString('id-ID')}\n`;
            }
            message += `Saldo Due: Rp ${balanceDue.toLocaleString('id-ID')}\n`;
            message += `Dibayar Sekarang: Rp ${totalPaid.toLocaleString('id-ID')}\n`;
            message += `Kurang: Rp ${(balanceDue - totalPaid).toLocaleString('id-ID')}`;
            alert(message);
        }
    });
</script>
</x-app-layout>

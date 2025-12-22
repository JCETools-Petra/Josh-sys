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
            <div class="bg-blue-50 p-4 rounded">
                <div class="flex justify-between items-center">
                    <span class="text-xl font-bold text-gray-800">GRAND TOTAL:</span>
                    <span id="grand-total" class="text-2xl font-bold text-blue-600">
                        Rp {{ number_format($roomStay->total_room_charge + $roomStay->fnbOrders->sum('total_amount') + $roomStay->tax_amount + $roomStay->service_charge, 0, ',', '.') }}
                    </span>
                </div>
                <input type="hidden" id="grand-total-value" value="{{ $roomStay->total_room_charge + $roomStay->fnbOrders->sum('total_amount') + $roomStay->tax_amount + $roomStay->service_charge }}">
            </div>
        </div>

        <!-- Payment Form -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Payment Details</h2>

            <form method="POST" action="{{ route('frontoffice.checkout.process', $roomStay) }}" id="payment-form">
                @csrf

                <div id="payment-methods-container">
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
                <button type="button" id="add-payment-btn" class="mb-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                    + Add Another Payment Method (Split Payment)
                </button>

                <!-- Payment Summary -->
                <div class="bg-gray-50 p-4 rounded mb-4">
                    <div class="flex justify-between mb-2">
                        <span class="font-medium">Total Bill:</span>
                        <span id="summary-total-bill" class="font-bold">Rp {{ number_format($roomStay->total_room_charge + $roomStay->fnbOrders->sum('total_amount') + $roomStay->tax_amount + $roomStay->service_charge, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="font-medium">Total Paid:</span>
                        <span id="summary-total-paid" class="font-bold text-blue-600">Rp 0</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="font-bold">Remaining:</span>
                        <span id="summary-remaining" class="font-bold text-red-600">Rp {{ number_format($roomStay->total_room_charge + $roomStay->fnbOrders->sum('total_amount') + $roomStay->tax_amount + $roomStay->service_charge, 0, ',', '.') }}</span>
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

        const remaining = grandTotal - totalPaid;

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

    // Form validation
    document.getElementById('payment-form').addEventListener('submit', function(e) {
        let totalPaid = 0;
        document.querySelectorAll('.payment-amount-input').forEach(input => {
            totalPaid += parseFloat(input.value) || 0;
        });

        if (Math.abs(totalPaid - grandTotal) > 0.01) {
            e.preventDefault();
            alert(`Total pembayaran harus sama dengan total tagihan!\n\nTagihan: Rp ${grandTotal.toLocaleString('id-ID')}\nDibayar: Rp ${totalPaid.toLocaleString('id-ID')}\nKurang: Rp ${(grandTotal - totalPaid).toLocaleString('id-ID')}`);
        }
    });
</script>
</x-app-layout>

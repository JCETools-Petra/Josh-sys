<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Guest Folio</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $roomStay->property->name }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('frontoffice.folio.print', $roomStay) }}" target="_blank"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print Folio
            </a>
            <a href="{{ route('frontoffice.index') }}"
                class="bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Front Office
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded">
        <p class="text-green-700 dark:text-green-400">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded">
        <p class="text-red-700 dark:text-red-400">{{ session('error') }}</p>
    </div>
    @endif

    <div class="grid md:grid-cols-3 gap-6">
        <!-- Guest & Stay Information -->
        <div class="md:col-span-1 space-y-6">
            <!-- Guest Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Guest Information</h2>
                <div class="space-y-3 text-sm">
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Guest Name</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->guest->full_name }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Email</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->guest->email ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Phone</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->guest->phone_number ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">ID Number</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->guest->id_number ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Stay Details -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Stay Details</h2>
                <div class="space-y-3 text-sm">
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Confirmation Number</div>
                        <div class="font-semibold text-blue-600">{{ $roomStay->confirmation_number }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Room Number</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->hotelRoom->room_number }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Room Type</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->hotelRoom->roomType->name ?? 'Standard' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Check-in Date</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->check_in_date->format('d M Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Check-out Date</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->check_out_date->format('d M Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Nights</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->check_in_date->diffInDays($roomStay->check_out_date) }} nights</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Status</div>
                        <div>
                            @if($roomStay->status === 'checked_in')
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Checked In</span>
                            @elseif($roomStay->status === 'checked_out')
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">Checked Out</span>
                            @else
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">{{ ucfirst($roomStay->status) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charges & Payments -->
        <div class="md:col-span-2 space-y-6">
            <!-- Daily Room Charges -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Daily Room Charges</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Description</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($dailyCharges as $charge)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 dark:text-white">
                                    {{ $charge['date']->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $charge['description'] }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-gray-800 dark:text-white">
                                    Rp {{ number_format($charge['rate'], 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400 text-sm">
                                    No daily charges recorded
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- F&B Orders -->
            @if($roomStay->fnbOrders->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Restaurant Charges</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date/Time</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Order #</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Items</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($roomStay->fnbOrders as $order)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 dark:text-white">
                                    {{ $order->order_time->format('d M Y H:i') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 dark:text-white">
                                    #{{ $order->order_number }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    @foreach($order->items as $item)
                                        <div>{{ $item->quantity }}x {{ $item->menuItem->name }}</div>
                                    @endforeach
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-gray-800 dark:text-white">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Room Changes History -->
            @if($roomChanges->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Room Changes History</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">From Room</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">To Room</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reason</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($roomChanges as $change)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 dark:text-white">
                                    {{ $change->processed_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $change->oldRoom->room_number }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 dark:text-white">
                                    {{ $change->newRoom->room_number }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ ucfirst(str_replace('_', ' ', $change->reason)) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Payments -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Payments Received</h2>
                    @if($roomStay->status === 'reserved' || $roomStay->status === 'checked_in')
                    <button type="button" onclick="showAddPaymentModal()" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Pembayaran
                    </button>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Method</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reference</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                                @if($roomStay->status === 'reserved' || $roomStay->status === 'checked_in')
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($roomStay->payments as $payment)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 dark:text-white">
                                    {{ $payment->payment_date->format('d M Y H:i') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                    @if($payment->notes)
                                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ Str::limit($payment->notes, 30) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $payment->reference_number ?? $payment->payment_number }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-green-600 font-semibold">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                @if($roomStay->status === 'reserved' || $roomStay->status === 'checked_in')
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" onclick="showEditPaymentModal({{ $payment->id }}, '{{ $payment->payment_method }}', {{ $payment->amount }}, '{{ addslashes($payment->notes ?? '') }}')"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="confirmDeletePayment({{ $payment->id }}, {{ $payment->amount }})"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ ($roomStay->status === 'reserved' || $roomStay->status === 'checked_in') ? '5' : '4' }}" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400 text-sm">
                                    No payments recorded
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Folio Summary -->
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-blue-800 dark:text-blue-400 mb-4">Folio Summary</h2>

                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-blue-700 dark:text-blue-300">Room Charges:</span>
                        <span class="font-semibold text-blue-800 dark:text-blue-200">Rp {{ number_format($roomCharges, 0, ',', '.') }}</span>
                    </div>
                    @if($breakfastCharges > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-blue-700 dark:text-blue-300">Breakfast Charges:</span>
                        <span class="font-semibold text-blue-800 dark:text-blue-200">Rp {{ number_format($breakfastCharges, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($fnbCharges > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-blue-700 dark:text-blue-300">F&B Charges:</span>
                        <span class="font-semibold text-blue-800 dark:text-blue-200">Rp {{ number_format($fnbCharges, 0, ',', '.') }}</span>
                    </div>
                    @endif

                    <div class="border-t border-blue-300 dark:border-blue-700 pt-2 mt-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-700 dark:text-blue-300">Subtotal:</span>
                            <span class="font-semibold text-blue-800 dark:text-blue-200">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-blue-700 dark:text-blue-300">Tax (10%):</span>
                        <span class="font-semibold text-blue-800 dark:text-blue-200">Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-blue-700 dark:text-blue-300">Service Charge (5%):</span>
                        <span class="font-semibold text-blue-800 dark:text-blue-200">Rp {{ number_format($serviceCharge, 0, ',', '.') }}</span>
                    </div>

                    @if($roomStay->discount_amount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-blue-700 dark:text-blue-300">Discount:</span>
                        <span class="font-semibold text-red-600">- Rp {{ number_format($roomStay->discount_amount, 0, ',', '.') }}</span>
                    </div>
                    @endif

                    <div class="border-t border-blue-300 dark:border-blue-700 pt-2 mt-2">
                        <div class="flex justify-between text-base">
                            <span class="font-semibold text-blue-800 dark:text-blue-300">Total Charges:</span>
                            <span class="font-bold text-blue-900 dark:text-blue-100">Rp {{ number_format($totalCharges, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between text-base">
                        <span class="font-semibold text-blue-800 dark:text-blue-300">Total Payments:</span>
                        <span class="font-bold text-green-600">Rp {{ number_format($totalPayments, 0, ',', '.') }}</span>
                    </div>

                    <div class="border-t-2 border-blue-400 dark:border-blue-600 pt-3 mt-3">
                        <div class="flex justify-between text-lg">
                            <span class="font-bold text-blue-900 dark:text-blue-200">Balance Due:</span>
                            <span class="font-bold {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                Rp {{ number_format($balance, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    {{-- 🔧 BUG FIX: Add Check-In button for reserved status --}}
                    @if($roomStay->status === 'reserved')
                    <div class="border-t border-blue-300 dark:border-blue-700 pt-4 mt-4">
                        <h4 class="font-semibold text-indigo-800 dark:text-indigo-300 mb-3 text-sm">Reservasi Perlu Diproses:</h4>

                        {{-- 💰 Payment Status Warning on Folio --}}
                        @php
                            $totalCharge = $roomStay->total_room_charge + $roomStay->tax_amount + $roomStay->service_charge;
                            $paidAmount = $roomStay->paid_amount ?? 0;
                            $balance = $totalCharge - $paidAmount;
                        @endphp

                        @if($balance > 0)
                        <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-3 mb-3 rounded">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-300 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="flex-1 text-sm">
                                    <p class="font-semibold text-yellow-800 dark:text-yellow-200 mb-1">⚠️ Pembayaran Belum Lunas</p>
                                    <div class="text-yellow-700 dark:text-yellow-300 text-xs space-y-0.5">
                                        <div class="flex justify-between">
                                            <span>Total:</span>
                                            <span class="font-medium">Rp {{ number_format($totalCharge, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Dibayar:</span>
                                            <span class="font-medium">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between border-t border-yellow-300 pt-0.5">
                                            <span class="font-bold">Sisa:</span>
                                            <span class="font-bold text-red-600">Rp {{ number_format($balance, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="bg-green-50 dark:bg-green-900 border border-green-200 p-2 mb-3 rounded">
                            <div class="flex items-center text-sm text-green-800 dark:text-green-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-semibold">✅ Pembayaran Lunas</span>
                            </div>
                        </div>
                        @endif
                        <form id="checkin-form" action="{{ route('frontoffice.verify-checkin', $roomStay) }}" method="POST">
                            @csrf
                            <button type="button" onclick="showCheckinModal()" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-semibold text-sm shadow-lg transition flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Check-In Sekarang
                            </button>
                        </form>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 text-center">
                            Klik tombol di atas untuk memproses check-in tamu
                        </p>
                    </div>
                    @endif

                    <!-- Early Check-in / Late Checkout Actions -->
                    @if($roomStay->status === 'checked_in' || $roomStay->status === 'confirmed')
                    <div class="border-t border-blue-300 dark:border-blue-700 pt-4 mt-4">
                        <h4 class="font-semibold text-blue-800 dark:text-blue-300 mb-2 text-sm">Quick Actions:</h4>
                        <div class="space-y-2">
                            @if($roomStay->status === 'confirmed' && $roomStay->check_in_date->isFuture())
                            <button type="button" onclick="showEarlyCheckinModal()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">
                                Request Early Check-in
                            </button>
                            @endif

                            @if($roomStay->status === 'checked_in')
                            <button type="button" onclick="showLateCheckoutModal()" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-sm">
                                Request Late Checkout
                            </button>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Early Check-in Modal -->
    <div id="earlyCheckinModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Request Early Check-in</h3>

                <form method="POST" action="{{ route('frontoffice.early-checkin.request', $roomStay) }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Current Check-in Time
                        </label>
                        <div class="text-gray-600 dark:text-gray-400">{{ $roomStay->check_in_date->format('d M Y H:i') }}</div>
                    </div>

                    <div class="mb-4">
                        <label for="early_checkin_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Requested Check-in Time <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="requested_time" id="early_checkin_time" required
                            max="{{ $roomStay->check_in_date->format('Y-m-d\TH:i') }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                    </div>

                    <div class="mb-4">
                        <label for="early_checkin_charge" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Additional Charge <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                            <input type="number" name="additional_charge" id="early_checkin_charge" required
                                value="50000" min="0" step="10000"
                                class="w-full pl-12 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Default: Rp 50,000</p>
                    </div>

                    <div class="mb-4">
                        <label for="early_checkin_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Notes
                        </label>
                        <textarea name="notes" id="early_checkin_notes" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
                            placeholder="Reason for early check-in..."></textarea>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                            Approve Request
                        </button>
                        <button type="button" onclick="hideEarlyCheckinModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Late Checkout Modal -->
    <div id="lateCheckoutModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Request Late Checkout</h3>

                <form method="POST" action="{{ route('frontoffice.late-checkout.request', $roomStay) }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Current Checkout Time
                        </label>
                        <div class="text-gray-600 dark:text-gray-400">{{ $roomStay->check_out_date->format('d M Y H:i') }}</div>
                    </div>

                    <div class="mb-4">
                        <label for="late_checkout_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Requested Checkout Time <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="requested_time" id="late_checkout_time" required
                            min="{{ $roomStay->check_out_date->format('Y-m-d\TH:i') }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                    </div>

                    <div class="mb-4">
                        <label for="late_checkout_charge" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Additional Charge <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                            <input type="number" name="additional_charge" id="late_checkout_charge" required
                                value="100000" min="0" step="10000"
                                class="w-full pl-12 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Default: Rp 100,000</p>
                    </div>

                    <div class="mb-4">
                        <label for="late_checkout_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Notes
                        </label>
                        <textarea name="notes" id="late_checkout_notes" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
                            placeholder="Reason for late checkout..."></textarea>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                            Approve Request
                        </button>
                        <button type="button" onclick="hideLateCheckoutModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 🔧 Custom Check-In Confirmation Modal --}}
    @if($roomStay->status === 'reserved')
    <div id="checkinConfirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 dark:bg-green-900 rounded-full mb-4">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 text-center mb-2">
                    Konfirmasi Check-In
                </h3>
                <p class="text-gray-600 dark:text-gray-400 text-center mb-6">
                    Apakah Anda yakin ingin melakukan check-in untuk tamu <strong class="text-gray-900 dark:text-gray-100">{{ $roomStay->guest->full_name }}</strong>?
                </p>

                {{-- Reservation Details --}}
                <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-3 mb-3">
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <div class="flex justify-between mb-1">
                            <span class="font-medium">Kamar:</span>
                            <span>{{ $roomStay->hotelRoom->room_number }}</span>
                        </div>
                        <div class="flex justify-between mb-1">
                            <span class="font-medium">Durasi:</span>
                            <span>{{ $roomStay->nights }} malam</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Konfirmasi:</span>
                            <span>{{ $roomStay->confirmation_number }}</span>
                        </div>
                    </div>
                </div>

                {{-- 💰 Payment Status Reminder --}}
                @php
                    $totalCharge = $roomStay->total_room_charge + $roomStay->tax_amount + $roomStay->service_charge;
                    $paidAmount = $roomStay->paid_amount ?? 0;
                    $balance = $totalCharge - $paidAmount;
                    $depositAmount = $roomStay->deposit_amount ?? 0;
                @endphp

                @if($balance > 0)
                <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 dark:border-yellow-600 p-3 mb-3">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-300 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">
                                ⚠️ Pembayaran Belum Lunas
                            </p>
                            <div class="mt-2 text-xs text-yellow-700 dark:text-yellow-300 space-y-1">
                                <div class="flex justify-between">
                                    <span>Total Tagihan:</span>
                                    <span class="font-semibold">Rp {{ number_format($totalCharge, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Sudah Dibayar:</span>
                                    <span class="font-semibold">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between border-t border-yellow-300 dark:border-yellow-600 pt-1 mt-1">
                                    <span class="font-bold">Sisa Harus Dibayar:</span>
                                    <span class="font-bold text-red-600 dark:text-red-400">Rp {{ number_format($balance, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-yellow-700 dark:text-yellow-300 italic">
                                💡 Pastikan tamu sudah membayar atau konfirmasi metode pembayaran sebelum check-in.
                            </p>
                        </div>
                    </div>
                </div>
                @else
                <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-3 mb-3">
                    <div class="flex items-center text-sm text-green-800 dark:text-green-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-semibold">✅ Pembayaran Lunas</span>
                    </div>
                </div>
                @endif
                <div class="flex gap-3">
                    <button type="button" onclick="hideCheckinModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold px-4 py-2 rounded-lg transition">
                        Batal
                    </button>
                    <button type="button" onclick="submitCheckin()" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg transition">
                        Ya, Check-In
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- 💰 Add Payment Modal --}}
    @if($roomStay->status === 'reserved' || $roomStay->status === 'checked_in')
    <div id="addPaymentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full">
            <form action="{{ route('frontoffice.folio.add-payment', $roomStay) }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            Tambah Pembayaran
                        </h3>
                        <button type="button" onclick="hideAddPaymentModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Jumlah Pembayaran (Rp) *
                            </label>
                            <input type="number" name="amount" id="payment_amount" required min="1000" step="1000"
                                value="{{ old('amount') }}"
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 @error('amount') border-red-500 @enderror"
                                placeholder="Masukkan jumlah">
                            @error('amount')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Metode Pembayaran *
                            </label>
                            <select name="payment_method" required
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 @error('payment_method') border-red-500 @enderror">
                                <option value="">-- Pilih Metode --</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="debit_card" {{ old('payment_method') == 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Catatan (Opsional)
                            </label>
                            <textarea name="notes" rows="2"
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 @error('notes') border-red-500 @enderror"
                                placeholder="Catatan pembayaran...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        @php
                            $totalCharge = $roomStay->total_room_charge + $roomStay->tax_amount + $roomStay->service_charge;
                            $paidAmount = $roomStay->paid_amount ?? 0;
                            $balance = $totalCharge - $paidAmount;
                        @endphp

                        @if($balance > 0)
                        <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-3">
                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                <div class="flex justify-between mb-1">
                                    <span>Total Tagihan:</span>
                                    <span class="font-semibold">Rp {{ number_format($totalCharge, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between mb-1">
                                    <span>Sudah Dibayar:</span>
                                    <span class="font-semibold">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between border-t border-blue-300 dark:border-blue-600 pt-1 mt-1">
                                    <span class="font-bold">Sisa:</span>
                                    <span class="font-bold text-red-600 dark:text-red-400">Rp {{ number_format($balance, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="hideAddPaymentModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold px-4 py-2 rounded-lg transition">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg transition">
                            Simpan Pembayaran
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ✏️ Edit Payment Modal --}}
    @if($roomStay->status === 'reserved' || $roomStay->status === 'checked_in')
    <div id="editPaymentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full">
            <form id="editPaymentForm" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            Edit Pembayaran
                        </h3>
                        <button type="button" onclick="hideEditPaymentModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Jumlah Pembayaran (Rp) *
                            </label>
                            <input type="number" name="amount" id="edit_payment_amount" required min="1000" step="1000"
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan jumlah">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Metode Pembayaran *
                            </label>
                            <select name="payment_method" id="edit_payment_method" required
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Pilih Metode --</option>
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Catatan (Opsional)
                            </label>
                            <textarea name="notes" id="edit_payment_notes" rows="2"
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Catatan pembayaran..."></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="hideEditPaymentModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold px-4 py-2 rounded-lg transition">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition">
                            Update Pembayaran
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- 🗑️ Delete Payment Confirmation Modal --}}
    @if($roomStay->status === 'reserved' || $roomStay->status === 'checked_in')
    <div id="deletePaymentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
            <form id="deletePaymentForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900 rounded-full mb-4">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 text-center mb-2">
                        Hapus Pembayaran?
                    </h3>

                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-4">
                        Anda akan menghapus pembayaran sebesar <span id="delete_payment_amount_display" class="font-semibold text-red-600 dark:text-red-400"></span>
                    </p>

                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-3 mb-4">
                        <p class="text-xs text-yellow-800 dark:text-yellow-400">
                            ⚠️ Tindakan ini akan mengurangi total pembayaran yang tercatat dan tidak dapat dibatalkan.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="hideDeletePaymentModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold px-4 py-2 rounded-lg transition">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg transition">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ⚠️ Auto Warning Popup for No Payment --}}
    @if($roomStay->status === 'reserved' && ($roomStay->paid_amount ?? 0) == 0)
    <div id="noPaymentWarning" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 dark:bg-red-900 rounded-full mb-4">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 text-center mb-2">
                    ⚠️ Belum Ada Pembayaran!
                </h3>
                <p class="text-gray-600 dark:text-gray-400 text-center mb-4">
                    Reservasi untuk <strong class="text-gray-900 dark:text-gray-100">{{ $roomStay->guest->full_name }}</strong> belum memiliki record pembayaran.
                </p>
                <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 mb-4">
                    <div class="text-sm text-yellow-800 dark:text-yellow-200">
                        <p class="font-semibold mb-2">📋 Detail Reservasi:</p>
                        <div class="space-y-1">
                            <div class="flex justify-between">
                                <span>Kamar:</span>
                                <span class="font-medium">{{ $roomStay->hotelRoom->room_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Check-in:</span>
                                <span class="font-medium">{{ $roomStay->check_in_date->format('d M Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Durasi:</span>
                                <span class="font-medium">{{ $roomStay->nights }} malam</span>
                            </div>
                            <div class="flex justify-between border-t border-yellow-300 dark:border-yellow-600 pt-1 mt-1">
                                <span class="font-bold">Total:</span>
                                <span class="font-bold text-red-600 dark:text-red-400">
                                    Rp {{ number_format($roomStay->total_room_charge + $roomStay->tax_amount + $roomStay->service_charge, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-6 italic">
                    💡 Pastikan tamu melakukan pembayaran sebelum check-in. Klik tombol di bawah untuk menambah pembayaran.
                </p>
                <div class="flex gap-3">
                    <button type="button" onclick="hideNoPaymentWarning()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold px-4 py-2 rounded-lg transition">
                        Tutup
                    </button>
                    <button type="button" onclick="hideNoPaymentWarning(); showAddPaymentModal();" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg transition">
                        Tambah Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
    function showEarlyCheckinModal() {
        document.getElementById('earlyCheckinModal').classList.remove('hidden');
    }

    function hideEarlyCheckinModal() {
        document.getElementById('earlyCheckinModal').classList.add('hidden');
    }

    function showLateCheckoutModal() {
        document.getElementById('lateCheckoutModal').classList.remove('hidden');
    }

    function hideLateCheckoutModal() {
        document.getElementById('lateCheckoutModal').classList.add('hidden');
    }

    // 🔧 Check-in confirmation modal functions
    function showCheckinModal() {
        document.getElementById('checkinConfirmModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideCheckinModal() {
        document.getElementById('checkinConfirmModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function submitCheckin() {
        document.getElementById('checkin-form').submit();
    }

    // 💰 Add Payment modal functions
    function showAddPaymentModal() {
        document.getElementById('addPaymentModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideAddPaymentModal() {
        document.getElementById('addPaymentModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // ✏️ Edit Payment modal functions
    function showEditPaymentModal(paymentId, method, amount, notes) {
        const form = document.getElementById('editPaymentForm');
        form.action = '{{ url("frontoffice/folio/" . $roomStay->id . "/payment") }}/' + paymentId;

        document.getElementById('edit_payment_amount').value = amount;
        document.getElementById('edit_payment_method').value = method;
        document.getElementById('edit_payment_notes').value = notes || '';

        document.getElementById('editPaymentModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideEditPaymentModal() {
        document.getElementById('editPaymentModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // 🗑️ Delete Payment modal functions
    function confirmDeletePayment(paymentId, amount) {
        const form = document.getElementById('deletePaymentForm');
        form.action = '{{ url("frontoffice/folio/" . $roomStay->id . "/payment") }}/' + paymentId;

        document.getElementById('delete_payment_amount_display').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);

        document.getElementById('deletePaymentModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideDeletePaymentModal() {
        document.getElementById('deletePaymentModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // ⚠️ No Payment Warning modal functions
    function showNoPaymentWarning() {
        const warningModal = document.getElementById('noPaymentWarning');
        if (warningModal) {
            warningModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function hideNoPaymentWarning() {
        const warningModal = document.getElementById('noPaymentWarning');
        if (warningModal) {
            warningModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    // Auto-show warning popup on page load if no payment
    document.addEventListener('DOMContentLoaded', function() {
        // Check if there are validation errors for payment form
        @if($errors->has('amount') || $errors->has('payment_method') || $errors->has('notes'))
            // If there are validation errors, show the payment modal instead of warning
            showAddPaymentModal();
        @else
            // Show warning after 500ms delay for better UX
            setTimeout(function() {
                showNoPaymentWarning();
            }, 500);
        @endif
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const earlyModal = document.getElementById('earlyCheckinModal');
        const lateModal = document.getElementById('lateCheckoutModal');
        const checkinModal = document.getElementById('checkinConfirmModal');
        const addPaymentModal = document.getElementById('addPaymentModal');
        const editPaymentModal = document.getElementById('editPaymentModal');
        const deletePaymentModal = document.getElementById('deletePaymentModal');
        const noPaymentWarning = document.getElementById('noPaymentWarning');

        if (event.target == earlyModal) {
            hideEarlyCheckinModal();
        }
        if (event.target == lateModal) {
            hideLateCheckoutModal();
        }
        if (event.target == checkinModal) {
            hideCheckinModal();
        }
        if (event.target == addPaymentModal) {
            hideAddPaymentModal();
        }
        if (event.target == editPaymentModal) {
            hideEditPaymentModal();
        }
        if (event.target == deletePaymentModal) {
            hideDeletePaymentModal();
        }
        if (event.target == noPaymentWarning) {
            hideNoPaymentWarning();
        }
    }
    </script>
</div>
</x-app-layout>

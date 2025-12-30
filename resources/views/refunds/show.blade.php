<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Refund Details</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $property->name }}</p>
        </div>
        <a href="{{ route('refunds.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            Back to List
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Refund Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Refund Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Refund Information</h2>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-{{ $refund->status_color }}-100 dark:bg-{{ $refund->status_color }}-900 text-{{ $refund->status_color }}-800 dark:text-{{ $refund->status_color }}-200">
                        {{ $refund->status_label }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Refund Number</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $refund->refund_number }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Amount</label>
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                            Rp {{ number_format($refund->amount, 0, ',', '.') }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Refund Method</label>
                        <p class="text-lg text-gray-900 dark:text-white">{{ $refund->refund_method_label }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Created Date</label>
                        <p class="text-lg text-gray-900 dark:text-white">{{ $refund->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                @if($refund->reason)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Reason</label>
                    <p class="text-gray-900 dark:text-white">{{ $refund->reason }}</p>
                </div>
                @endif

                @if($refund->notes)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Notes</label>
                    <p class="text-gray-900 dark:text-white whitespace-pre-line">{{ $refund->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Bank Transfer Details (if applicable) -->
            @if($refund->refund_method === 'bank_transfer' && ($refund->bank_name || $refund->account_number))
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                <h3 class="text-lg font-bold text-blue-900 dark:text-blue-300 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    Bank Transfer Details
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($refund->bank_name)
                    <div>
                        <label class="block text-sm font-medium text-blue-800 dark:text-blue-300">Bank Name</label>
                        <p class="text-lg font-semibold text-blue-900 dark:text-blue-200">{{ $refund->bank_name }}</p>
                    </div>
                    @endif

                    @if($refund->account_number)
                    <div>
                        <label class="block text-sm font-medium text-blue-800 dark:text-blue-300">Account Number</label>
                        <p class="text-lg font-semibold text-blue-900 dark:text-blue-200">{{ $refund->account_number }}</p>
                    </div>
                    @endif

                    @if($refund->account_holder_name)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-blue-800 dark:text-blue-300">Account Holder Name</label>
                        <p class="text-lg font-semibold text-blue-900 dark:text-blue-200">{{ $refund->account_holder_name }}</p>
                    </div>
                    @endif

                    @if($refund->reference_number)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-blue-800 dark:text-blue-300">Reference Number</label>
                        <p class="text-lg font-semibold text-blue-900 dark:text-blue-200">{{ $refund->reference_number }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Guest & Room Stay Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Guest & Stay Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Guest Name</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $refund->roomStay->guest->full_name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Room Number</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            Room {{ $refund->roomStay->hotelRoom->room_number ?? 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Confirmation Number</label>
                        <p class="text-lg text-gray-900 dark:text-white">{{ $refund->roomStay->confirmation_number }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Check-out Date</label>
                        <p class="text-lg text-gray-900 dark:text-white">
                            {{ $refund->roomStay->actual_check_out ? $refund->roomStay->actual_check_out->format('d M Y, H:i') : '-' }}
                        </p>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-800 dark:text-white mb-3">Payment Summary</h3>

                    @php
                        $totalBill = $refund->roomStay->total_room_charge
                            + $refund->roomStay->fnbOrders->sum('total_amount')
                            + $refund->roomStay->tax_amount
                            + $refund->roomStay->service_charge;
                        $totalDeposit = $refund->roomStay->payments->where('notes', 'like', '%Deposit%')->sum('amount');
                    @endphp

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Total Bill:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($totalBill, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Total Deposit Paid:</span>
                            <span class="font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($totalDeposit, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                            <span class="font-semibold text-gray-800 dark:text-white">Refund Amount:</span>
                            <span class="font-bold text-yellow-600 dark:text-yellow-400">Rp {{ number_format($refund->amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Processing Information -->
            @if($refund->status === 'processed' && $refund->processedBy)
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6">
                <h3 class="text-lg font-bold text-green-900 dark:text-green-300 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Processing Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-green-800 dark:text-green-300">Processed By</label>
                        <p class="text-lg font-semibold text-green-900 dark:text-green-200">{{ $refund->processedBy->name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-green-800 dark:text-green-300">Processed Date</label>
                        <p class="text-lg font-semibold text-green-900 dark:text-green-200">
                            {{ $refund->processed_at->format('d M Y, H:i') }}
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar Actions -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            @if($refund->status === 'pending')
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Actions</h3>

                <!-- Mark as Processed -->
                <form method="POST" action="{{ route('refunds.process', $refund) }}" class="mb-3" onsubmit="return confirm('Are you sure you want to mark this refund as processed? This means the refund has been given to the guest.');">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Processing Notes (Optional)</label>
                        <textarea name="notes" rows="3" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg" placeholder="e.g., Refunded via cash, Transfer completed..."></textarea>
                    </div>
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-semibold flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Mark as Processed
                    </button>
                </form>

                <!-- Cancel Refund -->
                <button onclick="showCancelModal()" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-semibold flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    Cancel Refund
                </button>
            </div>
            @endif

            <!-- Refund Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Timeline</h3>

                <div class="space-y-4">
                    <!-- Created -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Refund Created</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $refund->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>

                    @if($refund->status === 'processed')
                    <!-- Processed -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Refund Processed</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $refund->processed_at->format('d M Y, H:i') }}</p>
                            @if($refund->processedBy)
                            <p class="text-xs text-gray-500 dark:text-gray-400">by {{ $refund->processedBy->name }}</p>
                            @endif
                        </div>
                    </div>
                    @elseif($refund->status === 'cancelled')
                    <!-- Cancelled -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Refund Cancelled</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $refund->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    @else
                    <!-- Pending -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Awaiting Processing</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pending action</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Refund Modal -->
<div id="cancelModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white dark:bg-gray-800">
        <div class="mt-3">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Cancel Refund</h3>

            <form method="POST" action="{{ route('refunds.cancel', $refund) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Cancellation Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" required rows="4" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg" placeholder="Please provide a reason for cancelling this refund..."></textarea>
                </div>

                <div class="flex space-x-3">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">
                        Confirm Cancel
                    </button>
                    <button type="button" onclick="hideCancelModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold">
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCancelModal() {
    document.getElementById('cancelModal').classList.remove('hidden');
}

function hideCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('cancelModal');
    if (event.target === modal) {
        hideCancelModal();
    }
}
</script>
</x-app-layout>

<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Mark as No-Show</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $property->name }}</p>
        </div>
        <a href="{{ route('frontoffice.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            Kembali
        </a>
    </div>

    @if($errors->any())
    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded">
        <ul class="list-disc list-inside text-red-700 dark:text-red-400">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid md:grid-cols-3 gap-6">
        <!-- Reservation Information -->
        <div class="md:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Reservation Details</h2>

                <div class="space-y-3 text-sm">
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Confirmation Number</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->confirmation_number }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Guest Name</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->guest->full_name }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Phone</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->guest->phone_number ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Email</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->guest->email ?? '-' }}</div>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                        <div class="text-gray-600 dark:text-gray-400">Room Number</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->hotelRoom->room_number }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Room Type</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->hotelRoom->roomType->name ?? 'Standard' }}</div>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                        <div class="text-gray-600 dark:text-gray-400">Check-in Date</div>
                        <div class="font-semibold text-red-600">{{ $roomStay->check_in_date->format('d M Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Check-out Date</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->check_out_date->format('d M Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Nights</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $roomStay->check_in_date->diffInDays($roomStay->check_out_date) }} nights</div>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                        <div class="text-gray-600 dark:text-gray-400">Total Amount</div>
                        <div class="font-semibold text-gray-800 dark:text-white">Rp {{ number_format($roomStay->total_room_charge, 0, ',', '.') }}</div>
                    </div>
                    @if($roomStay->reservation && $roomStay->reservation->deposit_amount > 0)
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Deposit Paid</div>
                        <div class="font-semibold text-green-600">Rp {{ number_format($roomStay->reservation->deposit_amount, 0, ',', '.') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- No-Show Form -->
        <div class="md:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">No-Show Processing</h2>

                <!-- Warning Alert -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-4 rounded mb-6">
                    <div class="flex">
                        <svg class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-yellow-800 dark:text-yellow-400">Perhatian!</h3>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                Tamu tidak datang untuk check-in pada tanggal yang dijadwalkan. Proses ini akan:
                            </p>
                            <ul class="text-sm text-yellow-700 dark:text-yellow-300 mt-2 ml-4 list-disc">
                                <li>Mengubah status reservasi menjadi "No-Show"</li>
                                <li>Membebaskan kamar untuk tamu lain</li>
                                <li>Menerapkan cancellation fee sesuai kebijakan</li>
                                <li>Mencatat aktivitas di log sistem</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('frontoffice.no-show.process', $roomStay) }}">
                    @csrf

                    <div class="mb-4">
                        <label for="cancellation_fee" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Cancellation Fee <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                            <input type="number" name="cancellation_fee" id="cancellation_fee" required
                                value="{{ old('cancellation_fee', $roomStay->total_room_charge * 0.5) }}"
                                min="0" step="1000"
                                class="w-full pl-12 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Default: 50% dari total room charge (Rp {{ number_format($roomStay->total_room_charge * 0.5, 0, ',', '.') }})</p>
                    </div>

                    <div class="mb-4">
                        <label for="charge_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Charge Method <span class="text-red-500">*</span>
                        </label>
                        <select name="charge_method" id="charge_method" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                            <option value="">-- Select Method --</option>
                            @if($roomStay->reservation && $roomStay->reservation->deposit_amount > 0)
                            <option value="deposit" selected>Deduct from Deposit (Rp {{ number_format($roomStay->reservation->deposit_amount, 0, ',', '.') }} available)</option>
                            @endif
                            <option value="charge_card">Charge to Guest's Card (Will be charged)</option>
                            <option value="waive">Waive Fee (No charge)</option>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Notes
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                            placeholder="Additional notes about the no-show..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Summary -->
                    <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded mb-6">
                        <h3 class="font-semibold text-red-800 dark:text-red-400 mb-2">Action Summary:</h3>
                        <div class="text-sm text-red-700 dark:text-red-300 space-y-1">
                            <p>✗ Reservation will be marked as NO-SHOW</p>
                            <p>✓ Room {{ $roomStay->hotelRoom->room_number }} will be released and available</p>
                            <p>💰 Cancellation fee will be processed based on selected method</p>
                            <p>📋 Activity will be logged for audit trail</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold">
                            Confirm No-Show
                        </button>
                        <a href="{{ route('frontoffice.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

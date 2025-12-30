<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Extend Stay</h1>
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
        <!-- Current Stay Information -->
        <div class="md:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Current Stay Information</h2>

                <div class="space-y-3">
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Guest Name</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $stay->guest->full_name }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Room Number</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $stay->hotelRoom->room_number }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Check-in Date</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $stay->check_in_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Current Check-out Date</div>
                        <div class="font-semibold text-red-600">{{ $stay->check_out_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Nights Stayed</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $stay->check_in_date->diffInDays($stay->check_out_date) }} nights</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Room Rate</div>
                        <div class="font-semibold text-gray-800 dark:text-white">Rp {{ number_format($stay->room_rate, 0, ',', '.') }}/night</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Extend Stay Form -->
        <div class="md:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Extend Stay Period</h2>

                <form method="POST" action="{{ route('frontoffice.extend-stay', $stay) }}">
                    @csrf

                    <div class="mb-4">
                        <label for="new_check_out_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            New Check-out Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="new_check_out_date" id="new_check_out_date" required
                            min="{{ $stay->check_out_date->addDay()->toDateString() }}"
                            value="{{ old('new_check_out_date') }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                        <p class="mt-1 text-xs text-gray-500">Must be after current check-out date ({{ $stay->check_out_date->format('d M Y') }})</p>
                    </div>

                    <div class="mb-4">
                        <label for="new_room_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Room Rate for Extended Period
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                            <input type="number" name="new_room_rate" id="new_room_rate"
                                value="{{ old('new_room_rate', $stay->room_rate) }}"
                                min="0" step="1000"
                                class="w-full pl-12 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Leave as is to use current room rate</p>
                    </div>

                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Notes
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                            placeholder="Additional notes for extension..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Summary -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded mb-6">
                        <h3 class="font-semibold text-blue-800 dark:text-blue-400 mb-2">Extension Summary:</h3>
                        <div class="text-sm text-blue-700 dark:text-blue-300 space-y-1" id="extension_summary">
                            <p>Select new check-out date to see extension details</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                            Confirm Extension
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

<script>
document.getElementById('new_check_out_date')?.addEventListener('change', updateSummary);
document.getElementById('new_room_rate')?.addEventListener('input', updateSummary);

function updateSummary() {
    const currentCheckOut = new Date('{{ $stay->check_out_date->toDateString() }}');
    const newCheckOutInput = document.getElementById('new_check_out_date').value;
    const newRoomRate = parseInt(document.getElementById('new_room_rate').value) || {{ $stay->room_rate }};

    if (!newCheckOutInput) {
        return;
    }

    const newCheckOut = new Date(newCheckOutInput);
    const additionalNights = Math.ceil((newCheckOut - currentCheckOut) / (1000 * 60 * 60 * 24));

    if (additionalNights <= 0) {
        document.getElementById('extension_summary').innerHTML = '<p class="text-red-600">New check-out date must be after current check-out date!</p>';
        return;
    }

    const additionalAmount = additionalNights * newRoomRate;

    document.getElementById('extension_summary').innerHTML = `
        <p><strong>Additional Nights:</strong> ${additionalNights} night${additionalNights > 1 ? 's' : ''}</p>
        <p><strong>Rate per Night:</strong> Rp ${formatNumber(newRoomRate)}</p>
        <p><strong>Additional Charges:</strong> Rp ${formatNumber(additionalAmount)}</p>
        <p class="mt-2 pt-2 border-t border-blue-300"><strong>New Check-out Date:</strong> ${new Date(newCheckOutInput).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</p>
    `;
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
</script>
</x-app-layout>

<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Change Room</h1>
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
        <!-- Current Room Information -->
        <div class="md:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Current Stay</h2>

                <div class="space-y-3">
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Guest Name</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $stay->guest->full_name }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Current Room</div>
                        <div class="font-semibold text-red-600">{{ $stay->hotelRoom->room_number }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Room Type</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $stay->hotelRoom->roomType->name ?? 'Standard' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Check-in Date</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $stay->check_in_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Check-out Date</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $stay->check_out_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Remaining Nights</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ today()->diffInDays($stay->check_out_date) }} nights</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Current Room Rate</div>
                        <div class="font-semibold text-gray-800 dark:text-white">Rp {{ number_format($stay->room_rate, 0, ',', '.') }}/night</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Room Form -->
        <div class="md:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Select New Room</h2>

                <form method="POST" action="{{ route('frontoffice.change-room', $stay) }}">
                    @csrf

                    <div class="mb-4">
                        <label for="new_room_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            New Room <span class="text-red-500">*</span>
                        </label>
                        <select name="new_room_id" id="new_room_id" required onchange="updateRoomInfo()"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                            <option value="">-- Select Available Room --</option>
                            @foreach($availableRooms as $room)
                            <option value="{{ $room->id }}"
                                data-room-number="{{ $room->room_number }}"
                                data-room-type="{{ $room->roomType->name ?? 'Standard' }}"
                                data-base-rate="{{ $room->base_price }}"
                                {{ old('new_room_id') == $room->id ? 'selected' : '' }}>
                                Room {{ $room->room_number }} - {{ $room->roomType->name ?? 'Standard' }} (Rp {{ number_format($room->base_price, 0, ',', '.') }}/night)
                            </option>
                            @endforeach
                        </select>
                        @if($availableRooms->count() == 0)
                        <p class="mt-1 text-xs text-red-500">No available rooms at the moment</p>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label for="new_room_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            New Room Rate
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                            <input type="number" name="new_room_rate" id="new_room_rate"
                                value="{{ old('new_room_rate', $stay->room_rate) }}"
                                min="0" step="1000"
                                class="w-full pl-12 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Rate will be applied for remaining nights</p>
                    </div>

                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Reason for Change <span class="text-red-500">*</span>
                        </label>
                        <select name="reason" id="reason" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                            <option value="">-- Select Reason --</option>
                            <option value="guest_request" {{ old('reason') == 'guest_request' ? 'selected' : '' }}>Guest Request</option>
                            <option value="room_issue" {{ old('reason') == 'room_issue' ? 'selected' : '' }}>Room Issue/Maintenance</option>
                            <option value="upgrade" {{ old('reason') == 'upgrade' ? 'selected' : '' }}>Room Upgrade</option>
                            <option value="downgrade" {{ old('reason') == 'downgrade' ? 'selected' : '' }}>Room Downgrade</option>
                            <option value="other" {{ old('reason') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Notes
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                            placeholder="Additional notes about room change..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Room Change Summary -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded mb-6" id="change_summary" style="display: none;">
                        <h3 class="font-semibold text-blue-800 dark:text-blue-400 mb-2">Room Change Summary:</h3>
                        <div class="text-sm text-blue-700 dark:text-blue-300 space-y-1" id="summary_content">
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold" {{ $availableRooms->count() == 0 ? 'disabled' : '' }}>
                            Confirm Room Change
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
const currentRoomNumber = '{{ $stay->hotelRoom->room_number }}';
const currentRoomType = '{{ $stay->hotelRoom->roomType->name ?? "Standard" }}';
const currentRate = {{ $stay->room_rate }};
const remainingNights = {{ today()->diffInDays($stay->check_out_date) }};

function updateRoomInfo() {
    const select = document.getElementById('new_room_id');
    const option = select.options[select.selectedIndex];

    if (!option.value) {
        document.getElementById('change_summary').style.display = 'none';
        return;
    }

    const newRoomNumber = option.dataset.roomNumber;
    const newRoomType = option.dataset.roomType;
    const baseRate = parseFloat(option.dataset.baseRate);
    const newRate = parseFloat(document.getElementById('new_room_rate').value) || baseRate;

    // Update room rate input with base rate
    document.getElementById('new_room_rate').value = baseRate;

    const currentTotalRemaining = currentRate * remainingNights;
    const newTotalRemaining = newRate * remainingNights;
    const difference = newTotalRemaining - currentTotalRemaining;

    const summaryContent = `
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p><strong>From:</strong> Room ${currentRoomNumber} (${currentRoomType})</p>
                <p class="text-xs">Rate: Rp ${formatNumber(currentRate)}/night</p>
            </div>
            <div>
                <p><strong>To:</strong> Room ${newRoomNumber} (${newRoomType})</p>
                <p class="text-xs">Rate: Rp ${formatNumber(newRate)}/night</p>
            </div>
        </div>
        <div class="mt-3 pt-3 border-t border-blue-300">
            <p><strong>Remaining Nights:</strong> ${remainingNights} night${remainingNights > 1 ? 's' : ''}</p>
            <p><strong>Current Remaining Total:</strong> Rp ${formatNumber(currentTotalRemaining)}</p>
            <p><strong>New Remaining Total:</strong> Rp ${formatNumber(newTotalRemaining)}</p>
            <p class="mt-2 ${difference >= 0 ? 'text-red-600' : 'text-green-600'}">
                <strong>Difference:</strong> ${difference >= 0 ? '+' : ''}Rp ${formatNumber(difference)}
            </p>
        </div>
    `;

    document.getElementById('summary_content').innerHTML = summaryContent;
    document.getElementById('change_summary').style.display = 'block';
}

document.getElementById('new_room_rate')?.addEventListener('input', updateRoomInfo);

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
</script>
</x-app-layout>

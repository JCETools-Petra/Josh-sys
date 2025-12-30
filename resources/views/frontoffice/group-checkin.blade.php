<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Group Check-In</h1>
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

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form method="POST" action="{{ route('frontoffice.group-checkin') }}" id="groupCheckinForm">
            @csrf

            <!-- Group Information -->
            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Group Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="group_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Group Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="group_name" id="group_name" required value="{{ old('group_name') }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
                            placeholder="e.g., ABC Company Tour">
                    </div>

                    <div>
                        <label for="check_in_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Check-in Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="check_in_date" id="check_in_date" required
                            value="{{ old('check_in_date', today()->toDateString()) }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="check_out_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Check-out Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="check_out_date" id="check_out_date" required
                            value="{{ old('check_out_date', today()->addDays(1)->toDateString()) }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Guests Section -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Guests & Room Assignment</h2>
                    <button type="button" onclick="addGuest()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                        + Add Guest
                    </button>
                </div>

                <div id="guests_container" class="space-y-4">
                    <!-- Guest entries will be added here -->
                </div>
            </div>

            <!-- Payment Information -->
            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Payment Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Payment Method
                        </label>
                        <select name="payment_method" id="payment_method"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="deposit">Deposit/Prepaid</option>
                        </select>
                    </div>

                    <div>
                        <label for="special_requests" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Special Requests
                        </label>
                        <textarea name="special_requests" id="special_requests" rows="2"
                            placeholder="e.g., Early check-in, connecting rooms, etc."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">{{ old('special_requests') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded mb-6">
                <h3 class="font-semibold text-blue-800 dark:text-blue-400 mb-2">Group Check-in Summary:</h3>
                <div class="text-sm text-blue-700 dark:text-blue-300" id="summary">
                    <p>Add guests to see summary</p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-3">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                    Check-in Group
                </button>
                <a href="{{ route('frontoffice.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
let guestCount = 0;
const availableRooms = @json($availableRooms);

function addGuest() {
    guestCount++;
    const container = document.getElementById('guests_container');

    const guestHtml = `
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg" id="guest_${guestCount}">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-gray-800 dark:text-white">Guest #${guestCount}</h3>
                <button type="button" onclick="removeGuest(${guestCount})" class="text-red-600 hover:text-red-800 text-sm">
                    Remove
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="guests[${guestCount}][full_name]" required
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-600 dark:text-white"
                        placeholder="Guest Full Name">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Email
                    </label>
                    <input type="email" name="guests[${guestCount}][email]"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-600 dark:text-white"
                        placeholder="email@example.com">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Phone
                    </label>
                    <input type="text" name="guests[${guestCount}][phone_number]"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-600 dark:text-white"
                        placeholder="08xx-xxxx-xxxx">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Room <span class="text-red-500">*</span>
                    </label>
                    <select name="guests[${guestCount}][room_id]" required onchange="updateSummary()"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-600 dark:text-white">
                        <option value="">Select Room</option>
                        ${availableRooms.map(room => `
                            <option value="${room.id}" data-rate="${room.base_price}">
                                ${room.room_number} - Rp ${formatNumber(room.base_price)}
                            </option>
                        `).join('')}
                    </select>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', guestHtml);
    updateSummary();
}

function removeGuest(id) {
    document.getElementById(`guest_${id}`).remove();
    updateSummary();
}

function updateSummary() {
    const guestDivs = document.querySelectorAll('#guests_container > div');
    const totalGuests = guestDivs.length;

    if (totalGuests === 0) {
        document.getElementById('summary').innerHTML = '<p>Add guests to see summary</p>';
        return;
    }

    const checkInDate = new Date(document.getElementById('check_in_date').value);
    const checkOutDate = new Date(document.getElementById('check_out_date').value);
    const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));

    let totalAmount = 0;
    let roomsAssigned = 0;

    guestDivs.forEach(div => {
        const select = div.querySelector('select[name*="[room_id]"]');
        if (select && select.value) {
            roomsAssigned++;
            const rate = parseFloat(select.options[select.selectedIndex].dataset.rate || 0);
            totalAmount += rate * nights;
        }
    });

    const summary = `
        <p><strong>Total Guests:</strong> ${totalGuests}</p>
        <p><strong>Rooms Assigned:</strong> ${roomsAssigned} of ${totalGuests}</p>
        <p><strong>Nights:</strong> ${nights}</p>
        <p><strong>Estimated Total:</strong> Rp ${formatNumber(totalAmount)}</p>
    `;

    document.getElementById('summary').innerHTML = summary;
}

document.getElementById('check_in_date')?.addEventListener('change', updateSummary);
document.getElementById('check_out_date')?.addEventListener('change', updateSummary);

// Add first guest automatically
window.addEventListener('DOMContentLoaded', () => {
    addGuest();
});

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
</script>
</x-app-layout>

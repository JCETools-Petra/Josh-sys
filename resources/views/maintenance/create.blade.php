<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">New Maintenance Request</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $property->name }}</p>
        </div>
        <a href="{{ route('maintenance.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            Back to List
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

    <form method="POST" action="{{ route('maintenance.store') }}">
        @csrf

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-6">
                <!-- Location -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Location</h2>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Room (Optional)
                        </label>
                        <select name="hotel_room_id" id="hotel_room_id" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                            <option value="">-- Select Room (if applicable) --</option>
                            @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ old('hotel_room_id') == $room->id ? 'selected' : '' }}>
                                Room {{ $room->room_number }} - {{ $room->roomType->name ?? 'Standard' }}
                            </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty if not room-specific</p>
                    </div>

                    <div id="location_field">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Location Description
                        </label>
                        <input type="text" name="location" id="location" value="{{ old('location') }}"
                            placeholder="e.g., Lobby, Pool Area, Restaurant..."
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Required if no room selected</p>
                    </div>
                </div>

                <!-- Issue Details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Issue Details</h2>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" required value="{{ old('title') }}"
                            placeholder="Brief description of the issue..."
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" required rows="4"
                            placeholder="Detailed description of the maintenance issue..."
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select name="category" required class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                            <option value="">-- Select Category --</option>
                            <option value="plumbing" {{ old('category') === 'plumbing' ? 'selected' : '' }}>Plumbing</option>
                            <option value="electrical" {{ old('category') === 'electrical' ? 'selected' : '' }}>Electrical</option>
                            <option value="hvac" {{ old('category') === 'hvac' ? 'selected' : '' }}>HVAC/AC</option>
                            <option value="furniture" {{ old('category') === 'furniture' ? 'selected' : '' }}>Furniture</option>
                            <option value="electronics" {{ old('category') === 'electronics' ? 'selected' : '' }}>Electronics (TV, Phone, etc)</option>
                            <option value="cleaning" {{ old('category') === 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                            <option value="painting" {{ old('category') === 'painting' ? 'selected' : '' }}>Painting</option>
                            <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Priority & Assignment -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Priority & Assignment</h2>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Priority <span class="text-red-500">*</span>
                        </label>
                        <select name="priority" required class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                            <option value="">-- Select Priority --</option>
                            <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>🔴 Urgent - Immediate attention required</option>
                            <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>🟠 High - Needs quick resolution</option>
                            <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }} selected>🟡 Medium - Standard timeline</option>
                            <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>🟢 Low - Can wait</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Assign To (Optional)
                        </label>
                        <select name="assigned_to" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                            <option value="">-- Assign Later --</option>
                            @foreach($staff as $person)
                            <option value="{{ $person->id }}" {{ old('assigned_to') == $person->id ? 'selected' : '' }}>
                                {{ $person->name }} ({{ ucfirst($person->role) }})
                            </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Auto-marks as "Acknowledged" if assigned</p>
                    </div>
                </div>

                <!-- Cost & Notes -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Additional Information</h2>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Estimated Cost (Rp)
                        </label>
                        <input type="number" name="estimated_cost" value="{{ old('estimated_cost') }}"
                            min="0" step="1000" placeholder="0"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional estimated repair cost</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Notes
                        </label>
                        <textarea name="notes" rows="3"
                            placeholder="Any additional notes or instructions..."
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Alert -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded">
                    <div class="flex">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-blue-800 dark:text-blue-400">Note</h3>
                            <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                If a room is selected, its status will be automatically changed to "Maintenance" to prevent check-ins.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                        Create Request
                    </button>
                    <a href="{{ route('maintenance.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Show/hide location field based on room selection
    const roomSelect = document.getElementById('hotel_room_id');
    const locationField = document.getElementById('location_field');
    const locationInput = document.getElementById('location');

    roomSelect.addEventListener('change', function() {
        if (this.value) {
            locationInput.required = false;
        } else {
            locationInput.required = true;
        }
    });
</script>
</x-app-layout>

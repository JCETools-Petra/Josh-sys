<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Maintenance Request Detail</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $maintenanceRequest->request_number }}</p>
        </div>
        <a href="{{ route('maintenance.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
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

    <div class="grid md:grid-cols-3 gap-6">
        <!-- Left Column - Request Details -->
        <div class="md:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Request Information</h2>

                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <div class="text-gray-600 dark:text-gray-400 text-sm">Request Number</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $maintenanceRequest->request_number }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400 text-sm">Status</div>
                        <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full bg-{{ $maintenanceRequest->status_color }}-100 dark:bg-{{ $maintenanceRequest->status_color }}-900 text-{{ $maintenanceRequest->status_color }}-800 dark:text-{{ $maintenanceRequest->status_color }}-200">
                            {{ $maintenanceRequest->status_label }}
                        </span>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400 text-sm">Priority</div>
                        <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full bg-{{ $maintenanceRequest->priority_color }}-100 dark:bg-{{ $maintenanceRequest->priority_color }}-900 text-{{ $maintenanceRequest->priority_color }}-800 dark:text-{{ $maintenanceRequest->priority_color }}-200">
                            {{ $maintenanceRequest->priority_label }}
                        </span>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400 text-sm">Category</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $maintenanceRequest->category_label }}</div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-4">
                    <div class="text-gray-600 dark:text-gray-400 text-sm mb-2">Title</div>
                    <div class="font-semibold text-gray-800 dark:text-white text-lg">{{ $maintenanceRequest->title }}</div>
                </div>

                <div class="mb-4">
                    <div class="text-gray-600 dark:text-gray-400 text-sm mb-2">Description</div>
                    <div class="text-gray-800 dark:text-white bg-gray-50 dark:bg-gray-700 p-4 rounded">{{ $maintenanceRequest->description }}</div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-gray-600 dark:text-gray-400 text-sm">Location</div>
                        <div class="font-semibold text-gray-800 dark:text-white">
                            @if($maintenanceRequest->hotelRoom)
                                Room {{ $maintenanceRequest->hotelRoom->room_number }}
                            @else
                                {{ $maintenanceRequest->location }}
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400 text-sm">Reported By</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $maintenanceRequest->reporter->name }}</div>
                    </div>
                </div>

                @if($maintenanceRequest->notes)
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                    <div class="text-gray-600 dark:text-gray-400 text-sm mb-2">Initial Notes</div>
                    <div class="text-gray-800 dark:text-white bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded">{{ $maintenanceRequest->notes }}</div>
                </div>
                @endif
            </div>

            <!-- Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Timeline</h2>

                <div class="space-y-4">
                    <!-- Reported -->
                    <div class="flex">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="font-semibold text-gray-800 dark:text-white">Reported</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $maintenanceRequest->reported_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>

                    @if($maintenanceRequest->acknowledged_at)
                    <div class="flex">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="font-semibold text-gray-800 dark:text-white">Acknowledged</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $maintenanceRequest->acknowledged_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                    @endif

                    @if($maintenanceRequest->started_at)
                    <div class="flex">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="font-semibold text-gray-800 dark:text-white">Started</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $maintenanceRequest->started_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                    @endif

                    @if($maintenanceRequest->completed_at)
                    <div class="flex">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="font-semibold text-gray-800 dark:text-white">Completed</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $maintenanceRequest->completed_at->format('d M Y, H:i') }}</div>
                            @if($maintenanceRequest->completion_notes)
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-2 bg-green-50 dark:bg-green-900/20 p-3 rounded">
                                {{ $maintenanceRequest->completion_notes }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Cost Info -->
            @if($maintenanceRequest->estimated_cost || $maintenanceRequest->actual_cost)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Cost Information</h2>

                <div class="grid md:grid-cols-2 gap-4">
                    @if($maintenanceRequest->estimated_cost)
                    <div>
                        <div class="text-gray-600 dark:text-gray-400 text-sm">Estimated Cost</div>
                        <div class="font-semibold text-gray-800 dark:text-white">Rp {{ number_format($maintenanceRequest->estimated_cost, 0, ',', '.') }}</div>
                    </div>
                    @endif

                    @if($maintenanceRequest->actual_cost)
                    <div>
                        <div class="text-gray-600 dark:text-gray-400 text-sm">Actual Cost</div>
                        <div class="font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($maintenanceRequest->actual_cost, 0, ',', '.') }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column - Update Form -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Update Status</h2>

                <form method="POST" action="{{ route('maintenance.update', $maintenanceRequest) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" required class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                            <option value="pending" {{ $maintenanceRequest->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="acknowledged" {{ $maintenanceRequest->status === 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                            <option value="in_progress" {{ $maintenanceRequest->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ $maintenanceRequest->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $maintenanceRequest->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Assigned To
                        </label>
                        <select name="assigned_to" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                            <option value="">-- Not Assigned --</option>
                            @foreach($staff as $person)
                            <option value="{{ $person->id }}" {{ $maintenanceRequest->assigned_to == $person->id ? 'selected' : '' }}>
                                {{ $person->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4" id="actual_cost_field" style="display: {{ $maintenanceRequest->status === 'completed' ? 'block' : 'none' }};">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Actual Cost (Rp)
                        </label>
                        <input type="number" name="actual_cost" value="{{ $maintenanceRequest->actual_cost ?? $maintenanceRequest->estimated_cost }}"
                            min="0" step="1000"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                    </div>

                    <div class="mb-4" id="completion_notes_field" style="display: {{ $maintenanceRequest->status === 'completed' ? 'block' : 'none' }};">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Completion Notes
                        </label>
                        <textarea name="completion_notes" rows="3"
                            placeholder="What was done to resolve the issue..."
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">{{ $maintenanceRequest->completion_notes }}</textarea>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                        Update Request
                    </button>
                </form>
            </div>

            <!-- Assignment Info -->
            @if($maintenanceRequest->assignee)
            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded">
                <div class="font-semibold text-blue-800 dark:text-blue-400 mb-1">Assigned To</div>
                <div class="text-blue-700 dark:text-blue-300">{{ $maintenanceRequest->assignee->name }}</div>
                <div class="text-sm text-blue-600 dark:text-blue-400">{{ ucfirst($maintenanceRequest->assignee->role) }}</div>
            </div>
            @endif

            <!-- Delete -->
            @if(in_array($maintenanceRequest->status, ['pending', 'cancelled']))
            <form method="POST" action="{{ route('maintenance.destroy', $maintenanceRequest) }}" onsubmit="return confirm('Are you sure you want to delete this request?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold">
                    Delete Request
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

<script>
    // Show/hide completion fields based on status
    const statusSelect = document.querySelector('select[name="status"]');
    const actualCostField = document.getElementById('actual_cost_field');
    const completionNotesField = document.getElementById('completion_notes_field');

    statusSelect.addEventListener('change', function() {
        if (this.value === 'completed') {
            actualCostField.style.display = 'block';
            completionNotesField.style.display = 'block';
        } else {
            actualCostField.style.display = 'none';
            completionNotesField.style.display = 'none';
        }
    });
</script>
</x-app-layout>

<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Detail Housekeeping Task</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $task->property->name }}</p>
        </div>
        <a href="{{ route('housekeeping.tasks.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
            Kembali
        </a>
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
        <!-- Main Task Info -->
        <div class="md:col-span-2 space-y-6">
            <!-- Task Details -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Informasi Task</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Kamar</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $task->hotelRoom->room_number }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Tipe Task</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ ucfirst(str_replace('_', ' ', $task->task_type)) }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Tanggal</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $task->task_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Priority</div>
                        <div>
                            @if($task->priority === 'urgent')
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">URGENT</span>
                            @elseif($task->priority === 'high')
                                <span class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded">High</span>
                            @elseif($task->priority === 'normal')
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Normal</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">Low</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Assigned To</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $task->assignedTo->name ?? 'Belum di-assign' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Status</div>
                        <div>
                            @if($task->status === 'pending')
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Pending</span>
                            @elseif($task->status === 'in_progress')
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">In Progress</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Completed</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($task->notes)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Catatan</div>
                    <div class="text-gray-800 dark:text-white">{{ $task->notes }}</div>
                </div>
                @endif
            </div>

            <!-- Checklist -->
            @if($task->checklist)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">
                    Checklist
                    <span class="text-sm font-normal text-gray-600 dark:text-gray-400">
                        ({{ count($task->completed_items ?? []) }}/{{ count($task->checklist) }} selesai)
                    </span>
                </h2>

                <div class="space-y-2">
                    @foreach($task->checklist as $item)
                    <div class="flex items-center p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                        <input type="checkbox"
                            id="checklist_{{ $loop->index }}"
                            class="h-5 w-5 text-blue-600 rounded"
                            {{ in_array($item, $task->completed_items ?? []) ? 'checked' : '' }}
                            {{ $task->status === 'completed' ? 'disabled' : '' }}
                            onchange="updateChecklist({{ $task->id }}, this)">
                        <label for="checklist_{{ $loop->index }}"
                            class="ml-3 text-gray-700 dark:text-gray-300 {{ in_array($item, $task->completed_items ?? []) ? 'line-through' : '' }}">
                            {{ $item }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Time Tracking -->
            @if($task->started_at || $task->completed_at)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Time Tracking</h2>

                <div class="grid grid-cols-3 gap-4">
                    @if($task->started_at)
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Dimulai</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $task->started_at->format('H:i') }}</div>
                        <div class="text-xs text-gray-500">{{ $task->started_at->diffForHumans() }}</div>
                    </div>
                    @endif

                    @if($task->completed_at)
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Selesai</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $task->completed_at->format('H:i') }}</div>
                        <div class="text-xs text-gray-500">{{ $task->completed_at->diffForHumans() }}</div>
                    </div>
                    @endif

                    @if($task->duration_minutes)
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Durasi</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $task->duration_minutes }} menit</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Inspection -->
            @if($task->status === 'completed' && $task->inspected_at)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Hasil Inspeksi</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Quality Score</div>
                        <div class="text-2xl font-bold text-yellow-600">{{ $task->quality_score }}/5</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Inspected By</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $task->inspectedBy->name ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ $task->inspected_at->diffForHumans() }}</div>
                    </div>
                </div>

                @if($task->inspection_notes)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Catatan Inspeksi</div>
                    <div class="text-gray-800 dark:text-white">{{ $task->inspection_notes }}</div>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Actions Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Aksi</h2>

                @if($task->status === 'pending')
                <form action="{{ route('housekeeping.tasks.start', $task) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Mulai Task
                    </button>
                </form>
                @endif

                @if($task->status === 'in_progress')
                <form action="{{ route('housekeeping.tasks.complete', $task) }}" method="POST" onsubmit="return confirm('Yakin task sudah selesai?')">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                        Selesaikan Task
                    </button>
                </form>
                @endif

                @if($task->status === 'completed' && !$task->inspected_at && (auth()->user()->role === 'pengguna_properti' || auth()->user()->role === 'owner'))
                <button onclick="openInspectModal()" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded">
                    Inspect Task
                </button>
                @endif
            </div>

            <!-- Task Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Timeline</h2>

                <div class="space-y-3">
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-800 dark:text-white">Task dibuat</div>
                            <div class="text-xs text-gray-500">{{ $task->created_at->format('d M Y H:i') }}</div>
                        </div>
                    </div>

                    @if($task->started_at)
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full mt-2 mr-3"></div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-800 dark:text-white">Task dimulai</div>
                            <div class="text-xs text-gray-500">{{ $task->started_at->format('d M Y H:i') }}</div>
                        </div>
                    </div>
                    @endif

                    @if($task->completed_at)
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3"></div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-800 dark:text-white">Task selesai</div>
                            <div class="text-xs text-gray-500">{{ $task->completed_at->format('d M Y H:i') }}</div>
                        </div>
                    </div>
                    @endif

                    @if($task->inspected_at)
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-purple-500 rounded-full mt-2 mr-3"></div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-800 dark:text-white">Task diinspeksi</div>
                            <div class="text-xs text-gray-500">{{ $task->inspected_at->format('d M Y H:i') }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Inspect Modal -->
    <div id="inspectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Inspect Task</h3>

                <form action="{{ route('housekeeping.tasks.inspect', $task) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="quality_score" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Quality Score (1-5) <span class="text-red-500">*</span>
                        </label>
                        <select name="quality_score" id="quality_score" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                            <option value="">-- Pilih Score --</option>
                            <option value="1">1 - Sangat Buruk</option>
                            <option value="2">2 - Buruk</option>
                            <option value="3">3 - Cukup</option>
                            <option value="4">4 - Baik</option>
                            <option value="5">5 - Sangat Baik</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="inspection_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Catatan Inspeksi
                        </label>
                        <textarea name="inspection_notes" id="inspection_notes" rows="3"
                            placeholder="Catatan hasil inspeksi..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"></textarea>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                            Submit
                        </button>
                        <button type="button" onclick="closeInspectModal()"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openInspectModal() {
    document.getElementById('inspectModal').classList.remove('hidden');
}

function closeInspectModal() {
    document.getElementById('inspectModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('inspectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeInspectModal();
    }
});

// Update checklist (if needed)
function updateChecklist(taskId, checkbox) {
    // Get all checked checkboxes
    const checkboxes = document.querySelectorAll('input[type="checkbox"][id^="checklist_"]');
    const completedItems = [];

    checkboxes.forEach((cb, index) => {
        if (cb.checked) {
            const label = document.querySelector(`label[for="${cb.id}"]`);
            completedItems.push(label.textContent.trim());
        }
    });

    // Send AJAX request to update
    fetch(`/housekeeping/tasks/${taskId}/checklist`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            completed_items: completedItems
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Checklist updated:', data.progress);
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
</x-app-layout>

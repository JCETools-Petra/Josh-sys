<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Housekeeping Tasks</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $property->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('housekeeping.tasks.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Buat Task Baru
            </a>
            <form action="{{ route('housekeeping.tasks.generate-auto') }}" method="POST" class="inline" onsubmit="return confirm('Generate auto tasks untuk semua kamar kotor?')">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                    Generate Auto Tasks
                </button>
            </form>
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

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="text-sm text-gray-600 dark:text-gray-400">Total</div>
            <div class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <div class="text-sm text-yellow-600 dark:text-yellow-400">Pending</div>
            <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-500">{{ $stats['pending'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-orange-500">
            <div class="text-sm text-orange-600 dark:text-orange-400">In Progress</div>
            <div class="text-2xl font-bold text-orange-700 dark:text-orange-500">{{ $stats['in_progress'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="text-sm text-green-600 dark:text-green-400">Completed</div>
            <div class="text-2xl font-bold text-green-700 dark:text-green-500">{{ $stats['completed'] }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('housekeeping.tasks.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal</label>
                <input type="date" name="date" value="{{ request('date', today()->toDateString()) }}"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assigned To</label>
                <select name="assigned_to" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                    <option value="">Semua Staff</option>
                    @foreach($housekeepers as $hk)
                    <option value="{{ $hk->id }}" {{ request('assigned_to') == $hk->id ? 'selected' : '' }}>{{ $hk->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Tasks Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kamar</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipe Task</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Assigned To</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Priority</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Progress</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($tasks as $task)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-semibold text-gray-800 dark:text-white">{{ $task->hotelRoom->room_number }}</div>
                            <div class="text-xs text-gray-500">{{ $task->task_date->format('d M Y') }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $task->task_type)) }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                            {{ $task->assignedTo->name ?? 'Belum di-assign' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($task->priority === 'urgent')
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">URGENT</span>
                            @elseif($task->priority === 'high')
                                <span class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded">High</span>
                            @elseif($task->priority === 'normal')
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Normal</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">Low</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($task->status === 'pending')
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Pending</span>
                            @elseif($task->status === 'in_progress')
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">In Progress</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Completed</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                            @if($task->checklist)
                                {{ count($task->completed_items ?? []) }}/{{ count($task->checklist) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <a href="{{ route('housekeeping.tasks.show', $task) }}" class="text-blue-600 hover:text-blue-800">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                            Tidak ada task
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tasks->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $tasks->links() }}
        </div>
        @endif
    </div>
</div>
</x-app-layout>

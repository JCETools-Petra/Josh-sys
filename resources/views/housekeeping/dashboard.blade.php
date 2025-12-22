<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Dashboard Housekeeping</h1>
        <p class="text-gray-600 dark:text-gray-400">{{ $property->name }} - Halo, {{ auth()->user()->name }}!</p>
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

    <!-- Room Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-gray-500">
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Kamar</div>
            <div class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ $totalRooms }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <div class="text-sm text-yellow-600 dark:text-yellow-400">Kotor</div>
            <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-500">{{ $dirtyRooms }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="text-sm text-green-600 dark:text-green-400">Bersih</div>
            <div class="text-2xl font-bold text-green-700 dark:text-green-500">{{ $cleanRooms }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="text-sm text-blue-600 dark:text-blue-400">Terisi</div>
            <div class="text-2xl font-bold text-blue-700 dark:text-blue-500">{{ $occupiedRooms }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-orange-500">
            <div class="text-sm text-orange-600 dark:text-orange-400">Maintenance</div>
            <div class="text-2xl font-bold text-orange-700 dark:text-orange-500">{{ $maintenanceRooms }}</div>
        </div>
    </div>

    <!-- My Tasks Today & My Assigned Rooms -->
    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <!-- My Tasks Today -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Tugas Saya Hari Ini</h2>
                <div class="mt-2 flex gap-4 text-sm">
                    <span class="text-yellow-600">Pending: {{ $taskStats['my_pending'] }}</span>
                    <span class="text-blue-600">Progress: {{ $taskStats['my_in_progress'] }}</span>
                    <span class="text-green-600">Selesai: {{ $taskStats['my_completed'] }}</span>
                </div>
            </div>
            <div class="p-4">
                @forelse($myTodayTasks as $task)
                <div class="mb-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-semibold text-gray-800 dark:text-white">
                                Kamar {{ $task->hotelRoom->room_number }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ ucfirst(str_replace('_', ' ', $task->task_type)) }}
                            </div>
                            @if($task->status === 'in_progress' && $task->started_at)
                                <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                    Dimulai: {{ $task->started_at->diffForHumans() }}
                                </div>
                            @endif
                        </div>
                        <div>
                            @if($task->status === 'pending')
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Pending</span>
                            @elseif($task->status === 'in_progress')
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Progress</span>
                            @elseif($task->status === 'completed')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Selesai</span>
                            @endif

                            @if($task->priority === 'high' || $task->priority === 'urgent')
                                <span class="ml-2 px-2 py-1 text-xs bg-red-100 text-red-800 rounded">{{ strtoupper($task->priority) }}</span>
                            @endif
                        </div>
                    </div>

                    @if($task->status === 'pending')
                    <div class="mt-2 flex gap-2">
                        <form action="{{ route('housekeeping.tasks.start', $task) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                                Mulai
                            </button>
                        </form>
                        <a href="{{ route('housekeeping.tasks.show', $task) }}" class="text-sm bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded">
                            Detail
                        </a>
                    </div>
                    @elseif($task->status === 'in_progress')
                    <div class="mt-2 flex gap-2">
                        <form action="{{ route('housekeeping.tasks.complete', $task) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded">
                                Selesai
                            </button>
                        </form>
                        <a href="{{ route('housekeeping.tasks.show', $task) }}" class="text-sm bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded">
                            Detail
                        </a>
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                    <p>Tidak ada tugas hari ini</p>
                </div>
                @endforelse

                <div class="mt-4 text-center">
                    <a href="{{ route('housekeeping.tasks.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                        Lihat Semua Tugas →
                    </a>
                </div>
            </div>
        </div>

        <!-- My Assigned Rooms (Dirty) -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Kamar yang Di-assign ke Saya</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Kamar kotor yang perlu dibersihkan</p>
            </div>
            <div class="p-4">
                @forelse($myAssignedRooms as $room)
                <div class="mb-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg flex justify-between items-center">
                    <div>
                        <div class="font-semibold text-gray-800 dark:text-white">
                            Kamar {{ $room->room_number }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $room->roomType->name ?? 'Standard' }} - Lantai {{ $room->floor }}
                        </div>
                    </div>
                    <form action="{{ route('housekeeping.quick-mark-clean', $room) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                            Mark Clean
                        </button>
                    </form>
                </div>
                @empty
                <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                    <p>Tidak ada kamar yang di-assign</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- My Performance This Month -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Performa Saya Bulan Ini</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $myPerformance['total_tasks'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Tugas</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $myPerformance['completed_tasks'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Selesai</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600">{{ $myPerformance['completion_rate'] }}%</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Completion Rate</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600">
                        {{ $myPerformance['avg_quality_score'] ? number_format($myPerformance['avg_quality_score'], 1) : 'N/A' }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Avg Quality (of 5)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Lost & Found Items -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Barang Temuan Terbaru</h2>
        </div>
        <div class="p-4">
            @forelse($recentLostItems as $item)
            <div class="mb-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $item->item_name }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $item->category }} - {{ $item->location_found }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            Ditemukan: {{ $item->date_found->format('d M Y') }} oleh {{ $item->foundBy->name }}
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">{{ $item->status }}</span>
                </div>
            </div>
            @empty
            <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                <p>Tidak ada barang temuan</p>
            </div>
            @endforelse

            <div class="mt-4 text-center">
                <a href="{{ route('housekeeping.lost-found.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    Lihat Semua Lost & Found →
                </a>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

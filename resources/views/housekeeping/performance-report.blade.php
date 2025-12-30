<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Housekeeping Performance Report</h1>
        <p class="text-gray-600 dark:text-gray-400">{{ $property->name }}</p>
    </div>

    <!-- Date Filter -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('housekeeping.performance') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Overall Statistics -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Overall Statistics</h2>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $overallStats['total_tasks'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Total Tasks</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600">{{ $overallStats['completed_tasks'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Completed</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-orange-600">{{ $overallStats['avg_duration'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Avg Duration (min)</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-600">{{ $overallStats['avg_quality_score'] ?? 'N/A' }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Avg Quality</div>
            </div>
        </div>
    </div>

    <!-- Staff Performance -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Staff Performance</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Rank</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Staff Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Tasks</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Completed</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Completion Rate</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Avg Duration</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Quality Score</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Rooms Cleaned</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($performanceData as $index => $data)
                    <tr class="{{ $index < 3 ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($index === 0)
                                <span class="text-2xl">🥇</span>
                            @elseif($index === 1)
                                <span class="text-2xl">🥈</span>
                            @elseif($index === 2)
                                <span class="text-2xl">🥉</span>
                            @else
                                <span class="text-gray-600 dark:text-gray-400">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-semibold text-gray-800 dark:text-white">{{ $data['staff']->name }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                            {{ $data['total_tasks'] }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                            {{ $data['completed_tasks'] }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-semibold
                                    {{ $data['completion_rate'] >= 90 ? 'text-green-600' : '' }}
                                    {{ $data['completion_rate'] >= 70 && $data['completion_rate'] < 90 ? 'text-blue-600' : '' }}
                                    {{ $data['completion_rate'] < 70 ? 'text-orange-600' : '' }}
                                ">
                                    {{ $data['completion_rate'] }}%
                                </div>
                                <div class="ml-2 w-16 bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $data['completion_rate'] }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                            {{ $data['avg_duration'] }} min
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($data['avg_quality_score'])
                                <span class="px-2 py-1 text-xs rounded font-semibold
                                    {{ $data['avg_quality_score'] >= 4.5 ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $data['avg_quality_score'] >= 3.5 && $data['avg_quality_score'] < 4.5 ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $data['avg_quality_score'] < 3.5 ? 'bg-orange-100 text-orange-800' : '' }}
                                ">
                                    {{ $data['avg_quality_score'] }}/5
                                </span>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                            {{ $data['rooms_cleaned'] }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                            Tidak ada data performance untuk periode ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Task Type Breakdown -->
    @if(!empty($performanceData))
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Task Type Breakdown</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Staff Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Daily Cleaning</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Deep Cleaning</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($performanceData as $data)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap font-semibold text-gray-800 dark:text-white">
                            {{ $data['staff']->name }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                            {{ $data['daily_cleaning_count'] }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                            {{ $data['deep_cleaning_count'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Legend -->
    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded">
        <h3 class="font-semibold text-blue-800 dark:text-blue-400 mb-2">Keterangan:</h3>
        <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
            <li><strong>Completion Rate:</strong> Persentase task yang diselesaikan dari total task yang di-assign</li>
            <li><strong>Avg Duration:</strong> Rata-rata waktu yang dibutuhkan untuk menyelesaikan 1 task (dalam menit)</li>
            <li><strong>Quality Score:</strong> Rata-rata skor kualitas dari task yang telah diinspeksi (skala 1-5)</li>
            <li><strong>Rooms Cleaned:</strong> Total kamar yang telah dibersihkan pada periode ini</li>
        </ul>
    </div>
</div>
</x-app-layout>

<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Occupancy Report</h1>
            <p class="text-gray-600">{{ $property->name }}</p>
        </div>
        <div class="flex space-x-3">
            <form method="GET" class="flex space-x-2">
                <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="border-gray-300 rounded-lg shadow-sm">
                <span class="self-center">to</span>
                <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="border-gray-300 rounded-lg shadow-sm">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Filter
                </button>
            </form>
            <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                Print
            </button>
            <a href="{{ route('reports.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Kembali
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="text-sm text-blue-600">Average Occupancy</div>
            <div class="text-2xl font-bold text-blue-700">{{ number_format($averageOccupancy, 1) }}%</div>
            <div class="text-xs text-gray-600">{{ $startDate->format('d M') }} - {{ $endDate->format('d M Y') }}</div>
        </div>

        <div class="bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="text-sm text-green-600">Total Room Nights</div>
            <div class="text-2xl font-bold text-green-700">{{ number_format($totalRoomNights) }}</div>
            <div class="text-xs text-gray-600">Sold in period</div>
        </div>

        <div class="bg-purple-50 rounded-lg shadow p-4 border-l-4 border-purple-500">
            <div class="text-sm text-purple-600">Total Rooms</div>
            <div class="text-2xl font-bold text-purple-700">{{ $totalRooms }}</div>
            <div class="text-xs text-gray-600">Available rooms</div>
        </div>

        <div class="bg-orange-50 rounded-lg shadow p-4 border-l-4 border-orange-500">
            <div class="text-sm text-orange-600">Average ADR</div>
            <div class="text-2xl font-bold text-orange-700">Rp {{ number_format($averageADR, 0, ',', '.') }}</div>
            <div class="text-xs text-gray-600">Per occupied room</div>
        </div>
    </div>

    <!-- Occupancy Trend Chart -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Occupancy Trend</h2>
        <div class="overflow-x-auto">
            <div class="min-w-full" style="height: 300px;">
                <canvas id="occupancyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Daily Breakdown Table -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Daily Breakdown</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Date</th>
                        <th class="px-4 py-2 text-center text-sm font-semibold">Day</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Occupied</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Available</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Occupancy %</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Revenue</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">ADR</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($dailyData as $data)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold">{{ $data['date']->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 rounded text-xs
                                @if(in_array($data['date']->dayOfWeek, [0, 6])) bg-orange-100 text-orange-800
                                @else bg-blue-100 text-blue-800
                                @endif">
                                {{ $data['date']->format('D') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">{{ $data['occupied'] }}</td>
                        <td class="px-4 py-3 text-right">{{ $data['available'] }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full
                                        @if($data['occupancy_rate'] >= 80) bg-green-600
                                        @elseif($data['occupancy_rate'] >= 60) bg-blue-600
                                        @elseif($data['occupancy_rate'] >= 40) bg-yellow-600
                                        @else bg-red-600
                                        @endif"
                                        style="width: {{ $data['occupancy_rate'] }}%"></div>
                                </div>
                                <span class="font-semibold">{{ number_format($data['occupancy_rate'], 1) }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($data['revenue'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($data['adr'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No data available</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-100 font-bold">
                    <tr>
                        <td colspan="2" class="px-4 py-3">TOTAL / AVERAGE</td>
                        <td class="px-4 py-3 text-right">{{ number_format($totalRoomNights) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($totalRooms * $dailyData->count()) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($averageOccupancy, 1) }}%</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($dailyData->sum('revenue'), 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($averageADR, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Room Type Breakdown -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Occupancy by Room Type</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Room Type</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Total Rooms</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Room Nights Sold</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Occupancy %</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($roomTypeData as $rtData)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold">{{ $rtData['room_type'] }}</td>
                        <td class="px-4 py-3 text-right">{{ $rtData['total_rooms'] }}</td>
                        <td class="px-4 py-3 text-right">{{ $rtData['room_nights'] }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $rtData['occupancy_rate'] }}%"></div>
                                </div>
                                <span class="font-semibold">{{ number_format($rtData['occupancy_rate'], 1) }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($rtData['revenue'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No room type data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Occupancy Trend Chart
    const ctx = document.getElementById('occupancyChart').getContext('2d');
    const chartData = @json($dailyData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(d => new Date(d.date).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' })),
            datasets: [{
                label: 'Occupancy Rate (%)',
                data: chartData.map(d => d.occupancy_rate),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
</script>

<style>
@media print {
    .no-print {
        display: none;
    }
}
</style>
</x-app-layout>

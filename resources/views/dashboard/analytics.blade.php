<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header with Quick Actions -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Dashboard Analytics</h1>
            <p class="text-gray-600">{{ $property->name }} - {{ now()->format('d M Y, H:i') }}</p>
        </div>
        <div class="flex space-x-3">
            <form method="GET" class="flex space-x-2">
                <select name="days" class="border-gray-300 rounded-lg shadow-sm" onchange="this.form.submit()">
                    <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="14" {{ $days == 14 ? 'selected' : '' }}>Last 14 Days</option>
                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 Days</option>
                </select>
            </form>
            <a href="{{ route('reports.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                📊 Reports
            </a>
            <a href="{{ route('frontoffice.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                🏨 Front Office
            </a>
        </div>
    </div>

    <!-- Today's Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg shadow-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm opacity-90">Check-ins Today</div>
                    <div class="text-3xl font-bold">{{ $todayCheckIns }}</div>
                </div>
                <div class="text-4xl opacity-80">📥</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm opacity-90">Check-outs Today</div>
                    <div class="text-3xl font-bold">{{ $todayCheckOuts }}</div>
                </div>
                <div class="text-4xl opacity-80">📤</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg shadow-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm opacity-90">Occupied Rooms</div>
                    <div class="text-3xl font-bold">{{ $currentlyOccupied }}/{{ $totalRooms }}</div>
                </div>
                <div class="text-4xl opacity-80">🛏️</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-lg shadow-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm opacity-90">Occupancy Rate</div>
                    <div class="text-3xl font-bold">{{ number_format($occupancyRate, 1) }}%</div>
                </div>
                <div class="text-4xl opacity-80">📊</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-pink-500 to-pink-600 text-white rounded-lg shadow-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm opacity-90">Total Revenue</div>
                    <div class="text-2xl font-bold">Rp {{ number_format($totalRevenue / 1000, 0) }}K</div>
                </div>
                <div class="text-4xl opacity-80">💰</div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Revenue Trend</h2>
            <canvas id="revenueChart" height="250"></canvas>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Occupancy Trend</h2>
            <canvas id="occupancyChart" height="250"></canvas>
        </div>
    </div>

    <!-- Revenue Breakdown & Room Type Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Revenue Breakdown Pie Chart -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Revenue Breakdown</h2>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="text-center p-4 bg-blue-50 rounded">
                    <div class="text-sm text-blue-600">Room Revenue</div>
                    <div class="text-xl font-bold text-blue-700">Rp {{ number_format($roomRevenue / 1000, 0) }}K</div>
                    <div class="text-xs text-gray-600">{{ $totalRevenue > 0 ? number_format(($roomRevenue / $totalRevenue) * 100, 1) : 0 }}%</div>
                </div>
                <div class="text-center p-4 bg-green-50 rounded">
                    <div class="text-sm text-green-600">F&B Revenue</div>
                    <div class="text-xl font-bold text-green-700">Rp {{ number_format($fnbRevenue / 1000, 0) }}K</div>
                    <div class="text-xs text-gray-600">{{ $totalRevenue > 0 ? number_format(($fnbRevenue / $totalRevenue) * 100, 1) : 0 }}%</div>
                </div>
            </div>
            <canvas id="revenueBreakdownChart" height="200"></canvas>
        </div>

        <!-- Room Type Statistics -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Occupancy by Room Type</h2>
            <div class="space-y-4">
                @foreach($roomTypeStats as $stat)
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="font-semibold text-gray-700">{{ $stat['name'] }}</span>
                        <span class="text-sm text-gray-600">{{ $stat['occupied'] }}/{{ $stat['total'] }} ({{ $stat['rate'] }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="h-3 rounded-full transition-all
                            @if($stat['rate'] >= 80) bg-green-600
                            @elseif($stat['rate'] >= 60) bg-blue-600
                            @elseif($stat['rate'] >= 40) bg-yellow-600
                            @else bg-red-600
                            @endif"
                            style="width: {{ $stat['rate'] }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Top F&B Items & Upcoming Events -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Top F&B Items -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Top 5 F&B Items (Last 30 Days)</h2>
            <div class="space-y-3">
                @forelse($topFnbItems as $index => $item)
                <div class="flex items-center justify-between py-2 border-b last:border-0">
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full
                            @if($index === 0) bg-yellow-100 text-yellow-800
                            @elseif($index === 1) bg-gray-100 text-gray-800
                            @elseif($index === 2) bg-orange-100 text-orange-800
                            @else bg-blue-100 text-blue-800
                            @endif font-bold text-sm">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800">{{ $item['name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $item['quantity'] }} sold</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-green-600">Rp {{ number_format($item['revenue'], 0, ',', '.') }}</div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No F&B sales data</p>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('frontoffice.room-grid') }}" class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-lg transition">
                    <div class="text-center">
                        <div class="text-2xl mb-1">🔍</div>
                        <div class="text-sm font-semibold">Room Grid</div>
                    </div>
                </a>
                <a href="{{ route('restaurant.pos') }}" class="flex items-center justify-center bg-green-600 hover:bg-green-700 text-white p-4 rounded-lg transition">
                    <div class="text-center">
                        <div class="text-2xl mb-1">🍽️</div>
                        <div class="text-sm font-semibold">POS System</div>
                    </div>
                </a>
                <a href="{{ route('restaurant.index') }}" class="flex items-center justify-center bg-purple-600 hover:bg-purple-700 text-white p-4 rounded-lg transition">
                    <div class="text-center">
                        <div class="text-2xl mb-1">📋</div>
                        <div class="text-sm font-semibold">Orders</div>
                    </div>
                </a>
                <a href="{{ route('reports.night-audit') }}" class="flex items-center justify-center bg-orange-600 hover:bg-orange-700 text-white p-4 rounded-lg transition">
                    <div class="text-center">
                        <div class="text-2xl mb-1">🌙</div>
                        <div class="text-sm font-semibold">Night Audit</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Upcoming Arrivals & Departures -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming Arrivals -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Upcoming Arrivals (Next 3 Days)</h2>
            @forelse($upcomingArrivals as $stay)
            <div class="border-b py-3 last:border-0">
                <div class="flex justify-between">
                    <div>
                        <div class="font-semibold text-gray-800">{{ $stay->guest->full_name }}</div>
                        <div class="text-sm text-gray-600">Room {{ $stay->hotelRoom->room_number }} • {{ $stay->check_in_date->format('d M Y') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-blue-600">{{ $stay->nights }} nights</div>
                        <div class="text-xs text-gray-500">Rp {{ number_format($stay->room_rate_per_night, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No upcoming arrivals</p>
            @endforelse
        </div>

        <!-- Upcoming Departures -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Upcoming Departures (Next 3 Days)</h2>
            @forelse($upcomingDepartures as $stay)
            <div class="border-b py-3 last:border-0">
                <div class="flex justify-between">
                    <div>
                        <div class="font-semibold text-gray-800">{{ $stay->guest->full_name }}</div>
                        <div class="text-sm text-gray-600">Room {{ $stay->hotelRoom->room_number }} • {{ $stay->check_out_date->format('d M Y') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-green-600">{{ $stay->nights }} nights</div>
                        <div class="text-xs text-gray-500">Rp {{ number_format($stay->total_amount, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No upcoming departures</p>
            @endforelse
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Trend Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = @json($dailyRevenue);

    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueData.map(d => new Date(d.date).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' })),
            datasets: [
                {
                    label: 'Room Revenue',
                    data: revenueData.map(d => d.room_revenue),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'F&B Revenue',
                    data: revenueData.map(d => d.fnb_revenue),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
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
                            return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000) + 'K';
                        }
                    }
                }
            }
        }
    });

    // Occupancy Trend Chart
    const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
    const occupancyData = @json($occupancyTrend);

    new Chart(occupancyCtx, {
        type: 'bar',
        data: {
            labels: occupancyData.map(d => new Date(d.date).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' })),
            datasets: [{
                label: 'Occupancy Rate (%)',
                data: occupancyData.map(d => d.rate),
                backgroundColor: occupancyData.map(d => {
                    if (d.rate >= 80) return 'rgba(34, 197, 94, 0.8)';
                    if (d.rate >= 60) return 'rgba(59, 130, 246, 0.8)';
                    if (d.rate >= 40) return 'rgba(234, 179, 8, 0.8)';
                    return 'rgba(239, 68, 68, 0.8)';
                }),
                borderColor: occupancyData.map(d => {
                    if (d.rate >= 80) return 'rgb(34, 197, 94)';
                    if (d.rate >= 60) return 'rgb(59, 130, 246)';
                    if (d.rate >= 40) return 'rgb(234, 179, 8)';
                    return 'rgb(239, 68, 68)';
                }),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y.toFixed(1) + '%';
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

    // Revenue Breakdown Pie Chart
    const breakdownCtx = document.getElementById('revenueBreakdownChart').getContext('2d');

    new Chart(breakdownCtx, {
        type: 'doughnut',
        data: {
            labels: ['Room Revenue', 'F&B Revenue'],
            datasets: [{
                data: [{{ $roomRevenue }}, {{ $fnbRevenue }}],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(34, 197, 94, 0.8)'
                ],
                borderColor: [
                    'rgb(59, 130, 246)',
                    'rgb(34, 197, 94)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': Rp ' + value.toLocaleString('id-ID') + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
</script>
</x-app-layout>

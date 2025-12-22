<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">F&B Sales Report</h1>
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
            <div class="text-sm text-blue-600">Total Revenue</div>
            <div class="text-2xl font-bold text-blue-700">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            <div class="text-xs text-gray-600">{{ $startDate->format('d M') }} - {{ $endDate->format('d M Y') }}</div>
        </div>

        <div class="bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="text-sm text-green-600">Total Orders</div>
            <div class="text-2xl font-bold text-green-700">{{ number_format($totalOrders) }}</div>
            <div class="text-xs text-gray-600">Completed orders</div>
        </div>

        <div class="bg-purple-50 rounded-lg shadow p-4 border-l-4 border-purple-500">
            <div class="text-sm text-purple-600">Avg Order Value</div>
            <div class="text-2xl font-bold text-purple-700">Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</div>
            <div class="text-xs text-gray-600">Per order</div>
        </div>

        <div class="bg-orange-50 rounded-lg shadow p-4 border-l-4 border-orange-500">
            <div class="text-sm text-orange-600">Total Items Sold</div>
            <div class="text-2xl font-bold text-orange-700">{{ number_format($totalItemsSold) }}</div>
            <div class="text-xs text-gray-600">Items</div>
        </div>
    </div>

    <!-- Sales by Category -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Category Breakdown -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Sales by Category</h2>
            <div class="space-y-4">
                @forelse($categoryData as $category => $data)
                <div>
                    <div class="flex justify-between mb-2">
                        <div>
                            <span class="font-semibold text-gray-700">{{ ucfirst($category) }}</span>
                            <span class="text-sm text-gray-500 ml-2">({{ $data['items_sold'] }} items)</span>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-blue-600">Rp {{ number_format($data['revenue'], 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-500">{{ $totalRevenue > 0 ? number_format(($data['revenue'] / $totalRevenue) * 100, 1) : 0 }}%</div>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $totalRevenue > 0 ? ($data['revenue'] / $totalRevenue) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No category data available</p>
                @endforelse
            </div>
        </div>

        <!-- Order Type Breakdown -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Sales by Order Type</h2>
            <div class="space-y-4">
                @forelse($orderTypeData as $type => $data)
                <div>
                    <div class="flex justify-between mb-2">
                        <div>
                            <span class="font-semibold text-gray-700">{{ str_replace('_', ' ', ucfirst($type)) }}</span>
                            <span class="text-sm text-gray-500 ml-2">({{ $data['orders'] }} orders)</span>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-green-600">Rp {{ number_format($data['revenue'], 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-500">{{ $totalRevenue > 0 ? number_format(($data['revenue'] / $totalRevenue) * 100, 1) : 0 }}%</div>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $totalRevenue > 0 ? ($data['revenue'] / $totalRevenue) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No order type data available</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Top 10 Best Sellers -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Top 10 Best Selling Items</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold">#</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Item Name</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Category</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Qty Sold</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Revenue</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Avg Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($bestSellers as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full
                                @if($index === 0) bg-yellow-100 text-yellow-800
                                @elseif($index === 1) bg-gray-100 text-gray-800
                                @elseif($index === 2) bg-orange-100 text-orange-800
                                @else bg-blue-100 text-blue-800
                                @endif font-bold text-sm">
                                {{ $index + 1 }}
                            </div>
                        </td>
                        <td class="px-4 py-3 font-semibold">{{ $item->name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-800">
                                {{ ucfirst($item->category) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">{{ number_format($item->total_quantity) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-blue-600">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm">Rp {{ number_format($item->avg_price, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No sales data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Daily Sales Trend -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Daily Sales Trend</h2>
        <div class="overflow-x-auto">
            <div class="min-w-full" style="height: 300px;">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- All Items Performance -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">All Menu Items Performance</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Item Name</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Category</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Base Price</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Qty Sold</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Revenue</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">% of Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($allItemsPerformance as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold">{{ $item->name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded
                                @if($item->category === 'breakfast') bg-yellow-100 text-yellow-800
                                @elseif($item->category === 'lunch') bg-orange-100 text-orange-800
                                @elseif($item->category === 'dinner') bg-red-100 text-red-800
                                @elseif($item->category === 'beverage') bg-blue-100 text-blue-800
                                @elseif($item->category === 'snack') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($item->category) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($item->total_quantity ?? 0) }}</td>
                        <td class="px-4 py-3 text-right font-bold">Rp {{ number_format($item->total_revenue ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $totalRevenue > 0 ? (($item->total_revenue ?? 0) / $totalRevenue) * 100 : 0 }}%"></div>
                                </div>
                                <span class="text-xs">{{ $totalRevenue > 0 ? number_format((($item->total_revenue ?? 0) / $totalRevenue) * 100, 1) : 0 }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No menu items available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Daily Sales Trend Chart
    const ctx = document.getElementById('dailySalesChart').getContext('2d');
    const dailySalesData = @json($dailySalesData);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dailySalesData.map(d => new Date(d.date).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' })),
            datasets: [{
                label: 'Revenue (Rp)',
                data: dailySalesData.map(d => d.revenue),
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1
            }, {
                label: 'Orders',
                data: dailySalesData.map(d => d.orders),
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1,
                yAxisID: 'y1'
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
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.datasetIndex === 0) {
                                label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            } else {
                                label += context.parsed.y;
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: function(value) {
                            return value + ' orders';
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

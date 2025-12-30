<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Restaurant Sales Report</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $property->name }}</p>
        </div>
        <a href="{{ route('restaurant.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            Kembali
        </a>
    </div>

    <!-- Date Filter -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('restaurant.sales-report') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Overall Statistics -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Overall Statistics</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
        </p>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $totalOrders }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Total Orders</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Total Revenue</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-orange-600">Rp {{ number_format($totalTax, 0, ',', '.') }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Total Tax</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-600">Rp {{ number_format($totalService, 0, ',', '.') }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Service Charge</div>
            </div>
        </div>
    </div>

    <!-- Orders by Type -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Orders by Type</h2>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $typeLabels = [
                    'dine_in' => ['label' => 'Dine-In', 'icon' => '🍽️', 'color' => 'green'],
                    'room_service' => ['label' => 'Room Service', 'icon' => '🛎️', 'color' => 'blue'],
                    'takeaway' => ['label' => 'Takeaway', 'icon' => '📦', 'color' => 'yellow'],
                    'delivery' => ['label' => 'Delivery', 'icon' => '🚗', 'color' => 'purple'],
                ];
            @endphp

            @foreach($typeLabels as $type => $info)
                @php
                    $typeData = $ordersByType->firstWhere('order_type', $type);
                    $count = $typeData->count ?? 0;
                    $revenue = $typeData->revenue ?? 0;
                @endphp
                <div class="bg-{{ $info['color'] }}-50 dark:bg-{{ $info['color'] }}-900/20 rounded-lg p-4 border-l-4 border-{{ $info['color'] }}-500">
                    <div class="text-2xl mb-2">{{ $info['icon'] }}</div>
                    <div class="font-semibold text-{{ $info['color'] }}-800 dark:text-{{ $info['color'] }}-400">{{ $info['label'] }}</div>
                    <div class="text-2xl font-bold text-{{ $info['color'] }}-700 dark:text-{{ $info['color'] }}-500">{{ $count }}</div>
                    <div class="text-sm text-{{ $info['color'] }}-600 dark:text-{{ $info['color'] }}-400">
                        Rp {{ number_format($revenue, 0, ',', '.') }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Top Selling Items -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Top 10 Selling Items</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Rank</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Menu Item</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Category</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Qty Sold</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Revenue</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($topItems as $index => $item)
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
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-800 dark:text-white">{{ $item->menuItem->name }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($item->menuItem->category === 'breakfast') bg-yellow-100 text-yellow-800
                                @elseif($item->menuItem->category === 'lunch') bg-orange-100 text-orange-800
                                @elseif($item->menuItem->category === 'dinner') bg-red-100 text-red-800
                                @elseif($item->menuItem->category === 'beverage') bg-blue-100 text-blue-800
                                @else bg-purple-100 text-purple-800
                                @endif">
                                {{ ucfirst($item->menuItem->category) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-white">
                            {{ $item->total_qty }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-green-600">
                            Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                            Tidak ada data penjualan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Daily Revenue Trend -->
    @if($dailyRevenue->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Daily Revenue Trend</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Orders</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Revenue</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Trend</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($dailyRevenue as $day)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap font-semibold text-gray-800 dark:text-white">
                            {{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">
                            {{ $day->orders }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-white">
                            Rp {{ number_format($day->revenue, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                @php
                                    $maxRevenue = $dailyRevenue->max('revenue');
                                    $percentage = $maxRevenue > 0 ? ($day->revenue / $maxRevenue) * 100 : 0;
                                @endphp
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Summary Info -->
    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded">
        <h3 class="font-semibold text-blue-800 dark:text-blue-400 mb-2">Keterangan:</h3>
        <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
            <li><strong>Total Revenue:</strong> Total pendapatan dari semua order yang sudah dibayar (Subtotal saja, tidak termasuk tax & service)</li>
            <li><strong>Total Tax:</strong> Total pajak 10% yang dikumpulkan dari penjualan</li>
            <li><strong>Service Charge:</strong> Total service charge 5% yang dikumpulkan</li>
            <li><strong>Top Selling Items:</strong> Item yang paling banyak terjual berdasarkan quantity</li>
        </ul>
    </div>
</div>
</x-app-layout>

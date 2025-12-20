<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Daily Sales Report</h1>
            <p class="text-gray-600">{{ $property->name }} - {{ $targetDate->format('d M Y') }}</p>
        </div>
        <div class="flex space-x-3">
            <form method="GET" class="flex space-x-2">
                <input type="date" name="date" value="{{ $targetDate->toDateString() }}" class="border-gray-300 rounded-lg shadow-sm">
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
            <div class="text-sm text-blue-600">Room Revenue</div>
            <div class="text-2xl font-bold text-blue-700">Rp {{ number_format($roomRevenue, 0, ',', '.') }}</div>
            <div class="text-xs text-gray-600">{{ $roomSales }} transactions</div>
        </div>

        <div class="bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="text-sm text-green-600">F&B Revenue</div>
            <div class="text-2xl font-bold text-green-700">Rp {{ number_format($fnbRevenue, 0, ',', '.') }}</div>
            <div class="text-xs text-gray-600">{{ $fnbOrders }} orders</div>
        </div>

        <div class="bg-purple-50 rounded-lg shadow p-4 border-l-4 border-purple-500">
            <div class="text-sm text-purple-600">Total Revenue</div>
            <div class="text-2xl font-bold text-purple-700">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            <div class="text-xs text-gray-600">{{ $roomSales + $fnbOrders }} total transactions</div>
        </div>

        <div class="bg-orange-50 rounded-lg shadow p-4 border-l-4 border-orange-500">
            <div class="text-sm text-orange-600">Avg Transaction</div>
            <div class="text-2xl font-bold text-orange-700">Rp {{ number_format($avgTransaction, 0, ',', '.') }}</div>
            <div class="text-xs text-gray-600">Per transaction</div>
        </div>
    </div>

    <!-- Revenue Breakdown Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Revenue by Source -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Revenue by Source</h2>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">Room Revenue</span>
                        <span class="text-sm font-medium text-blue-600">{{ $totalRevenue > 0 ? number_format(($roomRevenue / $totalRevenue) * 100, 1) : 0 }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $totalRevenue > 0 ? ($roomRevenue / $totalRevenue) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">F&B Revenue</span>
                        <span class="text-sm font-medium text-green-600">{{ $totalRevenue > 0 ? number_format(($fnbRevenue / $totalRevenue) * 100, 1) : 0 }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $totalRevenue > 0 ? ($fnbRevenue / $totalRevenue) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t">
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-800">Total Revenue:</span>
                    <span class="text-xl font-bold text-purple-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Methods Breakdown -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Payment Methods</h2>
            <div class="space-y-3">
                @forelse($paymentMethods as $method => $amount)
                <div class="flex justify-between items-center py-2 border-b last:border-0">
                    <div class="flex items-center space-x-2">
                        @if($method === 'cash')
                            <span class="text-2xl">💵</span>
                        @elseif($method === 'credit_card')
                            <span class="text-2xl">💳</span>
                        @elseif($method === 'debit_card')
                            <span class="text-2xl">💳</span>
                        @elseif($method === 'transfer')
                            <span class="text-2xl">🏦</span>
                        @else
                            <span class="text-2xl">💰</span>
                        @endif
                        <span class="font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $method)) }}</span>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-gray-800">Rp {{ number_format($amount, 0, ',', '.') }}</div>
                        <div class="text-xs text-gray-500">{{ $totalRevenue > 0 ? number_format(($amount / $totalRevenue) * 100, 1) : 0 }}%</div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No payment data available</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Check-in/Check-out Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Check-ins Today -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Check-ins Today ({{ $checkIns->count() }})</h2>
            @forelse($checkIns as $stay)
            <div class="border-b py-3 last:border-0">
                <div class="flex justify-between">
                    <div>
                        <div class="font-semibold text-gray-800">{{ $stay->guest->full_name }}</div>
                        <div class="text-sm text-gray-600">Room {{ $stay->hotelRoom->room_number }} • {{ $stay->actual_check_in ? $stay->actual_check_in->format('H:i') : '-' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-blue-600">Rp {{ number_format($stay->room_rate_per_night, 0, ',', '.') }}</div>
                        <div class="text-xs text-gray-500">{{ $stay->nights }} nights</div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No check-ins today</p>
            @endforelse
        </div>

        <!-- Check-outs Today -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Check-outs Today ({{ $checkOuts->count() }})</h2>
            @forelse($checkOuts as $stay)
            <div class="border-b py-3 last:border-0">
                <div class="flex justify-between">
                    <div>
                        <div class="font-semibold text-gray-800">{{ $stay->guest->full_name }}</div>
                        <div class="text-sm text-gray-600">Room {{ $stay->hotelRoom->room_number }} • {{ $stay->actual_check_out ? $stay->actual_check_out->format('H:i') : '-' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-green-600">Rp {{ number_format($stay->total_amount, 0, ',', '.') }}</div>
                        <div class="text-xs text-gray-500">{{ $stay->nights }} nights</div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No check-outs today</p>
            @endforelse
        </div>
    </div>

    <!-- F&B Sales Details -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">F&B Sales Details</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Order #</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Time</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Type</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Items</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Amount</th>
                        <th class="px-4 py-2 text-center text-sm font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($fnbOrdersList as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold">#{{ $order->order_number }}</td>
                        <td class="px-4 py-3 text-sm">{{ $order->order_time->format('H:i') }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                                {{ str_replace('_', ' ', ucfirst($order->order_type)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $order->items->count() }} items</td>
                        <td class="px-4 py-3 text-sm text-right font-semibold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 text-xs rounded
                                @if($order->payment_status === 'paid') bg-green-100 text-green-800
                                @elseif($order->payment_status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No F&B orders today</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none;
    }
}
</style>
</x-app-layout>

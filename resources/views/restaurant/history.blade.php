<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Order History</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $property->name }}</p>
        </div>
        <a href="{{ route('restaurant.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            Kembali
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('restaurant.orders.history') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ request('start_date', today()->subDays(7)->toDateString()) }}"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ request('end_date', today()->toDateString()) }}"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipe Order</label>
                <select name="order_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                    <option value="">Semua Tipe</option>
                    <option value="dine_in" {{ request('order_type') === 'dine_in' ? 'selected' : '' }}>Dine-In</option>
                    <option value="room_service" {{ request('order_type') === 'room_service' ? 'selected' : '' }}>Room Service</option>
                    <option value="takeaway" {{ request('order_type') === 'takeaway' ? 'selected' : '' }}>Takeaway</option>
                    <option value="delivery" {{ request('order_type') === 'delivery' ? 'selected' : '' }}>Delivery</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>Preparing</option>
                    <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Ready</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Orders List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Order #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tanggal & Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipe</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Items</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Payment</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-semibold text-gray-800 dark:text-white">#{{ $order->order_number }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                            {{ $order->order_time->format('d M Y') }}<br>
                            <span class="text-xs text-gray-500">{{ $order->order_time->format('H:i') }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($order->order_type === 'room_service')
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Room Service</span>
                            @elseif($order->order_type === 'dine_in')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Dine-In</span>
                            @elseif($order->order_type === 'takeaway')
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Takeaway</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded">Delivery</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                            @if($order->guest)
                                {{ $order->guest->full_name }}
                            @elseif($order->customer_name)
                                {{ $order->customer_name }}
                            @elseif($order->hotelRoom)
                                Room {{ $order->hotelRoom->room_number }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                            {{ $order->items->count() }} items
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap font-semibold text-gray-800 dark:text-white">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($order->status === 'completed')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Completed</span>
                            @elseif($order->status === 'cancelled')
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">Cancelled</span>
                            @elseif($order->status === 'preparing')
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Preparing</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">{{ ucfirst($order->status) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($order->payment_status === 'paid')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Paid</span>
                            @elseif($order->payment_status === 'charge_to_room')
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Room Charge</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">{{ ucfirst($order->payment_status) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap text-sm">
                            <a href="{{ route('restaurant.orders.detail', $order) }}" class="text-blue-600 hover:text-blue-800">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            Tidak ada order yang ditemukan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
</x-app-layout>

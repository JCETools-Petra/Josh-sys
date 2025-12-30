<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Order Detail</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $property->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('restaurant.orders.history') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Kembali
            </a>
            @if($order->status !== 'cancelled' && $order->status !== 'completed')
            <button onclick="printReceipt()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Print Receipt
            </button>
            @endif
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <!-- Main Order Info -->
        <div class="md:col-span-2 space-y-6">
            <!-- Order Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Order Information</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Order Number</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $order->order_number }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Order Date & Time</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $order->order_time->format('d M Y, H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Order Type</div>
                        <div>
                            @if($order->order_type === 'room_service')
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Room Service</span>
                            @elseif($order->order_type === 'dine_in')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Dine-In</span>
                            @elseif($order->order_type === 'takeaway')
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Takeaway</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded">Delivery</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Status</div>
                        <div>
                            @if($order->status === 'completed')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Completed</span>
                            @elseif($order->status === 'cancelled')
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">Cancelled</span>
                            @elseif($order->status === 'preparing')
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Preparing</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">{{ ucfirst($order->status) }}</span>
                            @endif
                        </div>
                    </div>
                    @if($order->table_number)
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Table Number</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $order->table_number }}</div>
                    </div>
                    @endif
                    @if($order->hotelRoom)
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Room Number</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $order->hotelRoom->room_number }}</div>
                    </div>
                    @endif
                    @if($order->guest)
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Guest</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $order->guest->full_name }}</div>
                    </div>
                    @endif
                    @if($order->customer_name)
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Customer Name</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $order->customer_name }}</div>
                    </div>
                    @endif
                    @if($order->customer_phone)
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Customer Phone</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $order->customer_phone }}</div>
                    </div>
                    @endif
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Taken By</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $order->takenByUser->name ?? '-' }}</div>
                    </div>
                    @if($order->servedByUser)
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Served By</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $order->servedByUser->name }}</div>
                    </div>
                    @endif
                </div>

                @if($order->special_instructions)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Special Instructions</div>
                    <div class="text-gray-800 dark:text-white">{{ $order->special_instructions }}</div>
                </div>
                @endif
            </div>

            <!-- Order Items -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Order Items</h2>

                <div class="space-y-3">
                    @foreach($order->items as $item)
                    <div class="flex justify-between items-start p-3 bg-gray-50 dark:bg-gray-700 rounded">
                        <div class="flex-1">
                            <div class="font-semibold text-gray-800 dark:text-white">{{ $item->menuItem->name }}</div>
                            @if($item->special_instructions)
                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                Note: {{ $item->special_instructions }}
                            </div>
                            @endif
                        </div>
                        <div class="text-right ml-4">
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</div>
                            <div class="font-semibold text-gray-800 dark:text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="space-y-6">
            <!-- Payment Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Payment Summary</h2>

                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                        <span class="font-semibold text-gray-800 dark:text-white">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Tax (10%):</span>
                        <span class="text-gray-800 dark:text-white">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Service (5%):</span>
                        <span class="text-gray-800 dark:text-white">Rp {{ number_format($order->service_charge, 0, ',', '.') }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                    <div class="flex justify-between text-sm text-green-600">
                        <span>Discount:</span>
                        <span>- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($order->delivery_charge > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Delivery Charge:</span>
                        <span class="text-gray-800 dark:text-white">Rp {{ number_format($order->delivery_charge, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="border-t pt-2 flex justify-between font-bold text-lg">
                        <span class="text-gray-800 dark:text-white">Total:</span>
                        <span class="text-blue-600">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Payment Status</h2>

                <div class="space-y-3">
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Status</div>
                        <div class="font-semibold">
                            @if($order->payment_status === 'paid')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Paid</span>
                            @elseif($order->payment_status === 'charge_to_room')
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Charged to Room</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">{{ ucfirst($order->payment_status) }}</span>
                            @endif
                        </div>
                    </div>
                    @if($order->payment_method)
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Payment Method</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</div>
                    </div>
                    @endif
                    @if($order->paid_at)
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Paid At</div>
                        <div class="font-semibold text-gray-800 dark:text-white">{{ $order->paid_at->format('d M Y, H:i') }}</div>
                    </div>
                    @endif
                    @if($order->paid_amount > 0)
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Paid Amount</div>
                        <div class="font-semibold text-gray-800 dark:text-white">Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</div>
                    </div>
                    @endif
                    @if($order->balance_due > 0)
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Balance Due</div>
                        <div class="font-semibold text-red-600">Rp {{ number_format($order->balance_due, 0, ',', '.') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printReceipt() {
    window.print();
}
</script>
</x-app-layout>

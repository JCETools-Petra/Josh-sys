<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display System - {{ $property->name ?? 'Hotel' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: #1f2937;
            overflow: hidden;
        }
        .order-card {
            transition: all 0.3s ease;
        }
        .order-card:hover {
            transform: scale(1.02);
        }
        .new-order {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        .column-scroll {
            height: calc(100vh - 120px);
            overflow-y: auto;
        }
        .column-scroll::-webkit-scrollbar {
            width: 8px;
        }
        .column-scroll::-webkit-scrollbar-track {
            background: #374151;
        }
        .column-scroll::-webkit-scrollbar-thumb {
            background: #6b7280;
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-gray-900 text-white">
    <div class="container mx-auto px-4 py-4">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4 bg-gray-800 rounded-lg p-4">
            <div>
                <h1 class="text-3xl font-bold">🍳 Kitchen Display System</h1>
                <p class="text-gray-400 text-sm">{{ $property->name ?? 'Hotel' }}</p>
            </div>
            <div class="text-right">
                <div class="text-4xl font-bold" id="current-time">{{ now()->format('H:i') }}</div>
                <div class="text-sm text-gray-400" id="last-update">Last update: {{ now()->format('H:i:s') }}</div>
            </div>
        </div>

        <!-- Order Columns -->
        <div class="grid grid-cols-3 gap-4">
            <!-- New Orders -->
            <div class="bg-gray-800 rounded-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-yellow-400">🔔 New Orders</h2>
                    <span id="new-count" class="bg-yellow-500 text-gray-900 font-bold px-3 py-1 rounded-full">{{ $newOrders->count() }}</span>
                </div>
                <div id="new-orders-container" class="column-scroll space-y-3">
                    @forelse($newOrders as $order)
                    <div class="order-card new-order bg-yellow-900 border-2 border-yellow-500 rounded-lg p-4" data-order-id="{{ $order->id }}">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <div class="text-xl font-bold text-yellow-300">#{{ $order->order_number }}</div>
                                <div class="text-sm text-gray-300">
                                    @if($order->order_type === 'room_service')
                                        🛏️ Room {{ $order->hotelRoom->room_number }}
                                    @elseif($order->order_type === 'dine_in')
                                        🍽️ Table {{ $order->table_number }}
                                    @else
                                        📦 {{ ucfirst($order->order_type) }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-yellow-300">{{ $order->order_time->format('H:i') }}</div>
                                <div class="text-xs text-gray-400">{{ $order->order_time->diffForHumans() }}</div>
                            </div>
                        </div>

                        <div class="space-y-1 mb-3">
                            @foreach($order->items as $item)
                            <div class="flex justify-between bg-gray-800 p-2 rounded">
                                <span class="font-semibold">{{ $item->quantity }}x {{ $item->menuItem->name }}</span>
                                @if($item->special_instructions)
                                <span class="text-xs text-yellow-400">⚠️</span>
                                @endif
                            </div>
                            @if($item->special_instructions)
                            <div class="text-xs text-yellow-300 italic pl-2">📝 {{ $item->special_instructions }}</div>
                            @endif
                            @endforeach
                        </div>

                        @if($order->special_instructions)
                        <div class="bg-yellow-800 p-2 rounded mb-3 text-sm">
                            <strong>Special Instructions:</strong><br>
                            {{ $order->special_instructions }}
                        </div>
                        @endif

                        <button onclick="updateOrderStatus({{ $order->id }}, 'confirmed')"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded">
                            ✓ Confirm
                        </button>
                    </div>
                    @empty
                    <div class="text-center text-gray-500 py-8">No new orders</div>
                    @endforelse
                </div>
            </div>

            <!-- Preparing Orders -->
            <div class="bg-gray-800 rounded-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-orange-400">👨‍🍳 Preparing</h2>
                    <span id="preparing-count" class="bg-orange-500 text-gray-900 font-bold px-3 py-1 rounded-full">{{ $preparingOrders->count() }}</span>
                </div>
                <div id="preparing-orders-container" class="column-scroll space-y-3">
                    @forelse($preparingOrders as $order)
                    <div class="order-card bg-orange-900 border-2 border-orange-500 rounded-lg p-4" data-order-id="{{ $order->id }}">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <div class="text-xl font-bold text-orange-300">#{{ $order->order_number }}</div>
                                <div class="text-sm text-gray-300">
                                    @if($order->order_type === 'room_service')
                                        🛏️ Room {{ $order->hotelRoom->room_number }}
                                    @elseif($order->order_type === 'dine_in')
                                        🍽️ Table {{ $order->table_number }}
                                    @else
                                        📦 {{ ucfirst($order->order_type) }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-orange-300">{{ $order->order_time->format('H:i') }}</div>
                                <div class="text-xs text-gray-400">{{ $order->order_time->diffForHumans() }}</div>
                            </div>
                        </div>

                        <div class="space-y-1 mb-3">
                            @foreach($order->items as $item)
                            <div class="flex justify-between bg-gray-800 p-2 rounded">
                                <span class="font-semibold">{{ $item->quantity }}x {{ $item->menuItem->name }}</span>
                            </div>
                            @endforeach
                        </div>

                        <button onclick="updateOrderStatus({{ $order->id }}, 'ready')"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded">
                            ✓ Ready
                        </button>
                    </div>
                    @empty
                    <div class="text-center text-gray-500 py-8">No orders in preparation</div>
                    @endforelse
                </div>
            </div>

            <!-- Ready Orders -->
            <div class="bg-gray-800 rounded-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-green-400">✅ Ready for Pickup</h2>
                    <span id="ready-count" class="bg-green-500 text-gray-900 font-bold px-3 py-1 rounded-full">{{ $readyOrders->count() }}</span>
                </div>
                <div id="ready-orders-container" class="column-scroll space-y-3">
                    @forelse($readyOrders as $order)
                    <div class="order-card bg-green-900 border-2 border-green-500 rounded-lg p-4" data-order-id="{{ $order->id }}">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <div class="text-xl font-bold text-green-300">#{{ $order->order_number }}</div>
                                <div class="text-sm text-gray-300">
                                    @if($order->order_type === 'room_service')
                                        🛏️ Room {{ $order->hotelRoom->room_number }}
                                    @elseif($order->order_type === 'dine_in')
                                        🍽️ Table {{ $order->table_number }}
                                    @else
                                        📦 {{ ucfirst($order->order_type) }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm text-green-300">{{ $order->order_time->format('H:i') }}</div>
                        </div>

                        <div class="space-y-1">
                            @foreach($order->items as $item)
                            <div class="bg-gray-800 p-2 rounded">
                                <span class="font-semibold">{{ $item->quantity }}x {{ $item->menuItem->name }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-gray-500 py-8">No ready orders</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update order status
        async function updateOrderStatus(orderId, newStatus) {
            try {
                const response = await fetch(`/restaurant/orders/${orderId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                if (response.ok) {
                    // Play sound notification
                    playNotificationSound();

                    // Refresh orders
                    await refreshOrders();
                } else {
                    console.error('Failed to update order status');
                }
            } catch (error) {
                console.error('Error updating order status:', error);
            }
        }

        // Refresh orders via AJAX
        async function refreshOrders() {
            try {
                const response = await fetch('/kitchen/orders');
                const data = await response.json();

                // Update counters
                document.getElementById('new-count').textContent = data.newOrders.length;
                document.getElementById('preparing-count').textContent = data.preparingOrders.length;
                document.getElementById('ready-count').textContent = data.readyOrders.length;

                // Update last update time
                document.getElementById('last-update').textContent = 'Last update: ' + data.timestamp;

                // Render orders (simplified - in production, use proper diff/patch)
                renderOrders('new-orders-container', data.newOrders, 'yellow');
                renderOrders('preparing-orders-container', data.preparingOrders, 'orange');
                renderOrders('ready-orders-container', data.readyOrders, 'green');

            } catch (error) {
                console.error('Error refreshing orders:', error);
            }
        }

        // Render orders function
        function renderOrders(containerId, orders, color) {
            const container = document.getElementById(containerId);

            if (orders.length === 0) {
                container.innerHTML = '<div class="text-center text-gray-500 py-8">No orders</div>';
                return;
            }

            let html = '';
            orders.forEach(order => {
                const isNew = color === 'yellow';
                const isPreparing = color === 'orange';

                html += `
                    <div class="order-card ${isNew ? 'new-order' : ''} bg-${color}-900 border-2 border-${color}-500 rounded-lg p-4" data-order-id="${order.id}">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <div class="text-xl font-bold text-${color}-300">#${order.order_number}</div>
                                <div class="text-sm text-gray-300">
                                    ${order.room_number ? '🛏️ Room ' + order.room_number : ''}
                                    ${order.table_number ? '🍽️ Table ' + order.table_number : ''}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-${color}-300">${order.order_time}</div>
                                ${order.waiting_time ? '<div class="text-xs text-gray-400">' + order.waiting_time + '</div>' : ''}
                            </div>
                        </div>
                        <div class="space-y-1 mb-3">`;

                order.items.forEach(item => {
                    html += `
                        <div class="flex justify-between bg-gray-800 p-2 rounded">
                            <span class="font-semibold">${item.quantity}x ${item.name}</span>
                        </div>`;
                });

                html += `</div>`;

                if (isNew) {
                    html += '<button onclick="updateOrderStatus(' + order.id + ', \'confirmed\')" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded">✓ Confirm</button>';
                } else if (isPreparing) {
                    html += '<button onclick="updateOrderStatus(' + order.id + ', \'ready\')" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded">✓ Ready</button>';
                }

                html += '</div>';
            });

            container.innerHTML = html;
        }

        // Play notification sound
        function playNotificationSound() {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZSA8PTqXh8bllHAU2jtLyzHsrBSp7x/DglEILElyx6OypUxIIQJre8r9rIwU1g9Ly04MzBiJ0wO/mnUgPDU6l4fG5ZRwFNo7S8sx7KwUqe8fw4JRCC xBcsdjo');
            audio.play().catch(() => {});
        }

        // Update clock
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('current-time').textContent = hours + ':' + minutes;
        }

        // Auto-refresh every 10 seconds
        setInterval(refreshOrders, 10000);

        // Update clock every second
        setInterval(updateClock, 1000);

        // Check for new orders every 5 seconds and play sound
        let lastNewOrderCount = {{ $newOrders->count() }};
        setInterval(async () => {
            const response = await fetch('/kitchen/orders');
            const data = await response.json();

            if (data.newOrders.length > lastNewOrderCount) {
                playNotificationSound();
            }
            lastNewOrderCount = data.newOrders.length;
        }, 5000);
    </script>
</body>
</html>

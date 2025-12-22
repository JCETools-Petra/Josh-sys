<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Detail Tamu</h1>
            <p class="text-gray-600">Informasi Lengkap Tamu Hotel</p>
        </div>
        <a href="{{ route('frontoffice.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Guest Profile -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-center mb-6">
                    <div class="w-24 h-24 bg-blue-500 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-3xl font-bold text-white">
                            {{ strtoupper(substr($guest->first_name, 0, 1)) }}{{ strtoupper(substr($guest->last_name ?? '', 0, 1)) }}
                        </span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $guest->full_name }}</h2>

                    @if($guest->guest_type)
                    <span class="inline-block mt-2 px-3 py-1 text-sm font-semibold rounded-full
                        @if($guest->guest_type === 'vip') bg-yellow-100 text-yellow-800
                        @elseif($guest->guest_type === 'corporate') bg-blue-100 text-blue-800
                        @elseif($guest->guest_type === 'group') bg-purple-100 text-purple-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst($guest->guest_type) }}
                    </span>
                    @endif

                    @if($guest->is_blacklisted)
                    <div class="mt-2">
                        <span class="inline-block px-3 py-1 text-sm font-semibold bg-red-100 text-red-800 rounded-full">
                            ⚠️ Blacklisted
                        </span>
                    </div>
                    @endif
                </div>

                <div class="border-t pt-4 space-y-3">
                    <div>
                        <label class="text-sm text-gray-600">Email</label>
                        <div class="font-semibold text-gray-800">{{ $guest->email ?: '-' }}</div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Telepon</label>
                        <div class="font-semibold text-gray-800">{{ $guest->phone }}</div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">{{ $guest->id_type_label }}</label>
                        <div class="font-semibold text-gray-800">{{ $guest->id_number }}</div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Alamat</label>
                        <div class="font-semibold text-gray-800">
                            {{ $guest->address ?: '-' }}
                            @if($guest->city)
                            <br><span class="text-sm">{{ $guest->city }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="border-t mt-4 pt-4">
                    <h3 class="font-semibold text-gray-800 mb-3">Statistik</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-3 rounded text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $guest->total_stays }}</div>
                            <div class="text-xs text-gray-600">Total Menginap</div>
                        </div>
                        <div class="bg-green-50 p-3 rounded text-center">
                            <div class="text-lg font-bold text-green-600">Rp {{ number_format($guest->lifetime_value, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-600">Lifetime Value</div>
                        </div>
                    </div>
                </div>

                @if($guest->preferences)
                <div class="border-t mt-4 pt-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Preferensi</h3>
                    <div class="text-sm text-gray-600">{{ $guest->preferences }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Guest Activity -->
        <div class="lg:col-span-2">
            <!-- Room Stays History -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Riwayat Menginap</h3>

                @if($guest->roomStays->count() > 0)
                <div class="space-y-4">
                    @foreach($guest->roomStays->sortByDesc('check_in_date') as $stay)
                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <div class="text-2xl font-bold text-blue-600">
                                        {{ $stay->hotelRoom->room_number }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-800">
                                            {{ $stay->hotelRoom->roomType->name ?? 'Standard' }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ $stay->property->name }}
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                    <div>
                                        <label class="text-gray-600">Check-In</label>
                                        <div class="font-semibold">{{ $stay->check_in_date->format('d M Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $stay->check_in_date->format('H:i') }}</div>
                                    </div>
                                    <div>
                                        <label class="text-gray-600">Check-Out</label>
                                        <div class="font-semibold">{{ $stay->check_out_date->format('d M Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $stay->check_out_date->format('H:i') }}</div>
                                    </div>
                                    <div>
                                        <label class="text-gray-600">Malam</label>
                                        <div class="font-semibold">{{ $stay->nights }} malam</div>
                                    </div>
                                    <div>
                                        <label class="text-gray-600">Total</label>
                                        <div class="font-semibold text-green-600">Rp {{ number_format($stay->total_amount, 0, ',', '.') }}</div>
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                        @if($stay->status === 'checked_in') bg-blue-100 text-blue-800
                                        @elseif($stay->status === 'checked_out') bg-green-100 text-green-800
                                        @elseif($stay->status === 'reserved') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($stay->status) }}
                                    </span>

                                    <span class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">
                                        {{ ucfirst(str_replace('_', ' ', $stay->source)) }}
                                    </span>

                                    @if($stay->payment_status === 'paid')
                                    <span class="inline-block px-2 py-1 text-xs bg-green-100 text-green-800 rounded">
                                        ✓ Paid
                                    </span>
                                    @elseif($stay->payment_status === 'pending')
                                    <span class="inline-block px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">
                                        ⏳ Pending
                                    </span>
                                    @endif
                                </div>

                                @if($stay->special_requests)
                                <div class="mt-2 text-sm text-gray-600">
                                    <span class="font-semibold">Permintaan:</span> {{ $stay->special_requests }}
                                </div>
                                @endif
                            </div>

                            @if($stay->status === 'checked_in')
                            <form action="{{ route('frontoffice.check-out', $stay) }}" method="POST" onsubmit="return confirm('Yakin check-out tamu ini?')">
                                @csrf
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm transition">
                                    Check-Out
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <p>Belum ada riwayat menginap</p>
                </div>
                @endif
            </div>

            <!-- F&B Orders History -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Riwayat Order F&B</h3>

                @if($guest->fnbOrders->count() > 0)
                <div class="space-y-3">
                    @foreach($guest->fnbOrders->sortByDesc('created_at') as $order)
                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <span class="font-semibold text-gray-800">#{{ $order->order_number }}</span>
                                    <span class="px-2 py-1 text-xs rounded
                                        @if($order->order_type === 'room_service') bg-blue-100 text-blue-800
                                        @elseif($order->order_type === 'dine_in') bg-green-100 text-green-800
                                        @elseif($order->order_type === 'takeaway') bg-yellow-100 text-yellow-800
                                        @else bg-purple-100 text-purple-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}
                                    </span>
                                    <span class="px-2 py-1 text-xs rounded
                                        @if($order->status === 'completed') bg-green-100 text-green-800
                                        @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>

                                <div class="mt-2 text-sm text-gray-600">
                                    {{ $order->created_at->format('d M Y H:i') }}
                                    @if($order->hotelRoom)
                                    - Room {{ $order->hotelRoom->room_number }}
                                    @endif
                                </div>

                                <div class="mt-2 text-sm">
                                    @foreach($order->items as $item)
                                    <div class="text-gray-700">
                                        {{ $item->quantity }}x {{ $item->menuItem->name ?? 'Item' }}
                                        <span class="text-gray-500">@ Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="font-bold text-green-600">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                                @if($order->payment_status === 'paid')
                                <div class="text-xs text-green-600 mt-1">✓ Paid</div>
                                @else
                                <div class="text-xs text-yellow-600 mt-1">⏳ Pending</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p>Belum ada order F&B</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
</x-app-layout>
